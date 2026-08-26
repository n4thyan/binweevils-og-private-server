<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
    $weevilData = getAllWeevilStatsByName($_COOKIE['weevil_name']);
    $room = $_POST['room'];
    $quests = getQuestsForMissions(($room == "" ? false : true), $weevilData["id"], ($room == "" ? null : $room));

    if($quests != null) {
        $quests = pivot($quests);
        echo http_build_query([
            'responseCode' => 1,
            'idList' => implode('|', $quests['id']),
            'nameList' => implode('|', $quests['name']),
            'pathList' => implode('|', $quests['UIpath']),
            'levelList' => implode('|', $quests['minLevel']),
            'completedList' => implode('|', $quests['complete']),
            'tycoonList' => implode('|', $quests['tycoonOnly']),
            'roomList' => implode('|', $quests['room']),
            'priceList' => implode('|', $quests['price']),
            'scoreBronze' => implode('|', $quests['scoreBronze']),
            'scoreSilver' => implode('|', $quests['scoreSilver']),
            'scoreGold' => implode('|', $quests['scoreGold']),
            'scorePlatinum' => implode('|', $quests['scorePlatinum']),
            'highScore' => implode('|', $quests['highScore'])
        ], 'num', '&');
    }
    else
    echo 'responseCode=2';
}
else
echo 'responseCode=999';
?>