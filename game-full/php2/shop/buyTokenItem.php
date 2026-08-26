<?php
error_reporting(0);
include('../../essential/backbone.php');

if($_POST) {
    $itemID = $_POST['itemID'];
    $idx = $_POST['idx'];
    $tokens = $_POST['tokens'];
    $hash = $_POST['hash'];
    $timer = $_POST['timer'];
    $userData = getAllWeevilStatsByName($_COOKIE['weevil_name']);
    $itemData = getItemDataById($itemID);

    if(checkHash(["hash" => $hash, "idx" => $idx, "itemID" => $itemID, "timer" => $timer, "tokens" => $tokens]) && $userData != null && $itemData != null) {
        if(itemCountById($itemID, $userData["id"], "-1") > 800 || $userData["level"] < $itemData["minLevel"])
        echo 'responseCode=999';
        else if(buyTokenItem($userData["id"], $itemID, $itemData["category"], $itemData["configLocation"]))
        echo 'responseCode=1';
        else
        echo 'responseCode=999';
    }
    else
    echo 'responseCode=999';
}
else
echo 'responseCode=999';
?>