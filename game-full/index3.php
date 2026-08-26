<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta content="IE=edge" http-equiv="X-UA-Compatible">
	<meta content="width=device-width, initial-scale=1" name="viewport">
	<title>Bin Weevils Rewritten – Login</title>
	<link href="assets/images/weevil.png" rel="shortcut icon" type="image/x-icon">
	<meta content="assets/images/logo.png" property="og:image">
	<meta content="Binweevils Rewritten - The newly and improved binweevils, bringing back the stuff that you love!" name="description">
	<meta content="games, free online games, games, kids games, racing games, multiplayer games, maths games, virtual pets, pets, competitions, videos, bin, weevils, wivles, benwivles, bin weevils, bin weevils rewritten, bwr, bwrewritten" name="keywords">
	<meta content="#22b305" name="theme-color">
	<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet"><!-- Bulma Version 0.9.0-->
	<link href="https://unpkg.com/bulma@0.9.0/css/bulma.min.css" rel="stylesheet">
	<link href="assets/css/style.css" rel="stylesheet" type="text/css">
	<link href="https://unpkg.com/bulma-modal-fx/dist/css/modal-fx.min.css" rel="stylesheet">
	<script src="https://kit.fontawesome.com/4a71c0ba56.js">
	</script>
</head>
<body>
	<section class="hero is-info is-medium is-bold">
		<div class="hero-head">
			<nav class="navbar">
				<div class="container">
					<span class="navbar-burger burger" data-target="navbarMenu"><span></span> <span></span> <span></span></span>
				</div>
				<div class="navbar-menu" id="navbarMenu">
					<div class="navbar-end">
						<div class="navbar-bg">
							<div class="tabs is-right">
								<ul>
									<li class="is-active">
										<a href="https://bwrewritten.com">Home</a>
									</li>
									<li>
										<a href="https://play.bwrewritten.com/blog">Blog</a>
									</li>
									<li>
										<a href="https://play.bwrewritten.com/help">Help</a>
									</li>
									<li>
										<a href="https://topup.bwrewritten.com">Topups</a>
									</li>
									<li>
										<a href="https://play.bwrewritten.com/legal">Legal</a>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</nav>
		</div>
	</section>
	<div class="central-container">
		<img alt="" class="login-container" src="/assets/images/login/mainContainerBG.png"> <img alt="" class="img-guys" src="/assets/images/login/Tink_Clott.png">
		<div class="download-section">
			<h1>Haven't installed our app yet? Download it now!</h1>
		</div>
		<div class="download-buttons">
			<a data-os="windows" href="/download/#windows"><button class="button"><i aria-hidden="true" class="fa fa-windows"></i> Windows</button></a> <a data-os="mac" href="/download/#mac"><button class="button"><i aria-hidden="true" class="fa fa-apple"></i> Mac</button></a>
		</div>
		<div class="discord">
			<iframe frameborder="0" src="https://invidget.switchblade.xyz/binweevils"></iframe>
			<h2>Feel free to join our Discord!</h2>
		</div><!--Create New Weevil -->
		 <a href="/register"><img alt="" class="new-player img-hover" onmouseout="this.src='/assets/images/login/new_player_1.png'" onmouseover="this.src='/assets/images/login/new_player_2.png'" src="/assets/images/login/new_player_1.png"></a> <!--Login Form -->
		<form action="http://web.archive.org/web/20190819184941/https://www.binweevils.com/login" id="login-play-form" method="post" name="login-play-form">
			<input name="redirect_url" type="hidden" value="https://play.binweevils.com/game.php"> <img alt="" class="img-responsive img-align-center" src="/assets/images/login/returning_player.png" style="margin-bottom: 30px; max-width: 170px;">
			<div class="label-container-name">
				<label class="login-payment-label" for="userID">Bin Weevil Name</label> <input class="name login-payment-input" id="userID" name="userID" required="" type="text" value="">
			</div>
			<div class="label-container-pass">
				<label class="login-payment-label" for="password">Password</label> <input class="password login-payment-input" id="password" name="password" required="" type="password">
			</div><input class="input-submit" type="submit"> <a class="login-btn" href="game.php"><img class="img-hover img-responsive img-align-center wait-onclick" onmouseout="this.src='/assets/images/login/login_1.png'" onmouseover="this.src='/assets/images/login/login_2.png'" src="/assets/images/login/login_1.png" style="margin-top: 30px; max-width: 170px;"></a>
			<div align="center" id="loading" style="margin-top: 5px;">
				<img alt="" src="/web/20190819184941im_/https://play.binweevils.com/assets/img/blog/while-waiting.svg"> <span>Please wait...</span>
			</div><input id="form_token" name="form_token" type="hidden" value="6a2bd975da6534cfcaba215cce5e14ad">
		</form>
	</div>
	<div class="box cta">
		<p class="has-text-centered"></p>
		<nav class="level">
						<p class="level-item has-text-centered"><a class="link is-info" href="https://play.bwrewritten.com/privacy">Privacy</a></p>
			<p class="level-item has-text-centered"><a class="link is-info" href="https://play.bwrewritten.com/about">About Us</a></p>
			<p class="level-item has-text-centered"><img alt="" src="/assets/images/weevil.png" style="height: 30px;"></p>
			<p class="level-item has-text-centered"><a class="link is-info" href="https://discord.gg/hRw2rAfut2" target="_blank">Discord</a></p>
			<p class="level-item has-text-centered"><a class="link is-info" href="https://twitter.com/bwrewritten" target="_blank">Twitter</a></p>
		</nav>
		<p></p>
	</div>
	<footer class="footer">
		<div class="content has-text-centered">
			<p>Bin Weevils Rewritten is a fan-made recreation and is in no way affiliated with 55Pixels Ltd.</p>
			<p>Crafted with <i class="fas fa-heart"></i> by Luke & Loc</p>
		</div>
		<script src="../assets/js/bulma.js">
		</script>
	</footer>
	<style>
	             html, body {
	               background: url(/assets/images/background.png) no-repeat;
	               background-size: cover;
	             }
	             .hero.is-info.is-bold {
	               background: transparent;
	             }
	             .login-container {
	               margin: 50px auto 0;
	             }
	             .central-container {
	               position: relative;
	               width: 941px;
	               margin: 0 auto;
	             }
	             .img-guys {
	               position: absolute;
	               top: 20px;
	               left: 15px;
	             }
	             .new-player {
	               position: absolute;
	               top: 80px;
	               left: 371px;
	               width: 265px;
	               height: auto;
	             }
	#login-play-form {
	   position: absolute;
	   top: 80px;
	   right: 26px;
	   width: 265px;
	   height: 389px;
	   background-color: #e5f4fa;
	   border-radius: 10px;
	   padding: 15px;
	}
	#login-play-form .login-payment-label {
	 font-family: Burbank Small Bold;
	   color: #00528c;
	   font-size: 18px;
	   margin-bottom: 0;
	}
	#login-play-form .login-payment-input {
	   font-size: 18px;
	   border: solid #29a1cc 3px;
	   border-radius: 10px;
	   background-color: #e0eaf2;
	   width: 100%;
	   padding: 5px 10px;
	}
	.label-container-pass {
	   margin-top: 15px;
	}
	#login-play-form a.forgotten-label {
	   margin-left: 0;
	   margin-top: -2px;
	}
	form#login-play-form .remember-me-label {
	   color: #00528c;
	   font-size: 15px;
	   font-weight: 700;
	   margin-left: 10px;
	   margin-top: 2px;
	}
	#login-play-form a.forgotten-label {
	   margin-left: 0;
	   margin-top: -2px;
	}
	.pull-left {
	   float: left!important;
	}
	.pull-right {
	   float: right!important;
	}
	#login-play-form .blank-tick {
	   display: none;
	   width: 22px;
	   height: 22px;
	}
	#login-play-form .hidden-tick {
	   display: none;
	   position: absolute;
	   top: 165px;
	   left: 40px;
	}
	.input-submit {
	   visibility: hidden;
	   position: absolute;
	   top: 0;
	}
	#loading {
	   display: none;
	   margin-top: 15px;
	}
	.download-section {
	   position: absolute;
	   top: 200px;
	   left: 35px;
	   height: 300px;
	   width: 310px;
	   text-align: center;
	}
	.download-buttons {
	   position: absolute;
	   top: 270px;
	   left: 35px;
	   height: 300px;
	   width: 310px;
	   text-align: center;
	}
	.discord {
	   position: absolute;
	   top: 300px;
	   left: 35px;
	   height: 300px;
	   width: 310px;
	   text-align: center;
	}
	h1 {
	 font-size: 18px;
	 font-family: Burbank Small Bold;
	 color: #00528c;
	}
	h2 {
	 margin-top: -25px;
	 font-size: 18px;
	 font-family: Burbank Small Bold;
	 color: #00528c;
	}
	.button {
	 font-family: Burbank Small Bold;
	}
	.fa-windows {
	 margin-right: 10px;
	 margin-bottom: 5px;
	}
	.fa-apple {
	 margin-right: 10px;
	 margin-bottom: 5px;
	}
	.box.cta {
	 margin-top: 50px;
	}
	.img-align-center {
	   margin: 0 auto;
	   display: block;
	}
	</style>
</body>
</html>