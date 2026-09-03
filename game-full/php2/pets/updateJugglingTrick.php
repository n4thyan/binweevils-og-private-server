<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $hash = $_POST['hash'];
    $timer = $_POST['timer'];
    $idx = $_POST['idx'];
    $petID = $_POST['petID'];
    $trickID = $_POST['trickID'];
    $aptitude = $_POST['aptitude'];
    $skillLevel = $_POST['skillLevel'];
    $weevilStats = getAllWeevilStats($idx);

    if(!checkHash(["hash" => $hash, "idx" => $idx, "petID" => $petID, "trickID" => $trickID, "aptitude" => $aptitude, "skillLevel" => $skillLevel, "timer" => $timer])) {
        echo json_encode(["responseCode" => 999]);
        exit;
    }

    if($weevilStats['username'] != $_COOKIE['weevil_name']) {
        echo json_encode(["responseCode" => 999]);
        exit;
    }

    if(intval($aptitude) < 0 || intval($aptitude) > 100 || intval($skillLevel) < 0 || intval($skillLevel) > 100) {
        echo json_encode(["responseCode" => 2]);
        exit;
    }

    echo updateJugglingTrick($weevilStats['username'], $petID, $trickID, $aptitude, $skillLevel);
}
else echo json_encode(["responseCode" => 999]);