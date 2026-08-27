<?php
// php2/shop/bundles/getBundles.php
//
// WHY THIS PATH: the served 2015 Nestco SWF
// (cdn.binw.net/externalUIs/shops/nestco/nestco_30_04_15.swf) carries the
// literal endpoint string "shop/bundles/getBundles" in its ABC constant pool.
// It is requested as GET /php2/shop/bundles/getBundles.php?rndVar= when the
// Bundles tab is opened. That file did not exist (only getShowroom.php lived in
// this directory), producing the observed 404 and the "00 / 00" empty panel.
// A getBundles.php DID exist in the source checkouts but under the WRONG
// directory (php2/shop/departmentStore/), so the client could never reach it.
//
// RESPONSE CONTRACT: recovered from the SWF's own classes, not guessed.
// com.binweevils.externalUIs.shops.nestco:BundleData is built by
// getBundleDataObj() and exposes the same scalar fields as NestItemData
// (id/order/popular/name/description/configLocation/currency/price/
// defaultHexColour/minLevel/tycoon/xp/paletteId/shopName) PLUS a nested
// `items` collection populated via addItem(). The response root key is
// `bundles` (sibling of getFeatured's `featured` and getShopItems' `items`),
// and the SWF's JSON decoder (com.adobe.serialization.json) requires a
// well-formed JSON object with a numeric responseCode.
//
// CONTENT STATE (audited 2026-08-27, proven from the live `bwps` DB, NOT
// assumed): this database contains NO authored bundle content.
//   * no bundle table and no bundle column exist anywhere in `bwps`
//     (information_schema sweep found only task-completed.bundleNameRewarded,
//     an unrelated rewards column);
//   * `collectionID` is 0/NULL for every buyable nestco row, so no item
//     grouping encodes a bundle;
//   * `internalCategory` is NULL on all 1182 buyable mulch rows.
// Therefore Bundles legitimately has zero recoverable stock. This endpoint
// returns a VALID, WELL-FORMED, EMPTY bundle list so the tab renders its real
// "0 of 0" state instead of erroring on a 404. It is deliberately NOT a
// content-inventing shim: if bundle data is ever authored (a bundles table, or
// a collectionID grouping), populate $bundles below and the SWF will render it
// with no client change required.
error_reporting(0);
include('../../../essential/backbone.php');

// The SWF issues this as a GET (?rndVar=), unlike the POST item feeds, so
// accept either method and read the store identity from whichever name is
// present. The 2015 Nestco SWF uses `storeName`; older callers use `shopType`.
$src = !empty($_POST) ? $_POST : $_GET;
$storeName = isset($src['shopType'])
    ? $src['shopType']
    : (isset($src['storeName']) ? $src['storeName'] : 'nestco');

// Keep the Dosh/Mulch store split intact and explicit, matching
// essential/internal.php::getNestShopItems() and getFeatured.php, so a future
// populated bundle list can never leak the wrong currency into the wrong store.
$storeMap = array(
    'binmart' => array('shopType' => 'nestco', 'currency' => 'dosh'),
    'nestco'  => array('shopType' => 'nestco', 'currency' => 'mulch'),
);
$map = isset($storeMap[$storeName]) ? $storeMap[$storeName] : $storeMap['nestco'];

// No bundle source exists in this schema (see CONTENT STATE above). Left as an
// explicit empty set rather than a query against a table that does not exist.
$bundles = array();

$parts = array();
foreach ($bundles as $b) {
    $items = array();
    foreach ($b['items'] as $it) {
        $items[] = '{"itemTypeId":"'.$it['itemTypeId'].'"'
                 . ',"name":"'.addslashes($it['name']).'"'
                 . ',"configLocation":"'.$it['configLocation'].'"'
                 . ',"img":"'.$it['configLocation'].'"'
                 . ',"currency":"'.$it['currency'].'"'
                 . ',"price":"'.$it['price'].'"'
                 . ',"defaultHexColour":"'.$it['defaultHexColour'].'"'
                 . ',"minLevel":"'.$it['minLevel'].'"'
                 . ',"tycoonOnly":"'.$it['tycoonOnly'].'"'
                 . ',"expPoints":"'.$it['expPoints'].'"'
                 . ',"paletteId":"'.$it['paletteId'].'"}';
    }
    $parts[] = '{"bundleId":"'.$b['bundleId'].'"'
             . ',"displayOrder":"'.$b['displayOrder'].'"'
             . ',"name":"'.addslashes($b['name']).'"'
             . ',"description":"'.addslashes($b['description']).'"'
             . ',"configLocation":"'.$b['configLocation'].'"'
             . ',"img":"'.$b['configLocation'].'"'
             . ',"currency":"'.$map['currency'].'"'
             . ',"price":"'.$b['price'].'"'
             . ',"minLevel":"'.$b['minLevel'].'"'
             . ',"tycoonOnly":"'.$b['tycoonOnly'].'"'
             . ',"expPoints":"'.$b['expPoints'].'"'
             . ',"items":['.implode(',', $items).']}';
}

echo '{"responseCode":1,"storeName":"'.$storeName.'","bundles":['.implode(',', $parts).']}';
?>
