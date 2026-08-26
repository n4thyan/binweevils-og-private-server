<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $userID = $_COOKIE['weevil_name'];
    $gridID = $_POST['gridID'];

    if($userID == $_POST['userID']) {
        $userProgress = getCrosswordProgress($gridID);

        if($userProgress == null)
        echo 'prog=0&completed=0&b=r';
        else
        echo 'prog=' . $userProgress["progress"] . '&completed=' . $userProgress["completed"] . '&b=r';
    }
    else
    echo 'responseCode=999';
}
else
echo 'responseCode=999';
?>