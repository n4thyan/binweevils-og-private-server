<?php
error_reporting(0);
include_once(dirname(__DIR__) . '/essential/backbone.php');

$siteConfig = include(dirname(__FILE__) . '/config.php');
$siteLoggedIn = false;
$siteUser = null;

if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
    $siteLoggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']) === true;

    if($siteLoggedIn) {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $q = $db->prepare('SELECT id, username, level, mulch, dosh, xp, xp1, xp2, prestige_count, def FROM users WHERE username = ? LIMIT 1');
        $q->bind_param('s', $_COOKIE['weevil_name']);
        $q->execute();
        $res = $q->get_result();

        if($row = $res->fetch_array(MYSQLI_ASSOC)) {
            $siteUser = $row;
        }
        else {
            $siteLoggedIn = false;
        }
    }
}

if(!isset($_SESSION['site_csrf']) || !is_string($_SESSION['site_csrf']) || strlen($_SESSION['site_csrf']) < 32) {
    try {
        $_SESSION['site_csrf'] = bin2hex(random_bytes(32));
    }
    catch(Exception $e) {
        $_SESSION['site_csrf'] = hash('sha256', session_id() . '|' . microtime(true) . '|' . mt_rand());
    }
}

function site_e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function site_active($name, $active) {
    return $name === $active ? ' is-active' : '';
}

function site_int($value) {
    return number_format((int)$value);
}

function site_csrf_token() {
    return isset($_SESSION['site_csrf']) ? (string)$_SESSION['site_csrf'] : '';
}

function site_csrf_valid($token) {
    $known = site_csrf_token();
    return $known !== '' && is_string($token) && hash_equals($known, $token);
}

function site_ad_slot($placement, $format = 'leaderboard') {
    $safePlacement = site_e($placement);
    $safeFormat = preg_replace('/[^a-z0-9_-]/i', '', (string)$format);
    echo '<aside class="bw-ad-slot bw-ad-slot--' . $safeFormat . '" data-ad-slot="' . $safePlacement . '" aria-label="Reserved advert space"></aside>';
}
?>
