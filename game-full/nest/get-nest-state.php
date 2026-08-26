<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $ownerName = $_POST['id'];
    echo getNestState($ownerName);
}
else echo 'responseCode=999';
?>