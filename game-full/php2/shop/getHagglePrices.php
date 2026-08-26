<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $nestItemsArray = removeEmptyValuesFromArray(explode(',', $_POST['items'] . ','));
    $gardenItemsArray = removeEmptyValuesFromArray(explode(',', $_POST['gardenItems'] . ','));
    $garden = $_POST['gardenItems'];
    $items = $_POST['items'];
    $hash = $_POST['hash'];
    $timer = $_POST['timer'];
    
    if(!checkHash(["hash" => $hash, "items" => $items, "gardenItems" => $garden, "timer" => $timer]) || count($nestItemsArray) <= 0 && count($gardenItemsArray) <= 0 || (count($nestItemsArray) + count($gardenItemsArray)) > 20)
    echo 'responseCode=999&message=Looks like you are trying to do something here... Please do not attempt anything you may regret.';
    else {
        $weevilStats = getAllWeevilStatsByName($_COOKIE['weevil_name']);
        $currentNestItems = getWeevilItems($weevilStats["id"]);
        $currentGardenItems = getWeevilGardenItems($_COOKIE['weevil_name'], 2);
        $mulchEarned = 0;
        $gamblePrice1Earned = 0;
        $gamblePrice2Earned = 0;
        $gamblePrice3Earned = 0;
        $idsCheckedArray = [];
        $idsCheckedGardenArray = [];

        foreach($currentNestItems as $nestItems) {
            if($nestItems[11] == 2 || $nestItems[11] == 6 || $nestItems[11] == 3 || $nestItems[11] == 4) {
                // nightclub items
                $itemData = getNightclubItemDataById($nestItems[2]);

                if($itemData["canBuy"] == 1) {
                    for($i = 0; $i < count($nestItemsArray); $i++) {
                        if($nestItems[0] == $nestItemsArray[$i] && !in_array($nestItemsArray[$i], $idsCheckedArray)) {
                            $safePrice = floor((20 / 100) * ($itemData["currency"] == "dosh" ? ($itemData["price"] * 500) : $itemData["price"]));
                            $gamblePrice1 = floor((10 / 100) * ($itemData["currency"] == "dosh" ? ($itemData["price"] * 500) : $itemData["price"]));
                            $gamblePrice2 = floor((15 / 100) * ($itemData["currency"] == "dosh" ? ($itemData["price"] * 500) : $itemData["price"]));
                            $gamblePrice3 = floor((40 / 100) * ($itemData["currency"] == "dosh" ? ($itemData["price"] * 500) : $itemData["price"]));

                            $mulchEarned = $mulchEarned + $safePrice;
                            $gamblePrice1Earned = $gamblePrice1Earned + $gamblePrice1;
                            $gamblePrice2Earned = $gamblePrice2Earned + $gamblePrice2;
                            $gamblePrice3Earned = $gamblePrice3Earned + $gamblePrice3;
                            array_push($idsCheckedArray, $nestItemsArray[$i]);
                        }
                    }
                }
            }
            else {
                $itemData = getItemDataById($nestItems[2]);

                if($itemData["canBuy"] == 1) {
                    for($i = 0; $i < count($nestItemsArray); $i++) {
                        if($nestItems[0] == $nestItemsArray[$i] && !in_array($nestItemsArray[$i], $idsCheckedArray)) {
                            $safePrice = floor((20 / 100) * ($itemData["currency"] == "dosh" ? ($itemData["price"] * 500) : $itemData["price"]));
                            $gamblePrice1 = floor((10 / 100) * ($itemData["currency"] == "dosh" ? ($itemData["price"] * 500) : $itemData["price"]));
                            $gamblePrice2 = floor((15 / 100) * ($itemData["currency"] == "dosh" ? ($itemData["price"] * 500) : $itemData["price"]));
                            $gamblePrice3 = floor((40 / 100) * ($itemData["currency"] == "dosh" ? ($itemData["price"] * 500) : $itemData["price"]));

                            $mulchEarned = $mulchEarned + $safePrice;
                            $gamblePrice1Earned = $gamblePrice1Earned + $gamblePrice1;
                            $gamblePrice2Earned = $gamblePrice2Earned + $gamblePrice2;
                            $gamblePrice3Earned = $gamblePrice3Earned + $gamblePrice3;
                            array_push($idsCheckedArray, $nestItemsArray[$i]);
                        }
                    }
                }
            }
        }

        foreach($currentGardenItems as $gardenItems) {
            $itemData = getGardenItemConfigById($gardenItems[2]);

            if($itemData["canBuy"] == 1) {
                for($i = 0; $i < count($gardenItemsArray); $i++) {
                    if($gardenItems[0] == $gardenItemsArray[$i] && !in_array($gardenItemsArray[$i], $idsCheckedGardenArray)) {
                        $safePrice = floor((20 / 100) * ($itemData["currency"] == "dosh" ? ($itemData["price"] * 500) : $itemData["price"]));
                        $gamblePrice1 = floor((10 / 100) * ($itemData["currency"] == "dosh" ? ($itemData["price"] * 500) : $itemData["price"]));
                        $gamblePrice2 = floor((15 / 100) * ($itemData["currency"] == "dosh" ? ($itemData["price"] * 500) : $itemData["price"]));
                        $gamblePrice3 = floor((40 / 100) * ($itemData["currency"] == "dosh" ? ($itemData["price"] * 500) : $itemData["price"]));
    
                        $mulchEarned = $mulchEarned + $safePrice;
                        $gamblePrice1Earned = $gamblePrice1Earned + $gamblePrice1;
                        $gamblePrice2Earned = $gamblePrice2Earned + $gamblePrice2;
                        $gamblePrice3Earned = $gamblePrice3Earned + $gamblePrice3;
                        array_push($idsCheckedGardenArray, $gardenItemsArray[$i]);
                    }
                }
            }
        }

        echo 'responseCode=1&safePrice=' . $mulchEarned . '&gamblePrice1=' . $gamblePrice1Earned . '&gamblePrice2=' . $gamblePrice2Earned . '&gamblePrice3=' . $gamblePrice3Earned;
    }
}
else
echo 'responseCode=999&message=Looks like you are trying to do something here... Please do not attempt anything you may regret.';
?>