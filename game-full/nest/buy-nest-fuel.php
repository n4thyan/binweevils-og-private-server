<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $cost = $_POST['cost'];
    echo buyNestFuel($cost);
}
else echo 'res=999';
?>