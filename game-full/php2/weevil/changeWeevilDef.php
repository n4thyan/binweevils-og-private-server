<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $weevilDef = $_POST['def'];
    $idx = $_POST['idx'];
    $hash = $_POST['hash'];
    $timer = $_POST['timer'];

    if(checkHash(["hash" => $hash, "idx" => $idx, "def" => $weevilDef, "timer" => $timer]))
    echo changeWeevilDefinition($weevilDef);
    else
    echo 'responseCode=999';
}
else echo 'responseCode=999';
?>