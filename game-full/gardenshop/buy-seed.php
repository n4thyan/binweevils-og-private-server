<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $seedId = $_POST['id'];
    $quantity = $_POST['quantity'];
    $hash = $_POST['hash'];
    $st = $_POST['st'];

    if(!checkHash(["hash" => $hash, "id" => $seedId, "quantity" => $quantity, "st" => $st]) || intval($quantity) > 25 || intval($quantity) < 0) {
        echo 'res=999';
        return;
    }

    $weevilData = getAllWeevilStatsByName($_COOKIE['weevil_name']);
    $seedData = getSeedDataById($seedId);

    if($weevilData['level'] >= $seedData['level']) {
        if($weevilData['mulch'] >= ($seedData['price'] * intval($quantity))) {
            $bought = BuySeed($seedId, $quantity);
            if($bought == true){
                removeMulch($weevilData['id'], $seedData['price']*$quantity);
                echo "err=10&mulch=".strval($weevilData['mulch']-$seedData['price']*$quantity)."&xp=".strval($weevilData['xp'])."&quantityPurchased=".strval($quantity)."&price=".strval($seedData['price']*$quantity);
            }
            else{
                echo 'res=999';
            }
        }
        else{
            echo 'err=13';
        }
    }
    else{
        echo 'err=12';
    }
}
else echo 'res=999&message=nice try ;)';
?>