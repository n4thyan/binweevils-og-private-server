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

                // Achievement: record buy_seed after authoritative success.
                $achDb = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $achDb->begin_transaction();
                try {
                    $svc = new AchievementService((int)$weevilData['id'], $_COOKIE['weevil_name'], $achDb);
                    $svc->recordActivity('buy_seed', (int)$seedId, (int)$quantity, null, true);
                    $newIds = $svc->evaluateForActivity('buy_seed', (int)$seedId, (int)$quantity, null);
                    $achDb->commit();
                    $achCsv = $newIds ? implode(',', $newIds) : '0';
                } catch (Throwable $e) {
                    $achDb->rollback();
                    $achCsv = '0';
                }
                $achDb->close();

                echo "err=10&mulch=".strval($weevilData['mulch']-$seedData['price']*$quantity)."&xp=".strval($weevilData['xp'])."&quantityPurchased=".strval($quantity)."&price=".strval($seedData['price']*$quantity)."&completedAchievements=".$achCsv;
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