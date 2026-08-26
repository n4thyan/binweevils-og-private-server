<?php
error_reporting(0);
include('../../../essential/backbone.php');

if(isset($_POST)) {
    $gameId = $_POST['gameId'];
    $score = $_POST['score'];
    echo submitScore($gameId, $score);
}
else echo 'responseCode=999';
?>