<?php
error_reporting(0);
include('../essential/backbone.php');

function verifyUser($username, $password) {
    $aes = new AES256();

    if(!empty($username) && !empty($password)) {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$q = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1;");
		$q->bind_param('s', $username);
		$q->execute();
		
        $res = $q->get_result();
        
        if($res = $res->fetch_array()) {
            if(password_verify($password, $res['password'])) {
                if($res["active"] == 1) {
                    $bannedUntil = json_decode(time_until(time(), $res['bannedUntil']));

                    if($bannedUntil->days <= 0 && $bannedUntil->hours <= 0 && $bannedUntil->minutes <= 0 && $bannedUntil->seconds <= 0) {
                        // logged in successfully, set everything
                        $sessKey = generateSessionKey();
                        $logKey = generateLogKey();
                        $timestamp = time();
                        // Privacy-preserving IP: store HMAC hash, never the raw address.
                        $hashedIP = hash_hmac('sha256', GetIP(), IP_HASH_SECRET);

                        $u = $db->prepare("UPDATE users SET sessionKey = ?, loginKey = ?, lastLogin = ?, loginIP = ? WHERE username = ?");
                        $u->bind_param('ssiss', $sessKey, $logKey, $timestamp, $hashedIP, $username);
                        $u->execute();

                        setcookie("sessionId", $sessKey, time() + 86400, '/');
                        setcookie("weevil_name", $res['username'], time() + 86400, '/');

                        // Record one genuine successful login activity.
                        $activityIns = $db->prepare(
                            "INSERT INTO achievement_activity (userID, activityType) VALUES (?, 'login')"
                        );
                        $activityIns->bind_param('i', $res['id']);
                        $activityIns->execute();
                        $activityIns->close();

                        header('Location: ../game.php');
                    }
                    else header("Location: http://localhost/?err=" . urlencode($aes->encrypt("This account has been temporarily banned for:<br>" . $bannedUntil->days . " days, " . $bannedUntil->hours . " hours, " . $bannedUntil->minutes . " minutes, " . $bannedUntil->seconds . " seconds.", AES_PASSPHRASE)));
                }
                else header("Location: http://localhost/?err=" . urlencode($aes->encrypt("This account has been permanently banned.", AES_PASSPHRASE)));
            }
            else header("Location: http://localhost/?err=" . urlencode($aes->encrypt("Username or password is incorrect!", AES_PASSPHRASE)));
        }
        else header("Location: http://localhost/?err=" . urlencode($aes->encrypt("Username or password is incorrect!", AES_PASSPHRASE)));
    }
    else header("Location: http://localhost/?err=" . urlencode($aes->encrypt("Username or password is incorrect!", AES_PASSPHRASE)));
}

function logout() {
    if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
        $weevil_name = $_COOKIE['weevil_name'];
        //session_destroy();
        setcookie("sessionId", $_COOKIE['sessionId'], time() - 86400, '/'); // Eh ... we'll leave it for now ...
        setcookie("weevil_name", $_COOKIE['weevil_name'], time() - 86400, '/');
        session_destroy();

        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$q = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1;");
		$q->bind_param('s', $weevil_name);
		$q->execute();
		
        $res = $q->get_result();
        
        if($res = $res->fetch_array()) {
            $u = $db->prepare("UPDATE users SET sessionKey = '', loginKey = '' WHERE id = ?");
            $u->bind_param('i', $res['id']);
            $u->execute();
        }
    }

    header("Location: ../");
}

if(isset($_POST['userID']) && isset($_POST['password'])) {
    // Checkpoint A (A8): throttle the web login endpoint against brute-force.
    // 10 attempts per IP per 5 minutes. Genuine users are not affected in normal use.
    if(!rateLimit('web-login', 10, 300)) {
        header("Location: ../?err=" . urlencode("Too many login attempts. Please wait a few minutes."));
        return;
    }

    $username = $_POST['userID'];
    $password = $_POST['password'];

    verifyUser($username, $password);
}
else logout();
?>