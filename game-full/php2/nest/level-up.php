<?php

include('../essential/backbone.php');

if(isset($_COOKIE['weevil_name'])) {
    $weevil = $_COOKIE['weevil_name'];
    $weevilData = getAllWeevilStatsByName($weevil);
    if($weevilData['xp1'] >= $weevilData['xp2']){
        // levelWeevil now grants EVERY due level (multi-level catch-up, ROADMAP
        // §9.1) and awards each level's trophy + alert internally. Here we only
        // re-read the final state and echo it (the displayed hash is computed
        // from the actual stored fields, so it stays consistent).
        $levelled = levelWeevil($weevil);
        if($levelled == true){
            $weevilData = getAllWeevilStatsByName($weevil);
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
