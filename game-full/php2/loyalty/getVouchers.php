<?php
// loyalty/getVouchers.php
// Recovered contract from loyaltyCard_28_11_13.swf (LoyaltyCardMyVouchers class).
//
// Client POSTs (secure, JSON response):
//   userIDX=<user_id>  timer=<getTimer()>  hash=<md5>
//
// Server returns JSON:
//   responseCode = 1  -> success
//   vouchers[]   = array of { type, value } (type is the award type string,
//                  value is the integer amount)
//
// Vouchers are the item/currency rewards accumulated from loyalty card stamps
// (types: hat, sws, binmartDosh, nestcoDosh, storepc, storemulch, move).
// Currency rewards (mulch/dosh/xp) are applied immediately on stamping and
// are NOT returned as vouchers.

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

        $vouchers = [];
        $q = $db->prepare(
            "SELECT type, value FROM loyalty_vouchers WHERE user_id = ? AND redeemed = 0 ORDER BY id ASC"
        );
        $q->bind_param('i', $idx);
        $q->execute();
        $res = $q->get_result();
        while ($row = $res->fetch_assoc()) {
            $vouchers[] = [
                "type"  => $row['type'],
                "value" => intval($row['value']),
            ];
        }

        header('Content-Type: application/json');
        echo json_encode([
            "responseCode" => 1,
            "vouchers"     => $vouchers,
        ]);
    } else {
        echo json_encode(["responseCode" => 999, "message" => "Invalid request hash."]);
    }
} else {
    echo json_encode(["responseCode" => 999, "message" => "User is not logged in."]);
}
?>
