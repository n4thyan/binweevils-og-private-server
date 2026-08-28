<?php
function site_reward_catalog() {
    static $catalog = null;
    if($catalog !== null) return $catalog;

    $catalog = [
        'name-leaf' => [
            'name' => 'Leaf Green Name',
            'slot' => 'username_color',
            'cost' => 25000,
            'prestige' => 0,
            'description' => 'Use a classic leafy green for your website Weevil name.',
            'value' => '#4f8b20',
        ],
        'name-bin-blue' => [
            'name' => 'Bin Blue Name',
            'slot' => 'username_color',
            'cost' => 50000,
            'prestige' => 0,
            'description' => 'Use the familiar Bin blue for your website Weevil name.',
            'value' => '#075984',
        ],
        'name-orange' => [
            'name' => 'Orange Name',
            'slot' => 'username_color',
            'cost' => 100000,
            'prestige' => 0,
            'description' => 'A warm orange name inspired by the classic site chrome.',
            'value' => '#c85f10',
        ],
        'name-purple' => [
            'name' => 'Purple Name',
            'slot' => 'username_color',
            'cost' => 300000,
            'prestige' => 1,
            'description' => 'A rarer purple website name for Prestiged Weevils.',
            'value' => '#74449b',
        ],
        'name-prestige-gold' => [
            'name' => 'Prestige Gold Name',
            'slot' => 'username_color',
            'cost' => 2000000,
            'prestige' => 5,
            'description' => 'A high-tier gold name for established Prestige players.',
            'value' => '#9b7100',
        ],

        'title-resident' => [
            'name' => 'Bin Resident',
            'slot' => 'title',
            'cost' => 75000,
            'prestige' => 0,
            'description' => 'Show “Bin Resident” beneath your name on the website.',
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

        'bg-classic-sky' => [
            'name' => 'Classic Sky Background',
            'slot' => 'profile_background',
            'cost' => 500000,
            'prestige' => 0,
            'description' => 'Use an existing official/recovered website background behind your Weevil card.',
            'value' => '/assets/images/background.jpg',
        ],
        'bg-classic-bin' => [
            'name' => 'Classic Bin Background',
            'slot' => 'profile_background',
            'cost' => 1000000,
            'prestige' => 2,
            'description' => 'A second recovered Bin Weevils background preset.',
            'value' => '/assets/images/background2.jpg',
        ],
        'bg-classic-banner' => [
            'name' => 'Classic Banner Background',
            'slot' => 'profile_background',
            'cost' => 2000000,
            'prestige' => 4,
            'description' => 'Use recovered official banner artwork as your website profile backdrop.',
            'value' => '/assets/images/banner.jpg',
        ],
    ];

    return $catalog;
}

function site_cosmetics_ensure_schema($db) {
    static $done = false;
    if($done) return true;

    $unlockSql = "CREATE TABLE IF NOT EXISTS site_cosmetic_unlocks (
        user_id INT NOT NULL,
        reward_key VARCHAR(64) NOT NULL,
        cost_paid BIGINT NOT NULL DEFAULT 0,
        unlocked_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, reward_key),
        KEY idx_site_cosmetic_unlocks_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $equipSql = "CREATE TABLE IF NOT EXISTS site_cosmetic_equipped (
        user_id INT NOT NULL,
        slot VARCHAR(32) NOT NULL,
        reward_key VARCHAR(64) NOT NULL,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, slot),
        KEY idx_site_cosmetic_equipped_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if(!$db->query($unlockSql)) return false;
    if(!$db->query($equipSql)) return false;
    $done = true;
    return true;
}

function site_cosmetics_state($db, $userId) {
    $state = [
        'ready' => false,
        'unlocked' => [],
        'equipped' => [],
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

    $q = $db->prepare('SELECT slot, reward_key FROM site_cosmetic_equipped WHERE user_id = ?');
    if($q) {
        $q->bind_param('i', $userId);
        $q->execute();
        $res = $q->get_result();
        while($row = $res->fetch_array(MYSQLI_ASSOC)) {
            $state['equipped'][(string)$row['slot']] = (string)$row['reward_key'];
        }
    }

    return $state;
}

function site_cosmetic_equipped_reward($state, $slot) {
    if(!is_array($state) || empty($state['equipped'][$slot])) return null;
    $key = (string)$state['equipped'][$slot];
    $catalog = site_reward_catalog();
    if(empty($catalog[$key]) || $catalog[$key]['slot'] !== $slot) return null;
    return $catalog[$key];
}

function site_cosmetic_equipped_value($state, $slot, $fallback = '') {
    $reward = site_cosmetic_equipped_reward($state, $slot);
    return $reward ? (string)$reward['value'] : (string)$fallback;
}
?>
