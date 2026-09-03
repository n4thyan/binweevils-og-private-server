<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $itemId = $_POST['id'];
    $colour = $_POST['colour'];
    $hash = $_POST['hash'];
    $st = $_POST['st'];

    if(!checkHash(["hash" => $hash, "id" => $itemId, "colour" => $colour, "st" => $st])) {
        echo 'res=999';
        return;
    }

    $weevilData = getAllWeevilStatsByName($_COOKIE['weevil_name']);
    $itemData = getGardenItemDataById($itemId);

    if($weevilData['level'] >= $itemData['minLevel']){
        if($weevilData['mulch'] > $itemData['price']){
            if($colour != $itemData['defaultHexcolour']){
                echo "res=999&message=nice try ;)";
            }
            else{
                $bought = BuyGardenItem($itemId, $colour);
                if($bought == true){
                    removeMulch($weevilData['id'], $itemData['price']);
                    addExperience($weevilData['id'], $itemData['expPoints']);

                    // Achievement: record buy_garden_item after authoritative success.
                    $achDb = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                    $achDb->begin_transaction();
                    try {
                        $svc = new AchievementService((int)$weevilData['id'], $_COOKIE['weevil_name'], $achDb);
                        $svc->recordActivity('buy_garden_item', (int)$itemId, 1, null, true);
                        $newIds = $svc->evaluateForActivity('buy_garden_item', (int)$itemId, 1, null);
                        $achDb->commit();
                        $achCsv = $newIds ? implode(',', $newIds) : '0';
                    } catch (Throwable $e) {
                        $achDb->rollback();
                        $achCsv = '0';
                    }
                    $achDb->close();

                    echo "err=10&mulch=".strval($weevilData['mulch']-$itemData['price'])."&xp=".strval($weevilData['xp'] + $itemData['expPoints'])."&quantityPurchased=1&price=".$itemData['price']."&completedAchievements=".$achCsv;
                }
                else{
                    echo 'res=999';
                }
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