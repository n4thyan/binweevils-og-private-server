<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $nest = $_POST['nestID'];
    $weevilname = $_POST['userID'];
    $itemId = $_POST['itemID'];

    $weevilData = getAllWeevilStatsByName($nest);
    $ownsNest = checkNest($weevilname, $nest);
    
    if($ownsNest == true){
        $removeItem = removeItemFromNest($nest, $itemId);
        if($removeItem == false)
        echo 'responseCode=999';
        else
        echo '{"responseCode":1}';
    }
    else echo 'responseCode=999';

}
else echo 'responseCode=999';
?>
