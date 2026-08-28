<?php
include('bootstrap.php');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if(!$siteLoggedIn || !is_array($siteUser)) {
    http_response_code(401);
    echo json_encode(['ok' => false]);
    exit;
}

echo json_encode([
    'ok' => true,
    'user' => [
        'username' => (string)$siteUser['username'],
        'level' => (int)$siteUser['level'],
        'prestige' => (int)$siteUser['prestige_count'],
        'lifetimeXp' => (int)$siteUser['xp'],
        'bankedXp' => (int)$siteUser['xp1'],
        'nextXp' => (int)$siteUser['xp2'],
        'mulch' => (int)$siteUser['mulch'],
        'dosh' => (int)$siteUser['dosh'],
        'definition' => (string)$siteUser['def'],
    ],
]);
?>
