<?php
include('../site/bootstrap.php');

$sitePageTitle = 'Download';
$siteActive = 'download';
$downloads = isset($siteConfig['client_downloads']) && is_array($siteConfig['client_downloads']) ? $siteConfig['client_downloads'] : [];
$windows = isset($downloads['windows']) && is_array($downloads['windows']) ? $downloads['windows'] : [];
$windowsUrl = isset($windows['url']) ? trim((string)$windows['url']) : '';
$windowsVersion = isset($windows['version']) ? trim((string)$windows['version']) : '';
$windowsSize = isset($windows['size']) ? trim((string)$windows['size']) : '';
$sourceUrl = isset($downloads['source_url']) ? trim((string)$downloads['source_url']) : '';

include('../site/header.php');
?>

<section class="bw-panel bw-panel--green bw-content-panel">
    <p class="bw-eyebrow">Desktop client</p>
    <h1 class="bw-section-title">Download Bin Weevils</h1>
    <p class="bw-section-intro">Use the desktop client to play Bin Weevils with its built-in Flash player. Your Weevil account and progression stay on the game servers — reinstalling the client never creates a new account.</p>
    <div class="bw-button-row">
        <?php if($windowsUrl !== ''): ?>
            <a class="bw-button bw-button--green" href="<?php echo site_e($windowsUrl); ?>" rel="nofollow">Download for Windows</a>
        <?php else: ?>
            <span class="bw-button bw-button--green" aria-disabled="true" style="opacity:.58;cursor:not-allowed;">Windows build not published yet</span>
        <?php endif; ?>
        <a class="bw-button bw-button--blue" href="/help/#playing">Installation help</a>
    </div>
</section>

<section class="bw-tips-strip" aria-label="Client downloads">
    <div class="bw-tip">
        <p class="bw-eyebrow">Primary client</p>
        <h3 class="bw-card-title">Windows</h3>
        <p>The Windows Electron client is the main desktop launcher used for the restored OG game.</p>
        <?php if($windowsVersion !== ''): ?><p class="bw-muted" style="min-height:0;margin-top:4px;"><strong>Version:</strong> <?php echo site_e($windowsVersion); ?><?php echo $windowsSize !== '' ? ' · ' . site_e($windowsSize) : ''; ?></p><?php endif; ?>
        <?php if($windowsUrl !== ''): ?>
            <a class="bw-button bw-button--green bw-button--small" href="<?php echo site_e($windowsUrl); ?>" rel="nofollow">Download Windows client</a>
        <?php else: ?>
            <span class="bw-badge">Installer publishing pending</span>
        <?php endif; ?>
    </div>

    <div class="bw-tip bw-tip--accent">
        <p class="bw-eyebrow">Other platforms</p>
        <h3 class="bw-card-title">macOS &amp; Linux</h3>
        <p>The project contains legacy PepperFlash support for other desktop platforms, but public packaged builds should only be offered after they are tested end-to-end.</p>
        <span class="bw-badge">No public build yet</span>
    </div>

    <div class="bw-tip">
        <p class="bw-eyebrow">Developers</p>
        <h3 class="bw-card-title">Client source</h3>
        <p>The Electron launcher source is preserved in the project repository for auditing and development.</p>
        <?php if($sourceUrl !== ''): ?>
            <a class="bw-button bw-button--blue bw-button--small" href="<?php echo site_e($sourceUrl); ?>" target="_blank" rel="noopener">View client source</a>
        <?php endif; ?>
    </div>
</section>

<section class="bw-panel bw-content-panel" style="margin-top:27px;">
    <p class="bw-eyebrow">Getting started</p>
    <h2 class="bw-card-title">Install and enter the Bin</h2>
    <ol class="bw-steps">
        <li><strong>Download.</strong> Get the current Windows client from this page once a packaged build is published.</li>
        <li><strong>Install.</strong> Run the installer or extracted launcher. The desktop client carries the Flash runtime needed to play Bin Weevils.</li>
        <li><strong>Log in &amp; play.</strong> Use your existing Bin Weevil account. New players can create a Weevil from the website before entering the game.</li>
    </ol>
</section>

<section class="bw-panel bw-content-panel" style="margin-top:27px;">
    <p class="bw-eyebrow">Safety</p>
    <h2 class="bw-card-title">Download only from Bin Weevils</h2>
    <p class="bw-muted">Always get the client from this official site. Windows may warn you about an unsigned community build — only run it once you have downloaded it from here, and never enter your Bin Weevils password into any other launcher.</p>
</section>

<?php include('../site/footer.php'); ?>
