<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST) && isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
    $loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

    if($loggedIn == true) {
        $weevilReportingStats = getAllWeevilStatsByName($_COOKIE['weevil_name']);
        $weevilReportedStats = getAllWeevilStats($_POST['reportedUserIdx']);
        $reason = $_POST['reason'];
        $comment = $_POST['comment'];
        $reasonText = "";

        if($weevilReportedStats['username'] == $_POST['reportedUser'] && strlen($comment) <= 160) {
            switch($reason) {
                case "0":
                    $reasonText = "REPORT_BAD_LANGUAGE";
                    break;
                case "1":
                    $reasonText = "REPORT_BAD_NAME";
                    break;
                case "2":
                    $reasonText = "REPORT_BAD_BEHAVIOUR";
                    break;
                case "3":
                    $reasonText = "REPORT_BAD_DETAILS";
                    break;
                default:
                    echo 'responseCode=999';
                    return;
            }

            if(file_get_contents("https://desou.xyz/api/BWR/sendWeevilReport.php?weevilReporting=" . $_COOKIE['weevil_name'] . "&weevilReported=" . $weevilReportedStats['username'] . "&reason=" . urlencode($reasonText) . "&comment=" . urlencode($comment)) == "1")
            echo 'responseCode=1';
            else
            echo 'respondeCode=999';
        }
        else
        echo 'responseCode=999';
    }
    else
    echo 'responseCode=999';
}
else
echo 'responseCode=999';
?>