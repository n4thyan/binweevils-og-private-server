<?php

include('../essential/backbone.php');

if(isset($_COOKIE['weevil_name'])) {
    $weevil = $_COOKIE['weevil_name'];
    $weevilData = getAllWeevilStatsByName($weevil);
    if($weevilData['xp'] >= $weevilData['xp2']){
        $levelled = levelWeevil($weevil);
        if($levelled == true){
            $weevilData = getAllWeevilStatsByName($weevil);
            $alrtMsg = '<a href="event:weevil|'.strval($weevilData['id']).'\">'.$weevil.'</a> has reached level '.strval($weevilData['level']).'!';
            $icon = 'cdn.binw.net/users/o_levelTrophy'.strval($weevilData['level']).'_thumb.swf';

            rewardUserTrophy($weevil, $weevilData['id'], $weevilData['level']);
            sendAlert($weevil, $alrtMsg, $icon, time());
            $st = strval(rand(1000000, 9999999));
            echo "level=".$weevilData['level']."&mulch=".$weevilData['mulch']."&xp=".$weevilData['xp']."&xp1=".$weevilData['xp1']."&xp2=".$weevilData['xp2']."&st=" . $st . "&hash=" . md5('P07aJK8soogA815CxjkTcA==' . $weevilData['level'] . $weevilData['mulch'] . $weevilData['xp'] . $weevilData['xp1'] . $weevilData['xp2'] . $st) . "&x=y";
        }
        else echo 'res=999';
    }
    else{
        echo "res=999";
    }
}
// level=3&mulch=560&xp=71&xp1=60&xp2=90&st=3755600&hash=744621c518d28dc7b54ac30629e0177f&x=y
?>