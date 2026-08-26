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
    $duration = $_GET['duration'];
    $reason = $_GET['banReason'];
    if(!empty($adminName) && !empty($adminPassword) && !empty($weevilName) && !empty($duration)) {
        $sock = new sock("127.0.0.1", 9339);

        if($sock->ConnectToSocket()) {
            $sock->SendRawPackets("%xt%login%7#100%-1%$adminName%$adminPassword%$weevilName%$duration%");
            $sock->CloseSocket();

            logAdminActionBans($adminName, $weevilName, strval(strtotime('+' . $duration . ' days', time())), strval(time()), 3, $reason);
        }
        else echo json_encode(["responseCode" => 999, "message" => "could not connect to servers."]);
    }
    else echo json_encode(["responseCode" => 999, "message" => "error occured."]);
}
else echo json_encode(["responseCode" => 999, "message" => "error occured."]);
?>