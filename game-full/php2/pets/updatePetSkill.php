<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $hash = $_POST['hash'];
    $timer = $_POST['timer'];
    $idx = $_POST['idx'];
    $petID = $_POST['petID'];
    $skillID = $_POST['skillID'];
    $skillLevel = $_POST['skillLevel'];
    $obedience = intval($_POST['obedience']);
    $invalidSkillIDs = array(0,1,2,3,4,5,18,19,20,21);

    $weevilStats = getAllWeevilStats($idx);
    if($weevilStats['username'] != $_COOKIE['weevil_name']) {
        echo json_encode(["responseCode" => 999]);
        exit;
    }

    if($obedience < 20 || $obedience > 105 || intval($skillLevel) < 0 || intval($skillLevel) > 100 || in_array(intval($skillID), $invalidSkillIDs)) {
        echo json_encode(["responseCode" => 2]);
        exit;
    }

    echo updatePetSkill($weevilStats['username'], $petID, $skillID, $skillLevel, $obedience);
}
else echo json_encode(["responseCode" => 999]);