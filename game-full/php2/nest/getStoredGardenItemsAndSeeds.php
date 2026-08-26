<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $weevilData = getAllWeevilStatsByName($_COOKIE['weevil_name']);
    $weevilName = $weevilData['username'];

    $seedInfo = "";
    $seedData = "";
    $seedInfoCache = array();
    $seedCache = array();

    $itemInfo = "";
    $itemData = "";
    $itemInfoCache = array();
    $itemCache = array();


    $seeds = getWeevilGardenItems($weevilName, 1);
    $items = getWeevilGardenItems($weevilName, 2);

    foreach($seeds as $seed){
        $seedConfig = getSeedConfigById($seed[2]);

        if($seedConfig != false) {
            $seedInfo = "";
            $sCount = gardenItemCountByType($seed[2], $weevilName, "-1", 1);

            $allSeeds = getWeevilGardenItemsById($weevilName, $seed[2], $seed[4], 1);
            //var_dump($allSeeds);
            foreach($allSeeds as $seeda) {
                $seedInfo .= $seeda[0] .",";
                array_push($seedInfoCache, $seeda[0]);
            }

            $seedInfo = sortData($seedInfo);
            
            $seedb = '{"id": ['.$seedInfo.'],"name": "'.$seedConfig['name'].'","cat": 1,"fName": "'.$seedConfig['fileName'].'","growTime": '.$seedConfig['growTime'].',"cycleTime": '.$seedConfig['cycleTime'].',"r": '.$seedConfig['radius'].',"mulch": '.$seedConfig['mulchYield'].',"xp": '.$seedConfig['xpYield'].',"count": '.$sCount.'},';
            array_push($seedCache, $seedb);
        }
        else {
            //deleteGardenItemFromInventory($weevilName, $seed[0]);
            //deleteGardenItemFromInventory($weevilName, $seed[0]);
        }
    }

    $seedDataUnique = array_unique($seedCache);
    foreach($seedDataUnique as $uniqueSeed){
        $seedData.=$uniqueSeed;
    }
    $seedData = sortData($seedData);

    foreach($items as $item){
        $itemConfig = getGardenItemConfigById($item[2]);

        $itemInfo = "";
        $iCount = gardenItemCountByType($item[2], $weevilName, "-1", 2);

        $allItems = getWeevilGardenItemsById($weevilName, $item[2], $item[4], 2);
        //var_dump($allSeeds);
        foreach($allItems as $itema){
            $itemInfo .= $itema[0] .",";
            array_push($itemInfoCache, $itema[0]);
        }

        $itemInfo = sortData($itemInfo);
        
        $itemb = '{"id": ['.$itemInfo.'],"fName": "'.$itemConfig['configLocation'].'","clr": "'.$item[4].'","r": '.$itemConfig['boundRadius'].',"pc": 1,"dt": 0,"grp": 1,"count": '.$iCount.'},';
        array_push($itemCache, $itemb);
    }
    $itemDataUnique = array_unique($itemCache);
    foreach($itemDataUnique as $uniqueItem){
        $itemData.=$uniqueItem;
    }
    $itemData = sortData($itemData);
    echo '{"responseCode":1,"items":['.$itemData.'], "seeds":['.$seedData.']}';
}

function sortData($data){
    return substr($data, 0, -1);
}

?>