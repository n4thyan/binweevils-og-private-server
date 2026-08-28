<?php
include('site/bootstrap.php');

$sitePageTitle = 'Home';
$siteActive = 'home';
$errMessage = '';
$announcements = !empty($siteConfig['announcements']) && is_array($siteConfig['announcements']) ? $siteConfig['announcements'] : [];

usort($announcements, function($a, $b) {
    $aUrgent = !empty($a['urgent']) ? 1 : 0;
    $bUrgent = !empty($b['urgent']) ? 1 : 0;
    return $bUrgent - $aUrgent;
});

if(isset($_GET['err']) && $_GET['err'] !== '') {
    $rawError = (string)$_GET['err'];
    $aes = new AES256();
    $decodedError = $aes->decrypt($rawError, 'hdjjsdarkkarecool');
    $errMessage = !empty($decodedError) ? $decodedError : $rawError;
}

include('site/header.php');
?>

<?php if(!empty($announcements)): ?>
<section class="bw-announcement" aria-label="Announcements">
    <div class="bw-announcement-label">Bin Bulletin</div>
    <div class="bw-marquee">
        <div class="bw-marquee-track">
            <?php foreach($announcements as $i => $announcement): ?>
                <?php if($i > 0): ?><span class="bw-marquee-separator">★</span><?php endif; ?>
                <?php $announcementClass = !empty($announcement['urgent']) ? ' class="bw-announcement-urgent"' : ''; ?>
                <?php if(!empty($announcement['href'])): ?>
                    <a<?php echo $announcementClass; ?> href="<?php echo site_e($announcement['href']); ?>"><?php echo !empty($announcement['urgent']) ? 'Important: ' : ''; ?><?php echo site_e($announcement['text']); ?></a>
                <?php else: ?>
                    <span<?php echo $announcementClass; ?>><?php echo !empty($announcement['urgent']) ? 'Important: ' : ''; ?><?php echo site_e($announcement['text']); ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="bw-live-status" data-server-status aria-label="Live server status">
    <div class="bw-live-status-item">
        <span class="bw-status-dot" aria-hidden="true"></span>
        <span>Game server</span>
        <strong data-server-online>Checking…</strong>
    </div>
    <div class="bw-live-status-item">
        <span>Weevils online</span>
        <strong data-server-players>—</strong>
    </div>
    <div class="bw-live-status-item bw-live-status-build">
        <span>Build</span>
        <strong><?php echo site_e(isset($siteConfig['build_label']) ? $siteConfig['build_label'] : ''); ?></strong>
    </div>
</section>

<?php if($errMessage !== ''): ?>
    <div class="bw-alert" role="alert"><?php echo site_e(strip_tags($errMessage)); ?></div>
<?php endif; ?>

<section class="bw-hero">
    <div class="bw-panel bw-panel--green bw-hero-copy">
        <p class="bw-eyebrow">The Bin is back</p>
        <?php if($siteLoggedIn && is_array($siteUser)): ?>
            <h1>Welcome back, <span data-account-stat="username"><?php echo site_e($siteUser['username']); ?></span>!</h1>
            <p class="bw-hero-lead">Your Weevil is ready. Jump back into the Bin, visit the community chat, or tweak your account and cosmetics from My Weevil.</p>
            <div class="bw-button-row">
                <a class="bw-button bw-button--green" href="/game.php">Play Bin Weevils</a>
                <a class="bw-button bw-button--blue" href="/settings/">My Weevil</a>
            </div>
            <div class="bw-stat-grid" aria-label="Weevil stats">
                <div class="bw-stat"><span>Level</span><strong data-account-stat="level"><?php echo (int)$siteUser['level']; ?></strong></div>
                <div class="bw-stat"><span>Prestige</span><strong data-account-stat="prestige"><?php echo (int)$siteUser['prestige_count']; ?></strong></div>
                <div class="bw-stat"><span>Lifetime XP</span><strong data-account-stat="lifetime-xp"><?php echo site_int($siteUser['xp']); ?></strong></div>
                <div class="bw-stat"><span>Banked XP</span><strong data-account-stat="banked-xp"><?php echo site_int($siteUser['xp1']); ?></strong></div>
            </div>
        <?php else: ?>
            <h1>Welcome back to the Bin!</h1>
            <p class="bw-hero-lead">The classic Bin Weevils world, restored for the community. Log in with your Weevil or create a new one and start exploring.</p>
            <div class="bw-button-row">
                <a class="bw-button bw-button--green" href="#login">Log in &amp; play</a>
                <a class="bw-button bw-button--blue" href="/register/">Create a Weevil</a>
            </div>
        <?php endif; ?>
        <img class="bw-characters" src="/assets/images/login/Tink_Clott.png" alt="" aria-hidden="true">
    </div>

    <?php if($siteLoggedIn && is_array($siteUser)): ?>
        <aside class="bw-panel bw-login-card">
            <p class="bw-eyebrow">Your account</p>
            <h2 class="bw-card-title" data-account-stat="username"><?php echo site_e($siteUser['username']); ?></h2>
            <div class="bw-stat-grid">
                <div class="bw-stat"><span>Mulch</span><strong data-account-stat="mulch"><?php echo site_int($siteUser['mulch']); ?></strong></div>
                <div class="bw-stat"><span>Dosh</span><strong data-account-stat="dosh"><?php echo site_int($siteUser['dosh']); ?></strong></div>
                <div class="bw-stat"><span>Next level</span><strong><span data-account-stat="next-xp"><?php echo site_int($siteUser['xp2']); ?></span> XP</strong></div>
                <div class="bw-stat"><span>Server</span><strong data-server-online>Checking…</strong></div>
            </div>
            <div class="bw-button-row">
                <a class="bw-button bw-button--small" href="/game.php">Enter the Bin</a>
                <a class="bw-button bw-button--blue bw-button--small" href="/settings/">Settings</a>
            </div>
            <p class="bw-form-note">Nest News remains the place for proper in-game news. Website notices stay short and appear in the Bin Bulletin above.</p>
        </aside>
    <?php else: ?>
        <aside class="bw-panel bw-login-card" id="login">
            <p class="bw-eyebrow">Returning player</p>
            <h2 class="bw-card-title">Log in to your Weevil</h2>
            <form action="/login/login.php" method="post">
                <div class="bw-field">
                    <label for="userID">Bin Weevil Name</label>
                    <input class="bw-input" id="userID" name="userID" type="text" maxlength="16" autocomplete="username" required>
                </div>
                <div class="bw-field">
                    <label for="password">Password</label>
                    <input class="bw-input" id="password" name="password" type="password" autocomplete="current-password" required>
                </div>
                <label class="bw-remember-row"><input type="checkbox" data-remember-username> Remember my Weevil name on this device</label>
                <button class="bw-button bw-button--green" type="submit">Log in &amp; play</button>
            </form>
            <p class="bw-form-note">New to the Bin? <a href="/register/">Create your Weevil here.</a></p>
        </aside>
    <?php endif; ?>
</section>

<section class="bw-home-grid" aria-label="Explore the site">
    <article class="bw-panel bw-feature-card">
        <p class="bw-eyebrow">Play</p>
        <h2>Enter the Bin</h2>
        <p>Launch the restored classic client and pick up exactly where your Weevil left off.</p>
        <a class="bw-button bw-button--green bw-button--small" href="<?php echo $siteLoggedIn ? '/game.php' : '/#login'; ?>"><?php echo $siteLoggedIn ? 'Play now' : 'Log in'; ?></a>
    </article>

    <article class="bw-panel bw-panel--orange bw-feature-card">
        <p class="bw-eyebrow">Community</p>
        <h2>xat Chat</h2>
        <p>The website community room uses xat for a proper old-school Bin-era chat experience.</p>
        <a class="bw-button bw-button--small" href="/community/">Open community</a>
    </article>

    <article class="bw-panel bw-panel--green bw-feature-card">
        <p class="bw-eyebrow"><?php echo $siteLoggedIn ? 'Account' : 'New player'; ?></p>
        <h2><?php echo $siteLoggedIn ? 'My Weevil' : 'Create a Weevil'; ?></h2>
        <p><?php echo $siteLoggedIn ? 'View your progression, account options and unlocked customisation in one place.' : 'Make a new Weevil using the existing account system and head straight into the game.'; ?></p>
        <a class="bw-button bw-button--blue bw-button--small" href="<?php echo $siteLoggedIn ? '/settings/' : '/register/'; ?>"><?php echo $siteLoggedIn ? 'Open settings' : 'Get started'; ?></a>
    </article>
</section>

<?php include('site/footer.php'); ?>
