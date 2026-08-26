<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $userID = $_COOKIE['weevil_name'];
    $gridID = $_POST['gridID'];
    $progress = $_POST['progress'];
    $completed = $_POST['completed'];

    $weevilData = getAllWeevilStatsByName($userID);
    $crossWordData = getCrosswordData($gridID);
    $userProgress = getCrosswordProgress($gridID);

    if($weevilData != null && $crossWordData != null) {
        if($weevilData["level"] < $crossWordData["minLevel"]) {
            echo 'responseCode=999';
            return;
        }

        if($userProgress == null)
        addNewCrosswordProgress($progress, $gridID);
        else
        setCrosswordProgress($progress, $gridID);

        if($completed == "1") {
            $mulchEarned = 0;
            $xpEarned = 0;

            if($userProgress["completed"] != 1) {
                setCrosswordProgress($progress, $gridID, intval($completed));
                $mulchEarned = $crossWordData["mulchReward"];
                $xpEarned = $crossWordData["xpReward"];
                addMulchByName($userID, $mulchEarned);
                addExperienceByName($userID, $xpEarned);
            }

            echo 'res=2&mulch=' . ($weevilData["mulch"] + $mulchEarned) . '&xp=' . ($weevilData["xp"] + $xpEarned) . '&x=y';
            return;
        }

        echo 'res=1';
    }
    else
    echo 'responseCode=999';
}
else
echo 'responseCode=999';
?>