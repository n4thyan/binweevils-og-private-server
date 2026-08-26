<?php
error_reporting(0);
include('../essential/backbone.php');

$jsonData = file_get_contents("php://input");

if(!empty($jsonData))
addToBuddyListTable(json_decode($jsonData));
else
echo 'error';
?>