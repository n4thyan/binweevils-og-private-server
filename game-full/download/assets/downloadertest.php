<?php

$url = "https://www.binweevils.com/assets/img/blog/navbar/medium/nav-membership2.png";

$opts = array('http'=>array('header' => "User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.121 Safari/537.36 OPR/71.0.3770.287\r\n")); 
//Basically adding headers to the request
$context = stream_context_create($opts);
$html = file_get_contents($url,false,$context);
$headers = get_headers($url, true);

header($headers["Content-Type"]);

file_put_contents("nav-membership2.png", $html);

echo $html;

$myfile = fopen("test.txt", "w") or die("Unable to open file!");
$txt = $url;
fwrite($myfile, $url);
fclose($myfile);
?>