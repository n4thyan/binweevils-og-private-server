<?php
//error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $leaderBoardData = "";
    $game = $_POST['gameId'];
    $weevilData = getFriendGameLeaderboard($game);

    foreach($weevilData as $weevil){
        $weevilDef = getAllWeevilStatsByName($weevil[1])['def'];
        $av = $weevil[4] == 0 ? '0':round($weevil[4]/$weevil[3]);
        $leaderBoardData .= '{"userWeevilID":"'.$weevil[1].'","numPlays":"'.$weevil[3].'","averageScore":"'.$av.'","weevilDef":"'.$weevilDef.'","bestScore":"'.$weevil[4].'"},';
    }
    echo '{"responseCode":"1","scores":['.sortData($leaderBoardData).']}'; 
}
else echo 'responseCode=999';

function sortData($data){
    return substr($data, 0, -1);
}
?>