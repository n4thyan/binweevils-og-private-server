<?php
error_reporting(0);
include('../essential/backbone.php');

if($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
    $mushroomType = intval($_POST['mushroomType']);
    $total = intval($_POST['total']);
    $hash = $_POST['hash'];
    $st = $_POST['st'];

    if(!checkHash(["hash" => $hash, "mushroomType" => $mushroomType, "total" => $total, "st" => $st]) || !confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']) || $total < 0) {
        echo 'res=999';
        return;
    }

    $weevilStats = getAllWeevilStatsByName($_COOKIE['weevil_name']);
    $mushroomData = getMushroomData($mushroomType);
    $idx = $weevilStats['id'];
    checkForExistingMushroomData($idx, $mushroomType);

    if(canMushroomBeClaimed($mushroomType, $total)) {
        $mushroomRewardData = getMushroomRewardData($idx, $mushroomType);
        $timeUntil = json_decode(time_until(time(), $mushroomRewardData['lastClaimed']));

        if($timeUntil->seconds <= 0) {
            setNewMushroomRewardTime(strtotime('+10 seconds'), $mushroomType, $idx);
            if($mushroomData['rewardType'] == "mulch") {
                addMulchByName($weevilStats['username'], $total);
                echo 'res=0&mulch=' . strval($weevilStats['mulch'] + $total) . '&err=1&x=y';
            }
            else if($mushroomData['rewardType'] == "xp") {
                addExperienceByName($weevilStats['username'], $total);
                echo 'res=0&xp=' . strval($weevilStats['xp'] + $total) . '&err=1&x=y';
            }
        }
        else echo 'res=0&err=20&x=y';
    }
    else echo 'res=999';
}
else echo 'res=999';
?>
