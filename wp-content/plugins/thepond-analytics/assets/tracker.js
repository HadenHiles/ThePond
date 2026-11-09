/*
 * The Pond Analytics tracker.
 *
 * Reads window.pondAnalytics (printed by the plugin), waits for cookie consent
 * from the Hu-manity banner, then loads GA4 and (optionally) Microsoft Clarity
 * and records landing, signup, login and member content events.
 */
(function (w, d) {
    'use strict';

    var cfg = w.pondAnalytics || {};
    var VISITOR_TYPES = ['prospect', 'member', 'former_member', 'staff'];
    // Hu-manity categories: 3 = site optimization (analytics), 4 = ad personalization (Clarity shares data with Microsoft Ads).
    var CONSENT_ANALYTICS = 3;
    var CONSENT_RECORDINGS = 4;

    function readCookie(name) {
        var parts = (d.cookie || '').split(/;\s*/);
        for (var i = 0; i < parts.length; i++) {
            var index = parts[i].indexOf('=');
            if (index > 0 && parts[i].slice(0, index) === name) {
                var value = parts[i].slice(index + 1);
                try {
                    return decodeURIComponent(value);
                } catch (e) {
                    return value;
                }
            }
        }
        return null;
    }

    function writeCookie(name, value, days) {
        d.cookie = name + '=' + encodeURIComponent(value) + '; path=/; max-age=' + days * 86400 + '; SameSite=Lax' +
            (w.location.protocol === 'https:' ? '; Secure' : '');
    }

    function store() {
        try {
            var s = w.sessionStorage;
            s.setItem('__pond', '1');
            s.removeItem('__pond');
            return s;
        } catch (e) {
            return null;
        }
    }

    function visitorType() {
        if (cfg.visitorType && VISITOR_TYPES.indexOf(cfg.visitorType) > -1) {
            return cfg.visitorType;
        }
        var cookie = readCookie('pond_vt');
        return VISITOR_TYPES.indexOf(cookie) > -1 ? cookie : 'prospect';
    }

    function consentGranted(category) {
        var hu = w.huOptions;
        if (!hu || !hu.blocking) {
            return true;
        }
        var raw = readCookie('hu-consent');
        if (!raw) {
            return false;
        }
        try {
            var consent = JSON.parse(raw);
            return !!(consent && consent.consent && consent.categories && consent.categories[category] === true);
        } catch (e) {
            return false;
        }
    }

    function whenConsented(category, callback) {
        if (consentGranted(category)) {
            callback();
            return;
        }
        var done = false;
        function onConsent(event) {
            var categories = event && event.detail && event.detail.categories;
            if (done || !((categories && categories[category] === true) || consentGranted(category))) {
                return;
            }
            done = true;
            d.removeEventListener('set-consent.hu', onConsent);
            callback();
        }
        d.addEventListener('set-consent.hu', onConsent);
    }

    function loadScript(src, onload) {
        var script = d.createElement('script');
        script.async = true;
        script.src = src;
        if (onload) {
            script.onload = onload;
        }
        (d.head || d.documentElement).appendChild(script);
        return script;
    }

    function clean(value, length) {
        return String(value || '').replace(/\s+/g, ' ').trim().slice(0, length || 100);
    }

    function token(value) {
        return clean(value, 100).toLowerCase().replace(/[^a-z0-9._\-\/ ]/g, '');
    }

    function linkLabel(el) {
        return clean(el.getAttribute('data-pond-label') || el.textContent || el.getAttribute('aria-label') || el.getAttribute('title'), 80);
    }

    function sectionName(el) {
        var section = el.closest('section');
        if (!section) {
            if (el.closest('header, nav')) {
                return 'nav';
            }
            return el.closest('footer') ? 'footer' : 'page';
        }
        if (section.id) {
            return section.id;
        }
        var className = typeof section.className === 'string' ? section.className : '';
        var match = className.match(/\bpond-([a-z0-9-]+)/) || className.match(/\b(hero)\b/);
        if (match) {
            return match[1];
        }
        var first = className.split(/\s+/).filter(function (name) { return name && name !== 'section'; })[0];
        return first || 'main';
    }

    function classifyLink(el) {
        var href = el.getAttribute('href') || '';
        var plan = (href.match(/\/register\/([a-z0-9-]+)/i) || [])[1] || '';
        var group = el.getAttribute('data-pond-cta') || (plan ? 'signup'
            : /#(pricingSect|choose-your-subscription)\b/.test(href) ? 'plans'
            : /free-lessons/.test(href) ? 'free_lessons'
            : /\/(login|member-dashboard)\b/.test(href) ? 'login'
            : /\/(support|member-support)\b/.test(href) ? 'support'
            : href.charAt(0) === '#' ? 'navigation'
            : 'other');
        var info = { cta: linkLabel(el), cta_group: group, cta_location: sectionName(el), link_url: clean(href, 100) };
        if (plan) {
            info.plan = plan.toLowerCase();
        }
        return info;
    }

    function providerMethod(el) {
        var id = (el.id || '').toLowerCase();
        return id.indexOf('google') > -1 ? 'google' : id.indexOf('facebook') > -1 ? 'facebook' : id.indexOf('email') > -1 ? 'email' : '';
    }

    function currentTouch(now) {
        var params = new URLSearchParams(w.location.search);
        var referrer = '';
        try {
            var url = new URL(d.referrer);
            if (url.hostname !== w.location.hostname) {
                referrer = url.hostname;
            }
        } catch (e) {}
        var source = params.get('utm_source') || (params.get('gclid') ? 'google' : params.get('fbclid') ? 'facebook' : '');
        var medium = params.get('utm_medium') || (params.get('gclid') ? 'cpc' : params.get('fbclid') ? 'social' : '');
        return {
            s: token(source),
            m: token(medium),
            c: token(params.get('utm_campaign')),
            r: token(referrer),
            t: Math.floor(now / 1000),
            l: clean(w.location.pathname, 100)
        };
    }

    function readSource() {
        try {
            var data = JSON.parse(readCookie('pond_src') || 'null');
            return data && typeof data === 'object' ? data : {};
        } catch (e) {
            return {};
        }
    }

    // First touch and visit count stay in a first-party cookie so the server
    // can attribute a signup when the payment completes on Stripe.
    function rememberSource(session, now) {
        var data = readSource();
        var newVisit = !(session && session.getItem('pond_visit'));
        if (session) {
            session.setItem('pond_visit', '1');
        }
        if (!data.f) {
            data.f = currentTouch(now);
            data.v = 0;
        }
        if (newVisit) {
            data.v = (parseInt(data.v, 10) || 0) + 1;
        }
        writeCookie('pond_src', JSON.stringify(data), 400);
        return data;
    }

    function rememberSignupMethod(method) {
        if (!method || !consentGranted(CONSENT_ANALYTICS)) {
            return;
        }
        var data = readSource();
        data.sm = method;
        writeCookie('pond_src', JSON.stringify(data), 400);
    }

    var vt = visitorType();
    var session = store();
    var api = {
        visitorType: vt,
        track: function () {},
        classifyLink: classifyLink,
        sectionName: sectionName,
        consentGranted: consentGranted
    };
    w.PondAnalytics = api;

    if (!cfg.measurementId) {
        return;
    }
    if (vt === 'staff') {
        w['ga-disable-' + cfg.measurementId] = true;
        return;
    }

    w.dataLayer = w.dataLayer || [];
    if (typeof w.gtag !== 'function') {
        w.gtag = function () {
            w.dataLayer.push(arguments);
        };
    }
    var pageType = cfg.pageType || 'other';

    function track(name, params) {
        var payload = {};
        Object.keys(params || {}).forEach(function (key) {
            var value = params[key];
            if (value !== undefined && value !== null && value !== '') {
                payload[key] = value;
            }
        });
        payload.visitor_type = vt;
        payload.page_type = pageType;
        if (cfg.debug) {
            payload.debug_mode = true;
        }
        w.gtag('event', name, payload);
    }
    api.track = track;

    var config = {};
    if (cfg.userId) {
        config.user_id = String(cfg.userId);
    }
    if (cfg.debug) {
        config.debug_mode = true;
    }
    w.gtag('js', new Date());
    w.gtag('set', 'user_properties', { member_status: vt });
    w.gtag('set', { visitor_type: vt, page_type: pageType });
    w.gtag('config', cfg.measurementId, config);

    /* ---------- Page events ---------- */

    if (pageType === 'landing') {
        track('landing_view', {});
    }

    if (pageType === 'register' && cfg.plan) {
        track('begin_checkout', {
            plan: cfg.plan.id,
            value: cfg.plan.price,
            currency: cfg.currency,
            items: [{ item_id: cfg.plan.id, item_name: cfg.plan.name, price: cfg.plan.price, quantity: 1 }]
        });
    }

    if (pageType === 'content' && cfg.content) {
        track('content_view', {
            content_type: cfg.content.type,
            content_title: clean(cfg.content.title, 100),
            course: cfg.content.course ? clean(cfg.content.course, 100) : ''
        });
    }

    if (cfg.loggedIn && session) {
        var loginMethod = session.getItem('pond_login');
        if (loginMethod) {
            session.removeItem('pond_login');
            track('login', { method: loginMethod });
        }
        if (cfg.signup && cfg.signup.transactionId && !session.getItem('pond_signup_' + cfg.signup.transactionId)) {
            session.setItem('pond_signup_' + cfg.signup.transactionId, '1');
            track('sign_up', { method: session.getItem('pond_signup_method') || readSource().sm || 'unknown', plan: cfg.signup.plan });
        }
    }

    /* ---------- Clicks and forms ---------- */

    d.addEventListener('click', function (event) {
        var target = event.target;
        if (!target || !target.closest) {
            return;
        }

        var faq = target.closest('.c-accordion-item-q');
        if (faq && faq.getAttribute('aria-expanded') !== 'true') {
            track('faq_open', { faq_question: linkLabel(faq), section: sectionName(faq) });
            return;
        }

        var provider = target.closest('.pond-auth-provider');
        if (provider) {
            var method = providerMethod(provider);
            if (pageType === 'register') {
                if (session) {
                    session.setItem('pond_signup_method', method);
                }
                rememberSignupMethod(method);
                track('signup_method_select', { signup_method: method, plan: cfg.plan ? cfg.plan.id : '' });
            } else if (pageType === 'login' && session) {
                session.setItem('pond_login', method);
            }
            return;
        }

        var link = target.closest('a[href], [data-pond-cta]');
        if (!link) {
            return;
        }
        if (pageType === 'landing') {
            track('landing_cta_click', classifyLink(link));
        } else if (pageType === 'member_dashboard') {
            track('dashboard_click', {
                dashboard_area: sectionName(link),
                link_text: linkLabel(link),
                link_url: clean((link.getAttribute('href') || '').split('?')[0], 100)
            });
        } else if (pageType === 'login' && /forgot|lostpassword|reset/i.test(link.getAttribute('href') || '')) {
            track('password_reset_click', {});
        }
    }, true);

    d.addEventListener('submit', function (event) {
        var form = event.target;
        if (!form || !form.matches) {
            return;
        }
        if (form.matches('.mepr-signup-form')) {
            track('checkout_submit', {
                plan: cfg.plan ? cfg.plan.id : '',
                signup_method: (session && session.getItem('pond_signup_method')) || 'email'
            });
        } else if (pageType === 'login' && form.matches('#mepr_loginform, .mepr-login-form, form[name="mepr_loginform"]') && session) {
            session.setItem('pond_login', 'email');
        }
    }, true);

    /* ---------- Landing sections ---------- */

    if (pageType === 'landing' && 'IntersectionObserver' in w) {
        var seenSections = {};
        var observer = new w.IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }
                observer.unobserve(entry.target);
                var name = sectionName(entry.target);
                if (!seenSections[name]) {
                    seenSections[name] = true;
                    track('landing_section_view', { section: name });
                }
            });
        }, { rootMargin: '0px 0px -40% 0px', threshold: 0 });
        Array.prototype.forEach.call(d.querySelectorAll('main section'), function (section) {
            observer.observe(section);
        });
    }

    /* ---------- Vimeo ---------- */

    function attachVideo(frame) {
        if (frame.getAttribute('data-pond-video')) {
            return;
        }
        frame.setAttribute('data-pond-video', '1');
        var player;
        try {
            player = new w.Vimeo.Player(frame);
        } catch (e) {
            return;
        }
        var title = clean(frame.getAttribute('title'), 100);
        var started = false;
        var marks = {};
        function refreshTitle() {
            player.getVideoTitle().then(function (value) {
                if (value) {
                    title = clean(value, 100);
                }
            }).catch(function () {});
        }
        function params(percent) {
            var data = { video_provider: 'vimeo', video_title: title, video_url: clean((frame.getAttribute('src') || '').split('?')[0], 100) };
            if (percent) {
                data.video_percent = percent;
            }
            return data;
        }
        refreshTitle();
        player.on('loaded', function () {
            started = false;
            marks = {};
            refreshTitle();
        });
        player.on('play', function () {
            if (!started) {
                started = true;
                track('video_start', params(0));
            }
        });
        player.on('timeupdate', function (data) {
            var percent = Math.floor(((data && data.percent) || 0) * 100);
            [25, 50, 75].forEach(function (mark) {
                if (percent >= mark && !marks[mark]) {
                    marks[mark] = true;
                    track('video_progress', params(mark));
                }
            });
        });
        player.on('ended', function () {
            if (!marks[100]) {
                marks[100] = true;
                track('video_complete', params(100));
            }
        });
    }

    function setupVideos() {
        var selector = 'iframe[src*="player.vimeo.com"]';
        var loading = false;
        function scan() {
            if (!d.querySelector(selector + ':not([data-pond-video])')) {
                return;
            }
            if (w.Vimeo && w.Vimeo.Player) {
                Array.prototype.forEach.call(d.querySelectorAll(selector), attachVideo);
            } else if (!loading) {
                loading = true;
                loadScript('https://player.vimeo.com/api/player.js', scan);
            }
        }
        scan();
        if ('MutationObserver' in w && d.body) {
            var timer = null;
            new w.MutationObserver(function () {
                clearTimeout(timer);
                timer = setTimeout(scan, 400);
            }).observe(d.body, { childList: true, subtree: true });
        }
    }

    /* ---------- Third-party scripts after consent ---------- */

    function onReady(callback) {
        if (d.readyState === 'loading') {
            d.addEventListener('DOMContentLoaded', callback);
        } else {
            callback();
        }
    }

    whenConsented(CONSENT_ANALYTICS, function () {
        loadScript('https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(cfg.measurementId));
        if (vt === 'prospect' || vt === 'former_member') {
            rememberSource(session, Date.now());
        }
        onReady(setupVideos);
    });

    if (cfg.clarityId) {
        whenConsented(CONSENT_RECORDINGS, function () {
            w.clarity = w.clarity || function () {
                (w.clarity.q = w.clarity.q || []).push(arguments);
            };
            loadScript('https://www.clarity.ms/tag/' + encodeURIComponent(cfg.clarityId));
            w.clarity('consent');
            w.clarity('set', 'visitor_type', vt);
            w.clarity('set', 'page_type', pageType);
            if (cfg.plan) {
                w.clarity('set', 'plan', cfg.plan.id);
            }
            if (cfg.userId) {
                w.clarity('identify', 'wp-' + cfg.userId);
            }
        });
    }
})(window, document);
