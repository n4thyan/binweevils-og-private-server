<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $player1 = $_POST['player1'];
    $player2 = $_POST['player2'];
    $gameId = $_POST['gameId'];

    if($player1 != $_COOKIE['weevil_name'] && $player2 != $_COOKIE['weevil_name'])
    echo 'user1_wins=0&user1_losses=0&user2_wins=0&user2_losses=0';
    else {
        $player1GameData = getUserMultiplayerGameData($player1, $gameId);
        $player2GameData = getUserMultiplayerGameData($player2, $gameId);

        if($player1GameData != false && $player2GameData != false)
        echo 'user1_wins=' . $player1GameData["wins"] . '&user1_losses=' . $player1GameData["losses"] . '&user2_wins=' . $player2GameData["wins"] . '&user2_losses=' . $player2GameData["losses"];
        else
        echo 'user1_wins=0&user1_losses=0&user2_wins=0&user2_losses=0';
    }
}
else
echo 'user1_wins=0&user1_losses=0&user2_wins=0&user2_losses=0';
?>