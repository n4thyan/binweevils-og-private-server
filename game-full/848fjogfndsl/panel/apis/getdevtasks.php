<?php
include '../../../essential/backbone.php';
include '../../../essential/admin_guard.php';
enforceAdminAccess();
try{
    $page = $_GET['pageindex'];
    $developmentProg = getNextPage(intval($page));
    echo $developmentProg;
}
catch(Exception $e){
    echo "";
}

?>