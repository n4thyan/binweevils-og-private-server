<?php
error_reporting(0);
include('../../essential/backbone.php');

if($_POST) {
    $idx = $_POST['idx'];
    $hash = $_POST['hash'];
    $timer = $_POST['timer'];

    if(checkHash(["hash" => $hash, "idx" => $idx, "timer" => $timer]))
    echo "responseCode=1&newAchievements=";
    else
    echo 'responseCode=999';
}
else
echo 'responseCode=999';
?>