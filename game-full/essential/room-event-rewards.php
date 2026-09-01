<?php
function checkOrderedRewardHash($hash, array $values) {
    $expected = md5('P07aJK8soogA815CxjkTcA==' . implode('', array_map('strval', $values)));
    return is_string($hash) && hash_equals($expected, $hash);
}

/**
 * Atomically claims a recently server-authorised Flum mushroom reward.
 * The Node room handler opens a short claim window by setting
 * mushrooms.validUntil only after a fully-grown mushroom is popped.
 */
function claimFlumsReward($rewardType, $requestedTotal, $requestedIdx) {
    if(!isset($_COOKIE['weevil_name'], $_COOKIE['sessionId']) ||
       !confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId'])) {
        return 'res=999';
    }

    $rewardType = (string)$rewardType;
    $total = (int)$requestedTotal;
    $idx = (int)$requestedIdx;
    if(($rewardType !== 'mulch' && $rewardType !== 'xp') || $total <= 0) {
        return 'res=999';
    }

    $weevil = getAllWeevilStatsByName($_COOKIE['weevil_name']);
    if(!$weevil || (int)$weevil['id'] !== $idx) {
        return 'res=999';
    }

    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $db->begin_transaction();

    try {
        $q = $db->prepare(
            'SELECT mushroomType, rewardAmount, validUntil
               FROM mushrooms
              WHERE rewardType = ? AND rewardAmount = ? AND validUntil >= UNIX_TIMESTAMP()
              ORDER BY validUntil DESC
              LIMIT 1 FOR UPDATE'
        );
        $q->bind_param('si', $rewardType, $total);
        $q->execute();
        $reward = $q->get_result()->fetch_array(MYSQLI_ASSOC);
        if(!$reward) {
            $db->rollback();
            return 'res=0&err=20&x=y';
        }

        $mushroomType = (int)$reward['mushroomType'];
        $claimToken = (int)$reward['validUntil'];
        $q = $db->prepare(
            'SELECT lastClaimed FROM claimedmushrooms
              WHERE idx = ? AND mushroomType = ? FOR UPDATE'
        );
        $q->bind_param('ii', $idx, $mushroomType);
        $q->execute();
        $existing = $q->get_result()->fetch_array(MYSQLI_ASSOC);
        if($existing && (int)$existing['lastClaimed'] >= $claimToken) {
            $db->rollback();
            return 'res=0&err=20&x=y';
        }

        if($rewardType === 'xp') {
            // XP earned is permanent progress and immediately banked/spendable.
            $q = $db->prepare('UPDATE users SET xp = xp + ?, xp1 = xp1 + ? WHERE id = ?');
            $q->bind_param('iii', $total, $total, $idx);
        } else {
            $q = $db->prepare('UPDATE users SET mulch = mulch + ? WHERE id = ?');
            $q->bind_param('ii', $total, $idx);
        }
        $q->execute();
        if($q->affected_rows !== 1) {
            throw new Exception('balance update failed');
        }

        $q = $db->prepare(
            'INSERT INTO claimedmushrooms (idx, mushroomType, lastClaimed)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE lastClaimed = VALUES(lastClaimed)'
        );
        $q->bind_param('iii', $idx, $mushroomType, $claimToken);
        $q->execute();

        $field = $rewardType === 'xp' ? 'xp' : 'mulch';
        $q = $db->prepare("SELECT `$field` AS balance FROM users WHERE id = ? LIMIT 1");
        $q->bind_param('i', $idx);
        $q->execute();
        $balance = $q->get_result()->fetch_array(MYSQLI_ASSOC);

        $db->commit();
        return 'res=0&' . $field . '=' . (int)$balance['balance'] . '&err=1&x=y';
    } catch(Throwable $e) {
        $db->rollback();
        return 'res=999';
    }
}
?>
