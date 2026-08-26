<?php
error_reporting(0);
include('essential/backbone.php');
$aes = new AES256;
$err = $_GET['err'];
$err = $aes->decrypt($err, "hdjjsdarkkarecool");
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Login | Bin Weevils Rewritten - Free Online 3D virtual world | Online Games | Watch Videos | Enter Competitions</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/images/icons/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link rel="shortcut icon" href="assets/img/logo.png" type="image/x-icon"/>
    <meta property="og:image" content="http://bwrewritten.com/assets/img/logo.png">
    <meta name="description" content="Binweevils Rewritten - The newly and improved binweevils, bringing back the stuff that you love!">
	<link rel="icon" type="image/png" href="assets/img/logo.png">
    <meta name="theme-color" content="#22b305">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script src="http://ajax.aspnetcdn.com/ajax/jquery.ui/1.8.9/jquery-ui.js" type="text/javascript"></script>
    <link href="http://ajax.aspnetcdn.com/ajax/jquery.ui/1.8.9/themes/blitzer/jquery-ui.css"
    rel="stylesheet" type="text/css" />
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=McLaren&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <link href="//cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@3/dark.css" rel="stylesheet">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@9/dist/sweetalert2.min.js"></script>
    <script data-ad-client="ca-pub-1806512904788143" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script> 

    <script src="https://contextual.media.net/dmedianet.js?cid=8CU7F56KI" async="async"></script>
    <script type="text/javascript">
        window._mNHandle = window._mNHandle || {};
        window._mNHandle.queue = window._mNHandle.queue || [];
        medianet_versionId = "3121199";
    </script>
    <script type="text/javascript">
    if (screen.width <= 1000) {
        document.location = "http://bwrewritten.com/mobile.php";
    }
    </script>
  </head>

  <body>
    <nav>
      <input type="checkbox" id="check">
      <label for="check" class="checkbtn">
        <i class="fas fa-bars"></i>
      </label>
      <a href="#" class="logo"><img src="/assets/images/logo.png" width="110px"></a>
      <ul>
        <li><a href="http://bwrewritten.com">Home</a></li>
        <li><a href="http://play.bwrewritten.com/blog">Blog</a></li>
        <!--<li><a href="http://play.bwrewritten.com/help">Help</a></li>-->
        <li><a href="http://play.bwrewritten.com/legal">Legal</a></li>
        <li><a href="http://topup.bwrewritten.com">Topups</a></li>
    	<li><a href="http://discord.gg/HMMNXK2jFs">Discord</a></li>
	    <li><button id="download" class="play">Download</button></li>
      </ul>

    </nav>

    <div class="help-header">
     <h3>Welcome to Bin Weevils Rewritten!</h3>
    </div>

    <div id="contentLogin">
	
<div id="container">
<img class="img-guys" src="/assets/images/tink_clott.png" alt="">

<div class="splitscreen">
<div class="left">
   <img src="/assets/images/register.png" onClick="location.href = 'http://play.bwrewritten.com/register'" width="250px" style="cursor: pointer;">
</div>
<div class="right">
   <div id="login-title">Login</div>
   <form action="http://play.bwrewritten.com/login/login.php" method="POST">
      <div class="label-container-name">
         <label class="login-payment-label" for="userID">Bin Weevil Name</label>
         <input class="name login-payment-input" type="text" name="userID" id="userID" value="" required="">
      </div>
      <div class="label-container-pass">
         <label class="login-payment-label" for="password">Password</label>
         <input class="password login-payment-input" type="password" name="password" id="password" required="">
      </div>
      <div class="remember-me-block">
         <a class="remember-me" onclick="toggleTick(); return false;">
            <img id="tick" class="pull-left" alt="" src="/assets/images/tickbox.png">
            <p class="remember-me-label pull-left">Remember me</p>
            <div class="blank-tick pull-right"></div>
         </a>
         <div class="hidden-tick">
            <label for="rememberMe">
            <input type="checkbox" name="rememberMe" id="rememberMe" checked="checked">
            </label>
         </div>
      </div>
      <a class="remember-me-label forgotten-label" href="#">
      Forgotten your password?
      </a>
      <button id="myBtn" class="login">Login</button>
   </form>
</div>
</div>

</div>

    <footer>
      <div class="main-content">
        <div class="left box">
          <div class="content">
            <a href="#" class="footer-logo"><img src="/assets/images/logo.png" width="200px"></a>
          </div>
        </div>

        <div class="center box">
          <h2>Quick Links</h2>
          <div class="content">
            <div class="support">
              <span class="fab fa-twitter"></span>
              <span class="text">Twitter</span>
            </div>
            <div class="policy">
              <span class="fab fa-youtube"></span>
              <span class="text">YouTube</span>
            </div>
            <div class="email">
              <span class="fab fa-discord"></span>
              <span class="text">Discord</span>
            </div>
          </div>
        </div>

        <div class="right box">
          <h2>Weevil of the Week</h2>
          <div class="content">
            <div class="wow">
              <img src="/assets/images/wow.png" width="270px" style="border-radius:3px;">
            </div>
          </div>
        </div>
      </div>
      <div class="bottom">
        <center>
          <span class="credit">Bin Weevils Rewritten is a fan-made recreation and is in no way affiliated with 55Pixels Ltd.</span>
        </center>
      </div>
    </footer>

  </body>
</html>

<style>
nav ul {
    float: right;
    margin-right: 260px;
}
</style>

<script>
        //remember me check/uncheck
        function toggleTick() {
            var rememberMe = $("#rememberMe");
            var checked = $("#rememberMe:checked").length;
            var tick = $("#tick");
            $(".blank-tick").toggle();

            if (checked == 0) {
                rememberMe.prop("checked", "checked");
                tick.attr("src", "/assets/images/tickbox.png");
            } else {
                rememberMe.prop("checked", "");
                tick.attr("src", "/assets/images/tickbox2.png");
            }
        }
</script>

<script type="text/javascript">
    document.getElementById("download").onclick = function () {
        location.href = "http://play.bwrewritten.com/download";
    };
</script>

<script type="text/javascript">
<?php
if(!empty($err)) {
	echo 'Swal.fire(
		\'Uh Oh!\',
		\'' . $err . '\',
		\'error\'
	);';
}
?>
</script>