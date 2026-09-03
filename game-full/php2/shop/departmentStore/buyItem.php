<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include('../../../essential/backbone.php');

if(isset($_POST)) {
    $itemId = $_POST['itemTypeID'];
    $userId = $_POST['userIDX'];
    $colour = $_POST['colour'];

    $userData = getAllWeevilStats($userId);
    $itemData = getItemDataById($itemId);

    if($userData['username'] == $_COOKIE['weevil_name']) {
        if(itemCountById($itemId, $userId, $colour) > 800)
        echo 'responseCode=999';
        else {
            if($userData['level'] >= $itemData['minLevel']){
                if($itemData['currency'] == "mulch"){
                    if($userData['mulch'] >= $itemData['price']){
                        $bought = BuyItem($userId, $itemId, $colour);
                        if($bought == true){
                            removeMulch($userId, $itemData['price']);
                            addExperience($userId, $itemData['expPoints']);

                            // Achievement: record activity after authoritative success.
                            $achDb = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                            $achDb->begin_transaction();
                            try {
                                $svc = new AchievementService($userId, $_COOKIE['weevil_name'], $achDb);

                                // buy_item activity
                                $svc->recordActivity('buy_item', $itemId, 1, null, true);

                                // spend_mulch_single_item activity (value = actual price paid)
                                $svc->recordActivity('spend_mulch_single_item', $itemId, $itemData['price'], null, true);

                                $newIds = $svc->evaluateForActivity('buy_item', $itemId, 1, null);
                                $newIds2 = $svc->evaluateForActivity('spend_mulch_single_item', $itemId, $itemData['price'], null);

                                $achDb->commit();
                                $achCsv = implode(',', array_unique(array_merge($newIds, $newIds2)));
                            } catch (Throwable $e) {
                                $achDb->rollback();
                                $achCsv = '0';
                            }
                            $achDb->close();

                            echo "responseCode=1&mulch=".strval($userData['mulch']-$itemData['price'])."&completedAchievements=".$achCsv."&priceCharged=".strval($itemData['price'])."&xp=".strval($userData['xp']+$itemData['expPoints']);
                        }
                        else{
                            echo 'responseCode=999';
                        }
                    }
                    else echo 'responseCode=3';
                }
                else{
                    if($userData['dosh'] >= $itemData['price']){
                        $bought = BuyItem($userId, $itemId, $colour);
                        if($bought == true){
                            removeDosh($userId, $itemData['price']);
                            addExperience($userId, $itemData['expPoints']);

                            // Achievement: record dosh spend activity after authoritative success.
                            $achDb2 = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                            $achDb2->begin_transaction();
                            try {
                                $svc2 = new AchievementService($userId, $_COOKIE['weevil_name'], $achDb2);
                                $svc2->recordActivity('buy_item', $itemId, 1, null, true);
                                $svc2->recordActivity('spend_dosh_single_item', $itemId, $itemData['price'], null, true);
                                $newIdsD = $svc2->evaluateForActivity('buy_item', $itemId, 1, null);
                                $newIdsD2 = $svc2->evaluateForActivity('spend_dosh_single_item', $itemId, $itemData['price'], null);
                                $achDb2->commit();
                                $achCsv2 = implode(',', array_unique(array_merge($newIdsD, $newIdsD2)));
                            } catch (Throwable $e) {
                                $achDb2->rollback();
                                $achCsv2 = '0';
                            }
                            $achDb2->close();

                            echo "responseCode=1&dosh=".strval($userData['dosh']-$itemData['price'])."&completedAchievements=".$achCsv2."&priceCharged=".strval($itemData['price'])."&xp=".strval($userData['xp']+$itemData['expPoints']);
                        }
                        else{
                            echo 'responseCode=999';
                        }
                    }
                    else echo 'responseCode=3';
                }
            }
            else echo 'responseCode=8';
        }
    }
    else
    echo 'responseCode=999';
}
else{
    echo 'res=999';
}

?>