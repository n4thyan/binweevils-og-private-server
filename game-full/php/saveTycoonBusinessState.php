<?php

include('../essential/backbone.php');

if(isset($_POST)) {
    $roomId = $_POST['locID'];
    $busOpen = $_POST['busOpen'];
    $signTxtClr = strval($_POST['signTxtClr']);
    $signClr = strval($_POST['signClr']);
    $userId = $_COOKIE['weevil_name'];

    $userData = getAllWeevilStatsByName($userId);
    $username = $userData['username'];
    if(checkValidColour($signTxtClr) == true){
        if(checkValidColour($signClr) == true){
            if(saveBusiness($roomId, $signClr, $signTxtClr, $busOpen) == true){
                echo 'res=2';
            }
            else echo 'res=3';
        }
        else echo 'res=3';
    }
    else echo 'res=9';
}
else echo 'res=3';

?>