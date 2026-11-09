// Run: NODE_PATH=/path/to/node_modules node wp-content/plugins/thepond-analytics/tests/tracker-test.cjs  (requires jsdom)
'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');
const { JSDOM } = require('jsdom');

const source = fs.readFileSync(path.join(__dirname, '..', 'assets', 'tracker.js'), 'utf8');
let checks = 0;

function check(condition, message) {
    assert.ok(condition, message);
    checks++;
}

function page(cfg, options = {}) {
    const dom = new JSDOM(options.html || '<!doctype html><html><head></head><body></body></html>', {
        url: options.url || 'https://thepond.howtohockey.com/',
        referrer: options.referrer || undefined,
        runScripts: 'outside-only',
    });
    const w = dom.window;
    if (options.huBlocking !== undefined) {
        w.huOptions = { blocking: options.huBlocking };
    }
    (options.cookies || []).forEach((cookie) => { w.document.cookie = cookie; });
    Object.entries(options.session || {}).forEach(([key, value]) => w.sessionStorage.setItem(key, value));
    w.pondAnalytics = cfg;
    w.eval(source);
    w.document.addEventListener('click', (event) => event.preventDefault());
    return w;
}

function events(w, name) {
    return (w.dataLayer || [])
        .map((entry) => Array.from(entry))
        .filter((args) => args[0] === 'event' && (!name || args[1] === name))
        .map((args) => args[2]);
}

function scripts(w) {
    return Array.from(w.document.querySelectorAll('script[src]')).map((script) => script.src);
}

function cookie(w, name) {
    const match = w.document.cookie.split(/;\s*/).find((part) => part.startsWith(name + '='));
    return match ? decodeURIComponent(match.slice(name.length + 1)) : null;
}

function click(w, selector) {
    w.document.querySelector(selector).dispatchEvent(new w.MouseEvent('click', { bubbles: true, cancelable: true }));
}

const landingHtml = `<!doctype html><html><head></head><body>
<header><a id="nav-login" href="/login">Login</a></header>
<main>
  <section class="hero section"><a id="hero-cta" href="#pricingSect">Join The Pond</a></section>
  <section id="pricingSect"><a id="monthly" href="/register/monthly/"> Start   monthly </a></section>
  <section class="pond-faq"><button class="c-accordion-item-q" aria-expanded="false">Can I cancel?</button>
    <a id="free" href="/courses/free-lessons/lesson-1/">Free lesson</a></section>
</main>
<footer><a id="support" href="/support">Support</a></footer>
</body></html>`;

// Logged-out prospect on the landing page with the consent banner blocking.
let w = page({ measurementId: 'G-TEST', pageType: 'landing', currency: 'CAD', loggedIn: false, clarityId: 'clar1' }, {
    html: landingHtml,
    url: 'https://thepond.howtohockey.com/?utm_source=YouTube&utm_medium=video&utm_campaign=Spring',
    referrer: 'https://www.youtube.com/',
    huBlocking: true,
});
check(w.PondAnalytics.visitorType === 'prospect', 'logged-out visitors are prospects');
check(events(w, 'landing_view').length === 1, 'landing view is queued');
check(events(w, 'landing_view')[0].visitor_type === 'prospect' && events(w, 'landing_view')[0].page_type === 'landing', 'events carry visitor and page type');
check(scripts(w).length === 0, 'nothing loads before consent');
check(cookie(w, 'pond_src') === null, 'no attribution cookie before consent');

click(w, '#monthly');
let cta = events(w, 'landing_cta_click').pop();
check(cta.cta_group === 'signup' && cta.plan === 'monthly' && cta.cta_location === 'pricingSect' && cta.cta === 'Start monthly', 'signup cta is classified');
click(w, '#hero-cta');
cta = events(w, 'landing_cta_click').pop();
check(cta.cta_group === 'plans' && cta.cta_location === 'hero', 'plans cta in hero');
click(w, '#nav-login');
cta = events(w, 'landing_cta_click').pop();
check(cta.cta_group === 'login' && cta.cta_location === 'nav', 'login click from nav');
click(w, '#free');
cta = events(w, 'landing_cta_click').pop();
check(cta.cta_group === 'free_lessons' && cta.cta_location === 'faq', 'free lesson in pond-faq section');
click(w, '#support');
check(events(w, 'landing_cta_click').pop().cta_location === 'footer', 'footer link');
click(w, '.c-accordion-item-q');
check(events(w, 'faq_open').pop().faq_question === 'Can I cancel?', 'faq open is tracked');

// Recordings consent alone does not load GA.
w.document.dispatchEvent(new w.CustomEvent('set-consent.hu', { detail: { categories: { 3: false, 4: true } } }));
check(!scripts(w).some((src) => src.includes('googletagmanager')), 'GA waits for analytics consent');
check(scripts(w).some((src) => src.includes('clarity.ms/tag/clar1')), 'clarity loads with category 4');
w.document.dispatchEvent(new w.CustomEvent('set-consent.hu', { detail: { categories: { 3: true, 4: true } } }));
check(scripts(w).filter((src) => src.includes('googletagmanager.com/gtag/js?id=G-TEST')).length === 1, 'GA loads once after consent');
let src = JSON.parse(cookie(w, 'pond_src'));
check(src.f.s === 'youtube' && src.f.m === 'video' && src.f.c === 'spring' && src.f.r === 'www.youtube.com' && src.v === 1, 'first touch is remembered');
check(w.clarity.q.some((args) => args[0] === 'set' && args[1] === 'visitor_type' && args[2] === 'prospect'), 'clarity is tagged');

// Returning visitor in a new session keeps the first touch and counts the visit.
w = page({ measurementId: 'G-TEST', pageType: 'landing' }, {
    html: landingHtml,
    url: 'https://thepond.howtohockey.com/?utm_source=facebook',
    cookies: ['pond_src=' + encodeURIComponent(JSON.stringify({ f: { s: 'youtube' }, v: 2 }))],
});
src = JSON.parse(cookie(w, 'pond_src'));
check(src.f.s === 'youtube' && src.v === 3, 'first touch kept and visit counted without a blocking banner');

// Members coming back to log in are tagged from the pond_vt cookie.
w = page({ measurementId: 'G-TEST', pageType: 'landing' }, { html: landingHtml, cookies: ['pond_vt=member'], huBlocking: true });
check(w.PondAnalytics.visitorType === 'member' && events(w, 'landing_view')[0].visitor_type === 'member', 'returning member is recognised');
w.document.dispatchEvent(new w.CustomEvent('set-consent.hu', { detail: { categories: { 3: true } } }));
check(cookie(w, 'pond_src') === null, 'members do not get attribution cookies');
const userProps = w.dataLayer.map((entry) => Array.from(entry)).find((args) => args[0] === 'set' && args[1] === 'user_properties');
check(userProps[2].member_status === 'member', 'member status user property');

// Staff are never tracked.
w = page({ measurementId: 'G-TEST', pageType: 'landing', visitorType: 'staff', loggedIn: true, userId: 1 }, { html: landingHtml });
check(w['ga-disable-G-TEST'] === true && !w.dataLayer && scripts(w).length === 0, 'staff are excluded');

// Registration page.
w = page({ measurementId: 'G-TEST', pageType: 'register', currency: 'CAD', plan: { id: 'monthly', name: 'Monthly', price: 19.99 } }, {
    html: '<body><a class="pond-auth-provider" id="signup-with-google-btn" href="#">Google</a><form class="mepr-signup-form"></form></body>',
});
const checkout = events(w, 'begin_checkout')[0];
check(checkout.plan === 'monthly' && checkout.value === 19.99 && checkout.items[0].item_name === 'Monthly', 'begin checkout');
click(w, '#signup-with-google-btn');
check(events(w, 'signup_method_select').pop().signup_method === 'google', 'signup method is tracked');
check(JSON.parse(cookie(w, 'pond_src')).sm === 'google', 'signup method is stored for the server');
w.document.querySelector('form').dispatchEvent(new w.Event('submit', { bubbles: true, cancelable: true }));
check(events(w, 'checkout_submit').pop().signup_method === 'google', 'checkout submit carries method');

// Login page, then the next logged-in page.
w = page({ measurementId: 'G-TEST', pageType: 'login' }, {
    html: '<body><button class="pond-auth-provider" id="mo_firebase_Facebook_provider_login">Facebook</button><form id="mepr_loginform"></form></body>',
});
click(w, '#mo_firebase_Facebook_provider_login');
check(w.sessionStorage.getItem('pond_login') === 'facebook', 'provider login remembered');
w = page({ measurementId: 'G-TEST', pageType: 'member_dashboard', loggedIn: true, userId: 5, visitorType: 'member' }, {
    html: '<body><section id="skills-vault"><a id="vault" href="/skills-vault/?x=1">Open vault</a></section></body>',
    session: { pond_login: 'facebook' },
});
check(events(w, 'login')[0].method === 'facebook' && w.sessionStorage.getItem('pond_login') === null, 'login sent once');
const config = w.dataLayer.map((entry) => Array.from(entry)).find((args) => args[0] === 'config');
check(config[2].user_id === '5', 'user id is configured');
click(w, '#vault');
const dash = events(w, 'dashboard_click').pop();
check(dash.dashboard_area === 'skills-vault' && dash.link_url === '/skills-vault/', 'dashboard click');

// Thank-you page signup only fires once per transaction.
const signupCfg = { measurementId: 'G-TEST', pageType: 'member_dashboard', loggedIn: true, userId: 6, visitorType: 'member', signup: { transactionId: 77, plan: 'monthly' } };
w = page(signupCfg, { session: { pond_signup_method: 'email' } });
check(events(w, 'sign_up')[0].method === 'email' && events(w, 'sign_up')[0].plan === 'monthly', 'sign up sent');
w.eval(source);
check(events(w, 'sign_up').length === 1, 'sign up not repeated');

// Content views.
w = page({ measurementId: 'G-TEST', pageType: 'content', loggedIn: true, visitorType: 'member', content: { type: 'sfwd-lessons', title: '  Toe   drag ', course: 'Puck Skills' } });
const view = events(w, 'content_view')[0];
check(view.content_title === 'Toe drag' && view.course === 'Puck Skills', 'content view');

// No measurement id: inert.
w = page({});
check(!w.dataLayer && typeof w.PondAnalytics.classifyLink === 'function', 'inert without a measurement id');

console.log(`OK: ${checks} checks passed`);
