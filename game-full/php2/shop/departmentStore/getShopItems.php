<?php
error_reporting(0);
include('../../../essential/backbone.php');

if(isset($_POST)) {
    $tag = $_POST['tag'];
    // The served 2015 Nestco SWF (externalUIs/shops/nestco/nestco_30_04_15.swf)
    // posts the store identity as `storeName` — its ABC string pool contains
    // `storeName` and NO `shopType` literal at all, and it carries the SAME
    // secret `P07aJK8soogA815CxjkTcA==` that calcHash() concatenates. The OG
    // baseline read $_POST['shopType'] (null from this SWF) and signed under
    // the key `shopType`; the SWF signs `storeName`. That key mismatch — not a
    // secret mismatch — is what made checkHash() return res=999 and emptied
    // the grid. Accept either posted name (prefer shopType for any older
    // caller) but ALWAYS hash under the key the SWF actually uses (`storeName`),
    // exactly like the working buyDoshShopItem.php flow does.
    $storeName = isset($_POST['shopType']) ? $_POST['shopType'] : (isset($_POST['storeName']) ? $_POST['storeName'] : null);
    $hash = $_POST['hash'];
    $timer = $_POST['timer'];

    // Restore legitimate legacy hash validation (the real client contract).
    // Param set must mirror the SWF's makeHash(): timer present => ksort by
    // key => values concatenated in key order (storeName, tag, timer), wrapped
    // with the shared secret inside calcHash(). A request is accepted only
    // when its hash verifies; missing/empty hash or a tampered signed field
    // (tag/storeName/timer) is rejected with res=999.
    if(isset($tag) && isset($storeName) && isset($hash) && checkHash(["hash" => $hash, "storeName" => $storeName, "tag" => $tag, "timer" => $timer])) {
        $shopItems = getNestShopItems($tag, $storeName);
        $itemArr= array();
        $itemData = "";
        $itemcnt1 = 0;
        $itemcnt2 = -1;

        foreach($shopItems as $item) {
            if($itemcnt1 < 23)
            $itemcnt1++;
            else
            $itemcnt2++;
            $itemTypeId = $item[0];
            $name = $item[11];
            $description = $item[13];
            $configLocation = $item[2];
            $currency = $item[6];
            $price = $item[7];
            $minLevel= $item[19];
            $tycoonOnly = $item[20]; 
            $expPoints = $item[15];
            $palettedId = $item[4];
            $defaultHex = $item[5];
            $popularItems = getPopularNestShopItems($tag,$storeName);
            foreach($popularItems as $popItem) {
                if($popItem[0] == $itemTypeId)
                {
                    $itemData .= '{"displayOrder":"'.$itemcnt2.'","popularItem":"1","itemTypeId":"'.$itemTypeId.'","name":"'.$name.'","description":"'.$description.'","configLocation":"'.$configLocation.'","img":"'.$configLocation.'","currency":"'.$currency.'","price":"'.$price.'","defaultHexColour":"'.$defaultHex.'","minLevel":"'.$minLevel.'","tycoonOnly":"'.$tycoonOnly.'","expPoints":"'.$expPoints.'","paletteId":"'.$palettedId.'"},';
                    array_push($itemArr,$itemTypeId);
                }
            }
            if(!in_array($itemTypeId, $itemArr)) {
                $itemData .= '{"displayOrder":"'.$itemcnt2.'","popularItem":"0","itemTypeId":"'.$itemTypeId.'","name":"'.$name.'","description":"'.$description.'","configLocation":"'.$configLocation.'","img":"'.$configLocation.'","currency":"'.$currency.'","price":"'.$price.'","defaultHexColour":"'.$defaultHex.'","minLevel":"'.$minLevel.'","tycoonOnly":"'.$tycoonOnly.'","expPoints":"'.$expPoints.'","paletteId":"'.$palettedId.'"},';
                array_push($itemArr,$itemTypeId);
            }
        }
        
        $itemData = substr($itemData, 0, -1);
        echo '{"responseCode":1,"items":['.$itemData.']}';
    }
    else
    echo 'res=999';
}
else echo 'res=999';
?>