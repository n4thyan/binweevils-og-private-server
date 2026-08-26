<?php
//error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $leaderBoardData = "";
    $weevilData = getFriendXPLeaderboard();

    foreach($weevilData as $weevil){
        $leaderBoardData .= '{"userWeevilID":"'.$weevil[1].'","xp":"'.number_format($weevil[12]).'","level":"'.$weevil[7].'","weevilDef":"'.$weevil[11].'"},';
    }
    echo '{"responseCode":"1","buddies":['.sortData($leaderBoardData).']}'; 
}
else echo 'responseCode=999';

function sortData($data){
    return substr($data, 0, -1);
}
?>