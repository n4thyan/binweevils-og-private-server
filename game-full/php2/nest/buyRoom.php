<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $locId = $_POST['roomID'];
    $userId = $_POST['userIDX'];

    $userData = getAllWeevilStats($userId);
    $username = $userData['username'];
    $ownedRoom = DoesUserOwnRoom($locId, $username);
    
    if($ownedRoom == false){
        $roomPrice = CheckRoomPrice($locId);
        if($userData['mulch'] >= $roomPrice){
            $boughtRoom = BuyRoom($username, $locId);
            if($boughtRoom == true){
                echo "responseCode=1&currency=mulch&value=" . strval($userData['mulch'] - $roomPrice) . "&xp=". strval($userData['xp']);
                removeMulch($userId, $roomPrice);
            }
            else echo 'responseCode=999';
        }
        else echo 'responseCode=999';
    }
    else echo 'responseCode=999';
}
else echo 'responseCode=999';

?>