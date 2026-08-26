<?php
error_reporting(0);
include('essential/backbone.php');

// user — called by the SWF to fetch the logged-in weevil's core profile/stats.
// Returns the weevil's basic data as a valid JSON envelope (responseCode=1).
if(isset($_POST) || isset($_GET)) {
    $weevilData = getAllWeevilStatsByName($_COOKIE['weevil_name']);
    if($weevilData != null && is_array($weevilData)) {
        header('Content-Type: application/json');
        echo json_encode([
            'responseCode' => 1,
            'id'          => $weevilData['id'],
            'username'    => $weevilData['username'],
            'level'       => $weevilData['level'],
            'mulch'       => $weevilData['mulch'],
            'dosh'        => $weevilData['dosh'],
            'xp'          => $weevilData['xp'],
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['responseCode' => 999, 'message' => 'User not logged in.']);
    }
}
else {
    header('Content-Type: application/json');
    echo json_encode(['responseCode' => 999]);
}
?>
