<!DOCTYPE html>
	<?php
error_reporting(0);
error_reporting(0);
include('essential/backbone.php');

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
	<meta content="Binweevils Rewritten - The newly and improved binweevils, bringing back the stuff that you love!" name="description">
	<meta content="games, free online games, games, kids games, racing games, multiplayer games, maths games, virtual pets, pets, competitions, videos, bin, weevils, wivles, benwivles, bin weevils, bin weevils rewritten, bwr, bwrewritten" name="keywords">
	<meta content="#22b305" name="theme-color">
	<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet"><!-- Bulma Version 0.9.0-->
	<link href="https://unpkg.com/bulma@0.9.0/css/bulma.min.css" rel="stylesheet">
	<link href="../assets/css/game.css" rel="stylesheet" type="text/css">
	<link href="https://unpkg.com/bulma-modal-fx/dist/css/modal-fx.min.css" rel="stylesheet">
	    <link rel="stylesheet" href="../discord-invite/discordInvite.css"/>
    <script src="../discord-invite/discordInvite.js?<?php echo time(); ?>"></script>
	<script src="https://kit.fontawesome.com/4a71c0ba56.js">
	</script>
	<script>
    discordInvite.init({
        inviteCode: 'bWRHynAEZ9',
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
									<li class="is-active">
										<a href="https://play.bwrewritten.com/">Home</a>
									</li>
									<li>
										<a href="/blog/">Blog</a>
									</li>
									<li>
										<a href="/help/">Help</a>
									</li>
									<li>
										<a href="/topups/">Topups</a>
									</li>
									<li>
										<a href="/legal/">Legal</a>
									</li>
	<li>
										<a href="https://play.bwrewritten.com/login/login.php">Logout</a>
									</li>
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
	<h3><?php if (strpos($uag,'Electron') !== false) {echo 'Currently logged in as '.$_COOKIE['weevil_name'];} else echo 'Uh oh!';?></h3>
    </div>
    <div id="contentLogin">
    <?php
        if (strpos($uag,'Electron') !== false) {
            echo '<div id="containerGame" style="background:#fff; width: 940px; height: 650px;"><object type="application/x-shockwave-flash" id="flashContentObject" data="/main_24_01_21.swf?ver=1" style="width: 940px;height:650px">
                <param name="movie" value="/main_22_12_20.swf?ver=1"/>
                <param name="FlashVars" value="cluster=uk&loginPath=http://play.bwrewritten.com&autoBin=false&zone=" />
    
                <param name="allowFullScreen" value="true"/>
                <param name="wmode" value="opaque"/>
                <param name="allowScriptAccess" value="always"/>
            </object></div>';
        }
        else{
            echo '<div id="containerGame" style="background:#fff; height:fit-content!important"><br><br><img class="rigg" width="20%" src="/assets/images/ram.png" alt=""><br>
            <h2>It looks like you are trying to play Bin Weevils Rewritten from the web.</h2><h2><a href="../download/">Please download the latest build of the Bin Weevils Rewritten Application here<a></h2>
            <center>
            <br><img src="/assets/images/needhelp.png" alt=""><br>
            <div id="discordInviteBox" version="1.0">
                <div id="discordInvite" style="width: 400px;">
                    <h5 id="introText" class="noselect loadHidden" style="display: block;">YOUVE BEEN INVITED TO JOIN A SERVER</h5>
                    <div id="discordData">
                        <div id="serverImg" class="discordLink loadHidden" style="background: url(&quot;https://cdn.discordapp.com/icons/783834980744691742/2971df2623b44653a24aaa325889d505.jpg&quot;) 50% 50% / 100% 100% repeat scroll padding-box padding-box rgb(54, 57, 63); display: block;"></div>
                        <div id="discordInfo">
                            <div id="serverNameBox" class="discordLink">
                                <span class="noselect" id="serverName">Bin Weevils Rewritten</span>
                            </div>
                            <div id="status" class="loadHidden" style="display: block;">
                                <div id="statusIndicators" class="noselect">
                                    <i id="onlineInd"></i>
                                    <span id="numOnline"></span>
                                    <i id="offlineInd"></i><span id="numTotal"></span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="discordLink" id="callToAction"><div id="buttonText" class="noselect">Join</div></button>
                    </div>
                </div>
            </div></center><br></div>';
        }
    
    ?>
    <?php
if (strpos($uag,'Electron') !== true) {
    echo '<script type="text/javascript">
        var ws;
        window.addEventListener("load", function(evt) {
            if (ws) return false;
            startWs();
        });

        function sendToWS(toSend) {
            ws.send(toSend);
        }

        function startWs(){
            ws = new WebSocket("wss://play.bwrewritten.com:2087");
            

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
    </script>';
}
?>
</body>
</html>