    </main>

    <?php if(site_has_ads('site-side')): ?>
    <div class="bw-side-ads" aria-hidden="true">
        <aside class="bw-side-ad bw-side-ad--left"><?php site_ad_slot('site-side', 'skyscraper'); ?></aside>
        <aside class="bw-side-ad bw-side-ad--right"><?php site_ad_slot('site-side', 'skyscraper'); ?></aside>
    </div>
    <?php endif; ?>

    <footer class="bw-footer">
        <div class="bw-footer-weevil">
            <img src="/assets/images/weevil.png" alt="" aria-hidden="true">
        </div>
        <nav aria-label="Footer links">
            <a href="/download/">Download</a>
            <a href="/rules/">Rules</a>
            <a href="/help/">Help</a>
            <a href="/community/">Community</a>
            <a href="/credits/">Credits</a>
            <a href="/privacy/">Privacy</a>
        </nav>
        <p>Fan-made Bin Weevils preservation project. Not affiliated with the original rights holders.</p>
    </footer>
</div>
<script src="/assets/js/site-redesign.js?v=2"></script>
<script src="/assets/js/site-ads.js?v=2"></script>
<?php if(is_file(dirname(__DIR__) . '/weevil-creator/src/runtime/WeevilDef.js')): ?>
<script type="module" src="/assets/js/site-weevil-renderer.js?v=2"></script>
<?php endif; ?>
</body>
</html>
