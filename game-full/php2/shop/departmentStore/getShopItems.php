<?php
error_reporting(0);
include('../../../essential/backbone.php');

if(isset($_POST)) {
    $tag = $_POST['tag'];
    $storeName = $_POST['shopType'];
    $hash = $_POST['hash'];
    $timer = $_POST['timer'];

    // TEMP DEBUG (Bin Pet wiring): capture the real tag/shopType the client sends.
    if($storeName === 'binPetShop') {
        @file_put_contents(__DIR__ . '/binpet_shop_debug.log',
            date('Y-m-d H:i:s') . " tag=" . var_export($tag, true) . " shopType=" . var_export($storeName, true) . "\n",
            FILE_APPEND);
    }

    if(checkHash(["hash" => $hash, "tag" => $tag, "shopType" => $storeName, "timer" => $timer])) {
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
                    $itemData .= '{"displayOrder":"'.$itemcnt2.'","popularItem":"1","itemTypeId":"'.$itemTypeId.'","name":"'.$name.'","description":"'.$description.'","configLocation":"'.$configLocation.'","currency":"'.$currency.'","price":"'.$price.'","defaultHexColour":"'.$defaultHex.'","minLevel":"'.$minLevel.'","tycoonOnly":"'.$tycoonOnly.'","expPoints":"'.$expPoints.'","paletteId":"'.$palettedId.'"},';
                    array_push($itemArr,$itemTypeId);
                }
            }
            if(!in_array($itemTypeId, $itemArr)) {
                $itemData .= '{"displayOrder":"'.$itemcnt2.'","popularItem":"0","itemTypeId":"'.$itemTypeId.'","name":"'.$name.'","description":"'.$description.'","configLocation":"'.$configLocation.'","currency":"'.$currency.'","price":"'.$price.'","defaultHexColour":"'.$defaultHex.'","minLevel":"'.$minLevel.'","tycoonOnly":"'.$tycoonOnly.'","expPoints":"'.$expPoints.'","paletteId":"'.$palettedId.'"},';
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