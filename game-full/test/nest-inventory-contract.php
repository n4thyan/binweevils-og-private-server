<?php
require_once(__DIR__ . '/../essential/backbone.php');
error_reporting(E_ERROR);
$name='InventoryContract0908'; $session='abcdef0123456789abcdef0123456789';
$db=new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
$db->query("DELETE wi FROM weevilitems wi JOIN users u ON u.id=wi.weevilID WHERE u.username='$name'");
$db->query("DELETE FROM users WHERE username='$name'");
try {
    $q=$db->prepare("INSERT INTO users (username,password,sessionKey,loginKey,lastLogin,active) VALUES (?,'x',?,'abcde',NOW(),1)");
    $q->bind_param('ss',$name,$session); $q->execute(); $id=intval($q->insert_id);
    $fixtures=[[2634,16759552,993,'f_petBasket2',1],[2625,0,993,'f_petBowl_blue',1],[1986,0,993,'o_levelTrophy2',1],[1,0,9,'f_castlegam_slimefall',0]];
    $ins=$db->prepare('INSERT INTO weevilitems (weevilID,itemId,colour,category,isInRoom,configName,roomId,position,fID,spot,internalCategory) VALUES (?,?,?,?,0,?,0,1,0,0,?)');
    foreach($fixtures as $f) { $ins->bind_param('iiiisi',$id,$f[0],$f[1],$f[2],$f[3],$f[4]); $ins->execute(); }
    $opts=['http'=>['method'=>'POST','header'=>"Content-Type: application/x-www-form-urlencoded\r\nCookie: weevil_name=$name; sessionId=$session\r\n",'content'=>http_build_query(['idx'=>$id]),'timeout'=>10]];
    $raw=file_get_contents('http://localhost/php2/nest/getStoredItems.php?rndVar=contract',false,stream_context_create($opts));
    $data=json_decode($raw,true);
    if(($data['responseCode']??0)!==1) throw new RuntimeException("bad response: $raw");
    $names=array_column($data['items'],'configName');
    foreach(array_column($fixtures,3) as $required) if(!in_array($required,$names,true)) throw new RuntimeException("missing $required: $raw");
    echo "INVENTORY CONTRACT PASS: ".implode(', ',$names)."\n";
} finally {
    $db->query("DELETE wi FROM weevilitems wi JOIN users u ON u.id=wi.weevilID WHERE u.username='$name'");
    $db->query("DELETE FROM users WHERE username='$name'");
}
