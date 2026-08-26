<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $weevilname = $_POST['id'];
    echo weevilData((strpos($weevilname, ':') !== false ? substr(str_replace(' : ', '', $weevilname), 0, -1) : $weevilname));
}
else echo 'res=999';
?>