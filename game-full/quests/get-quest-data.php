<?php 
error_reporting(0);
include('../essential/backbone.php');

if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
    $loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

    if($loggedIn == true) {
        $completedTasks = getListOfCompletedTasks();
        $tasks = "";

        if($completedTasks != null) {
            foreach($completedTasks as $task) {
                $tasks .= ',' . $task["tasks"];
            }
        }

        echo 'tasks=999%2C946%2C901%2C902%2C903%2C904%2C905%2C906%2C907%2C908%2C911%2C949%2C950%2C951%2C952%2C953%2C954%2C955%2C956%2C957%2C913%2C916%2C914%2C915%2C917%2C918%2C919%2C945' . urlencode($tasks) . '&itemList=&b=r&responseCode=1';
    }
    else
    echo 'responseCode=999';
}
else
echo 'responseCode=999';
?>