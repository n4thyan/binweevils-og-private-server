<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $trackID = $_POST['trackID'];
    $track = getTrackDetails($trackID);
    echo 'responseCode=1&trackID='.$trackID.'&file='.$track['file'].'&artist='.$track['artist'].'&title='.$track['title'];
}
else echo 'responseCode=999';
?>