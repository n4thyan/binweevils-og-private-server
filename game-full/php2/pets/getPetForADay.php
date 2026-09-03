<?php
error_reporting(0);
include('../../essential/backbone.php');

if(isset($_POST)) {
    $hash = $_POST['hash'];
    $timer = $_POST['timer'];
    $idx = $_POST['idx'];
    $petName = strtolower($_POST['petName']);
    $bodyColour = intval($_POST['bc']);
    $armColour1 = 2631720; // black arms
    $armColour2 = 2631720;
    $eyeColour1 = $bodyColour; // needs to be same as body colour
    $eyeColour2 = $bodyColour;

    $allowedPetNames = array("alfie", "archie", "barney", "benny", "billy", "bingo", "binny", "bobo", "bolt", "boo", "bouncy", "bubbles", "buddy", "buster", "buzz", "candy", "charlie", "cheeky", "cleo", "coco", "crash", "cuddles", "cupcake", "dash", "dexter", "dizzy", "dolly", "fang", "felix", "fifi", "fizzy", "flash", "fluffy", "frankie", "george", "giggles", "gizmo", "gogo", "happy", "henry", "iggy", "izzy", "jack", "jazzy", "jimmy", "jojo", "kiki", "lilly", "lizzy", "lolly", "lottie", "lucky", "lulu", "max", "mikey", "milo", "mimi", "missy", "muffin", "nibbles", "ninja", "ollie", "oscar", "patch", "penny", "pinky", "pip", "pixie", "polly", "poppy", "puffy", "rainbow", "rocket", "rocky", "sasha", "shadow", "simon", "smiley", "snuffles", "snuggles", "sonic", "sparkle", "sparky", "speedy", "spike", "spinny", "spot", "spud", "star", "sugar", "sunny", "tango", "teddy", "tiger", "tiny", "trixie", "violet", "yoyo", "zap", "zoey");
    $allowedColours = array(26367, 43520, 8913032, 11198463, 15597568, 16745604, 16750848, 16763904);

    $bowlItemId = 38; // green
    $bedItemId = 32;
    $bedColour = 16776960; // yellow

    if(!checkHash(["hash" => $hash, "idx" => $idx, "petName" => $petName, "bc" => $bodyColour, "ac1" => $armColour1, "ac2" => $armColour2, "ec1" => $eyeColour1, "ec2" => $eyeColour2, "timer" => $timer])) {
        echo 'responseCode=999';
        exit;
    }

    $weevilStats = getAllWeevilStats($idx);
    if($weevilStats['username'] != $_COOKIE['weevil_name']) {
        echo 'responseCode=999';
        exit;
    }

    $userPets = getUserPets();
    if($userPets) {
        echo 'responseCode=2';
        exit;
    }

    if(!in_array($petName, $allowedPetNames) || !in_array($bodyColour, $allowedColours)) {
        echo 'responseCode=3';
        exit;
    }

    $bedID = rewardItem($weevilStats['id'], $bedItemId, $bedColour, true);
    $bowlID = rewardItem($weevilStats['id'], $bowlItemId, 0, true);

    if($bedID && $bowlID) {
        list($petID, $hasRentedPet) = buyPet($weevilStats['username'], $petName, $bedID, $bowlID, $bodyColour, $armColour1, $armColour2, $eyeColour1, $eyeColour2, 1);

        if($petID) {
            $skillsInserted = insertPetSkills($weevilStats['username'], $petID, 1);
            $tricksInserted = insertPetJugglingTricks($weevilStats['username'], $petID);

            if($skillsInserted && $tricksInserted)
            echo 'responseCode=1';
            else echo 'responseCode=996';
        }
        else echo 'responseCode=997';
    }
    else echo 'responseCode=998';
}
else echo 'responseCode=999';