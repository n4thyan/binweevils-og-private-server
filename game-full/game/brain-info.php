<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
    $err = null;
    $modes = null;
    $levels = null;

    try {
        $weevilData = getAllWeevilStatsByName($_COOKIE['weevil_name']);

        if($weevilData != null) {
            $levels = getStrainLevels($weevilData["id"]);
            $userGameData = getSinglePlayerUserData($weevilData["id"], 4);

            if($userGameData == null) // column hasnt been made for game yet, so they havent played
            $modes = 2;
            else { // column has been made, so check if already played today
                $now = date('Y-m-d');
                $nextDay = date('Y-m-d', strtotime('+1 day', $userGameData["last_played"]));

                if($nextDay > $now) // already played today
                $modes = 1;
                else
                $modes = 2;
            }

            $err = 0;
        }
        else {
            echo 'res=999';
            return;
        }
    }
    catch(Exception $e) {
        $err = -1;
    }

    echo 'modes=' . $modes . '&levels=' . $levels . '&err=' . $err;
}
else
echo 'res=999';
?>