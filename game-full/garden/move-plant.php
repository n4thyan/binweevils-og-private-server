<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $plantID = $_POST['plantID'];
    $x = urldecode($_POST['x']);
    $z = urldecode($_POST['z']);

    if(checkXZForPlant($_COOKIE['weevil_name'], $x, $z) == 0) {
        $plantsInUse = getGardenPlantsInUse($_COOKIE['weevil_name']);
        $itemsInUse = getGardenItemsInUse($_COOKIE['weevil_name']);
        $itemData = getWeevilGardenItemsByItemId($_COOKIE['weevil_name'], $plantID);

        if($itemData != null) {
            if($itemData["isInGarden"] == 1) {
                $itemConfig = getSeedDataById($itemData["itemid"]);

                foreach($plantsInUse as $plant) {
                    if($plant[0] != $itemData["id"] && $plant[10] != 0) {
                        //echo strval((intval($x) - $plant[8]) * (intval($x) - $plant[8]) + (intval($z) - $plant[9]) * (intval($z) - $plant[9])) . ' (' . ($plant[10] * $plant[10]) . ') ';
                        if((intval($x) - $plant[8]) * (intval($x) - $plant[8]) + (intval($z) - $plant[9]) * (intval($z) - $plant[9]) <=
                        $plant[10] * $plant[10]) return;
                    }
                }

                foreach($itemsInUse as $item) {
                    if($item[0] != $itemData["id"] && $item[10] != 0) {
                        //echo strval((intval($x) - $item[8]) * (intval($x) - $item[8]) + (intval($z) - $item[9]) * (intval($z) - $item[9])) . ' (' . ($item[10] * $item[10]) . ') ';
                        if((intval($x) - $item[8]) * (intval($x) - $item[8]) + (intval($z) - $item[9]) * (intval($z) - $item[9]) <=
                        $item[10] * $item[10]) return;
                    }
                }

                if($itemConfig != false)
                if($itemConfig["category"] != 1)
                movePlantInGarden($_COOKIE['weevil_name'], $plantID, $x, $z, $itemConfig["radius"]);
            }
        }
    }
}
?>