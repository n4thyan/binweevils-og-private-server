<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$statusFile = __DIR__ . '/runtime-status.json';
$online = false;
$players = null;
$generatedAt = null;

if(is_file($statusFile) && is_readable($statusFile)) {
    $raw = @file_get_contents($statusFile);
    $data = json_decode((string)$raw, true);

    if(is_array($data) && isset($data['generatedAt'])) {
        $generatedAt = (int)$data['generatedAt'];
        $ageMs = (int)round(microtime(true) * 1000) - $generatedAt;

        // Main.js writes every 5 seconds. Give it a generous 20-second grace
        // window before declaring the game server offline.
        if($ageMs >= 0 && $ageMs <= 20000 && !empty($data['online'])) {
            $online = true;
            $players = isset($data['players']) ? max(0, (int)$data['players']) : 0;
        }
    }
}

echo json_encode([
    'ok' => true,
    'online' => $online,
    'players' => $online ? $players : null,
    'generatedAt' => $generatedAt,
]);
?>
