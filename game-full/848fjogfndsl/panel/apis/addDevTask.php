<?php
include '../../../essential/backbone.php';
include '../../../essential/admin_guard.php';
enforceAdminAccess();
try{
    $name = $_GET['name'];
    $description = $_GET['description'];
    $icon = $_GET['icon'];
    AddDevelopmentTask($name, $description, $icon);
}
catch(Exception $e){
    echo "";
}

?>