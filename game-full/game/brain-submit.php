<?php
error_reporting(0);
include('../essential/backbone.php');

if(isset($_POST) && isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
    $score = $_POST['score'];
    $levels = $_POST['levels'];
    $hash = $_POST['hash'];
    $st = $_POST['st'];

    try {
        $weevilData = getAllWeevilStatsByName($_COOKIE['weevil_name']);

        if($weevilData != null) {
            $mulchEarned  = 0;
            $xpEarned     = 0;
            $result       = 1; // default

            if(checkHash(["hash" => $hash, "score" => $score, "levels" => $levels, "st" => $st])) {
                $game = getGameData(4);

                if($game != null && $game["isLive"] == 1) {
                    if(intval($score) > $game["maxScore"] || intval($score) < 0 || intval($score) == 3000) {
                        GrantBan(strtotime('+1 day', time()), $weevilData["id"], "brain-submit");
                        echo 'res=999&message=exploiting detected.';
                        return;
                    }

                    $userGameData = getSinglePlayerUserData($weevilData["id"], 4);

                    if($userGameData == null) {
                        $updatedGameData = setSinglePlayerUserData($weevilData["id"], 4, $score, strval(time()));
                        $result = 1;
                        $mulchEarned = round($score * $game["mulchFactor"]) + $game["minMulch"];
                        $xpEarned = round($score * $game["xpFactor"]) + $game["minXp"];

                        addMulchByName($_COOKIE['weevil_name'], $mulchEarned);
                        addExperienceByName($_COOKIE['weevil_name'], $xpEarned);
                        $levels = updateStrainLevels($weevilData["id"], $levels);

                        $achCsv = '0';
                        $achDb3 = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                        $achDb3->begin_transaction();
                        try {
                            $svc3 = new AchievementService((int)$weevilData["id"], $_COOKIE['weevil_name'], $achDb3);

                            // Maintain brainstrain_stats cumulative total BEFORE evaluating.
                            // NOT users.mulch wallet (spending changes it). NOT achievement_activity SUM.
                            $cumulativeDb = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                            $cumulativeDb->begin_transaction();
                            $cumulativeDb->query(
                                "INSERT INTO brainstrain_stats (userID, totalMulchEarned, totalPlays, last_played) VALUES (" . (int)$weevilData["id"] . ", " . (int)$mulchEarned . ", 1, '" . date('Y-m-d') . "') ON DUPLICATE KEY UPDATE totalMulchEarned = totalMulchEarned + " . (int)$mulchEarned . ", totalPlays = totalPlays + 1, last_played = '" . date('Y-m-d') . "'"
                            );
                            $cumulativeDb->commit();
                            $cumulativeDb->close();

                            $svc3->recordActivity('brain_strain_earn', 4, (int)$mulchEarned, null, true);
                            $newIds3 = $svc3->evaluateForActivity('brain_strain_earn', 4, (int)$mulchEarned, null);
                            $achDb3->commit();
                            $achCsv = $newIds3 ? implode(',', $newIds3) : '0';
                        } catch (Throwable $e) {
                            $achDb3->rollback();
                            $achCsv = '0';
                        }
                        $achDb3->close();
                    }
                    else {
                        $now = date('Y-m-d');
                        $nextDay = date('Y-m-d', strtotime('+1 day', $userGameData["last_played"]));

                        if($nextDay > $now) {
                            $result = 3;
                            $achCsv = '0';
                        }
                        else {
                            $updatedGameData = setSinglePlayerUserData($weevilData["id"], 4, $score, strval(time()));
                            $result = 1;
                            $mulchEarned = round($score * $game["mulchFactor"]) + $game["minMulch"];
                            $xpEarned = round($score * $game["xpFactor"]) + $game["minXp"];

                            addMulchByName($_COOKIE['weevil_name'], $mulchEarned);
                            addExperienceByName($_COOKIE['weevil_name'], $xpEarned);
                            $levels = updateStrainLevels($weevilData["id"], $levels);

                            $achCsv = '0';
                            $achDb4 = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                            $achDb4->begin_transaction();
                            try {
                                $svc4 = new AchievementService((int)$weevilData["id"], $_COOKIE['weevil_name'], $achDb4);

                                // Maintain brainstrain_stats cumulative total BEFORE evaluating.
                                // NOT users.mulch wallet (spending changes it). NOT achievement_activity SUM.
                                $cumulativeDb4 = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                                $cumulativeDb4->begin_transaction();
                                $cumulativeDb4->query(
                                    "INSERT INTO brainstrain_stats (userID, totalMulchEarned, totalPlays, last_played) VALUES (" . (int)$weevilData["id"] . ", " . (int)$mulchEarned . ", 1, '" . date('Y-m-d') . "') ON DUPLICATE KEY UPDATE totalMulchEarned = totalMulchEarned + " . (int)$mulchEarned . ", totalPlays = totalPlays + 1, last_played = '" . date('Y-m-d') . "'"
                                );
                                $cumulativeDb4->commit();
                                $cumulativeDb4->close();

                                $svc4->recordActivity('brain_strain_earn', 4, (int)$mulchEarned, null, true);
                                $newIds4 = $svc4->evaluateForActivity('brain_strain_earn', 4, (int)$mulchEarned, null);
                                $achDb4->commit();
                                $achCsv = $newIds4 ? implode(',', $newIds4) : '0';
                            } catch (Throwable $e) {
                                $achDb4->rollback();
                            }
                            $achDb4->close();
                        }
                    }

                    if($updatedGameData == null) {
                        echo 'res=999&message=already played today.';
                        return;
                    }

                    if($userGameData["bestScore"] > 0 && $score > $userGameData["bestScore"])
                    sendAlert($_COOKIE['weevil_name'], $_COOKIE['weevil_name'] . ' has a new best <a href="event:location|172">Brain Strain</a> score of ' . $score, null, strval(time()));
                }
                else {
                    echo 'res=999';
                    return;
                }
            }
            else {
                echo 'res=999';
                return;
            }
        }
        else {
            echo 'res=999';
            return;
        }
    }
    catch(Exception $e) {
        echo 'res=999&message=something happened.';
        return;
    }

    echo 'res=0&modes=1&ave=' . $updatedGameData["averageScore"] . '&high=' . $updatedGameData["bestScore"] . '&levels=' . $levels . '&completedAchievements=' . ($achCsv ?? '0') . '&mulchEarned=' . $mulchEarned . '&xpEarned=' . $xpEarned . '&mulch=' . ($weevilData["mulch"] + $mulchEarned) . '&xp=' . ($weevilData["xp"] + $xpEarned) . '&result=' . $result . '&x=y';
}
else
echo 'res=999';
?>