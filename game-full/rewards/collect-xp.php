<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $idx = $_POST['idx'];
    $total = $_POST['total'];
    $locID = $_POST['locID'];
    $hash = $_POST['hash'];
    $st = $_POST['st'];
    $weevil = getAllWeevilStatsByName($_COOKIE['weevil_name']);
    CheckForExistingGameReward($weevil["id"]);
    $rewardData = getAreaRewardData($weevil["id"]);

    if(intval($total) > 110)
    echo 'res=0&err=20&x=y';
    else if(checkHash(["hash" => $hash, "idx" => $idx, "total" => $total, "st" => $st])) {
        switch(intval($locID)) {
            case 194:
                $flingData = $rewardData['flingXp'];
                $timeUntil = json_decode(time_until(time(), $flingData));

                if($timeUntil->minutes <= 0 && $timeUntil->seconds <= 0) {
                    setNewRewardTimeFling(strtotime('+2 minutes', time()), $weevil["id"]);
                    echo rewardCollectXp($total);
                }
                else
                echo 'res=0&err=20&x=y';
                return;
            default:
                break;
        }

        $mushroomData = $rewardData['flumsXp'];
        $timeUntil = json_decode(time_until(time(), $mushroomData));

        if($timeUntil->minutes <= 0 && $timeUntil->seconds <= 0) {
            setNewRewardTimeFlumsXp(strtotime('+1 minutes', time()), $weevil["id"]);
            echo rewardCollectXp($total);
        }
        else
        echo 'res=0&err=20&x=y';
    }
    else
    echo 'res=999';
    //echo rewardCollectXp($total);
}
else echo 'res=999';
?>