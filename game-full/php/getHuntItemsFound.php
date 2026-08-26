<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $compID = $_POST['compID'];

    if(isHuntAvailable($compID)) {
        $huntItems = getHuntItemsFoundById($compID);
        $items = "";

        foreach($huntItems as $item) {
            $items .= $item["itemID"] . ',';
        }

        echo 'res=' . substr($items, 0, -1);
    }
    else
    echo 'res=999';
}
else
echo 'res=999';
?>