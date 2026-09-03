<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $username = $_POST['userID'];

    if($username != $_COOKIE['weevil_name']) {
        echo 'result=';
        exit;
    }

    echo feedPet($username);
}
else echo 'result=';