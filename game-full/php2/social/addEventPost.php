<?php
error_reporting(E_ALL);
include('../../essential/backbone.php');

if(!isset($_POST)) {
    echo json_encode(["responseCode" => 999]);
    exit;
}

$score = $_POST['value'];
$postType = $_POST['eventPostType'];
$gameId = 34; // Mulch Dig
$weevilName = $_COOKIE['weevil_name'];
$weevilData = getAllWeevilStatsByName($weevilName);
$alrtMsg = '';
$icon = '';
$ok = false;

switch(intval($postType)){
    case 417:
        if(hasUserPlayedGame($weevilName, $gameId) == true){
            $game = getUserGameData($weevilName, $gameId);
            if($game != false){
                if(intval($score) > intval($game['total'])){
                    $alrtMsg = '<a href="event:weevil|'.strval($weevilData['id']).'">'.$weevilName.'</a> has hit an all time highscore of '.strval($score).' in Mulch Dig!';
                    $icon = 'bodyAlertIcons/MulchDigg.swf';
                }
                else {
                    $alrtMsg = '<a href="event:weevil|'.strval($weevilData['id']).'">'.$weevilName.'</a> has just got a score of '.$score.' in Mulch Dig!';
                    $icon = 'bodyAlertIcons/MulchDigg.swf';
                }
                $ok = true;
            }
            else {
                echo 'responseCode=999';
                exit;
            }
        }
        else if(createUserGame($weevilName, $gameId) == true){
            $alrtMsg = '<a href="event:weevil|'.strval($weevilData['id']).'">'.$weevilName.'</a> has played Mulch Dig for the first time!';
            $icon = 'bodyAlertIcons/MulchDigg.swf';
            $curTime = time();
            $nscore = intval($score);
            setNewHighscore($weevilName, $gameId, $score);
            playGame($weevilName, $gameId, strval($curTime));
            addMulchByName($weevilName, $nscore);
            $ok = true;
        }
        else {
            echo 'responseCode=999';
            exit;
        }
        break;

    case 416:
        if(hasUserPlayedGame($weevilName, $gameId) == true){
            $game = getUserGameData($weevilName, $gameId);
            if($game != false){
                if(intval($score) > intval($game['total'])){
                    $alrtMsg = '<a href="event:weevil|'.strval($weevilData['id']).'">'.$weevilName.'</a> has found the Rare Gem and hit an all time highscore of '.strval($score).' on Mulch Dig!';
                    $icon = 'bodyAlertIcons/MulchDiggGem.swf';
                }
                else{
                    $alrtMsg = '<a href="event:weevil|'.strval($weevilData['id']).'">'.$weevilName.'</a> has found the Rare Gem on Mulch Dig!';
                    $icon = 'bodyAlertIcons/MulchDiggGem.swf';
                }
                $ok = true;
            }
            else {
                echo 'res=999';
                exit;
            }
        }
        else {
            echo 'res=999';
            exit;
        }
        break;
}

if($ok && $alrtMsg !== '' && $icon !== '')
    sendAlert($weevilName, $alrtMsg, $icon, time());

echo 'responseCode=1';
?>