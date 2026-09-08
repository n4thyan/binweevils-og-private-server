<?php
// Integration regression for authoritative XP award -> reconciliation.
// Uses one throwaway local user and removes every generated row in finally.
require_once(__DIR__ . '/../essential/backbone.php');
error_reporting(E_ERROR);
ini_set('display_errors', '1');
register_shutdown_function(function() {
    $e = error_get_last();
    if($e && in_array($e['type'], [E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR], true)) {
        fwrite(STDERR, "PHP SHUTDOWN: {$e['message']} at {$e['file']}:{$e['line']}\n");
    }
});

$name = 'XpContract0908';
$session = '0123456789abcdef0123456789abcdef';
$_COOKIE['weevil_name'] = $name;
$_COOKIE['sessionId'] = $session;
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

function wipeXpFixture(mysqli $db, string $name): void {
    $id = intval($db->query("SELECT id FROM users WHERE username='" . $db->real_escape_string($name) . "'")->fetch_assoc()['id'] ?? 0);
    if($id) {
        $db->query("DELETE FROM achievement_activity WHERE userID=$id");
        $db->query("DELETE FROM achievementscompleted WHERE idx=$id");
        $db->query("DELETE FROM prestige_trophies WHERE weevil_id=$id");
        $db->query("DELETE FROM buddyalerts WHERE receiver='" . $db->real_escape_string($name) . "'");
        $db->query("DELETE FROM weevilitems WHERE weevilID=$id");
    }
    $db->query("DELETE FROM users WHERE username='" . $db->real_escape_string($name) . "'");
}
function state(mysqli $db, string $name): array {
    return $db->query("SELECT level,xp,xp1,xp2,prestige_count FROM users WHERE username='" . $db->real_escape_string($name) . "'")->fetch_assoc();
}
function expectState(mysqli $db, string $name, array $expected, string $label): void {
    $actual = state($db, $name);
    foreach($expected as $key => $value) {
        if(intval($actual[$key]) !== intval($value)) throw new RuntimeException("$label: expected $key=$value, got {$actual[$key]}");
    }
    echo "PASS $label\n";
}
function resetProgress(mysqli $db, int $level=1, int $xp=0, int $xp1=0, int $xp2=30, int $prestige=0): void {
    global $name;
    $q=$db->prepare('UPDATE users SET level=?,xp=?,xp1=?,xp2=?,prestige_count=?,prestige_xp_base=0 WHERE username=?');
    $q->bind_param('iiiiis',$level,$xp,$xp1,$xp2,$prestige,$name); $q->execute();
    $id=intval($db->query("SELECT id FROM users WHERE username='$name'")->fetch_assoc()['id']);
    $db->query("DELETE FROM prestige_trophies WHERE weevil_id=$id");
    $db->query("DELETE FROM weevilitems WHERE weevilID=$id");
    $db->query("DELETE FROM achievement_activity WHERE userID=$id");
}
function awardAndExpect(mysqli $db, int $id, int $award, array $expected, string $label): void {
    global $name;
    if(!addExperience($id,$award)) throw new RuntimeException("$label: award failed");
    expectState($db,$name,$expected,$label);
}

wipeXpFixture($db,$name);
try {
    $q=$db->prepare("INSERT INTO users (username,password,sessionKey,loginKey,mulch,dosh,lastLogin,active,level,xp,xp1,xp2,prestige_count,prestige_xp_base) VALUES (?,'x',?,'abcde',0,0,NOW(),1,1,0,0,30,0,0)");
    $q->bind_param('ss',$name,$session); $q->execute(); $id=intval($q->insert_id);
    // Isolate progression from legitimate trophy-collection achievement rewards.
    $db->query("INSERT IGNORE INTO achievementscompleted (idx,achievementId) VALUES ($id,64),($id,65),($id,66),($id,67),($id,68),($id,140),($id,141),($id,142),($id,143),($id,144)");

    awardAndExpect($db,$id,29,['level'=>1,'xp'=>29,'xp1'=>29,'xp2'=>30,'prestige_count'=>0],'L1 + 29');
    awardAndExpect($db,$id,1,['level'=>2,'xp'=>30,'xp1'=>0,'xp2'=>60,'prestige_count'=>0],'+1 reaches L2');
    resetProgress($db);
    awardAndExpect($db,$id,50,['level'=>2,'xp'=>50,'xp1'=>20,'xp2'=>60,'prestige_count'=>0],'fresh L1 + 50 overflow');
    resetProgress($db);
    awardAndExpect($db,$id,100,['level'=>3,'xp'=>100,'xp1'=>10,'xp2'=>90,'prestige_count'=>0],'fresh L1 + 100');
    resetProgress($db);
    awardAndExpect($db,$id,1000,['level'=>6,'xp'=>1000,'xp1'=>370,'xp2'=>500,'prestige_count'=>0],'multi-level grant');

    resetProgress($db,79,0,0,2840000,0);
    awardAndExpect($db,$id,2840000,['level'=>1,'xp'=>2840000,'xp1'=>0,'xp2'=>45,'prestige_count'=>1],'P0 L79 exact to P1');
    resetProgress($db,79,0,0,2840000,0);
    awardAndExpect($db,$id,2840050,['level'=>2,'xp'=>2840050,'xp1'=>5,'xp2'=>90,'prestige_count'=>1],'P0 L79 through P1 overflow');

    resetProgress($db);
    awardAndExpect($db,$id,62292780,['level'=>2,'xp'=>62292780,'xp1'=>20,'xp2'=>120,'prestige_count'=>2],'cross-prestige large grant');
    resetProgress($db,80,0,123,2840000,13);
    awardAndExpect($db,$id,3000000,['level'=>80,'xp'=>3000000,'xp1'=>3000123,'xp2'=>2840000,'prestige_count'=>13],'P13 L80 cap');

    resetProgress($db);
    awardAndExpect($db,$id,30,['level'=>2,'xp'=>30,'xp1'=>0,'xp2'=>60,'prestige_count'=>0],'trophy award baseline');
    $before=intval($db->query("SELECT COUNT(*) c FROM weevilitems WHERE weevilID=$id AND configName LIKE 'o_levelTrophy%'")->fetch_assoc()['c']);
    if(!levelWeevil($name)) throw new RuntimeException('idempotent reconcile returned false');
    $after=intval($db->query("SELECT COUNT(*) c FROM weevilitems WHERE weevilID=$id AND configName LIKE 'o_levelTrophy%'")->fetch_assoc()['c']);
    if($before !== $after) throw new RuntimeException("duplicate trophy rows: before=$before after=$after");
    echo "PASS zero duplicate trophies\nXP MATRIX PASS\n";
} finally {
    wipeXpFixture($db,$name);
}
