<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $weevilname = $_POST['userID'];
    $nest = $_POST['nestID'];
    $room = $_POST['locationID'];
    $spot = $_POST['spot'];
    $position = $_POST['currentframe'];
    $itemId = $_POST['itemID'];
    $fid = $_POST['fID'];

    $ownsNest = checkNest($weevilname, $nest);
    if($ownsNest == true){
        $ownsRoom = checkNestRoom($weevilname, $room);
        if($ownsRoom == true){
            $addToRoom = addItemToNestRoom($room, $itemId, $position, $fid, $spot);
            if($addToRoom == false)
            echo 'responseCode=999';
        }
    }
    else echo 'responseCode=999';

}
else echo 'responseCode=999';
?>