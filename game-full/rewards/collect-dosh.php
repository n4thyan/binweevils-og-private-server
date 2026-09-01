<?php
error_reporting(0);
include('../essential/backbone.php');
include('../essential/room-event-rewards.php');

if($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['idx'], $_POST['total'], $_POST['hash'], $_POST['st'])) {
    echo 'res=999';
    return;
}

$idx = (int)$_POST['idx'];
$total = (int)$_POST['total'];
$locID = isset($_POST['locID']) ? (int)$_POST['locID'] : null;
$hash = (string)$_POST['hash'];
$st = (string)$_POST['st'];

$hashData = ["hash" => $hash, "idx" => $_POST['idx'], "total" => $_POST['total'], "st" => $_POST['st']];
if($locID !== null) $hashData['locID'] = $_POST['locID'];
$hashValid = $locID === null
    ? checkOrderedRewardHash($hash, [$_POST['idx'], $_POST['total'], $_POST['st']])
    : checkHash($hashData);
if($total <= 0 || $total > 1100 || !$hashValid) {
    echo 'res=999';
    return;
}

$weevil = getAllWeevilStatsByName(isset($_COOKIE['weevil_name']) ? $_COOKIE['weevil_name'] : '');
if(!$weevil || (int)$weevil['id'] !== $idx) {
    echo 'res=999';
    return;
}

// The live Flum asset sends no locID. Its reward is authorised by the Node
// event server's short mushrooms.validUntil window and claimed atomically.
if($locID === null) {
    echo claimFlumsReward('mulch', $total, $idx);
    return;
}

CheckForExistingGameReward($idx);
$rewardData = getAreaRewardData($idx);
switch($locID) {
    case 115:
        $timeUntil = json_decode(time_until(time(), $rewardData['castleMulch']));
        if($timeUntil->minutes <= 0 && $timeUntil->seconds <= 0) {
            setNewRewardTimeGam(strtotime('+2 minutes', time()), $idx);
            echo rewardCollectDosh($total);
        } else echo 'res=0&err=20&x=y';
        return;
    case 169:
        $timeUntil = json_decode(time_until(time(), $rewardData['doshMulch']));
        if($timeUntil->minutes <= 0 && $timeUntil->seconds <= 0) {
            setNewRewardTimeDoshs(strtotime('+2 minutes', time()), $idx);
            echo rewardCollectDosh($total);
        } else echo 'res=0&err=20&x=y';
        return;
    default:
        echo 'res=999';
}
?>
