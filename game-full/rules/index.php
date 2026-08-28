<?php
include('../site/bootstrap.php');
$sitePageTitle = 'Rules';
$siteActive = '';
include('../site/header.php');
?>

<section class="bw-panel bw-panel--green bw-content-panel">
    <p class="bw-eyebrow">Community</p>
    <h1 class="bw-section-title">Bin Rules</h1>
    <p class="bw-section-intro">Keep the game and xat community friendly, fair and safe for everyone.</p>
</section>

<section class="bw-home-grid" style="margin-top:27px;">
    <article class="bw-panel bw-feature-card"><p class="bw-eyebrow">1</p><h2>Respect other players</h2><p>No harassment, targeted abuse, threats, hate speech or deliberately disruptive behaviour.</p></article>
    <article class="bw-panel bw-panel--orange bw-feature-card"><p class="bw-eyebrow">2</p><h2>No scams or impersonation</h2><p>Do not pretend to be staff, ask for another player's password, or use fake rewards/trades to trick people.</p></article>
    <article class="bw-panel bw-panel--green bw-feature-card"><p class="bw-eyebrow">3</p><h2>Keep accounts secure</h2><p>Never share your Bin Weevils password or session details. xat accounts and Bin Weevils accounts are separate.</p></article>
    <article class="bw-panel bw-panel--green bw-feature-card"><p class="bw-eyebrow">4</p><h2>Play fairly</h2><p>Do not exploit bugs, automation or modified clients to damage the server, duplicate rewards or gain unfair progression.</p></article>
    <article class="bw-panel bw-feature-card"><p class="bw-eyebrow">5</p><h2>Don't spam</h2><p>Avoid flooding chat, repeated advertising, malicious links or anything intended to make the game or community unusable.</p></article>
    <article class="bw-panel bw-panel--orange bw-feature-card"><p class="bw-eyebrow">6</p><h2>Staff decisions</h2><p>Moderation exists to protect the community. Serious or repeated rule-breaking may result in chat or game restrictions.</p></article>
</section>

<section class="bw-panel bw-content-panel" style="margin-top:27px;">
    <p class="bw-eyebrow">Remember</p>
    <h2 class="bw-card-title">Use common sense</h2>
    <p class="bw-muted">These rules apply to the private server and its website community spaces. Nest News and the in-game systems remain the main place for game-specific notices.</p>
    <div class="bw-button-row">
        <a class="bw-button bw-button--blue bw-button--small" href="/community/">Community</a>
        <a class="bw-button bw-button--green bw-button--small" href="<?php echo $siteLoggedIn ? '/game.php' : '/#login'; ?>"><?php echo $siteLoggedIn ? 'Play now' : 'Log in'; ?></a>
    </div>
</section>

<?php include('../site/footer.php'); ?>
