<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $plantID = $_POST['plantID'];
    $x = urldecode($_POST['x']);
    $z = urldecode($_POST['z']);
    $hash = $_POST['hash'];
    $st = $_POST['st'];

    if(checkHash(["hash" => $hash, "plantID" => $plantID, "x" => $x, "z" => $z, "st" => $st]) && checkXZForPlant($_COOKIE['weevil_name'], $x, $z) == 0) {
        $plantsInUse = getGardenPlantsInUse($_COOKIE['weevil_name'], false);
        if(count($plantsInUse) >= 300) {
            echo 'res=999&message=You have too many plants in your garden already.&xp=0';
            return;
        }

        $itemsInUse = getGardenItemsInUse($_COOKIE['weevil_name']);
        $itemData = getWeevilGardenItemsByItemId($_COOKIE['weevil_name'], $plantID);
        $seedConfig = getSeedConfigById($itemData["itemid"]);
        
        if($itemData != null && $seedConfig != false) {
            if($itemData["isInGarden"] != 1) {
                foreach($plantsInUse as $plant) {
                    if($plant[0] != $itemData["id"] && $plant[10] != 0) {
                        //echo strval((intval($x) - $plant[8]) * (intval($x) - $plant[8]) + (intval($z) - $plant[9]) * (intval($z) - $plant[9])) . ' (' . ($plant[10] * $plant[10]) . ') ';
                        if((intval($x) - $plant[8]) * (intval($x) - $plant[8]) + (intval($z) - $plant[9]) * (intval($z) - $plant[9]) <=
                        $plant[10] * $plant[10]) {
                            echo 'res=999&message=Nice try&xp=0';
                            return;
                        }
                    }
                }

                foreach($itemsInUse as $item) {
                    if($item[0] != $itemData["id"] && $item[10] != 0) {
                        //echo strval((intval($x) - $item[8]) * (intval($x) - $item[8]) + (intval($z) - $item[9]) * (intval($z) - $item[9])) . ' (' . ($item[10] * $item[10]) . ') ';
                        if((intval($x) - $item[8]) * (intval($x) - $item[8]) + (intval($z) - $item[9]) * (intval($z) - $item[9]) <=
                        $item[10] * $item[10]) {
                            echo 'res=999&message=Nice try&xp=0';
                            return;
                        }
                    }
                }

                echo addPlantToGarden($_COOKIE['weevil_name'], $plantID, $x, $z, $seedConfig["radius"]);
            }
            else
            echo 'res=999&message=Nice try&xp=0';
        }
        else echo 'res=999&message=Could not add plant to garden.&xp=0';
    }
    else echo 'res=999&message=Nice try&xp=0';
}
else echo 'res=999&message=Nice try&xp=0';
?>