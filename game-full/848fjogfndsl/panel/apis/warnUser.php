<?php
session_start();
error_reporting(0);
include('../../../essential/backbone.php');
include('../../../essential/admin_guard.php');
enforceAdminAccess();


if(isset($_GET)) {
    $adminName = $_SESSION['admin'];
    $adminPassword = $_SESSION['adminToken'];
    $weevilName = $_GET['weevilName'];
    $modMsg = $_GET['modMsg'];

    if(!empty($adminName) && !empty($adminPassword) && !empty($weevilName) && !empty($modMsg)) {
        $sock = new sock("127.0.0.1", 9339);

        if($sock->ConnectToSocket()) {
            $sock->SendRawPackets("%xt%login%7#300%-1%$adminName%$adminPassword%$weevilName%$modMsg%");
            $sock->CloseSocket();

            logAdminAction($adminName, $weevilName, strval(time()), 0);
        }
        else echo json_encode(["responseCode" => 999, "message" => "could not connect to servers."]);
    }
    else echo json_encode(["responseCode" => 999, "message" => "error occured."]);
}
else echo json_encode(["responseCode" => 999, "message" => "error occured."]);
?>