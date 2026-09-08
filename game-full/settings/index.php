<?php
include('../site/bootstrap.php');
include_once('../site/referrals.php');
include_once('../site/weevil-appearance.php');

if(!$siteLoggedIn || !is_array($siteUser)) {
    header('Location: /#login');
    exit;
}

$referralSummary = referral_account_summary((int)$siteUser['id'], (string)$siteUser['username']);
$referralPath = '/register/?ref=' . rawurlencode($referralSummary['code']);

$profileNameColor = site_cosmetic_equipped_value($siteCosmetics, 'username_color', '#075984');
$profileTitle = site_cosmetic_equipped_value($siteCosmetics, 'title', '');
$customNameColourOwned = !empty($siteCosmetics['unlocked']['custom-name-colour']);
$advancedAppearanceEligible = weevil_advanced_update_allowed($siteUser['prestige_count']);
$appearanceDefinition = weevil_parse_definition((string)$siteUser['def']);
if($appearanceDefinition === null) {
    $appearanceDefinition = weevil_parse_definition('401135129001323200');
}
$headTypes = [1 => 'Spheroid', 2 => 'Cone', 3 => 'Inverted cone', 4 => 'Cuboid'];
$bodyTypes = [1 => 'Spheroid', 2 => 'Cone', 3 => 'Narrow inverted cone', 4 => 'Cuboid'];
$eyeTypes = [1 => 'Standard', 2 => 'Wide', 3 => 'Raised', 4 => 'High', 5 => 'Outer', 6 => 'Far outer'];
$antennaTypes = weevil_antenna_types();
$legTypes = weevil_leg_types();

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

        <section class="bw-panel bw-content-panel" id="advanced-weevil-appearance">
            <p class="bw-eyebrow">Prestige perk</p>
            <h2 class="bw-card-title">Advanced Weevil Appearance</h2>
            <?php if(!$advancedAppearanceEligible): ?>
                <p class="bw-muted">Reach Prestige 1 to change your full definition, choose any RGB colour and edit individual body parts here. The original Dosh's Palace editor remains available normally.</p>
                <div class="bw-field">
                    <label for="current-weevil-def">Current definition</label>
                    <input class="bw-input" id="current-weevil-def" type="text" value="<?php echo site_e($siteUser['def']); ?>" readonly>
                </div>
                <button class="bw-button bw-button--blue bw-button--small" type="button" data-copy-definition>Copy Def</button>
            <?php else: ?>
                <p class="bw-muted">Edit native Weevil parts and exact RGB colours for free. Changes are stored on your account and refresh in-game on the next room change.</p>
                <form id="advanced-weevil-form" action="/settings/weevil-appearance-action.php" method="post">
                    <input type="hidden" name="csrf" value="<?php echo site_e(site_csrf_token()); ?>">
                    <input type="hidden" name="mode" value="parts">
                    <div class="bw-field">
                        <label for="current-weevil-def">Current definition</label>
                        <input class="bw-input" id="current-weevil-def" type="text" value="<?php echo site_e($siteUser['def']); ?>" readonly>
                    </div>
                    <div class="bw-button-row bw-definition-actions">
                        <button class="bw-button bw-button--blue bw-button--small" type="button" data-copy-definition>Copy Def</button>
                        <button class="bw-button bw-button--blue bw-button--small" type="button" id="change-def-toggle" aria-expanded="false" aria-controls="change-def-panel">Change Def</button>
                        <button class="bw-button bw-button--green bw-button--small" type="submit">Apply / Save</button>
                    </div>
                    <div id="change-def-panel" class="bw-change-def-panel" hidden>
                        <div class="bw-field">
                            <label for="pasted-weevil-def">Paste full Weevil definition</label>
                            <textarea class="bw-input" id="pasted-weevil-def" rows="3" maxlength="255" spellcheck="false"></textarea>
                        </div>
                        <button class="bw-button bw-button--green bw-button--small" type="button" id="change-def-apply">Validate &amp; Change Def</button>
                    </div>

                    <h3 class="bw-settings-subtitle">Body parts</h3>
                    <div class="bw-appearance-grid">
                        <div class="bw-field"><label for="head-type">Head shape</label><select class="bw-input" id="head-type" name="head_type"><?php foreach($headTypes as $id => $label): ?><option value="<?php echo $id; ?>"<?php echo (int)$appearanceDefinition['head_type'] === $id ? ' selected' : ''; ?>><?php echo site_e($label); ?></option><?php endforeach; ?></select></div>
                        <div class="bw-field"><label for="body-type">Body shape</label><select class="bw-input" id="body-type" name="body_type"><?php foreach($bodyTypes as $id => $label): ?><option value="<?php echo $id; ?>"<?php echo (int)$appearanceDefinition['body_type'] === $id ? ' selected' : ''; ?>><?php echo site_e($label); ?></option><?php endforeach; ?></select></div>
                        <div class="bw-field"><label for="eye-type">Eye layout</label><select class="bw-input" id="eye-type" name="eye_type"><?php foreach($eyeTypes as $id => $label): ?><option value="<?php echo $id; ?>"<?php echo (int)$appearanceDefinition['eye_type'] === $id ? ' selected' : ''; ?>><?php echo site_e($label); ?></option><?php endforeach; ?></select></div>
                        <div class="bw-field"><label for="eyelids">Eyelids</label><select class="bw-input" id="eyelids" name="eyelids"><option value="0"<?php echo (int)$appearanceDefinition['eyelids'] === 0 ? ' selected' : ''; ?>>Off</option><option value="1"<?php echo (int)$appearanceDefinition['eyelids'] === 1 ? ' selected' : ''; ?>>On</option></select></div>
                        <div class="bw-field"><label for="antenna-type">Antennae</label><select class="bw-input" id="antenna-type" name="antenna_type"><?php foreach($antennaTypes as $id => $label): ?><option value="<?php echo $id; ?>"<?php echo (int)$appearanceDefinition['antenna_type'] === $id ? ' selected' : ''; ?>><?php echo site_e($id . ' · ' . $label); ?></option><?php endforeach; ?></select></div>
                        <div class="bw-field"><label for="leg-type">Legs</label><select class="bw-input" id="leg-type" name="leg_type"><?php foreach($legTypes as $id => $label): ?><option value="<?php echo $id; ?>"<?php echo (int)$appearanceDefinition['leg_type'] === $id ? ' selected' : ''; ?>><?php echo site_e($id . ' · ' . $label); ?></option><?php endforeach; ?></select></div>
                    </div>

                    <h3 class="bw-settings-subtitle">Exact RGB colours</h3>
                    <p class="bw-form-note">Use exactly six hexadecimal digits, with an optional #.</p>
                    <div class="bw-appearance-grid bw-appearance-colours">
                        <?php foreach(['head' => 'Head', 'body' => 'Body', 'eye' => 'Eyes', 'antenna' => 'Antennae', 'leg' => 'Legs'] as $key => $label): $hex = $appearanceDefinition[$key . '_colour']; ?>
                        <div class="bw-field bw-appearance-colour-field">
                            <label for="<?php echo $key; ?>-colour"><?php echo $label; ?> colour</label>
                            <span><input type="color" value="#<?php echo site_e($hex); ?>" data-appearance-picker="<?php echo $key; ?>-colour"><input class="bw-input" id="<?php echo $key; ?>-colour" name="<?php echo $key; ?>_colour" type="text" value="#<?php echo site_e($hex); ?>" maxlength="7" pattern="#?[0-9A-Fa-f]{6}" spellcheck="false" autocomplete="off"></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="bw-form-note" id="advanced-weevil-status" role="status" aria-live="polite"></p>
                </form>
            <?php endif; ?>
        </section>

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
                setTimeout(function () { copy.textContent = 'Copy Def'; }, 1400);
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
    var form = document.getElementById('advanced-weevil-form');
    var status = document.getElementById('advanced-weevil-status');
    if (!form || !status || !window.fetch) return;

    var current = document.getElementById('current-weevil-def');
    var toggle = document.getElementById('change-def-toggle');
    var panel = document.getElementById('change-def-panel');
    var pasted = document.getElementById('pasted-weevil-def');
    var change = document.getElementById('change-def-apply');
    var validHex = /^#?[0-9A-Fa-f]{6}$/;

    Array.prototype.forEach.call(form.querySelectorAll('[data-appearance-picker]'), function (picker) {
        var target = document.getElementById(picker.getAttribute('data-appearance-picker'));
        if (!target) return;
        picker.addEventListener('input', function () { target.value = picker.value.toUpperCase(); });
        target.addEventListener('input', function () {
            var value = target.value.trim();
            if (validHex.test(value)) picker.value = (value.charAt(0) === '#' ? value : '#' + value);
        });
    });

    function displaySavedDefinition(data) {
        if (!data.ok || !data.definition) return;
        current.value = data.definition;
        Array.prototype.forEach.call(document.querySelectorAll('[data-weevil-render]'), function (mount) {
            mount.setAttribute('data-weevil-definition', data.definition);
            mount.dispatchEvent(new CustomEvent('bw:weevil-definition-change', {detail: {definition: data.definition}}));
        });
    }

    function send(body, button) {
        button.disabled = true;
        status.textContent = 'Validating and saving appearance…';
        return fetch(form.action, {
            method: 'POST',
            body: body,
            credentials: 'same-origin',
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        }).then(function (response) {
            return response.json().catch(function () { return {ok:false, message:'Unexpected server response.'}; });
        }).then(function (data) {
            status.textContent = data.message || (data.ok ? 'Appearance saved.' : 'Appearance could not be saved.');
            displaySavedDefinition(data);
            return data;
        }).catch(function () {
            status.textContent = 'Could not contact the server.';
            return {ok:false};
        }).finally(function () { button.disabled = false; });
    }

    toggle.addEventListener('click', function () {
        var willOpen = panel.hidden;
        panel.hidden = !willOpen;
        toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        if (willOpen) {
            pasted.value = current.value;
            pasted.focus();
        }
    });

    change.addEventListener('click', function () {
        var raw = pasted.value.trim();
        if (!raw) {
            status.textContent = 'Paste a Weevil definition first.';
            return;
        }
        var body = new FormData();
        body.append('csrf', form.querySelector('[name="csrf"]').value);
        body.append('mode', 'definition');
        body.append('definition', raw);
        send(body, change).then(function (data) {
            if (data.ok) window.setTimeout(function () { window.location.reload(); }, 450);
        });
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        var invalid = Array.prototype.some.call(form.querySelectorAll('[name$="_colour"]'), function (field) {
            return !validHex.test(field.value.trim());
        });
        if (invalid) {
            status.textContent = 'Each colour must contain exactly six hexadecimal digits.';
            return;
        }
        send(new FormData(form), form.querySelector('button[type="submit"]'));
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
