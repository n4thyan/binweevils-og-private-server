<?php
error_reporting(0);
include('../../essential/backbone.php');

// grantRewardItem: insert an item into weevilitems (mirrors BuyItem's columns) without
// the buyable gate. Returns the new weevilitems ID, or 0 on failure.
// Defined at top level so it is available when called inside the POST handler below.
function grantRewardItem($weevilId, $itemId, $colour) {
    $itemData = getItemDataById($itemId);
    if($itemData == null) return 0;
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $q = $db->prepare("INSERT INTO `weevilitems`(`weevilID`, `itemId`, `colour`, `category`, `configName`) VALUES (?, ?, ?, ?, ?)");
    $q->bind_param('iisss', $weevilId, $itemId, $colour, $itemData['category'], $itemData['configLocation']);
    $q->execute();
    return $q->affected_rows == 1 ? $q->insert_id : 0;
}

// Bowl/basket items are PER-COLOUR itemTypeIDs (2625=Blue .. 2633=Purple). The SWF
// sends `bowlItemTypeId` which is EITHER a bowl-type code (20,33..40) OR the real
// itemTypeID (2625..2633). Map either form to the correct bowl item, and derive the
// matching-colour bed (2855=Blue .. 2863=Purple) so bowl + bed stay in sync.
$BOWL_TYPE_TO_ITEM = [
    20 => 2625, // blue
    33 => 2626, // black
    34 => 2627, // white
    35 => 2628, // red
    36 => 2629, // orange
    37 => 2630, // yellow
    38 => 2631, // green
    39 => 2632, // pink
    40 => 2633, // purple
];
$BED_BASE_ITEM = 2855;

function resolvePetBowlBed($bowlItemTypeId) {
    global $BOWL_TYPE_TO_ITEM, $BED_BASE_ITEM;
    if(array_key_exists($bowlItemTypeId, $BOWL_TYPE_TO_ITEM)) {
        $bowlItem = $BOWL_TYPE_TO_ITEM[$bowlItemTypeId];
        $idx = array_search($bowlItemTypeId, array_keys($BOWL_TYPE_TO_ITEM), true);
        return array($bowlItem, $BED_BASE_ITEM + $idx);
    }
    if($bowlItemTypeId >= 2625 && $bowlItemTypeId <= 2633) {
        return array($bowlItemTypeId, $BED_BASE_ITEM + ($bowlItemTypeId - 2625));
    }
    return array(2625, 2855); // Blue fallback
}

// The default Bin Pet skill tree (skillID => [obedience, skillLevel]). Seeded into
// petacquiredskills at adoption so getPetSkills returns the contract the petBuilder
// SWF expects. skillID 9 is intentionally absent (matches the reference response).
$PET_DEFAULT_SKILLS = array(
    array(1,20,0), array(2,20,0), array(3,100,0), array(4,100,0), array(5,100,0),
    array(6,20,2), array(7,20,0), array(8,100,0), array(10,20,19), array(11,20,0),
    array(12,20,0), array(13,20,0), array(14,20,0), array(15,30,5), array(16,30,10),
    array(17,30,1),
);

// buy.php — called by the petBuilder SWF to complete a Bin Pet adoption.
// Real contract captured from the live SWF POST:
//   name, bc, ac1, ac2, ec1, ec2, bowlItemTypeId, bedColour
//   (NO posted hash/userIDX/itemTypeID — auth is via the session cookie).
// The SWF renders the result as "ERROR:<code>", so success = error=0.
// Adoption price is 5000 mulch (hardcoded in the SWF's UI), deducted here.
if(isset($_POST)) {
    // TEMP DEBUG: capture the exact POST the SWF sends (remove once confirmed).
    file_put_contents(dirname(__FILE__) . '/buy_post_debug.log',
        date('c') . " " . print_r($_POST, true), FILE_APPEND);

    // Auth via session cookie (getAllWeevilStatsByName validates the session).
    $weevilData = getAllWeevilStatsByName($_COOKIE['weevil_name']);
    if(!is_array($weevilData) || !isset($weevilData['id'])) {
        echo 'error=999';
        exit;
    }

    $petName = isset($_POST['name']) ? trim($_POST['name']) : (isset($_POST['petName']) ? trim($_POST['petName']) : '');
    $bc  = isset($_POST['bc'])  ? intval($_POST['bc'])  : 1;
    $ac1 = isset($_POST['ac1']) ? intval($_POST['ac1']) : 0;
    $ac2 = isset($_POST['ac2']) ? intval($_POST['ac2']) : 0;
    $ec1 = isset($_POST['ec1']) ? intval($_POST['ec1']) : 1;
    $ec2 = isset($_POST['ec2']) ? intval($_POST['ec2']) : 0;
    // Each bowl/basket is its OWN per-colour itemTypeID. The SWF sends `bowlItemTypeId`
    // as either a bowl-type code or the real item; resolvePetBowlBed maps it to the
    // correct bowl item AND the matching-colour bed item.
    $bowlItemTypeId = isset($_POST['bowlItemTypeId']) ? intval($_POST['bowlItemTypeId']) : 0;
    list($BOWL_ITEM, $BED_ITEM) = resolvePetBowlBed($bowlItemTypeId);


    // Name rules.
    if(strlen($petName) < 2 || strlen($petName) > 16) {
        echo 'error=1';
        exit;
    }

    // Checkpoint D (A7/A8 spirit): throttle adoptions per IP.
    if(!rateLimit('adopt-pet', 10, 300)) {
        echo 'error=429';
        exit;
    }

    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Cap total pets.
    $cap = $db->prepare("SELECT COUNT(*) FROM pets WHERE ownerID = ?");
    $cap->bind_param('s', $weevilData['username']);
    $cap->execute();
    $capRes = $cap->get_result()->fetch_array();
    if(intval($capRes[0]) >= 12) {
        echo 'error=3';
        exit;
    }

    // Adoption price (matches the SWF's displayed 5000 MULCH).
    // NOTE: do NOT deduct here — only deduct after the pet row is confirmed
    // inserted, so a failed adoption can never eat the player's mulch.
    $PRICE = 5000;
    if($weevilData['mulch'] < $PRICE) {
        echo 'error=4'; // insufficient funds
        exit;
    }

    // Grant the bowl + matching bed items (the SWF picks the colour; we resolve the
    // real per-colour itemTypeIDs). grantRewardItem returns the new weevilitems ID so
    // the pet row can reference them.
    $bowlId = grantRewardItem($weevilData['id'], $BOWL_ITEM, 0);
    $bedId  = grantRewardItem($weevilData['id'], $BED_ITEM, 0);

    // Insert the adopted pet with starting stats + visual fields.
    $ownerID = $weevilData['username'];
    $adoptedDate = date('Y-m-d H:i:s');
    $nameHash = hash_hmac('sha256', strtolower($petName), IP_HASH_SECRET);
    $ins = $db->prepare(
        "INSERT INTO pets (ownerID, name, bedID, bowlID, bc, ac1, ac2, ec1, ec2, fuel, mentalEnergy, health, fitness, experience, adoptedDate, nameHash) " .
        "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 100, 100, 100, 0, 0, ?, ?)"
    );
    // 11 columns -> 11 type chars (s,s,i,i,i,i,i,i,s,s,s). Owner of earlier bug:
    // string was 9 chars for 11 values -> ArgumentCountError -> empty body -> SWF hung.
    $ins->bind_param('ssiiiiissss', $ownerID, $petName, $bedId, $bowlId, $bc, $ac1, $ac2, $ec1, $ec2, $adoptedDate, $nameHash);
    $ins->execute();

    if($ins->affected_rows == 1) {
        // Pet inserted successfully — only NOW deduct the adoption price.
        removeMulch($weevilData['id'], $PRICE);
        $petID = $ins->insert_id;
        addExperience($weevilData['id'], 50);
        // Bestow the "Adopt a Bin Pet" achievement (id 2). Guarded: the live DB has
        // `achievementscompleted`, not `userachievements`, so wrap so a missing/renamed
        // table can NEVER swallow the success response (that was the empty-body hang).
        @$db->query("INSERT INTO achievementscompleted (idx, achievementId) VALUES (" . intval($weevilData['id']) . ", 2) ON DUPLICATE KEY UPDATE achievementId = 2");
        // Seed the pet's default skill tree into petacquiredskills so getPetSkills
        // returns the contract the petBuilder SWF expects on first inspection.
        $skStmt = $db->prepare("INSERT INTO petacquiredskills (ownerID, petID, skillID, obedience, skillLevel) VALUES (?, ?, ?, ?, ?)");
        foreach($PET_DEFAULT_SKILLS as $s) {
            $skStmt->bind_param('siiii', $ownerID, $petID, $s[0], $s[1], $s[2]);
            $skStmt->execute();
        }
        $newMulch = $weevilData['mulch'] - $PRICE;
        $newDosh  = $weevilData['dosh'];
        // Contract the petBuilder SWF expects (matches the rest of the php2 family):
        //   res=1&...&completedAchievements=N   (achievement id 2 = Adopt a Bin Pet)
        echo 'res=1&completedAchievements=2&petID=' . $petID . '&mulch=' . $newMulch . '&dosh=' . $newDosh . '&xp=' . ($weevilData['xp'] + 50);
    } else {
        echo 'error=999';
    }

}
else {
    echo 'error=999';
}
?>
