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

                // Record task_complete activity after authoritative success.
                $achDbT = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $achDbT->begin_transaction();
                try {
                    $svcT = new AchievementService((int)$idx, $_COOKIE['weevil_name'], $achDbT);
                    $svcT->recordActivity('task_complete', (int)$taskID, 1, ['questID' => (int)$questID], true);
                    $newIdsT = $svcT->evaluateForActivity('task_complete', (int)$taskID, 1, ['questID' => (int)$questID]);
                    $achDbT->commit();
                    $achCsvT = $newIdsT ? implode(',', $newIdsT) : '0';
                } catch (Throwable $e) {
                    $achDbT->rollback();
                    $achCsvT = '0';
                }
                $achDbT->close();

                echo 'responseCode=1&mulch='.strval($weevilStats['mulch']+$TaskDetails['mulchRewarded']).'&xp='.strval($weevilStats['xp']+$TaskDetails['xpRewarded']).'&dosh='.strval($weevilStats['dosh']+$TaskDetails['doshRewarded']).'&itemName='.$itemData['name'].'&gardenItemName='.$gardenItemData['name'].'&move='.$specialMove.'&deleted=&completedAchievements='.$achCsvT.'&bundleName=';
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