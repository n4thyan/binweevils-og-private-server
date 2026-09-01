(function () {
    'use strict';

    document.querySelectorAll('[data-ad-rotation]').forEach(function (slot) {
        var creatives = Array.prototype.slice.call(slot.querySelectorAll('[data-ad-creative]'));
        if (!creatives.length) return;

        var index = Math.max(0, creatives.findIndex(function (node) { return node.classList.contains('is-active'); }));
        var timer = null;

        function stopCurrent(node) {
            var video = node ? node.querySelector('[data-ad-video]') : null;
            if (video) {
                try { video.pause(); } catch (e) {}
            }
            if (timer) {
                window.clearTimeout(timer);
                timer = null;
            }
        }

        function show(nextIndex) {
            stopCurrent(creatives[index]);
            index = ((nextIndex % creatives.length) + creatives.length) % creatives.length;

            creatives.forEach(function (node, i) {
                node.classList.toggle('is-active', i === index);
            });

            var current = creatives[index];
            var video = current.querySelector('[data-ad-video]');
            if (video) {
                video.currentTime = 0;
                var promise = video.play();
                if (promise && typeof promise.catch === 'function') promise.catch(function () {});
                video.onended = function () { show(index + 1); };
                return;
            }

            var duration = Number(current.getAttribute('data-ad-duration')) || 12;
            timer = window.setTimeout(function () { show(index + 1); }, Math.max(4, duration) * 1000);
        }

        if (creatives.length === 1) {
            var onlyVideo = creatives[0].querySelector('[data-ad-video]');
            if (onlyVideo) {
                onlyVideo.loop = true;
                var singlePromise = onlyVideo.play();
                if (singlePromise && typeof singlePromise.catch === 'function') singlePromise.catch(function () {});
            }
            return;
        }

        show(index);
    });
}());
