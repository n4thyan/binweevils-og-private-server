<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST['food'])) {
    $food = $_POST['food'];
    $fitness = $_POST['fitness'];
    $happiness = $_POST['happiness'];
    $st = $_POST['st'];
    $hash = $_POST['hash'];

    if(checkHash(["hash" => $hash, "food" => $food, "fitness" => $fitness, "happiness" => $happiness, "st" => $st]))
    echo updateStats($food);
    else
    echo 'res=999';
}
else echo 'res=999';

?>