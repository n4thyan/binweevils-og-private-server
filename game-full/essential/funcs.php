<?php
error_reporting(0);

/// Admin Functions
function getNextPage($page){
    $offset = $page * 4;
    $developmentProg = '';
    $developmentProgress = getDevelopmentProgress(4, $offset);
    $i = 0;
    
    foreach($developmentProgress as $progress){
        $i++;
        $icon = "";
        $clr = "";
        $status = "";
        switch($progress[3]){
            case "pending":
                $icon = "danger"; $clr = "FC424A"; $status = "Pending"; break;
            case "workingon":
                $icon = "warning"; $clr = "FFAB00"; $status = "In progress"; break;
            default:
                $icon = "success"; $clr = "00D25B"; $status = "Complete"; break;
        }
        $developmentProg .= '<div class="preview-item border-bottom devlogitem" id="devlog"><div class="preview-thumbnail"><div class="preview-icon bg-'.$icon.'" id="status-bg'.strval($i).'"><i class="'.$progress[1].'"></i></div></div><div class="preview-item-content d-sm-flex flex-grow"><div class="flex-grow"><h6 class="preview-subject">'.$progress[2].'</h6><p class="text-muted mb-0" id="status-txt'.strval($i).'">'.$progress[4].'</p></div><div class="mr-auto text-sm-right pt-2 pt-sm-0"><button class="badge badge-'.$icon.' status-btn" style="border-color: #'.$clr.'!important;" id="status-btn'.strval($i).'">'.$status.'</button></div></div></div>';
    }
    return $developmentProg;
}

function getNextPageAdminLog($page){
    $offset = $page * 8;
    $adminLogs = '';
    $AdminLog = getAdminLogs(8, $offset);
    $i = 0;
    
    foreach($AdminLog as $log){
        $i++;
        $logType = "";
        $item = "";
        switch($log[6]){
            case 0:
                $logType = "Warning";
                break;
            case 1:
                $logType = "Kick";
                break;
            case 2:
                $logType = "Chat ban";
                break;
            case 3:
                $logType = "Account ban";
                break;
            case 4:
                $logType = "Item Reward";
                break;
        }
        if($log[7] == ""){
            $item = "No reward";
        }
        else{
            $item = $log[7];
        }
        $adminLogs .= '<tr id="log-tr'.strval($i).'"><td id="admin-name'.strval($i).'">'.$log[5].'</td><td id="weevil-name'.strval($i).'">'.$log[1].'</td><td id="log-id'.strval($i).'">'.$log[0].'</td><td id="log-type'.strval($i).'">'.$logType.'</td><td id="log-date'.strval($i).'">'.time_ago($log[4]).'</td><td id="item-id'.strval($i).'">'.$item.'</td><td id="reasoning'.strval($i).'"><button class="badge badge-info status-btn" style="border-color: #8F5FE8!important;" id="reason-btn'.strval($i).'">View reason</button><tr>';
    }
    return $adminLogs;
}
/// Admin Functions

/// MD5 Stuff
function setToString($params)
{
    $paramString = "";

    foreach ($params as $param) {
        $paramString .= $param;
    }

    return $paramString;
}

function calcHash($paramString) {
    $hash = md5("P07aJK8soogA815CxjkTcA==" . $paramString);
    $hash2 = $_SESSION['theHasher'];

    if($hash != $hash2)
    return $hash;
    else
    return null;
}

function makeHash(array $params) {
    $utime = microtime(false);
    $usec = intval(substr($utime, 2));
    
    $params['st'] = $usec;
    $paramString = setToString($params);
    $params['hash'] = calcHash($paramString);

    return $params;
}

function isValidRequest($hashParams, $params) {
    if(!isset($hashParams)) return false;

    $paramString = setToString($params);
    $hashString = calcHash($paramString);

    if(empty($paramString)) return false;
    if($hashString === $hashParams) {
        $_SESSION['theHasher'] = $hashString;
        return true;
    }

    return false;
}

function checkHash($arr) {
    $hash = $arr["hash"];
    unset($arr["hash"]);

    if(array_key_exists("timer", $arr))
    ksort($arr);

    $hashKeys = array_keys($arr);
    $hashContent = array_values($arr);

    if(!isValidRequest($hash, $hashContent)) return false;

    return true;
}
/// MD5 Stuff

/// Garden Functions
function getPlantStock() {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $q = $db->prepare("SELECT * FROM seeds WHERE `canBuy` = 1 AND `category` = 1 AND `level` <= 8");
    $q->execute();
    $data1 = $q->get_result()->fetch_all(MYSQLI_ASSOC);

    $q = $db->prepare("SELECT *, probability * RAND() AS random FROM seeds WHERE `canBuy` = 1 AND `category` = 1 AND `level` >= 9 ORDER BY random DESC LIMIT " . rand(28, 50));
    $q->execute();
    $data2 = $q->get_result()->fetch_all(MYSQLI_ASSOC);

    $category1 = orderArray(array_merge($data1, $data2));

    $q = $db->prepare("SELECT *, probability * RAND() AS random FROM seeds WHERE `canBuy` = 1 AND `category` = 2 AND `level` <= 8");
    $q->execute();
    $data3 = $q->get_result()->fetch_all(MYSQLI_ASSOC);

    $q = $db->prepare("SELECT *, probability * RAND() AS random FROM seeds WHERE `canBuy` = 1 AND `category` = 2 AND `level` >= 9 ORDER BY random DESC LIMIT " . rand(9, 30));
    $q->execute();
    $data4 = $q->get_result()->fetch_all(MYSQLI_ASSOC);

    $category2 = orderArray(array_merge($data3, $data4));

    return array_merge($category1, $category2);
}

function orderArray($array, $plants = true) {
    $newArray = array();
    
    if(is_array($array) and count($array) > 0) {
        foreach($array as $key => $element) {
            if($plants) {
                $minLevel = $element['level'];

                if($minLevel < 10)
                $minLevel = '0' . $minLevel;

                $newArray[$minLevel . '_' . $key] = $element;
            }
            else {
                $minLevel = $element['minLevel'];

                if($minLevel < 10)
                $minLevel = '0' . $minLevel;

                $newArray[$minLevel . '_' . $key] = $element;
            }
        }
    }

    ksort($newArray);
    return $newArray;
}

function getPlants() {
    $plantTypes = getPlantStock();
    $xml = "";

    shuffle($plantTypes);

    if($plantTypes) {
        foreach($plantTypes as $singlePlant) {
            $id         = $singlePlant['id'];
            if (isset($singlePlant['random'])) {
                $random = $singlePlant['random'];
            }
            $name        = $singlePlant['name'];
            $filename    = $singlePlant['fileName'];
            $price       = $singlePlant['price'];
            $probability = $singlePlant['probability'];
            $category    = $singlePlant['category'];
            $growTime    = $singlePlant['growTime'];
            $cycleTime   = $singlePlant['cycleTime'];
            $mulchYield  = $singlePlant['mulchYield'];
            $xpYield     = $singlePlant['xpYield'];
            $radius      = $singlePlant['radius'];
            $minLevel    = $singlePlant['level'];
            $tycoonOnly  = $singlePlant['tycoon'];

            $xml .= "<seed id=\"$id\" $extraField cat=\"$category\" tyc=\"$tycoonOnly\" " .
            "level=\"$minLevel\" fileName=\"$filename\" name=\"$name\" " .
            "price=\"$price\" mulchYield=\"$mulchYield\" xpYield=\"$xpYield\" " .
            "growTime=\"$growTime\" cycleTime=\"$cycleTime\" prob=\"$probability\" />\n";
        }
    }

    return $xml;
}

function getPlants2() {
    $plantsByCategory = array(
        1 => array('level_1_8' => -1, 'level_9_plus' => 28),
        2 => array('level_1_8' => -1, 'level_9_plus' => 9)
    );

    $allPlants = GetBuyableSeeds();
    $xml = "";

    $randomPlants = array();
    $expandedPlants = array();

    foreach($allPlants as $plant) {
        for($i = 1; $i <= $plant['probability']; $i++) {
            $expandedPlants[] = $plant;
        }
    }

    shuffle($expandedPlants);

    foreach($plantsByCategory as $category => $numberOfItems) {
        $count = 0;

        foreach($expandedPlants as $plant) {
            if($plant['probability'] == 127 and $plant['level'] <= 8 and $plant['category'] == $category and !array_key_exists($plant['id'], $randomPlants)) {
                $randomPlants[$plant['id']] = $plant;
                $count++;
                continue;
            }

            if($count >= $numberOfItems['level_1_8'] and $numberOfItems['level_1_8'] != -1) continue;

            if($plant['level'] <= 8 and $plant['category'] == $category and !array_key_exists($plant['id'], $randomPlants)) {
                $randomPlants[$plant['id']] = $plant;
                $count++;
            }
        }

        $count = 0;

        foreach($expandedPlants as $plant) {
            if($plant['probability'] == 127 and $plant['level'] >= 9 and $plant['category'] == $category and !array_key_exists($plant['id'], $randomPlants)) {
                $randomPlants[$plant['id']] = $plant;
                $count++;
                continue;
            }

            if($count >= $numberOfItems['level_9_plus'] and $numberOfItems['level_9_plus'] != -1) continue;

            if($plant['level'] >= 9 and $plant['category'] == $category and !array_key_exists($plant['id'], $randomPlants)) {
                $randomPlants[$plant['id']] = $plant;
                $count++;
            }
        }
    }

    usort($randomPlants, array("Weevils_Models_Itemtype", "compareItems"));

    foreach($randomPlants as $singlePlant) {
        $extraField = '';
        $id         = $singlePlant['id'];
            if (isset($singlePlant['random'])) {
                $random = $singlePlant['random'];
            }
            $name        = $singlePlant['name'];
            $filename    = $singlePlant['fileName'];
            $price       = $singlePlant['price'];
            $probability = $singlePlant['probability'];
            $category    = $singlePlant['category'];
            $growTime    = $singlePlant['growTime'];
            $cycleTime   = $singlePlant['cycleTime'];
            $mulchYield  = $singlePlant['mulchYield'];
            $xpYield     = $singlePlant['xpYield'];
            $radius      = $singlePlant['radius'];
            $minLevel    = $singlePlant['level'];
            $tycoonOnly  = $singlePlant['tycoon'];

        $xml .= "<seed id=\"$id\" $extraField cat=\"$category\" tyc=\"$tycoonOnly\" " .
        "level=\"$minLevel\" fileName=\"$filename\" name=\"$name\" " .
        "price=\"$price\" mulchYield=\"$mulchYield\" xpYield=\"$xpYield\" " .
        "growTime=\"$growTime\" cycleTime=\"$cycleTime\" prob=\"$probability\" />\n";
    }

    return $xml;
}

function getItems2() {
    $itemsByLevel = array( //level => number of items
        1 => 99, // all level 1 items, or at least, 99 different variations
        2 => 3,
        3 => 2,
        4 => 2,
        5 => 2,
        6 => 2,
        7 => 2,
        8 => 2,
        9 => 8,
    );

    $xml = '';
    $allItems = GetBuyableGardenItems();

    $randomItems = array();
    $expandedItems = array();

    foreach($allItems as $item) {
        for($i = 1; $i <= $item['probability']; $i++) {
            $expandedItems[] = $item;
        }
    }

    shuffle($expandedItems);

    foreach($itemsByLevel as $level => $numberOfItems) {
        $count = 0;

        foreach($expandedItems as $item) {
            $index = $item['itemTypeID'] . $item['defaultHexcolour'];

            if($item['probability'] == 127 && !array_key_exists($index, $randomItems)) {
                $randomItems["$index"] = $item;
                $count++;
                continue;
            }

            if($count >= $numberOfItems) continue;

            if($level == 9) {
                if($item['minLevel'] >= $level and !array_key_exists($index, $randomItems)) {
                    $randomItems["$index"] = $item;
                    $count++;
                }
            }
            else if($item['minLevel'] == $level and !array_key_exists($index, $randomItems)) {
                $randomItems["$index"] = $item;
                $count++;
            }
        }
    }

    usort($randomItems, array("Weevils_Gardenshop_Helper", "compareItems"));

    foreach($randomItems as $item) {
        $itemTypeID       = $item['itemTypeID'];
            $category         = $item['category'];
            $configLocation   = $item['configLocation'];
            $paletteId        = $item['paletteId'];
            $price            = $item['price'];
            $probability      = $item['probability'];
            $name             = $item['name'];
            $description      = $item['description'];
            $deliveryTime     = '0';
            $expPoints        = $item['expPoints'];
            $powerConsumption = $item['powerConsumption'];
            $boundRadius      = $item['boundRadius'];
            $minLevel         = $item['minLevel'];
            $tycoonOnly       = $item['tycoonOnly'];
            $hexcolour        = ($item['defaultHexcolour'] == '' ? '-1' : $item['defaultHexcolour']);

            $xml .= "<item id=\"$itemTypeID\" tyc=\"$tycoonOnly\" level=\"$minLevel\" " .
                "xp=\"$expPoints\" fileName=\"$configLocation\" clr=\"$hexcolour\" " .
                "name=\"$name\" descr=\"$description\" dt=\"$deliveryTime\" " .
                "prob=\"$probability\" price=\"$price\" />\n";
    }

    return $xml;
}

function generateGardenShopXML($probabilityMethod = 2) {
    if($probabilityMethod == 1) {
        $xmlItems = getItems2();
        $xmlPlants = getPlants();
    }
    else {
        $xmlItems = getItems2();
        $xmlPlants = getPlants2();
    }

    $xml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
    $xml .= "<stock>\n";
    $xml .= $xmlItems;
    $xml .= $xmlPlants;
    $xml .= "</stock>";

    return $xml;
}

function generatePlantConfigXML($plants) {
    $str = "<plantConfigs weevilHappiness='100'>";

    foreach($plants as $plant) {
        $plantConfig = getSeedConfigById($plant['itemid']);
        $watered = ($plant['wateredUnix'] != "" ? 1 : 0);
        $growTime = ($plantConfig['growTime'] != "" ? intval($plantConfig['growTime']) : 0);
        $cycleTime = ($plantConfig['cycleTime'] != "" ? intval($plantConfig['cycleTime']) : 0);
        $growTimeSecs = ($growTime * 60);

        switch(intval($plantConfig['category'])) {
            case 1:
                $age = calculatePerishableAge($plant, $growTimeSecs);
                break;
            case 2:
                $age = calculateFruitingAge($plant, $growTimeSecs);
                break;
            default:
                $age = 0;
                break;
        }

        $ageInMins = 0;
        if($age) $ageInMins = round($age / 60);

        $str .= <<<EOT
<plant id="{$plant['id']}" name="{$plantConfig['name']}" cat="{$plantConfig['category']}" fName="{$plantConfig['fileName']}" age="{$ageInMins}" growTime="{$growTime}" cycleTime="{$cycleTime}" watered="{$watered}" x="{$plant['x']}" z="{$plant['z']}" r="{$plant['r']}" mulch="{$plantConfig['mulchYield']}" xp="{$plantConfig['xpYield']}" />
EOT;
    }

    $str .= '</plantConfigs>';
    return $str;
}

function calculatePerishableAge($plant, $growTimeInSeconds) {
    if($plant['wateredUnix'] == "") {
        $age = time() - intval($plant['plantedUnix']);
        return $age;
    }

    return time() - intval($plant['wateredUnix']) + $growTimeInSeconds;
}

function calculateFruitingAge($plant, $growTimeInSeconds) {
    if($plant['harvestUnix'] == "")
    return time() - $plant['plantedUnix'];

    return time() - intval($plant['harvestUnix']) + $growTimeInSeconds;
}

function hasBeenPlanted($plant) {
    return $plant['plantedUnix'] != "";
}

function canBeHarvested($plant, $plantConfig = null) {
    if(!hasBeenPlanted($plant)) return false;

    if($plantConfig == null) $plantConfig = getSeedConfigById($plant['itemid']);

    if($plantConfig['category'] == 1) {
        if(!hasFinishedGrowing($plant, $plantConfig)) return false;

        if(hasRotted($plant, $plantConfig)) return false;

        return true;
    }

    if(!hasBornFruit($plant, $plantConfig)) return false;

    return true;
}

function hasBornFruit($plant, $plantConfig) {
    if($plantConfig['category'] != 2) return false;

    return getFruitingAge($plant, $plantConfig) > getMaxMatureTime($plantConfig);
}

function hasRotted($plant, $plantConfig) {
    if($plantConfig['category'] != 1) return false;

    return getPerishableAge($plant, $plantConfig) > getMaxMatureTime($plantConfig);
}

function getMaxMatureTime($plantConfig) {
    return getGrowTime($plantConfig) + getCycleTime($plantConfig);
}

function getPerishableAge($plant, $plantConfig) {
    $age = time() - intval($plant['plantedUnix']);

    if($plant['wateredUnix'] != "")
    $age = time() - intval($plant['wateredUnix']) + getGrowTime($plantConfig);

    return $age;
}

function getFruitingAge($plant, $plantConfig) {
    $age = time() - intval($plant['plantedUnix']);

    if($plant['harvestUnix'] != "")
    $age = time() - intval($plant['harvestUnix']) + getGrowTime($plantConfig);

    return $age;
}

function getCycleTime($plantConfig) {
    return intval($plantConfig['cycleTime']) * 60;
}

function hasFinishedGrowing($plant, $plantConfig) {
    if(intval($plant['plantedUnix']) + getGrowTime($plantConfig) <= time()) return true;
    
    return false;
}

function getGrowTime($plantConfig) {
    return intval($plantConfig['growTime']) * 60;
}
/// Garden Functions

/// Brain Strain
function decodeLevels($string)
{
    $data = array();

    $arr = explode(',', $string);
    foreach($arr as $pair) {
        $split = explode('|', $pair);
        if (count($split) == 1) {
            $qType = $split[0];
            $level = 0;
        } else {
            list($qType, $level) = $split;
        }
        $data[intval($qType)] = intval($level);
    }
    return $data;
}
/// Brain Strain

/// Missions
function pivot(array $rows) {
    $columns = array();
    foreach ($rows as $row) {
        foreach ($row as $key => $value) {
            $columns[$key][] = $value;
        }
    }
    return $columns;
}
/// Missions

/// Apparel Shop
function getInXmlFormat(array $hats) {
    $xml = "<stock type=\"apparel\">\n";

    if(!empty($hats)) {
        foreach($hats as $key => $hat) {
            $id          = $hat['id'];
            $level       = $hat['minLevel'];
            $colour      = $hat['hexcolour'];
            $name        = $hat['name'];
            $description = $hat['description'];
            $probability = $hat['probability'];
            $price       = $hat['price'];
            $tyc         = $hat['tycoonOnly'];

            if (empty($colour)) {
                $colour = '0,0,0';
            }

            $xml .= "<item id=\"$id\" level=\"$level\" clr=\"$colour\" name=\"$name\" descr=\"$description\" prob=\"$probability\" price=\"$price\" tyc=\"$tyc\" />\n";
        }
    }

    $xml .= "</stock>\n";
    return $xml;
}
/// Apparel Shop
?>