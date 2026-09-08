<?php
error_reporting(0);
header('Content-Type: application/x-www-form-urlencoded; charset=UTF-8');
require_once(__DIR__ . '/../../essential/backbone.php');
require_once(__DIR__ . '/../../essential/email-providers.php');

function activationResponse($code) {
    echo 'responseCode=' . intval($code);
    exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_COOKIE['weevil_name'], $_COOKIE['sessionId'])) activationResponse(4);
if(!confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId'])) activationResponse(4);

$email = normalizeAllowedActivationEmail($_POST['email'] ?? '');
$idx = intval($_POST['idx'] ?? 0);
if($email === null || $idx < 1) activationResponse(4);

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$db->begin_transaction();
try {
    $q = $db->prepare('SELECT id, username, activated FROM users WHERE id = ? AND username = ? FOR UPDATE');
    $q->bind_param('is', $idx, $_COOKIE['weevil_name']);
    $q->execute();
    $user = $q->get_result()->fetch_assoc();
    if(!$user) throw new RuntimeException('owner mismatch');

    // activated is the durable one-time ledger. Replays return success for the
    // Flash overlay but never change email or grant a second reward.
    if(intval($user['activated']) === 1) {
        $db->commit();
        activationResponse(1);
    }

    $q = $db->prepare('UPDATE users SET email = ?, activated = 1, dosh = dosh + 25, mulch = mulch + 3200 WHERE id = ? AND activated = 0');
    $q->bind_param('si', $email, $idx);
    $q->execute();
    if($q->affected_rows !== 1) throw new RuntimeException('activation race');

    if(!addExperienceByNameTx($user['username'], 100, $db)) throw new RuntimeException('xp award failed');
    $db->commit();
    activationResponse(1);
} catch(Throwable $e) {
    $db->rollback();
    activationResponse(4);
}
