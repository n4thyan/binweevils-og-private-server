<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $fuel = $_POST['fuel'];
    echo updateFuel($fuel);
}
else echo 'res=999';
?>