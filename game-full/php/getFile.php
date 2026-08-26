<?php
exit;
$file =  $_REQUEST['file'];
$fileName = $_SERVER['DOCUMENT_ROOT'] . $file;
error_log('getFile.php - '.__FILE__.': '.$fileName);
$xml_string = file_get_contents($fileName,"rb");
echo $xml_string;
