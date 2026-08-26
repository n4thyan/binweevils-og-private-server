<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $codes = getRedeemableCodes();
    $codeArray = '';
    foreach($codes as $code){
        $codeArray .= $code[1].',';
    }
    echo 'responseCode=1&codes='.md5(substr($codeArray, 0, -1));
}
else echo 'responseCode=999';

?>