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

function site_e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function site_active($name, $active) {
    return $name === $active ? ' is-active' : '';
}

function site_int($value) {
    return number_format((int)$value);
}

function site_ad_slot($placement, $format = 'leaderboard') {
    $safePlacement = site_e($placement);
    $safeFormat = preg_replace('/[^a-z0-9_-]/i', '', (string)$format);
    echo '<aside class="bw-ad-slot bw-ad-slot--' . $safeFormat . '" data-ad-slot="' . $safePlacement . '" aria-label="Reserved advert space"></aside>';
}
?>
