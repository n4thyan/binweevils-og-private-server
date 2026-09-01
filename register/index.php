<?php
include('../site/bootstrap.php');

if($siteLoggedIn) {
    header('Location: /settings/');
    exit;
}

$referralCode = strtoupper(trim((string)($_GET['ref'] ?? '')));
if(!preg_match('/^BW[A-Z0-9]{8,22}$/', $referralCode)) $referralCode = '';

$sitePageTitle = 'Create a Weevil';
$siteActive = 'register';
include('../site/header.php');
?>

<section class="bw-hero">
    <div class="bw-panel bw-panel--green bw-hero-copy">
        <p class="bw-eyebrow">New player</p>
        <h1>Create your Weevil</h1>
        <p class="bw-hero-lead">Pick a Bin Weevil name and a password, then drop straight into the game. Your Weevil, garden and progression are all kept safe on the Bin Weevils servers.</p>
        <img class="bw-characters" src="/assets/images/login/Tink_Jump.png" alt="" aria-hidden="true">
    </div>

    <aside class="bw-panel bw-login-card">
        <p class="bw-eyebrow">Join the Bin</p>
        <h2 class="bw-card-title">Create account</h2>
        <div class="bw-alert" id="register-error" role="alert" hidden></div>
        <form id="register-form">
            <div class="bw-field">
                <label for="userID">Bin Weevil Name</label>
                <input class="bw-input" id="userID" name="userID" type="text" minlength="3" maxlength="16" autocomplete="username" required>
            </div>
            <div class="bw-field">
                <label for="password">Password</label>
                <input class="bw-input" id="password" name="password" type="password" autocomplete="new-password" required>
            </div>
            <div class="bw-field">
                <label for="referral-code">Referral code <span class="bw-muted">(optional)</span></label>
                <input class="bw-input" id="referral-code" name="referral_code" type="text" value="<?php echo site_e($referralCode); ?>" maxlength="24" autocomplete="off" spellcheck="false">
            </div>
            <button class="bw-button bw-button--green" id="register-submit" type="submit">Create my Weevil</button>
        </form>
        <p class="bw-form-note">Already have a Weevil? <a href="/#login">Log in here.</a></p>
    </aside>
</section>

<section class="bw-tips-strip" aria-label="Before you sign up">
    <div class="bw-tip">
        <h3 class="bw-card-title">Pick carefully</h3>
        <p>Weevil names are 3–16 characters and are checked against reserved names and the game's existing filters.</p>
    </div>
    <div class="bw-tip bw-tip--accent">
        <h3 class="bw-card-title">Keep it private</h3>
        <p>Use a password you do not share with anyone in-game or on xat. Staff should never need your password.</p>
    </div>
    <div class="bw-tip">
        <h3 class="bw-card-title">Straight into the Bin</h3>
        <p>A successful signup creates your existing game session and takes you directly to the game, just like the original flow.</p>
    </div>
</section>

<script>
(function () {
    var form = document.getElementById('register-form');
    var error = document.getElementById('register-error');
    var submit = document.getElementById('register-submit');
    if (!form) return;

    function showError(message) {
        error.textContent = message;
        error.hidden = false;
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        error.hidden = true;
        submit.disabled = true;
        submit.textContent = 'Creating…';

        var data = new URLSearchParams();
        data.set('userID', document.getElementById('userID').value);
        data.set('password', document.getElementById('password').value);
        data.set('referral_code', document.getElementById('referral-code').value.trim());
        data.set('recap', '1');

        fetch('/register/create-new-weevil.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'},
            body: data.toString(),
            credentials: 'same-origin'
        }).then(function (response) {
            if (response.redirected && response.url.indexOf('/game.php') !== -1) {
                window.location.href = response.url;
                return null;
            }
            return response.text();
        }).then(function (text) {
            if (text === null) return;
            if (text.indexOf('responseCode=3') !== -1) {
                showError('That Weevil name is unavailable or does not meet the name rules.');
            } else if (text.indexOf('responseCode=4') !== -1) {
                showError('That referral code is not valid. Check the link or leave the field blank.');
            } else if (text.indexOf('responseCode=429') !== -1) {
                showError('Too many accounts have been created from this connection. Try again later.');
            } else if (text.indexOf('responseCode=2') !== -1) {
                showError('We could not create the account. Check your details and try again.');
            } else if (text.indexOf('responseCode=999') !== -1) {
                showError('Please fill in both fields and try again.');
            } else {
                window.location.href = '/game.php';
            }
        }).catch(function () {
            showError('The registration service could not be reached.');
        }).finally(function () {
            submit.disabled = false;
            submit.textContent = 'Create my Weevil';
        });
    });
}());
</script>

<?php include('../site/footer.php'); ?>
