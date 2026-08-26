<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta content="IE=edge" http-equiv="X-UA-Compatible">
	<meta content="width=device-width, initial-scale=1" name="viewport">
	<title>Bin Weevils Rewritten – Register</title>
	<link href="../assets/images/weevil.png" rel="shortcut icon" type="image/x-icon">
	<meta content="../assets/images/logo.png" property="og:image">
	<meta content="Bin Weevils Rewritten - The newly and improved Bin Weevils, bringing back the stuff that you love!" name="description">
	<meta content="games, free online games, games, kids games, racing games, multiplayer games, maths games, virtual pets, pets, competitions, videos, bin, weevils, wivles, benwivles, bin weevils, bin weevils rewritten, bwr, bwrewritten" name="keywords">
	<meta content="#22b305" name="theme-color">
	<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet"><!-- Bulma Version 0.9.0-->
	<link href="https://unpkg.com/bulma@0.9.0/css/bulma.min.css" rel="stylesheet">
	<link href="../assets/css/style.css" rel="stylesheet" type="text/css">
	<link href="https://unpkg.com/bulma-modal-fx/dist/css/modal-fx.min.css" rel="stylesheet">
	<script src="https://kit.fontawesome.com/4a71c0ba56.js">
	<link href="//cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@3/dark.css" rel="stylesheet">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@9/dist/sweetalert2.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js?render=explicit" async defer></script>
	</script>
	<script data-ad-client="ca-pub-9438037613750689" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
	<style type="text/css">
      #recaptcha > div {
      width: auto !important;
      margin-bottom: .5em;
    }
  </style>
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
	<div class="central-container" id="container-content-play">
		<img alt="Make a Bin Weevil to Play Free Online Games for Kids" class="img-responsive img-align-center" src="../assets/images/register/create_your_bin_weevil.png"> <img alt="" class="bg-img" src="../assets/images/register/bg.png">
		<form action="#" id="create-new-weevil" method="post" name="create-new-weevil">
			<img alt="" class="label-username" src="../assets/images/register/username.png" style="position: absolute;"> <input class="registration-input top-input" data-ok="false" id="userID" name="userID" type="text"> <!--Username Errors-->
			 <span class="be-safe">Be safe - don't use your real name</span> <span class="span span-username" id="span-username-error" style="display: none;">Username &amp; password must be different</span> <!--End of Username Errors-->
			 <!--Username Alternatives-->
			<div id="alternatives"></div><!--End of Username Alternatives-->
			<!--Error-->
			<div id="errors"></div><!--End of Error-->
			<img alt="" class="label-password" src="../assets/images/register/password.png" style="position: absolute;"> <input autocomplete="off" class="registration-input bottom-input showpassword" data-ok="false" id="password" name="password" type="password"> <!--Password Errors-->
			 <span class="span span-password invalid-password" style="display: none;">Invalid password</span> <span class="span span-password four-or-more" style="display: none;">Please enter four or more characters</span> <span class="span span-password fifteen-or-less" style="display: none;">Please enter fifteen or less characters</span> <span class="span span-password different" id="span-password-different" style="display: none;">Username &amp; password must be different</span> <!--End of Password Errors-->
			 <a class="green-button button-play" id="play-green"><img alt="" onclick="submitTheForm()" class="img-hover" data-img-over="../assets/img/play/create-new-weevil/play3.png" id="play-button" src="../assets/images/register/play1.png"></a> <a class="green-button button-play" id="play-grey"><img alt="" id="play-button" src="../assets/images/register/play2.png"></a>
			 <input type="hidden" name="recaptcha_response" id="recaptchaResponse">

			 <script type="text/javascript">
			 async function submitTheForm() {
      var userID = document.getElementById("userID");
      var password = document.getElementById("password");
      var recaptchaResponse = document.getElementById('recaptchaResponse');

      if(userID.value == "" || password.value == "") {
        Swal.fire(
          'Error',
          'Please fill out all fields!',
          'error'
        );
      }
      else {
        var validated = false;
            await Swal.fire({
                title: 'Human Verification',
                html: '<div id="recaptcha"></div>',
                onOpen: function() {
                    grecaptcha.render('recaptcha', {
                    'sitekey': '6LcvFZAaAAAAAK8t98bN7si9lzUtOWiv5NaOpwKv',
                    'theme' : 'dark'
                  });
                },
                confirmButtonText: 'Submit',
                showCancelButton: true,
                allowOutsideClick: false,
                preConfirm: function () {
                  if (grecaptcha.getResponse().length === 0) {
                    Swal.showValidationMessage(`Please verify that you're not a robot`);
                    validated = false;
                  }
                  else{
                      recaptchaResponse.value = grecaptcha.getResponse();
                      validated = true;
                  }
                }
            });
            
            if(!validated) { return; }

            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                                if (this.readyState == 4 && this.status == 200) {
                                    if(this.responseText.includes('responseCode=2'))
                                    {
                                        Swal.fire(
                                            'Error',
                                            'There was a problem with creating your account. Please check all information and try again.',
                                            'error'
                                            );
                                    }
                                    else if(this.responseText.includes('responseCode=3')) {
                                      Swal.fire(
                                            'Error',
                                            'Sorry, this account is either reserved or already exists!',
                                            'error'
                                            );
                                    }
                                    else if(this.responseText.includes('responseCode=999')) {
                                      Swal.fire(
                                            'Error',
                                            'An error has occurred!',
                                            'info'
                                            );
                                    }
                                    else if(this.responseText.includes('Please download the latest build')) {
                                      window.location.replace("https://play.bwrewritten.com/game.php");
                                    }
                                    else {
                                      Swal.fire(
                                            'Information',
                                            this.responseText,
                                            'info'
                                            );
                                    }
                                }
                            };
                            xhttp.open("POST", "create-new-weevil.php");
                            xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                            xhttp.send("userID=" + userID.value + "&password=" + password.value + "&recap=" + recaptchaResponse.value);
      }
    }
			 </script>
		</form>
	</div>
	<div class="box cta">
		<p class="has-text-centered"></p>
		<nav class="level">
			<p class="level-item has-text-centered"><a class="link is-info">Terms</a></p>
			<p class="level-item has-text-centered"><a class="link is-info">Contact Us</a></p>
			<p class="level-item has-text-centered"><img alt="" src="../assets/images/weevil.png" style="height: 30px;"></p>
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
	             .central-container {
	               position: relative;
	               width: 941px;
	               margin: 0 auto;
	             }
	             .img-align-center {
	               margin: 0 auto;
	               display: block;
	             }
	             .bg-img {
	   margin-top: 10px;
	   margin-left: 1px;
	}
	.label-username {
	   top: 171px;
	   left: 445px;
	}
	.top-input {
	   top: 200px;
	   right: 195px;
	}
	.registration-input {
	   position: absolute;
	   font-size: 20px;
	   width: 300px;
	   padding: 8px 45px 8px 8px;
	   border-radius: 10px;
	   border: solid 3px #2CAAD7;
	   background-color: #ecf7ff;
	}
	.icon-username {
	   position: absolute;
	   top: 203px;
	   right: 198px;
	}
	#error-username {
	   top: 208px;
	   right: 200px;
	}
	.be-safe {
	   position: absolute;
	   top: 254px;
	   right: 223px;
	   color: darkblue;
	   font-weight: bolder;
	   font-size: 14px;
	   font-family: Burbank Small Bold;
	}
	#span-username-error {
	   right: 127px;
	   width: 368px;
	}
	.bottom-input {
	   top: 310px;
	   right: 195px;
	}
	.label-password {
	   top: 282px;
	   left: 445px;
	}
	.button-play {
	   top: 400px;
	   right: 245px;
	}
	.green-button {
	   position: absolute;
	}
	.box.cta {
	   margin-top: 50px;
	}
	</style>
</body>
</html>