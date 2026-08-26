<?php
error_reporting(0);
include('../../essential/backbone.php');

$tycoonBusinesses = grabBusinesses($_COOKIE['weevil_name']);

if($tycoonBusinesses != null) {
    $businessData = [];

    foreach($tycoonBusinesses["businessInfo"] as $businessInfo) {
        array_push($businessData, ['typeID' => $businessInfo['businessType'], 'businessName' => $businessInfo['businessName'], 'currency' => $businessInfo['currencyType'], 'total' => strval($businessInfo['total']), 'collected' => strval($businessInfo['collected'])]);
    }

    echo json_encode(['responseCode' => 1, 'businesses' => $businessData, 'tycoon' => "1", 'nextDate' => 'now', 'nextAmount' => 0]);
}
else
echo json_encode(['responseCode' => 999]);
?>