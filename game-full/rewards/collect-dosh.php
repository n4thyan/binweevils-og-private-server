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

    if(intval($total) > 1100)
    echo 'res=0&err=20&x=y';
    else if(checkHash(["hash" => $hash, "locID" => $locID, "idx" => $idx, "total" => $total, "st" => $st]) || checkHash(["hash" => $hash, "idx" => $idx, "total" => $total, "st" => $st])) {
        switch(intval($locID)) {
            case 115:
                $castleData = $rewardData['castleMulch'];
                $timeUntil = json_decode(time_until(time(), $castleData));

                if($timeUntil->minutes <= 0 && $timeUntil->seconds <= 0) {
                    setNewRewardTimeGam(strtotime('+2 minutes', time()), $weevil["id"]);
                    echo rewardCollectDosh($total);
                }
                else
                echo 'res=0&err=20&x=y';
                return;
            case 169:
                $doshData = $rewardData['doshMulch'];
                $timeUntil = json_decode(time_until(time(), $doshData));

                if($timeUntil->minutes <= 0 && $timeUntil->seconds <= 0) {
                    setNewRewardTimeDoshs(strtotime('+2 minutes', time()), $weevil["id"]);
                    echo rewardCollectDosh($total);
                }
                else
                echo 'res=0&err=20&x=y';
                return;
            default:
                break;
        }

        $mushroomData = $rewardData['flumsMulch'];
        $timeUntil = json_decode(time_until(time(), $mushroomData));

        if($timeUntil->minutes <= 0 && $timeUntil->seconds <= 0) {
            setNewRewardTimeFlumsMulch(strtotime('+1 minutes', time()), $weevil["id"]);
            echo rewardCollectDosh($total);
        }
        else
        echo 'res=0&err=20&x=y';
    }
    else
    echo 'res=999';
    //echo rewardCollectDosh($total);
}
else echo 'res=999';
?>