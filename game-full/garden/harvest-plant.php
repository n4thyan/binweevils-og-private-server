<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $plantID = $_POST['plantID'];
    $itemData = getWeevilGardenItemsByItemId($_COOKIE['weevil_name'], $plantID);

    if($itemData != null) {
        $itemConfig = getSeedDataById($itemData["itemid"]);

        if($itemConfig["category"] == 1) {
            if(canBeHarvested($itemData, $itemConfig))
            echo harvestPlantInGarden($_COOKIE['weevil_name'], $plantID, $itemConfig["mulchYield"], $itemConfig["xpYield"], $itemConfig["category"], "0");
            else
            echo 'res=999';
        }
        else {
            if(canBeHarvested($itemData, $itemConfig))
            echo harvestPlantInGarden($_COOKIE['weevil_name'], $plantID, $itemConfig["mulchYield"], $itemConfig["xpYield"], $itemConfig["category"], strval(time()));
            else
            echo 'res=999';
        }
    }
    else echo 'res=999';
}
else
echo 'res=999';
?>