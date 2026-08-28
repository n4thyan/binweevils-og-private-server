<?php
include('../site/bootstrap.php');

$sitePageTitle = 'Help';
$siteActive = 'help';
include('../site/header.php');
?>

<section class="bw-panel bw-panel--green bw-content-panel">
    <p class="bw-eyebrow">Need a hand?</p>
    <h1 class="bw-section-title">Bin Weevils Help</h1>
    <p class="bw-section-intro">Quick help for accounts, launching the restored client and the main classic Bin Weevils systems.</p>
    <div class="bw-button-row">
        <a class="bw-button bw-button--green bw-button--small" href="#account">Account</a>
        <a class="bw-button bw-button--blue bw-button--small" href="#playing">Playing</a>
        <a class="bw-button bw-button--small" href="#progression">XP &amp; currency</a>
        <a class="bw-button bw-button--green bw-button--small" href="#classic-guide">Classic guide</a>
    </div>
</section>

<section class="bw-home-grid" style="margin-top:27px;" aria-label="Help shortcuts">
    <article class="bw-panel bw-feature-card">
        <p class="bw-eyebrow">New player</p>
        <h2>Create a Weevil</h2>
        <p>Use the existing registration flow to make your account, then sign in from the homepage.</p>
        <a class="bw-button bw-button--blue bw-button--small" href="/register/">Create a Weevil</a>
    </article>

    <article class="bw-panel bw-panel--orange bw-feature-card">
        <p class="bw-eyebrow">Returning player</p>
        <h2>Log in &amp; play</h2>
        <p>Your website session is the same account used to launch the restored classic game client.</p>
        <a class="bw-button bw-button--small" href="<?php echo $siteLoggedIn ? '/game.php' : '/#login'; ?>"><?php echo $siteLoggedIn ? 'Enter the Bin' : 'Log in'; ?></a>
    </article>

    <article class="bw-panel bw-panel--green bw-feature-card">
        <p class="bw-eyebrow">Account</p>
        <h2>My Weevil</h2>
        <p>View progression, currencies, your saved Weevil definition and website preferences. Signed-in players can also change their password.</p>
        <a class="bw-button bw-button--green bw-button--small" href="<?php echo $siteLoggedIn ? '/settings/' : '/#login'; ?>"><?php echo $siteLoggedIn ? 'Open My Weevil' : 'Log in first'; ?></a>
    </article>
</section>

<section class="bw-panel bw-content-panel" id="account" style="margin-top:27px;">
    <p class="bw-eyebrow">Account</p>
    <h2 class="bw-card-title">Creating, signing in and changing your password</h2>
    <p class="bw-muted"><strong>Creating an account:</strong> open <a href="/register/">Create a Weevil</a> and complete the registration form. Your Weevil name is the public name other players will see in the game.</p>
    <p class="bw-muted"><strong>Signing in:</strong> use the login panel on the homepage. A successful login creates the website session and takes you to the Play page.</p>
    <p class="bw-muted"><strong>Changing your password:</strong> while signed in, open <a href="/settings/#security">My Weevil → Security</a>. Changing it also rotates the website session key and game login key.</p>
    <p class="bw-muted"><strong>Logging out:</strong> use the Logout link in the signed-in account panel at the top of the website.</p>
</section>

<section class="bw-panel bw-panel--green bw-content-panel" id="playing" style="margin-top:27px;">
    <p class="bw-eyebrow">Playing</p>
    <h2 class="bw-card-title">Launching the restored classic client</h2>
    <p class="bw-muted">After signing in, choose <strong>Play</strong> or <strong>Enter the Bin</strong>. The Play page hosts the restored classic Bin Weevils client and keeps the existing game connection bridge intact.</p>
    <p class="bw-muted">This preservation build is primarily a desktop experience. If the game frame itself does not start, that is separate from the website login: check that the local game/server stack is running before changing your account details.</p>
    <p class="bw-muted">The website redesign does not replace the game client. Rooms, Weevils, inventory, shops and other in-game systems continue to come from the restored game and server.</p>
</section>

<section class="bw-panel bw-panel--orange bw-content-panel" id="progression" style="margin-top:27px;">
    <p class="bw-eyebrow">Progression</p>
    <h2 class="bw-card-title">Levels, XP, Mulch &amp; Dosh</h2>
    <p class="bw-muted"><strong>XP</strong> drives level progression. The website distinguishes permanent <strong>Lifetime XP</strong> from <strong>Banked XP</strong>, the spendable/progression balance reserved for the planned XP rewards catalogue.</p>
    <p class="bw-muted"><strong>Mulch</strong> and <strong>Dosh</strong> are separate in-game currencies. Shops continue to decide which currency applies to their own stock rather than the website changing purchase rules.</p>
    <p class="bw-muted">Your current level, Prestige, XP, Mulch and Dosh are shown on <a href="/settings/">My Weevil</a> when signed in.</p>
</section>

<section class="bw-panel bw-content-panel" id="classic-guide" style="margin-top:27px;">
    <p class="bw-eyebrow">Classic gameplay reference</p>
    <h2 class="bw-card-title">Useful things to remember in the Bin</h2>
    <p class="bw-muted">These are classic Bin Weevils mechanics preserved as a reference. Individual activities may appear gradually as restoration work reaches them.</p>

    <div class="bw-home-grid" style="margin-top:20px;">
        <article class="bw-panel bw-feature-card">
            <p class="bw-eyebrow">Secret Codes</p>
            <h2>Rewards</h2>
            <p>Secret Codes were entered from the game UI or the Mystery Code Machine in Lab's Lab to unlock rewards.</p>
        </article>

        <article class="bw-panel bw-panel--green bw-feature-card">
            <p class="bw-eyebrow">Hats</p>
            <h2>Dress your Weevil</h2>
            <p>Classic hats are part of Weevil customisation, with Hem's Hat Shop traditionally found inside Dosh's Palace.</p>
        </article>

        <article class="bw-panel bw-panel--orange bw-feature-card">
            <p class="bw-eyebrow">Nests</p>
            <h2>Decorating</h2>
            <p>Nest furniture is managed through the My Stuff Box. Nestco and Bin Mart provide separate furniture stock and currencies.</p>
        </article>

        <article class="bw-panel bw-panel--green bw-feature-card">
            <p class="bw-eyebrow">Gardens</p>
            <h2>Plant &amp; harvest</h2>
            <p>Gardens are reached from your Nest and use the My Stuff Box for seeds and garden items.</p>
        </article>

        <article class="bw-panel bw-feature-card">
            <p class="bw-eyebrow">Plazas</p>
            <h2>Bin Tycoon</h2>
            <p>Tycoon Plazas are personal spaces that can be decorated and opened for other Weevils to visit.</p>
        </article>

        <article class="bw-panel bw-panel--orange bw-feature-card">
            <p class="bw-eyebrow">Community</p>
            <h2>Outside the game</h2>
            <p>The redesigned website has a separate community page for the project's web chat and announcements.</p>
            <a class="bw-button bw-button--small" href="/community/">Open Community</a>
        </article>
    </div>
</section>

<?php include('../site/footer.php'); ?>
