<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_GET)) {
    $hats = GetBuyableHats();

    if($hats != null)
    echo getInXmlFormat($hats);
    else echo 'responseCode=999';
}
else echo 'responseCode=999';
?>