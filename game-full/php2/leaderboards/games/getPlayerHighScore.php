<?php
error_reporting(0);
include('../../../essential/backbone.php');

if(isset($_POST)) {
    $gameId = $_POST['gameId'];
    $weevilName = $_COOKIE['weevil_name'];
    $curTime = time();

    if(hasUserPlayedGame($weevilName, $gameId) == true){
        $game = getUserGameData($weevilName, $gameId);
        if($game != false){
            if(intval($curTime) > intval($game['lastplayed'] + (24 * 60 * 60)))
            echo 'responseCode=1&playedToday=0';
            else
            echo 'responseCode=1&playedToday=1';
        }
        else
        echo "responseCode=999&playedToday=1";
    }
    else
    echo 'responseCode=1&playedToday=0';
}
else
echo 'responseCode=999&playedToday=1';
?>