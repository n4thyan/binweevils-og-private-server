<?php

//error_reporting(0);
include('../../../essential/backbone.php');

if(isset($_POST)) {
    $tag = $_POST['tag'];
    $storeName = $_POST['storeName'];
    $shopItems = getNestShopItems($tag,$storeName);
    $itemArr=array();
    $itemData = "";
    $itemcnt1 = 0;
    $itemcnt2 = -1;
    foreach($shopItems as $item){
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
        $displayOrder = $item[26];
        $tags = $item[12];
        $popularItems = getPopularNestShopItems($tag,$storeName);
        foreach($popularItems as $popItem){
            if($popItem[0] == $itemTypeId)
            {
                $itemData .= '{"displayOrder":"'.$displayOrder.'","itemTypeID":"'.$itemTypeId.'","name":"'.$name.'","description":"'.$description.'","file":"'.$configLocation.'","currency":"'.$currency.'","price":"'.$price.'","defaultHexColour":"'.$defaultHex.'","minLevel":"'.$minLevel.'","tycoonOnly":"'.$tycoonOnly.'","xp":"'.$expPoints.'","paletteID":"'.$palettedId.'","tags":"'.$tags.'"},';
                array_push($itemArr,$itemTypeId);
            }
        }
        if(!in_array($itemTypeId, $itemArr)){
            $itemData .= '{"displayOrder":"'.$displayOrder.'","itemTypeID":"'.$itemTypeId.'","name":"'.$name.'","description":"'.$description.'","file":"'.$configLocation.'","currency":"'.$currency.'","price":"'.$price.'","defaultHexColour":"'.$defaultHex.'","minLevel":"'.$minLevel.'","tycoonOnly":"'.$tycoonOnly.'","xp":"'.$expPoints.'","paletteID":"'.$palettedId.'","tags":"'.$tags.'"},';
            array_push($itemArr,$itemTypeId);
        }
    }
    $itemData = substr($itemData, 0, -1);
    echo '{"responseCode":1,"items":['.$itemData.']}';
}
else echo '{"responseCode":999}';

?>