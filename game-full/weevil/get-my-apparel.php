<?php

include('../essential/backbone.php');

if(isset($_POST)) {
    $hatData = "";
    $hats = GetOwnedHats();
    foreach($hats as $hat){
        $hatData .= '<item id="'.$hat[1].'" cat="1" rgb="'.$hat[3].'"/>';
    }
    echo '<apparelOwned><item id="1" cat="1" rgb="-140,-140,-140"/>'.$hatData.'</apparelOwned>';
}
else echo '';
?>