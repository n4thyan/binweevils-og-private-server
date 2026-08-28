<?php
include('../site/bootstrap.php');
$sitePageTitle = 'Community';
$siteActive = 'community';
include('../site/header.php');
?>

<section class="bw-panel bw-content-panel">
    <p class="bw-eyebrow">Community</p>
    <h1 class="bw-section-title">xat Chat</h1>
    <p class="bw-section-intro">The community room lives here on the website, keeping the same old-school web-chat feel as the era Bin Weevils came from. Bin Weevils accounts and xat accounts stay separate.</p>

    <?php if(!empty($siteConfig['xat_embed_url'])): ?>
        <iframe
            class="bw-community-frame"
            src="<?php echo site_e($siteConfig['xat_embed_url']); ?>"
            title="Bin Weevils xat community chat"
            allow="clipboard-read; clipboard-write"
            loading="lazy"></iframe>
    <?php else: ?>
        <div class="bw-empty-chat">
            <div>
                <strong>xat room ready to plug in</strong>
                <p>The page and responsive frame are finished. Once the final xat group/embed URL is chosen, it only needs to be placed in <code>site/config.php</code>.</p>
            </div>
        </div>
    <?php endif; ?>
</section>

<section class="bw-home-grid">
    <article class="bw-panel bw-panel--green bw-feature-card">
        <p class="bw-eyebrow">Keep it friendly</p>
        <h2>Chat rules</h2>
        <p>The same basic community rules apply here as they do around the game. No staff impersonation, harassment, scams or disruptive spam.</p>
        <a class="bw-button bw-button--green bw-button--small" href="/rules/">Read the rules</a>
    </article>
    <article class="bw-panel bw-feature-card">
        <p class="bw-eyebrow">In-game</p>
        <h2>Nest News</h2>
        <p>Long-form updates and proper Bin news remain inside Nest News rather than being duplicated into a website blog.</p>
        <a class="bw-button bw-button--blue bw-button--small" href="<?php echo $siteLoggedIn ? '/game.php' : '/#login'; ?>">Go to the game</a>
    </article>
    <article class="bw-panel bw-panel--orange bw-feature-card">
        <p class="bw-eyebrow">Account safety</p>
        <h2>Separate logins</h2>
        <p>Your xat login is not your Bin Weevils login. Never give anyone your Bin Weevils password in chat, including people claiming to be staff.</p>
    </article>
</section>

<?php include('../site/footer.php'); ?>
