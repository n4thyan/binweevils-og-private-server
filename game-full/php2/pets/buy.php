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

// The working Bin Pets package sends bowl selector codes 20,33..40. This
// database stores the corresponding colour-specific bowls at 2625..2633, but
// the bed is NOT colour-specific: it is item 2634 (`f_petBasket2`) tinted with
// the separately posted bedColour. Never synthesize `f_petBed_*` item IDs.
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
$PET_BASKET_ITEM = 2634;
$PET_BODY_COLOURS = [8913032, 43520, 11198463, 26367, 15597568, 16750848, 16763904, 16745604];
$PET_BED_COLOURS = [16759552, 15597568, 16777215, 65314, 16764108, 238, 13369565, 16776960, 3158064];

function resolvePetBowl($bowlItemTypeId) {
    global $BOWL_TYPE_TO_ITEM;
    if(array_key_exists($bowlItemTypeId, $BOWL_TYPE_TO_ITEM)) return $BOWL_TYPE_TO_ITEM[$bowlItemTypeId];
    if($bowlItemTypeId >= 2625 && $bowlItemTypeId <= 2633) return $bowlItemTypeId;
    return 0;
}

// buy.php — called by the petBuilder SWF to complete a Bin Pet adoption.
// Real contract captured from the live SWF POST:
//   name, bc, ac1, ac2, ec1, ec2, bowlItemTypeId, bedColour
//   (NO posted hash/userIDX/itemTypeID — auth is via the session cookie).
// The SWF renders the result as "ERROR:<code>", so success = error=0.
// Adoption price is 5000 mulch (hardcoded in the SWF's UI), deducted here.
if(isset($_POST)) {
    // Auth via session cookie (getAllWeevilStatsByName validates the session).
    $weevilData = getAllWeevilStatsByName($_COOKIE['weevil_name']);
    if(!is_array($weevilData) || !isset($weevilData['id'])) {
        echo 'error=999';
        exit;
    }

    $petName = isset($_POST['name']) ? trim($_POST['name']) : (isset($_POST['petName']) ? trim($_POST['petName']) : '');
    $bc  = isset($_POST['bc']) ? intval($_POST['bc']) : 0;
    // Canonical builder contract: arms remain black and eyelids match the body.
    $ac1 = 2631720;
    $ac2 = 2631720;
    $ec1 = $bc;
    $ec2 = $bc;
    $bedColour = isset($_POST['bedColour']) ? intval($_POST['bedColour']) : 0;
    $bowlItemTypeId = isset($_POST['bowlItemTypeId']) ? intval($_POST['bowlItemTypeId']) : 0;
    $BOWL_ITEM = resolvePetBowl($bowlItemTypeId);
    $BED_ITEM = $PET_BASKET_ITEM;

    if(!in_array($bc, $PET_BODY_COLOURS, true) ||
       !in_array($bedColour, $PET_BED_COLOURS, true) ||
       $BOWL_ITEM === 0) {
        echo 'error=4';
        exit;
    }

    // Canonical pet names are 1-10 ASCII letters.
    if(strlen($petName) == 0 || strlen($petName) > 10 || !preg_match('/^[a-zA-Z]+$/', $petName)) {
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

    // Grant the colour-specific bowl and the canonical colour-tinted basket. The
    // returned ownership row IDs are persisted on the pet as bowlID/bedID.
    $bowlId = grantRewardItem($weevilData['id'], $BOWL_ITEM, 0);
    $bedId  = grantRewardItem($weevilData['id'], $BED_ITEM, $bedColour);
    if(!$bowlId || !$bedId) {
        if($bowlId) $db->query("DELETE FROM weevilitems WHERE ID = " . intval($bowlId) . " AND weevilID = " . intval($weevilData['id']));
        if($bedId) $db->query("DELETE FROM weevilitems WHERE ID = " . intval($bedId) . " AND weevilID = " . intval($weevilData['id']));
        echo 'error=998';
        exit;
    }

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
        $petID = $ins->insert_id;

        // Canonical package contract: every adopted pet receives the full default
        // skill tree and all juggling definitions before adoption is acknowledged.
        $skillsInserted = insertPetSkills($ownerID, $petID);
        $tricksInserted = insertPetJugglingTricks($ownerID, $petID);
        if(!$skillsInserted || !$tricksInserted) {
            $cleanup = $db->prepare("DELETE FROM petacquiredskills WHERE ownerID = ? AND petID = ?");
            $cleanup->bind_param('si', $ownerID, $petID);
            $cleanup->execute();
            $cleanup = $db->prepare("DELETE FROM petacquiredtricks WHERE ownerID = ? AND petID = ?");
            $cleanup->bind_param('si', $ownerID, $petID);
            $cleanup->execute();
            $cleanup = $db->prepare("DELETE FROM pets WHERE id = ? AND ownerID = ?");
            $cleanup->bind_param('is', $petID, $ownerID);
            $cleanup->execute();
            $cleanup = $db->prepare("DELETE FROM weevilitems WHERE ID IN (?, ?) AND weevilID = ?");
            $weevilId = intval($weevilData['id']);
            $cleanup->bind_param('iii', $bowlId, $bedId, $weevilId);
            $cleanup->execute();
            echo 'res=996';
            exit;
        }

        // Pet state is complete — only now charge and grant rewards.
        removeMulch($weevilData['id'], $PRICE);
        addExperience($weevilData['id'], 50);
        // Bestow the "Adopt a Bin Pet" achievement (id 2). Guarded: the live DB has
        // `achievementscompleted`, not `userachievements`, so wrap so a missing/renamed
        // table can NEVER swallow the success response (that was the empty-body hang).
        @$db->query("INSERT INTO achievementscompleted (idx, achievementId) VALUES (" . intval($weevilData['id']) . ", 2) ON DUPLICATE KEY UPDATE achievementId = 2");
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
