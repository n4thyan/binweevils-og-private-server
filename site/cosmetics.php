<?php
function site_reward_catalog() {
    static $catalog = null;
    if($catalog !== null) return $catalog;

    $catalog = [
        'custom-name-colour' => [
            'name' => 'Custom Name Colour',
            'slot' => 'username_color',
            'cost' => 100000,
            'prestige' => 0,
            'description' => 'Unlock a hex colour picker in Settings and choose any colour for your website username.',
            'value' => '#075984',
        ],
        'title-resident' => [
            'name' => 'Bin Resident',
            'slot' => 'title',
            'cost' => 75000,
            'prestige' => 0,
            'description' => 'Show "Bin Resident" beneath your name on the website.',
            'value' => 'Bin Resident',
        ],
        'title-mulch-master' => [
            'name' => 'Mulch Master',
            'slot' => 'title',
            'cost' => 350000,
            'prestige' => 1,
            'description' => 'A classic Bin-flavoured title for experienced Weevils.',
            'value' => 'Mulch Master',
        ],
        'title-bin-tycoon' => [
            'name' => 'Bin Tycoon',
            'slot' => 'title',
            'cost' => 1500000,
            'prestige' => 4,
            'description' => 'Show your long-term progression with the Bin Tycoon title.',
            'value' => 'Bin Tycoon',
        ],
        'title-prestige-veteran' => [
            'name' => 'Prestige Veteran',
            'slot' => 'title',
            'cost' => 5000000,
            'prestige' => 8,
            'description' => 'A high-Prestige title for veteran players.',
            'value' => 'Prestige Veteran',
        ],
        'title-bin-legend' => [
            'name' => 'Bin Legend',
            'slot' => 'title',
            'cost' => 15000000,
            'prestige' => 11,
            'description' => 'An endgame title reserved for very high Prestige.',
            'value' => 'Bin Legend',
        ],
        'title-prestige-xiii' => [
            'name' => 'Prestige XIII',
            'slot' => 'title',
            'cost' => 30000000,
            'prestige' => 13,
            'description' => 'A capstone title for reaching Prestige 13.',
            'value' => 'Prestige XIII',
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
