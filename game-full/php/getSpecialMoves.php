<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $userName = $_COOKIE['weevil_name'];
    $weevilData = getAllWeevilStatsByName($userName);

        $specialMoves = getSpecialMoves($userName, $weevilData["id"]);
        $sMoves = "";

        foreach($specialMoves as $moves) {
            $sMoves .= $moves["moves"] . ';';
        }

        echo 'responseCode=1&result=' . $sMoves . '58;59;49;64';
    }
    else
    echo 'responseCode=999';
?>