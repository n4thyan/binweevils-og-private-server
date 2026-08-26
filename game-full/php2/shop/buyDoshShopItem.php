<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $itemId = $_POST['itemTypeID'];
    $userId = $_POST['userIDX'];
    $colour = $_POST['colour'];
    $shopType = $_POST['storeName'];
    $hash = $_POST['hash'];
    $timer = $_POST['timer'];

    $userData = getAllWeevilStats($userId);
    $itemData = getItemDataById($itemId);

    if(checkHash(["hash" => $hash, "itemTypeID" => $itemId, "userIDX" => $userId, "colour" => $colour, "storeName" => $shopType, "timer" => $timer]) && $userData['username'] == $_COOKIE['weevil_name']) {
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
                            echo "responseCode=1&dosh=".strval($userData['dosh'])."&mulch=".strval($userData['mulch']-$itemData['price'])."&xp=".strval($userData['xp']+$itemData['expPoints']) . "&collectionItem=0";
                        }
                        else{
                            echo 'res=999';
                        }
                    }
                    else echo 'responseCode=4';
                }
                else{
                    if($userData['dosh'] >= $itemData['price']){
                        $bought = BuyItem($userId, $itemId, $colour);
                        if($bought == true){
                            removeDosh($userId, $itemData['price']);
                            addExperience($userId, $itemData['expPoints']);
                            echo "responseCode=1&dosh=".strval($userData['dosh']-$itemData['price'])."&mulch=".strval($userData['mulch'])."&xp=".strval($userData['xp']+$itemData['expPoints']) . "&collectionItem=0";
                        }               
                        else{
                            echo 'res=999';
                        }
                    }
                    else echo 'responseCode=4';
                }
            }
            else echo 'err=12';
        }
    }
    else
    echo 'responseCode=999';
}
else{
    echo 'res=999';
}

?>