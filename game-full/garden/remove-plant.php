<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $plantID = $_POST['plantID'];
    $hash = $_POST['hash'];
    $st = $_POST['st'];
    $itemData = getWeevilGardenItemsByItemId($_COOKIE['weevil_name'], $plantID);

    if($itemData != null && checkHash(["hash" => $hash, "plantID" => $plantID, "st" => $st])) {
        if($itemData["isInGarden"] == 1) {
            $itemConfig = getSeedDataById($itemData["itemid"]);

            if($itemConfig["category"] == 1) {
                deleteGardenItemFromInventory($_COOKIE['weevil_name'], $plantID);
                deleteGardenItemFromInventory($_COOKIE['weevil_name'], $plantID);
            }
            else
            removePlantFromGarden($_COOKIE['weevil_name'], $plantID);
        }
    }
}
?>