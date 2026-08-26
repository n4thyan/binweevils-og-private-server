<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $weevilDef = $_POST['weevilDef'];
    $hash = $_POST['hash'];
    $st = $_POST['st'];

    if(checkHash(["hash" => $hash, "weevilDef" => $weevilDef, "st" => $st]))
    echo changeDefinition($weevilDef);
    else
    echo 'responseCode=999';
}
else echo 'responseCode=999';
?>