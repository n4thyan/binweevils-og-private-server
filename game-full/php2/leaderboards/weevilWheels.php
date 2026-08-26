<?php
//error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $leaderBoardData = "";
    $gameId = 0;
    switch($_POST['trackNumber']){
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
    $weevilData = getFriendRaceLeaderboard($gameId);

    foreach($weevilData as $weevil){
        $total = sortData($weevil[4]/1000);
        $lap1 = sortData($weevil[5]/1000);
        $lap2 = sortData($weevil[6]/1000);
        $lap3 = sortData($weevil[7]/1000);
        $weevilDef = getAllWeevilStatsByName($weevil[1])['def'];
        $leaderBoardData .= '{"userWeevilID":"'.$weevil[1].'","lap1":"'.$lap1.'","lap2":"'.$lap2.'","lap3":"'.$lap3.'","total":"'.$total.'","weevilDef":"'.$weevilDef.'"},';
    }
    if($leaderBoardData == ""){
        echo '{"responseCode":"2"}';
    }
    else
    echo '{"responseCode":"1","times":['.sortData($leaderBoardData).']}'; 
}
else echo 'responseCode=999';

function sortData($data){
    return substr($data, 0, -1);
}
?>