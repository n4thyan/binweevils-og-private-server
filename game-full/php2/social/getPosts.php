<?php
error_reporting(E_ALL);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $period = max(1, intval($_POST['period']));
    $offset = ($period - 1) * 3;
    $weevilName = $_COOKIE['weevil_name'];
    $weevilPosts = getBuddyPosts($weevilName, $offset);
    $postCnt = 0;
    $posts = '';
    foreach($weevilPosts as $weevilPost){
        $postCnt++;
        $weevil = getAllWeevilStatsByName($weevilPost[1]);
        $posts .= '{"idx":"'.htmlspecialchars($weevilPost[1], ENT_QUOTES).'","userWeevilID":"'.htmlspecialchars($weevilPost[1], ENT_QUOTES).'","weevilDef":"'.htmlspecialchars($weevil['def'], ENT_QUOTES).'","message":"'.addslashes($weevilPost[2]).'","icon":"'.htmlspecialchars($weevilPost[3], ENT_QUOTES).'","ago":"'.time_ago($weevilPost[4]).'"},';
    }
    if($postCnt > 0){
        echo '{"responseCode":1,"period":"'.strval($offset+1) . ' - ' . strval($offset+3).'","posts":['.substr($posts, 0, -1).']}';
    }
    else{
        echo '{"responseCode":3}';
    }
}
else echo json_encode(["responseCode" => 999]);
?>
