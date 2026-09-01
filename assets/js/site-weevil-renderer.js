import { loadAtlasMap, loadImage } from '/weevil-creator/src/runtime/canvasAtlasLoader.js';
import { getDefObj } from '/weevil-creator/src/runtime/WeevilDef.js';
import { WeevilCanvasRenderer } from '/weevil-creator/src/runtime/WeevilCanvasRenderer.js';

(function () {
    var mounts = Array.prototype.slice.call(document.querySelectorAll('[data-weevil-render]'));
    if (!mounts.length) return;

    var creatorBaseUrl = new URL('/weevil-creator/', window.location.origin).href.replace(/\/$/, '');
    var websiteAtlasKeys = [
        'body_spheroid', 'body_cone', 'body_cone_narrow_inv', 'body_cuboid',
        'head_spheroid', 'head_cone', 'head_cone_inv', 'head_cuboid',
        'misc', 'misc_Prob1_mc', 'mouth_Mouth2_mc',
        'eyes', 'eyes_Eye_white1_mc', 'eyes_Eye_iris1_mc', 'eyes_Eye_iris2_mc'
    ];

    function alphaBounds(canvas) {
        var width = canvas.width;
        var height = canvas.height;
        var pixels = canvas.getContext('2d').getImageData(0, 0, width, height).data;
        var minX = width;
        var minY = height;
        var maxX = -1;
        var maxY = -1;

        for (var y = 0; y < height; y++) {
            for (var x = 0; x < width; x++) {
                if (pixels[(y * width + x) * 4 + 3] === 0) continue;
                if (x < minX) minX = x;
                if (x > maxX) maxX = x;
                if (y < minY) minY = y;
                if (y > maxY) maxY = y;
            }
        }

        return maxX < minX ? null : {
            minX: minX,
            minY: minY,
            maxX: maxX,
            maxY: maxY,
            width: maxX - minX + 1,
            height: maxY - minY + 1
        };
    }

    Promise.all([
        loadAtlasMap(creatorBaseUrl, 'assets/atlases/manifest.json', websiteAtlasKeys),
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
            if (mount.getAttribute('data-weevil-render-ready') === '1') return;
            mount.setAttribute('data-weevil-render-ready', '1');

            var canvas = document.createElement('canvas');
            canvas.setAttribute('aria-hidden', 'true');
            mount.innerHTML = '';
            mount.appendChild(canvas);

            var cropMode = mount.getAttribute('data-weevil-crop') === 'head' ? 'head' : 'full';
            var renderCanvas = cropMode === 'head' ? document.createElement('canvas') : canvas;
            var renderer = new WeevilCanvasRenderer(renderCanvas, atlases, rawImages);
            renderer.autoRotate = false;

            function renderDefinition(rawDefinition) {
                var source = String(rawDefinition || '').trim();
                var defString = /^\d{18}$/.test(source) ? source : '401135129001323200';
                var definition = getDefObj(defString);

                // Website previews are body-only. This wrapper deliberately omits
                // equipped Flash hats without changing the stored game definition.
                definition.hat = 0;
                definition.hatc = 0;
                definition.htc = 0;

                if (cropMode === 'head') {
                    // Render at a stable full-body resolution first. Rendering the
                    // fixed-size Flash geometry directly into the 42px header box
                    // clipped the head before CSS could zoom it, leaving a blank box.
                    renderer.resize(360, 360);
                }
                else {
                    renderer.resize(
                        Math.max(64, Math.round(mount.clientWidth || 220)),
                        Math.max(68, Math.round(mount.clientHeight || 220))
                    );
                }

                renderer.setDefinition(Object.assign({}, definition), {expressionIndex: 0});
                renderer.setView(16, 0);
                renderer.render();

                if (cropMode === 'head') {
                    var targetWidth = Math.max(1, Math.round(mount.clientWidth || 46));
                    var targetHeight = Math.max(1, Math.round(mount.clientHeight || 46));
                    canvas.width = targetWidth;
                    canvas.height = targetHeight;

                    var bounds = alphaBounds(renderCanvas);
                    if (!bounds) return;

                    // The face occupies the upper portion of the full Weevil bounds.
                    // Use a square crop centred on that upper portion, with a small
                    // amount of breathing room for eyes/proboscis.
                    var cropSize = Math.max(1, Math.min(bounds.width * 1.16, bounds.height * 0.58));
                    var sourceX = Math.max(0, bounds.minX + bounds.width / 2 - cropSize / 2);
                    var sourceY = Math.max(0, bounds.minY - cropSize * 0.04);
                    cropSize = Math.min(cropSize, renderCanvas.width - sourceX, renderCanvas.height - sourceY);

                    var context = canvas.getContext('2d');
                    context.clearRect(0, 0, targetWidth, targetHeight);
                    context.drawImage(
                        renderCanvas,
                        sourceX,
                        sourceY,
                        cropSize,
                        cropSize,
                        1,
                        1,
                        Math.max(1, targetWidth - 2),
                        Math.max(1, targetHeight - 2)
                    );
                }
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
