(function () {
    'use strict';

    var config = window.pondAnalyticsAdmin || {};
    var storageKey = 'pondAnalyticsDays';

    function savedDays() {
        try {
            return window.localStorage.getItem(storageKey) || '30';
        } catch (e) {
            return '30';
        }
    }

    function load(panel, days, refresh) {
        var body = new window.FormData();
        body.append('action', 'thepond_analytics_panel');
        body.append('nonce', config.nonce);
        body.append('panel', panel.getAttribute('data-panel'));
        body.append('days', days);
        if (refresh) {
            body.append('refresh', '1');
        }
        panel.setAttribute('data-days', days);
        panel.classList.add('tpa-busy');
        return window.fetch(config.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body })
            .then(function (response) { return response.json(); })
            .then(function (json) {
                if (!json || !json.success) {
                    throw new Error('failed');
                }
                panel.innerHTML = json.data.html;
            })
            .catch(function () {
                panel.innerHTML = '<p class="tpa-note tpa-note-warning"></p>';
                panel.firstChild.textContent = config.error;
            })
            .then(function () {
                panel.classList.remove('tpa-busy');
            });
    }

    function panels() {
        return Array.prototype.slice.call(document.querySelectorAll('.tpa-panel'));
    }

    // Load panels one after another so a cold cache doesn't fire many
    // Google requests at once.
    function loadAll(days, refresh) {
        panels().reduce(function (chain, panel) {
            return chain.then(function () { return load(panel, days, refresh); });
        }, Promise.resolve());
        Array.prototype.forEach.call(document.querySelectorAll('.tpa-range'), function (select) {
            select.value = days;
        });
    }

    document.addEventListener('change', function (event) {
        if (!event.target.classList || !event.target.classList.contains('tpa-range')) {
            return;
        }
        try {
            window.localStorage.setItem(storageKey, event.target.value);
        } catch (e) {}
        loadAll(event.target.value, false);
    });

    document.addEventListener('click', function (event) {
        var button = event.target.closest && event.target.closest('.tpa-refresh');
        if (button) {
            loadAll(savedDays(), true);
        }
    });

    if (panels().length) {
        loadAll(savedDays(), false);
    }
})();
