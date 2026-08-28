import { loadAtlasMap, loadImage } from '/weevil-creator/src/runtime/canvasAtlasLoader.js';
import { getDefObj } from '/weevil-creator/src/runtime/WeevilDef.js';
import { WeevilCanvasRenderer } from '/weevil-creator/src/runtime/WeevilCanvasRenderer.js';

(function () {
    var mounts = Array.prototype.slice.call(document.querySelectorAll('[data-weevil-render]'));
    if (!mounts.length) return;

    var creatorBaseUrl = new URL('/weevil-creator/', window.location.origin).href.replace(/\/$/, '');

    Promise.all([
        loadAtlasMap(creatorBaseUrl),
        loadImage(creatorBaseUrl + '/assets/raw/misc/upper_leg.png'),
        loadImage(creatorBaseUrl + '/assets/raw/misc/lower_leg.png'),
        loadImage(creatorBaseUrl + '/assets/raw/misc/lower_leg_stripy.png')
    ]).then(function (loaded) {
        var atlases = loaded[0];
        var rawImages = new Map([
            ['upper_leg.png', loaded[1]],
            ['lower_leg.png', loaded[2]],
            ['lower_leg_stripy.png', loaded[3]]
        ]);

        mounts.forEach(function (mount) {
            var canvas = document.createElement('canvas');
            canvas.setAttribute('aria-hidden', 'true');
            mount.innerHTML = '';
            mount.appendChild(canvas);

            var renderer = new WeevilCanvasRenderer(canvas, atlases, rawImages);
            renderer.autoRotate = false;

            function renderDefinition(rawDefinition) {
                var source = String(rawDefinition || '').trim();
                var defString = /^\d{18}$/.test(source) ? source : '401135129001323200';
                var definition = getDefObj(defString);

                // Website previews are BODY-ONLY. The shared HTML5 renderer reads
                // hat/headwear fields from the definition; zero them here so the
                // website never draws an equipped hat. This only affects the
                // website wrapper -- the Flash game, Hem's Hat Shop and the game
                // hat inventory are untouched. ht is the def's hat-type digit.
                definition.hat = 0;
                definition.hatc = 0;
                definition.htc = 0;
                definition.ht = 0;
                var width = Math.max(64, Math.round(mount.clientWidth || 220));
                var height = Math.max(68, Math.round(mount.clientHeight || 220));

                renderer.resize(width, height);
                renderer.setDefinition(Object.assign({}, definition), {expressionIndex: 0});
                renderer.setView(16, 302);
                renderer.render();
            }

            renderDefinition(mount.getAttribute('data-weevil-definition'));

            mount.addEventListener('bw:weevil-definition-change', function (event) {
                var next = event && event.detail
                    ? event.detail.definition
                    : mount.getAttribute('data-weevil-definition');
                renderDefinition(next);
            });

            if (typeof ResizeObserver !== 'undefined') {
                var observer = new ResizeObserver(function () {
                    renderDefinition(mount.getAttribute('data-weevil-definition'));
                });
                observer.observe(mount);
            }
            else {
                window.addEventListener('resize', function () {
                    renderDefinition(mount.getAttribute('data-weevil-definition'));
                });
            }
        });
    }).catch(function (error) {
        if (window.console && console.warn) {
            console.warn('Bin Weevils website renderer assets are not installed yet.', error);
        }
    });
}());
