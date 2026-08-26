<?php
error_reporting(0);
include('../../essential/backbone.php');

// validate-pet-name.php — called by the petBuilder SWF as the weevil types a name.
// The SWF expects a response containing "error=0" for a valid name (it renders the
// name box as "ERROR:<code>", so error=0 means OK). Any other code = invalid.
if(isset($_POST)) {
    $petName = isset($_POST['name']) ? trim($_POST['name']) : (isset($_POST['petName']) ? trim($_POST['petName']) : '');
    $idx = isset($_POST['idx']) ? intval($_POST['idx']) : (isset($_POST['userIDX']) ? intval($_POST['userIDX']) : 0);

    // Length sanity (2-16 chars).
    if(strlen($petName) < 2 || strlen($petName) > 16) {
        echo 'error=1';
        exit;
    }
    if(trim($petName) === '') {
        echo 'error=1';
        exit;
    }

    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Reject a name this owner already uses (case-insensitive).
    if($idx > 0) {
        $owner = getAllWeevilStats($idx);
        if($owner != null && is_array($owner)) {
            $chk = $db->prepare("SELECT COUNT(*) FROM pets WHERE ownerID = ? AND LOWER(name) = LOWER(?)");
            $chk->bind_param('ss', $owner['username'], $petName);
            $chk->execute();
            $res = $chk->get_result()->fetch_array();
            if(intval($res[0]) > 0) {
                echo 'error=2'; // duplicate for this owner
                exit;
            }
        }
    }

    // Valid.
    echo 'error=0';
}
else {
    echo 'error=0';
}
?>
