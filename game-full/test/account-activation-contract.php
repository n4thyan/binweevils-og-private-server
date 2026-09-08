<?php
require_once(__DIR__ . '/../essential/backbone.php');
error_reporting(E_ERROR);
$name='ActivationContract0908'; $session='fedcba9876543210fedcba9876543210';
$db=new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
function activationCall($name,$session,$id,$email) {
    $opts=['http'=>['method'=>'POST','header'=>"Content-Type: application/x-www-form-urlencoded\r\nCookie: weevil_name=$name; sessionId=$session\r\n",'content'=>http_build_query(['idx'=>$id,'email'=>$email,'timer'=>123,'hash'=>'contract']),'timeout'=>10]];
    return file_get_contents('http://localhost/php2/registration/activateAccountWithEmail.php?rndVar=contract',false,stream_context_create($opts));
}
function activationCleanup(mysqli $db,$name) {
    $id=intval($db->query("SELECT id FROM users WHERE username='$name'")->fetch_assoc()['id']??0);
    if($id){$db->query("DELETE FROM achievement_activity WHERE userID=$id");$db->query("DELETE FROM achievementscompleted WHERE idx=$id");$db->query("DELETE FROM prestige_trophies WHERE weevil_id=$id");$db->query("DELETE FROM buddyalerts WHERE receiver='$name'");$db->query("DELETE FROM weevilitems WHERE weevilID=$id");}
    $db->query("DELETE FROM users WHERE username='$name'");
}
activationCleanup($db,$name);
try {
    $q=$db->prepare("INSERT INTO users (username,password,sessionKey,loginKey,mulch,dosh,lastLogin,active,level,xp,xp1,xp2,prestige_count,activated,email) VALUES (?,'x',?,'abcde',11,7,NOW(),1,1,0,0,30,0,0,'')");
    $q->bind_param('ss',$name,$session);$q->execute();$id=intval($q->insert_id);
    $db->query("INSERT IGNORE INTO achievementscompleted (idx,achievementId) VALUES ($id,1),($id,64),($id,65),($id,66),($id,67),($id,68),($id,140),($id,141),($id,142),($id,143),($id,144)");
    foreach(['not-an-email','user@gmail.fake.com','user@example.com'] as $bad) if(activationCall($name,$session,$id,$bad)!=='responseCode=4') throw new RuntimeException("accepted $bad");
    $before=$db->query("SELECT activated,email,mulch,dosh,xp,xp1,level,xp2 FROM users WHERE id=$id")->fetch_assoc();
    if(array_map('strval',$before)!==array_map('strval',['activated'=>0,'email'=>'','mulch'=>11,'dosh'=>7,'xp'=>0,'xp1'=>0,'level'=>1,'xp2'=>30])) throw new RuntimeException('invalid requests mutated account');
    if(activationCall($name,$session,$id,'  Runner.Contract@GMAIL.COM ')!=='responseCode=1') throw new RuntimeException('allowed provider failed');
    $after=$db->query("SELECT activated,email,mulch,dosh,xp,xp1,level,xp2 FROM users WHERE id=$id")->fetch_assoc();
    $expected=['activated'=>1,'email'=>'runner.contract@gmail.com','mulch'=>3211,'dosh'=>32,'xp'=>100,'xp1'=>10,'level'=>3,'xp2'=>90];
    if(array_map('strval',$after)!==array_map('strval',$expected)) throw new RuntimeException('wrong activation state: '.json_encode($after));
    if(activationCall($name,$session,$id,'changed@yahoo.com')!=='responseCode=1') throw new RuntimeException('replay failed');
    $replay=$db->query("SELECT activated,email,mulch,dosh,xp,xp1,level,xp2 FROM users WHERE id=$id")->fetch_assoc();
    if($after!=$replay) throw new RuntimeException('replay issued reward or changed email');
    echo "ACTIVATION CONTRACT PASS\n";
} finally { activationCleanup($db,$name); }
