<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $itemID = $_POST['itemID'];
    $hash = $_POST['hash'];
    $st = $_POST['st'];

    if(checkHash(["hash" => $hash, "itemID" => $itemID, "st" => $st]))
    removeItemFromGarden($_COOKIE['weevil_name'], $itemID);
}
?>