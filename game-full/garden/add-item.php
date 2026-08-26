<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $itemID = $_POST['itemID'];
    $locID = $_POST['locID'];
    $x = urldecode($_POST['x']);
    $z = urldecode($_POST['z']);
    $hash = $_POST['hash'];
    $st = $_POST['st'];

    if(!checkHash(["hash" => $hash, "itemID" => $itemID, "locID" => $locID, "x" => $x, "z" => $z, "st" => $st])) {
        echo 'res=999';
        return;
    }

    if($x != "0" && $z != "0") {
        if(checkXZForGardenItem($_COOKIE['weevil_name'], $x, $z) == 0) {
            $itemsInUse = getGardenItemsInUse($_COOKIE['weevil_name']);
            if(count($itemsInUse) >= 500) {
                echo 'res=999&message=You have too many plants in your garden already.&xp=0';
                return;
            }

            $plantsInUse = getGardenPlantsInUse($_COOKIE['weevil_name'], false);
            $itemData = getWeevilGardenItemsByItemId($_COOKIE['weevil_name'], $itemID);
    
            if($itemData != null) {
                if($itemData["isInGarden"] != 1) {
                    $itemConfig = getGardenItemConfigById($itemData["itemid"]);
    
                    if($itemConfig != false) {

                        if(strtolower(substr($itemConfig["configLocation"], 0, 5)) == "fence" || strpos($itemConfig["configLocation"], 'fence') !== false)
                        addItemToGarden($_COOKIE['weevil_name'], $itemID, $locID, 0, 0, 0);
                        else {
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

                            addItemToGarden($_COOKIE['weevil_name'], $itemID, $locID, $x, $z, $itemConfig["boundRadius"]);
                        }
                    }
                }
            }
        }
    }
    else {
        $itemsInUse = getGardenItemsInUse($_COOKIE['weevil_name']);
        if(count($itemsInUse) >= 350) {
            echo 'res=999&message=You have too many plants in your garden already.&xp=0';
            return;
        }

        $plantsInUse = getGardenPlantsInUse($_COOKIE['weevil_name'], false);
        $itemData = getWeevilGardenItemsByItemId($_COOKIE['weevil_name'], $itemID);
    
        if($itemData != null) {
            if($itemData["isInGarden"] != 1) {
                $itemConfig = getGardenItemConfigById($itemData["itemid"]);
    
                if($itemConfig != false) {
                    if(strtolower(substr($itemConfig["configLocation"], 0, 5)) == "fence" || strpos($itemConfig["configLocation"], 'fence') !== false)
                    addItemToGarden($_COOKIE['weevil_name'], $itemID, $locID, 0, 0, 0);
                    else {
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

                        addItemToGarden($_COOKIE['weevil_name'], $itemID, $locID, $x, $z, $itemConfig["boundRadius"]);
                    }
                }
            }
        }
    }
}
?>