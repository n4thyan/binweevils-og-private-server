<?php
include('../site/bootstrap.php');

$sitePageTitle = 'About';
$siteActive = 'about';
include('../site/header.php');
?>

<section class="bw-panel bw-panel--green bw-content-panel">
    <p class="bw-eyebrow">Preserving the Bin</p>
    <h1 class="bw-section-title">About this Bin Weevils project</h1>
    <p class="bw-section-intro">A fan-made preservation and restoration project built around recovered Bin Weevils game assets, the classic client and a compatible private-server stack.</p>

    <p class="bw-muted">Bin Weevils was a browser-based virtual world where players created a Weevil, explored the Binscape, decorated nests and gardens, played games and met other players. This project exists to keep that classic experience usable rather than replacing it with an unrelated remake.</p>
    <p class="bw-muted">The website is being rebuilt around the original game's visual language and recovered artwork while the working account, progression and game-server contracts remain in place underneath it.</p>

    <div class="bw-button-row" style="margin-top:22px;">
        <a class="bw-button bw-button--green" href="<?php echo $siteLoggedIn ? '/game.php' : '/#login'; ?>"><?php echo $siteLoggedIn ? 'Enter the Bin' : 'Log in &amp; play'; ?></a>
        <a class="bw-button bw-button--blue" href="/community/">Community</a>
    </div>
</section>

<section class="bw-home-grid" aria-label="Project principles">
    <article class="bw-panel bw-feature-card">
        <p class="bw-eyebrow">Classic first</p>
        <h2>The original world</h2>
        <p>The recovered Flash client and official game assets remain the reference point for rooms, UI, characters and behaviour.</p>
    </article>

    <article class="bw-panel bw-panel--orange bw-feature-card">
        <p class="bw-eyebrow">Preservation</p>
        <h2>Keep what works</h2>
        <p>Existing authentication, player data and game-server behaviour are preserved while the public website is modernised around them.</p>
    </article>

    <article class="bw-panel bw-panel--green bw-feature-card">
        <p class="bw-eyebrow">Community</p>
        <h2>Built for players</h2>
        <p>The project is community-run and independent. It is not affiliated with the original Bin Weevils rights holders.</p>
    </article>
</section>

<section class="bw-panel bw-content-panel" style="margin-top:27px;">
    <p class="bw-eyebrow">Project status</p>
    <h2 class="bw-card-title">Old game, rebuilt front door</h2>
    <p class="bw-muted">The redesign only changes the presentation layer where possible. The restored game client, server endpoints and saved player data continue to be treated as the source of truth, with new website features added in controlled pieces.</p>
    <p class="bw-muted">Recovered artwork is reused directly when it is suitable for the web. Game-only assets stay with the client rather than being duplicated or approximated unnecessarily.</p>
</section>

<?php include('../site/footer.php'); ?>
