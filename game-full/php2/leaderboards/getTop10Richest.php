<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $userData = getTop10RichestPlayers();
    
    if($userData != null) {
        $str = '';

        foreach($userData as $data)
        foreach($data as $username => $mulch)
        $str .= $username . '=' . $mulch . '&';

        echo substr($str, 0, -1);
    }
    else echo 'res=999';
}
else echo 'res=999';
?>