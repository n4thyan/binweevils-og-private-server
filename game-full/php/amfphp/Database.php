<?php

// DARKKKKKKKKKKKKKKKKKKKKK

class Database extends PDO {

    public function __construct() {
		
		$connectionString = sprintf("mysql:dbname=%s;host=%s", 'bwps', 'localhost');

		try {
			parent::__construct($connectionString, 'root', '&FkhmHh^zTumduz!mah3');
			parent::setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $pdoException) {
			var_dump($pdoException);
		}
    }

    public function GetFrameDetails($frame, $myNest){
        switch($myNest){
            case 1:
                switch($frame){
                    case 2187:
                        return json_encode(["price"=>69, "frameSwf"=>"f_picFrameOrnateL3_v2"]);
                    case 2188:
                        return json_encode(["price"=>59, "frameSwf"=>"f_picFrameSquare3_v2"]);
                    case 2182:
                        return json_encode(["price"=>39, "frameSwf"=>"f_picFrameOrnateP1_v2"]);
                    case 2183:
                        return json_encode(["price"=>39, "frameSwf"=>"f_picFrameOrnateL2_v2"]);
                    case 2184:
                        return json_encode(["price"=>33, "frameSwf"=>"f_picFrameSquare2_v2"]);
                    case 2185:
                        return json_encode(["price"=>33, "frameSwf"=>"f_picFrameCircle2_v2"]);
                    case 2186:
                        return json_encode(["price"=>33, "frameSwf"=>"f_picFrameHeart2_v2"]);
                    case 2177:
                        return json_encode(["price"=>25, "frameSwf"=>"f_picFrameOrnateP1_v2"]);
                    case 2178:
                        return json_encode(["price"=>25, "frameSwf"=>"f_picFrameOrnateL1_v2"]);
                    case 2179:
                        return json_encode(["price"=>19, "frameSwf"=>"f_picFrameSquare1_v2"]);
                    case 2180:
                        return json_encode(["price"=>19, "frameSwf"=>"f_picFrameCircle1_v2"]);
                    case 2181:
                        return json_encode(["price"=>19, "frameSwf"=>"f_picFrameHeart1_v2"]);
                }
            case 0:
                switch($frame){
                    case 2187:
                        return json_encode(["price"=>345, "frameSwf"=>"f_picFrameOrnateL3_v2"]);
                    case 2188:
                        return json_encode(["price"=>295, "frameSwf"=>"f_picFrameSquare3_v2"]);
                    case 2182:
                        return json_encode(["price"=>195, "frameSwf"=>"f_picFrameOrnateP1_v2"]);
                    case 2183:
                        return json_encode(["price"=>195, "frameSwf"=>"f_picFrameOrnateL2_v2"]);
                    case 2184:
                        return json_encode(["price"=>165, "frameSwf"=>"f_picFrameSquare2_v2"]);
                    case 2185:
                        return json_encode(["price"=>165, "frameSwf"=>"f_picFrameCircle2_v2"]);
                    case 2186:
                        return json_encode(["price"=>165, "frameSwf"=>"f_picFrameHeart2_v2"]);
                    case 2177:
                        return json_encode(["price"=>125, "frameSwf"=>"f_picFrameOrnateP1_v2"]);
                    case 2178:
                        return json_encode(["price"=>125, "frameSwf"=>"f_picFrameOrnateL1_v2"]);
                    case 2179:
                        return json_encode(["price"=>95, "frameSwf"=>"f_picFrameSquare1_v2"]);
                    case 2180:
                        return json_encode(["price"=>95, "frameSwf"=>"f_picFrameCircle1_v2"]);
                    case 2181:
                        return json_encode(["price"=>95, "frameSwf"=>"f_picFrameHeart1_v2"]);
                    case 2189:
                        return json_encode(["price"=>100, "frameSwf"=>"f_celebrity_picFrameGam1_v2"]);
                    case 2192:
                        return json_encode(["price"=>100, "frameSwf"=>"f_celebrity_picFramePosh1_v2"]);
                    case 2193:
                        return json_encode(["price"=>100, "frameSwf"=>"f_celebrity_picFrameLab1_v2"]);
                    case 2194:
                        return json_encode(["price"=>100, "frameSwf"=>"f_celebrity_picFrameScribbles1_v2"]);
                    case 2195:
                        return json_encode(["price"=>100, "frameSwf"=>"f_celebrity_picFrameTink1_v2"]);
                    case 2196:
                        return json_encode(["price"=>100, "frameSwf"=>"f_celebrity_picFrameClott1_v2"]);
                    case 2197:
                        return json_encode(["price"=>100, "frameSwf"=>"f_celebrity_picFrameBunty1_v2"]);
                    case 2198:
                        return json_encode(["price"=>100, "frameSwf"=>"f_celebrity_picFrameFlam1_v2"]);
                    case 2199:
                        return json_encode(["price"=>100, "frameSwf"=>"f_celebrity_picFrameDosh1_v2"]);
                    case 2190:
                        return json_encode(["price"=>200, "frameSwf"=>"f_celebrity_picFrameGam2_v3"]);
                    case 2200:
                        return json_encode(["price"=>200, "frameSwf"=>"f_celebrity_picFramePosh2_v3"]);
                    case 2201:
                        return json_encode(["price"=>200, "frameSwf"=>"f_celebrity_picFrameLab2_v3"]);
                    case 2202:
                        return json_encode(["price"=>200, "frameSwf"=>"f_celebrity_picFrameScrib2_v3"]);
                    case 2203:
                        return json_encode(["price"=>200, "frameSwf"=>"f_celebrity_picFrameTink2_v3"]);
                    case 2204:
                        return json_encode(["price"=>200, "frameSwf"=>"f_celebrity_picFrameClott2_v3"]);
                    case 2205:
                        return json_encode(["price"=>200, "frameSwf"=>"f_celebrity_picFrameBunty2_v3"]);
                    case 2206:
                        return json_encode(["price"=>200, "frameSwf"=>"f_celebrity_picFrameFlam2_v3"]);
                    case 2207:
                        return json_encode(["price"=>200, "frameSwf"=>"f_celebrity_picFrameDosh2_v3"]);
                    case 2191:
                        return json_encode(["price"=>350, "frameSwf"=>"f_celebrity_picFrameGam3_v2"]);
                    case 2208:
                        return json_encode(["price"=>350, "frameSwf"=>"f_celebrity_picFramePosh3_v2"]);
                    case 2209:
                        return json_encode(["price"=>350, "frameSwf"=>"f_celebrity_picFrameLab3_v2"]);
                    case 2210:
                        return json_encode(["price"=>350, "frameSwf"=>"f_celebrity_picFrameScribbles3_v2"]);
                    case 2211:
                        return json_encode(["price"=>350, "frameSwf"=>"f_celebrity_picFrameTink3_v2"]);
                    case 2212:
                        return json_encode(["price"=>350, "frameSwf"=>"f_celebrity_picFrameClott3_v2"]);
                    case 2213:
                        return json_encode(["price"=>350, "frameSwf"=>"f_celebrity_picFrameBunty3_v2"]);
                    case 2214:
                        return json_encode(["price"=>350, "frameSwf"=>"f_celebrity_picFrameFlam3_v2"]);
                    case 2215:
                        return json_encode(["price"=>350, "frameSwf"=>"f_celebrity_picFrameDosh3_v2"]);
                    case 2485:
                        return json_encode(["price"=>100, "frameSwf"=>"f_xmas_picFrameBing1_v2"]);
                    case 2486:
                        return json_encode(["price"=>100, "frameSwf"=>"f_xmas_picFrameHoly1_v2"]);
                    case 2487:
                        return json_encode(["price"=>100, "frameSwf"=>"f_xmas_picFrameIce1_v2"]);
                    case 2488:
                        return json_encode(["price"=>100, "frameSwf"=>"f_xmas_picFramePresents1_v2"]);
                    case 2489:
                        return json_encode(["price"=>200, "frameSwf"=>"f_xmas_picFrameBing2_v2"]);
                    case 2490:
                        return json_encode(["price"=>200, "frameSwf"=>"f_xmas_picFrameHoly2_v2"]);
                    case 2491:
                        return json_encode(["price"=>200, "frameSwf"=>"f_xmas_picFrameIce2_v2"]);
                    case 2492:
                        return json_encode(["price"=>200, "frameSwf"=>"f_xmas_picFramePresents2_v2"]);
                    case 2493:
                        return json_encode(["price"=>350, "frameSwf"=>"f_xmas_picFrameBing3_v2"]);
                    case 2494:
                        return json_encode(["price"=>350, "frameSwf"=>"f_xmas_picFrameHoly3_v2"]);
                    case 2495:
                        return json_encode(["price"=>350, "frameSwf"=>"f_xmas_picFrameIce3_v2"]);
                    case 2496:
                        return json_encode(["price"=>350, "frameSwf"=>"f_xmas_picFramePresents3_v2"]);
                    
                }
        }
    }

    public function GrantBan($time, $id, $api = "") {
        try {

			$updateStmt = $this->prepare("UPDATE `users` SET bannedUntil = :timeUntil WHERE id = :ID");
			$updateStmt->bindValue(":timeUntil", $time);
			$updateStmt->bindValue(":ID", $id);
			$updateStmt->execute();
			$updateStmt->closeCursor();
			
            $this->InsertBanLog($time, $api);
			return true;

		} catch(PDOException $e) {
			var_dump($e);
		}
    }

    private function InsertBanLog($time, $api) {
        try {

			$updateStmt = $this->prepare("INSERT INTO `game-logs` (`weevilName`, `apiExecuted`, `bannedUntil`, `bannedFrom`, `type`) VALUES (:weevil, :apiE, :bU, :bF, :tpe)");
            $updateStmt->bindValue(':weevil', $_COOKIE['weevil_name']);
            $updateStmt->bindValue(':apiE', $api);
            $updateStmt->bindValue(':bU', strval($time));
            $updateStmt->bindValue(':bF', strval(time()));
            $updateStmt->bindValue(':tpe', 1);
			$updateStmt->execute();
			$updateStmt->closeCursor();
			
			return true;

		} catch(PDOException $e) {
			var_dump($e);
		}
    }

    public function SaveSnapshot($user, $itemId, $swf, $colour){
		try {
			$insertStatement = $this->prepare("INSERT INTO `weevilitems` (`weevilID`, `itemId`, `colour`, `category`, `configName`) VALUES ('$user', '$itemId', '$colour', '1', '$swf')");
			$insertStatement->execute();
			$lastid = $this->lastInsertId();
			$insertStatement->closeCursor();
			return $lastid;
		} catch(PDOException $pdoException) {
			var_dump($pdoException);
		}
	}

    public function GetWeevilStats($id) {
		try {
			$look = $this->prepare("SELECT * FROM `users` WHERE id = '$id'");

			$look->execute();
			$data = $look->fetch(PDO::FETCH_ASSOC);
			$look->closeCursor();

			return $data;
		} catch(PDOException $pdoException) {
			var_dump($pdoException);
		}
	}

    public function giveBusinessEarnings($weevil, $business, $amount) {
		try {
			$selectStmt = $this->prepare("UPDATE `tycoonBusinesses` SET total = total + :amt WHERE belongsTo = :weevil AND businessName = :busName");
			$selectStmt->bindValue(":weevil", $weevil);
            $selectStmt->bindValue(":amt", $amount);
            $selectStmt->bindValue(":busName", $business);
			$selectStmt->execute();
			$selectStmt->closeCursor();
		} catch(PDOException $exc) {
			var_dump($exc);
		}
	}

    public function removeWeevilMulch($weevil, $amount) {
		try {
			$selectStmt = $this->prepare("UPDATE `users` SET mulch = mulch - :amt WHERE username = :weevil");
			$selectStmt->bindValue(":weevil", $weevil);
            $selectStmt->bindValue(":amt", $amount);
			$selectStmt->execute();
			$selectStmt->closeCursor();
		} catch(PDOException $exc) {
			var_dump($exc);
		}
	}

}

?>