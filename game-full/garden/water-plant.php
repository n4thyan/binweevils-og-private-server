<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $plantID = $_POST['plantID'];
    
    if(checkGardenForItemByTypeId($_COOKIE['weevil_name'], 5)) {
        $itemData = getWeevilGardenItemsByItemId($_COOKIE['weevil_name'], $plantID);

        if($itemData != null) {
            $itemConfig = getSeedDataById($itemData["itemid"]);

            if($itemConfig["category"] == 1) {
                if(!hasFinishedGrowing($itemData, $itemConfig)) {
                    echo 'res=999';
                    return;
                }

                if(hasRotted($itemData, $itemConfig)) {
                    echo 'res=999';
                    return;
                }

                waterPlantInGarden($_COOKIE['weevil_name'], $plantID, strval(time()));
            }
            else
            echo 'res=999';
        }
        else
        echo 'res=999';
    }
    else
    echo 'res=999';
}
?>