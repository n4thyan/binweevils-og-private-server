    </main>

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
        <small><?php echo site_e(isset($siteConfig['build_label']) ? $siteConfig['build_label'] : ''); ?></small>
    </footer>
</div>
<script src="/assets/js/site-redesign.js?v=1"></script>
<script src="/assets/js/site-ads.js?v=1"></script>
<?php if(is_file(dirname(__DIR__) . '/weevil-creator/src/runtime/WeevilDef.js')): ?>
<script type="module" src="/assets/js/site-weevil-renderer.js?v=1"></script>
<?php endif; ?>
</body>
</html>
