<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include('../essential/backbone.php');

if(isset($_POST)) {
    $track = $_POST['trackID'];
    $reward = "";
    $canreward = 0;
    $colour = "";
    $lap1 = $_POST['lap1'];
    $lap2 = $_POST['lap2'];
    $lap3 = $_POST['lap3'];
    $total = $_POST['total'];
    $totaltime = $total;
    $weevilName = $_COOKIE['weevil_name'];
    $weevilStats = getAllWeevilStatsByName($weevilName);

    $stats = getWeevilRaceData($track);
    if($stats['total'] > $total || $stats['total'] == 0){
        setNewHighscore($weevilName, $track, $total, '', $lap1, $lap2, $lap3);
    }
    $total = floor($total / 1000);

    switch($track){
        case '24':
            if(intval($totaltime) < 24000){
                GrantBan(strtotime('+1 day', time()), $weevilStats["id"]);
            }
            else{
                if($stats['bronze'] == 0 && $total <= 45 && $total > 37 && $total > 30){
                    $reward = "Bronze";
                    $canreward = 1;
                    $colour = "12425032";
                    rewardItem($weevilStats["id"], 2166, $colour);
                    setRaceStatus(24, 'bronze');
                }
                if($stats['silver'] == 0 && $total <= 37 && $total > 30){
                    $reward = "Silver";
                    $canreward = 1;
                    $colour = "-1";
                    rewardItem($weevilStats["id"], 2166, $colour);
                    setRaceStatus(24, 'silver');
                }
                if($stats['gold'] == 0 && $total <= 30){
                    $reward = "Gold";
                    $canreward = 1;
                    $colour = "16777136";
                    rewardItem($weevilStats["id"], 2166, $colour);
                    setRaceStatus(24, 'gold');
                }
            }
            break;
    }
    echo "res=1&lap1=".$lap1."&lap2=".$lap2."&lap3=".$lap3."&total=".$total."&hasWonMedal=".strval($canreward)."&medalType=".$reward."&clr=".$colour;
}
else echo 'responseCode=999';

?>