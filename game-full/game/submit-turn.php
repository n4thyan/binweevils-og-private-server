<?php
error_reporting(E_ALL);
include('../essential/backbone.php');

if(isset($_POST)) {
    $weevilName = $_COOKIE['weevil_name'];
    $key = $_POST['key'];
    $gameId = GetMultiplayerDataByKey($key)['game'];
    $result = $_POST['result'];
    $weevilStats = getAllWeevilStatsByName($weevilName);
    $curTime = time();
    $mulchGain = 0;
    $xpGain = 0;
    $key2 = md5(strval(random_int(10, 999999))) . strval(random_int(100,999));

    $win = 0;
    $lose = 0;
    if(hasUserPlayedMultiplayerGame($weevilName, $gameId) == true){
        $game = getUserMultiplayerGameData($weevilName, $gameId);
        $keyData = substr($key, -3);
        if(($keyData >= 100) && ($num <= 999)) {
            if($key == $game['gamekey']){
                if($game != false){
                    if($result == "0"){
                        $xpGain = 10;
                        $mulchGain = 15;
                        $lose = 1;
                    }
                    else{
                        $xpGain = 20;
                        $mulchGain = 35;
                        $win = 1;
                    }
                    if(intval($curTime) > intval($game['lastplayed'] + (1 * 1 * 5 * 60))){
                        setNewGameKeyMultiplayer($weevilName, $gameId, $key2);
                        addMulchByName($weevilName, $mulchGain);
                        addExperienceByName($weevilName, $xpGain);
                        playGameMultiplayer($weevilName, $gameId, strval($curTime), $win, $lose);
                        echo "result=1&mulchEarned=".strval($mulchGain)."&mulch=".strval($weevilStats['mulch']+$mulchGain)."&xpEarned=".strval($xpGain). "&xp=".strval($weevilStats['xp']+$xpGain);
                    }
                    else {
                        playGameMultiplayer($weevilName, $gameId, strval($game['lastplayed']), $win, $lose);
                        echo "result=3";
                    }
                }
                else echo "res=999";
            }
            else echo "res=998";
        }
        else echo "res=997";
    }
    else echo "res=996";
    
}
else echo "res=999";
?>