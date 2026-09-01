<?php
// Launch referral rewards are server-owned constants. The historical client contains
// no authoritative amounts; these are intentionally modest early-game values.
const REFERRAL_REWARD_MULCH = 500;
const REFERRAL_REWARD_DOSH = 5;
const REFERRAL_REWARD_XP = 25;

function referral_normalize_code($value) {
    return strtoupper(trim((string)$value));
}

function referral_get_or_create_code(mysqli $db, $userId, $username = '') {
    $userId = (int)$userId;
    $q = $db->prepare('SELECT code FROM referral_codes WHERE user_id = ? LIMIT 1');
    $q->bind_param('i', $userId);
    $q->execute();
    $row = $q->get_result()->fetch_assoc();
    if($row) return $row['code'];

    $base = 'BW' . strtoupper(base_convert((string)$userId, 10, 36));
    for($attempt = 0; $attempt < 5; $attempt++) {
        $code = $base . strtoupper(substr(hash('sha256', $username . ':' . $userId . ':' . random_bytes(16)), 0, 7));
        $now = time();
        $insert = $db->prepare('INSERT IGNORE INTO referral_codes (user_id, code, created_at) VALUES (?, ?, ?)');
        $insert->bind_param('isi', $userId, $code, $now);
        $insert->execute();
        if($insert->affected_rows === 1) return $code;

        $q->execute();
        $row = $q->get_result()->fetch_assoc();
        if($row) return $row['code'];
    }
    throw new RuntimeException('Could not allocate referral code.');
}

function referral_find_inviter(mysqli $db, $code, $lock = false) {
    $code = referral_normalize_code($code);
    if(!preg_match('/^BW[A-Z0-9]{8,22}$/', $code)) return null;
    $sql = 'SELECT rc.user_id, rc.code, u.username FROM referral_codes rc JOIN users u ON u.id = rc.user_id WHERE rc.code = ? LIMIT 1';
    if($lock) $sql .= ' FOR UPDATE';
    $q = $db->prepare($sql);
    $q->bind_param('s', $code);
    $q->execute();
    $row = $q->get_result()->fetch_assoc();
    return $row ?: null;
}

function referral_record_registration(mysqli $db, $inviterId, $referredId, $code, $createdAt) {
    $inviterId = (int)$inviterId;
    $referredId = (int)$referredId;
    if($inviterId <= 0 || $referredId <= 0 || $inviterId === $referredId) return false;
    $code = referral_normalize_code($code);
    $state = 'pending';
    $q = $db->prepare('INSERT INTO referrals (inviter_user_id, referred_user_id, referral_code, created_at, reward_state) VALUES (?, ?, ?, ?, ?)');
    $q->bind_param('iisis', $inviterId, $referredId, $code, $createdAt, $state);
    return $q->execute() && $q->affected_rows === 1;
}

function referral_claim_pending_reward($username) {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $db->begin_transaction();
    try {
        $q = $db->prepare('SELECT id FROM users WHERE username = ? LIMIT 1 FOR UPDATE');
        $q->bind_param('s', $username);
        $q->execute();
        $user = $q->get_result()->fetch_assoc();
        if(!$user) {
            $db->rollback();
            return ['responseCode' => 2];
        }
        $userId = (int)$user['id'];

        $q = $db->prepare("SELECT id, inviter_user_id FROM referrals WHERE referred_user_id = ? AND reward_state = 'pending' LIMIT 1 FOR UPDATE");
        $q->bind_param('i', $userId);
        $q->execute();
        $referral = $q->get_result()->fetch_assoc();
        if(!$referral || (int)$referral['inviter_user_id'] === $userId) {
            $db->rollback();
            return ['responseCode' => 2];
        }

        $mulch = REFERRAL_REWARD_MULCH;
        $dosh = REFERRAL_REWARD_DOSH;
        $xp = REFERRAL_REWARD_XP;
        $q = $db->prepare('UPDATE users SET mulch = mulch + ?, dosh = dosh + ?, xp = xp + ?, xp1 = xp1 + ? WHERE id = ?');
        $q->bind_param('iiiii', $mulch, $dosh, $xp, $xp, $userId);
        $q->execute();
        if($q->affected_rows !== 1) throw new RuntimeException('Reward balance update failed.');

        $now = time();
        $state = 'granted';
        $q = $db->prepare('UPDATE referrals SET reward_state = ?, rewarded_at = ?, reward_mulch = ?, reward_dosh = ?, reward_xp = ? WHERE id = ? AND reward_state = \'pending\'');
        $referralId = (int)$referral['id'];
        $q->bind_param('siiiii', $state, $now, $mulch, $dosh, $xp, $referralId);
        $q->execute();
        if($q->affected_rows !== 1) throw new RuntimeException('Reward state update failed.');

        $q = $db->prepare('SELECT mulch, dosh, xp, xp1 FROM users WHERE id = ? LIMIT 1');
        $q->bind_param('i', $userId);
        $q->execute();
        $totals = $q->get_result()->fetch_assoc();
        $db->commit();
        return [
            'responseCode' => 1,
            'mulch' => (int)$totals['mulch'],
            'dosh' => (int)$totals['dosh'],
            'xp' => (int)$totals['xp'],
            'xp1' => (int)$totals['xp1'],
        ];
    } catch(Throwable $e) {
        $db->rollback();
        error_log('Referral reward failed: ' . $e->getMessage());
        return ['responseCode' => 999];
    }
}

function referral_account_summary($userId, $username) {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $code = referral_get_or_create_code($db, (int)$userId, (string)$username);
    $q = $db->prepare("SELECT r.reward_state, r.created_at, r.rewarded_at, r.reward_mulch, r.reward_dosh, r.reward_xp, u.username referred_username FROM referrals r JOIN users u ON u.id = r.referred_user_id WHERE r.inviter_user_id = ? ORDER BY r.created_at DESC LIMIT 25");
    $q->bind_param('i', $userId);
    $q->execute();
    $history = $q->get_result()->fetch_all(MYSQLI_ASSOC);
    $granted = 0;
    foreach($history as $row) if($row['reward_state'] === 'granted') $granted++;
    return ['code' => $code, 'count' => count($history), 'granted' => $granted, 'pending' => count($history) - $granted, 'history' => $history];
}
?>
