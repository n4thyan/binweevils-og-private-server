<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)){
    $gameId = 0;
    $key = generateSessionKey(30);
    $weevilName = $_COOKIE['weevil_name'];
    switch($_POST['trackId']){
        case '1':
            $gameId = 24;
            break;
        case '2':
            $gameId = 23;
            break;
        case '3':
            $gameId = 25;
            break;
    }

    if(hasUserPlayedGame($weevilName, $gameId) == true){
        $game = getUserGameData($weevilName, $gameId);
        if($game != false){
            setNewGameKey($weevilName, $gameId, $key);
            echo 'res=1&key='.$key;
        }
        else echo "res=999";
    }
    else if(createUserGame($weevilName, $gameId, $key) == true){
        echo 'res=1&key='.$key;
    }
}
?>