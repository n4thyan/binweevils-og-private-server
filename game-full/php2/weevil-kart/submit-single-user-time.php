<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('../../essential/backbone.php');
if(isset($_POST)) {
    $track = $_POST['trackID'];
    $reward = "";
    $canreward = 0;
    $colour = "";
    $unlock = 0;
    $lap1 = $_POST['lap1'];
    $lap2 = $_POST['lap2'];
    $lap3 = $_POST['lap3'];
    $total = $_POST['total'];
    $totaltime = $total;
    $weevilName = $_COOKIE['weevil_name'];
    $weevilStats = getAllWeevilStatsByName($weevilName);
    switch($track){
        case '24':
            if(hasUserPlayedGame($weevilName, 24) == false){
                $key = generateSessionKey(30);
                createUserGame($weevilName, 24, $key) == true;
            }
            break;
        case '23':
            if(hasUserPlayedGame($weevilName, 23) == false){
                $key = generateSessionKey(30);
                createUserGame($weevilName, 23, $key) == true;
            }
            break;
        case '25':
            if(hasUserPlayedGame($weevilName, 25) == false){
                $key = generateSessionKey(30);
                createUserGame($weevilName, 25, $key) == true;
            }
            break;
    }

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
                    $unlock = 1;
                    rewardItem($weevilStats["id"], 2166, $colour);
                    setRaceStatus(24, 'bronze');
                }
                if($stats['silver'] == 0 && $total <= 37 && $total > 30){
                    $reward = "Silver";
                    $canreward = 1;
                    $colour = "15793919";
                    $unlock = 1;
                    rewardItem($weevilStats["id"], 2166, $colour);
                    setRaceStatus(24, 'silver');
                }
                if($stats['gold'] == 0 && $total <= 30){
                    $reward = "Gold";
                    $canreward = 1;
                    $colour = "16777136";
                    $unlock = 1;
                    rewardItem($weevilStats["id"], 2166, $colour);
                    setRaceStatus(24, 'gold');
                }
            }
            break;
        case '23':
            if(intval($totaltime) < 37000){
                GrantBan(strtotime('+1 day', time()), $weevilStats["id"]);
            }
            else{
                if($stats['bronze'] == 0 && $total <= 90 && $total > 75 && $total > 60){
                    $reward = "Bronze";
                    $canreward = 1;
                    $colour = "12425032";
                    $unlock = 1;
                    rewardItem($weevilStats["id"], 2167, $colour);
                    setRaceStatus(23, 'bronze');
                }
                if($stats['silver'] == 0 && $total <= 75 && $total > 60){
                    $reward = "Silver";
                    $canreward = 1;
                    $colour = "16711422";
                    $unlock = 1;
                    rewardItem($weevilStats["id"], 2167, $colour);
                    setRaceStatus(23, 'silver');
                }
                if($stats['gold'] == 0 && $total <= 60){
                    $reward = "Gold";
                    $canreward = 1;
                    $colour = "16777136";
                    $unlock = 1;
                    rewardItem($weevilStats["id"], 2167, $colour);
                    setRaceStatus(23, 'gold');
                }
            }
            break;
        case '25':
            if(intval($totaltime) < 48000){
                GrantBan(strtotime('+1 day', time()), $weevilStats["id"]);
            }
            else{
                if($stats['bronze'] == 0 && $total <= 110 && $total > 95 && $total > 80){
                    $reward = "Bronze";
                    $canreward = 1;
                    $colour = "12425032";
                    $unlock = 1;
                    rewardItem($weevilStats["id"], 2168, $colour);
                    setRaceStatus(25, 'bronze');
                }
                if($stats['silver'] == 0 && $total <= 95 && $total > 80){
                    $reward = "Silver";
                    $canreward = 1;
                    $colour = "16711422";
                    $unlock = 1;
                    rewardItem($weevilStats["id"], 2168, $colour);
                    setRaceStatus(25, 'silver');
                }
                if($stats['gold'] == 0 && $total <= 80){
                    $reward = "Gold";
                    $canreward = 1;
                    $colour = "16777136";
                    $unlock = 1;
                    rewardItem($weevilStats["id"], 2168, $colour);
                    setRaceStatus(25, 'gold');
                }
            }
            break;
    }
    echo "res=1&lap1=".$lap1."&lap2=".$lap2."&lap3=".$lap3."&total=".$totaltime."&hasWonMedal=".strval($canreward)."&medalType=".$reward."&clr=".$colour."&unlock=".strval($unlock);
}
else echo 'responseCode=999';

?>