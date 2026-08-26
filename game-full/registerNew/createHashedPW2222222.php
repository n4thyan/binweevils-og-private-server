<?php
error_reporting(0);
include('../essential/backbone.php');

    $username = $_GET['userID'];
    $password = $_GET['password'];

echo scramblePassword($username, $password);

?>