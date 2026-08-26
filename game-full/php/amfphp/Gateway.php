<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
include 'vendor/autoload.php';
include 'Requests.php';
include 'Database.php';
#include 'DataStructures/jpegencoder.JPEGEncoder.saveFramedPic/saveFramedPic.php';

function autoload($path ) {
    $items = glob($path . DIRECTORY_SEPARATOR . "*");

    foreach($items as $item) {
        $isPhp = pathinfo($item )["extension"] === "php";
        if (is_file($item) && $isPhp) {
            require_once $item;
        } elseif (is_dir($item)){
            autoload($item);
        }
    }
}
autoload(dirname(__FILE__).DIRECTORY_SEPARATOR."DataStructures");

$db = new Database();
$server = new SabreAMF_Server();
http_response_code(200);
header('Content-Type: application/x-amf');
foreach ($server->getRequests() as $request)
{
    //$server->addHeader("RequestPersistentHeader", true, new DSId());
    $target = $request["target"];
    if(isset($targets[$target])) {
        $targetInfo = $targets[$target];
        $response = $targetInfo['type'] == 'class' ? new $targetInfo['method']($db, $request['data']) : $targetInfo['method']($db, $request['data']);
        if($targetInfo['type'] == 'class' && isset($response->AMF_CLASSNAME)) {
            $response = new SabreAMF_TypedObject($response->AMF_CLASSNAME, $response);
        }
        else if($targetInfo['type'] == 'plaintext'){
            echo $response;
            return;
        }
        $server->setResponse($request['response'], SabreAMF_Const::R_RESULT, $response);
        $server->sendResponse();
    } else {
        die("NEW UNKNOWN METHOD");
    }
}

// Coded by Darkk