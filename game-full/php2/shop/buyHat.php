<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $itemId = $_POST['id'];
    $userId = $_POST['idx'];
    $colour = $_POST['colour'];

    $userData = getAllWeevilStats($userId);
    $userOwns = checkIfUserOwnsHat($itemId, $colour);
    $itemData = getHatDataById($itemId);
    if($userOwns == true){
      echo 'responseCode=2';
    }
    else{
        if($userData['dosh'] >= $itemData['price']){
            $bought = BuyHat($itemId, $colour);
            if($bought == true){
                removeDosh($userId, $itemData['price']);
                addExperience($userId, $itemData['price']*10);
                echo "responseCode=1&message=Success&dosh=".strval($userData['dosh']-$itemData['price'])."&completedAchievements=&hash=0242f5022d15e5e862c0b2406a7042c7&colour=".$colour."&idx=".$userId."&id=".$id."&timer=114899&voucher=%2D1&xp=".strval($userData['xp']+$itemData['price']*10);
            }               
            else{
                echo 'responseCode=999';
            }
        }
        else echo 'responseCode=3';
    }
}
else{
    echo 'responseCode=999';
}

?>