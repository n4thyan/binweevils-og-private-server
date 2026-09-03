<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $hash = $_POST['hash'];
    $timer = $_POST['timer'];
    $petID = intval($_POST['petID']);

    if(checkHash(["hash" => $hash, "petID" => $petID, "timer" => $timer])) {
        $petData = getPetProfile($petID);

        if($petData != null)
        echo json_encode(["responseCode" => 1, "profile" => $petData]);
        else
        echo json_encode(["responseCode" => 2, "message" => "Something went wrong."]);
    }
    else echo json_encode(["responseCode" => 999]);
}
else echo json_encode(["responseCode" => 999]);