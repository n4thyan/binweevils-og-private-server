<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
    $loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

    if($loggedIn == true) {
        $oo5 = 0;
        /*$sock = new sock("localhost", 9339);
        if($sock->ConnectToSocket()) {
            var_dump("hi");
            $online = $sock->OnlineWeevils();
            $online = json_decode(substr($online, 0, -1));
            $online = $online->{ 'weevils' };
            $sock->CloseSocket();
            if($online >= 0 && $online < 20)
            $oo5 = 1;
            else if($online >= 20 && $online < 50)
            $oo5 = 2;
            else if($online >= 30 && $online < 100)
            $oo5 = 4;
            else if($online >= 40 && $online < 1000)
            $oo5 = 5;
            else if($online >= 1000) $oo5 = 6;

            echo 'servers=Mulch&ips=127.0.0.1&oo5='.$oo5.'&responseCode=1';
        }*/
        //else echo json_encode(["responseCode" => 999, "message" => "could not connect to servers."]);
        echo 'servers=Mulch&ips=127.0.0.1&oo5='.$oo5.'&responseCode=1';
    }
}
?>