<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
    $username = $_COOKIE['weevil_name'];
    $key = $_COOKIE['sessionId'];

    if(confirmSessionKey($username, $key)) {
        echo 'responseCode=1&time=' . time();
        return;
    }
}

echo 'responseCode=999';

?>