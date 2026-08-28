import { loadAtlasMap, loadImage } from '/weevil-creator/src/runtime/canvasAtlasLoader.js';
import { getDefObj } from '/weevil-creator/src/runtime/WeevilDef.js';
import { WeevilCanvasRenderer } from '/weevil-creator/src/runtime/WeevilCanvasRenderer.js';

const mounts = Array.from(document.querySelectorAll('[data-weevil-render]'));

if (mounts.length) {
    const creatorBaseUrl = new URL('/weevil-creator/', window.location.origin).href.replace(/\/$/, '');

    try {
        const [atlases, upperLegImage, lowerLegImage, lowerLegStripyImage] = await Promise.all([
            loadAtlasMap(creatorBaseUrl),
            loadImage(`${creatorBaseUrl}/assets/raw/misc/upper_leg.png`),
            loadImage(`${creatorBaseUrl}/assets/raw/misc/lower_leg.png`),
            loadImage(`${creatorBaseUrl}/assets/raw/misc/lower_leg_stripy.png`),
        ]);

        const rawImages = new Map([
            ['upper_leg.png', upperLegImage],
            ['lower_leg.png', lowerLegImage],
            ['lower_leg_stripy.png', lowerLegStripyImage],
        ]);

        mounts.forEach(function (mount) {
            const canvas = document.createElement('canvas');
            canvas.setAttribute('aria-hidden', 'true');
            mount.innerHTML = '';
            mount.appendChild(canvas);

            const renderer = new WeevilCanvasRenderer(canvas, atlases, rawImages);
            renderer.autoRotate = false;

            function renderDefinition(rawDefinition) {
                const defString = /^\d{18}$/.test(String(rawDefinition || '').trim())
                    ? String(rawDefinition).trim()
                    : '401135129001323200';
                const definition = getDefObj(defString);
                const width = Math.max(64, Math.round(mount.clientWidth || 220));
                const height = Math.max(68, Math.round(mount.clientHeight || 220));
                renderer.resize(width, height);
                renderer.setDefinition(structuredClone(definition), {expressionIndex: 0});
                renderer.setView(16, 302);
                renderer.render();
            }

            renderDefinition(mount.getAttribute('data-weevil-definition'));

            mount.addEventListener('bw:weevil-definition-change', function (event) {
                const next = event && event.detail ? event.detail.definition : mount.getAttribute('data-weevil-definition');
                renderDefinition(next);
            });

            if (typeof ResizeObserver !== 'undefined') {
                const observer = new ResizeObserver(function () {
                    renderDefinition(mount.getAttribute('data-weevil-definition'));
                });
                observer.observe(mount);
            }
        });
    }
    catch (error) {
        console.warn('Bin Weevils website renderer assets are not installed yet.', error);
    }
}
