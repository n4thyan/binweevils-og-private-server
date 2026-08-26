<?php
error_reporting(0);
include('../../essential/backbone.php');
// Stub: php2/weevil/change-definition (canonical path).
if(isset($_POST)) {
    $weevilDef = isset($_POST['weevilDef']) ? $_POST['weevilDef'] : '';
    $hash = isset($_POST['hash']) ? $_POST['hash'] : '';
    $st = isset($_POST['st']) ? $_POST['st'] : '';
    if(checkHash(['hash' => $hash, 'weevilDef' => $weevilDef, 'st' => $st]))
        echo changeDefinition($weevilDef);
    else
        echo 'responseCode=999';
}
else echo 'responseCode=999';
?>
