<?php
include('../site/bootstrap.php');

if(!$siteLoggedIn || !is_array($siteUser)) {
    header('Location: /#login');
    exit;
}

$profileNameColor = site_cosmetic_equipped_value($siteCosmetics, 'username_color', '#075984');
$profileTitle = site_cosmetic_equipped_value($siteCosmetics, 'title', '');
$profileBackground = site_cosmetic_equipped_value($siteCosmetics, 'profile_background', '');

$sitePageTitle = 'My Weevil';
$siteActive = 'settings';
include('../site/header.php');
?>

<section>
    <p class="bw-eyebrow">Account</p>
    <h1 class="bw-section-title">My Weevil</h1>
    <p class="bw-section-intro">Your progression, customisation and account settings in one place. Lifetime XP is permanent; Banked XP is the spendable/progression value used by XP Rewards.</p>
</section>

<div class="bw-settings-grid">
    <aside class="bw-panel bw-panel--green bw-profile-card">
        <div class="bw-profile-render-large" data-weevil-render data-weevil-definition="<?php echo site_e($siteUser['def']); ?>" data-weevil-name="<?php echo site_e($siteUser['username']); ?>"<?php echo $profileBackground !== '' ? ' style="background-image:url(\'' . site_e($profileBackground) . '\');background-size:cover;background-position:center;"' : ''; ?>>
            <div class="bw-render-pending">Weevil</div>
        </div>
        <h2 class="bw-card-title" data-account-stat="username" style="color:<?php echo site_e($profileNameColor); ?>"><?php echo site_e($siteUser['username']); ?></h2>
        <?php if($profileTitle !== ''): ?><span class="bw-badge" style="margin-right:6px;"><?php echo site_e($profileTitle); ?></span><?php endif; ?>
        <span class="bw-badge">Prestige <span data-account-stat="prestige"><?php echo (int)$siteUser['prestige_count']; ?></span></span>
        <div class="bw-stat-grid">
            <div class="bw-stat"><span>Level</span><strong data-account-stat="level"><?php echo (int)$siteUser['level']; ?></strong></div>
            <div class="bw-stat"><span>Prestige</span><strong data-account-stat="prestige"><?php echo (int)$siteUser['prestige_count']; ?></strong></div>
            <div class="bw-stat"><span>Lifetime XP</span><strong data-account-stat="lifetime-xp"><?php echo site_int($siteUser['xp']); ?></strong></div>
            <div class="bw-stat"><span>Banked XP</span><strong data-account-stat="banked-xp"><?php echo site_int($siteUser['xp1']); ?></strong></div>
            <div class="bw-stat"><span>Mulch</span><strong data-account-stat="mulch"><?php echo site_int($siteUser['mulch']); ?></strong></div>
            <div class="bw-stat"><span>Dosh</span><strong data-account-stat="dosh"><?php echo site_int($siteUser['dosh']); ?></strong></div>
        </div>
        <div class="bw-button-row">
            <a class="bw-button bw-button--green bw-button--small" href="/game.php">Play now</a>
            <a class="bw-button bw-button--blue bw-button--small" href="/settings/xp-rewards.php">XP Rewards</a>
        </div>
    </aside>

    <div class="bw-settings-stack">
        <section class="bw-panel bw-content-panel">
            <p class="bw-eyebrow">Progression</p>
            <h2 class="bw-card-title">XP &amp; Prestige</h2>
            <div class="bw-stat-grid">
                <div class="bw-stat"><span>Banked XP</span><strong data-account-stat="banked-xp"><?php echo site_int($siteUser['xp1']); ?></strong></div>
                <div class="bw-stat"><span>Next threshold</span><strong data-account-stat="next-xp"><?php echo site_int($siteUser['xp2']); ?></strong></div>
            </div>
            <p class="bw-muted">Lifetime XP never decreases. XP reward purchases use Banked XP only, so permanent progress and the future lifetime-XP leaderboard stay intact.</p>
        </section>

        <section class="bw-panel bw-panel--orange bw-content-panel" id="xp-rewards">
            <p class="bw-eyebrow">Customisation</p>
            <h2 class="bw-card-title">XP Rewards</h2>
            <p class="bw-muted">Unlock permanent website cosmetics with Banked XP, then equip or swap anything you own for free. The current catalogue includes name colours, titles and recovered official profile backgrounds.</p>
            <a class="bw-button bw-button--small" href="/settings/xp-rewards.php">Open XP Rewards</a>
        </section>

        <section class="bw-panel bw-content-panel">
            <p class="bw-eyebrow">Your look</p>
            <h2 class="bw-card-title">Weevil Definition</h2>
            <p class="bw-muted">This is read directly from your OG account. Website renderer mounts follow the saved definition whenever the account state changes.</p>
            <div class="bw-field">
                <label for="current-weevil-def">Current definition</label>
                <input class="bw-input" id="current-weevil-def" type="text" value="<?php echo site_e($siteUser['def']); ?>" readonly>
            </div>
            <button class="bw-button bw-button--blue bw-button--small" type="button" data-copy-definition>Copy definition</button>
        </section>

        <section class="bw-panel bw-content-panel" id="site-preferences">
            <p class="bw-eyebrow">Quality of life</p>
            <h2 class="bw-card-title">Website preferences</h2>
            <p class="bw-muted">These settings are stored only in this browser and do not alter your game account.</p>
            <div class="bw-pref-list">
                <label class="bw-pref-row">
                    <input type="checkbox" data-site-pref="reduce-motion">
                    <span><strong>Reduce website animations</strong><span>Stops the scrolling bulletin and reduces interface motion and transitions.</span></span>
                </label>
                <label class="bw-pref-row">
                    <input type="checkbox" data-site-pref="compact-layout">
                    <span><strong>Compact website layout</strong><span>Tightens spacing while keeping the same Bin Weevils panels and artwork.</span></span>
                </label>
            </div>
        </section>

        <section class="bw-panel bw-panel--green bw-content-panel" id="security">
            <p class="bw-eyebrow">Security</p>
            <h2 class="bw-card-title">Change password</h2>
            <p class="bw-muted">Changing your password rotates both your website session key and your game login key. Your current browser remains signed in with the new session.</p>

            <form id="password-change-form" action="/settings/change-password.php" method="post" autocomplete="off">
                <input type="hidden" name="csrf" value="<?php echo site_e(site_csrf_token()); ?>">
                <div class="bw-field">
                    <label for="current-password">Current password</label>
                    <input class="bw-input" id="current-password" name="current_password" type="password" autocomplete="current-password" required>
                </div>
                <div class="bw-field">
                    <label for="new-password">New password</label>
                    <input class="bw-input" id="new-password" name="new_password" type="password" minlength="8" maxlength="72" autocomplete="new-password" required>
                </div>
                <div class="bw-field">
                    <label for="confirm-password">Confirm new password</label>
                    <input class="bw-input" id="confirm-password" name="confirm_password" type="password" minlength="8" maxlength="72" autocomplete="new-password" required>
                </div>
                <button class="bw-button bw-button--green bw-button--small" type="submit">Change password</button>
                <p class="bw-form-note" id="password-change-status" role="status" aria-live="polite"></p>
            </form>
        </section>
    </div>
</div>

<script>
(function () {
    var copy = document.querySelector('[data-copy-definition]');
    var field = document.getElementById('current-weevil-def');
    if (copy && field) {
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
    }

    var form = document.getElementById('password-change-form');
    var status = document.getElementById('password-change-status');
    if (!form || !status || !window.fetch) return;

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        var button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        status.textContent = 'Updating password…';

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            credentials: 'same-origin',
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        }).then(function (response) {
            return response.json().catch(function () {
                return {ok: false, message: 'Unexpected server response.'};
            });
        }).then(function (data) {
            status.textContent = data.message || (data.ok ? 'Password changed.' : 'Password change failed.');
            if (data.ok) {
                form.reset();
                setTimeout(function () { window.location.reload(); }, 900);
            }
        }).catch(function () {
            status.textContent = 'Could not contact the server.';
        }).finally(function () {
            button.disabled = false;
        });
    });
}());
</script>

<?php include('../site/footer.php'); ?>
