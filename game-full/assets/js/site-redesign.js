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
}());
