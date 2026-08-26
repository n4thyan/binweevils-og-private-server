<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
    $loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

    if($loggedIn == true) {
        $probabilityMethodArray = [1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2];
        echo generateGardenShopXML($probabilityMethodArray[rand(0, 19)]);
    }
    else
    echo 'res=999';

    /*$gardenItemData = GetBuyableGardenItems();
    $gardenItemStock = "";
    $seedData = GetBuyableSeeds();
    $seedStock = "";

    if($seedData == false){
        echo 'responseCode=999&message=Nice try <3';
    }
    else if($gardenItemData == false){
        echo 'responseCode=999&message=Nice try <3';
    }
    else{
        foreach($gardenItemData as $gardenItem){
            if($gardenItem[8] < 20){
                if(chance($gardenItem[8]) == true){
                    $gardenItemStock .= '<item id="'.$gardenItem[0].'" tyc="'.$gardenItem[15].'" level="'.$gardenItem[14].'" xp="'.$gardenItem[11].'" fileName="'.$gardenItem[2].'" clr="'.$gardenItem[4].'" name="'.$gardenItem[9].'" descr="'.$gardenItem[10].'" dt="0" prob="'.$gardenItem[8].'" price="'.$gardenItem[6].'" />';
                }
            }
            else{
                $gardenItemStock .= '<item id="'.$gardenItem[0].'" tyc="'.$gardenItem[15].'" level="'.$gardenItem[14].'" xp="'.$gardenItem[11].'" fileName="'.$gardenItem[2].'" clr="'.$gardenItem[4].'" name="'.$gardenItem[9].'" descr="'.$gardenItem[10].'" dt="0" prob="'.$gardenItem[8].'" price="'.$gardenItem[6].'" />';
            }
        }
        foreach($seedData as $seed){
            if($seed[11] < 20){
                if(chance($seed[11]) == true){
                    $seedStock .= '<seed id="'.$seed[0].'" cat="'.$seed[1].'" tyc="'.$seed[2].'" level="'.$seed[3].'" fileName="'.$seed[4].'" name="'.$seed[5].'" price="'.$seed[6].'" mulchYield="'.$seed[7].'" xpYield="'.$seed[8].'" growTime="'.$seed[9].'" cycleTime="'.$seed[10].'" prob="'.$seed[11].'"/>';
                }
            }
            else{
                $seedStock .= '<seed id="'.$seed[0].'" cat="'.$seed[1].'" tyc="'.$seed[2].'" level="'.$seed[3].'" fileName="'.$seed[4].'" name="'.$seed[5].'" price="'.$seed[6].'" mulchYield="'.$seed[7].'" xpYield="'.$seed[8].'" growTime="'.$seed[9].'" cycleTime="'.$seed[10].'" prob="'.$seed[11].'"/>';
            }
        }
        echo '<?xml version="1.0" encoding="utf-8" ?><stock>'.$gardenItemStock.$seedStock.'</stock>';
    }*/
}
else echo 'res=999';

function chance($percent) {
    return mt_rand(0, $percent) == $percent;
}
?>