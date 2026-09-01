<?php
error_reporting(0);
include('../../essential/backbone.php');
include('../../essential/room-event-rewards.php');

if($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['mushroomType'], $_POST['total'], $_POST['hash'], $_POST['st'])) {
    echo 'res=999';
    return;
}

$mushroomType = (int)$_POST['mushroomType'];
$total = (int)$_POST['total'];
$hash = (string)$_POST['hash'];
$st = (string)$_POST['st'];
if(!checkHash(["hash" => $hash, "mushroomType" => $_POST['mushroomType'], "total" => $_POST['total'], "st" => $_POST['st']])) {
    echo 'res=999';
    return;
}

$weevil = getAllWeevilStatsByName(isset($_COOKIE['weevil_name']) ? $_COOKIE['weevil_name'] : '');
$mushroom = getMushroomData($mushroomType);
if(!$weevil || !$mushroom || (int)$mushroom['rewardAmount'] !== $total) {
    echo 'res=999';
    return;
}

echo claimFlumsReward($mushroom['rewardType'], $total, (int)$weevil['id']);
?>
