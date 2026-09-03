<?php
// loyalty/getProgress.php
// Recovered contract from loyaltyCard_28_11_13.swf (LAST_CARD_NUM=16, NUM_STAMPS=30).
//
// Client POSTs (secure, JSON response):
//   userIDX=<user_id>  timer=<getTimer()>  hash=<md5>
//
// Server returns JSON:
//   responseCode = 1  -> can stamp (progress load success)
//   responseCode = 2  -> already stamped today
//   cardNum    = current card number (1..16)
//   numStamped = stamps collected on current card (0..30)
//   awards[]   = array of { stampNum, type, value, tycoonOnly }
//
// Security: validates session cookie + checkHash + posted ID matches session user.
// Reward table is read from the DB (loyalty_card_rewards), never trusted from client.

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

        // --- Load or initialise the user's loyalty card ---
        $q = $db->prepare("SELECT * FROM loyalty_cards WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $q->bind_param('i', $idx);
        $q->execute();
        $cardRow = $q->get_result()->fetch_assoc();

        if (!$cardRow) {
            // First-time user: create card 1, zero stamps.
            $ins = $db->prepare(
                "INSERT INTO loyalty_cards (user_id, cardNum, stamps, lastStampDay, completed) VALUES (?, 1, 0, NULL, 0)"
            );
            $ins->bind_param('i', $idx);
            $ins->execute();
            $ins->close();

            $cardRow = [
                'cardNum'      => 1,
                'stamps'       => 0,
                'lastStampDay' => null,
                'completed'    => 0,
            ];
        }

        $cardNum    = intval($cardRow['cardNum']);
        $numStamped = intval($cardRow['stamps']);

        // --- Build the awards array for this card ---
        $awards = [];
        $aq = $db->prepare(
            "SELECT stampNum, type, value, tycoonOnly FROM loyalty_card_rewards WHERE card = ? ORDER BY stamp"
        );
        $aq->bind_param('i', $cardNum);
        $aq->execute();
        $ares = $aq->get_result();
        while ($row = $ares->fetch_assoc()) {
            $awards[] = [
                'stampNum'    => intval($row['stamp']),
                'type'        => $row['type'],
                'value'       => intval($row['value']),
                'tycoonOnly'  => intval($row['tycoonOnly']),
            ];
        }
        $aq->close();

        // --- responseCode: 2 = already stamped today, 1 = can stamp ---
        $today        = date('Y-m-d');
        $lastStampDay = $cardRow['lastStampDay'];
        $responseCode = ($lastStampDay === $today) ? 2 : 1;

        header('Content-Type: application/json');
        echo json_encode([
            "responseCode" => $responseCode,
            "cardNum"      => $cardNum,
            "numStamped"   => $numStamped,
            "awards"       => $awards,
        ]);
    } else {
        echo json_encode(["responseCode" => 999, "message" => "Invalid request hash."]);
    }
} else {
    echo json_encode(["responseCode" => 999, "message" => "User is not logged in."]);
}
?>
