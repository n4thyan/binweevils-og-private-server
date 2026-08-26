<?php
//error_reporting(0);
include('essential/backbone.php');

if(isset($_GET['adminCode']) and isset($_GET['userID']) and isset($_GET['userToChange'])) {
    if($_GET['adminCode'] == "jjsandhdarecool12") {
        $userID = $_GET['userID'];
        $userToChange = $_GET['userToChange'];

        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$q = $db->prepare("SELECT `namesList` FROM `buddylist`;");
		$q->execute();
		
        $res = $q->get_result();

        if($res = $res->fetch_all(MYSQLI_ASSOC)) {
            $neededToBeUpdated = 0;
            $updatedCount = 0;

            foreach($res as $buddyData) {
                if(strpos($buddyData["namesList"], $userID) !== false) {
                    $neededToBeUpdated++;
                    $data = str_replace($userID . ',', $userToChange . ',', $buddyData["namesList"]);

                    $q = $db->prepare("UPDATE `buddylist` SET `namesList` = '$data' WHERE `namesList` = '" . $buddyData["namesList"] . "';");
		            $q->execute();

                    if($q->affected_rows > 0)
                    $updatedCount++;

                    //echo 'Before: ' . $buddyData["namesList"] . '<br>After: ' . $data . '<br><br>';
                }
            }

            echo 'responseCode=1&updatedRows=' . $updatedCount . '&rowsThatNeededToBeUpdated=' . $neededToBeUpdated;
        }
        else
        echo 'responseCode=999';
    }
    else
    echo 'responseCode=999';
}
else
echo 'responseCode=999';
?>