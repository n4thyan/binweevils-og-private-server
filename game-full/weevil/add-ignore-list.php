<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $weevil = $_POST['username'];
    echo addToIgnoreList($weevil, true);
}
else echo 'responseCode=999';
?>