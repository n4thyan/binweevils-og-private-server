<?php
include('site/bootstrap.php');

if(!$siteLoggedIn || !is_array($siteUser)) {
    header('Location: /#login');
    exit;
}

$sitePageTitle = 'Play';
$siteActive = 'play';
$flashMovie = isset($siteConfig['flash_movie']) ? (string)$siteConfig['flash_movie'] : '/mainDEV663.swf?ver=1';
$flashLoginPath = isset($siteConfig['flash_login_path']) ? (string)$siteConfig['flash_login_path'] : 'http://localhost/';
$websocketUrl = isset($siteConfig['websocket_url']) ? (string)$siteConfig['websocket_url'] : 'ws://localhost:2087';
include('site/header.php');
?>

<section class="bw-game-page">
    <div class="bw-game-head">
        <div>
            <p class="bw-eyebrow">Play</p>
            <h1 class="bw-section-title" style="margin-bottom:4px;">Enter the Bin</h1>
            <p class="bw-section-intro" style="margin:0;">Logged in as <span data-account-stat="username"><?php echo site_e($siteUser['username']); ?></span> · Level <span data-account-stat="level"><?php echo (int)$siteUser['level']; ?></span> · Prestige <span data-account-stat="prestige"><?php echo (int)$siteUser['prestige_count']; ?></span></p>
        </div>
        <div class="bw-button-row">
            <a class="bw-button bw-button--green bw-button--small" href="/download/">Desktop client</a>
            <a class="bw-button bw-button--blue bw-button--small" href="/settings/">My Weevil</a>
        </div>
    </div>

    <div class="bw-game-frame">
        <span class="bw-game-frame-trim" aria-hidden="true"></span>
        <div class="bw-game-viewport">
            <object
                type="application/x-shockwave-flash"
                id="flashContentObject"
                data="<?php echo site_e($flashMovie); ?>"
                style="display:block;width:100%;height:100%;border:0;background:#dff4fb;">
                <param name="movie" value="<?php echo site_e($flashMovie); ?>">
                <param name="FlashVars" value="cluster=uk&amp;loginPath=<?php echo site_e($flashLoginPath); ?>&amp;autoBin=false&amp;zone=">
                <param name="allowFullScreen" value="true">
                <param name="wmode" value="opaque">
                <param name="allowScriptAccess" value="always">
            </object>
        </div>
    </div>

    <p class="bw-game-note">The game renderer keeps its original proportions. Use the in-game controls for play; the website only frames the client.</p>
</section>

<script>
(function () {
    var ws = null;
    var reconnectTimer = null;
    var pingTimer = null;
    var websocketUrl = <?php echo json_encode($websocketUrl); ?>;

    function getRandomInt(min, max) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }

    function startWs() {
        if (ws && (ws.readyState === WebSocket.OPEN || ws.readyState === WebSocket.CONNECTING)) return;

        ws = new WebSocket(websocketUrl);

        ws.onopen = function () {
            if (pingTimer) clearInterval(pingTimer);
            pingTimer = setInterval(function () {
                if (ws && ws.readyState === WebSocket.OPEN) ws.send('ping/pong{}');
            }, 15000);
        };

        ws.onerror = function (error) {
            console.log('WebSocket Error', error);
        };

        ws.onmessage = function (event) {
            try {
                document.getElementById('flashContentObject').receiveFromWS(event.data);
            } catch (err) {}
        };

        ws.onclose = function () {
            if (pingTimer) clearInterval(pingTimer);
            var timeout = getRandomInt(1000, 2000);
            clearTimeout(reconnectTimer);
            reconnectTimer = setTimeout(startWs, timeout);
        };
    }

    window.sendToWS = function (payload) {
        if (ws && ws.readyState === WebSocket.OPEN) ws.send(payload);
    };

    window.addEventListener('load', startWs);
}());
</script>

<?php include('site/footer.php'); ?>
