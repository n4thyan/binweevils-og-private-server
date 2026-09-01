<?php
function site_reward_catalog() {
    static $catalog = null;
    if($catalog !== null) return $catalog;

    $catalog = [
        'custom-name-colour' => [
            'name' => 'Custom Username Colour',
            'slot' => 'username_color',
            'cost' => 100,
            'prestige' => 0,
            'description' => 'Unlock the Settings colour picker and choose any safe six-digit hex colour for your website username.',
            'value' => '#075984',
        ],
        'title-resident' => [
            'name' => 'Bin Resident',
            'slot' => 'title',
            'cost' => 25,
            'prestige' => 0,
            'description' => 'Show "Bin Resident" beneath your name on the website.',
            'value' => 'Bin Resident',
        ],
        'title-nest-dweller' => [
            'name' => 'Nest Dweller',
            'slot' => 'title',
            'cost' => 50,
            'prestige' => 0,
            'description' => 'A friendly early-game title for players making the Binscape their home.',
            'value' => 'Nest Dweller',
        ],
        'title-bin-explorer' => [
            'name' => 'Bin Explorer',
            'slot' => 'title',
            'cost' => 75,
            'prestige' => 0,
            'description' => 'For Weevils who like discovering rooms, shops and secrets.',
            'value' => 'Bin Explorer',
        ],
        'title-mulch-master' => [
            'name' => 'Mulch Master',
            'slot' => 'title',
            'cost' => 150,
            'prestige' => 1,
            'description' => 'A classic Bin-flavoured title unlocked from Prestige 1.',
            'value' => 'Mulch Master',
        ],
        'title-bintastic' => [
            'name' => 'Bintastic',
            'slot' => 'title',
            'cost' => 200,
            'prestige' => 1,
            'description' => 'A restrained celebratory title for Prestige 1 players.',
            'value' => 'Bintastic',
        ],
        'title-bin-tycoon' => [
            'name' => 'Bin Tycoon',
            'slot' => 'title',
            'cost' => 250,
            'prestige' => 1,
            'description' => 'A launch-era Prestige 1 title for established private-server players.',
            'value' => 'Bin Tycoon',
        ],
    ];

    return $catalog;
}

function site_cosmetics_ensure_schema($db) {
    static $done = false;
    if($done) return true;

    $q = $db->query('SHOW TABLES LIKE \'site_cosmetic_unlocks\'');
    if(!$q || !$q->fetch_array()) {
        $db->query('CREATE TABLE IF NOT EXISTS site_cosmetic_unlocks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            reward_key VARCHAR(64) NOT NULL,
            cost_paid INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_user_reward (user_id, reward_key),
            KEY idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    }

    $q = $db->query('SHOW TABLES LIKE \'site_cosmetic_equipped\'');
    if(!$q || !$q->fetch_array()) {
        $db->query('CREATE TABLE IF NOT EXISTS site_cosmetic_equipped (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            slot VARCHAR(32) NOT NULL,
            reward_key VARCHAR(64) NOT NULL,
            meta JSON DEFAULT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_user_slot (user_id, slot),
            KEY idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    } else {
        $col = $db->query('SHOW COLUMNS FROM site_cosmetic_equipped WHERE Field = \'meta\'');
        if(!$col || !$col->fetch_array()) {
            $db->query('ALTER TABLE site_cosmetic_equipped ADD COLUMN meta JSON DEFAULT NULL AFTER reward_key');
        }
    }

    $done = true;
    return true;
}

function site_cosmetics_state($db, $userId) {
    $state = [
        'ready' => false,
        'unlocked' => [],
        'equipped' => [],
        'meta' => [],
    ];

    if(!site_cosmetics_ensure_schema($db)) return $state;
    $state['ready'] = true;
    $userId = (int)$userId;

    $q = $db->prepare('SELECT reward_key FROM site_cosmetic_unlocks WHERE user_id = ?');
    if($q) {
        $q->bind_param('i', $userId);
        $q->execute();
        $res = $q->get_result();
        while($row = $res->fetch_array(MYSQLI_ASSOC)) {
            $state['unlocked'][(string)$row['reward_key']] = true;
        }
    }

    $q = $db->prepare('SELECT slot, reward_key, meta FROM site_cosmetic_equipped WHERE user_id = ?');
    if($q) {
        $q->bind_param('i', $userId);
        $q->execute();
        $res = $q->get_result();
        while($row = $res->fetch_array(MYSQLI_ASSOC)) {
            $slot = (string)$row['slot'];
            $state['equipped'][$slot] = (string)$row['reward_key'];
            if(isset($row['meta']) && is_string($row['meta']) && $row['meta'] !== '') {
                $meta = json_decode($row['meta'], true);
                if(is_array($meta)) $state['meta'][$slot] = $meta;
            }
        }
    }

    return $state;
}

function site_cosmetic_equipped_value($state, $slot, $fallback = '') {
    if(empty($state['equipped'][$slot])) return $fallback;
    $key = (string)$state['equipped'][$slot];
    $catalog = site_reward_catalog();
    if(empty($catalog[$key]) || $catalog[$key]['slot'] !== $slot) return $fallback;

    $value = $catalog[$key]['value'];

    if($slot === 'username_color' && $key === 'custom-name-colour' && !empty($state['meta'][$slot]) && is_array($state['meta'][$slot])) {
        $hex = isset($state['meta'][$slot]['colour_hex']) ? (string)$state['meta'][$slot]['colour_hex'] : '';
        if($hex !== '' && preg_match('/^#[0-9a-f]{6}$/i', $hex)) {
            return $hex;
        }
    }

    return $value;
}

function site_cosmetic_equipped($state, $slot, $fallback = '') {
    if(empty($state['equipped'][$slot])) return $fallback;
    $key = (string)$state['equipped'][$slot];
    $catalog = site_reward_catalog();
    if(empty($catalog[$key]) || $catalog[$key]['slot'] !== $slot) return $fallback;
    return $key;
}

function site_cosmetic_owned($state, $slot) {
    $result = [];
    foreach($state['unlocked'] ?? [] as $key => $rewardKey) {
        if(!empty($state['unlocked'][$key]) && !empty($rewardKey)) {
            $catalog = site_reward_catalog();
            if(!empty($catalog[$key]) && $catalog[$key]['slot'] === $slot) {
                $result[] = $key;
            }
        }
    }
    return $result;
}

function site_cosmetic_owned_array($state, $slot) {
    $result = [];
    foreach($state['unlocked'] ?? [] as $key => $rewardKey) {
        if(!empty($state['unlocked'][$key]) && !empty($rewardKey)) {
            $catalog = site_reward_catalog();
            if(!empty($catalog[$key]) && $catalog[$key]['slot'] === $slot) {
                $result[] = $catalog[$key]['value'];
            }
        }
    }
    return $result;
}
