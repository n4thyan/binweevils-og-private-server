<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $nest = $_POST['nestID'];
    $weevilname = $_POST['userID'];
    $itemId = $_POST['itemID'];

    $weevilData = getAllWeevilStatsByName($nest);
    $ownsNest = checkNest($weevilname, $nest);
    
    if($ownsNest == true){
        $removeItem = removeItemFromNest($nest, $itemId);
        if($removeItem == false)
        echo 'responseCode=999';
        else{
            $itemTags = explode(",", getItemTags($userItem[2])['tags']);
            $tags = "";
            foreach($itemTags as $tag){
                if(!in_array($tag, $tagCache)){
                    $tags .= '"'.$tag.'",';
                    array_push($tagCache, $tag);
                }
            }
            $tags = sortData($tags);
            echo '{"responseCode":1,"items":[{"itemID":'.$itemId.',"grp":"1","tags":['.$tags.']}]}';
        }
       
    }
    else echo 'responseCode=999';

}
else echo 'responseCode=999';

function sortData($data){
    return substr($data, 0, -1);
}
?>