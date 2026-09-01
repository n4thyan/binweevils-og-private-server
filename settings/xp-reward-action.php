<?php
include('../site/bootstrap.php');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function reward_response($ok, $message, $extra = [], $code = 200) {
    http_response_code($code);
    echo json_encode(array_merge([
        'ok' => (bool)$ok,
        'message' => (string)$message,
    ], is_array($extra) ? $extra : []));
    exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    reward_response(false, 'Invalid request.', [], 405);
}

if(!$siteLoggedIn || !is_array($siteUser)) {
    reward_response(false, 'You need to log in again.', [], 401);
}

if(!site_csrf_valid(isset($_POST['csrf']) ? (string)$_POST['csrf'] : '')) {
    reward_response(false, 'Your session token is invalid. Refresh the page and try again.', [], 403);
}

if(!rateLimit('site-xp-rewards', 20, 60)) {
    reward_response(false, 'Too many reward actions. Try again in a moment.', [], 429);
}

$action = isset($_POST['action']) ? (string)$_POST['action'] : '';
$rewardKey = isset($_POST['reward_key']) ? (string)$_POST['reward_key'] : '';
$slot = isset($_POST['slot']) ? (string)$_POST['slot'] : '';
$userId = (int)$siteUser['id'];
$catalog = site_reward_catalog();
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if(!site_cosmetics_ensure_schema($db)) {
    reward_response(false, 'The cosmetic storage could not be prepared.', [], 500);
}

if($action === 'buy') {
    if(empty($catalog[$rewardKey])) {
        reward_response(false, 'That XP reward does not exist.', [], 400);
    }

    $reward = $catalog[$rewardKey];
    $cost = (int)$reward['cost'];
    $requiredPrestige = (int)$reward['prestige'];

    $db->begin_transaction();
    try {
        $q = $db->prepare('SELECT xp1, prestige_count FROM users WHERE id = ? LIMIT 1 FOR UPDATE');
        $q->bind_param('i', $userId);
        $q->execute();
        $row = $q->get_result()->fetch_array(MYSQLI_ASSOC);
        if(!$row) throw new Exception('account');

        $q = $db->prepare('SELECT reward_key FROM site_cosmetic_unlocks WHERE user_id = ? AND reward_key = ? LIMIT 1');
        $q->bind_param('is', $userId, $rewardKey);
        $q->execute();
        if($q->get_result()->fetch_array(MYSQLI_ASSOC)) {
            $db->rollback();
            reward_response(false, 'You already own this reward.', ['bankedXp' => (int)$row['xp1']], 409);
        }

        if((int)$row['prestige_count'] < $requiredPrestige) {
            $db->rollback();
            reward_response(false, 'This reward requires Prestige ' . $requiredPrestige . '.', ['bankedXp' => (int)$row['xp1']], 403);
        }

        if((int)$row['xp1'] < $cost) {
            $db->rollback();
            reward_response(false, 'You do not have enough Banked XP for this reward.', ['bankedXp' => (int)$row['xp1']], 400);
        }

        $q = $db->prepare('UPDATE users SET xp1 = xp1 - ? WHERE id = ? AND xp1 >= ? LIMIT 1');
        $q->bind_param('iii', $cost, $userId, $cost);
        $q->execute();
        if($q->affected_rows !== 1) throw new Exception('xp-update');

        $q = $db->prepare('INSERT INTO site_cosmetic_unlocks (user_id, reward_key, cost_paid) VALUES (?, ?, ?)');
        $q->bind_param('isi', $userId, $rewardKey, $cost);
        $q->execute();
        if($q->affected_rows !== 1) throw new Exception('unlock-insert');

        $db->commit();
        reward_response(true, 'Reward unlocked!', [
            'rewardKey' => $rewardKey,
            'bankedXp' => (int)$row['xp1'] - $cost,
        ]);
    }
    catch(Exception $e) {
        $db->rollback();
        reward_response(false, 'The reward purchase could not be completed.', [], 500);
    }
}

if($action === 'equip') {
    if(empty($catalog[$rewardKey])) {
        reward_response(false, 'That XP reward does not exist.', [], 400);
    }

    $reward = $catalog[$rewardKey];
    $rewardSlot = (string)$reward['slot'];

    // Validate colour_hex for custom-name-colour
    $colourHex = null;
    if($rewardSlot === 'username_color' && $rewardKey === 'custom-name-colour' && isset($_POST['colour_hex'])) {
        $raw = (string)$_POST['colour_hex'];
        $raw = ltrim($raw, '#');
        if(preg_match('/^[0-9a-fA-F]{6}$/', $raw)) {
            $colourHex = '#' . strtolower($raw);
        }
    }

    $q = $db->prepare('SELECT reward_key FROM site_cosmetic_unlocks WHERE user_id = ? AND reward_key = ? LIMIT 1');
    $q->bind_param('is', $userId, $rewardKey);
    $q->execute();
    if(!$q->get_result()->fetch_array(MYSQLI_ASSOC)) {
        reward_response(false, 'Unlock this reward before equipping it.', [], 403);
    }

    if($colourHex !== null) {
        $meta = json_encode(['colour_hex' => $colourHex]);
        $q = $db->prepare('INSERT INTO site_cosmetic_equipped (user_id, slot, reward_key, meta) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE reward_key = VALUES(reward_key), meta = VALUES(meta), updated_at = CURRENT_TIMESTAMP');
        $q->bind_param('isss', $userId, $rewardSlot, $rewardKey, $meta);
    } else {
        $q = $db->prepare('INSERT INTO site_cosmetic_equipped (user_id, slot, reward_key) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE reward_key = VALUES(reward_key), updated_at = CURRENT_TIMESTAMP');
        $q->bind_param('iss', $userId, $rewardSlot, $rewardKey);
    }
    $q->execute();

    reward_response(true, 'Reward equipped.', [
        'rewardKey' => $rewardKey,
        'slot' => $rewardSlot,
    ]);
}

if($action === 'unequip') {
    $allowedSlots = ['username_color', 'title'];
    if(!in_array($slot, $allowedSlots, true)) {
        reward_response(false, 'That cosmetic slot is invalid.', [], 400);
    }

    $q = $db->prepare('DELETE FROM site_cosmetic_equipped WHERE user_id = ? AND slot = ?');
    $q->bind_param('is', $userId, $slot);
    $q->execute();
    reward_response(true, 'Cosmetic reset to default.', ['slot' => $slot]);
}

reward_response(false, 'Unknown reward action.', [], 400);
?>
