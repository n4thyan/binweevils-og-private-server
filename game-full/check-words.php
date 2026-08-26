<?php

$word = $_GET['w'];
$res = file_get_contents('https://www.purgomalum.com/service/containsprofanity?text='.$word);
echo $res;

?>