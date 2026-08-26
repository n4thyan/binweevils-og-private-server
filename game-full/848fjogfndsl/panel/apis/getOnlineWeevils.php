<?php
error_reporting(0);
include('../../../essential/backbone.php');
include('../../../essential/admin_guard.php');
enforceAdminAccess();


if(isset($_GET)) {
    $sock = new sock("127.0.0.1", 9339);

    if($sock->ConnectToSocket()) {
        $online = $sock->OnlineWeevils();
        $sock->CloseSocket();
        echo $online;
    }
    else echo json_encode(["responseCode" => 999, "message" => "could not connect to servers."]);
}
else echo json_encode(["responseCode" => 999, "message" => "error occured."]);
?>