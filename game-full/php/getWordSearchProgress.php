<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $userID = $_COOKIE['weevil_name'];
    $gridID = $_POST['gridID'];

    if($userID == $_POST['userID']) {
        $userProgress = getWordsearchProgress($gridID);

        if($userProgress == null)
        echo 'result=0&b=r';
        else
        echo 'result=' . $userProgress["progress"] . '&b=r';
    }
    else
    echo 'responseCode=999';
}
else
echo 'responseCode=999';
?>