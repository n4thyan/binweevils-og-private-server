<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $key = $_POST['key'];
    $numBeaten = $_POST['numBeaten'];
    $hash = $_POST['hash'];
    $st = $_POST['st'];
    $weevilName = $_COOKIE['weevil_name'];

    if(!checkHash(["hash" => $hash, "key" => $key, "numBeaten" => $numBeaten, "st" => $st])) {
        echo 'res=999';
        return;
    }

    $weevilStats = getAllWeevilStatsByName($weevilName);
    $gameData = getUserGameDataByKey($weevilName, $key);
    //var_dump($gameData);
    $gameId = $gameData['game'];
    $curTime = time();

    $xp =0; $mulch =0;

    switch($numBeaten) {
        case '1':
            $xp = 20;
            $mulch = 30;
            break;
        case '3':
            $xp = 20;
            $mulch = 30;
            break;
        case '2':
            $xp = 20;
            $mulch = 30;
            break;
        default:
            $xp = 8;
            $mulch = 15;
            break;
    }

    if(hasUserPlayedGame($weevilName, $gameId) == true) {
        $game = getUserGameData($weevilName, $gameId);
        if($game != false){
            if(intval($curTime) > intval($game['lastplayed'] + (1 * 1 * 5 * 60))){
                addMulchByName($weevilName, $mulch);
                addExperienceByName($weevilName, $xp);
                playGame($weevilName, $gameId, strval($curTime));
                echo "res=1&mulchEarned=".strval($mulch)."&mulch=".strval($weevilStats['mulch']+$mulch)."&xpEarned=".strval($xp)."&xp=".strval($weevilStats['xp']+$xp);
            }
            else {
                playGame($weevilName, $gameId, strval($curTime));
                echo "res=2";
            }
        }
    }
    else echo "res=99";
    
}
else echo "res=999";

?>