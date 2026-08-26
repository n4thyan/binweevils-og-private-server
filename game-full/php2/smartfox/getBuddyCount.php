<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
    $loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

    if($loggedIn == true) {
        $sock = new sock("127.0.0.1", 9339);

        if($sock->ConnectToSocket()) {
            $sock->SendRawPackets("%xt%login%-1#1%-1%" . $_COOKIE['weevil_name'] . "%");
            $budCount = socket_read($sock->ReturnSocket(), 1024) or die('counts=0');
            $sock->CloseSocket();

            echo 'counts=' . $budCount;
        }
        else
        echo 'counts=0';
    }
    else
    echo 'counts=0';
}
else
echo 'counts=0';
?>