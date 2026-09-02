<?php
error_reporting(0);
include('../../essential/backbone.php');

// updatePetStats.php — called by the petBuilder/SWF to persist a Bin Pet's live stats
// (fuel/health/fitness/experience). Mirrors feedPet.php's ownership-checked UPDATE.
// The SWF expects a responseCode=1 line with the updated values (or responseCode=999).
if(isset($_POST)) {
    $hash = isset($_POST['hash']) ? $_POST['hash'] : '';
    $idx  = isset($_POST['idx']) ? intval($_POST['idx']) : 0;
    $petID = isset($_POST['petID']) ? intval($_POST['petID']) : 0;
    $timer = isset($_POST['timer']) ? $_POST['timer'] : '';

    $weevilData = getAllWeevilStatsByName($_COOKIE['weevil_name']);

    if($weevilData != null && $weevilData['id'] == $idx) {
        if(checkHash(['hash' => $hash, 'idx' => $idx, 'petID' => $petID, 'timer' => $timer])
           && $weevilData['username'] == $_COOKIE['weevil_name']) {

            if(!rateLimit('update-pet-stats', 20, 60)) {
                echo 'res=429';
                exit;
            }

            $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

            // Confirm ownership before mutating.
            $own = $db->prepare('SELECT id, fuel, health, fitness, experience FROM pets WHERE id = ? AND ownerID = ?');
            $own->bind_param('is', $petID, $weevilData['username']);
            $own->execute();
            $pet = $own->get_result()->fetch_array();
            if(!$pet) { echo 'res=998'; exit; }

            // Accept posted values if present, clamped to sane bounds.
            $fuel     = isset($_POST['fuel'])         ? max(0, min(100, intval($_POST['fuel'])))         : intval($pet['fuel']);
            $energy   = isset($_POST['mentalEnergy'])  ? max(0, min(100, intval($_POST['mentalEnergy']))) : intval($pet['mentalEnergy']);
            $fitness  = isset($_POST['fitness'])       ? max(0, min(100, intval($_POST['fitness'])))      : intval($pet['fitness']);
            $xp       = isset($_POST['experience'])    ? intval($_POST['experience'])                      : intval($pet['experience']);

            $upd = $db->prepare('UPDATE pets SET fuel = ?, mentalEnergy = ?, fitness = ?, experience = ? WHERE id = ? AND ownerID = ?');
            $upd->bind_param('iiiiis', $fuel, $energy, $fitness, $xp, $petID, $weevilData['username']);
            $upd->execute();

            if($upd->affected_rows >= 0)
                echo 'responseCode=1&fuel=' . $fuel . '&mentalEnergy=' . $energy . '&fitness=' . $fitness . '&experience=' . $xp;
            else
                echo 'res=999';
        }
        else
        echo 'responseCode=999';
    }
    else
    echo 'responseCode=999';
}
else
echo 'res=999';
?>
