<?php
error_reporting(0);
include('../essential/backbone.php');

if(checkPerishableDailyHarvestAll($_COOKIE['weevil_name']) != 0) {
    echo 'responseCode=2';
    return;
}

$gardenPlantsInUse = getGardenPlantsInUse($_COOKIE['weevil_name']);

if($gardenPlantsInUse != false) {
    $harvestablePlantsArray = [];
    $unix = strval(time());
    $xpEarned = 0;
    $curMulch = 0;

    foreach($gardenPlantsInUse as $plant) {
        $itemConfig = getSeedDataById($plant["itemid"]);

        if($itemConfig["category"] == 1) {
            if(canBeHarvested($plant, $itemConfig))
            array_push($harvestablePlantsArray, ["plantID" => $plant["id"], "mulch" => $itemConfig["mulchYield"], "xp" => $itemConfig["xpYield"]]);
        }
    }

    if(count($harvestablePlantsArray) > 0) {
        $res = harvestPerishablePlantsInGarden($_COOKIE['weevil_name'], $harvestablePlantsArray);
        $xpEarned = $xpEarned + intval(explode('|', $res)[0]);
        $curMulch = (intval(explode('|', $res)[1]) == 0 ? $curMulch : intval(explode('|', $res)[1]));
        updateDailyHarvestTimer($_COOKIE['weevil_name'], $unix, true);
        echo 'responseCode=1&mulch=' . $curMulch . '&xp=' . $xpEarned;
    }
    else
    echo 'responseCode=2';
}
else
echo 'responseCode=2';
?>