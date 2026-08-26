<?php
error_reporting(-1);
ini_set('display_errors', 'On');
$uri = $_GET['f'];
$pathToDownload = str_replace("/cdn.binw.net/", "", $uri);
$url = "http://cdn.binw.net/$pathToDownload";
//echo $pathToDownload;


$opts = array('http'=>array('header' => "User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.121 Safari/537.36 OPR/71.0.3770.287\r\n")); 
//Basically adding headers to the request
$context = stream_context_create($opts);
$html = file_get_contents($url,false,$context);
$headers = get_headers($url, true);


header($headers["Content-Type"]);
$dir = __DIR__ .'/';
$directory = dirname('/var/www/play/cdn.binw.net/'.$pathToDownload);

//echo $directory;
if(!is_dir($directory)) {
    mkdir($directory, 0777, true);
}

file_put_contents('/var/www/play/cdn.binw.net/'.$pathToDownload, $html);

$myfile = fopen("test.txt", "w") or die("Unable to open file!");
$txt = $url;
fwrite($myfile, $url);
fclose($myfile);
echo $html;



?>