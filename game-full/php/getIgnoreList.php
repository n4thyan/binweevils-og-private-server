<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $weevilname = $_POST['userID'];
    echo getIgnoreList();
}
else echo 'responseCode=999';
?>