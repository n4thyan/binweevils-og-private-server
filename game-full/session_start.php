<?php
error_reporting(0);
include('essential/backbone.php');

// session_start — called by the SWF on login/startup to (re)establish the player session.
// Returns a minimal valid JSON envelope so the client stops 403/404ing on it.
if(isset($_POST) || isset($_GET)) {
    $weevilData = getAllWeevilStatsByName($_COOKIE['weevil_name']);
    if($weevilData != null && is_array($weevilData)) {
        header('Content-Type: application/json');
        echo json_encode([
            'responseCode' => 1,
            'userID'      => $weevilData['id'],
            'username'    => $weevilData['username'],
            'sessionStarted' => true,
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['responseCode' => 999, 'message' => 'Session not established.']);
    }
}
else {
    header('Content-Type: application/json');
    echo json_encode(['responseCode' => 999]);
}
?>
