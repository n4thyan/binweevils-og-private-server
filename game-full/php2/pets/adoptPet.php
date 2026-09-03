<?php
error_reporting(0);
include('../../essential/backbone.php');

// Adopt a Bin Pet: writes a pets row for the logged-in weevil, deducts the
// shop item's currency, and grants XP. Follows the existing php2/pets auth
// pattern (checkHash + getAllWeevilStatsByName). Rate-limited per Checkpoint D.
if(isset($_POST)) {
    $hash = $_POST['hash'];
    $idx = $_POST['idx'];
    $timer = $_POST['timer'];
    $itemTypeID = isset($_POST['itemTypeID']) ? intval($_POST['itemTypeID']) : 0;
    $petName = isset($_POST['petName']) ? trim($_POST['petName']) : '';
    // Bin Pet visual fields sent by the petBuilder UI (body/accessory/eye colours).
    // Accept them if present; default to a valid non-zero so the renderer never
    // draws a broken/blank pet when the builder omits one.
    $bc  = isset($_POST['bc'])  ? intval($_POST['bc'])  : 1;
    $ac1 = isset($_POST['ac1']) ? intval($_POST['ac1']) : 0;
    $ac2 = isset($_POST['ac2']) ? intval($_POST['ac2']) : 0;
    $ec1 = isset($_POST['ec1']) ? intval($_POST['ec1']) : 1;
    $ec2 = isset($_POST['ec2']) ? intval($_POST['ec2']) : 0;

    $weevilData = getAllWeevilStatsByName($_COOKIE['weevil_name']);

    if($weevilData != null && $weevilData["id"] == $idx) {
        if(checkHash(["hash" => $hash, "idx" => $idx, "itemTypeID" => $itemTypeID, "timer" => $timer])
           && $weevilData['username'] == $_COOKIE['weevil_name']) {

            // Checkpoint D (A7/A8 spirit): throttle adoptions per IP.
            if(!rateLimit('adopt-pet', 10, 300)) {
                echo 'res=429';
                exit;
            }

            // Validate the chosen item is actually a Bin Pet shop item.
            $itemData = getItemDataById($itemTypeID);
            if($itemData == null || $itemData['shopType'] != 'binPetShop' || $itemData['canBuy'] != 1) {
                echo 'res=998';
                exit;
            }

            // Pet name rules (reuse username-style sanity: 3-16 chars, no empty).
            if(strlen($petName) < 2 || strlen($petName) > 16) {
                echo 'res=997';
                exit;
            }

            $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

            // One pet per species per owner is fine; cap total pets at a sane number.
            $cap = $db->prepare("SELECT COUNT(*) FROM pets WHERE ownerID = ?");
            $cap->bind_param('s', $weevilData['username']);
            $cap->execute();
            $capRes = $cap->get_result()->fetch_array();
            if(intval($capRes[0]) >= 12) {
                echo 'res=996';
                exit;
            }

            // Currency check + deduct (mirrors buyDoshShopItem.php).
            if($itemData['currency'] == "mulch") {
                if($weevilData['mulch'] < $itemData['price']) { echo 'responseCode=4'; exit; }
                $paid = removeMulch($weevilData['id'], $itemData['price']);
            } else {
                if($weevilData['dosh'] < $itemData['price']) { echo 'responseCode=4'; exit; }
                $paid = removeDosh($weevilData['id'], $itemData['price']);
            }
            if($paid != true) { echo 'res=999'; exit; }

            // Insert the adopted pet with sensible starting stats + visual fields.
            $ownerID = $weevilData['username'];
            $adoptedDate = date('Y-m-d H:i:s');
            $nameHash = hash_hmac('sha256', strtolower($petName), IP_HASH_SECRET);
            $ins = $db->prepare(
                "INSERT INTO pets (ownerID, name, bedID, bowlID, bc, ac1, ac2, ec1, ec2, fuel, mentalEnergy, health, fitness, experience, adoptedDate, nameHash) " .
                "VALUES (?, ?, 0, 0, ?, ?, ?, ?, ?, 100, 100, 100, 0, 0, ?, ?)"
            );
            // 11 columns -> 11 type chars (s,s,i,i,i,i,i,i,s,s,s).
            $ins->bind_param('ssiiiiissss', $ownerID, $petName, $bc, $ac1, $ac2, $ec1, $ec2, $adoptedDate, $nameHash);
            $ins->execute();

            if($ins->affected_rows == 1) {
                $petID = $ins->insert_id;
                addExperience($weevilData['id'], $itemData['expPoints']);

                // Record adopt_pet activity for achievement id 2.
                $achDbP = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $achDbP->begin_transaction();
                try {
                    $svcP = new AchievementService((int)$weevilData['id'], $_COOKIE['weevil_name'], $achDbP);
                    $svcP->recordActivity('adopt_pet', null, 1, null, true);
                    $newIdsP = $svcP->evaluateForActivity('adopt_pet');
                    $achDbP->commit();
                    $achCsvP = $newIdsP ? implode(',', $newIdsP) : '0';
                } catch (Throwable $e) {
                    $achDbP->rollback();
                    $achCsvP = '0';
                }
                $achDbP->close();

                // Also write to the legacy userachievements table for compatibility.
                $legacy = $db->prepare("INSERT IGNORE INTO userachievements (userID, achievementID) VALUES (?, 2)");
                $legacy->bind_param('i', $weevilData['id']);
                $legacy->execute();
                $legacy->close();

                echo 'responseCode=1&petID=' . $petID . '&mulch=' . ($weevilData['mulch'] - ($itemData['currency']=="mulch"?$itemData['price']:0)) . '&dosh=' . ($weevilData['dosh'] - ($itemData['currency']=="dosh"?$itemData['price']:0)) . '&completedAchievements=' . $achCsvP;
            } else {
                echo 'res=999';
            }
        }
        else
        echo 'responseCode=999';
    }
    else
    echo 'responseCode=999';
}
else
echo 'res=999';
?>
