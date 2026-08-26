<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $compID = $_POST['compID'];
    $itemID = $_POST['itemID'];
    
    if(isHuntAvailable($compID)) {
        $count = getAmountOfHuntItemsFoundById($compID);

        switch(intval($compID)) {
            case 59:
                $eggHuntIds = [1, 2, 3, 4, 5, 6, 7, 8, 9];

                if($count >= count($eggHuntIds))
                echo 'res=1&status=1'; // hunt is already fully completed
                else if(in_array(intval($itemID), $eggHuntIds)) {
                    if(!hasUserFoundHuntItem($compID, $itemID)) {
                        setUserFoundHuntItem($compID, $itemID);
                        $count++;

                        if($count >= count($eggHuntIds))
                        echo 'res=1&status=3';
                        else
                        echo 'res=1&status=2&count=' . strval($count) . '&max=9';
                    }
                    else
                    echo 'res=1&status=0&count=' . strval($count) . '&max=9';
                }
                else
                echo 'res=999';
                return;

            default:
                echo 'res=999';
                return;
        }
    }
    else
    echo 'res=999';
}
else
echo 'res=999';
?>