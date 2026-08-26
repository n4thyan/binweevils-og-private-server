<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
    $username = $_COOKIE['weevil_name'];
    $key = $_COOKIE['sessionId'];

    if(confirmSessionKey($username, $key)) {
        session_destroy();
        // NOTE: do NOT blank sessionKey/loginKey here. The Flash client calls
        // logoutClient.php as a routine bin-connection cleanup; wiping the keys
        // here invalidates the web session and the TCP loginKey, dropping the
        // player after server-select. Keys are cleared explicitly on real logout
        // via login.php's logout() path instead.
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $q = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1;");
        $q->bind_param('s', $username);
        $q->execute();

        $res = $q->get_result();

        if($res = $res->fetch_array()) {
            // intentionally do not UPDATE sessionKey/loginKey to ''
        }
        else header("Location: ../../game.php");
    }
    else header("Location: ../../game.php");
}
else header("Location: ../../game.php");

?>