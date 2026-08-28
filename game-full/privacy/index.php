<?php
include('../site/bootstrap.php');
$sitePageTitle = 'Privacy';
$siteActive = '';
include('../site/header.php');
?>

<section class="bw-panel bw-panel--green bw-content-panel">
    <p class="bw-eyebrow">Privacy</p>
    <h1 class="bw-section-title">Privacy &amp; account data</h1>
    <p class="bw-section-intro">A plain-language summary of the data the private server needs in order to run accounts, sessions and the restored game.</p>
</section>

<section class="bw-panel bw-content-panel" style="margin-top:27px;">
    <h2 class="bw-card-title">Account and game data</h2>
    <p class="bw-muted">The service stores the information needed to operate a Bin Weevils account and preserve its game state. That includes your Weevil name, password credential, session/login keys and gameplay data such as level, XP, currencies, inventory and other saved progress.</p>
    <p class="bw-muted">Passwords should be stored as one-way password hashes by the account system. Never share your password or session details with other players.</p>
</section>

<section class="bw-panel bw-panel--orange bw-content-panel" style="margin-top:27px;">
    <h2 class="bw-card-title">Website sessions</h2>
    <p class="bw-muted">The website uses cookies required to keep you signed in and connect the signed-in website account to the restored game launch flow. Optional browser-only preferences, such as remembering your Weevil name or reducing website animation, stay in your browser.</p>
</section>

<section class="bw-panel bw-panel--green bw-content-panel" style="margin-top:27px;">
    <h2 class="bw-card-title">Community chat</h2>
    <p class="bw-muted">The Community page uses xat as a separate third-party chat service. A xat account is not a Bin Weevils account and the private server does not intentionally merge the two identities. xat may use its own cookies and process data under its own policies when its embedded chat is loaded.</p>
</section>

<section class="bw-panel bw-content-panel" style="margin-top:27px;">
    <h2 class="bw-card-title">Security and changes</h2>
    <p class="bw-muted">Account data should only be used for operating, moderating, securing and maintaining the private server. The operator may update this page when website features or external services change.</p>
    <p class="bw-muted">Before any public deployment, the live server configuration and data-retention behaviour should be checked against this page so the published description matches the service that is actually running.</p>
</section>

<?php include('../site/footer.php'); ?>
