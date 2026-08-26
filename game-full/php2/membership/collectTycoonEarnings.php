<?php
error_reporting(0);
include('../../essential/backbone.php');

$weevilData = getAllWeevilStatsByName($_COOKIE['weevil_name']);
$tycoonBusinesses = grabBusinesses($_COOKIE['weevil_name']);

if($tycoonBusinesses != null) {
    $doshEarned = 0;
    $mulchEarned = 0;

    foreach($tycoonBusinesses["businessInfo"] as $businessInfo) {
        $earningAmt = $businessInfo['total'] - $businessInfo['collected'];

        if($earningAmt > 0) {
            if($businessInfo['currencyType'] == "Mulch") {
                if(updateBusinessCollected($_COOKIE['weevil_name'], $businessInfo['businessType'], $earningAmt) && addMulchByName($_COOKIE['weevil_name'], $earningAmt))
                $mulchEarned = $mulchEarned + $earningAmt;
            }
            else if($businessInfo['currencyType'] == "Dosh") {
                if(updateBusinessCollected($_COOKIE['weevil_name'], $businessInfo['businessType'], $earningAmt) && addDoshByName($_COOKIE['weevil_name'], $earningAmt))
                $doshEarned = $doshEarned + $earningAmt;
            }
        }
    }

    echo 'responseCode=1&mulch=' . ($weevilData['mulch'] + $mulchEarned) . '&dosh=' . ($weevilData['dosh'] + $doshEarned);
}
else
echo 'responseCode=999';
?>