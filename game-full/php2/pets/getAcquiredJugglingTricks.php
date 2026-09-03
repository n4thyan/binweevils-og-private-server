<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $hash = $_POST['hash'];
    $timer = $_POST['timer'];
    $idx = $_POST['idx'];
    $petID = $_POST['petID'];

    if(checkHash(["hash" => $hash, "petID" => $petID, "idx" => $idx, "timer" => $timer])) {
        $jugglingTricks = getPetJugglingTricks($_COOKIE['weevil_name'], $petID);

        if($jugglingTricks != null)
        echo json_encode(["responseCode" => 1, "tricks" => $jugglingTricks]);
        else
        echo json_encode(["responseCode" => 2, "message" => "Something went wrong."]);
    }
    else echo json_encode(["responseCode" => 999]);
}
else echo json_encode(["responseCode" => 999]);