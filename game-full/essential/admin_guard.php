<?php
/**
 * Checkpoint D — central admin-panel guard.
 *
 * Locks the 848fjogfndsl moderator panel behind a validated modToken.
 * A request reaches panel functionality ONLY if BOTH:
 *   - the session holds a logged-in admin (username + adminToken), AND
 *   - that token matches the modToken stored on the user row, AND
 *   - the user is a moderator (isModerator = 1).
 * Otherwise the request is refused (403) and redirected to the login page.
 *
 * Game endpoints (game-full/php2/*) are intentionally NOT gated — per the
 * deployment plan those stay publicly readable so remote clients can connect.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function enforceAdminAccess() {
    if (!isset($_SESSION['admin']) || !isset($_SESSION['adminToken'])) {
        http_response_code(403);
        header('Location: /848fjogfndsl/');
        exit;
    }

    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($db->connect_error) {
        http_response_code(403);
        header('Location: /848fjogfndsl/');
        exit;
    }

    $q = $db->prepare("SELECT modToken, isModerator FROM users WHERE username = ? LIMIT 1");
    $q->bind_param('s', $_SESSION['admin']);
    $q->execute();
    $q->bind_result($storedToken, $isMod);
    if (!$q->fetch() || $isMod != 1 || empty($storedToken) || !hash_equals((string)$storedToken, (string)$_SESSION['adminToken'])) {
        $q->close();
        $db->close();
        // Token invalid/mismatched — destroy the session so a stale/forged session cannot linger.
        session_unset();
        session_destroy();
        http_response_code(403);
        header('Location: /848fjogfndsl/');
        exit;
    }
    $q->close();
    $db->close();
}
