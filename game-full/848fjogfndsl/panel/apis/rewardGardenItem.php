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
    $itemName = $_GET['itemName'];

    if(!empty($adminName) && !empty($adminPassword) && !empty($weevilName) && !empty($itemName)) {
        if(adminLogin($adminName, $adminPassword)) {
            $weevilData = adminGetWeevilStatsByName($weevilName);
            $itemData = getGardenItemDataByName($itemName);

            if(is_array($weevilData)) {
                if(!empty($itemData)) {
                    if(adminRewardGardenItem($weevilName, $itemData["itemTypeID"])) {
                        logAdminActionItem($adminName, $weevilName, strval(time()), 4, $itemData["name"]);
                        echo '1';
                    }
                    else echo json_encode(["responseCode" => 999, "message" => "could not give player item."]);
                }
                else echo json_encode(["responseCode" => 999, "message" => "item not found."]);
            }
            else echo json_encode(["responseCode" => 999, "message" => "weevil not found."]);
        }
        else echo json_encode(["responseCode" => 999, "message" => "error occured."]);
    }
    else echo json_encode(["responseCode" => 999, "message" => "error occured."]);
}
else echo json_encode(["responseCode" => 999, "message" => "error occured."]);
?>