<?php
error_reporting(0);
include('../../essential/backbone.php');
//echo '{"responseCode":3,"message":"User does not have a pet"}';

if(isset($_POST)) {
    $weevilData = getAllWeevilStatsByName($_COOKIE['weevil_name']);
    $hash = $_POST['hash'];
    $idx = $_POST['idx'];
    $timer = $_POST['timer'];

    if($weevilData != null && $weevilData["id"] == $_POST['idx']) {
        if(checkHash(["hash" => $hash, "idx" => $idx, "timer" => $timer])) {
            $petData = getUserPets();

            if($petData != null)
            echo json_encode(["responseCode" => 1, "pets" => $petData]);
            else
            echo json_encode(["responseCode" => 3, "message" => "User does not have a pet."]);
        }
        else
        echo json_encode(["responseCode" => 999, "message" => "User is not logged in."]);
    }
    else
    echo json_encode(["responseCode" => 3, "message" => "User does not have a pet."]);
}
else
echo json_encode(["responseCode" => 999, "message" => "User is not logged in."]);
?>