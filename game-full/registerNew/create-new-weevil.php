<?php
error_reporting(0);
include('../essential/backbone.php');
include('../essential/BanBuilder/CensorWords.php');
include('../essential/ProfanityFilter/Check.php');

$bbcensor = new CensorWords();
$pfcensor = new Check();
$bbcensor->setDictionary(array(
    'cs',
    'de',
    'en-base',
    'en-uk',
    'en-us',
    'es',
    'fi',
    'fr',
    'it',
    'jp',
    'kr',
    'nl',
    'no'
));

$recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
$recaptcha_secret = '6LcvFZAaAAAAAJzGrFPQpDqVFCNxsBZtJYRzgaWQ';

function checkUserExists($username) {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	$q = $db->prepare("SELECT * FROM `users` WHERE `username` = ? LIMIT 1;");
	$q->bind_param('s', $username);
	$q->execute();
		
    $res = $q->get_result();

    if($res = $res->fetch_array())
    return true;
    else
    return false;
}

function checkReservedName($newName) {
    $reservedNames = array('asd', 'awk', 'baby_colin', 'babycolin', 'bam', 'big weevil', 'big_weevil', 'bigg', 'bigweevil', 'bing', 'bintestus', 'bipolarise', 'bitjockey', 'blem', 'bling', 'blott', 'bodge', 'bong', 'bongo', 'bosh', 'bott', 'bubbabin', 'bunt', 'bunty', 'castle_guard', 'castleguard', 'clem', 'clink', 'colin_the_dragon', 'colinthedragon', 'cram', 'cyclopsman', 'dab', 'dana22cc', 'devfive5', 'digg', 'ding', 'dip', 'dong', 'dosh', 'dott', 'dr_weevil', 'drweevil', 'fab', 'figg', 'fink', 'flam', 'flem', 'fling', 'flip', 'flum', 'fum', 'funt', 'gab', 'gam', 'garden_inspector', 'gardeninspector', 'gem', 'gene_simmons', 'glamm', 'glum', 'gnu', 'gnu2nd', 'gong', 'gosh', 'gott', 'green_weevil', 'greenweevil', 'grunt', 'gubbins', 'gubbins2', 'gum', 'ham', 'hem', 'hum', 'hunt', 'ink', 'jam', 'james', 'james2nd', 'jott', 'kalel', 'kalel2', 'kem', 'kip', 'kong', 'kong_fu', 'kongfu', 'kosh', 'lab', 'lady_wawa', 'ladywawa', 'lia', 'lip', 'maybee', 'maybee2', 'mem', 'moko1', 'mokoniji', 'monty', 'moorty', 'mr-pure', 'mudd', 'myke', 'nab', 'nemee', 'nest_inspector', 'nestinspector', 'ninouche', 'octeelia', 'oswaldie', 'pab', 'pink', 'pong', 'posh', 'prigg', 'punt', 'ram', 'recluse', 'redcoat', 'rigg', 'ring', 'rip', 'roots', 'roots2', 'rott', 'rss', 'rum', 'runt', 'sanyojan', 'sanyojan2', 'scram', 'scribbles', 'seenoz', 'sethsalt', 'shem', 'ship', 'sigg', 'sing', 'sink', 'sip', 'slam', 'sling', 'slosh', 'slum', 'snappy', 'song', 'spot', 'spring', 'stanweevil', 'stephwoolley', 'stink', 'stunt', 'sum', 'superalitos', 'superalitos2', 'tab', 'teacup', 'tevil', 'the maker', 'the maker2', 'the_recluse', 'thedsad', 'therecluse', 'thing', 'thong', 'thugg', 'tigg', 'times', 'times2', 'tip', 'toddrivers', 'tong', 'trem', 'trickeyd', 'trickeydee', 'trickster77', 'trigg', 'tum', 'twigg', 'usa', 'videoweev', 'vidweev', 'weevil_x', 'weevilx', 'wigg', 'wink', 'zing', 'zip', 'bing','bling','fling','sling','thing','zing','dosh','bosh','gosh','kosh','posh','slosh','blem','flem','gem','hem','kem','mem','flam','gam','ham','jam','ram','slam','bongo','gong','kong fu','pong','song','tong','fink','ink','pink','sink','wink','dip','flip','kip','ship','sip','zip','big weevil','big_weevil','big-weevil','colin','garden inspector','nest inspector','nest_inspector','garden_inspector','nest-inspector','garden-inspector','scribbles','scribles','snappy','bunty','grunt','hunt','punt','runt','stunt','digg','figg','rigg','trigg','twigg','wigg','blott','dott','gott','jott','rott','dab','fab','gab','lab','nab','tab','fum','glum','slum','sum','tum','flum','octeelia','thugg','thug','weevil x','weevil-x','weevil_x','the maker','the_maker','the-maker');
    $newName = strtolower(trim($newName));
    return in_array($newName, $reservedNames);
}

function createWeevil($username, $password) {
    $sessKey = generateSessionKey();
    $logKey = generateLogKey();
    $timestamp = time();

    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	$q = $db->prepare("INSERT INTO `users` (`username`, `password`, `sessionKey`, `loginKey`, `lastLogin`, `createdAt`) VALUES (?, ?, ?, ?, ?, ?)");
	$q->bind_param('ssssss', $username, scramblePassword($username, $password), $sessKey, $logKey, $timestamp, $timestamp);
	$q->execute();

    if($q->affected_rows == 1) {
        createBuddyListForWeevil($username);

        setcookie("sessionId", $sessKey, time() + 86400, '/'); // Eh ... we'll leave it for now ...
        setcookie("weevil_name", $username, time() + 86400, '/');
                            
        header('Location: ../game.php');

        return "responseCode=1";
    }

    return "responseCode=2";
}

if(isset($_POST['userID']) && isset($_POST['password']) && isset($_POST['recap'])) {
    $username = $_POST['userID'];
    $password = $_POST['password'];
    $recap = $_POST['recap'];

    if(!empty($username) && !empty($password) && !empty($recap)) {
        if(!$pfcensor->hasProfanity($username) && strpos($bbcensor->censorString($username, true)['clean'], '*') === false && preg_match('/^(?=[a-zA-Z]{2})(?=.{3,16})[\w -]+$/iD', $username) && !preg_match('/([a-z A-Z]+\w)\1+$/', $username) && strlen($username) <= 16
        && preg_match_all('/[0-9]/', $username) <= 4 && preg_match_all('/-/', $username) <= 2 && preg_match_all('/_/', $username) <= 2 && preg_match('/^\S.*\S$/', $username) && substr_count($username, ' ') <= 2) {
            // valid username
            if(checkReservedName($username) || checkUserExists($username) || strlen($username) > 16 || strlen($username) < 3) {
                echo 'responseCode=3';
                return;
            }

            $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recap);
            $recaptcha = json_decode($recaptcha);

            if($recaptcha->success)
            echo createWeevil($username, $password);
            else
            echo 'responseCode=2';
        }
        else
        echo 'responseCode=2';
    }
    else
    echo 'responseCode=999';
}
else
echo 'responseCode=999';
?>