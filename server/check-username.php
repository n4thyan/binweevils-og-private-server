<?php
error_reporting(0);
include('BanBuilder/CensorWords.php');
include('ProfanityFilter/Check.php');

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

if(isset($_GET)) {
    $username = $_GET['username'];

    if(!$pfcensor->hasProfanity($username) && strpos($bbcensor->censorString($username, true)['clean'], '*') === false && preg_match('/^(?=[a-zA-Z]{2})(?=.{3,16})[\w -]+$/iD', $username) && !preg_match('/([a-z A-Z]+\w)\1+$/', $username) && strlen($username) <= 16
    && preg_match_all('/[0-9]/', $username) <= 4 && preg_match_all('/-/', $username) <= 2 && preg_match_all('/_/', $username) <= 2) {
        echo 'valid username';
    }
    else echo 'invalid username';
}

?>