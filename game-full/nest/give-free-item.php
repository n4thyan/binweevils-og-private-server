<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $itemTypeID = $_POST['itemTypeId'];
    $weevilStats = getAllWeevilStatsByName($_COOKIE['weevil_name']);

    if(isItemRewardableById($itemTypeID)) {
        if(doesUserHaveNestItemById($itemTypeID))
        echo 'err=8';
        else if(rewardItem($weevilStats["id"], $itemTypeID))
        echo 'err=1';
        else
        echo 'err=2';
    }
    else {
        $bannedUntil = strval(strtotime('+1 week', time()));

        if(GrantBan($bannedUntil, $weevilStats["id"]))
        echo 'err=999&message=Your account has been temporarily banned due to an exploit attempt.';
        else
        echo 'err=999&message=Nice exploit attempt lol - BWR Team.';
    }
}
else
echo 'err=999';
?>