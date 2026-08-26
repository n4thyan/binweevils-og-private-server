<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $code = $_POST['code'];
    $id = $_POST['userIDX'];
    $weevilData = getAllWeevilStats($id);

    if(checkIfCodeIsValid($code) == true) {
        if(canWeevilRedeem($id, $code) == true){
            if(redeemCode($id, $code) == true){
                $rewards = getCodeRewards($code);
                if($rewards != false){
                    if($rewards['mulch'] > 0){
                        addMulchByName($weevilData['username'], $rewards['mulch']);
                        $weevilData['mulch'] = $weevilData['mulch'] + $rewards['mulch'];
                    }
                    if($rewards['dosh'] > 0){
                        addDoshByName($weevilData['username'], $rewards['dosh']);
                        $weevilData['dosh'] = $weevilData['dosh'] + $rewards['dosh'];
                    }
                    if($rewards['xp'] > 0){
                        addExperience($id, $rewards['xp']);
                        $weevilData['xp'] = $weevilData['xp'] + $rewards['xp'];
                    }
                    if($rewards['item'] > 0){
                        rewardItem($id, $rewards['item']);
                    }
                    if($rewards['seed'] > 0){
                        rewardSeed($rewards['seed']);
                    }
                    if($rewards['gardenItem'] > 0){
                        rewardGardenItem($rewards['gardenItem']);
                    }
                    echo 'responseCode=1&latestMulchValue='.strval($weevilData['mulch']).'&mulchReward='.strval($rewards['mulch']).'&xpReward='.strval($rewards['xp']).'&latestXPValue='.strval($weevilData['xp']).'&doshReward='.strval($rewards['dosh']).'&latestDoshValue='.strval($weevilData['dosh']).'&seedReward='.strval($rewards['seed']).'&gardenItem='.strval($rewards['gardenItem']).'&itemReward='.strval($rewards['item']);
                }
                else echo 'responseCode=2';
            }
            else echo 'responseCode=3';
        }
        else echo 'responseCode=2';
    }
    else echo 'responseCode=3';
}
else echo 'responseCode=999';

?>