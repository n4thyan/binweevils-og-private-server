<?php
// loyalty/finalReward.php
// Recovered contract from loyaltyCard_28_11_13.swf (collectAllPuzzlePieces).
//
// Client POSTs (secure, query-string response):
//   idx=<user_id>  timer=<getTimer()>  hash=<md5>
//
// Called when the player completes card 16 (LAST_CARD_NUM) — i.e. all 30
// stamps earned on the final card. This collects the final set of puzzle
// pieces / completes the loyalty card meta-achievement.
//
// Server returns:
//   responseCode=1  -> success
//   responseCode=999 -> error
//
// Non-JSON response (the client's sendAndAwaitResponse uses $isJSONResponse=false
// for this call, so the response is read as POST variables, not JSON).

error_reporting(0);
include('../../essential/backbone.php');

if (!isset($_POST) || empty($_POST)) {
    header('Content-Type: text/plain');
    echo "responseCode=999&message=No POST data";
    exit;
}

$hash   = isset($_POST['hash']) ? $_POST['hash'] : '';
$idx    = isset($_POST['idx'])   ? intval($_POST['idx']) : 0;
$timer  = isset($_POST['timer']) ? $_POST['timer'] : 0;

$weevilData = getAllWeevilStatsByName($_COOKIE['weevil_name'] ?? '');

if ($weevilData != null && isset($weevilData["id"]) && $weevilData["id"] == $idx) {
    if (checkHash(["hash" => $hash, "idx" => $idx, "timer" => $timer])) {

        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        // Load the user's loyalty card.
        $q = $db->prepare("SELECT * FROM loyalty_cards WHERE user_id = ? ORDER BY id DESC LIMIT 1");
        $q->bind_param('i', $idx);
        $q->execute();
        $cardRow = $q->get_result()->fetch_assoc();

        if (!$cardRow) {
            echo "responseCode=999&message=Loyalty card not found";
            exit;
        }

        $cardNum    = intval($cardRow['cardNum']);
        $numStamped = intval($cardRow['stamps']);

        // Only allow finalReward when card 16 is fully stamped (30/30).
        if ($cardNum != 16 || $numStamped < 30) {
            echo "responseCode=3&message=Card not complete";
            exit;
        }

        // If already completed, don't re-grant.
        if (intval($cardRow['completed']) == 1) {
            echo "responseCode=1";
            exit;
        }

        // --- Mark the final card as completed ---
        $uq = $db->prepare("UPDATE loyalty_cards SET completed = 1 WHERE user_id = ? AND cardNum = ?");
        $uq->bind_param('ii', $idx, $cardNum);
        $uq->execute();
        $uq->close();

        echo "responseCode=1";
    } else {
        echo "responseCode=999&message=Invalid request hash";
    }
} else {
    echo "responseCode=999&message=User is not logged in";
}
?>
