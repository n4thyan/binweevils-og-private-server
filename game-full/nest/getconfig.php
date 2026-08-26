<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST)) {
    $ownerName = $_POST['id'];
    $hash = $_POST['hash'];
    $st = $_POST['st'];
    $instanceID = rand();

    $roomInfo = "";
    $gardenInfo = "";

    $machineNames = ['Photo Studio', 'Magazines', 'Tycoon Business', 'Mulch Topup', 'Dosh Topup'];
    $machineTypeIds = ['Photo Studio' => 1, 'Magazines' => 2, 'Tycoon Business' => 3, 'Mulch Topup' => 4, 'Dosh Topup' => 5];
    $machineCurrencyTypes = ['Photo Studio' => 0, 'Magazines' => 0, 'Tycoon Business' => 0, 'Mulch Topup' => 0, 'Dosh Topup' => 1];

    if(!checkHash(["hash" => $hash, "id" => $ownerName, "st" => $st])) {
        echo 'res=999';
        return;
    }

    $roomSetup = checkUserRooms($ownerName);
    getNestConfig($ownerName);
    sleep(2);
    $config = getNestConfig($ownerName); //incase fails, sometimes does
    $tycoonBusinesses = grabBusinesses($_COOKIE['weevil_name']);
    $gardenItemsInUse = getGardenItemsInUse($ownerName);

    foreach($roomSetup as $room){
        $roomType = CheckRoomType($room[1]);
        if($roomType == "nest-room"){
            if($ownerName == $_COOKIE['weevil_name']){
                $roomInfo .= '<loc id="'.$room[1].'" instanceID="'.$room[2].'" colour="'.$room[3].'" />';
            }
            else{
                $roomInfo .= '<loc id="'.strval(0-$room[1]).'" instanceID="'.$room[2].'" colour="'.$room[3].'" />';
            }
        }
        else {
            if($ownerName == $_COOKIE['weevil_name']){
                $roomInfo .= '<loc id="'.$room[1].'" instanceID="'.$room[2].'" colour="'.$room[3].'" signClr="'.$room[6].'" signTxtClr="'.$room[5].'" busOpen="'.$room[4].'" name="'.$room[7].'"/>';
            }
            else{
                $roomInfo .= '<loc id="'.strval(0-$room[1]).'" instanceID="'.$room[2].'" colour="'.$room[3].'" signClr="'.$room[6].'" signTxtClr="'.$room[5].'" busOpen="'.$room[4].'" name="'.$room[7].'"/>';
            }
        }
    }

    foreach($roomSetup as $room){
        $userItemsInUse = getNestItemsInUse($room[2]);

        if(!$userItemsInUse == false){
            foreach($userItemsInUse as $item){
                //$spotc = $item[10] == 0 ? 0:$item[10];
                $roomInfo .= '<item id="'.$item[0].'" cat="'.$item[11].'" configName="'.$item[6].'" locID="'.$item[7].'" pc="1" crntPos="'.$item[8].'" fID="'.$item[9].'" clr="'.$item[3].'" spot="'.$item[10].'" />';
            }
        }
    }

    if(!$gardenItemsInUse == false) {
        foreach($gardenItemsInUse as $gardenItem) {
            $gardenItemConfig = getGardenItemConfigById($gardenItem[2]);
            $gardenInfo .= '<item id="' . $gardenItem[0] . '" cat="' . $gardenItemConfig["category"] . '" fName="' . $gardenItemConfig["configLocation"] . '" locID="' . $gardenItem[6] . '" pc="1" x="' . $gardenItem[8] . '" z="' . $gardenItem[9] . '" r="' . $gardenItem[10] . '" clr="'.$gardenItem[4].'"/>';
        }
    }

    if($tycoonBusinesses == null) {
        foreach($machineNames as $businessNames)
        sendBusiness($_COOKIE['weevil_name'], $machineTypeIds[$businessNames], $businessNames, $machineCurrencyTypes[$businessNames]);
    }
    else {
        foreach($machineNames as $businessNames) {
            if(!in_array($businessNames, $tycoonBusinesses["0"]))
            sendBusiness($_COOKIE['weevil_name'], $machineTypeIds[$businessNames], $businessNames, $machineCurrencyTypes[$businessNames]);
        }
    }

    if($config != null)
    echo '<?xml version="1.0" encoding="UTF-8"?><nestConfig id=\'' . $config['id'] . '\' idx=\'' . $config['idx'] . '\' lastUpdate=\'' . $config['lastUpdate'] . '\' score=\'' . $config['score'] . '\' canSubmit=\'0\' gardenCanSubmit=\'0\' fuel=\'' . (intval($config['fuel']) >= 80000 ? '80000' : $config['fuel']) . '\' weevilXp=\'' . $config['xp'] . '\' gardenSize=\'' . $config['gardenSize'] . '\'>'. $roomInfo .'<!-- getPositionedGardenItems  -->' . $gardenInfo . '<!-- /getPositionedGardenItems  --></nestConfig>';
    else echo 'res=999';
}
else echo 'res=999';
?>