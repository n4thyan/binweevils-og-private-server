<?php
if(!isset($sitePageTitle)) $sitePageTitle = 'Bin Weevils';
if(!isset($siteActive)) $siteActive = '';
if(!isset($siteShowTopAd)) $siteShowTopAd = site_has_ads('site-top');
$siteHeaderNameColor = $siteLoggedIn ? site_cosmetic_equipped_value($siteCosmetics, 'username_color', '#075d86') : '#075d86';
$siteHeaderTitle = $siteLoggedIn ? site_cosmetic_equipped_value($siteCosmetics, 'title', '') : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#70b52d">
    <title><?php echo site_e($sitePageTitle); ?> · Bin Weevils</title>
    <link rel="icon" href="/assets/images/weevil.png" type="image/png">
    <link rel="stylesheet" href="/assets/css/site-redesign.css?v=9">
    <link rel="stylesheet" href="/assets/css/site-preferences.css?v=1">
    <link rel="stylesheet" href="/assets/css/site-live.css?v=1">
    <link rel="stylesheet" href="/assets/css/site-rewards.css?v=1">
    <link rel="stylesheet" href="/assets/css/site-ads.css?v=5">
</head>
<body>
<div class="bw-page-shell"<?php echo $siteLoggedIn ? ' data-account-live' : ''; ?>>
    <header class="bw-header">
        <a class="bw-brand" href="/" aria-label="Bin Weevils home">
            <img src="/assets/images/logo2.png" alt="Bin Weevils">
        </a>

        <button class="bw-nav-toggle" type="button" aria-label="Open navigation" aria-expanded="false" data-nav-toggle>
            <span></span><span></span><span></span>
        </button>

        <nav class="bw-nav" data-nav>
            <a class="bw-nav-link<?php echo site_active('home', $siteActive); ?>" href="/">Home</a>
            <a class="bw-nav-link<?php echo site_active('play', $siteActive); ?>" href="<?php echo $siteLoggedIn ? '/game.php' : '/#login'; ?>">Play</a>
            <a class="bw-nav-link<?php echo site_active('download', $siteActive); ?>" href="/download/">Download</a>
            <a class="bw-nav-link<?php echo site_active('community', $siteActive); ?>" href="/community/">Community</a>
            <?php if($siteLoggedIn): ?>
                <a class="bw-nav-link<?php echo site_active('settings', $siteActive); ?>" href="/settings/">My Weevil</a>
            <?php else: ?>
                <a class="bw-nav-link<?php echo site_active('register', $siteActive); ?>" href="/register/">Create a Weevil</a>
            <?php endif; ?>
        </nav>

        <span class="bw-online-count" data-server-status aria-label="Weevils online">
            <span class="bw-status-dot" aria-hidden="true"></span>
            <strong data-server-players>—</strong>
            <span class="bw-online-label">Weevils online</span>
        </span>

        <?php if($siteLoggedIn && is_array($siteUser)): ?>
            <aside class="bw-account-chip" aria-label="Signed-in Weevil">
                <a class="bw-account-render" href="/settings/" data-weevil-render data-weevil-crop="head" data-weevil-definition="<?php echo site_e($siteUser['def']); ?>" data-weevil-name="<?php echo site_e($siteUser['username']); ?>">
                    <span class="bw-render-pending">Weevil</span>
                </a>
                <div class="bw-account-copy">
                    <strong data-account-stat="username" style="color:<?php echo site_e($siteHeaderNameColor); ?>"><?php echo site_e($siteUser['username']); ?></strong>
                    <?php if($siteHeaderTitle !== ''): ?><em class="bw-account-title"><?php echo site_e($siteHeaderTitle); ?></em><?php endif; ?>
                    <span>Lv <b data-account-stat="level"><?php echo (int)$siteUser['level']; ?></b> · P<b data-account-stat="prestige"><?php echo (int)$siteUser['prestige_count']; ?></b></span>
                    <small><a href="/settings/">Settings</a> · <a href="/login/login.php">Logout</a></small>
                </div>
            </aside>
        <?php else: ?>
            <a class="bw-header-login" href="/#login">Log in</a>
        <?php endif; ?>

    </header>

    <main class="bw-main">
    <?php if($siteShowTopAd): ?>
    <section class="bw-ad-row bw-ad-row--site" aria-label="Sponsor">
        <?php site_ad_slot('site-top', 'leaderboard'); ?>
    </section>
    <?php endif; ?>
<script>
(function () {
    var toggle = document.querySelector('[data-nav-toggle]');
    var nav = document.querySelector('[data-nav]');
    if (!toggle || !nav) return;
    toggle.addEventListener('click', function () {
        var open = nav.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
}());
</script>
