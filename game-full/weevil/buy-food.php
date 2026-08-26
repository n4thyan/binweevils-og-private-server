<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $energyValue = $_POST['energyValue'];
    $cost = $_POST['cost'];
    $type = $_POST['type'];
    $hash = $_POST['hash'];
    $st = $_POST['st'];

    if(checkHash(["hash" => $hash, "cost" => $cost, "energyValue" => $energyValue, "type" => $type, "st" => $st]))
    echo buyFood($energyValue, $cost);
    else
    echo 'res=999';
}
else echo 'res=999';
?>