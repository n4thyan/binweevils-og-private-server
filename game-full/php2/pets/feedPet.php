<?php
error_reporting(0);
include('../../essential/backbone.php');

// Feed / care for a Bin Pet: bumps fuel/health/fitness/experience on a pet the
// weevil owns. Rate-limited per Checkpoint D. Auth mirrors getPetSkills.php.
if(isset($_POST)) {
    $hash = $_POST['hash'];
    $idx = $_POST['idx'];
    $petID = isset($_POST['petID']) ? intval($_POST['petID']) : 0;
    $timer = $_POST['timer'];
    $action = isset($_POST['action']) ? $_POST['action'] : 'feed'; // feed | play | train

    $weevilData = getAllWeevilStatsByName($_COOKIE['weevil_name']);

    if($weevilData != null && $weevilData["id"] == $idx) {
        if(checkHash(["hash" => $hash, "idx" => $idx, "petID" => $petID, "timer" => $timer])
           && $weevilData['username'] == $_COOKIE['weevil_name']) {

            // Checkpoint D: throttle pet-care actions (e.g. 20/min/IP).
            if(!rateLimit('care-pet', 20, 60)) {
                echo 'res=429';
                exit;
            }

            $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

            // Confirm ownership before any mutation.
            $own = $db->prepare("SELECT id, fuel, health, fitness, experience FROM pets WHERE id = ? AND ownerID = ?");
            $own->bind_param('is', $petID, $weevilData['username']);
            $own->execute();
            $pet = $own->get_result()->fetch_array();
            if(!$pet) { echo 'res=998'; exit; }

            // Apply the action, clamped to sane bounds (0..100 for bars, exp uncapped).
            $fuel = intval($pet['fuel']); $health = intval($pet['health']);
            $fitness = intval($pet['fitness']); $xp = intval($pet['experience']);
            if($action == 'train') { $fitness = min(100, $fitness + 5); $xp += 5; }
            else if($action == 'play') { $health = min(100, $health + 5); $xp += 3; }
            else { $fuel = min(100, $fuel + 10); $health = min(100, $health + 2); $xp += 1; }

            $upd = $db->prepare("UPDATE pets SET fuel = ?, health = ?, fitness = ?, experience = ? WHERE id = ? AND ownerID = ?");
            $upd->bind_param('iiiiis', $fuel, $health, $fitness, $xp, $petID, $weevilData['username']);
            $upd->execute();

            if($upd->affected_rows == 1)
                echo 'responseCode=1&fuel=' . $fuel . '&health=' . $health . '&fitness=' . $fitness . '&experience=' . $xp;
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
