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
    <p class="bw-section-intro">Use the desktop Electron client to run the restored classic Flash game with its bundled PepperFlash player. Your Weevil account and progression remain on the game server — reinstalling the client does not create a new account.</p>
    <div class="bw-button-row">
        <?php if($windowsUrl !== ''): ?>
            <a class="bw-button bw-button--green" href="<?php echo site_e($windowsUrl); ?>" rel="nofollow">Download for Windows</a>
        <?php else: ?>
            <span class="bw-button bw-button--green" aria-disabled="true" style="opacity:.58;cursor:not-allowed;">Windows build not published yet</span>
        <?php endif; ?>
        <a class="bw-button bw-button--blue" href="/help/#playing">Installation help</a>
    </div>
</section>

<section class="bw-home-grid" style="margin-top:27px;" aria-label="Client downloads">
    <article class="bw-panel bw-feature-card" id="windows">
        <p class="bw-eyebrow">Primary client</p>
        <h2>Windows</h2>
        <p>The Windows Electron client is the main desktop launcher used for the restored OG game.</p>
        <?php if($windowsVersion !== ''): ?><p class="bw-muted" style="min-height:0;margin-top:-8px;"><strong>Version:</strong> <?php echo site_e($windowsVersion); ?><?php echo $windowsSize !== '' ? ' · ' . site_e($windowsSize) : ''; ?></p><?php endif; ?>
        <?php if($windowsUrl !== ''): ?>
            <a class="bw-button bw-button--green bw-button--small" href="<?php echo site_e($windowsUrl); ?>" rel="nofollow">Download Windows client</a>
        <?php else: ?>
            <span class="bw-badge">Installer publishing pending</span>
        <?php endif; ?>
    </article>

    <article class="bw-panel bw-panel--orange bw-feature-card">
        <p class="bw-eyebrow">Other platforms</p>
        <h2>macOS &amp; Linux</h2>
        <p>The project contains legacy PepperFlash support for other desktop platforms, but public packaged builds should only be offered after they are tested end-to-end.</p>
        <span class="bw-badge">No public build yet</span>
    </article>

    <article class="bw-panel bw-panel--green bw-feature-card">
        <p class="bw-eyebrow">Developers</p>
        <h2>Client source</h2>
        <p>The Electron launcher source is preserved in the project repository for auditing and development.</p>
        <?php if($sourceUrl !== ''): ?>
            <a class="bw-button bw-button--blue bw-button--small" href="<?php echo site_e($sourceUrl); ?>" target="_blank" rel="noopener">View client source</a>
        <?php endif; ?>
    </article>
</section>

<section class="bw-panel bw-content-panel" style="margin-top:27px;">
    <p class="bw-eyebrow">Getting started</p>
    <h2 class="bw-card-title">Install and enter the Bin</h2>
    <div class="bw-home-grid" style="margin-top:18px;">
        <article class="bw-panel bw-feature-card">
            <p class="bw-eyebrow">1</p>
            <h2>Download</h2>
            <p>Download the current Windows client from this page once a packaged build is published.</p>
        </article>
        <article class="bw-panel bw-panel--green bw-feature-card">
            <p class="bw-eyebrow">2</p>
            <h2>Install</h2>
            <p>Run the installer or extracted launcher. The desktop client carries the Flash runtime needed by the restored game.</p>
        </article>
        <article class="bw-panel bw-panel--orange bw-feature-card">
            <p class="bw-eyebrow">3</p>
            <h2>Log in &amp; play</h2>
            <p>Use your existing Bin Weevil account. New players can create a Weevil from the website before entering the game.</p>
        </article>
    </div>
</section>

<section class="bw-panel bw-panel--orange bw-content-panel" style="margin-top:27px;">
    <p class="bw-eyebrow">Important</p>
    <h2 class="bw-card-title">Use the client linked from this site</h2>
    <p class="bw-muted">Old Bin Weevils Rewritten download links and installers are not part of this project. This page is the canonical download location for the OG private-server client once a release build is published.</p>
    <p class="bw-muted">If Windows shows a reputation warning for an unsigned community build, verify that you downloaded it from this page before running it. Never enter your Bin Weevils password into third-party launchers.</p>
</section>

<?php include('../site/footer.php'); ?>
