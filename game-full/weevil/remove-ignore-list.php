<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $weevilname = $_POST['username'];
    echo removeFromIgnoreList($weevilname);
}
else echo 'responseCode=999';
?>