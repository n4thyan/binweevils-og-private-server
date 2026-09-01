<?php
error_reporting(0);
include('../../essential/backbone.php');
include('../../site/referrals.php');

header('Content-Type: application/x-www-form-urlencoded; charset=UTF-8');

if($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_COOKIE['weevil_name'], $_COOKIE['sessionId'])) {
    echo 'responseCode=999';
    exit;
}

$username = (string)$_COOKIE['weevil_name'];
if(!confirmSessionKey($username, (string)$_COOKIE['sessionId'])) {
    echo 'responseCode=999';
    exit;
}

// The 2021 Nest Hall client shows its reward popup solely for responseCode=1.
// Return 2 when there is no pending persisted referral; never return a fake 1.
$result = referral_claim_pending_reward($username);
echo http_build_query($result, '', '&', PHP_QUERY_RFC3986);
?>
