<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $visitorIdx = $_POST['visitorIdx'];
    $tycoonIdx = $_POST['tycoonIdx'];
    $step = $_POST['step'];

    $steps = array(
        1 => array(
            'visitorXp' => 5,
            'tycoonMulch' => 20,
        ),
        2 => array(
            'visitorXp' => 10,
            'tycoonMulch' => 25,
        ),
        3 => array(
            'visitorXp' => 15,
            'tycoonMulch' => 40,
        ),
    );

    $visitorWeevilData = getAllWeevilStatsByName($_COOKIE['weevil_name']);
    $tycoonWeevilData = getAllWeevilStats(intval($tycoonIdx));

    if(is_array($tycoonWeevilData)) {
        if($visitorWeevilData["id"] == intval($visitorIdx) && $visitorWeevilData["id"] != intval($tycoonIdx)) {
            if(intval($step) < 0 || intval($step) > 3)
            echo 'responseCode=999';
            else if(checkPlazaEarningTimer()) {
                updatePlazaEarningTimer(strtotime('+1 minute', time()));
                addExperienceByName($_COOKIE['weevil_name'], $steps[intval($step)]["visitorXp"]);
                giveBusinessEarnings($tycoonWeevilData["username"], "Tycoon Business", $steps[intval($step)]["tycoonMulch"]);
                echo 'responseCode=1&visitorXp=' . ($visitorWeevilData["xp"] + $steps[intval($step)]["visitorXp"]);
            }
            else echo 'responseCode=2';
        }
        else echo 'responseCode=999';
    }
    else echo 'responseCode=999';
}
else echo 'responseCode=999';
?>