<?php
include('../site/bootstrap.php');
include_once('../site/referrals.php');

if(!$siteLoggedIn || !is_array($siteUser)) {
    header('Location: /#login');
    exit;
}

$referralSummary = referral_account_summary((int)$siteUser['id'], (string)$siteUser['username']);
$referralPath = '/register/?ref=' . rawurlencode($referralSummary['code']);

$profileNameColor = site_cosmetic_equipped_value($siteCosmetics, 'username_color', '#075984');
$profileTitle = site_cosmetic_equipped_value($siteCosmetics, 'title', '');
$customNameColourOwned = !empty($siteCosmetics['unlocked']['custom-name-colour']);

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
        <span class="bw-profile-trim" aria-hidden="true"></span>
        <div class="bw-profile-render-large" data-weevil-render data-weevil-definition="<?php echo site_e($siteUser['def']); ?>" data-weevil-name="<?php echo site_e($siteUser['username']); ?>">
            <div class="bw-render-pending">Weevil</div>
        </div>
        <h2 class="bw-card-title" data-account-stat="username" style="color:<?php echo site_e($profileNameColor); ?>"><?php echo site_e($siteUser['username']); ?></h2>
        <?php if($profileTitle !== ''): ?><span class="bw-badge" style="margin-right:6px;"><?php echo site_e($profileTitle); ?></span><?php endif; ?>
        <span class="bw-badge">Prestige <span data-account-stat="prestige"><?php echo (int)$siteUser['prestige_count']; ?></span></span>
        <div class="bw-profile-currency">
            <span class="bw-cur"><img src="/assets/images/mulch.png" alt="Mulch"> <strong data-account-stat="mulch"><?php echo site_int($siteUser['mulch']); ?></strong></span>
            <span class="bw-cur"><img src="/assets/images/dosh.png" alt="Dosh"> <strong data-account-stat="dosh"><?php echo site_int($siteUser['dosh']); ?></strong></span>
        </div>
        <div class="bw-button-row">
            <a class="bw-button bw-button--green bw-button--small" href="/game.php">Play now</a>
        </div>
    </aside>

    <div class="bw-settings-stack">
        <section class="bw-panel bw-content-panel">
            <p class="bw-eyebrow">Progression</p>
            <h2 class="bw-card-title">XP &amp; Prestige</h2>
            <div class="bw-stat-grid">
                <div class="bw-stat"><span>Level</span><strong data-account-stat="level"><?php echo (int)$siteUser['level']; ?></strong></div>
                <div class="bw-stat"><span>Prestige</span><strong data-account-stat="prestige"><?php echo (int)$siteUser['prestige_count']; ?></strong></div>
            </div>
            <div class="bw-xp-block">
                <div class="bw-xp-row"><span>Lifetime XP</span><strong data-account-stat="lifetime-xp"><?php echo site_int($siteUser['xp']); ?></strong></div>
                <div class="bw-xp-row"><span>Banked XP</span><strong data-account-stat="banked-xp"><?php echo site_int($siteUser['xp1']); ?></strong></div>
                <div class="bw-xp-bar" role="progressbar" aria-valuemin="0" aria-valuemax="<?php echo (int)$siteUser['xp2']; ?>" aria-valuenow="<?php echo min((int)$siteUser['xp1'], (int)$siteUser['xp2']); ?>">
                    <span class="bw-xp-bar-fill" style="width:<?php echo ($siteUser['xp2'] > 0 ? min(100, round((int)$siteUser['xp1'] / (int)$siteUser['xp2'] * 100)) : 100); ?>%"></span>
                </div>
                <p class="bw-xp-threshold">Next threshold: <strong data-account-stat="next-xp"><?php echo site_int($siteUser['xp2']); ?> XP</strong></p>
            </div>
            <p class="bw-muted">Lifetime XP never decreases. XP reward purchases use Banked XP only, so permanent progress and the future lifetime-XP leaderboard stay intact.</p>
        </section>

        <section class="bw-panel bw-content-panel bw-reward-module" id="xp-rewards">
            <span class="bw-reward-badge" aria-hidden="true"><img src="/assets/images/dosh.png" alt=""></span>
            <div>
                <p class="bw-eyebrow">Customisation</p>
                <h2 class="bw-card-title">XP Rewards</h2>
                <div class="bw-reward-chips">
                    <span class="bw-chip">Custom username colour</span>
                    <span class="bw-chip">Title</span>
                </div>
                <p class="bw-muted">Spend Banked XP on permanent cosmetics — equip or swap anything you own for free.</p>
                <a class="bw-button bw-button--small" href="/settings/xp-rewards.php">Browse XP Rewards</a>
            </div>
        </section>

        <section class="bw-panel bw-content-panel" id="referrals">
            <p class="bw-eyebrow">Invite a Weevil</p>
            <h2 class="bw-card-title">Referral link</h2>
            <p class="bw-muted">Share this link with a new player. A valid code is locked to their account during registration. When they first enter their Nest Hall, they receive 500 Mulch, 5 Dosh and 25 XP.</p>
            <div class="bw-field">
                <label for="referral-link">Your invite link</label>
                <input class="bw-input" id="referral-link" type="text" value="<?php echo site_e($referralPath); ?>" readonly>
            </div>
            <div class="bw-button-row">
                <button class="bw-button bw-button--blue bw-button--small" type="button" data-copy-referral>Copy invite link</button>
                <span class="bw-badge"><?php echo (int)$referralSummary['count']; ?> referral<?php echo $referralSummary['count'] === 1 ? '' : 's'; ?></span>
                <span class="bw-badge"><?php echo (int)$referralSummary['granted']; ?> reward<?php echo $referralSummary['granted'] === 1 ? '' : 's'; ?> claimed</span>
            </div>
            <?php if(!empty($referralSummary['history'])): ?>
            <div class="bw-pref-list" style="margin-top:16px;">
                <?php foreach($referralSummary['history'] as $referralRow): ?>
                <div class="bw-pref-row">
                    <span><strong><?php echo site_e($referralRow['referred_username']); ?></strong><span>Joined <?php echo date('j M Y', (int)$referralRow['created_at']); ?></span></span>
                    <span class="bw-badge"><?php echo $referralRow['reward_state'] === 'granted' ? 'Reward claimed' : 'Pending first Nest Hall visit'; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="bw-form-note">No referred accounts yet.</p>
            <?php endif; ?>
        </section>

        <?php if($customNameColourOwned): ?>
        <section class="bw-panel bw-content-panel" id="username-colour-settings">
            <p class="bw-eyebrow">Unlocked cosmetic</p>
            <h2 class="bw-card-title">Custom Username Colour</h2>
            <p class="bw-muted">Choose one safe six-digit hex colour for your website username.</p>
            <div class="bw-colour-settings">
                <label class="bw-colour-picker-label" for="username-colour-picker">
                    <span>Colour</span>
                    <input id="username-colour-picker" type="color" value="<?php echo site_e($profileNameColor); ?>" aria-label="Username colour picker">
                </label>
                <div class="bw-field bw-colour-hex-field">
                    <label for="username-colour-hex">Hex colour</label>
                    <input class="bw-input" id="username-colour-hex" type="text" value="<?php echo site_e(strtoupper($profileNameColor)); ?>" maxlength="7" pattern="#[0-9A-Fa-f]{6}" spellcheck="false" autocomplete="off" inputmode="text">
                </div>
                <div class="bw-colour-preview" aria-live="polite">
                    <span>Preview</span>
                    <strong id="username-colour-preview" style="color:<?php echo site_e($profileNameColor); ?>"><?php echo site_e($siteUser['username']); ?></strong>
                </div>
            </div>
            <div class="bw-button-row">
                <button class="bw-button bw-button--green bw-button--small" type="button" id="username-colour-save">Save colour</button>
                <button class="bw-button bw-button--blue bw-button--small" type="button" id="username-colour-reset">Reset to default</button>
            </div>
            <p class="bw-form-note" id="username-colour-status" role="status" aria-live="polite"></p>
        </section>
        <?php endif; ?>

        <details class="bw-panel bw-content-panel bw-disclosure">
            <summary><p class="bw-eyebrow">Your look</p><span>Advanced / Appearance data</span></summary>
            <h2 class="bw-card-title">Weevil Definition</h2>
            <p class="bw-muted">This is read directly from your Bin Weevils account. The website renderer follows your saved appearance whenever your account changes.</p>
            <div class="bw-field">
                <label for="current-weevil-def">Current definition</label>
                <input class="bw-input" id="current-weevil-def" type="text" value="<?php echo site_e($siteUser['def']); ?>" readonly>
            </div>
            <button class="bw-button bw-button--blue bw-button--small" type="button" data-copy-definition>Copy definition</button>
        </details>

        <section class="bw-panel bw-content-panel" id="game-settings">
            <p class="bw-eyebrow">Game</p>
            <h2 class="bw-card-title">Playing the game</h2>
            <p class="bw-muted">The Bin Weevils client runs inside your browser at its original proportions. Open the game and use the ⛶ icon in the top-right corner of the game frame to play in fullscreen.</p>
            <div class="bw-button-row">
                <a class="bw-button bw-button--green bw-button--small" href="/game.php">Play now</a>
                <a class="bw-button bw-button--blue bw-button--small" href="/download/">Desktop client</a>
            </div>
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

    var referralCopy = document.querySelector('[data-copy-referral]');
    var referralField = document.getElementById('referral-link');
    if (referralCopy && referralField) {
        referralCopy.addEventListener('click', function () {
            var fullLink = window.location.origin + referralField.value;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(fullLink);
            } else {
                referralField.value = fullLink;
                referralField.select();
                document.execCommand('copy');
                referralField.value = <?php echo json_encode($referralPath); ?>;
            }
            referralCopy.textContent = 'Invite link copied!';
            setTimeout(function () { referralCopy.textContent = 'Copy invite link'; }, 1400);
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

(function () {
    var picker = document.getElementById('username-colour-picker');
    var hex = document.getElementById('username-colour-hex');
    var preview = document.getElementById('username-colour-preview');
    var save = document.getElementById('username-colour-save');
    var reset = document.getElementById('username-colour-reset');
    var status = document.getElementById('username-colour-status');
    if (!picker || !hex || !preview || !save || !reset || !status || !window.fetch) return;

    var csrf = <?php echo json_encode(site_csrf_token()); ?>;
    var validHex = /^#[0-9A-Fa-f]{6}$/;

    function showColour(value) {
        if (!validHex.test(value)) return false;
        var normalised = value.toUpperCase();
        picker.value = normalised;
        hex.value = normalised;
        preview.style.color = normalised;
        status.textContent = '';
        return true;
    }

    function post(data) {
        var body = new FormData();
        Object.keys(data).forEach(function (key) { body.append(key, data[key]); });
        body.append('csrf', csrf);
        return fetch('/settings/xp-reward-action.php', {
            method: 'POST',
            body: body,
            credentials: 'same-origin',
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        }).then(function (response) {
            return response.json().catch(function () { return {ok:false, message:'Unexpected server response.'}; });
        });
    }

    picker.addEventListener('input', function () { showColour(picker.value); });
    hex.addEventListener('input', function () {
        var value = hex.value.trim();
        if (validHex.test(value)) {
            picker.value = value;
            preview.style.color = value;
            status.textContent = '';
        }
    });

    save.addEventListener('click', function () {
        var value = hex.value.trim();
        if (!showColour(value)) {
            status.textContent = 'Enter a valid colour in the form #RRGGBB.';
            return;
        }
        save.disabled = true;
        status.textContent = 'Saving colour…';
        post({action:'equip', reward_key:'custom-name-colour', colour_hex:value}).then(function (data) {
            status.textContent = data.message || (data.ok ? 'Colour saved.' : 'Colour could not be saved.');
            if (data.ok) window.setTimeout(function () { window.location.reload(); }, 400);
        }).catch(function () {
            status.textContent = 'Could not contact the server.';
        }).finally(function () { save.disabled = false; });
    });

    reset.addEventListener('click', function () {
        reset.disabled = true;
        status.textContent = 'Resetting colour…';
        post({action:'unequip', slot:'username_color'}).then(function (data) {
            status.textContent = data.message || (data.ok ? 'Colour reset.' : 'Colour could not be reset.');
            if (data.ok) window.setTimeout(function () { window.location.reload(); }, 400);
        }).catch(function () {
            status.textContent = 'Could not contact the server.';
        }).finally(function () { reset.disabled = false; });
    });
}());
</script>

<?php include('../site/footer.php'); ?>
