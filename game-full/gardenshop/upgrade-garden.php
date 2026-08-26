<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $newSize = $_POST['size'];
    $weevilStats = getNestConfig($_COOKIE['weevil_name']);
    $plotPrices = getGardenPlotPrices($weevilStats['gardenSize']);
    $weevilData = getAllWeevilStatsByName($_COOKIE['weevil_name']);
    
    if($plotPrices != null && $plotPrices[$newSize] != null && intval($newSize) > $weevilStats['gardenSize']) {
        if($weevilStats['mulch'] >= $plotPrices[$newSize]) {
            if(removeMulch($weevilStats['id'], $plotPrices[$newSize]) && upgradeGardenPlot($_COOKIE['weevil_name'], $newSize)) {
                $updatedWeevilStats = getAllWeevilStatsByName($_COOKIE['weevil_name']);
                $alrtMsg = '<a href="event:weevil|'.strval($weevilData['id']).'\">'.$weevilData['username'].'</a> has just upgraded to a new garden size!';
                $icon = 'cdn.binw.net/assetsGarden/scarecrowClott_thumb.swf';
                sendAlert($_COOKIE['weevil_name'], $alrtMsg, $icon, time());
                echo 'err=1&mulch=' . $updatedWeevilStats['mulch'] . '&xp=' . $updatedWeevilStats['xp'];
            }
            else
            echo 'err=999';
        }
        else
        echo 'err=2';
    }
    else
    echo 'err=999';
}
else
echo 'err=999';
?>