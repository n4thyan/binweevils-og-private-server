<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $user = getAllWeevilStatsByName($_COOKIE['weevil_name']);
    $collectedSeeds = removeEmptyValuesFromArray(explode(",", $_POST['seedList']));
    $seeds = array(180,179,177,176,178,175,129);
    $idx = $_POST['idx'];
    $seedList = $_POST['seedList'];
    $hash = $_POST['hash'];
    $st = $_POST['st'];
    CheckForExistingGameReward($user["id"]);
    $seedRewardData = getSeedRewardData($user["id"])['tinkSeed'];

    if(checkHash(["hash" => $hash, "idx" => $idx, "seedList" => $seedList, "st" => $st])) {
        $timeUntil = json_decode(time_until(time(), $seedRewardData));

        if($timeUntil->days <= 0 && $timeUntil->hours <= 0 && $timeUntil->minutes <= 0 && $timeUntil->seconds <= 0) {
            setNewRewardTimeTinks(strtotime('+1 day', time()), $user["id"]);
    
            foreach($collectedSeeds as $seed) {
                if(!in_array(intval($seed), $seeds)) {
                    GrantBan(strtotime('+1 day', time()), $user["id"]);
                    echo 'err=20';
                    return;
                } 
            }
    
            foreach($collectedSeeds as $seed) {
                rewardSeed(intval($seed), 1);
            }
    
            echo 'err=1';
        }
        else echo 'err=20';
    }
    else echo 'err=20';
}
else echo 'err=20';

function sortData($data) {
    return substr($data, 0, -1);
}
?>