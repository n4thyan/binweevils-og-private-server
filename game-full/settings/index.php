<?php
include('../site/bootstrap.php');

if(!$siteLoggedIn || !is_array($siteUser)) {
    header('Location: /#login');
    exit;
}

$sitePageTitle = 'My Weevil';
$siteActive = 'settings';
include('../site/header.php');
?>

<section>
    <p class="bw-eyebrow">Account</p>
    <h1 class="bw-section-title">My Weevil</h1>
    <p class="bw-section-intro">Your progression, customisation and account settings in one place. Lifetime XP is permanent; Banked XP is the spendable/progression value used by the future XP rewards catalogue.</p>
</section>

<div class="bw-settings-grid">
    <aside class="bw-panel bw-panel--green bw-profile-card">
        <div class="bw-profile-render-large" data-weevil-render data-weevil-definition="<?php echo site_e($siteUser['def']); ?>" data-weevil-name="<?php echo site_e($siteUser['username']); ?>">
            <div class="bw-render-pending">Verified Weevil renderer slot</div>
        </div>
        <h2 class="bw-card-title"><?php echo site_e($siteUser['username']); ?></h2>
        <span class="bw-badge">Prestige <?php echo (int)$siteUser['prestige_count']; ?></span>
        <div class="bw-stat-grid">
            <div class="bw-stat"><span>Level</span><strong><?php echo (int)$siteUser['level']; ?></strong></div>
            <div class="bw-stat"><span>Prestige</span><strong><?php echo (int)$siteUser['prestige_count']; ?></strong></div>
            <div class="bw-stat"><span>Lifetime XP</span><strong><?php echo site_int($siteUser['xp']); ?></strong></div>
            <div class="bw-stat"><span>Banked XP</span><strong><?php echo site_int($siteUser['xp1']); ?></strong></div>
            <div class="bw-stat"><span>Mulch</span><strong><?php echo site_int($siteUser['mulch']); ?></strong></div>
            <div class="bw-stat"><span>Dosh</span><strong><?php echo site_int($siteUser['dosh']); ?></strong></div>
        </div>
        <a class="bw-button bw-button--green bw-button--small" href="/game.php">Play now</a>
    </aside>

    <div class="bw-settings-stack">
        <section class="bw-panel bw-content-panel">
            <p class="bw-eyebrow">Progression</p>
            <h2 class="bw-card-title">XP &amp; Prestige</h2>
            <div class="bw-stat-grid">
                <div class="bw-stat"><span>Banked XP</span><strong><?php echo site_int($siteUser['xp1']); ?></strong></div>
                <div class="bw-stat"><span>Next threshold</span><strong><?php echo site_int($siteUser['xp2']); ?></strong></div>
            </div>
            <p class="bw-muted">Lifetime XP never decreases. XP rewards will spend Banked XP only, so leaderboard/lifetime progress remains intact.</p>
        </section>

        <section class="bw-panel bw-panel--orange bw-content-panel" id="xp-rewards">
            <p class="bw-eyebrow">Customisation</p>
            <h2 class="bw-card-title">XP Rewards</h2>
            <p class="bw-muted">This is the home for permanent cosmetic unlocks: username colours, chat colours, level-star colours, titles, badges, backgrounds, saved presets and — if the existing hat palette proves isolated — hat colours.</p>
            <p class="bw-muted"><strong>Implementation rule:</strong> unlock once with Banked XP, then equip or swap freely without paying again.</p>
        </section>

        <section class="bw-panel bw-content-panel">
            <p class="bw-eyebrow">Your look</p>
            <h2 class="bw-card-title">Weevil Definition</h2>
            <p class="bw-muted">Your saved definition is already available to this page. The apply tool will use the server's existing definition validator and a preview step before anything is written.</p>
            <div class="bw-field">
                <label for="current-weevil-def">Current definition</label>
                <input class="bw-input" id="current-weevil-def" type="text" value="<?php echo site_e($siteUser['def']); ?>" readonly>
            </div>
            <button class="bw-button bw-button--blue bw-button--small" type="button" data-copy-definition>Copy definition</button>
        </section>

        <section class="bw-panel bw-panel--green bw-content-panel">
            <p class="bw-eyebrow">Account</p>
            <h2 class="bw-card-title">Security &amp; account details</h2>
            <p class="bw-muted">Password changes, session management and the carefully-audited username-change flow will live here. Username changing will not be enabled until every username-keyed database reference has been audited.</p>
            <div class="bw-button-row">
                <span class="bw-badge">Password change: wiring next</span>
                <span class="bw-badge">Username change: audit required</span>
            </div>
        </section>
    </div>
</div>

<script>
(function () {
    var copy = document.querySelector('[data-copy-definition]');
    var field = document.getElementById('current-weevil-def');
    if (!copy || !field) return;
    copy.addEventListener('click', function () {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(field.value);
            copy.textContent = 'Copied!';
            setTimeout(function () { copy.textContent = 'Copy definition'; }, 1400);
        } else {
            field.select();
            document.execCommand('copy');
        }
    });
}());
</script>

<?php include('../site/footer.php'); ?>
