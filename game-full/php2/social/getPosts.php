<?php
error_reporting(E_ALL);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $posts = '';
    $offset = $_POST['period']*3;
    $weevilName = $_COOKIE['weevil_name'];
    $weevilPosts = getBuddyPosts($weevilName, $offset);
    $postCnt = 0;
    foreach($weevilPosts as $weevilPost){
        $postCnt++;
        $weevil = getAllWeevilStatsByName($weevilPost[1]);
        $posts .= '{"idx":"224832231","userWeevilID":"'.$weevilPost[1].'","weevilDef":"'.$weevil['def'].'","message":"'.addslashes($weevilPost[2]).'","icon":"'.$weevilPost[3].'","ago":"'.time_ago($weevilPost[4]).'"},';
    }
    echo '{"responseCode":1,"period":"'.strval($offset+1) . ' - ' . strval($offset+3).'","posts":['.substr($posts, 0, -1).']}';
    /*if($postCnt > 0){
        echo '{"responseCode":1,"period":"'.strval($offset+1) . ' - ' . strval($offset+3).'","posts":['.substr($posts, 0, -1).']}';
    }
    else{
        echo '{"responseCode":3}';
    }*/
}
else echo json_encode(["responseCode" => 999]);
?>