<?php
	include('../essential/backbone.php');

    if(isset($_POST)) {
        $trackID = $_POST['trackID'];   
        if($trackID == '25'){
            $stats = getWeevilRaceData(23);
            if($stats['bronze'] == 1 || $stats['silver'] == 1 || $stats['gold'] == 1){
                echo 'res=1';
            }
            else{
                echo 'res=0';
            }
        }
        else{
            $stats = getWeevilRaceData(24);
            if($stats['bronze'] == 1 || $stats['silver'] == 1 || $stats['gold'] == 1){
                echo 'res=1';
            }
            else{
                echo 'res=0';
            }
        }
    }
    else echo 'res=0';
?>