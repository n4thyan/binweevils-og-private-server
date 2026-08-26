<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $tagsForItem = "";
    $itemData = "";
    $PhotoData = "";
    $itemsCache = array();
    $itemsInfoCache = array();
    $nestPhotoCache = array();
    $weevilId = $_POST['idx'];
    $userItems = getWeevilItems($weevilId);

    foreach($userItems as $userItem){
        $itemInfo = "";
        $tags = "";
        $tagCache = array();
        $itemColor = $userItem[3];
        $group = 0;
        $iCount = itemCountById($userItem[2], $weevilId, $itemColor);
        $itemDatas = getItemDataById($userItem[2]);

        if($userItem[11] == 1){
            $itemTags = explode(",", getItemTags($userItem[2])['tags']);
            foreach($itemTags as $tag){
                if(!in_array($tag, $tagCache)){
                    $tags .= '"'.$tag.'",';
                    array_push($tagCache, $tag);
                }
            }
            $tags = sortData($tags);
        }
        
        if($itemDatas["canBuy"] == 2) {
            $allItems = getWeevilItemsById($weevilId, $userItem[2], $userItem[3]);
            $itemPhotoInfo = "";
            foreach($allItems as $itema){
                $itemPhotoInfo .= $itema[0] . ',';
                $itemPhotoInfo = sortData($itemPhotoInfo);
                $photoi = '{"id":['.$itemPhotoInfo.'],"cat":'.$userItem[11].',"configName":"'.$userItem[6].'","clr":"'.$userItem[3].'","pc":1,"dt":0,"grp":0,"tags":['.$tags.'],"count":1},';
                array_push($nestPhotoCache, $photoi);
                $itemPhotoInfo = "";
            }
        }else{
            $allItems = getWeevilItemsById($weevilId, $userItem[2], $itemColor);
            foreach($allItems as $itema){
                $itemInfo .= $itema[0] .",";
                array_push($itemsInfoCache, $itema[0]);
            }

            $itemInfo = sortData($itemInfo);
            $itemi = '{"id":['.$itemInfo.'],"cat":'.$userItem[11].',"configName":"'.$userItem[6].'","clr":"'.$userItem[3].'","pc":1,"dt":0,"grp":'.$group.',"tags":['.$tags.'],"count":'.$iCount.'},';
            array_push($itemsCache, $itemi);
            $itemInfo = "";
        }
    }

    $itemDataUnique = array_unique($itemsCache);
    foreach($itemDataUnique as $uniqueItem){
        $itemData.=$uniqueItem;
    }
    
    $itemDataUnique = array_unique($nestPhotoCache);
    foreach($itemDataUnique as $uniqueItem){
        $PhotoData.=$uniqueItem;
    }
    $PhotoData = sortData($PhotoData);

    if($PhotoData == "" || $PhotoData == null) {
        $PhotoData = "";
        $itemData = sortData($itemData);
    }

    echo '{"responseCode":1,"items":['.$itemData.$PhotoData.']}';
}

function sortData($data){
    return substr($data, 0, -1);
}

?>