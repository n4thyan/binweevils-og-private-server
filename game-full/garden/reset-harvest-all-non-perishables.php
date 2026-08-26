<?php
error_reporting(0);
include('../essential/backbone.php');

if(checkNonPerishableDailyHarvestAll($_COOKIE['weevil_name']) <= 0) {
    echo 'responseCode=2';
    return;
}

if(getAllWeevilStatsByName($_COOKIE['weevil_name'])["dosh"] >= 5) {
    if(removeDoshByName($_COOKIE['weevil_name'], 5) && updateDailyHarvestTimer($_COOKIE['weevil_name'], "0", false))
    echo 'responseCode=1';
    else
    echo 'responseCode=999';
}
else
echo 'responseCode=3';
?>