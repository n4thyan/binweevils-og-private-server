<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $weevils = urldecode($_POST['weevils']);
    echo getIgnoreListDefs($weevils);
}
else echo json_encode(["responseCode" => 999]);
?>