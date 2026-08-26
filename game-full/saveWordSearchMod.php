<?php
error_reporting(0);
include('essential/backbone.php');

if(isset($_GET)) {
    $gridID = $_GET['gridID'];
    $progress = urldecode($_GET['progress']);
    $userID = $_GET['weevilName'];
    $completed = "1";

    $userProgress = getWordsearchProgressMod($gridID, $userID);

    if($userProgress == null)
    addNewWordsearchProgressMod($userID, $progress, $gridID);
    else
    setWordsearchProgressMod($progress, $gridID, $userID);

    $alreadyCompleted = false;
    $giveReward = true;

    if($userProgress["completed"] == 1)
    $alreadyCompleted = true;
    else if($userProgress["progress"] == $progress || $userProgress["wordsFound"] > 39)
    $giveReward = false;

    if(!$alreadyCompleted) {
        if($completed == "1" && count(explode('|', $progress)) >= 12 && count(explode('|', $progress)) <= 20) {
            setWordsearchProgressMod($progress, $gridID, $userID, 1);
            $mulchEarned = 110;
            $xpEarned = 11;
        }
        else if($giveReward) {
            setWordsearchProgressMod($progress, $gridID, $userID);
            $mulchEarned = 10;
            $xpEarned = 1;
        }
        else {
            setWordsearchProgressMod($progress, $gridID, $userID);
            $mulchEarned = 0;
            $xpEarned = 0;
            $userMulch = 0;
            $userXp = 0;
        }

        if($mulchEarned > 0) {
            addMulchByNameMod($userID, $mulchEarned);
            addExperienceByNameMod($userID, $xpEarned);
        }

        echo 'responseCode=1';
    }
}
else
echo 'responseCode=999';
?>