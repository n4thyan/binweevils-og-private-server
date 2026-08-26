<?php
error_reporting(0);
include('../essential/backbone.php');

$nonperish = checkNonPerishableDailyHarvestAll($_COOKIE['weevil_name']);
$perish = checkPerishableDailyHarvestAll($_COOKIE['weevil_name']);

if($nonperish == -1 || $perish == -1)
echo 'responseCode=999';
else
echo 'responseCode=1&peri=' . $perish . '&non-peri=' . $nonperish;
?>