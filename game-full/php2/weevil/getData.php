<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $username = urldecode($_POST['id']);
    $hash = $_POST['hash'];
    $timer = $_POST['timer'];

    if(checkHash(["hash" => $hash, "id" => $username, "timer" => $timer]))
    echo weevilGetData($username);
    else
    echo json_encode(["responseCode" => 999]);
}
else
echo json_encode(["responseCode" => 999]);
?>