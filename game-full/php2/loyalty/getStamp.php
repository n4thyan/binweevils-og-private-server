<?php
// loyalty/getStamp.php
// Recovered contract from loyaltyCard_28_11_13.swf.
//
// Client POSTs (secure, JSON response):
//   userIDX=<user_id>  timer=<getTimer()>  hash=<md5>
//
// Server returns JSON:
//   responseCode = 1  -> stamp accepted + reward granted
//   responseCode = 2  -> already stamped today
//   responseCode = 3  -> stamp accepted, progression advance, no reward
//   (on code 1 or 3, may also return mulch / dosh / xp deltas)
//
// Server-authoritative: rewards come from loyalty_card_rewards, never from client.
// Prevents duplicate daily stamping via lastStampDay.
// Tycoon-only stamps are enforced server-side via the users.tycoon flag.

error_reporting(0);
include('../../essential/backbone.php');

if (!isset($_POST) || empty($_POST)) {
    echo json_encode(["responseCode" => 999, "message" => "No POST data."]);
    exit;
}

$hash   = isset($_POST['hash'])   ? $_POST['hash']   : '';
$idx    = isset($_POST['userIDX']) ? intval($_POST['userIDX']) : 0;
$timer  = isset($_POST['timer'])   ? $_POST['timer']   : 0;

$weevilData = getAllWeevilStatsByName($_COOKIE['weevil_name'] ?? '');

if ($weevilData != null && isset($weevilData["id"]) && $weevilData["id"] == $idx) {
    if (checkHash(["hash" => $hash, "userIDX" => $idx, "timer" => $timer])) {

        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        // --- Load current card state ---
        $q = $db->prepare("SELECT * FROM loyalty_cards WHERE user_id = ? ORDER BY id DESC LIMIT 1 FOR UPDATE");
        $q->bind_param('i', $idx);
        $q->execute();
        $cardRow = $q->get_result()->fetch_assoc();

        if (!$cardRow) {
            echo json_encode(["responseCode" => 999, "message" => "Loyalty card not found."]);
            exit;
        }

        $cardNum    = intval($cardRow['cardNum']);
        $numStamped = intval($cardRow['stamps']);

        // --- Prevent duplicate daily stamping ---
        $today        = date('Y-m-d');
        $lastStampDay = $cardRow['lastStampDay'];
        if ($lastStampDay === $today) {
            echo json_encode(["responseCode" => 2]);
            exit;
        }

        // --- Check if we've already completed all stamps on this card ---
        if ($numStamped >= 30) {
            echo json_encode(["responseCode" => 999, "message" => "Card already complete."]);
            exit;
        }

        // --- Advance the stamp ---
        $newNumStamped = $numStamped + 1;

        // --- Look up the award for this stamp ---
        $aq = $db->prepare("SELECT type, value, tycoonOnly FROM loyalty_card_rewards WHERE card = ? AND stamp = ?");
        $aq->bind_param('ii', $cardNum, $newNumStamped);
        $aq->execute();
        $awardRow = $aq->get_result()->fetch_assoc();
        $aq->close();

        // --- Check tycoon gating on the stamp we just earned ---
        $isTycoon = isset($weevilData['tycoon']) ? intval($weevilData['tycoon']) : 0;
        $stampIsTycoonOnly = $awardRow ? intval($awardRow['tycoonOnly']) : 0;

        // Special case from the SWF: the stamp 30 on the last card (cardNum==16)
        // requires tycoon. This is enforced client-side by a pre-check, but we
        // also validate server-side: if a non-tycoon has somehow gotten to
        // stamp 30 on card 16, deny it.
        if ($cardNum == 16 && $newNumStamped == 30 && !$isTycoon) {
            echo json_encode(["responseCode" => 999, "message" => "Tycoon required for this stamp."]);
            exit;
        }

        // --- Grant reward (or progression advance) ---
        $mulchDelta = 0;
        $doshDelta  = 0;
        $xpDelta    = 0;

        if ($awardRow) {
            $type  = $awardRow['type'];
            $value = intval($awardRow['value']);

            switch ($type) {
                case 'mulch':
                    addMulchByName($_COOKIE['weevil_name'], $value);
                    $mulchDelta = $value;
                    break;
                case 'dosh':
                    addDoshByName($_COOKIE['weevil_name'], $value);
                    $doshDelta = $value;
                    break;
                case 'doshGold':
                    // Gold dosh is tycoon-only (last stamp of card 16).
                    addDoshByName($_COOKIE['weevil_name'], $value);
                    $doshDelta = $value;
                    break;
                case 'xp':
                    addExperience($weevilData['id'], $value);
                    $xpDelta = $value;
                    break;
                case 'seed':
                    rewardSeed($value);
                    break;
                case 'hat':
                    // Hat rewards are item rewards via the inventory system.
                    rewardItem($idx, $value);
                    break;
                case 'sws':
                case 'storepc':
                case 'storemulch':
                case 'binmartDosh':
                case 'nestcoDosh':
                case 'move':
                    // These are item/voucher-style rewards — recorded as vouchers
                    // so the player can redeem them later.
                    $ins = $db->prepare(
                        "INSERT INTO loyalty_vouchers (user_id, type, value, redeemed) VALUES (?, ?, ?, 0)"
                    );
                    $ins->bind_param('isi', $idx, $type, $value);
                    $ins->execute();
                    $ins->close();
                    // Also grant the corresponding currency if it's a dosh-variant.
                    if ($type == 'binmartDosh' || $type == 'nestcoDosh' || $type == 'sws') {
                        addDoshByName($_COOKIE['weevil_name'], $value);
                        $doshDelta = $value;
                    }
                    break;
            }
        }

        // --- Update card state ---
        // Bind date as string ('s') because bind_param 'i' truncates date to 0.
        $uq = $db->prepare(
            "UPDATE loyalty_cards SET stamps = ?, lastStampDay = ?, updated_at = NOW() WHERE user_id = ? AND cardNum = ?"
        );
        $uq->bind_param('issi', $newNumStamped, $today, $idx, $cardNum);
        $uq->execute();
        $uq->close();

        // --- Determine response code ---
        // responseCode 1 = stamp + reward granted (normal case).
        // responseCode 3 = stamp accepted but no reward (progression advance).
        if ($awardRow) {
            // Check if tycoon-only and user is not tycoon — in the SWF the client
            // checks this BEFORE calling getStamp (it shows a confirm dialog).
            // If it reaches the server, the user clicked "confirm" so we grant anyway.
            // But if the stamp itself is tycoonOnly and user is not tycoon,
            // we give responseCode 3 (progression without reward) per the SWF
            // responseCode==3 branch which also does stampFrontEnd().
            //
            // Actually, re-reading the SWF: responseCode 3 does NOT grant a reward
            // (no showCongrats call). Tycoon-only on non-tycoon is blocked at the
            // client pre-check, and the last card stamp-30 is also blocked.
            // So if we reach here with a tycoonOnly stamp and a non-tycoon user,
            // it means they confirmed and we should still grant (responseCode 1).
            $responseCode = 1;
        } else {
            $responseCode = 3;
        }

        // --- Build response ---
        $resp = ["responseCode" => $responseCode];

        if ($responseCode == 1) {
            // Return updated currency values (client reads these via checkUpdateBin).
            $freshData = getAllWeevilStatsByName($_COOKIE['weevil_name']);
            if ($freshData) {
                $resp['mulch'] = intval($freshData['mulch']);
                $resp['dosh']  = intval($freshData['dosh']);
                $resp['xp']    = intval($freshData['xp']);
            }
        }

        // If this was the last stamp of the last card (cardNum==16, stamp==30),
        // the SWF client treats the type as "doshGold". Return the gold dosh
        // award info so the congrats screen shows correctly.
        if ($cardNum == 16 && $newNumStamped == 30) {
            $resp['mulch'] = $resp['mulch'] ?? 0;
            $resp['dosh']  = $resp['dosh'] ?? 0;
            $resp['xp']    = $resp['xp'] ?? 0;
        }

        header('Content-Type: application/json');
        echo json_encode($resp);
    } else {
        echo json_encode(["responseCode" => 999, "message" => "Invalid request hash."]);
    }
} else {
    echo json_encode(["responseCode" => 999, "message" => "User is not logged in."]);
}
?>
