<?php
error_reporting(E_ALL);
include('../essential/backbone.php');

if(isset($_POST)) {
    $weevilName = $_COOKIE['weevil_name'];
    $gameId = $_POST['gameId'];
    $weevilStats = getAllWeevilStatsByName($weevilName);
    $key = md5(strval(random_int(10, 999999))) . strval(random_int(100,999));
    if(hasUserPlayedMultiplayerGame($weevilName, $gameId) == true){
        $game = getUserMultiplayerGameData($weevilName, $gameId);
        if($game != false){
            setNewGameKeyMultiplayer($weevilName, $gameId, $key);
            echo 'res=1&key='.$key;
        }
        else echo "failed getting user game data";
    }
    else if(createUserMultiplayerGame($weevilName, $gameId, $key) == true){
        echo 'res=1&key='.$key;
    }
    else echo "res=999";
    
}
else echo "res=999";
?>