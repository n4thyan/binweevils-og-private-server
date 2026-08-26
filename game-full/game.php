<!DOCTYPE html>
<?php
error_reporting(0);
include('essential/backbone.php');
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

if(!isset($_COOKIE['weevil_name']) || !isset($_COOKIE['sessionId'])) {
    header("Location: ../");
}
else {
    $loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
		
	if($loggedIn == false) {
        header("Location: ../");
    }
}

function getRequestHeaders() {
    $headers = array();
    foreach($_SERVER as $key => $value) {
        if (substr($key, 0, 5) <> 'HTTP_') {
            continue;
        }
        $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
        $headers[$header] = $value;
    }
    return $headers;
}

$headers = getRequestHeaders();
$uag = $headers['User-Agent'];
?>
<html>
<head>
	<meta charset="utf-8">
	<meta content="IE=edge" http-equiv="X-UA-Compatible">
	<meta content="width=device-width, initial-scale=1" name="viewport">
	<title>Bin Weevils Rewritten</title>
	<link href="../assets/images/weevil.png" rel="shortcut icon" type="image/x-icon">
	<meta content="/assets/images/logo.png" property="og:image">
	<meta content="Bin Weevils Rewritten - The newly and improved binweevils, bringing back the stuff that you love!" name="description">
	<meta content="games, free online games, games, kids games, racing games, multiplayer games, maths games, virtual pets, pets, competitions, videos, bin, weevils, wivles, benwivles, bin weevils, bin weevils rewritten, bwr, bwrewritten" name="keywords">
	<meta content="#22b305" name="theme-color">
	<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet"><!-- Bulma Version 0.9.0-->
	<link href="../assets/css/bulma.min.css" rel="stylesheet">
	<link href="../assets/css/game.css?2" rel="stylesheet" type="text/css">
	<link href="../assets/css/modal-fx.min.css" rel="stylesheet">
	    <link rel="stylesheet" href="../discord-invite/discordInvite.css"/>
    <script src="../discord-invite/discordInvite.js?<?php echo time(); ?>"></script>
	<script src="https://kit.fontawesome.com/4a71c0ba56.js">
	</script>
    <script src="https://kit.fontawesome.com/4a71c0ba56.js">
        var ws;
        window.addEventListener("load", function(evt) {
            if (ws) return false;
            startWs();
        });

        function sendToWS(toSend) {
            ws.send(toSend);
        }

        function startWs(){
            ws = new WebSocket("ws://localhost:2087");
            

            ws.onopen = function(evt) {
                console.info("OPEN");
                setInterval(function () {
                    ws.send("ping/pong{}");
                }, 15000);
            };
            ws.onclose = function (evt) {
                console.info("Closed");
            };
            // Log errors
            ws.onerror = function (error) {
                console.log("WebSocket Error " + error);
            };

            // Log messages from the server
            ws.onmessage = function (e) {
                try {
                    document.getElementById("flashContentObject").receiveFromWS(e.data);
                } catch (err) {}
            };
            ws.onclose = function(){
//              var timeout = getRandomInt(3000,30000);
                var timeout = getRandomInt(1000,2000);
                console.log("Closed connection, reconnecting in " + timeout);
                //try to reconnect in 5 seconds
                setTimeout(function(){startWs()}, timeout);
            };
        }
        function getRandomInt(min, max) {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }
    </script>
	<script>
    discordInvite.init({
        inviteCode: 'F9F6zN8RhY',
        title: 'Bin Weevils Rewritten',
    });
    discordInvite.render();
</script>


<style type="text/css">
#flash{
	position:relative;
	top:66px;
	left:20px;
	height:560px;
	width:940px;
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
									<li class="is-active"><a href="http://localhost/">Home</a></li><li><a href="/blog/">Blog</a></li><li><a href="/help/">Help</a></li><li><a href="/topups/">Topups</a></li><li><a href="/legal/">Legal</a></li><li><a href="http://localhost/login/login.php">Logout</a></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</nav>
		</div>
		<script src="../assets/js/bulma.js">
		</script>
	</section>
    </div>
    <div id="contentLogin">
    <?php

            echo '<div id="containerGame" style="height: 650px;"><center><object type="application/x-shockwave-flash" id="flashContentObject" data="/mainDEV663.swf?ver=1" style="width: 940px;height:650px;">
                <param name="movie" value="/mainDEV663.swf?ver=1"/>
                <param name="FlashVars" value="cluster=uk&loginPath=http://localhost/&autoBin=false&zone=" />

                <param name="allowFullScreen" value="true"/>
                <param name="wmode" value="opaque"/>
                <param name="allowScriptAccess" value="always"/>
            </object><center></div>';
    
    ?>
</body>
</html>