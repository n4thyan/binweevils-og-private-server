<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $userID = $_POST['userID'];
    $plantInfo = "";
    $gardenPlantsInUse = getGardenPlantsInUse($userID);

    if($gardenPlantsInUse != false) {
        $plantInfo = generatePlantConfigXML($gardenPlantsInUse);

        /*foreach($gardenPlantsInUse as $plant) {
            $plantConfig = getSeedDataById($plant[2]);
            $age = 0;

            switch($plantConfig["category"]) {
                case 1:
                    $age = calculatePerishableAge($plant, ($plantConfig["growTime"] * 60));
                    break;
                case 2:
                    $age = calculateFruitingAge($plant, ($plantConfig["growTime"] * 60));
                    break;
                default:
                    $age = 0;
                    break;
            }

            $ageInMins = round($age / 60);
            $plantInfo .= '<plant id="' . $plant[0] . '" name="' . $plantConfig["name"] . '" fName="' . $plantConfig["fileName"] . '" cat="' . $plantConfig["category"] . '" age="' . $ageInMins . '" growTime="' . $plantConfig["growTime"] . '" cycleTime="' . $plantConfig["cycleTime"] . '" mulch="' . $plantConfig["mulchYield"] . '" xp="' . $plantConfig["xpYield"] . '" x="' . $plant[8] . '" z="' . $plant[9] . '" r="' . $plant[10] . '" watered="0" />';
        }*/
    }

    echo $plantInfo;
}
else
echo 'res=999';
?>