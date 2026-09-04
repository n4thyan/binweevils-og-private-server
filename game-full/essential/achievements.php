<?php
/**
 * Central Achievement Service — Activity Ledger + Evaluator + Completion + Rewards
 *
 * This file is included from backbone.php after internal.php.
 * It reuses the existing reward helpers with transaction-safe optional $db params.
 *
 * Architecture:
 *   recordActivity()  — INSERT into achievement_activity (the event log)
 *   evaluateForActivity() — run evaluator for a specific activity type
 *   completeAchievement()  — atomic completion INSERT + reward grant
 *
 * All 7 query patterns are implemented as methods on the evaluator.
 */

if (!defined('DB_NAME')) {
    // Safety: refuse to load outside backbone.php context
    http_response_code(500);
    exit('AchievementService requires backbone.php');
}

/* ==========================================================================
 * ACHIEVEMENT DEFINITIONS
 *
 * Each entry: id => [activityType, queryType, threshold, metadataRules]
 *
 * queryType:
 *   'count'            = COUNT(*)
 *   'count_distinct_date' = COUNT(DISTINCT DATE(occurredAt))
 *   'count_distinct_target' = COUNT(DISTINCT targetID)
 *   'sum'              = SUM(value)
 *   'exists'           = EXISTS any row (optionally filtered by targetID)
 *   'exists_target'    = EXISTS for targetID in specified array (for location achievements)
 *   'exists_state'     = EXISTS based on current DB state (no activity row needed)
 *   'inventory_count'  = COUNT(*) of owned items from the authoritative inventory table
 *                        (e.g. weevilhats for hat achievements). Triggered after a successful
 *                        inventory change, but queries the live DB state, NOT activity rows.
 *   'cumulative_game_stat' = Read a cumulative authoritative game statistic column
 *                        (e.g. brainstrain_stats.totalMulchEarned). NOT users.mulch wallet.
 *                        NOT achievement_activity SUM. This is the authoritative cumulative source.
 *
 * For missions (100-129): queryType='mission_grade', metadataRules specifies
 *   the grade threshold (e.g. 'A' means grade >= 'A' which is just A).
 *   For case files (130-136): queryType='case_stars', metadataRules='1','2','3'.
 *
 * NOTE: Achievements are grouped by activity type for efficient evaluation.
 * ========================================================================== */

define('ACHIEVEMENT_DEFS', serialize([
    // EXISTS state — no activity row
    ['id' => 1,  'activityType' => '',               'queryType' => 'exists_state',  'threshold' => 0, 'stateCheck' => 'SELECT tycoon FROM users WHERE id = ? AND tycoon = 1'],
    ['id' => 39, 'activityType' => 'login',           'queryType' => 'exists',        'threshold' => 0],

    // COUNT — change_look (achievement 3 = any single change)
    ['id' => 3,  'activityType' => 'change_look',     'queryType' => 'count',         'threshold' => 1],

    // COUNT DISTINCT DATE — change_look on N different days
    ['id' => 4,  'activityType' => 'change_look',     'queryType' => 'count_distinct_date', 'threshold' => 5],
    ['id' => 6,  'activityType' => 'change_look',     'queryType' => 'count_distinct_date', 'threshold' => 10],
    ['id' => 7,  'activityType' => 'change_look',     'queryType' => 'count_distinct_date', 'threshold' => 20],
    ['id' => 8,  'activityType' => 'change_look',     'queryType' => 'count_distinct_date', 'threshold' => 50],

    // COUNT DISTINCT DATE — login on N different days (months/years inferred from name)
    ['id' => 9,  'activityType' => 'login',           'queryType' => 'count_distinct_date', 'threshold' => 1],
    ['id' => 10, 'activityType' => 'login',           'queryType' => 'count_distinct_date', 'threshold' => 2],
    ['id' => 11, 'activityType' => 'login',           'queryType' => 'count_distinct_date', 'threshold' => 3],
    ['id' => 12, 'activityType' => 'login',           'queryType' => 'count_distinct_date', 'threshold' => 6],
    ['id' => 13, 'activityType' => 'login',           'queryType' => 'count_distinct_date', 'threshold' => 12],

    // COUNT DISTINCT DATE — Super Fan login days
    ['id' => 14, 'activityType' => 'login',           'queryType' => 'count_distinct_date', 'threshold' => 3],
    ['id' => 15, 'activityType' => 'login',           'queryType' => 'count_distinct_date', 'threshold' => 7],
    ['id' => 16, 'activityType' => 'login',           'queryType' => 'count_distinct_date', 'threshold' => 30],
    ['id' => 17, 'activityType' => 'login',           'queryType' => 'count_distinct_date', 'threshold' => 100],
    ['id' => 18, 'activityType' => 'login',           'queryType' => 'count_distinct_date', 'threshold' => 300],

    // SUM value — spend_mulch_single_item (>= threshold)
    ['id' => 19, 'activityType' => 'spend_mulch_single_item', 'queryType' => 'sum', 'threshold' => 500],
    ['id' => 20, 'activityType' => 'spend_mulch_single_item', 'queryType' => 'sum', 'threshold' => 1000],
    ['id' => 21, 'activityType' => 'spend_mulch_single_item', 'queryType' => 'sum', 'threshold' => 1500],
    ['id' => 22, 'activityType' => 'spend_mulch_single_item', 'queryType' => 'sum', 'threshold' => 3000],
    ['id' => 23, 'activityType' => 'spend_mulch_single_item', 'queryType' => 'sum', 'threshold' => 5000],

    // SUM value — spend_dosh_single_item (>= threshold)
    ['id' => 24, 'activityType' => 'spend_dosh_single_item',  'queryType' => 'sum', 'threshold' => 1],
    ['id' => 25, 'activityType' => 'spend_dosh_single_item',  'queryType' => 'sum', 'threshold' => 3],
    ['id' => 26, 'activityType' => 'spend_dosh_single_item',  'queryType' => 'sum', 'threshold' => 5],
    ['id' => 27, 'activityType' => 'spend_dosh_single_item',  'queryType' => 'sum', 'threshold' => 15],
    ['id' => 28, 'activityType' => 'spend_dosh_single_item',  'queryType' => 'sum', 'threshold' => 20],

    // COUNT — buy_item
    ['id' => 29, 'activityType' => 'buy_item',         'queryType' => 'count',         'threshold' => 3],
    ['id' => 30, 'activityType' => 'buy_item',         'queryType' => 'count',         'threshold' => 20],
    ['id' => 31, 'activityType' => 'buy_item',         'queryType' => 'count',         'threshold' => 100],
    ['id' => 32, 'activityType' => 'buy_item',         'queryType' => 'count',         'threshold' => 500],
    ['id' => 33, 'activityType' => 'buy_item',         'queryType' => 'count',         'threshold' => 1000],

    // HAT ACHIEVEMENTS — query authoritative inventory, NOT buy_hat activity rows.
    // After a successful hat purchase, the evaluator triggers 'hat_inventory_changed'
    // and queries the weevilhats table directly for the current owned-hat count.
    // queryType 'inventory_count' means: SELECT COUNT(*) FROM weevilhats WHERE ownerName = username.
    // COUNT semantics: COUNT(*) of owned hat rows (each row = one owned hat, including colour variants).
    ['id' => 34, 'activityType' => 'hat_inventory_changed', 'queryType' => 'inventory_count', 'threshold' => 3, 'inventoryTable' => 'weevilhats', 'inventoryOwnerColumn' => 'ownerName'],
    ['id' => 35, 'activityType' => 'hat_inventory_changed', 'queryType' => 'inventory_count', 'threshold' => 10, 'inventoryTable' => 'weevilhats', 'inventoryOwnerColumn' => 'ownerName'],
    ['id' => 36, 'activityType' => 'hat_inventory_changed', 'queryType' => 'inventory_count', 'threshold' => 25, 'inventoryTable' => 'weevilhats', 'inventoryOwnerColumn' => 'ownerName'],
    ['id' => 37, 'activityType' => 'hat_inventory_changed', 'queryType' => 'inventory_count', 'threshold' => 50, 'inventoryTable' => 'weevilhats', 'inventoryOwnerColumn' => 'ownerName'],
    ['id' => 38, 'activityType' => 'hat_inventory_changed', 'queryType' => 'inventory_count', 'threshold' => 100, 'inventoryTable' => 'weevilhats', 'inventoryOwnerColumn' => 'ownerName'],

    // COUNT — buy_seed
    ['id' => 40, 'activityType' => 'buy_seed',         'queryType' => 'count',         'threshold' => 20],
    ['id' => 41, 'activityType' => 'buy_seed',         'queryType' => 'count',         'threshold' => 100],
    ['id' => 42, 'activityType' => 'buy_seed',         'queryType' => 'count',         'threshold' => 500],
    ['id' => 43, 'activityType' => 'buy_seed',         'queryType' => 'count',         'threshold' => 1000],
    ['id' => 44, 'activityType' => 'buy_seed',         'queryType' => 'count',         'threshold' => 5000],

    // COUNT — buy_garden_item
    ['id' => 45, 'activityType' => 'buy_garden_item',  'queryType' => 'count',         'threshold' => 1],
    ['id' => 46, 'activityType' => 'buy_garden_item',  'queryType' => 'count',         'threshold' => 3],
    ['id' => 47, 'activityType' => 'buy_garden_item',  'queryType' => 'count',         'threshold' => 5],
    ['id' => 48, 'activityType' => 'buy_garden_item',  'queryType' => 'count',         'threshold' => 10],
    ['id' => 49, 'activityType' => 'buy_garden_item',  'queryType' => 'count',         'threshold' => 20],

    // COUNT — eat_ice_cream (any single; first achievement)
    ['id' => 50, 'activityType' => 'eat_ice_cream',    'queryType' => 'count',         'threshold' => 1],

    // COUNT DISTINCT DATE — eat_ice_cream on N different days
    ['id' => 51, 'activityType' => 'eat_ice_cream',    'queryType' => 'count_distinct_date', 'threshold' => 5],
    ['id' => 52, 'activityType' => 'eat_ice_cream',    'queryType' => 'count_distinct_date', 'threshold' => 10],
    ['id' => 53, 'activityType' => 'eat_ice_cream',    'queryType' => 'count_distinct_date', 'threshold' => 25],
    ['id' => 54, 'activityType' => 'eat_ice_cream',    'queryType' => 'count_distinct_date', 'threshold' => 50],

    // BRAIN STRAIN ACHIEVEMENTS — query cumulative authoritative stats from the DB,
    // NOT users.mulch (which is the current wallet balance and changes when mulch is spent),
    // and NOT achievement_activity SUM (which duplicates the authoritative source).
    // The singleplayergames_stats table has no cumulative column, so we use a dedicated
    // 'brainstrain_total_mulch' table that tracks total mulch earned from brain strain.
    // queryType 'cumulative_game_stat' reads from that table's 'totalMulchEarned' column.
    ['id' => 55, 'activityType' => 'brain_strain_earn', 'queryType' => 'cumulative_game_stat', 'threshold' => 50, 'statColumn' => 'totalMulchEarned', 'statTable' => 'brainstrain_stats'],
    ['id' => 56, 'activityType' => 'brain_strain_earn', 'queryType' => 'cumulative_game_stat', 'threshold' => 500, 'statColumn' => 'totalMulchEarned', 'statTable' => 'brainstrain_stats'],
    ['id' => 57, 'activityType' => 'brain_strain_earn', 'queryType' => 'cumulative_game_stat', 'threshold' => 1500, 'statColumn' => 'totalMulchEarned', 'statTable' => 'brainstrain_stats'],
    ['id' => 58, 'activityType' => 'brain_strain_earn', 'queryType' => 'cumulative_game_stat', 'threshold' => 5000, 'statColumn' => 'totalMulchEarned', 'statTable' => 'brainstrain_stats'],
    ['id' => 59, 'activityType' => 'brain_strain_earn', 'queryType' => 'cumulative_game_stat', 'threshold' => 10000, 'statColumn' => 'totalMulchEarned', 'statTable' => 'brainstrain_stats'],

    // COUNT DISTINCT target — earn_trophy
    ['id' => 64, 'activityType' => 'earn_trophy',      'queryType' => 'count_distinct_target', 'threshold' => 1],
    ['id' => 65, 'activityType' => 'earn_trophy',      'queryType' => 'count_distinct_target', 'threshold' => 3],
    ['id' => 66, 'activityType' => 'earn_trophy',      'queryType' => 'count_distinct_target', 'threshold' => 5],
    ['id' => 67, 'activityType' => 'earn_trophy',      'queryType' => 'count_distinct_target', 'threshold' => 10],
    ['id' => 68, 'activityType' => 'earn_trophy',      'queryType' => 'count_distinct_target', 'threshold' => 15],

    // COUNT DISTINCT target — earn_trophy (garden trophies, Best Garden type 41)
    ['id' => 140, 'activityType' => 'earn_trophy',     'queryType' => 'count_distinct_target', 'threshold' => 1],
    ['id' => 141, 'activityType' => 'earn_trophy',     'queryType' => 'count_distinct_target', 'threshold' => 3],
    ['id' => 142, 'activityType' => 'earn_trophy',     'queryType' => 'count_distinct_target', 'threshold' => 5],
    ['id' => 143, 'activityType' => 'earn_trophy',     'queryType' => 'count_distinct_target', 'threshold' => 10],
    ['id' => 144, 'activityType' => 'earn_trophy',     'queryType' => 'count_distinct_target', 'threshold' => 15],

    // EXISTS — adopt_pet
    ['id' => 2,  'activityType' => 'adopt_pet',        'queryType' => 'exists',        'threshold' => 0],

    // EXISTS — join_sws
    ['id' => 137, 'activityType' => 'join_sws',        'queryType' => 'exists',        'threshold' => 0],

    // EXISTS — enter_location
    // id=138: Festive Fun - Entered the Winter Wonderland (Room 131: PartyBoxInside3)
    // id=139: Weevil Holiday - Flew with Weevil Air (DEFERRED - flight trigger not recovered)
    // 
    // Weevil Holiday trigger requires proving the Weevil Air flight event.
    // Per the original game flow: Airport Interior -> Plane boarding -> Mulch Island arrival.
    // The achievement is for "Flew with Weevil Air", NOT "arrived at Mulch Island".
    // Until the actual flight transition is proven, achievement 139 cannot be triggered.
    // Using a dummy non-existent targetID (999) to prevent accidental completion.
    ['id' => 138, 'activityType' => 'enter_location',  'queryType' => 'exists_target', 'targetIDs' => [131], 'threshold' => 0],
    ['id' => 139, 'activityType' => 'enter_location',  'queryType' => 'exists_target', 'targetIDs' => [999], 'threshold' => 0],  // DEFERRED - needs flight event

    // COUNT — task_complete
    ['id' => 60, 'activityType' => 'task_complete',    'queryType' => 'count',         'threshold' => 1],

    // Mission grade (100-129): activityType=mission_complete, metadata.grade >= required grade
    // Grade scale: D=1, C=2, B=3, A=4. So "D or higher" means grade >= 1 (always true on any mission_complete).
    // We store the required grade level in the query.
    ['id' => 100, 'activityType' => 'mission_complete', 'queryType' => 'mission_grade', 'threshold' => 1, 'missionId' => 'rlbp'],
    ['id' => 101, 'activityType' => 'mission_complete', 'queryType' => 'mission_grade', 'threshold' => 1, 'missionId' => 'rlbp'],
    ['id' => 102, 'activityType' => 'mission_complete', 'queryType' => 'mission_grade', 'threshold' => 2, 'missionId' => 'rlbp'],
    ['id' => 103, 'activityType' => 'mission_complete', 'queryType' => 'mission_grade', 'threshold' => 3, 'missionId' => 'rlbp'],
    ['id' => 104, 'activityType' => 'mission_complete', 'queryType' => 'mission_grade', 'threshold' => 4, 'missionId' => 'rlbp'],
    ['id' => 105, 'activityType' => 'mission_complete', 'queryType' => 'mission_grade', 'threshold' => 1, 'missionId' => 'hwx'],
    ['id' => 106, 'activityType' => 'mission_complete', 'queryType' => 'mission_grade', 'threshold' => 1, 'missionId' => 'hwx'],
    ['id' => 107, 'activityType' => 'mission_complete', 'queryType' => 'mission_grade', 'threshold' => 2, 'missionId' => 'hwx'],
    ['id' => 108, 'activityType' => 'mission_complete', 'queryType' => 'mission_grade', 'threshold' => 3, 'missionId' => 'hwx'],
    ['id' => 109, 'activityType' => 'mission_complete', 'queryType' => 'mission_grade', 'threshold' => 4, 'missionId' => 'hwx'],
    ['id' => 115, 'activityType' => 'mission_complete', 'queryType' => 'mission_grade', 'threshold' => 1, 'missionId' => 'ddp'],
    ['id' => 116, 'activityType' => 'mission_complete', 'queryType' => 'mission_grade', 'threshold' => 1, 'missionId' => 'ddp'],
    ['id' => 117, 'activityType' => 'mission_complete', 'queryType' => 'mission_grade', 'threshold' => 2, 'missionId' => 'ddp'],
    ['id' => 118, 'activityType' => 'mission_complete', 'queryType' => 'mission_grade', 'threshold' => 3, 'missionId' => 'ddp'],
    ['id' => 119, 'activityType' => 'mission_complete', 'queryType' => 'mission_grade', 'threshold' => 4, 'missionId' => 'ddp'],
    ['id' => 120, 'activityType' => 'mission_complete', 'queryType' => 'mission_grade', 'threshold' => 1, 'missionId' => 'ttt'],
    ['id' => 121, 'activityType' => 'mission_complete', 'queryType' => 'mission_grade', 'threshold' => 1, 'missionId' => 'ttt'],
    ['id' => 122, 'activityType' => 'mission_complete', 'queryType' => 'mission_grade', 'threshold' => 2, 'missionId' => 'ttt'],
    ['id' => 123, 'activityType' => 'mission_complete', 'queryType' => 'mission_grade', 'threshold' => 3, 'missionId' => 'ttt'],
    ['id' => 124, 'activityType' => 'mission_complete', 'queryType' => 'mission_grade', 'threshold' => 4, 'missionId' => 'ttt'],
    ['id' => 125, 'activityType' => 'mission_complete', 'queryType' => 'mission_grade', 'threshold' => 1, 'missionId' => 'lld'],
    ['id' => 126, 'activityType' => 'mission_complete', 'queryType' => 'mission_grade', 'threshold' => 1, 'missionId' => 'lld'],
    ['id' => 127, 'activityType' => 'mission_complete', 'queryType' => 'mission_grade', 'threshold' => 2, 'missionId' => 'lld'],
    ['id' => 128, 'activityType' => 'mission_complete', 'queryType' => 'mission_grade', 'threshold' => 3, 'missionId' => 'lld'],
    ['id' => 129, 'activityType' => 'mission_complete', 'queryType' => 'mission_grade', 'threshold' => 4, 'missionId' => 'lld'],

    // Case file stars (130-136): metadata.stars >= threshold
    ['id' => 130, 'activityType' => 'case_file_complete', 'queryType' => 'case_stars', 'threshold' => 1],
    ['id' => 131, 'activityType' => 'case_file_complete', 'queryType' => 'case_stars', 'threshold' => 1],
    ['id' => 132, 'activityType' => 'case_file_complete', 'queryType' => 'case_stars', 'threshold' => 2],
    ['id' => 133, 'activityType' => 'case_file_complete', 'queryType' => 'case_stars', 'threshold' => 3],
    ['id' => 134, 'activityType' => 'case_file_complete', 'queryType' => 'case_stars', 'threshold' => 1],
    ['id' => 135, 'activityType' => 'case_file_complete', 'queryType' => 'case_stars', 'threshold' => 2],
    ['id' => 136, 'activityType' => 'case_file_complete', 'queryType' => 'case_stars', 'threshold' => 3],
]));

/* ==========================================================================
 * TRANSACTION-SAFE REWARD HELPERS
 *
 * Existing addMulchByName/addDoshByName/addExperienceByName each create their
 * own mysqli connection, which breaks atomicity inside a transaction.
 *
 * These transaction-aware variants accept an optional $db parameter. When
 * provided, they use that connection; when omitted they fall back to the
 * original behaviour (create a new connection) so existing callers are
 * unaffected.
 * ========================================================================== */

function addMulchByNameTx($weevilName, $total, $db = null) {
    $t = intval($total);
    if ($db) {
        $q = $db->prepare("UPDATE `users` SET `mulch` = mulch + ? WHERE username = ?;");
        $q->bind_param('ss', $t, $weevilName);
        $q->execute();
        return $q->affected_rows === 1;
    }
    return addMulchByName($weevilName, $total);
}

function addDoshByNameTx($weevilName, $total, $db = null) {
    $t = intval($total);
    if ($db) {
        $q = $db->prepare("UPDATE `users` SET `dosh` = dosh + ? WHERE username = ?;");
        $q->bind_param('ss', $t, $weevilName);
        $q->execute();
        return $q->affected_rows === 1;
    }
    return addDoshByName($weevilName, $total);
}

function addExperienceByNameTx($weevilName, $total, $db = null) {
    $t = intval($total);
    if ($db) {
        $q = $db->prepare("UPDATE `users` SET `xp` = xp + ?, `xp1` = xp1 + ? WHERE username = ?;");
        $q->bind_param('sss', $t, $t, $weevilName);
        $q->execute();
        return $q->affected_rows === 1;
    }
    return addExperienceByName($weevilName, $total);
}


/* ==========================================================================
 * ACHIEVEMENT SERVICE
 * ========================================================================== */

class AchievementService {

    /** @var mysqli|null Active DB connection for transaction-aware operations */
    private $db;

    /** @var int Current user ID */
    private $userID;

    /** @var string|null Current username (for reward grants) */
    private $username;

    /** @var array Evaluator cache: activityType => [newlyCompleted ids] */
    private $lastNewIds = [];

    /** @var int Last query scalar result (for test inspection) */
    public $lastQueryCount = 0;

    public function __construct($userID, $username = null, mysqli $db = null) {
        $this->userID = (int)$userID;
        $this->username = $username;
        $this->db = $db;
    }

    /* -------------------------------------------------------------------------
     * ACTIVITY RECORDING
     * ------------------------------------------------------------------------- */

    /**
     * Record one legitimate activity event.
     * Returns the inserted activity ID, or 0 if a duplicate was suppressed.
     *
     * @param string $activityType
     * @param int|null $targetID
     * @param int $value
     * @param array|null $metadata (encoded as JSON)
     * @param bool $sameSecondGuard  If true, suppress exact-second duplicates
     */
    public function recordActivity($activityType, $targetID = null, $value = 1, $metadata = null, $sameSecondGuard = true) {
        $db = $this->getDb();

        // Same-second guard for retry protection
        if ($sameSecondGuard && $targetID !== null) {
            $dup = $db->prepare(
                "SELECT id FROM achievement_activity
                 WHERE userID = ? AND activityType = ? AND targetID = ?
                   AND occurredAt >= DATE_SUB(NOW(3), INTERVAL 1 SECOND)
                 LIMIT 1"
            );
            $dup->bind_param('isi', $this->userID, $activityType, $targetID);
            $dup->execute();
            if ($dup->get_result()->fetch_assoc()) {
                $dup->close();
                return 0; // duplicate within same second
            }
            $dup->close();
        }

        $metaJson = $metadata ? json_encode($metadata) : null;
        $t = $this->userID;
        $ins = $db->prepare(
            "INSERT INTO achievement_activity (userID, activityType, targetID, value, metadata)
             VALUES (?, ?, ?, ?, ?)"
        );
        $ins->bind_param('isis', $t, $activityType, $targetID, $value, $metaJson);
        $ins->execute();
        $id = (int)$ins->insert_id;
        $ins->close();
        return $id;
    }

    /* -------------------------------------------------------------------------
     * EVALUATOR — run only achievements relevant to the given activity type
     * ------------------------------------------------------------------------- */

    /**
     * Evaluate all achievements for a given activity type.
     * Returns array of newly completed achievement IDs (empty if none).
     *
     * @param string $activityType
     * @param int|null $targetID  The target from the activity (for DISTINCT target queries)
     * @param int $value          The value from the activity (for SUM queries)
     * @param array|null $metadata The metadata from the activity (for mission_grade / case_stars)
     */
    public function evaluateForActivity($activityType, $targetID = null, $value = 1, $metadata = null) {
        $db = $this->getDb();
        $newlyCompleted = [];
        $defs = $this->getDefsForActivityType($activityType);

        // Also check state-based achievements on every evaluation (cheap)
        $stateIds = $this->getStateBasedAchievementIds();
        $defs = array_merge($defs, array_filter($stateIds, function($id) use ($defs) {
            return !in_array($id, array_column($defs, 'id'));
        }));

        foreach ($defs as $def) {
            if ($this->isAlreadyCompleted($def['id'])) {
                continue;
            }
            $achieved = $this->evaluateOne($db, $def, $targetID, $value, $metadata);
            if ($achieved) {
                $newly = $this->completeAchievement($def['id'], $db);
                if ($newly) {
                    $newlyCompleted[] = $def['id'];
                }
            }
        }

        $this->lastNewIds[$activityType] = $newlyCompleted;
        return $newlyCompleted;
    }

    /**
     * Evaluate a single achievement definition against current state.
     */
    private function evaluateOne(mysqli $db, array $def, $targetID, $value, $metadata) {
        $userID = $this->userID;
        switch ($def['queryType']) {
            case 'count':
                $r = $this->queryScalar($db,
                    "SELECT COUNT(*) FROM achievement_activity WHERE userID = ? AND activityType = ?",
                    'is', [$userID, $def['activityType']]);
                return $r >= $def['threshold'];

            case 'count_distinct_date':
                $r = $this->queryScalar($db,
                    "SELECT COUNT(DISTINCT DATE(occurredAt)) FROM achievement_activity WHERE userID = ? AND activityType = ?",
                    'is', [$userID, $def['activityType']]);
                return $r >= $def['threshold'];

            case 'count_distinct_target':
                $r = $this->queryScalar($db,
                    "SELECT COUNT(DISTINCT targetID) FROM achievement_activity WHERE userID = ? AND activityType = ?",
                    'is', [$userID, $def['activityType']]);
                return $r >= $def['threshold'];

            case 'sum':
                $r = $this->queryScalar($db,
                    "SELECT COALESCE(SUM(value),0) FROM achievement_activity WHERE userID = ? AND activityType = ?",
                    'is', [$userID, $def['activityType']]);
                return $r >= $def['threshold'];

            case 'exists':
                $r = $this->queryScalar($db,
                    "SELECT COUNT(*) FROM achievement_activity WHERE userID = ? AND activityType = ?" .
                    ($targetID !== null ? " AND targetID = ?" : ""),
                    $targetID !== null ? 'isi' : 'is',
                    $targetID !== null ? [$userID, $def['activityType'], $targetID] : [$userID, $def['activityType']]);
                return $r > 0;

            case 'exists_target':
                // EXISTS with specific targetID (for location-based achievements)
                // Definition must include 'targetIDs' as array of allowed room IDs
                if (!isset($def['targetIDs']) || !is_array($def['targetIDs'])) {
                    return false;
                }
                $inClause = str_repeat('?,', count($def['targetIDs']) - 1) . '?';
                $sql = "SELECT COUNT(*) FROM achievement_activity WHERE userID = ? AND activityType = ? AND targetID IN ($inClause)";
                $params = array_merge([$userID, $def['activityType']], $def['targetIDs']);
                $r = $this->queryScalar($db, $sql, str_repeat('i', count($params)), $params);
                return $r > 0;

            case 'exists_state':
                return $this->checkState($db, $def['stateCheck']);

            case 'inventory_count':
                // Query the authoritative inventory table directly (e.g. weevilhats).
                // Does NOT count activity rows — counts actual owned items.
                // Uses username (ownerName in weevilhats) for the query.
                return $this->checkInventoryCount($db, $def['inventoryTable'], $def['inventoryOwnerColumn'], $def['threshold']);

            case 'cumulative_game_stat':
                // Query a cumulative authoritative game statistic column (e.g. brainstrain_stats.totalMulchEarned).
                // NOT users.mulch wallet (which changes when spent).
                // NOT achievement_activity SUM (which duplicates the source).
                $statValue = $this->checkCumulativeStat($db, $def['statTable'], $def['statColumn']);
                return $statValue >= $def['threshold'];

            case 'mission_grade':
                if (!$metadata || !isset($metadata['grade']) || !isset($def['missionId'])) {
                    return false;
                }
                $gradeLevels = ['D' => 1, 'C' => 2, 'B' => 3, 'A' => 4];
                $achievedGrade = $gradeLevels[$metadata['grade']] ?? 0;
                // Count activities for this mission type where grade >= threshold
                $r = $this->queryScalar($db,
                    "SELECT COUNT(*) FROM achievement_activity WHERE userID = ? AND activityType = 'mission_complete' AND metadata->>'$.grade' IN ("
                    . $this->gradesAtLeast($def['threshold']) . ")",
                    'i', [$userID]);
                return $r > 0;

            case 'case_stars':
                if (!$metadata || !isset($metadata['stars']) || !isset($def['missionId'])) {
                    return false;
                }
                $r = $this->queryScalar($db,
                    "SELECT COUNT(*) FROM achievement_activity WHERE userID = ? AND activityType = 'case_file_complete' AND CAST(metadata->>'$.stars' AS UNSIGNED) >= ?",
                    'ii', [$userID, $def['threshold']]);
                return $r > 0;

            default:
                return false;
        }
    }

    private function gradesAtLeast($minLevel) {
        $all = [1 => "'D'", 2 => "'C','D'", 3 => "'B','C','D'", 4 => "'A','B','C','D'"];
        return $all[$minLevel] ?? "'A'";
    }

    private function queryScalar(mysqli $db, $sql, $types, $params) {
        $stmt = $db->prepare($sql);
        if (!$stmt) return 0;
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_row();
        $stmt->close();
        return $row ? (int)$row[0] : 0;
    }

    /* -------------------------------------------------------------------------
     * COMPLETION + REWARD GRANT (atomic)
     * ------------------------------------------------------------------------- */

    /**
     * Complete an achievement for this user: INSERT completion row + grant rewards.
     * Uses the provided $db connection (or creates one).
     * Returns true if the achievement was newly completed, false if already done.
     */
    public function completeAchievement($achievementId, mysqli $db = null) {
        $useDb = $db ?: $this->getDb();
        $achievementId = (int)$achievementId;

        // SELECT FOR UPDATE to lock the row
        $lock = $useDb->prepare(
            "SELECT id FROM achievementscompleted WHERE idx = ? AND achievementId = ? FOR UPDATE"
        );
        $lock->bind_param('ii', $this->userID, $achievementId);
        $lock->execute();
        $exists = $lock->get_result()->fetch_assoc();
        $lock->close();

        if ($exists) {
            return false; // already completed
        }

        // INSERT completion row
        $ins = $useDb->prepare(
            "INSERT INTO achievementscompleted (idx, achievementId, is_it_new) VALUES (?, ?, 1)"
        );
        $ins->bind_param('ii', $this->userID, $achievementId);
        $ins->execute();

        if ($ins->affected_rows === 0) {
            $ins->close();
            return false; // UNIQUE constraint or other failure
        }
        $ins->close();

        // Grant rewards
        $this->grantRewards($achievementId, $useDb);

        return true;
    }

    /**
     * Load reward rows from achievement_rewards_v1 and grant via
     * transaction-safe reward helpers.
     */
    private function grantRewards($achievementId, mysqli $db) {
        $stmt = $db->prepare(
            "SELECT rewardType, rewardValue FROM achievement_rewards_v1 WHERE achievementId = ?"
        );
        $stmt->bind_param('i', $achievementId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $type = $row['rewardType'];
            $val  = (int)$row['rewardValue'];
            $name = $this->username ?: $this->getUsername($db);
            if (!$name) continue;

            switch ($type) {
                case 'mulch': addMulchByNameTx($name, $val, $db); break;
                case 'dosh':  addDoshByNameTx($name, $val, $db);  break;
                case 'xp':    addExperienceByNameTx($name, $val, $db); break;
            }
        }
        $stmt->close();
    }

    /* -------------------------------------------------------------------------
     * HELPERS
     * ------------------------------------------------------------------------- */

    /** Get the active DB connection (reuse or create) */
    private function getDb(): mysqli {
        if ($this->db instanceof mysqli) {
            return $this->db;
        }
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->db->connect_errno) {
            throw new RuntimeException('AchievementService DB connect failed: ' . $this->db->connect_error);
        }
        return $this->db;
    }

    private function getUsername(mysqli $db): ?string {
        $q = $db->prepare("SELECT username FROM users WHERE id = ? LIMIT 1");
        $q->bind_param('i', $this->userID);
        $q->execute();
        $row = $q->get_result()->fetch_assoc();
        $q->close();
        return $row ? $row['username'] : null;
    }

    private function isAlreadyCompleted($achievementId): bool {
        $db = $this->getDb();
        $aid = (int)$achievementId;
        $stmt = $db->prepare(
            "SELECT id FROM achievementscompleted WHERE idx = ? AND achievementId = ? LIMIT 1"
        );
        $stmt->bind_param('ii', $this->userID, $aid);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (bool)$exists;
    }

    private function checkState(mysqli $db, $sql): bool {
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $this->userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_row();
        $stmt->close();
        return $row && ((int)$row[0] > 0);
    }

    private function checkInventoryCount(mysqli $db, $table, $ownerCol, $threshold): bool {
        $this->lastQueryCount = 0;
        // Resolve username from userID, then count owned items.
        $username = $this->username ?: $this->getUsername($db);
        if (!$username) return false;
        // weevilhats uses ownerName (varchar username); query by the resolved username.
        $sql = "SELECT COUNT(*) FROM `$table` WHERE `$ownerCol` = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_row();
        $stmt->close();
        $this->lastQueryCount = $row ? (int)$row[0] : 0;
        return $this->lastQueryCount >= $threshold;
    }

    private function checkCumulativeStat(mysqli $db, $table, $column): int {
        // Read a cumulative authoritative game statistic column (e.g. brainstrain_stats.totalMulchEarned).
        // NOT users.mulch wallet. NOT achievement_activity SUM.
        // Use single-quoted string to avoid PHP backtick shell-execution.
        $sql = 'SELECT `' . $column . '` FROM `' . $table . '` WHERE userID = ? LIMIT 1';
        $stmt = $db->prepare($sql);
        if (!$stmt) return 0;
        $stmt->bind_param('i', $this->userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_row();
        $stmt->close();
        return $row ? (int)$row[0] : 0;
    }

    private function getDefsForActivityType(string $activityType): array {
        $all = unserialize(ACHIEVEMENT_DEFS);
        return array_values(array_filter($all, function($d) use ($activityType) {
            return $d['activityType'] === $activityType;
        }));
    }

    private function getStateBasedAchievementIds(): array {
        $all = unserialize(ACHIEVEMENT_DEFS);
        return array_values(array_filter($all, function($d) {
            return $d['queryType'] === 'exists_state';
        }));
    }

    /**
     * Return the last set of newly completed IDs for a given activity type
     * (populated after evaluateForActivity()).
     */
    public function getLastNewIds(string $activityType): array {
        return $this->lastNewIds[$activityType] ?? [];
    }

    /**
     * Convenience: record one activity then evaluate achievements for that type.
     * Returns newly completed achievement IDs.
     */
    public function recordAndEvaluate(string $activityType, $targetID = null, $value = 1, $metadata = null, $sameSecondGuard = true): array {
        $this->recordActivity($activityType, $targetID, $value, $metadata, $sameSecondGuard);
        return $this->evaluateForActivity($activityType, $targetID, $value, $metadata);
    }

    /**
     * Evaluate state-based achievements (no specific activity trigger).
     */
    public function evaluateStateAchievements(): array {
        $db = $this->getDb();
        $newlyCompleted = [];
        $stateIds = $this->getStateBasedAchievementIds();
        foreach ($stateIds as $def) {
            if ($this->isAlreadyCompleted($def['id'])) {
                continue;
            }
            if ($this->evaluateOne($db, $def, null, 0, null)) {
                $newly2 = $this->completeAchievement($def['id'], $db);
                if ($newly2) {
                    $newlyCompleted[] = $def['id'];
                }
            }
        }
        return $newlyCompleted;
    }
}
