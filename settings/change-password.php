<?php
include('../site/bootstrap.php');
header('Content-Type: application/json');

function password_response($ok, $message, $code = 200) {
    http_response_code($code);
    echo json_encode(['ok' => (bool)$ok, 'message' => (string)$message]);
    exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    password_response(false, 'Invalid request.', 405);
}

if(!$siteLoggedIn || !is_array($siteUser)) {
    password_response(false, 'You need to log in again.', 401);
}

if(!rateLimit('site-password-change', 5, 900)) {
    password_response(false, 'Too many password-change attempts. Try again later.', 429);
}

$csrf = isset($_POST['csrf']) ? (string)$_POST['csrf'] : '';
$current = isset($_POST['current_password']) ? (string)$_POST['current_password'] : '';
$new = isset($_POST['new_password']) ? (string)$_POST['new_password'] : '';
$confirm = isset($_POST['confirm_password']) ? (string)$_POST['confirm_password'] : '';

if(!site_csrf_valid($csrf)) {
    password_response(false, 'Your session token is invalid. Refresh the page and try again.', 403);
}

if($current === '' || $new === '' || $confirm === '') {
    password_response(false, 'Fill in all password fields.', 400);
}

if($new !== $confirm) {
    password_response(false, 'The new passwords do not match.', 400);
}

if(strlen($new) < 8 || strlen($new) > 72) {
    password_response(false, 'Use a password between 8 and 72 characters.', 400);
}

$username = (string)$siteUser['username'];
if(!checkPassword($username, $current)) {
    password_response(false, 'Your current password is incorrect.', 403);
}

if(hash_equals($current, $new)) {
    password_response(false, 'Choose a different password from your current one.', 400);
}

$newHash = password_hash($new, PASSWORD_DEFAULT);
if($newHash === false) {
    password_response(false, 'The password could not be updated.', 500);
}

$newSessionKey = generateSessionKey();
$newLoginKey = generateLogKey();

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$q = $db->prepare('UPDATE users SET password = ?, sessionKey = ?, loginKey = ? WHERE id = ? AND username = ? LIMIT 1');
$userId = (int)$siteUser['id'];
$q->bind_param('sssis', $newHash, $newSessionKey, $newLoginKey, $userId, $username);
$q->execute();

if($q->affected_rows !== 1) {
    password_response(false, 'The password could not be updated.', 500);
}

setcookie('sessionId', $newSessionKey, time() + 86400, '/', '', false, true);
setcookie('weevil_name', $username, time() + 86400, '/', '', false, true);

try {
    $_SESSION['site_csrf'] = bin2hex(random_bytes(32));
}
catch(Exception $e) {
    $_SESSION['site_csrf'] = hash('sha256', session_id() . '|' . microtime(true) . '|' . mt_rand());
}

password_response(true, 'Password changed. Your login and game keys have been rotated.');
?>
