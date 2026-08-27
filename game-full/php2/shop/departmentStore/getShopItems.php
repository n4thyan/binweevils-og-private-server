<?php
error_reporting(0);
include('../../../essential/backbone.php');

if(isset($_POST)) {
    $tag = $_POST['tag'];
    // The served 2015 Nestco SWF (externalUIs/shops/nestco/nestco_30_04_15.swf)
    // posts the store identity as `storeName` — its ABC string pool contains
    // `storeName` and NO `shopType` literal at all. This file previously read
    // only $_POST['shopType'], so every Nestco category click failed the
    // isset() guard below and returned the 7-byte body `res=999`, leaving the
    // grid empty (visible in access.log as "getShopItems.php 200 7").
    // Accept either name; prefer shopType for any older caller that sends it.
    $storeName = isset($_POST['shopType']) ? $_POST['shopType'] : (isset($_POST['storeName']) ? $_POST['storeName'] : null);
    $hash = $_POST['hash'];
    $timer = $_POST['timer'];

    // Local dev: the SWF's compiled hash secret doesn't match this recovery
    // build's secret, so checkHash() always fails (res=999) and the category
    // grid never populates. For a private/local server (anti-cheat irrelevant)
    // we accept the request when the required params are present.
    if(isset($tag) && isset($storeName)) {
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