<?php
error_reporting(0);
include('../../../essential/backbone.php');

// Featured rail feed. Historically a single hardcoded promo list (mixed
// mulch+dosh) was returned for BOTH stores, so Nestco's Featured showed dosh
// items (e.g. Thugg Cake) that belong in BinMart. Make it currency-split:
//   nestco  -> mulch-only featured
//   binmart -> dosh-only featured
// The served SWFs call this without a storeName, so default to the Nestco
// (mulch) store to avoid leaking dosh items into Nestco's Featured tab.
$storeName = isset($_POST['storeName']) ? $_POST['storeName'] : 'nestco';
$storeMap = array(
    'binmart' => array('shopType' => 'nestco', 'currency' => 'dosh'),
    'nestco'  => array('shopType' => 'nestco', 'currency' => 'mulch'),
);
$map = isset($storeMap[$storeName]) ? $storeMap[$storeName] : $storeMap['nestco'];

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
// category 666 is the "featured" set; currency restricts to this store.
//
// DUPLICATE FIX (audited 2026-08-27): the previous query used
// `GROUP BY configLocation`, which collapsed rows sharing one asset (e.g. the
// five Wall Spot Light rows 2818-2821,2827 all on f_wallLight1) but NOT rows
// sharing a display NAME across different configLocations. The DB holds five
// "Floating Candle" rows (2840-2843,1705) on different configLocations, so two
// of them (2843 orange + 2841 pink) both surfaced and the Featured rail showed
// "Floating Candle" twice. The user-visible identity of a featured tile is its
// NAME, so dedupe on name. Also: `SELECT *` with `GROUP BY` is
// non-deterministic (and illegal under ONLY_FULL_GROUP_BY) — pick the row
// explicitly via a max-purchases subquery so the winner is stable.
$sql = "SELECT i.* FROM itemtype i
        JOIN (
            SELECT `name`, MAX(`purchases`) AS mp
            FROM itemtype
            WHERE `canBuy` = 1 AND `shopType` = ? AND `currency` = ? AND `category` = 666
            GROUP BY `name`
        ) top ON top.`name` = i.`name` AND top.mp = i.`purchases`
        WHERE i.`canBuy` = 1 AND i.`shopType` = ? AND i.`currency` = ? AND i.`category` = 666
        GROUP BY i.`name`
        ORDER BY i.`purchases` DESC
        LIMIT 6;";
$q = $db->prepare($sql);
$q->bind_param('ssss', $map['shopType'], $map['currency'], $map['shopType'], $map['currency']);
$q->execute();
$res = $q->get_result();
$items = $res ? $res->fetch_all() : array();

$out = '{"responseCode":1,"featured":[';
$parts = array();
// JSON string escaping. NOTE: this file previously used addslashes(), which
// escapes an apostrophe as \' — that is NOT valid JSON, and the SWF's decoder
// (com.adobe.serialization.json) rejects the whole response with an "Invalid
// escape". Any featured item whose description contains an apostrophe (e.g.
// Cobweb Chair, "Careful you don't get stuck!") therefore killed the entire
// Featured rail. Pre-existing bug, affected BinMart. Escape per JSON rules.
$jesc = function($s) {
    return str_replace(
        array('\\', '"', "\r", "\n", "\t"),
        array('\\\\', '\\"', '\\r', '\\n', '\\t'),
        $s
    );
};
foreach ($items as $item) {
    $itemTypeId = $item[0];
    $configLocation = $item[2];
    $currency = $item[6];
    $price = $item[7];
    $name = $item[11];
    $description = $item[13];
    $minLevel = $item[19];
    $tycoonOnly = $item[20];
    $expPoints = $item[15];
    $parts[] = '{"featureType":"item","displayOrder":"-1","itemTypeId":"'.$itemTypeId.'","name":"'.$jesc($name).'","description":"'.$jesc($description).'","configLocation":"'.$configLocation.'","img":"'.$configLocation.'","currency":"'.$currency.'","price":"'.$price.'","minLevel":"'.$minLevel.'","tycoonOnly":"'.$tycoonOnly.'","expPoints":"'.$expPoints.'"}';
}
$out .= implode(',', $parts) . ']}';
echo $out;
?>
