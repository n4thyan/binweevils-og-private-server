<?php
include('../site/bootstrap.php');
include_once('../site/weevil-appearance.php');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function appearance_response($ok, $message, $extra = [], $code = 200) {
    http_response_code($code);
    echo json_encode(array_merge([
        'ok' => (bool)$ok,
        'message' => (string)$message,
    ], is_array($extra) ? $extra : []));
    exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    appearance_response(false, 'Invalid request.', [], 405);
}
if(!$siteLoggedIn || !is_array($siteUser)) {
    appearance_response(false, 'You need to log in again.', [], 401);
}
if(!site_csrf_valid(isset($_POST['csrf']) ? (string)$_POST['csrf'] : '')) {
    appearance_response(false, 'Your session token is invalid. Refresh the page and try again.', [], 403);
}
if(!rateLimit('site-weevil-appearance', 12, 60)) {
    appearance_response(false, 'Too many appearance changes. Try again in a moment.', [], 429);
}

$userId = (int)$siteUser['id'];
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$db->begin_transaction();
try {
    // Prestige and the current definition are re-read under lock. The page value
    // is never trusted for either authorization or merge state.
    $q = $db->prepare('SELECT prestige_count, def FROM users WHERE id = ? LIMIT 1 FOR UPDATE');
    $q->bind_param('i', $userId);
    $q->execute();
    $account = $q->get_result()->fetch_array(MYSQLI_ASSOC);
    if(!$account) throw new Exception('account');

    if(!weevil_advanced_update_allowed($account['prestige_count'])) {
        $db->rollback();
        appearance_response(false, 'Advanced Weevil Appearance requires Prestige 1.', [], 403);
    }

    $mode = isset($_POST['mode']) ? (string)$_POST['mode'] : 'parts';
    if($mode === 'definition') {
        $rawDefinition = isset($_POST['definition']) ? (string)$_POST['definition'] : '';
        $parsed = weevil_parse_definition($rawDefinition);
        if($parsed === null) {
            $db->rollback();
            appearance_response(false, 'That Weevil definition is malformed or contains an unknown body part.', [], 400);
        }
        $definition = weevil_build_definition($parsed);
    }
    else if($mode === 'parts') {
        $fields = [
            'head_type', 'body_type', 'eye_type', 'eyelids', 'antenna_type', 'leg_type',
            'head_colour', 'body_colour', 'eye_colour', 'antenna_colour', 'leg_colour',
        ];
        $changes = [];
        foreach($fields as $field) {
            if(!array_key_exists($field, $_POST)) {
                $db->rollback();
                appearance_response(false, 'All appearance fields are required.', [], 400);
            }
            $changes[$field] = (string)$_POST[$field];
        }
        $definition = weevil_apply_definition_changes((string)$account['def'], $changes);
    }
    else {
        $db->rollback();
        appearance_response(false, 'Unknown appearance action.', [], 400);
    }

    if($definition === null) {
        $db->rollback();
        appearance_response(false, 'The appearance contains an invalid colour or body part.', [], 400);
    }

    $q = $db->prepare('UPDATE users SET def = ? WHERE id = ? LIMIT 1');
    $q->bind_param('si', $definition, $userId);
    $q->execute();
    if($q->affected_rows !== 1 && (string)$account['def'] !== $definition) throw new Exception('update');
    $db->commit();

    $parts = weevil_parse_definition($definition);
    appearance_response(true, 'Weevil appearance saved. It will refresh in-game on your next room change.', [
        'definition' => $definition,
        'parts' => $parts,
    ]);
}
catch(Exception $e) {
    $db->rollback();
    appearance_response(false, 'The Weevil appearance could not be saved.', [], 500);
}
?>
