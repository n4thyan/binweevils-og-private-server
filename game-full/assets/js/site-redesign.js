(function () {
    'use strict';

    var root = document.documentElement;

    function getPref(key) {
        try { return window.localStorage.getItem(key); }
        catch (e) { return null; }
    }

    function setPref(key, value) {
        try {
            if (value === null || value === undefined) window.localStorage.removeItem(key);
            else window.localStorage.setItem(key, value);
        } catch (e) {}
    }

    function applyClassPref(storageKey, className) {
        var enabled = getPref(storageKey) === '1';
        root.classList.toggle(className, enabled);
        return enabled;
    }

    function formatNumber(value) {
        var number = Number(value);
        if (!isFinite(number)) return '0';
        try { return new Intl.NumberFormat().format(number); }
        catch (e) { return String(number); }
    }

    function updateText(selector, value, formatter) {
        document.querySelectorAll(selector).forEach(function (node) {
            node.textContent = formatter ? formatter(value) : String(value);
        });
    }

    applyClassPref('bw_reduce_motion', 'bw-reduce-motion');
    applyClassPref('bw_compact_layout', 'bw-compact-layout');

    document.querySelectorAll('[data-site-pref]').forEach(function (control) {
        var pref = control.getAttribute('data-site-pref');
        var storageKey = pref === 'reduce-motion' ? 'bw_reduce_motion' : (pref === 'compact-layout' ? 'bw_compact_layout' : null);
        var className = pref === 'reduce-motion' ? 'bw-reduce-motion' : (pref === 'compact-layout' ? 'bw-compact-layout' : null);
        if (!storageKey || !className) return;

        control.checked = getPref(storageKey) === '1';
        control.addEventListener('change', function () {
            setPref(storageKey, control.checked ? '1' : '0');
            root.classList.toggle(className, control.checked);
        });
    });

    var loginForm = document.querySelector('form[action="/login/login.php"]');
    if (loginForm) {
        var username = loginForm.querySelector('[name="userID"]');
        var remember = loginForm.querySelector('[data-remember-username]');
        var remembered = getPref('bw_remembered_username');

        if (username && remembered && !username.value) username.value = remembered;
        if (remember) remember.checked = !!remembered;

        loginForm.addEventListener('submit', function () {
            if (!username || !remember) return;
            setPref('bw_remembered_username', remember.checked ? username.value.trim() : null);
        });
    }

    function applyAccountState(user) {
        if (!user) return;

        updateText('[data-account-stat="username"]', user.username);
        updateText('[data-account-stat="level"]', user.level);
        updateText('[data-account-stat="prestige"]', user.prestige);
        updateText('[data-account-stat="lifetime-xp"]', user.lifetimeXp, formatNumber);
        updateText('[data-account-stat="banked-xp"]', user.bankedXp, formatNumber);
        updateText('[data-account-stat="next-xp"]', user.nextXp, formatNumber);
        updateText('[data-account-stat="mulch"]', user.mulch, formatNumber);
        updateText('[data-account-stat="dosh"]', user.dosh, formatNumber);

        document.querySelectorAll('[data-weevil-render]').forEach(function (mount) {
            var previous = mount.getAttribute('data-weevil-definition') || '';
            var next = String(user.definition || '');
            mount.setAttribute('data-weevil-name', String(user.username || ''));
            if (previous === next) return;

            mount.setAttribute('data-weevil-definition', next);
            try {
                mount.dispatchEvent(new CustomEvent('bw:weevil-definition-change', {
                    bubbles: true,
                    detail: {definition: next, username: user.username}
                }));
            } catch (e) {}
        });

        var definitionField = document.getElementById('current-weevil-def');
        if (definitionField && document.activeElement !== definitionField) {
            definitionField.value = String(user.definition || '');
        }
    }

    function refreshAccountState() {
        if (!document.querySelector('[data-account-live]')) return;
        fetch('/site/account-state.php', {credentials: 'same-origin', cache: 'no-store'})
            .then(function (response) {
                if (!response.ok) throw new Error('account-state');
                return response.json();
            })
            .then(function (data) {
                if (data && data.ok && data.user) applyAccountState(data.user);
            })
            .catch(function () {});
    }

    function applyServerStatus(data) {
        var online = !!(data && data.online);
        document.querySelectorAll('[data-server-status]').forEach(function (node) {
            node.classList.toggle('is-online', online);
            node.classList.toggle('is-offline', !online);
        });
        updateText('[data-server-online]', online ? 'Online' : 'Offline');
        updateText('[data-server-players]', online && data.players !== null && data.players !== undefined ? data.players : '—');
    }

    function refreshServerStatus() {
        if (!document.querySelector('[data-server-status]')) return;
        fetch('/site/server-status.php', {credentials: 'same-origin', cache: 'no-store'})
            .then(function (response) { return response.json(); })
            .then(applyServerStatus)
            .catch(function () { applyServerStatus({online: false, players: null}); });
    }

    if (document.querySelector('[data-account-live]')) {
        refreshAccountState();
        window.setInterval(refreshAccountState, 20000);
    }

    if (document.querySelector('[data-server-status]')) {
        refreshServerStatus();
        window.setInterval(refreshServerStatus, 10000);
    }
}());
