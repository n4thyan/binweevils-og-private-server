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
    <div class="bw-game-wrap" id="bw-game-wrap">
        <button type="button" class="bw-game-fs" id="bw-game-fullscreen" aria-label="Fullscreen game" title="Fullscreen">⛶</button>
        <div class="bw-game-viewport">
            <object
                type="application/x-shockwave-flash"
                id="flashContentObject"
                data="<?php echo site_e($flashMovie); ?>"
                style="display:block;width:100%;height:100%;border:0;background:#0b2233;">
                <param name="movie" value="<?php echo site_e($flashMovie); ?>">
                <param name="FlashVars" value="cluster=uk&amp;loginPath=<?php echo site_e($flashLoginPath); ?>&amp;autoBin=false&amp;zone=">
                <param name="allowFullScreen" value="true">
                <param name="wmode" value="opaque">
                <param name="allowScriptAccess" value="always">
            </object>
        </div>
    </div>

    <p class="bw-game-note">Tip: use the ⛶ icon in the corner of the game for fullscreen. Your account, Weevil and progression all live on the Bin Weevils servers.</p>
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

    var fsBtn = document.getElementById('bw-game-fullscreen');
    var fsWrap = document.getElementById('bw-game-wrap');
    if (fsBtn && fsWrap) {
        fsBtn.addEventListener('click', function () {
            var req = fsWrap.requestFullscreen || fsWrap.webkitRequestFullscreen || fsWrap.mozRequestFullScreen || fsWrap.msRequestFullscreen;
            if (req) { try { req.call(fsWrap); } catch (e) {} }
        });
        document.addEventListener('fullscreenchange', function () {
            var on = !!(document.fullscreenElement || document.webkitFullscreenElement);
            fsWrap.classList.toggle('bw-game-wrap--full', on);
        });
    }
}());
</script>

<?php include('site/footer.php'); ?>
