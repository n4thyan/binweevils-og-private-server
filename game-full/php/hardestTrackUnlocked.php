<?php
error_reporting(0);
include('../../essential/backbone.php');
// Stub: hardestTrackUnlocked. Reports the player's hardest unlocked race track.
// Returns a benign success with no unlocked track (feature inactive on this server).
if(isset($_POST)) {
    echo 'responseCode=1&trackID=0&unlocked=0';
}
else echo 'responseCode=999';
?>
