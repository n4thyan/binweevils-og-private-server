<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $idx = $_POST['idx'];
    $petID = $_POST['petID'];
    $timer = $_POST['timer'];
    $hash = $_POST['hash'];

    if(checkHash(["hash" => $hash, "idx" => $idx, "petID" => $petID, "timer" => $timer])) {
        $petSkills = getPetSkills($petID);

        if($petSkills != null)
        echo json_encode(["responseCode" => 1, "skills" => $petSkills]);
        else
        echo json_encode(["responseCode" => 2, "message" => "Something went wrong."]);
    }
    else
    echo json_encode(["responseCode" => 999, "message" => "User is not logged in."]);
}
else
echo json_encode(["responseCode" => 999, "message" => "User is not logged in."]);
?>