<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $taskID = $_POST['taskID'];
    $questID = $_POST['questID'];
    $username = $_COOKIE['weevil_name'];
    $weevilStats = getAllWeevilStatsByName($username);
    $TaskDetails = GetTaskDetails($taskID);
    $idx = $weevilStats[id];
    $specialMove = NULL;
    if($TaskDetails['canReward'] == 1) {
        if(HasUserCompletedTask($taskID, $username, $idx) == false) {
            if(CompleteTask($taskID, $username,$idx, $questID) == true) {
                if($taskID == 45) {
                    rewardSpecialMoves($username, $idx, 23);
                    $specialMove = 23;
                }
                $itemData = getItemDataById($TaskDetails['itemNameRewarded']);
                $gardenItemData = getGardenItemDataById($TaskDetails['gardenItemNameRewarded']);
    
                if($TaskDetails['itemNameRewarded'] != 0)
                rewardItem($weevilStats['id'], $TaskDetails['itemNameRewarded'], -1);
                if($TaskDetails['gardenItemNameRewarded'] != 0)
                rewardGardenItem($TaskDetails['gardenItemNameRewarded']);
                
                addMulchByName($username, $TaskDetails['mulchRewarded']);
                addExperienceByName($username, $TaskDetails['xpRewarded']);
                addDoshByName($username, $TaskDetails['doshRewarded']);
                echo 'responseCode=1&mulch='.strval($weevilStats['mulch']+$TaskDetails['mulchRewarded']).'&xp='.strval($weevilStats['xp']+$TaskDetails['xpRewarded']).'&dosh='.strval($weevilStats['dosh']+$TaskDetails['doshRewarded']).'&itemName='.$itemData['name'].'&gardenItemName='.$gardenItemData['name'].'&move='.$specialMove.'&deleted=&completedAchievements=&bundleName=';
            }
            else
            echo 'responseCode=2&msg=user has completed task';
        }
        else
        echo 'responseCode=2';
    }
    else
    echo 'responseCode=999&message=Task not available.';
}
else echo 'responseCode=999';
?>