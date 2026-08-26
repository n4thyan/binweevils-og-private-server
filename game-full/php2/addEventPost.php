<?php
error_reporting(E_ALL);
include('../essential/backbone.php');

if(isset($_POST)) {
    $score = $_POST['value'];
    $postType = $_POST['eventPostType'];
    $gameId = 0;
    $weevilData = getAllWeevilStatsByName($weevilName);
    $weevilName = $_COOKIE['weevil_name'];
    switch(intval($postType)){
        case 417:
            $gameId = 34;
            if(hasUserPlayedGame($weevilName, $gameId) == true){
                $game = getUserGameData($weevilName, $gameId);
                if($game != false){
                    if(intval($score) > intval($game['total'])){
                        $alrtMsg = '<a href=\"event:weevil|'.strval($weevilData['id']).'\">'.$weevilName.'</a> has hit an all time highscore of '.strval($score).' in Mulch Dig!';
                        $icon = 'bodyAlertIcons/MulchDigg.swf';
                    }
                    else {
                        $alrtMsg = '<a href=\"event:weevil|'.strval($weevilData['id']).'\">'.$weevilName.'</a> has just got a score of '.$score.' in Mulch Dig!';
                        $icon = 'bodyAlertIcons/MulchDigg.swf';
                    }
                }
                else {
                    echo "responseCode=999";
                    return;
                } 
            }
            else if(createUserGame($weevilName, $gameId) == true){
                $alrtMsg = '<a href=\"event:weevil|'.strval($weevilData['id']).'\">'.$weevilName.'</a> has played Mulch Dig for the first time!';
                $icon = 'bodyAlertIcons/MulchDigg.swf';


                setNewHighscore($weevilName, $gameId, $score);
                playGame($weevilName, $gameId, strval($curTime));
                addMulchByName($weevilName, $nscore);
            }
            else {
                echo "responseCode=999";
                return;
            } 
            sendAlert($weevilName, $alrtMsg, $icon, time());
            break;
        
        case 416:
            $gameId = 34;
            if(hasUserPlayedGame($weevilName, $gameId) == true){
                $game = getUserGameData($weevilName, $gameId);
                if($game != false){
                    if(intval($score) > intval($game['total'])){
                        $alrtMsg = '<a href=\"event:weevil|'.strval($weevilData['id']).'\">'.$weevilName.'</a> has found the Rare Gem and hit an all time highscore of '.strval($score).' on Mulch Dig!';
                        $icon = 'bodyAlertIcons/MulchDiggGem.swf';
                    }
                    else{
                        $alrtMsg = '<a href=\"event:weevil|'.strval($weevilData['id']).'\">'.$weevilName.'</a> has found the Rare Gem on Mulch Dig!';
                        $icon = 'bodyAlertIcons/MulchDiggGem.swf';
                    }
                }
                else {
                    echo "responseCode=999";
                    return;
                } 
            }
            else {
                echo "responseCode=999";
                return;
            }
            sendAlert($weevilName, $alrtMsg, $icon, time());
            break;
    }
    echo 'responseCode=1';
}
else echo json_encode(["responseCode" => 999]);
?>