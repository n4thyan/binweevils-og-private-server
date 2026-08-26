<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $nestId = $_POST['nestID'];
    $locId = $_POST['locID'];
    $colour = $_POST['col'];
    $colourTypes = explode(',', urldecode($colour));

    if(intval($colourTypes[0]) > 60 || intval($colourTypes[0]) < -120 || intval($colourTypes[1]) > 60 || intval($colourTypes[1]) < -120 || intval($colourTypes[2]) > 60 || intval($colourTypes[2]) < -120) {
        echo 'responseCode=999&message=Nice try <3';
    }
    else {
        setLocColour($locId, $colour);
    }
}
?>