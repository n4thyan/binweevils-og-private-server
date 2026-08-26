<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include('../../../essential/backbone.php');

if(isset($_POST)) {
    $itemId = $_POST['itemTypeID'];
    $userId = $_POST['userIDX'];
    $colour = $_POST['colour'];

    $userData = getAllWeevilStats($userId);
    $itemData = getItemDataById($itemId);

    if($userData['username'] == $_COOKIE['weevil_name']) {
        if(itemCountById($itemId, $userId, $colour) > 800)
        echo 'responseCode=999';
        else {
            if($userData['level'] >= $itemData['minLevel']){
                if($itemData['currency'] == "mulch"){
                    if($userData['mulch'] >= $itemData['price']){
                        $bought = BuyItem($userId, $itemId, $colour);
                        if($bought == true){
                            removeMulch($userId, $itemData['price']);
                            addExperience($userId, $itemData['expPoints']);
                            echo "responseCode=1&mulch=".strval($userData['mulch']-$itemData['price'])."&completedAchievements=0&priceCharged=".strval($itemData['price'])."&xp=".strval($userData['xp']+$itemData['expPoints']);
                        }
                        else{
                            echo 'responseCode=999';
                        }
                    }
                    else echo 'responseCode=3';
                }
                else{
                    if($userData['dosh'] >= $itemData['price']){
                        $bought = BuyItem($userId, $itemId, $colour);
                        if($bought == true){
                            removeDosh($userId, $itemData['price']);
                            addExperience($userId, $itemData['expPoints']);
                            echo "responseCode=1&dosh=".strval($userData['dosh']-$itemData['price'])."&completedAchievements=0&priceCharged=".strval($itemData['price'])."&xp=".strval($userData['xp']+$itemData['expPoints']);
                        }               
                        else{
                            echo 'responseCode=999';
                        }
                    }
                    else echo 'responseCode=3';
                }
            }
            else echo 'responseCode=8';
        }
    }
    else
    echo 'responseCode=999';
}
else{
    echo 'res=999';
}

?>