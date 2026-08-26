<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $GardenData = "";
    $PhotoData = "";
    $NestData = "";
    $BusinessData = "";

    $weevilstats = getAllWeevilStatsByName($_COOKIE['weevil_name']);
    $weevilId = $weevilstats['id'];
    $userItems = getWeevilItems($weevilId);
    $gardenItems = getWeevilGardenItems($_COOKIE['weevil_name'], 2);

    $gardenItemCache = array();
    $nestItemCache = array();
    $nestPhotoCache = array();
    $businessItemCache = array();

    foreach($userItems as $item){
        if($item[11] == 2 || $item[11] == 6 || $item[11] == 3 || $item[11] == 4) {
            $itemData = getNightclubItemDataById($item[2]);
            if($itemData['canBuy'] == 1) {
                $itemInfo = "";
                $iCount = itemCountById($item[2], $weevilId, $item[3]);
                $allItems = getWeevilItemsById($weevilId, $item[2], $item[3]);

                foreach($allItems as $itema) {
                    $itemInfo .= $itema[0] .",";
                }
                
                $itemInfo = sortData($itemInfo);
                $itemi = '{"id":['.$itemInfo.'],"configName":"'.$itemData['fileName'].'","clr":"'.$item[3].'","count":'.$iCount.'},';
                array_push($businessItemCache, $itemi);
            }
        }
        else{
            $itemData = getItemDataById($item[2]);
            if($itemData['canBuy'] == 1){
                $itemInfo = "";
                $iCount = itemCountById($item[2], $weevilId, $item[3]);
                $allItems = getWeevilItemsById($weevilId, $item[2], $item[3]);

                foreach($allItems as $itema){
                    $itemInfo .= $itema[0] .",";
                }
                $itemInfo = sortData($itemInfo);
                $itemi = '{"id":['.$itemInfo.'],"configName":"'.$itemData['configLocation'].'","clr":"'.$item[3].'","count":'.$iCount.'},';
                array_push($nestItemCache, $itemi);
            }
            else if($itemData['canBuy'] == 2) {
                $itemPhotoInfo = "";
                $allItems = getWeevilItemsById($weevilId, $item[2], $item[3]);
                foreach($allItems as $itema) {
                    $itemPhotoInfo .= $itema[0] . ',';
                    $itemPhotoInfo = sortData($itemPhotoInfo);
                    $photoi = '{"id":['.$itemPhotoInfo.'],"configName":"'.$item[6].'","clr":"'.$item[3].'","count":1},';
                    array_push($nestPhotoCache, $photoi);
                    $itemPhotoInfo = "";
                }
            }
        }
    }

    $itemDataUnique = array_unique($nestItemCache);
    foreach($itemDataUnique as $uniqueItem){
        $NestData.=$uniqueItem;
    }
    $NestData = sortData($NestData);

    $itemDataUnique = array_unique($nestPhotoCache);
    foreach($itemDataUnique as $uniqueItem){
        $PhotoData.=$uniqueItem;
    }
    $PhotoData = sortData($PhotoData);

    foreach($gardenItems as $gitem){
        $itemData = getGardenItemConfigById($gitem[2]);
        if($itemData['canBuy'] == 1){
            $garden_data = "";

            $gardenItemCount = gardenItemCountByType($gitem[2], $_COOKIE['weevil_name'], $gitem[4], 2);
            $allItems = getWeevilGardenItemsById($_COOKIE['weevil_name'], $gitem[2], $gitem[4], 2);
            foreach($allItems as $itema){
                $garden_data .= $itema[0] .",";
            }

            $garden_data = sortData($garden_data);
        
            $itemb = '{"id":['.$garden_data.'],"configName":"'.$itemData['configLocation'].'","clr":"-1","count":'.$gardenItemCount.'},';
            array_push($gardenItemCache, $itemb);
        }
    }

    $itemDataUnique = array_unique($gardenItemCache);
    foreach($itemDataUnique as $uniqueItem){
        $GardenData.=$uniqueItem;
    }
    $GardenData = sortData($GardenData);

    $itemDataUnique = array_unique($businessItemCache);
    foreach($itemDataUnique as $uniqueItem){
        $BusinessData.=$uniqueItem;
    }
    $BusinessData = sortData($BusinessData);

    echo '{"responseCode":1,"nestItems":['.$NestData.'],"gardenItems":['.$GardenData.'],"photoFrames":['.$PhotoData.'], "businessItems":[' . $BusinessData . ']}';
}
else echo 'responseCode=999';

function sortData($data){
    return substr($data, 0, -1);
}

?>