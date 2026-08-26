<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $userID = $_COOKIE['weevil_name'];
    $gridID = $_POST['gridID'];
    $progress = $_POST['progress'];
    $completed = $_POST['completed'];

    $weevilData = getAllWeevilStatsByName($userID);
    $wordSearchData = getWordsearchData($gridID);
    $userProgress = getWordsearchProgress($gridID);

    if($weevilData != null && $wordSearchData != null) {
        if($weevilData["level"] < $wordSearchData["minLevel"]) {
            echo 'responseCode=999';
            return;
        }

        if($userProgress == null)
        addNewWordsearchProgress($progress, $gridID);
        else
        setWordsearchProgress($progress, $gridID);

        $alreadyCompleted = false;
        $giveReward = true;

        if($userProgress["completed"] == 1)
        $alreadyCompleted = true;
        else if($userProgress["progress"] == $progress || $userProgress["wordsFound"] > 39)
        $giveReward = false;

        if(!$alreadyCompleted) {
            if($completed == "1" && count(explode('|', $progress)) >= 12 && count(explode('|', $progress)) <= 20) {
                setWordsearchProgress($progress, $gridID, 1);
                $mulchEarned = 110;
                $xpEarned = 11;
            }
            else if($giveReward) {
                setWordsearchProgress($progress, $gridID);
                $mulchEarned = 10;
                $xpEarned = 1;
            }
            else {
                setWordsearchProgress($progress, $gridID);
                $mulchEarned = 0;
                $xpEarned = 0;
                $userMulch = 0;
                $userXp = 0;
            }

            if($mulchEarned > 0) {
                $userMulch = $weevilData["mulch"] + $mulchEarned;
                $userXp = $weevilData["xp"] + $xpEarned;
                addMulchByName($userID, $mulchEarned);
                addExperienceByName($userID, $xpEarned);
            }

            echo 'mulch=' . $userMulch . '&xp=' . $userXp . '&x=y';
        }
    }
    else
    echo 'responseCode=999';
}
else
echo 'responseCode=999';
?>