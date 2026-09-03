<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $size = $_POST['size'] ?? '';
    $feedMap = array(
        "small" => 5,
        "large" => 35
    );
    $costMap = array(
        "small" => 20,
        "large" => 100
    );
    $feeds = $feedMap[$size];
    $cost = $costMap[$size];

    if(!$feeds || !$cost) {
        echo 'res=2';
        exit;
    }

    echo buyPetFood($_COOKIE['weevil_name'], $feeds, $cost);
}
else echo 'res=999';