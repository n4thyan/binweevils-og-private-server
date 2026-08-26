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

if(isset($_POST)) {
    $roomId = $_POST['locID'];
    $text = $_POST['busName'];

    if(!$pfcensor->hasProfanity($text) && strpos($bbcensor->censorString($text, true)['clean'], '*') === false && preg_match('/^(?=[a-zA-Z]{2})(?=.{2,20})[\w -.!?]+$/iD', $text) && strlen($text) <= 20
    && preg_match_all('/_/', $text) == 0) {
        if(SaveBusText($text, $roomId))
        echo 'res=2';
        else echo 'res=3';
    }
    else echo 'res=3';
}
else echo 'res=3';
?>