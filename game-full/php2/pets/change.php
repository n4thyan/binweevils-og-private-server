<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $hash = $_POST['hash'];
    $timer = $_POST['timer'];
    $idx = $_POST['idx'];
    $bodyColour = intval($_POST['bc']);
    $armColour1 = intval($_POST['ac1']);
    $armColour2 = intval($_POST['ac2']);
    $eyeColour1 = intval($_POST['ec1']);
    $eyeColour2 = intval($_POST['ec2']);
    $weevilStats = getAllWeevilStats($idx);
    $allowedColours = array(10027008, 43520, 153, 10057472, 8913032, 11198463, 26367, 16750848, 13421568, 61166, 13369548, 16777215, 16766429, 11206400, 16763904, 15658496, 16745604, 2631720, 10066329, 16777145, 15597568, 26112);
    $costToChange = 500;

    if(!checkHash(["hash" => $hash, "idx" => $idx, "bc" => $bodyColour, "ac1" => $armColour1, "ac2" => $armColour2, "ec1" => $eyeColour1, "ec2" => $eyeColour2, "timer" => $timer])) {
        echo 'responseCode=999';
        exit;
    }

    if(!in_array($bodyColour, $allowedColours) || !in_array($armColour1, $allowedColours) || !in_array($armColour2, $allowedColours) || !in_array($eyeColour1, $allowedColours) || !in_array($eyeColour2, $allowedColours)) {
        echo 'responseCode=2';
        exit;
    }

    if($weevilStats['username'] != $_COOKIE['weevil_name']) {
        echo 'responseCode=999';
        exit;
    }

    if($weevilStats['mulch'] < $costToChange) {
        echo 'responseCode=2';
        exit;
    }

    echo changePetDef($weevilStats['username'], $idx, $weevilStats['mulch'], $costToChange, $bodyColour, $armColour1, $armColour2, $eyeColour1, $eyeColour2);
}
else echo 'responseCode=999';