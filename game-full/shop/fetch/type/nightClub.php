<?php
error_reporting(1);
include('../../../essential/backbone.php');

if(isset($_POST)) {
    $stock = '';
    $itemData = GetAllNightClubItems('nightClub');
    foreach($itemData as $item){
        $stock .= '<item id="'.$item[0].'" tyc="1" level="'.$item[5].'" xp="'.$item[4].'" fileName="'.$item[1].'" name="'.$item[2].'" descr="'.$item[3].'" dt="0" prob="'.$item[12].'" price="'.$item[6].'" clr="'.$item[11].'"/>';
    }
    echo '<?xml version="1.0"?><stock type="tycoon">'.$stock.'</stock>';
}
else echo 'responseCode=999';
?>