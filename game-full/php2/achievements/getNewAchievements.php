<?php
error_reporting(0);
include('../../essential/backbone.php');

function newAchievementsResponse($code, $ids = []) {
    echo http_build_query([
        'responseCode' => $code,
        'newAchievements' => implode(',', $ids)
    ], '', '&');
}

if (!isset($_POST['idx'], $_POST['hash'], $_POST['timer'], $_COOKIE['weevil_name'], $_COOKIE['sessionId'])) {
    newAchievementsResponse(999);
    exit;
}

$idx = filter_var($_POST['idx'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$hash = (string) $_POST['hash'];
$timer = (string) $_POST['timer'];
if ($idx === false ||
    !confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']) ||
    !checkHash(['hash' => $hash, 'idx' => $idx, 'timer' => $timer])) {
    newAchievementsResponse(999);
    exit;
}

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($db->connect_errno) {
    newAchievementsResponse(999);
    exit;
}

$userStmt = $db->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
if (!$userStmt) {
    newAchievementsResponse(999);
    exit;
}
$userStmt->bind_param('s', $_COOKIE['weevil_name']);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
$userStmt->close();
if (!$user || (int) $user['id'] !== $idx) {
    $db->close();
    newAchievementsResponse(999);
    exit;
}

$db->begin_transaction();
try {
    $select = $db->prepare(
        'SELECT id, achievementId FROM achievementscompleted WHERE idx = ? AND is_it_new = 1 ORDER BY completedDate ASC, id ASC FOR UPDATE'
    );
    if (!$select) {
        throw new RuntimeException('select prepare failed');
    }
    $select->bind_param('i', $idx);
    $select->execute();
    $result = $select->get_result();
    $ids = [];
    $rowIds = [];
    $seen = [];
    while ($row = $result->fetch_assoc()) {
        $rowIds[] = (int) $row['id'];
        $achievementId = (int) $row['achievementId'];
        if ($achievementId > 0 && !isset($seen[$achievementId])) {
            $seen[$achievementId] = true;
            $ids[] = $achievementId;
        }
    }
    $select->close();

    if ($rowIds) {
        $placeholders = implode(',', array_fill(0, count($rowIds), '?'));
        $types = str_repeat('i', count($rowIds));
        $update = $db->prepare("UPDATE achievementscompleted SET is_it_new = 0 WHERE id IN ($placeholders)");
        if (!$update) {
            throw new RuntimeException('update prepare failed');
        }
        $update->bind_param($types, ...$rowIds);
        $update->execute();
        $update->close();
    }

    $db->commit();
    $db->close();
    newAchievementsResponse(1, $ids);
} catch (Throwable $error) {
    $db->rollback();
    $db->close();
    newAchievementsResponse(999);
}
?>
