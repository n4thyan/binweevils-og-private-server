<?php
error_reporting(0);
include('../../../essential/backbone.php');

header('Content-Type: application/json; charset=UTF-8');

// The live 2015 Nestco/BinMart SWFs call this read-only catalogue feed with
// sendAndAwaitResponse(..., secure=false, isJSONResponse=true). Their exact
// request contract is therefore only `tag` + `storeName`; no timer/hash is
// generated. Older secure callers remain supported when they supply both.
$tag = isset($_POST['tag']) ? filter_var($_POST['tag'], FILTER_VALIDATE_INT) : false;
$storeName = isset($_POST['storeName'])
    ? strtolower(trim((string) $_POST['storeName']))
    : (isset($_POST['shopType']) ? strtolower(trim((string) $_POST['shopType'])) : '');

$allowedStores = array('nestco', 'binmart');
$allowedTags = [0, 1, 2, 4, 5, 6, 7, 8, 9, 10, 13, 15, 16, 17, 18, 25, 28, 31, 53, 57, 59];

$valid = $tag !== false
    && in_array((int) $tag, $allowedTags, true)
    && in_array($storeName, $allowedStores, true);

// If a legacy caller elects to sign the request, verify the signature rather
// than silently ignoring it. The live SWF intentionally sends no signature.
if ($valid && (isset($_POST['hash']) || isset($_POST['timer']))) {
    $valid = isset($_POST['hash'], $_POST['timer'])
        && checkHash(array(
            'hash' => (string) $_POST['hash'],
            'storeName' => $storeName,
            'tag' => (int) $tag,
            'timer' => $_POST['timer'],
        ));
}

if (!$valid) {
    echo json_encode(array('responseCode' => 999, 'items' => array()));
    exit;
}

$shopItems = getNestShopItems((int) $tag, $storeName);
$popularRows = getPopularNestShopItems((int) $tag, $storeName);
$popularIds = array();
foreach ($popularRows as $popularRow) {
    $popularIds[(string) $popularRow[0]] = true;
}

$items = array();
$regularOrder = -1;
foreach ($shopItems as $index => $item) {
    if ($index >= 23) {
        $regularOrder++;
    }

    $itemTypeId = (string) $item[0];
    $items[] = array(
        'displayOrder' => (string) $regularOrder,
        'popularItem' => isset($popularIds[$itemTypeId]) ? '1' : '0',
        'itemTypeId' => $itemTypeId,
        'name' => (string) $item[11],
        'description' => (string) $item[13],
        'configLocation' => (string) $item[2],
        'img' => (string) $item[2],
        'currency' => (string) $item[6],
        'price' => (string) $item[7],
        'defaultHexColour' => (string) $item[5],
        'minLevel' => (string) $item[19],
        'tycoonOnly' => (string) $item[20],
        'expPoints' => (string) $item[15],
        'paletteId' => (string) $item[4],
    );
}

echo json_encode(array('responseCode' => 1, 'items' => $items), JSON_UNESCAPED_SLASHES);
?>