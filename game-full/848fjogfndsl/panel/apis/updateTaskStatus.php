<?php
include '../../../essential/backbone.php';
include '../../../essential/admin_guard.php';
enforceAdminAccess();
try{
    $description = $_GET['description'];
    $status = $_GET['status'];
    updateDevelopmentStatus($description, $status);
}
catch(Exception $e){
    echo "";
}

?>