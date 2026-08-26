<?php
error_reporting(E_ALL);
include('../essential/backbone.php');

if(isset($_POST)) {
    $weevilName = $_COOKIE['weevil_name'];
    $gameId = $_POST['gameID'];
    $score = $_POST['score'];
    $weevilStats = getAllWeevilStatsByName($weevilName);
    $curTime = time();
    if($score > 45000 || $score < 0){
        echo "res=999";
    }
    else{
        if(hasUserPlayedGame($weevilName, $gameId) == true){
            $game = getUserGameData($weevilName, $gameId);
            if($game != false){
                if(intval($curTime) > intval($game['lastplayed'] + (1 * 1 * 5 * 60))){
                    if(intval($score) > intval($game['total'])){
                        setNewHighscore($weevilName, $gameId, $score);
                    }
                    addMulchByName($weevilName, 50);
                    addExperienceByName($weevilName, 30);
                    playGame($weevilName, $gameId, strval($curTime));
                    echo "result=1&mulchEarned=50&xpEarned=30";
                }
                else {
                    if(intval($score) > intval($game['total'])){
                        setNewHighscore($weevilName, $gameId, $score);
                    }
                    playGame($weevilName, $gameId, $game['lastplayed']);
                    echo "result=3";
                }
            }
            else echo "failed getting user game data";
        }
        else if(createUserGame($weevilName, $gameId) == true){
            setNewHighscore($weevilName, $gameId, $score);
            addMulchByName($weevilName, 50);
            addExperienceByName($weevilName, 30);
            playGame($weevilName, $gameId, strval($curTime));
            echo "result=1&mulchEarned=50&xpEarned=30";
        }
        else echo "res=999";
    }
}
else echo "res=999";
?>