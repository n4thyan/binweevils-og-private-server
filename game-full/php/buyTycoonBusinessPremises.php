<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $locId = $_POST['locTypeID'];
    $userId = $_COOKIE['weevil_name'];

    $userData = getAllWeevilStatsByName($userId);
    $username = $userData['username'];
    $ownedRoom = DoesUserOwnRoom($locId, $username);
    
    if($ownedRoom == false) {
        $roomPrice = CheckRoomPrice($locId);

        if($roomPrice != null) {
            if($userData['mulch'] >= $roomPrice) {
                $boughtRoom = BuyRoom($username, $locId);
                if($boughtRoom == true) {
                    echo "res=3";
                    removeMulch($userData['id'], $roomPrice);
                }
                else echo 'responseCode=999';
            }
            else echo 'res=2';
        }
        else echo 'responseCode=999';
    }
    else echo 'res=1';
}
else echo 'responseCode=999';
?>