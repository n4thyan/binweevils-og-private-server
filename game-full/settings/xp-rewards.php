<?php
include('../site/bootstrap.php');

if(!$siteLoggedIn || !is_array($siteUser)) {
    header('Location: /#login');
    exit;
}

$sitePageTitle = 'XP Rewards';
$siteActive = 'settings';
$catalog = site_reward_catalog();
$groups = [
    'username_color' => ['label' => 'Name Colours', 'description' => 'Permanent website username colours.'],
    'title' => ['label' => 'Titles', 'description' => 'Permanent titles shown with your website profile.'],
    'profile_background' => ['label' => 'Profile Backgrounds', 'description' => 'Recovered Bin Weevils artwork for your profile card.'],
];

$equippedName = site_cosmetic_equipped_value($siteCosmetics, 'username_color', '#075984');
$equippedTitle = site_cosmetic_equipped_value($siteCosmetics, 'title', '');
$equippedBackground = site_cosmetic_equipped_value($siteCosmetics, 'profile_background', '');

include('../site/header.php');
?>

<section class="bw-rewards-head">
    <div>
        <p class="bw-eyebrow">My Weevil</p>
        <h1 class="bw-section-title">XP Rewards</h1>
        <p class="bw-section-intro">Spend <strong>Banked XP</strong> on permanent cosmetic unlocks. Lifetime XP is never spent, so your lifetime total and future leaderboard position stay intact.</p>
    </div>
    <div class="bw-rewards-balance">
        <span>Banked XP</span>
        <strong data-reward-balance><?php echo site_int($siteUser['xp1']); ?></strong>
    </div>
</section>

<section class="bw-cosmetic-preview" data-reward-preview<?php echo $equippedBackground !== '' ? ' style="background-image:url(\'' . site_e($equippedBackground) . '\')"' : ''; ?>>
    <div class="bw-profile-render-large bw-profile-render-large--preview" data-weevil-render data-weevil-definition="<?php echo site_e($siteUser['def']); ?>" data-weevil-name="<?php echo site_e($siteUser['username']); ?>">
        <span class="bw-render-pending">Weevil</span>
    </div>
    <div class="bw-cosmetic-preview-copy">
        <h2 class="bw-cosmetic-preview-name" data-preview-name style="--bw-preview-name:<?php echo site_e($equippedName); ?>"><?php echo site_e($siteUser['username']); ?></h2>
        <span class="bw-cosmetic-preview-title" data-preview-title<?php echo $equippedTitle === '' ? ' hidden' : ''; ?>><?php echo site_e($equippedTitle); ?></span>
        <p>Level <?php echo (int)$siteUser['level']; ?> · Prestige <?php echo (int)$siteUser['prestige_count']; ?></p>
    </div>
</section>

<p class="bw-reward-status" data-reward-status role="status" aria-live="polite"></p>

<?php foreach($groups as $slot => $group): ?>
<section class="bw-reward-section" data-reward-section="<?php echo site_e($slot); ?>">
    <div class="bw-reward-section-head">
        <div>
            <h2><?php echo site_e($group['label']); ?></h2>
            <p class="bw-muted"><?php echo site_e($group['description']); ?></p>
        </div>
        <?php if(!empty($siteCosmetics['equipped'][$slot])): ?>
            <button class="bw-reward-reset" type="button" data-reward-reset="<?php echo site_e($slot); ?>">Reset to original</button>
        <?php endif; ?>
    </div>

    <div class="bw-reward-grid">
        <?php foreach($catalog as $key => $reward): ?>
            <?php if($reward['slot'] !== $slot) continue; ?>
            <?php
            $owned = !empty($siteCosmetics['unlocked'][$key]);
            $equipped = isset($siteCosmetics['equipped'][$slot]) && $siteCosmetics['equipped'][$slot] === $key;
            $canPrestige = (int)$siteUser['prestige_count'] >= (int)$reward['prestige'];
            $canAfford = (int)$siteUser['xp1'] >= (int)$reward['cost'];
            $classes = 'bw-reward-card' . ($owned ? ' is-owned' : '') . ($equipped ? ' is-equipped' : '');
            ?>
            <article class="<?php echo $classes; ?>" data-reward-card="<?php echo site_e($key); ?>" data-slot="<?php echo site_e($slot); ?>" data-value="<?php echo site_e($reward['value']); ?>">
                <h3><?php echo site_e($reward['name']); ?></h3>
                <p><?php echo site_e($reward['description']); ?></p>
                <div class="bw-reward-meta">
                    <span class="bw-reward-price"><?php echo site_int($reward['cost']); ?> XP</span>
                    <?php if((int)$reward['prestige'] > 0): ?><span class="bw-reward-prestige">P<?php echo (int)$reward['prestige']; ?>+</span><?php endif; ?>
                    <?php if($owned): ?><span class="bw-reward-owned">Owned</span><?php endif; ?>
                </div>

                <?php if($equipped): ?>
                    <button class="bw-button bw-button--green bw-button--small bw-reward-action" type="button" disabled>Equipped</button>
                <?php elseif($owned): ?>
                    <button class="bw-button bw-button--blue bw-button--small bw-reward-action" type="button" data-reward-action="equip" data-reward-key="<?php echo site_e($key); ?>">Equip</button>
                <?php else: ?>
                    <button class="bw-button bw-button--small bw-reward-action" type="button" data-reward-action="buy" data-reward-key="<?php echo site_e($key); ?>"<?php echo (!$canPrestige || !$canAfford) ? ' disabled' : ''; ?>>
                        <?php echo !$canPrestige ? 'Requires P' . (int)$reward['prestige'] : (!$canAfford ? 'Need more XP' : 'Unlock'); ?>
                    </button>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endforeach; ?>

<section class="bw-panel bw-content-panel" style="margin-top:28px;">
    <p class="bw-eyebrow">How it works</p>
    <h2 class="bw-card-title">Unlock once, change whenever you like</h2>
    <p class="bw-muted">Buying a reward subtracts its cost from Banked XP only. Once owned, equipping or swapping that reward is free. Resetting a slot returns it to the original Bin Weevils website appearance without deleting the unlock.</p>
    <div class="bw-button-row">
        <a class="bw-button bw-button--blue bw-button--small" href="/settings/">Back to My Weevil</a>
        <a class="bw-button bw-button--green bw-button--small" href="/game.php">Play now</a>
    </div>
</section>

<script>
(function () {
    var csrf = <?php echo json_encode(site_csrf_token()); ?>;
    var status = document.querySelector('[data-reward-status]');
    var balance = document.querySelector('[data-reward-balance]');

    function setStatus(message) {
        if (status) status.textContent = message || '';
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

    document.addEventListener('click', function (event) {
        var actionButton = event.target.closest('[data-reward-action]');
        var resetButton = event.target.closest('[data-reward-reset]');
        if (!actionButton && !resetButton) return;

        if (actionButton) {
            var action = actionButton.getAttribute('data-reward-action');
            var key = actionButton.getAttribute('data-reward-key');
            actionButton.disabled = true;
            setStatus(action === 'buy' ? 'Unlocking reward…' : 'Equipping reward…');
            post({action: action, reward_key: key}).then(function (data) {
                setStatus(data.message || (data.ok ? 'Done.' : 'That action failed.'));
                if (data.ok) {
                    if (typeof data.bankedXp !== 'undefined' && balance) balance.textContent = Number(data.bankedXp).toLocaleString();
                    window.setTimeout(function () { window.location.reload(); }, 450);
                } else {
                    actionButton.disabled = false;
                }
            }).catch(function () {
                setStatus('Could not contact the server.');
                actionButton.disabled = false;
            });
            return;
        }

        var slot = resetButton.getAttribute('data-reward-reset');
        resetButton.disabled = true;
        setStatus('Resetting cosmetic…');
        post({action: 'unequip', slot: slot}).then(function (data) {
            setStatus(data.message || (data.ok ? 'Reset.' : 'Reset failed.'));
            if (data.ok) window.setTimeout(function () { window.location.reload(); }, 350);
            else resetButton.disabled = false;
        }).catch(function () {
            setStatus('Could not contact the server.');
            resetButton.disabled = false;
        });
    });
}());
</script>

<?php include('../site/footer.php'); ?>
