<?php
error_reporting(0);
include('../../essential/backbone.php');

function completedAchievementsResponse($code, $ids = [], $lastId = 0) {
    echo http_build_query([
        'responseCode' => $code,
        'userCompletedAchievements' => implode(',', $ids),
        // The original client contract contains this misspelling.
        'lastCompletedAchivement' => $lastId
    ], '', '&');
}

if (!isset($_POST['idx'], $_POST['hash'], $_POST['timer'], $_COOKIE['weevil_name'], $_COOKIE['sessionId'])) {
    completedAchievementsResponse(999);
    exit;
}

$idx = filter_var($_POST['idx'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$hash = (string) $_POST['hash'];
$timer = (string) $_POST['timer'];

if ($idx === false ||
    !confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']) ||
    !checkHash(['hash' => $hash, 'idx' => $idx, 'timer' => $timer])) {
    completedAchievementsResponse(999);
    exit;
}

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($db->connect_errno) {
    completedAchievementsResponse(999);
    exit;
}

$stmt = $db->prepare(
    'SELECT achievementId FROM achievementscompleted WHERE idx = ? ORDER BY completedDate DESC, id DESC'
);
if (!$stmt) {
    completedAchievementsResponse(999);
    exit;
}

$stmt->bind_param('i', $idx);
$stmt->execute();
$result = $stmt->get_result();
$ids = [];
$seen = [];
$lastId = 0;
while ($row = $result->fetch_assoc()) {
    $achievementId = (int) $row['achievementId'];
    if ($achievementId <= 0) {
        continue;
    }
    if ($lastId === 0) {
        $lastId = $achievementId;
    }
    if (!isset($seen[$achievementId])) {
        $seen[$achievementId] = true;
        $ids[] = $achievementId;
    }
}
$stmt->close();
$db->close();

completedAchievementsResponse(1, $ids, $lastId);
?>
