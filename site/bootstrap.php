<?php
error_reporting(0);
include_once(dirname(__DIR__) . '/essential/backbone.php');
include_once(dirname(__FILE__) . '/cosmetics.php');

$siteConfig = include(dirname(__FILE__) . '/config.php');
$siteLoggedIn = false;
$siteUser = null;
$siteCosmetics = [
    'ready' => false,
    'unlocked' => [],
    'equipped' => [],
];

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
            $siteCosmetics = site_cosmetics_state($db, (int)$row['id']);
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

function site_ad_creatives($placement) {
    global $siteConfig;
    $all = isset($siteConfig['ad_creatives']) && is_array($siteConfig['ad_creatives']) ? $siteConfig['ad_creatives'] : [];
    if(empty($all[$placement]) || !is_array($all[$placement])) return [];

    $valid = [];
    foreach($all[$placement] as $creative) {
        if(!is_array($creative)) continue;
        $type = isset($creative['type']) ? strtolower((string)$creative['type']) : '';
        $src = isset($creative['src']) ? trim((string)$creative['src']) : '';
        if(($type !== 'video' && $type !== 'image') || $src === '') continue;
        $valid[] = $creative;
    }
    return $valid;
}

function site_has_ads($placement) {
    return count(site_ad_creatives($placement)) > 0;
}

function site_ad_slot($placement, $format = 'leaderboard') {
    $creatives = site_ad_creatives($placement);
    if(empty($creatives)) return;

    $safePlacement = site_e($placement);
    $safeFormat = preg_replace('/[^a-z0-9_-]/i', '', (string)$format);
    echo '<aside class="bw-ad-slot bw-ad-slot--' . $safeFormat . '" data-ad-slot="' . $safePlacement . '" data-ad-rotation aria-label="Advertisement">';

    foreach($creatives as $index => $creative) {
        $type = strtolower((string)$creative['type']);
        $src = site_e($creative['src']);
        $href = !empty($creative['href']) ? site_e($creative['href']) : '';
        $label = !empty($creative['label']) ? site_e($creative['label']) : 'Advertisement';
        $active = $index === 0 ? ' is-active' : '';
        $duration = isset($creative['duration']) ? max(4, (int)$creative['duration']) : 12;

        echo '<div class="bw-ad-creative' . $active . '" data-ad-creative data-ad-duration="' . $duration . '">';
        if($href !== '') echo '<a href="' . $href . '" target="_blank" rel="noopener sponsored" aria-label="' . $label . '">';

        if($type === 'video') {
            echo '<video muted playsinline preload="metadata" data-ad-video aria-label="' . $label . '"><source src="' . $src . '"></video>';
        }
        else {
            echo '<img src="' . $src . '" alt="' . $label . '" loading="lazy">';
        }

        if($href !== '') echo '</a>';
        echo '</div>';
    }

    echo '</aside>';
}
?>
