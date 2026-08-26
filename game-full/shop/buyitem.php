<?php
error_reporting(E_ALL);
include('../essential/backbone.php');

if(isset($_POST)) {
    $itemId = $_POST['id'];
    $userId = $_COOKIE['weevil_name'];
    $colour = $_POST['itemColour'];

    $userData = getAllWeevilStatsByName($userId);
    $itemData = getNightclubItemDataById($itemId);

    if(itemCountById($itemId, $userData['id'], $colour) > 800)
    echo 'res=999';
    else {
        if($userData['level'] >= $itemData['minLevel']){
            if($userData['mulch'] >= $itemData['price']){
                $bought = BuyNightclubItem($userData['id'], $itemId, $colour, $itemData['inventoryCategory']);
                if($bought == true){
                    removeMulch($userData['id'], $itemData['price']);
                    addExperience($userData['id'], $itemData['xp']);
                    echo "res=1&mulch=".strval($userData['mulch']-$itemData['price'])."&xp=".strval($userData['xp']+$itemData['xp']);
                }
                else{
                    echo 'res=3';
                }
            }
            else echo 'res=3';
        }
        else echo 'res=3';
    }
}
else{
    echo 'res=999';
}

?>