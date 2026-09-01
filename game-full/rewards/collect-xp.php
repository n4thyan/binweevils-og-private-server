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
if($total <= 0 || $total > 110 || !$hashValid) {
    echo 'res=999';
    return;
}

$weevil = getAllWeevilStatsByName(isset($_COOKIE['weevil_name']) ? $_COOKIE['weevil_name'] : '');
if(!$weevil || (int)$weevil['id'] !== $idx) {
    echo 'res=999';
    return;
}

if($locID === null) {
    echo claimFlumsReward('xp', $total, $idx);
    return;
}

CheckForExistingGameReward($idx);
$rewardData = getAreaRewardData($idx);
if($locID === 194) {
    $timeUntil = json_decode(time_until(time(), $rewardData['flingXp']));
    if($timeUntil->minutes <= 0 && $timeUntil->seconds <= 0) {
        setNewRewardTimeFling(strtotime('+2 minutes', time()), $idx);
        echo rewardCollectXp($total);
    } else echo 'res=0&err=20&x=y';
    return;
}

echo 'res=999';
?>
