<?php

include('../essential/backbone.php');

if(isset($_POST)) {
    $weevilname = $_POST['userID'];
    $nest = $_POST['nestID'];
    $room = $_POST['locationID'];
    $spot = $_POST['spot'];
    $position = $_POST['crntPos'];
    $itemId = $_POST['itemID'];
    $fid = $_POST['fID'];

    $ownsNest = checkNest($weevilname, $nest);
    if($ownsNest == true){
        $updatePos = updateItemPositionInNest($nest, $itemId, $position, $fid, $spot);
        if($updatePos == false)
        echo 'responseCode=999';

    }
    else echo 'responseCode=999';

}
else echo 'responseCode=999';
?>