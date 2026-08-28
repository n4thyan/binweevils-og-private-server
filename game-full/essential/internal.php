<?php
    error_reporting(0);
    /* Admin panel functions */
	function getDevelopmentProgress($limit, $offset) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$lim = intval($limit);
		$off = intval($offset);
		$q = $db->prepare("SELECT * FROM development ORDER BY id DESC LIMIT ? OFFSET ?");
		$q->bind_param('ii', $lim, $off);
		$q->execute();

		$res = $q->get_result();
        
        if($res = $res->fetch_all()) {
            return $res;
        }
    }

    function AddDevelopmentTask($name, $description, $icon) {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$q = $db->prepare("INSERT INTO `development` (`icon`, `task`, `status`, `task-description`) VALUES (?, ?, 'pending', ?)");
		$q->bind_param('sss', $icon, $name, $description);
		$q->execute();
    }

    function updateDevelopmentStatus($description, $status) {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $q = $db->prepare("UPDATE development SET `status` = ? WHERE `task-description` = ?;");
        $q->bind_param('ss', $status, $description);
        $q->execute();
    }

	function logAdminActionBans($adminName, $weevilName, $bannedUntil, $bannedFrom, $type, $reason) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $q = $db->prepare("INSERT INTO `game-logs` (`weevilName`, `bannedUntil`, `bannedFrom`, `adminName`, `type`, `reason`) VALUES (?, ?, ?, ?, ?, ?)");
        $q->bind_param('ssssss', $weevilName, strval($bannedUntil), strval($bannedFrom), $adminName, $type, $reason);
        $q->execute();
	}

	function logAdminActionItem($adminName, $weevilName, $bannedFrom, $type, $itemId) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $q = $db->prepare("INSERT INTO `game-logs` (`weevilName`, `bannedFrom`, `adminName`, `type`, `itemId`) VALUES (?, ?, ?, ?, ?)");
        $q->bind_param('sssss', $weevilName, strval($bannedFrom), $adminName, $type, $itemId);
        $q->execute();
	}

	function logAdminAction($adminName, $weevilName, $bannedFrom, $type) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $q = $db->prepare("INSERT INTO `game-logs` (`weevilName`, `bannedFrom`, `adminName`, `type`) VALUES (?, ?, ?, ?)");
        $q->bind_param('ssss', $weevilName, strval($bannedFrom), $adminName, $type);
        $q->execute();
	}

	// Verify a weevil's password against the (now hashed) stored password.
	function checkPassword($username, $plain) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$q = $db->prepare("SELECT `password` FROM `users` WHERE `username` = ? LIMIT 1;");
		$q->bind_param('s', $username);
		$q->execute();

		$res = $q->get_result();

		if($res = $res->fetch_array()) {
			if(isset($res['password']) && password_verify($plain, $res['password']))
				return true;
		}

		return false;
	}

	function adminLogin($adminName, $adminPassword) {
		// Password is now hashed; verify it, then confirm moderator status.
		if(!checkPassword($adminName, $adminPassword))
			return false;

		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $q = $db->prepare("SELECT isModerator FROM `users` WHERE `username` = ?");
        $q->bind_param('s', $adminName);
        $q->execute();

		$res = $q->get_result();

		if($res = $res->fetch_array()) {
			if($res['isModerator'] == 1)
			return true;
		}

		return false;
	}

	function adminGetWeevilStatsByName($weevilName) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$q = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1;");
		$q->bind_param('s', $weevilName);
		$q->execute();
		
		$res = $q->get_result();

		if($res = $res->fetch_array())
		return $res;
		else return json_encode(["responseCode" => 999, "message" => "weevil data not found"]);
	}

    function getStatsFromModPanel($weevilName) {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $q = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1;");
        $q->bind_param('s', $weevilName);
        $q->execute();
        
        $res = $q->get_result();

        if($res = $res->fetch_array())
        return $res;
        else return json_encode(["responseCode" => 999, "message" => "weevil data not found"]);
    }

    function UserLogin($weevilname, $password) {
        // Password is now hashed; use password_verify via checkPassword().
        return checkPassword($weevilname, $password);
    }

	function adminRewardNestItem($weevilidx, $itemId, $cat, $configLoc) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$q = $db->prepare("INSERT INTO `weevilitems` (`weevilID`, `itemId`, `category`, `configName`) VALUES (?, ?, ?, ?)");
		$q->bind_param('ssss', $weevilidx, $itemId, $cat, $configLoc);
		$q->execute();

		$res = $q->get_result();

		if($q->affected_rows == 1)
		return true;
		else
		return false;
	}
	function adminRewardGardenItem($weevilName, $itemId) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$q = $db->prepare("INSERT INTO `gardenInventory` (`ownerName`, `itemid`, `itemtype`) VALUES (?, ?, 2)");
		$q->bind_param('ss', $weevilName, $itemId);
		$q->execute();

		$res = $q->get_result();

		if($q->affected_rows == 1)
		return true;
		else
		return false;
	}
    function GetJoinedWeevils($since){
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$q = $db->prepare("SELECT COUNT(*) FROM `users` WHERE `createdAt` > ?");
        $q->bind_param('s', $since);
        $q->execute();

        $res = $q->get_result();

        if($res = $res->fetch_array()) {
            return $res['0'];
        }
    }

    function getAdminLogs($limit, $offset) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$lim = intval($limit);
		$off = intval($offset);
		$q = $db->prepare("SELECT * FROM `game-logs` ORDER BY id DESC LIMIT ? OFFSET ?");
		$q->bind_param('ii', $lim, $off);
		$q->execute();

		$res = $q->get_result();
        
        if($res = $res->fetch_all()) {
            return $res;
        }
    }
    /* Admin panel functions */

	function generateSessionKey($len = 32)
	{
		// Cryptographically secure session id. Uses random_bytes(); falls back safely.
		try {
			return bin2hex(random_bytes(intdiv($len, 2) + ($len % 2)));
		} catch (Exception $e) {
			$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
			$ret = "";
			for($i = 0; $i < $len; $i++) $ret .= $chars[random_int(0, strlen($chars) - 1)];
			return $ret;
		}
	}

	function generateLogKey($len = 24)
	{
		// Strengthened login key (was 5 chars via rand()). Used by TCP login.
		try {
			return bin2hex(random_bytes(intdiv($len, 2) + ($len % 2)));
		} catch (Exception $e) {
			$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
			$ret = "";
			for($i = 0; $i < $len; $i++) $ret .= $chars[random_int(0, strlen($chars) - 1)];
			return $ret;
		}
	}

    function time_ago($timestamp)
    {
        $etime = time() - $timestamp;
    
        if ($etime < 1)
        {
            return 'just now';
        }
    
        $a = array(12 * 30 * 24 * 60 * 60  =>  'year', 30 * 24 * 60 * 60 => 'month', 24 * 60 * 60 => 'day', 60 * 60 => 'hour', 60 => 'minute', 1 => 'second');
    
        foreach ($a as $secs => $str)
        {
            $d = $etime / $secs;
            if ($d >= 1)
            {
                $r = round($d);
                return $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago';
            }
        }
    }

    function time_until($time, $until){
        $diff2 = 0;
        $seconds = 0;
        $minutes = 0;
        $hours = 0;
        $days = 0;
        if($until > $time){
            $diff = abs($until - $time);
            while ($diff2 < $diff){
                $diff2++;
                $seconds++;
                if($seconds > 59){
                    $seconds =0;
                    $minutes++;
                }
                if($minutes > 59){
                    $minutes = 0;
                    $hours++;
                }
                if($hours > 23){
                    $hours = 0;
                    $days++;
                }
            }
        }
        return json_encode(["days"=>$days, "hours"=>$hours, "minutes"=>$minutes, "seconds" => $seconds]);
        
    }
	
	function confirmSessionKey($username, $key)
	{
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$q = $db->prepare("SELECT active FROM users WHERE username = ? AND sessionKey = ? LIMIT 1;");
		$q->bind_param('ss', $username, $key);
		$q->execute();
		
		$res = $q->get_result();
		
		if($res = $res->fetch_array())
		{
			if((int)$res['active'] == 1) {
				$bannedUntil = json_decode(time_until(time(), $res['bannedUntil']));

				if($bannedUntil->days <= 0 && $bannedUntil->hours <= 0 && $bannedUntil->minutes <= 0 && $bannedUntil->seconds <= 0)
				return true;
			} // Checks if banned
		}

		return false;
	}
	
	function getLoginDetails()
	{
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
			
			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$a = $db->prepare("SELECT id, username, loginKey, tycoon FROM users WHERE username = ? LIMIT 1;");
				$a->bind_param('s', $_COOKIE['weevil_name']);
				$a->execute();
				
				$res = $a->get_result();
				
				if($res = $res->fetch_array()) {
					return "res=1&userName=" . $res['username'] . "&userIDX=" . $res['id'] . "&tycoon=" . $res['tycoon'] . "&tycoonTV=0&loginKey=" . $res['loginKey'];
				}
			}
		}
		
		return "res=999";
	}
	
	function getWeevilStats()
	{
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
			
			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1;");
				$q->bind_param('s', $_COOKIE['weevil_name']);
				$q->execute();
				
				$res = $q->get_result();
				
				if($res = $res->fetch_array())
				{
					$st = rand();
					$age = 0; // Don't count reg_date timestamp yet ...
					$canSpeak = compareChatState($res['canSpeak']);
					
					$hash = md5('P07aJK8soogA815CxjkTcA==' . arrayToString(array($res['level'], $res['mulch'], $res['dosh'], $res['xp'], $res['xp1'], $res['xp2'], (intval($res['food']) >= 100 ? '100' : $res['food']), $age, '', $res['activated'], '365', $canSpeak, '0', ($res['email'] == "" || $res['email'] == null ? '1' : '0'), ($res['email'] == "" || $res['email'] == null ? '0' : '1'), '0', $st)));
					return 'level=' . $res['level'] . '&mulch=' . $res['mulch'] . '&dosh=' . $res['dosh'] . '&xp=' . $res['xp'] . '&xp1=' . $res['xp1'] . '&xp2=' . $res['xp2'] . '&food=' . (intval($res['food']) >= 100 ? '100' : $res['food']) . '&age=' . $age . '&sex=&activated=' . $res['activated'] . '&daysRemaining=365&cs=' . $canSpeak . '&key=0&displayActivation=' . ($res['email'] == "" || $res['email'] == null ? '1' : '0') . '&email=' . ($res['email'] == "" || $res['email'] == null ? '0' : '1') . '&newsVersion=0&st=' . $st . '&hash=' . $hash;
				}
			}
		}
		
		return "res=999";
	}

	function compareChatState($curUnix) {
		if(intval($curUnix) != 0) {
			$mins = intval((intval($curUnix) - time()) / 60);
		
			if($mins <= 0)
			return "0";
			else if($mins >= 9999)
			return "9999";
			else
			return strval($mins);
		}

		return "0";
	}

	function weevilGetData($username) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1;");
				$q->bind_param('s', $username);
				$q->execute();
				
				$res = $q->get_result();

				if($res = $res->fetch_array())
				return json_encode(["responseCode" => 1, "weevil" => ["idx" => intval($res["id"]), "weevilDef" => strval($res["def"]), "level" => intval($res["level"]), "tycoon" => intval($res["tycoon"]), "lastLog" => "1970-01-01 00:00:00", "dateJoined" => "1970-01-01 00:00:00", "petIds" => null]]);
				else return json_encode(["responseCode" => 999, "message" => "weevil data not found"]);
			}
		}

		return json_encode(["responseCode" => 999, "message" => "Hahahaha"]);
	}

	function weevilData($username) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1;");
				$q->bind_param('s', $username);
				$q->execute();
				
				$res = $q->get_result();

				if($res = $res->fetch_array())
				return "res=1&idx=" . $res['id'] . "&weevilDef=" . $res['def'] . "&level=" . $res['level'] . "&tycoon=" . $res['tycoon'] . "&lastLog=1970-01-01 00:00:00&dateJoined=".gmdate("Y-m-d\ H:i:s\Z", $res['createdAt'])."&x=y";
			}
		}

		return "res=999";
	}

	function getAllWeevilStats($userIDX) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1;");
				$q->bind_param('s', $userIDX);
				$q->execute();
				
				$res = $q->get_result();

				if($res = $res->fetch_array())
				return $res;
				else return json_encode(["responseCode" => 999, "message" => "weevil data not found"]);
			}
		}

		return json_encode(["responseCode" => 999, "message" => "Hahahaha"]);
	}

	function getAllWeevilStatsByName($weevilName) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1;");
				$q->bind_param('s', $weevilName);
				$q->execute();
				
				$res = $q->get_result();

				if($res = $res->fetch_array())
				return $res;
				else return json_encode(["responseCode" => 999, "message" => "weevil data not found"]);
			}
		}

		return json_encode(["responseCode" => 999, "message" => "Hahahaha"]);
	}

	function updateStats($food) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("UPDATE users SET food = ? WHERE username = ?;");
				$q->bind_param('ss', $food, $_COOKIE['weevil_name']);
				$q->execute();

				if($q->affected_rows == 1) return "res=1&x=y";
			}
		}

		return "res=999";
	}

	function buyFood($energyValue, $cost) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				if(intval($cost) <= 0) {
					GrantBan(strtotime('+1 hour', time()), getAllWeevilStatsByName($_COOKIE['weevil_name'])["id"]);
					return "res=999";
				}

				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("UPDATE users SET food = food + ?, mulch = mulch - ? WHERE username = ?;");
				$q->bind_param('sss', $energyValue, $cost, $_COOKIE['weevil_name']);
				$q->execute();

				if($q->affected_rows == 1) {
					$q = $db->prepare("SELECT food, mulch FROM users WHERE username = ? LIMIT 1;");
					$q->bind_param('s', $_COOKIE['weevil_name']);
					$q->execute();

					$res = $q->get_result();

					if($res = $res->fetch_array()) {
						if(intval($res['food']) >= 100) {
							return "res=1&mulch=" . $res['mulch'] . "&food=100&success=1&x=y";
						}
						else return "res=1&mulch=" . $res['mulch'] . "&food=" . $res['food'] . "&success=1&x=y";
					}
				}
			}
		}

		return "res=999";
	}

	function isDefValid($weevilDef) {
		try {
			$clrs1 = [10027008,43520,153,10057472,8913032,11198463,26367,16750848,13421568,61166,13369548,16777215,16766429,11206400,16763904,15658496,16745604,2631720,10066329,16777145,15597568,26112,1184274,12733185,16736768,16425579,16767167,7620096,16771473,6394113,8899328,14548127,62720,11993014,25670,110971,61093,7011535,25219,50886,10289151,2797311,3014772,5243334,8334079,14138879,16729855,16756735,11338573,15597672,15952037,16757203];
			$clrs2 = [52224,4474077,15610675,13421568,52428,13369548,8943360,2136473,11206400,16763904,15658496,16745604,10027008,15597568,16766429,12733185,16736768,16425579,16767167,7620096,16750848,16771473,10057472,16777145,6394113,8899328,11206400,14548127,26112,43520,62720,11993014,25670,110971,61093,7011535,25219,50886,61166,10289151,153,26367,2797311,11198463,3014772,5243334,8334079,14138879,8913032,16729855,16756735,11338573,15597672,15952037,16757203,10066329,16777215,2631720];

			$ht = intval(substr($weevilDef, 0, 1));
			$hc = $clrs1[intval(substr($weevilDef, 1, 2))];
			$bt = intval(substr($weevilDef, 3, 1));
			$bc = $clrs1[intval(substr($weevilDef, 4, 2))];
			$et = intval(substr($weevilDef, 6, 1));
			$ec = $clrs2[intval(substr($weevilDef, 7, 2))];
			$lids = intval(substr($weevilDef, 9, 1));
			$at = intval(substr($weevilDef, 10, 2));
			$ac = $clrs1[intval(substr($weevilDef, 12, 2))];
			$lc = $clrs1[intval(substr($weevilDef, 14, 2))];
			$lt = intval(substr($weevilDef, 16, 2));

			// if there wasnt an error already, do other checks
			if(strlen($weevilDef) > 18 || strlen($weevilDef) != 18 || preg_match('/\D+/', $weevilDef) || $hc == 7 || $hc == 0 || $ht == 0 || $bc == 7 || $bc == 0 || $bt == 0 || $lc == 7 || $lc == 0 || $ac == 7 || $ac == 0 || $et == 0 || $weevilDef == "000000000000000000" || $ht > 4 || $bt > 4 || $et > 6 || $lids > 1 || $bc == 1184274 && $hc == 1184274 || $bc == 1184274 && $ac == 1184274 || $bc == 1184274 && $lc == 1184274 || $bc == 1184274 && $ec == 1184274 || $hc == 1184274 && $ec == 1184274 || $hc == 1184274 && $lc == 1184274 || $hc == 1184274 && $ac == 1184274 || $ac == 1184274 && $lc == 1184274 || $ec == 1184274 && $ac == 1184274 || $bc == 22 && $hc == 22 || $bc == 22 && $ac == 22 || $bc == 22 && $lc == 22 || $bc == 22 && $ec == 22 || $hc == 22 && $ec == 22 || $hc == 22 && $lc == 22 || $hc == 22 && $ac == 22 || $ac == 22 && $lc == 22 || $ec == 22 && $ac == 22 || $weevilDef == 322311313109222200 || $weevilDef == 322311313109172200) return false;

			return true;
		}
		catch(Exception $e) {
			return false;
		}
	}

	function changeDefinition($weevilDef) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
 
			if($loggedIn == true) {
				if(intval(substr($weevilDef, 10, 2)) >= 10 || intval(substr($weevilDef, 16, 2)) >= 2 || !isDefValid($weevilDef))
				return "responseCode=999";
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("UPDATE users SET def = ?, mulch = mulch - 150, xp = xp + 2 WHERE username = ?;");
				$q->bind_param('ss', $weevilDef, $_COOKIE['weevil_name']);
				$q->execute();

				if($q->affected_rows == 1) {
					$q = $db->prepare("SELECT mulch, xp FROM users WHERE username = ? LIMIT 1;");
					$q->bind_param('s', $_COOKIE['weevil_name']);
					$q->execute();

					$res = $q->get_result();

					if($res = $res->fetch_array()) return "responseCode=1&err=1&mulch=" . $res['mulch'] . "&xp=" . $res['xp'] . "&completedAchievements=";
				}
			}
		}

		return "responseCode=999";
	}

	function changeWeevilDefinition($weevilDef) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
 
			if($loggedIn == true) {
				$weevilStats = getAllWeevilStatsByName($_COOKIE['weevil_name']);
				if(intval($weevilStats['level']) < 30)
				return "responseCode=999";

				$admins = array('MARIO200', 'Jjs', 'Darkk');
				if(!isDefValid($weevilDef)) return "responseCode=999";
				if(!in_array($_COOKIE['weevil_name'], $admins)) {
					if(intval(substr($weevilDef, 10, 2)) == 18 || intval(substr($weevilDef, 16, 2)) == 7)
					return "responseCode=999";

					if(intval(substr($weevilDef, 10, 2)) == 53 && intval($weevilStats['level']) < 60 || intval(substr($weevilDef, 10, 2)) == 41 && intval($weevilStats['level']) < 60 || intval(substr($weevilDef, 16, 2)) == 39 && intval($weevilStats['level']) < 60)
					return "responseCode=999";
				}
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("UPDATE users SET def = ? WHERE username = ?;");
				$q->bind_param('ss', $weevilDef, $_COOKIE['weevil_name']);
				$q->execute();

				if($q->affected_rows == 1)
				return "responseCode=1";
			}
		}

		return "responseCode=999";
	}

	function submitScore($gameId, $score) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
            $weevilName = $_COOKIE['weevil_name'];

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				switch(intval($gameId)) {
					case 34:
						// mulch dig
						if(intval($score) < 0) {
							GrantBan(strtotime('+1 hour', time()), getAllWeevilStatsByName($weevilName)["id"]);
							return "responseCode=999";
						}

						$nscore = (intval($score) * 2 >= 3000 ? '3000' : strval(intval($score) * 2));
                        $curTime = time();
                        $weevilData = getAllWeevilStatsByName($weevilName);
                        if(hasUserPlayedGame($weevilName, $gameId) == true){
                            $game = getUserGameData($weevilName, $gameId);
                            if($game != false){
                                if(intval($curTime) > intval($game['lastplayed'] + (24 * 60 * 60))){
                                    if(intval($score) > intval($game['total'])){
                                        setNewHighscore($weevilName, $gameId, $score);
                                    }
                                    playGame($weevilName, $gameId, strval($curTime));
                                    addMulchByName($weevilName, $nscore);
                                }
                                else return 'responseCode=2';
                            }
                            else return "responseCode=999";
                        }
                        else if(createUserGame($weevilName, $gameId) == true){
                            setNewHighscore($weevilName, $gameId, $score);
                            playGame($weevilName, $gameId, strval($curTime));
                            addMulchByName($weevilName, $nscore);
                        }
                        else return "responseCode=999";
                        $q = $db->prepare("SELECT mulch, xp FROM users WHERE username = ? LIMIT 1;");
                        $q->bind_param('s', $_COOKIE['weevil_name']);
                        $q->execute();

                        $res = $q->get_result();

                        if($res = $res->fetch_array())
                        return "responseCode=1&mulchBalance=" . $res['mulch'] . "&xpBalance=" . $res['xp'] . "&itemReward=0&seedReward=0";
				}
			}
		}

		return "responseCode=999";
	}

	function setLocColour($locId, $colour) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $weevil = $_COOKIE['weevil_name'];
				$q = $db->prepare("UPDATE `nestinfo` SET `colour` = ? WHERE `Weevil` = ? AND `roomID` = ?");
				$q->bind_param('sss', $colour, $weevil, $locId);
				$q->execute();
			}
		}
		return "";
	}

	function getNestConfig($ownerName) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT * FROM nest WHERE `ownerName` = ? LIMIT 1;");
				$q->bind_param('s', $ownerName);
				$q->execute();

				$res = $q->get_result();

				if($res = $res->fetch_array()) {
					$q = $db->prepare("SELECT * FROM users WHERE `username` = ? LIMIT 1;");
					$q->bind_param('s', $ownerName);
					$q->execute();

					$res2 = $q->get_result();

					if($res2 = $res2->fetch_array()) {
						$arr = array_merge($res, $res2);
						
						if($arr == null) {
							$weevilInfo = getAllWeevilStatsByName($ownerName);
							$weevilIdx = $weevilInfo['id'];
							$q = $db->prepare("INSERT INTO `nest` (`ownerName`, `idx`) VALUES (?, ?)");
							$q->bind_param('ss', $ownerName, $weevilIdx);
							$q->execute();

							if($q->affected_rows > 0) {
								$q = $db->prepare("SELECT * FROM users WHERE `username` = ? LIMIT 1;");
								$q->bind_param('s', $ownerName);
								$q->execute();

								$res2 = $q->get_result();

								if($res2 = $res2->fetch_array()) {
									$arr = array_merge($res, $res2);
									return $arr;
								}
							}
						}
						else
						return $arr;
					}
				}
			}
		}

		return null;
	}

	function getNestState($ownerName) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$config = getNestConfig($ownerName);
				if($config != null)
				return "responseCode=1&err=OK&xp=" . $config['xp'] . "&score=" . $config['score'] . "&fuel=" . (intval($config['fuel']) >= 80000 ? '80000' : $config['fuel']) . "&lastUpdate=" . $config['lastUpdate'];
			}
		}

		return "responseCode=999";
	}

	function updateFuel($fuel) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("UPDATE nest SET fuel = ? WHERE ownerName = ?;");
				$q->bind_param('ss', $fuel, $_COOKIE['weevil_name']);
				$q->execute();

				if($q->affected_rows == 1) return "res=1&x=y";
			}
		}

		return "res=999";
	}

	function buyNestFuel($cost) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				if(intval($cost) <= 0) {
					GrantBan(strtotime('+1 hour', time()), getAllWeevilStatsByName($_COOKIE['weevil_name'])["id"]);
					return "res=999";
				}

				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("UPDATE users SET mulch = mulch - ? WHERE username = ?;");
				$q->bind_param('ss', $cost, $_COOKIE['weevil_name']);
				$q->execute();

				if($q->affected_rows == 1) {
					$nfuel = strval(intval($cost) * 500);
					$q = $db->prepare("UPDATE nest SET fuel = fuel + ? WHERE ownerName = ?;");
					$q->bind_param('ss', $nfuel, $_COOKIE['weevil_name']);
					$q->execute();

					if($q->affected_rows == 1) {
						$config = getNestConfig($_COOKIE['weevil_name']);
						if($config != null)
						return "res=1&success=1&fuel=" . (intval($config['fuel']) >= 80000 ? '80000' : $config['fuel']) . "&mulch=" . $config['mulch'] . "&x=y";
					}
				}
			}
		}

		return "res=999";
	}

	function getIgnoreList() {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT * FROM buddylist WHERE `ownerName` = ? LIMIT 1;");
				$q->bind_param('s', $_COOKIE['weevil_name']);
				$q->execute();

				$res = $q->get_result();

				if($res = $res->fetch_array())
				return "responseCode=1&result=" . substr($res['blockList'], 0, -1);
			}
		}

		return "responseCode=999";
	}

	function getIgnoreListDefs($weevils) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				if(strpos($weevils, ',') !== false) {
					$warray = array();
					
					foreach(explode(',', $weevils) as $weevil) {
						$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
						$q = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1;");
						$q->bind_param('s', $weevil);
						$q->execute();

						$res = $q->get_result();

						if($res = $res->fetch_array())
						array_push($warray, ["idx" => strval($res['id']), "userWeevilID" => $res['username'], "weevilDef" => $res['def'], "level" => strval($res['level']), "tycoon" => $res['tycoon'], "lastLog" => "1970-01-01 00:00:00"]);
					}

					return json_encode(["responseCode" => 1, "weevils" => $warray]);
				}
				else {
					$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
					$q = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1;");
					$q->bind_param('s', $weevils);
					$q->execute();

					$res = $q->get_result();

					if($res = $res->fetch_array())
					return json_encode(["responseCode" => 1, "weevils" => [json_decode(json_encode(["idx" => strval($res['id']), "userWeevilID" => $res['username'], "weevilDef" => $res['def'], "level" => strval($res['level']), "tycoon" => $res['tycoon'], "lastLog" => "1970-01-01 00:00:00"]))]]);
				}
			}
		}

		return json_encode(["responseCode" => 999]);
	}

	function addToIgnoreList($weevil, $attempt = false) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$name = strval($weevil . ',');
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("UPDATE buddylist SET blockList = CONCAT(blockList, ?) WHERE `ownerName` = ?;");
				$q->bind_param('ss', $name, $_COOKIE['weevil_name']);
				$q->execute();

				if($q->affected_rows == 1)
				return "err=1";
			}
		}

		return "err=999";
	}

	function removeFromIgnoreList($weevil) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$name = strval($weevil . ',');
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("UPDATE buddylist SET blockList = REPLACE(blockList, ?, '') WHERE `ownerName` = ?;");
				$q->bind_param('ss', $name, $_COOKIE['weevil_name']);
				$q->execute();

				if($q->affected_rows == 1)
				return "err=1";
			}
		}

		return "err=999";
	}

	function rewardCollectXp($total) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$ntotal = intval($total);
				if($ntotal < 0) {
					GrantBan(strtotime('+1 hour', time()), getAllWeevilStatsByName($_COOKIE['weevil_name'])["id"]);
					return "res=999";
				}

				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("UPDATE users SET xp = xp + ? WHERE username = ?;");
				$q->bind_param('ss', $ntotal, $_COOKIE['weevil_name']);
				$q->execute();

				if($q->affected_rows == 1) {
					$q = $db->prepare("SELECT xp FROM users WHERE username = ? LIMIT 1;");
					$q->bind_param('s', $_COOKIE['weevil_name']);
					$q->execute();

					$res = $q->get_result();
					if($res = $res->fetch_array())
					return "res=0&xp=" . $res['xp'] . "&err=1&x=y";
				}
			}
		}

		return "res=999";
	}

	function rewardCollectDosh($total) { // really mulch, not dosh
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$ntotal = intval($total);
				if($ntotal < 0) {
					GrantBan(strtotime('+1 hour', time()), getAllWeevilStatsByName($_COOKIE['weevil_name'])["id"]);
					return "res=999";
				}

				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("UPDATE users SET mulch = mulch + ? WHERE username = ?;");
				$q->bind_param('ss', $ntotal, $_COOKIE['weevil_name']);
				$q->execute();

				if($q->affected_rows == 1) {
					$q = $db->prepare("SELECT mulch FROM users WHERE username = ? LIMIT 1;");
					$q->bind_param('s', $_COOKIE['weevil_name']);
					$q->execute();

					$res = $q->get_result();
					if($res = $res->fetch_array())
					return "res=0&mulch=" . $res['mulch'] . "&err=1&x=y";
				}
			}
		}

		return "res=999";
	}

	function createBuddyListForWeevil($weevil) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT * FROM buddylist WHERE `ownerName` = ? LIMIT 1;");
				$q->bind_param('s', $weevil);
				$q->execute();

				$res = $q->get_result();

				if($res = $res->fetch_array())
				return true;
				else {
					$q = $db->prepare("INSERT INTO buddylist (`ownerName`) VALUES (?);");
					$q->bind_param('s', $weevil);
					$q->execute();

					if($q->affected_rows == 1)
					return true;
				}

		return false;
	}

	function addToBuddyListTable($weevils) {
		foreach($weevils as $weevil) {
			createBuddyListForWeevil($weevil);
		}
	}

	function addToApparelListTable($apparels) {
		foreach($apparels as $hat) {
			createApparelList($hat);
		}
	}

	function createApparelList($hatData) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$q = $db->prepare("INSERT INTO appareltypes ('apparelId', 'name', 'clr', 'description', 'price', 'minLevel') VALUES (?, ?, ?, ?, ?, ?)");
		$q->bind_param('ssssss', $hatData->id, $hatData->name, $hatData->clr, $hatData->descr, $hatData->price, $hatData->level);
		$q->execute();
	}

	function checkUserRooms($weevil) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $q = $db->prepare("SELECT * FROM nestinfo WHERE `Weevil` = ?;");
        $q->bind_param('s', $weevil);
        $q->execute();

        $res = $q->get_result();

        if($res = $res->fetch_all()) {
            return $res;
        }
        else {
            $weevInfo = getAllWeevilStatsByName($weevil);
            $weevilIDX = $weevInfo['id'];
            $q = $db->prepare("INSERT INTO `nestinfo` (`Weevil`, `locID`) VALUES (?, '4'), (?, '20'), (?, '5'), (?, '10'), (?, '50');");
			$q->bind_param('sssss', $weevil, $weevil, $weevil, $weevil, $weevil);
            $q->execute();

            if($q->affected_rows == 5)
            {
                $q = $db->prepare("INSERT INTO `nest` (`ownerName`, `idx`) VALUES (?, ?);");
				$q->bind_param('ss', $weevil, $weevilIDX);
                $q->execute();

                if($q->affected_rows == 1)
                {
                    $q = $db->prepare("SELECT * FROM nestinfo WHERE `Weevil` = ?;");
                    $q->bind_param('s', $weevil);
                    $q->execute();

                    $res = $q->get_result();

                    if($res = $res->fetch_all())
					return $res;
                }
            }
        }

		return false;
	}

	function getNestShopItems($tag,$storeName) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		// Bin Pets are adopted (customised at adoption), not slotted into the
		// normal category taxonomy — return all binPetShop stock regardless of
		// the requested category tag so the Bin Pet Shop always populates.
		// Shop currency split (2026-08-27): Binmart is Dosh-only, Nestco is
		// Mulch-only. The SWF posts an explicit storeName (Binmart.setStoreName
		// => "binmart", Nestco.setStoreName => "nestco"). In the DB every
		// department-store item carries shopType="nestco" (the storeName is a
		// client concept); the two stores are distinguished by `currency`. So
		// map storeName -> (shopType, currency) and filter on both. binPetShop
		// is exempt (returns all its stock). Any unknown storeName falls back
		// to the original category+shopType behaviour.
		$storeMap = array(
			'binmart' => array('shopType' => 'nestco', 'currency' => 'dosh'),
			'nestco'  => array('shopType' => 'nestco', 'currency' => 'mulch'),
		);
		if($storeName === 'binPetShop') {
			$q = $db->prepare("SELECT * FROM itemtype WHERE `canBuy` = 1 AND `shopType` = ? ORDER BY `ordering` DESC,`itemTypeID` DESC;");
			$q->bind_param('s', $storeName);
		} else if(isset($storeMap[$storeName])) {
			$map = $storeMap[$storeName];
			if($tag === 'featured') {
				// Featured set is the popular/featured items (proven query).
				$q = $db->prepare("SELECT * FROM itemtype WHERE `canBuy` = 1 AND `shopType` = ? AND `currency` = ? AND `category` = 666 ORDER BY `purchases` DESC LIMIT 30;");
				$q->bind_param('ss', $map['shopType'], $map['currency']);
			} else if(is_numeric($tag)) {
				// Served 2015 Nestco/BinMart SWFs post INTEGER category ids
				// (e.g. 57). Match the integer `category` column directly.
				// bind_param needs a variable by reference, so assign first.
				$tagInt = (int)$tag;
				$q = $db->prepare("SELECT * FROM itemtype WHERE `category` = ? AND `canBuy` = 1 AND `shopType` = ? AND `currency` = ? ORDER BY `ordering` DESC,`itemTypeID` DESC;");
				$q->bind_param('iss', $tagInt, $map['shopType'], $map['currency']);
			} else {
				// The 2015 Nestco/BinMart SWFs post human-readable MENU LABELS
				// ("nest items", "nestige", "bundles", "showroom", ...) that are
				// NOT db `tags` tokens. Translate them to tag sets. For unknown
				// labels fall back to a direct FIND_IN_SET on the literal tag.
				$labelMap = array(
					'nest items' => array('lounge','seating','bedroom','kitchen','bathroom','lighting','flooring','walls','wall stuff','ceilings','storage','shelving','tables','plants','toys','gadgets','other stuff'),
					'nestige'    => array('statues'),
					'bundles'    => array('sale'),
					'showroom'   => array('trophies'),
				);
				if(isset($labelMap[$tag])) {
					$toks = $labelMap[$tag];
					$conds = array();
					foreach($toks as $t) { $conds[] = 'FIND_IN_SET(?, `tags`)'; }
					$sql = 'SELECT * FROM itemtype WHERE ('.implode(' OR ', $conds).') AND `canBuy` = 1 AND `shopType` = ? AND `currency` = ? ORDER BY `ordering` DESC,`itemTypeID` DESC;';
					$q = $db->prepare($sql);
					$types = str_repeat('s', count($toks)).'ss';
					$args = array_merge($toks, array($map['shopType'], $map['currency']));
					$q->bind_param($types, ...$args);
				} else {
					$q = $db->prepare("SELECT * FROM itemtype WHERE FIND_IN_SET(?, `tags`) AND `canBuy` = 1 AND `shopType` = ? AND `currency` = ? ORDER BY `ordering` DESC,`itemTypeID` DESC;");
					$q->bind_param('sss', $tag, $map['shopType'], $map['currency']);
				}
			}
		} else {
			$q = $db->prepare("SELECT * FROM itemtype WHERE `category` = ? AND `canBuy` = 1 AND `shopType` = ? ORDER BY `ordering` DESC,`itemTypeID` DESC;");
			$q->bind_param('ss', $tag, $storeName);
		}
		$q->execute();

		$res = $q->get_result();

		if($res = $res->fetch_all())
		return $res;
	}

	function getPopularNestShopItems($tag,$storeName) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		// Shop currency split (2026-08-27): keep the "popular" tiles within the
		// same per-store currency as getNestShopItems so Binmart shows only
		// dosh-priced popular items and Nestco only mulch-priced ones. Map
		// storeName -> (shopType, currency); DB rows use shopType="nestco" for
		// both department stores, distinguished by `currency`.
		$storeMap = array(
			'binmart' => array('shopType' => 'nestco', 'currency' => 'dosh'),
			'nestco'  => array('shopType' => 'nestco', 'currency' => 'mulch'),
		);
		if(isset($storeMap[$storeName])) {
			$map = $storeMap[$storeName];
			if($tag === 'featured') {
				$q = $db->prepare("SELECT * FROM itemtype WHERE `shopType` = ? AND `currency` = ? AND `category` = 666 ORDER BY `purchases` DESC LIMIT 3;");
				$q->bind_param('ss', $map['shopType'], $map['currency']);
			} else if(is_numeric($tag)) {
				$tagInt = (int)$tag;
				$q = $db->prepare("SELECT * FROM itemtype WHERE `category` = ? AND `shopType` = ? AND `currency` = ? ORDER BY `purchases` DESC LIMIT 3;");
				$q->bind_param('iss', $tagInt, $map['shopType'], $map['currency']);
			} else {
				// Same label->tags translation as getNestShopItems (2015 SWF menu labels).
				$labelMap = array(
					'nest items' => array('lounge','seating','bedroom','kitchen','bathroom','lighting','flooring','walls','wall stuff','ceilings','storage','shelving','tables','plants','toys','gadgets','other stuff'),
					'nestige'    => array('statues'),
					'bundles'    => array('sale'),
					'showroom'   => array('trophies'),
				);
				if(isset($labelMap[$tag])) {
					$toks = $labelMap[$tag];
					$conds = array();
					foreach($toks as $t) { $conds[] = 'FIND_IN_SET(?, `tags`)'; }
					$sql = 'SELECT * FROM itemtype WHERE ('.implode(' OR ', $conds).') AND `shopType` = ? AND `currency` = ? ORDER BY `purchases` DESC LIMIT 3;';
					$q = $db->prepare($sql);
					$types = str_repeat('s', count($toks)).'ss';
					$args = array_merge($toks, array($map['shopType'], $map['currency']));
					$q->bind_param($types, ...$args);
				} else {
					$q = $db->prepare("SELECT * FROM itemtype WHERE FIND_IN_SET(?, `tags`) AND `shopType` = ? AND `currency` = ? ORDER BY `purchases` DESC LIMIT 3;");
					$q->bind_param('sss', $tag, $map['shopType'], $map['currency']);
				}
			}
		} else {
			$q = $db->prepare("SELECT * FROM itemtype WHERE `category` = ? AND `shopType` = ? ORDER BY `purchases` DESC LIMIT 3;");
			$q->bind_param('ss', $tag, $storeName);
		}
		$q->execute();

		$res = $q->get_result();

		if($res = $res->fetch_all())
		return $res;
	}

	function getItemDataById($id) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$q = $db->prepare("SELECT * FROM itemtype WHERE `itemTypeID` = ?;");
		$q->bind_param('s', $id);
		$q->execute();

		$res = $q->get_result();

		if($res = $res->fetch_array())
		return $res;

		return null;
	}

	function getItemDataByName($name) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$q = $db->prepare("SELECT * FROM itemtype WHERE `name` = ?;");
		$q->bind_param('s', $name);
		$q->execute();

		$res = $q->get_result();

		if($res = $res->fetch_array())
		return $res;

		return null;
	}

	function getGardenItemDataByName($name) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$q = $db->prepare("SELECT * FROM gardenItemType WHERE `name` = ?;");
		$q->bind_param('s', $name);
		$q->execute();

		$res = $q->get_result();

		if($res = $res->fetch_array())
		return $res;
	}

	function BuyItem($weevilidx, $itemId, $colour) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                if(!checkIfItemIsBuyable($itemId)) return false;
				$itemData = getItemDataById($itemId);
				$cat = $itemData['category'];
				$configLoc = $itemData['configLocation'];

				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("INSERT INTO `weevilitems`(`weevilID`, `itemId`, `colour`, `category`,`configName`) VALUES (?, ?, ?, ?, ?)");
				$q->bind_param('sssss', $weevilidx, $itemId, $colour, $cat, $configLoc);
				$q->execute();

				$res = $q->get_result();

				if($q->affected_rows == 1) {
                    $q = $db->prepare("UPDATE `itemtype` SET `purchases` = purchases + 1 WHERE `itemtype`.`itemTypeID` = ?;");
					$q->bind_param('s', $itemId);
                    $q->execute();
    
                    $res = $q->get_result();
    
                    if($q->affected_rows == 1)
					return true;
				}
			}
		}

		return false;
    }
    
    function checkIfItemIsBuyable($itemId) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT COUNT(*) FROM itemtype WHERE `itemTypeID` = ? AND `canBuy` = 1 AND `shopType` = 'binPetShop' OR `shopType` = 'nestco';");
				$q->bind_param('s', $itemId);
                $q->execute();

                $res = $q->get_result();

                if($res = $res->fetch_array()) {
                    if(intval($res['0']) > 0)
					return true;
                }
            }
        }

        return false;
    }

	function removeMulch($weevilId, $total) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("UPDATE `users` SET `mulch` = mulch - ? WHERE `id` = ?;");
				$q->bind_param('ss', $total, $weevilId);
				$q->execute();

				$res = $q->get_result();

				if($q->affected_rows == 1)
				return true;
			}
		}

		return false;
	}

	function removeDosh($weevilId, $total) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("UPDATE `users` SET `dosh` = dosh - ? WHERE `users`.`id` = ?;");
				$q->bind_param('ss', $total, $weevilId);
				$q->execute();

				$res = $q->get_result();

				if($q->affected_rows == 1)
				return true;
			}
		}

		return false;
	}

	function removeDoshByName($weevilName, $total) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("UPDATE `users` SET `dosh` = dosh - ? WHERE `users`.`username` = ?;");
				$q->bind_param('ss', intval($total), $weevilName);
				$q->execute();

				$res = $q->get_result();

				if($q->affected_rows == 1)
				return true;
			}
		}
		
		return false;
	}

	function addExperience($weevilId, $total) {
	    if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
	        $loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

	        if($loggedIn == true) {
	            $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	            // xp = lifetime (never decreases); xp1 = progress within the current
	            // prestige cycle, so banked XP actually drives leveling. Both advance
	            // together (see ROADMAP §9.1/§9.2 — lifetime vs banked/progression XP).
	            $t = intval($total);
	            $q = $db->prepare("UPDATE `users` SET `xp` = xp + ?, `xp1` = xp1 + ? WHERE `id` = ?;");
	            $q->bind_param('sss', $t, $t, $weevilId);
	            $q->execute();

	            $res = $q->get_result();

	            if($q->affected_rows == 1)
	            return true;
	        }
	    }

	    return false;
	}

	function addExperienceByName($weevilName, $total) {
	    if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
	        $loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

	        if($loggedIn == true) {
	            $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	            $t = intval($total);
	            $q = $db->prepare("UPDATE `users` SET `xp` = xp + ?, `xp1` = xp1 + ? WHERE `users`.`username` = ?;");
	            $q->bind_param('sss', $t, $t, $weevilName);
	            $q->execute();

	            $res = $q->get_result();

	            if($q->affected_rows == 1){
	                return true;
	            }
	        }
	    }

	    return false;
	}

	function addExperienceByNameMod($weevilName, $total) {
	    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	    $t = intval($total);
	    $q = $db->prepare("UPDATE `users` SET `xp` = xp + ?, `xp1` = xp1 + ? WHERE `users`.`username` = ?;");
	    $q->bind_param('sss', $t, $t, $weevilName);
	    $q->execute();

				$res = $q->get_result();

				if($q->affected_rows == 1){
					return true;
				}
		
		return false;
	}
	
	function addMulchByName($weevilName, $total) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("UPDATE `users` SET `mulch` = mulch + ? WHERE username = ?;");
				$q->bind_param('ss', intval($total), $weevilName);
				$q->execute();

				$res = $q->get_result();

				if($q->affected_rows == 1)
				return true;
			}
		}
		
		return false;
	}

	function addMulchByNameMod($weevilName, $total) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("UPDATE `users` SET `mulch` = mulch + ? WHERE username = ?;");
				$q->bind_param('ss', intval($total), $weevilName);
				$q->execute();

				$res = $q->get_result();

				if($q->affected_rows == 1)
				return true;
		
		return false;
	}
	
	function addDoshByName($weevilName, $total) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("UPDATE `users` SET `dosh` = dosh + ? WHERE `users`.`username` = ?;");
				$q->bind_param('ss', intval($total), $weevilName);
				$q->execute();

				$res = $q->get_result();

				if($q->affected_rows == 1)
				return true;
			}
		}
		
		return false;
	}
    
    function getXPDataByLevel($level) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$q = $db->prepare("SELECT * FROM levels WHERE `level` = ?;");
		$q->bind_param('s', $level);
		$q->execute();

		$res = $q->get_result();

        if($res = $res->fetch_array())
        return $res;
    }
    
    function levelWeevil($weevil) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
            $loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

            if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                // Grant EVERY legitimately-earned level in sequence (ROADMAP §9.1
                // multi-level catch-up), instead of one level per call. Each level
                // awards its trophy + alert. At the top of the 1-80 cycle we award
                // the Level 80 (prestige) reward and, if below the cap (prestige 13),
                // begin the next prestige cycle (fresh 1-80 progression behind the
                // permanent L80 badge). Difficulty scales 1 + prestige*0.5.
                $guard = 0;
                while($guard++ < 300) {
                    $currentData = getAllWeevilStatsByName($weevil);
                    if(!is_array($currentData)) break;
                    $mult = 1 + intval($currentData['prestige_count']) * 0.5;

                    if($currentData['xp1'] < $currentData['xp2']) break; // nothing due

                    if($currentData['level'] + 1 >= 80) {
                        // Entering / at the top of the cycle: award the L80 reward once.
                        if(intval($currentData['level']) < 80) {
                            rewardUserTrophy($weevil, $currentData['id'], 80);
                            $icon = 'cdn.binw.net/users/o_levelTrophy80_thumb.swf';
                            $alrtMsg = '<a href="event:weevil|'.strval($currentData['id']).'">'.$weevil.'</a> has reached level 80!';
                            sendAlert($weevil, $alrtMsg, $icon, time());
                        }

                        if(intval($currentData['prestige_count']) < 13) {
                            // Begin the next prestige cycle. CRITICAL: the previous
                            // code set xp1 = 0 here, which DESTROYED legitimate banked
                            // overflow earned above the Level-80 threshold. Instead we
                            // pay only the Level-80 threshold (xp1 - xp2) and KEEP the
                            // remaining banked XP so the loop below continues earning
                            // the fresh Prestige's levels in the same call (ROADMAP
                            // §9.2: "Do not reset legitimate overflow to zero simply
                            // because Prestige changed"). The new cycle starts at
                            // level 1 with its own (higher, multiplier-scaled) xp2.
                            $newMult = 1 + (intval($currentData['prestige_count']) + 1) * 0.5;
                            $next = getXPDataByLevel(2);
                            $newXP2 = $next ? intval($next['xpRequired']) * $newMult : 30 * $newMult;
                            $spend = $currentData['xp2'];
                            $q = $db->prepare("UPDATE `users` SET `prestige_count` = `prestige_count` + 1, `prestige_xp_base` = `xp`, `level` = 1, `xp1` = `xp1` - ?, `xp2` = ? WHERE `users`.`username` = ?;");
                            $q->bind_param('sss', $spend, $newXP2, $weevil);
                            $q->execute();
                            // Loop continues: remaining banked xp1 now earns new
                            // Prestige levels via the else branch below.
                        }
                        else {
                            // Max prestige reached: clamp at 80, consume the L80
                            // threshold, preserve any overflow above it.
                            $spend = $currentData['xp2'];
                            $q = $db->prepare("UPDATE `users` SET `level` = 80, `xp1` = `xp1` - ? WHERE `users`.`username` = ?;");
                            $q->bind_param('ss', $spend, $weevil);
                            $q->execute();
                            break;
                        }
                    }
                    else {
                        $newXP2row = getXPDataByLevel($currentData['level'] + 2);
                        $newXP = $newXP2row ? intval($newXP2row['xpRequired']) * $mult : 30 * $mult;
                        // Carry the overflow: xp1 keeps the banked XP above the
                        // threshold just spent, so a large banked grant levels the
                        // weevil all the way up in one call (ROADMAP §9.1 catch-up).
                        $spend = $currentData['xp2'];
                        $q = $db->prepare("UPDATE `users` SET `level` = `level` + 1, `xp1` = `xp1` - ?, `xp2` = ? WHERE `users`.`username` = ?;");
                        $q->bind_param('sss', $spend, $newXP, $weevil);
                        $q->execute();

                        $cd = getAllWeevilStatsByName($weevil);
                        rewardUserTrophy($weevil, $cd['id'], $cd['level']);
                        $icon = 'cdn.binw.net/users/o_levelTrophy'.strval($cd['level']).'_thumb.swf';
                        $alrtMsg = '<a href="event:weevil|'.strval($cd['id']).'">'.$weevil.'</a> has reached level '.strval($cd['level']).'!';
                        sendAlert($weevil, $alrtMsg, $icon, time());
                    }
                }
                return true;
            }
        }

        return false;
    }

    function itemCountById($itemId, $weevilId, $colour) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT COUNT(*) FROM weevilitems WHERE `weevilID` = ? AND `itemId` = ? AND `isInRoom` = 0 AND `colour` = ?;");
				$q->bind_param('sss', $weevilId, $itemId, $colour);
                $q->execute();

                $res = $q->get_result();

                if($res = $res->fetch_array())
                return $res['0'];
            }
        }

        return false;
    }

    function getItemTags($itemId) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM itemtype WHERE `itemTypeID` = ?;");
				$q->bind_param('s', $itemId);
                $q->execute();

                $res = $q->get_result();

                if($res = $res->fetch_array())
                return $res;
            }
        }

        return false;
    }

    function getWeevilItems($weevilId) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM weevilitems WHERE `weevilID` = ? AND `isInRoom` = 0 ORDER BY `ID` DESC;");
				$q->bind_param('s', $weevilId);
                $q->execute();

                $res = $q->get_result();
 
                if($res = $res->fetch_all())
                return $res;
            }
        }

        return false;
    }

    function getWeevilItemsById($weevilId, $itemId, $colour) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM weevilitems WHERE `weevilID` = ? AND `itemId` = ? AND `isInRoom` = 0 AND `colour` = ?;");
				$q->bind_param('sss', $weevilId, $itemId, $colour);
                $q->execute();

                $res = $q->get_result();

                if($res = $res->fetch_all())
                return $res;
            }
        }

        return false;
    }

    function GetItemDataByConfig($config) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM itemtype WHERE `configLocation` = ?;");
				$q->bind_param('s', $config);
                $q->execute();

                $res = $q->get_result();

                if($res = $res->fetch_array())
				return $res;
			}
		}

		return false;
    }

    function rewardUserTrophy($weevilname, $userIDX, $level) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
            $loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
            if($loggedIn == true) {

                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $configLoc = "o_levelTrophy". strval($level);
                $idata = GetItemDataByConfig($configLoc);
                $itemId = $idata['itemTypeID'];

                // Record the award as prestige-aware (ROADMAP §9.1): a fresh set of
                // level trophies is earnable once per prestige, while remaining
                // idempotent inside one prestige. The physical item is granted each
                // cycle (a new trophy), and the prestige_trophies row lets the game
                // prove/idempotency-check ownership per (user, prestige, level).
                $stats = getAllWeevilStatsByName($weevilname);
                $pc = is_array($stats) ? intval($stats['prestige_count']) : 0;
                $q = $db->prepare("INSERT INTO `prestige_trophies` (`weevil_id`, `username`, `prestige_count`, `level`) VALUES (?, ?, ?, ?)");
                $q->bind_param('isii', $userIDX, $weevilname, $pc, $level);
                $q->execute();

                $q = $db->prepare("INSERT INTO `weevilitems` (`weevilID`, `itemId`, `category`, `configName`) VALUES (?, ?, '993', ?)");
                $q->bind_param('sss', $userIDX, $itemId, $configLoc);
                $q->execute();

                $res = $q->get_result();

                if($q->affected_rows == 1)
                return true;
            }
        }

        return false;
    }

    function checkNest($weevilname, $nestID) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
			if($loggedIn == true) {

                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT COUNT(*) FROM `nest` WHERE `ownerName` = ? AND `idx` = ?;");
				$q->bind_param('ss', $weevilname, $nestID);
                $q->execute();

                $res = $q->get_result();

                if($res = $res->fetch_array()) {
                    if(intval($res['0']) > 0)
					return true;
                }
			}
		}

		return false;
    }

    function checkNestRoom($weevilname, $roomId) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT COUNT(*) FROM `nestinfo` WHERE `Weevil` = ? AND `roomID` = ?;");
				$q->bind_param('ss', $weevilname, $roomId);
                $q->execute();

                $res = $q->get_result();

                if($res = $res->fetch_array()) {
                    if(intval($res['0']) > 0)
					return true;
                }
			}
		}

		return false;
    }

    function addItemToNestRoom($room, $itemId, $position, $fid, $spot) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
			if($loggedIn == true) {
                if(GetWeevilItemCountByID($itemId) == 0){
                    GrantBan(strtotime('+1 day'));
                    return false;
                }
                $coolness = floor(getItemDataById(GetWeevilItemByID($itemId)['itemId'])['price'] / 10);
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE `weevilitems` SET `isInRoom` = '1', `position` = ?, `roomId` = ?, `fID` = ?, `spot` = ? WHERE `weevilitems`.`id` = ?;");
				$q->bind_param('sssss', $position, $room, $fid, $spot, $itemId);
                $q->execute();

                $res = $q->get_result();
                if($q->affected_rows == 1)
                {
                    addNestCoolness($coolness);
                    return true;
                }
			}
		}

		return false;
    }

    function getNestItemsInUse($roomId) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM `weevilitems` WHERE `roomId` = ?;");
				$q->bind_param('s', $roomId);
                $q->execute();

                $res = $q->get_result();
                if($res = $res->fetch_all(MYSQLI_NUM))
                return $res;
			}
		}

		return false;
	}
	
	function getGardenItemsInUse($weevilName) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM `gardenInventory` WHERE `ownerName` = ? AND `itemtype` = 2 AND `isInGarden` = 1;");
				$q->bind_param('s', $weevilName);
                $q->execute();

                $res = $q->get_result();
                if($res = $res->fetch_all(MYSQLI_NUM))
                return $res;
			}
		}
		
		return false;
	}

	function getGardenPlantsInUse($weevilName, $array = true) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM `gardenInventory` WHERE `ownerName` = ? AND `itemtype` = 1 AND `isInGarden` = 1;");
				$q->bind_param('s', $weevilName);
                $q->execute();

                $res = $q->get_result();
				if($array) {
					if($res = $res->fetch_all(MYSQLI_ASSOC))
                	return $res;
				}
				else {
					if($res = $res->fetch_all(MYSQLI_NUM))
                	return $res;
				}
			}
		}
		
		return false;
	}

    function updateItemPositionInNest($nest, $itemId, $position, $fid, $spot) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE `weevilitems` SET `position` = ?, `fID` = ?,  `spot` = ? WHERE `ID` = ? AND `weevilID` = ?;");
				$q->bind_param('sssss', $position, $fid, $spot, $itemId, $nest);
                $q->execute();

                $res = $q->get_result();
                if($q->affected_rows == 1)
				return true;
			}
		}

		return false;
    }

    function removeItemFromNest($nest, $itemId) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
			if($loggedIn == true) {
                if(GetWeevilItemCountByID($itemId) == 0){
                    GrantBan(strtotime('+1 day'));
                    return false;
                }
                $coolness = floor(getItemDataById(GetWeevilItemByID($itemId)['itemId'])['price'] / 10);
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE `weevilitems` SET `position` = 0, `fID` = 0, `spot` = 0, `roomId` = 0, `isInRoom` = 0 WHERE `ID` = ? AND `weevilID` = ?");
				$q->bind_param('ss', $itemId, $nest);
                $q->execute();

                $res = $q->get_result();
                if($q->affected_rows == 1)
                {
                    addNestCoolness(0 - $coolness);
                    return true;
                }
			}
		}

		return false;
    }

    function DoesUserOwnRoom($locId, $username) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT COUNT(*) FROM `nestinfo` WHERE `Weevil` = ? AND `locID` = ?;");
				$q->bind_param('ss', $username, $locId);
                $q->execute();

                $res = $q->get_result();

                if($res = $res->fetch_array()) {
                    if(intval($res['0']) > 0)
					return true;
                }
			}
		}

		return false;
    }

    function CheckRoomPrice($roomId) {
        $rooms = [
            1 => 4000,
            2 => 8000,
            3 => 4000,
            6 => 2000,
            9 => 3000,
            8 => 6000,
            7 => 3000,
            51 => 5000,
            52 => 5000,
            53 => 5000,
            54 => 5000,
            55 => 5000
        ];

        if($rooms[$roomId] != null)
		return $rooms[$roomId];
		else
		return null;
    }

    function CheckRoomType($id) {
        switch($id) {
            case 51:
                return "plaza";
                break;
            case 52:
                return "plaza";
                break;
            case 53:
                return "plaza";
                break;
            case 54:
                return "plaza";
                break;
            case 55:
                return "plaza";
                break;
            default:
                return "nest-room";
                break;
        }
    }

    function BuyRoom($weevilname, $locId) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
			if($loggedIn == true) {

                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

                $q = $db->prepare("INSERT INTO `nestinfo` (`Weevil`, `locID`) VALUES (?, ?);");
				$q->bind_param('ss', $weevilname, $locId);
                $q->execute();

                $res = $q->get_result();

                if($q->affected_rows == 1)
				return true;
			}
		}

		return false;
    }

    function GetBuyableHats() {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT a.id, a.minLevel, a.name, a.description, a.probability, a.price, a.tycoonOnly, c.hexcolour FROM apparelTypes a LEFT JOIN colourPalettes c ON a.paletteId = c.id WHERE a.isLive = 1;");
                $q->execute();

                $res = $q->get_result();
                if($res = $res->fetch_all(MYSQLI_ASSOC))
                return $res;
			}
		}

		return null;
    }

    function GetOwnedHats() {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
            $loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
            if($loggedIn == true) {
                $weevilName = $_COOKIE['weevil_name'];
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM `weevilhats` WHERE `ownerName` = ?;");
				$q->bind_param('s', $weevilName);
                $q->execute();

                $res = $q->get_result();
                if($res = $res->fetch_all())
                return $res;
            }
        }

        return false;
    }

	function getHatDataById($id) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$q = $db->prepare("SELECT * FROM apparelTypes WHERE id = ?;");
		$q->bind_param('s', $id);
		$q->execute();

		$res = $q->get_result();

		if($res = $res->fetch_array())
		return $res;
	}

    function checkIfUserOwnsHat($itemId, $colour) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
            $loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
            if($loggedIn == true) {
                $weevilName = $_COOKIE['weevil_name'];
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT COUNT(*) FROM `weevilhats` WHERE `ownerName` = ? AND `colour` = ? AND `apparelId` = ?;");
				$q->bind_param('sss', $weevilName, $colour, $itemId);
                $q->execute();

                $res = $q->get_result();
                if($res = $res->fetch_array()) {
                    if(intval($res['0']) > 0)
					return true;
                }
            }
        }

        return false;
    }

    function checkIfHatIsBuyable($itemId) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT COUNT(*) FROM apparelTypes WHERE id = ? AND isLive = 1;");
				$q->bind_param('s', $itemId);
                $q->execute();

                $res = $q->get_result();

                if($res = $res->fetch_array()) {
                    if(intval($res['0']) > 0)
					return true;
                }
            }
        }

        return false;
    }

    function BuyHat($itemId, $colour) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                if(!checkIfHatIsBuyable($itemId)) return false;
                $weevilName = $_COOKIE['weevil_name'];

				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("INSERT INTO `weevilhats` (`apparelId`, `ownerName`, `colour`) VALUES (?, ?, ?);");
				$q->bind_param('sss', $itemId, $weevilName, $colour);
				$q->execute();

				$res = $q->get_result();

				if($q->affected_rows == 1)
				return true;
			}
		}

		return false;
    }

    function getFriendXPLeaderboard() {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $weevilName = $_COOKIE['weevil_name'];
                $q = $db->prepare("SELECT `namesList` FROM buddylist WHERE `ownerName` = ?;");
				$q->bind_param('s', $weevilName);
                $q->execute();

                $res = $q->get_result();
 
                if($res = $res->fetch_array()) {
                    $friendData = "";
                    $resp = substr($res[0], 0, -1);
                    $friends = explode(",",$resp);
                    foreach($friends as $friend){
                        $data = getAllWeevilStatsByName($friend);
                        $friendData .= '"'.$friend.'",';
                    }
                    $friendData .= '"'.$weevilName.'"';
                    //$friendData = substr($friendData, 0, -1);
                    
                    $q = $db->prepare("SELECT * FROM `users` WHERE `username` IN ($friendData) ORDER BY `xp` DESC;");
                    $q->execute();

                    $res = $q->get_result();
                    if($res = $res->fetch_all())
                    return $res;
                    /*
                    $resp = substr($res[0], 0, -1);
                    return $friendData;*/
                }
            }
        }

        return false;
    }

    function getGlobalXPLeaderboard() {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM users ORDER BY `xp` DESC;");
                $q->execute();

                $res = $q->get_result();
 
                if($res = $res->fetch_all())
                return $res;
            }
        }

        return false;
    }

    function GetBuyableSeeds() {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM seeds WHERE `canBuy` = 1 ORDER BY `level` ASC;");
                $q->execute();

                $res = $q->get_result();
 
                if($res = $res->fetch_all(MYSQLI_ASSOC))
                return $res;
            }
        }

        return false;
    }

    function GetBuyableGardenItems() {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM gardenItemType WHERE `canBuy` = 1 ORDER BY `minLevel` ASC;");
                $q->execute();

                $res = $q->get_result();
 
                if($res = $res->fetch_all(MYSQLI_ASSOC))
                return $res;
            }
        }

        return false;
    }
    
	function getSeedDataById($id) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$q = $db->prepare("SELECT * FROM seeds WHERE `id` = ?;");
		$q->bind_param('s', $id);
		$q->execute();

		$res = $q->get_result();

		if($res = $res->fetch_array(MYSQLI_ASSOC))
		return $res;
    }

    function checkIfSeedIsBuyable($seedId) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT COUNT(*) FROM seeds WHERE `id` = ? AND `canBuy` = 1;");
				$q->bind_param('s', $seedId);
                $q->execute();

                $res = $q->get_result();

                if($res = $res->fetch_array()) {
                    if(intval($res['0']) > 0)
					return true;
                }
            }
        }

        return false;
    }
    
    function BuySeed($seedId, $amount) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                if(!checkIfSeedIsBuyable($seedId)) return false;
                $weevilName = $_COOKIE['weevil_name'];
                $insertData = "";
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                for ($x = 0; $x < $amount; $x++) {
                    $insertData .= "('$weevilName', '$seedId', '1'),";
                }
                $insertData = substr($insertData, 0, -1);
				$q = $db->prepare("INSERT INTO `gardenInventory` (`ownerName`, `itemid`, `itemtype`) VALUES $insertData;");
				$q->execute();

				$res = $q->get_result();

				if($q->affected_rows == $amount)
				return true;
			}
		}

		return false;
    }

    function getGardenItemDataById($id) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$q = $db->prepare("SELECT * FROM gardenItemType WHERE `itemTypeID` = ?;");
		$q->bind_param('s', $id);
		$q->execute();

		$res = $q->get_result();

		if($res = $res->fetch_array())
		return $res;
    }

    function checkIfGardenItemIsBuyable($itemId, $colour) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT COUNT(*) FROM gardenItemType WHERE `itemTypeID` = ? AND `canBuy` = 1 AND `defaultHexcolour` = ?;");
				$q->bind_param('ss', $itemId, $colour);
                $q->execute();

                $res = $q->get_result();

                if($res = $res->fetch_array()) {
                    if(intval($res['0']) > 0)
					return true;
                }
            }
        }

        return false;
    }

    function BuyGardenItem($itemId, $colour) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                if(!checkIfGardenItemIsBuyable($itemId, $colour)) return false;
                $weevilName = $_COOKIE['weevil_name'];
                $insertData = "";
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("INSERT INTO `gardenInventory` (`ownerName`, `itemid`, `itemtype`, `colour`) VALUES (?, ?, '2', ?);");
				$q->bind_param('sss', $weevilName, $itemId, $colour);
				$q->execute();

				$res = $q->get_result();

				if($q->affected_rows == 1)
				$q = $db->prepare("UPDATE `gardenItemType` SET `purchases` = purchases + 1 WHERE `gardenItemType`.`itemTypeID` = ?;");
				$q->bind_param('s', $itemId);
				$q->execute();
			
				$res = $q->get_result();
			
				if($q->affected_rows == 1)
				return true;
			}
		}

		return false;
    }

    function getWeevilGardenItems($weevilName, $type) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM gardenInventory WHERE `ownerName` = ? AND `isInGarden` = 0 AND `itemtype` = ? ORDER BY `ID` DESC;");
				$q->bind_param('ss', $weevilName, $type);
                $q->execute();

                $res = $q->get_result();
 
                if($res = $res->fetch_all())
                return $res;
            }
        }

        return false;
    }

    function gardenItemCountByType($itemId, $weevilName, $colour, $type) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT COUNT(*) FROM gardenInventory WHERE `ownerName` = ? AND `itemid` = ? AND `isInGarden` = 0 AND `colour` = ? AND `itemtype` = ?;");
				$q->bind_param('ssss', $weevilName, $itemId, $colour, $type);
                $q->execute();

                $res = $q->get_result();

                if($res = $res->fetch_array())
                return $res['0'];
            }
        }

        return false;
    }

    function getWeevilGardenItemsById($weevilName, $itemId, $colour, $type) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM gardenInventory WHERE `ownerName` = ? AND `itemid` = ? AND `isInGarden` = 0 AND `colour` = ? AND `itemtype` = ?;");
                $q->bind_param('ssss', $weevilName, $itemId, $colour, $type);
				$q->execute();

                $res = $q->get_result();

                if($res = $res->fetch_all())
                return $res;
            }
		}
		
        return false;
	}
	
	function getWeevilGardenItemsByItemId($weevilName, $itemId) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM gardenInventory WHERE `ownerName` = ? AND `id` = ?;");
				$q->bind_param('ss', $weevilName, $itemId);
                $q->execute();

                $res = $q->get_result();

                if($res = $res->fetch_array())
                return $res;
            }
		}
		
        return null;
	}

    function getSeedConfigById($itemId) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM seeds WHERE `id` = ?;");
				$q->bind_param('s', $itemId);
                $q->execute();

                $res = $q->get_result();

                if($res = $res->fetch_array())
                return $res;
            }
		}
		
        return false;
    }

    function getGardenItemConfigById($itemId) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM gardenItemType WHERE `itemTypeID` = ?;");
				$q->bind_param('s', $itemId);
                $q->execute();

                $res = $q->get_result();

                if($res = $res->fetch_array())
                return $res;
            }
		}
		
        return false;
	}

	function checkGardenForItemByTypeId($weevilName, $itemId) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM gardenInventory WHERE `ownerName` = ? AND `itemid` = ? AND `isInGarden` = 1");
				$q->bind_param('ss', $weevilName, $itemId);
				$q->execute();
				
				$res = $q->get_result();

                if($res = $res->fetch_array())
                return true;
            }
		}

		return false;
	}

	function addItemToGarden($weevilName, $itemId, $locId, $x, $z, $r) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE gardenInventory SET `isInGarden` = 1, `roomid` = ?, `x` = ?, `z` = ?, `r` = ? WHERE `ownerName` = ? AND `id` = ?");
				$q->bind_param('ssssss', $locId, $x, $z, $r, $weevilName, $itemId);
                $q->execute();
            }
		}
	}

	function removeItemFromGarden($weevilName, $itemId) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE gardenInventory SET `isInGarden` = 0, `roomid` = 0, `x` = 0, `z` = 0, `r` = 0 WHERE `ownerName` = ? AND `id` = ?");
				$q->bind_param('ss', $weevilName, $itemId);
                $q->execute();
            }
		}
	}

	function moveItemInGarden($weevilName, $itemId, $x, $z, $r) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE gardenInventory SET `x` = ?, `z` = ?, `r` = ? WHERE `ownerName` = ? AND `id` = ?");
				$q->bind_param('sssss', $x, $z, $r, $weevilName, $itemId);
                $q->execute();
            }
		}
	}

	function checkXZForGardenItem($weevilName, $x, $z) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT x, z FROM gardenInventory WHERE `ownerName` = ? AND `x` = ? AND `z` = ?");
				$q->bind_param('sss', $weevilName, $x, $z);
				$q->execute();
				
				$res = $q->get_result();

                if($res = $res->fetch_array())
                return count($res);
            }
		}

		return 0;
	}

	function checkPlantEarningTimer() {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT plantTimer FROM nest WHERE ownerName = ?");
				$q->bind_param('s', $_COOKIE['weevil_name']);
                $q->execute();

				$res = $q->get_result();

				if($res = $res->fetch_array()) {
					$timeUntil = json_decode(time_until(time(), $res['plantTimer']));

					if($timeUntil->seconds == 0)
					return true;
				}
            }
		}

		return false;
	}

	function updatePlantEarningTimer($time) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE nest SET plantTimer = ? WHERE ownerName = ?");
				$q->bind_param('ss', $time, $_COOKIE['weevil_name']);
                $q->execute();

				if($q->affected_rows == 1)
				return true;
            }
		}

		return false;
	}

	function addPlantToGarden($weevilName, $plantId, $x, $z, $r) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE gardenInventory SET `isInGarden` = 1, `plantedUnix` = ?, `x` = ?, `z` = ?, `r` = ? WHERE `ownerName` = ? AND `id` = ?");
				$q->bind_param('ssssss', strval(time()), $x, $z, $r, $weevilName, $plantId);
				$q->execute();
				
				if($q->affected_rows == 1) {
					if(checkPlantEarningTimer()) {
						updatePlantEarningTimer(strtotime('+5 seconds', time()));
						addExperienceByName($weevilName, 1);
					}
					$stats = getAllWeevilStatsByName($weevilName);
					return "res=1&message=Success&xp=" . $stats["xp"] . "&plantID=$plantId&x=y";
				}
            }
		}

		return "res=999&message=Could not add plant to garden.&xp=0";
	}

	function removePlantFromGarden($weevilName, $plantId) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE gardenInventory SET `isInGarden` = 0, `plantedUnix` = '', `x` = 0, `z` = 0, `r` = 0 WHERE `ownerName` = ? AND `id` = ?");
				$q->bind_param('ss', $weevilName, $plantId);
				$q->execute();
            }
		}
	}

	function movePlantInGarden($weevilName, $plantId, $x, $z, $r) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE gardenInventory SET `x` = ?, `z` = ?, `r` = ? WHERE `ownerName` = ? AND `id` = ?");
				$q->bind_param('sssss', $x, $z, $r, $weevilName, $plantId);
				$q->execute();
            }
		}
	}

	function checkXZForPlant($weevilName, $x, $z) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT x, z FROM gardenInventory WHERE `ownerName` = ? AND `x` = ? AND `z` = ?");
				$q->bind_param('sss', $weevilName, $x, $z);
				$q->execute();
				
				$res = $q->get_result();

                if($res = $res->fetch_array())
                return count($res);
            }
		}

		return 0;
	}

	function harvestPlantInGarden($weevilName, $plantId, $mulch, $xp, $cat, $unix) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				if($cat == 1) {
					$q = $db->prepare("DELETE FROM gardenInventory WHERE `ownerName` = ? AND `id` = ?");
					$q->bind_param('ss', $weevilName, $plantId);
				}
				else {
					$q = $db->prepare("UPDATE gardenInventory SET `harvestUnix` = ? WHERE `ownerName` = ? AND `id` = ?");
					$q->bind_param('sss', $unix, $weevilName, $plantId);
				}
				$q->execute();
				
				if($q->affected_rows == 1) {
					addMulchByName($weevilName, $mulch);
					addExperienceByName($weevilName, $xp);
					$stats = getAllWeevilStatsByName($weevilName);
					return "res=1&message=Success&mulch=" . $stats["mulch"] . "&xp=" . $stats["xp"] . "&plantID=$plantId&x=y";
				}
            }
		}

		return "res=999&message=Could not harvest plant.";
	}

	function waterPlantInGarden($weevilName, $plantId, $unix) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE gardenInventory SET `wateredUnix` = ? WHERE `ownerName` = ? AND `id` = ?");
				$q->bind_param('sss', $unix, $weevilName, $plantId);
				$q->execute();
            }
		}
    }

    function saveBusiness($roomId, $signColour, $signTextColour, $open) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $weevilName = $_COOKIE['weevil_name'];
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE nestinfo SET `busOpen` = ?, `signClr` = ?, `signTxtClr` = ? WHERE `Weevil` = ? AND `roomID` = ?");
				$q->bind_param('sssss', $open, $signColour, $signTextColour, $weevilName, $roomId);
                $q->execute();
				if($q->affected_rows == 1)
				return true;
            }
		}
		
		return false;
    }

    function SaveBusText($text, $roomId) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $weevilName = $_COOKIE['weevil_name'];
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE nestinfo SET `name` = ? WHERE `Weevil` = ? AND `roomID` = ?");
				$q->bind_param('sss', $text, $weevilName, $roomId);
                $q->execute();
				if($q->affected_rows == 1)
				return true;
            }
		}
		
		return false;
    }

    function checkValidColour($colour) {
        $colours = array("0", "10027008", "43520", "153", "10057472", "8913032", "11198463", "26367", "16750848", "13421568", "61166", "13369548", "16777215","16766429","11206400","16763904","15658496","16745604","2631720","10066329","16777145","15597568","26112");
        if(in_array($colour, $colours))
		return true;

        return false;
	}
	
	function checkNonPerishableDailyHarvestAll($weevilName) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT dailyHarvest FROM nest WHERE `ownerName` = ?");
				$q->bind_param('s', $weevilName);
				$q->execute();
				
				$res = $q->get_result();

                if($res = $res->fetch_array()) {
					if(explode(',', $res['dailyHarvest'])[0] == "0")
					return 0;
					else {
						$now = date('Y-m-d');
						$nextDay = date('Y-m-d', strtotime('+1 day', intval(explode(',', $res['dailyHarvest'])[0])));
						
						if($nextDay > $now)
						return strtotime('+1 day', intval(explode(',', $res['dailyHarvest'])[0])) - time();
						else
						return 0;
					}
				}
            }
		}
		
		return -1;
	}

	function checkPerishableDailyHarvestAll($weevilName) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT dailyHarvest FROM nest WHERE `ownerName` = ?");
				$q->bind_param('s', $weevilName);
				$q->execute();
				
				$res = $q->get_result();

                if($res = $res->fetch_array()) {
					if(explode(',', $res['dailyHarvest'])[1] == "0")
					return 0;
					else {
						$now = date('Y-m-d');
						$nextDay = date('Y-m-d', strtotime('+1 day', intval(explode(',', $res['dailyHarvest'])[1])));
						
						if($nextDay > $now)
						return strtotime('+1 day', intval(explode(',', $res['dailyHarvest'])[1])) - time();
						else
						return 0;
					}
				}
            }
		}
		
		return -1;
	}

	function updateDailyHarvestTimer($weevilName, $unix, $perish) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				if($perish) $q = $db->prepare("UPDATE nest SET `dailyHarvest` = CONCAT('', SPLIT_STR(`dailyHarvest`, ',', 1), ',$unix') WHERE `ownerName` = ?");
				else $q = $db->prepare("UPDATE nest SET `dailyHarvest` = CONCAT('', '$unix,', SPLIT_STR(`dailyHarvest`, ',', 2)) WHERE `ownerName` = ?");
				$q->bind_param('s', $weevilName);
				$q->execute();
				
				if($q->affected_rows == 1)
				return true;
            }
		}

		return false;
	}

	function harvestPerishablePlantInGarden($weevilName, $plantId, $mulch, $xp) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$stats = getAllWeevilStatsByName($weevilName);
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("DELETE FROM gardenInventory WHERE `ownerName` = ? AND `id` = ?");
				$q->bind_param('ss', $weevilName, $plantId);
				$q->execute();
				
				if($q->affected_rows == 1) {
					addMulchByName($weevilName, $mulch);
					addExperienceByName($weevilName, $xp);
					return $xp . "|" . ($stats["mulch"] + $mulch);
				}
            }
		}

		return "0|" . $stats["mulch"];
	}

	function harvestPerishablePlantsInGarden($weevilName, $itemarray) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$stats = getAllWeevilStatsByName($weevilName);
				$mulch = 0;
				$xp = 0;

				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("DELETE FROM gardenInventory WHERE `ownerName` = ? AND `id` IN (" . implode(',', array_column($itemarray, "plantID")) . ")");
				$q->bind_param('s', $weevilName);
				$q->execute();
				
				if($q->affected_rows > 0) {
					foreach($itemarray as $plantData) {
						$mulch = $mulch + $plantData["mulch"];
						$xp = $xp + $plantData["xp"];
					}

					addMulchByName($weevilName, $mulch);
					addExperienceByName($weevilName, $xp);
					return $xp . "|" . ($stats["mulch"] + $mulch);
				}
            }
		}

		return "0|" . $stats["mulch"];
	}

	function harvestNonPerishablePlantsInGarden($weevilName, $itemarray) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$stats = getAllWeevilStatsByName($weevilName);
				$mulch = 0;
				$xp = 0;

				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("UPDATE gardenInventory SET `harvestUnix` = ? WHERE `ownerName` = ? AND `id` IN (" . implode(',', array_column($itemarray, "plantID")) . ")");
				$q->bind_param('ss', strval(time()), $weevilName);
				$q->execute();
				
				if($q->affected_rows > 0) {
					foreach($itemarray as $plantData) {
						$mulch = $mulch + $plantData["mulch"];
						$xp = $xp + $plantData["xp"];
					}

					addMulchByName($weevilName, $mulch);
					addExperienceByName($weevilName, $xp);
					return $xp . "|" . ($stats["mulch"] + $mulch);
				}
            }
		}

		return "0|" . $stats["mulch"];
	}

	function harvestNonPerishablePlantInGarden($weevilName, $plantId, $mulch, $xp) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$stats = getAllWeevilStatsByName($weevilName);
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("UPDATE gardenInventory SET `harvestUnix` = ? WHERE `ownerName` = ? AND `id` = ?");
				$q->bind_param('sss', strval(time()), $weevilName, $plantId);
				$q->execute();
				
				if($q->affected_rows == 1) {
					addMulchByName($weevilName, $mulch);
					addExperienceByName($weevilName, $xp);
					return $xp . "|" . ($stats["mulch"] + $mulch);
				}
            }
		}

		return "0|" . $stats["mulch"];
	}

	function getGardenPlotPrices($curSize) {
		switch(intval($curSize)) {
			case 1:
				return ["2" => 16000, "3" => 38000, "4" => 68000, "5" => 108000];
			case 2:
				return ["3" => 22000, "4" => 52000, "5" => 92000];
			case 3:
				return ["4" => 30000, "5" => 70000];
			case 4:
				return ["5" => 40000];
			default:
				return null;
		}
	}

	function upgradeGardenPlot($weevilName, $newSize) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("UPDATE nest SET `gardenSize` = ? WHERE `ownerName` = ?");
				$q->bind_param('ss', $newSize, $weevilName);
				$q->execute();
				
				if($q->affected_rows == 1)
				return true;
            }
		}

		return false;
    }
    
    function getAllWeevilStatsByNameBot($weevilName) {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $q = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1;");
        $q->bind_param('s', $weevilName);
        $q->execute();
        
        $res = $q->get_result();

        if($res = $res->fetch_array())
        return $res;
        else return json_encode(["responseCode" => 999, "message" => "weevil data not found"]);
    }

    function getRedeemableCodes() {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $q = $db->prepare("SELECT * FROM rewardCodes WHERE redeemable = 1;");
        $q->execute();
        
        $res = $q->get_result();

        if($res = $res->fetch_all())
        return $res;
        else return json_encode(["responseCode" => 999, "message" => "unable to grab codes"]);
    }

    function checkIfCodeIsValid($code) {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $q = $db->prepare("SELECT COUNT(*) FROM rewardCodes WHERE redeemable = 1 AND code = ? AND `quantity` != 0;");
		$q->bind_param('s', $code);
        $q->execute();
        
        $res = $q->get_result();

        if($res = $res->fetch_array()){
            if(intval($res['0']) > 0){
                return true;
            }
            else
            return false;
        }
        else return false;
    }

    function canWeevilRedeem($id, $code) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT COUNT(*) FROM redeemedCodes WHERE useridx = ? AND code = ?;");
				$q->bind_param('ss', $id, $code);
                $q->execute();
                
                $res = $q->get_result();

                if($res = $res->fetch_array()){
                    if(intval($res['0']) == 0){
                        return true;
                    }
                    else
                    return false;
                }
                else return false;
            }
            else return false;
        }
        else return false;
    }

    function redeemCode($id, $code) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("INSERT INTO `redeemedCodes` (`useridx`, `code`) VALUES (?, ?)");
				$q->bind_param('ss', $id, $code);
                $q->execute();

                if($q->affected_rows == 1) {
                    $q = $db->prepare("UPDATE `rewardCodes` SET `quantity` = quantity - 1 WHERE `code` = ?;");
					$q->bind_param('s', $code);
                    $q->execute();
                    if($q->affected_rows == 1)
                    return true;
                }
            }
		}

		return false;
    }

    function getCodeRewards($code) {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $q = $db->prepare("SELECT * FROM rewardCodes WHERE redeemable = 1 AND code = '$code';");
		$q->bind_param('s', $code);
        $q->execute();
        
        $res = $q->get_result();

        if($res = $res->fetch_array()){
            return $res;
        }
        else return false;
    }

    function rewardItem($weevilidx, $itemId, $colour = 0) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$itemData = getItemDataById($itemId);
				$cat = $itemData['category'];
				$configLoc = $itemData['configLocation'];
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("INSERT INTO `weevilitems` (`weevilID`, `itemId`, `category`, `configName`, `colour`) VALUES (?, ?, ?, ?, ?)");
                $q->bind_param('sssss', $weevilidx, $itemId, $cat, $configLoc, $colour);
				$q->execute();

				$res = $q->get_result();

				if($q->affected_rows == 1)
				return true;
			}
		}

		return false;
    }

    function rewardSeed($seedId) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $weevilName = $_COOKIE['weevil_name'];
                $insertData = "";
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("INSERT INTO `gardenInventory` (`ownerName`, `itemid`, `itemtype`) VALUES (?, ?, 1)");
				$q->bind_param('ss', $weevilName, $seedId);
				$q->execute();

				$res = $q->get_result();

				if($q->affected_rows == 1)
				return true;
			}
		}

		return false;
    }

    function rewardGardenItem($itemId) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $weevilName = $_COOKIE['weevil_name'];
                $insertData = "";
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("INSERT INTO `gardenInventory` (`ownerName`, `itemid`, `itemtype`) VALUES (?, ?, '2');");
				$q->bind_param('ss', $weevilName, $itemId);
				$q->execute();

				$res = $q->get_result();

				if($q->affected_rows == 1)
				return true;
			}
		}

		return false;
    }

    function hasUserPlayedGame($weevil, $game) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT COUNT(*) FROM weevilGames WHERE weevil = ? AND game = ?;");
				$q->bind_param('ss', $weevil, $game);
                $q->execute();
                
                $res = $q->get_result();

                if($res = $res->fetch_array()) {
                    if(intval($res['0']) > 0)
					return true;
                }
            }
        }

		return false;
    }

    function getUserGameData($weevil, $game) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM weevilGames WHERE weevil = ? AND game = ?");
				$q->bind_param('ss', $weevil, $game);
                $q->execute();
                
                $res = $q->get_result();

                if($res = $res->fetch_array())
				return $res;
            }
        }

		return false;
    }

    function setNewHighscore($weevil, $game, $score = 0, $key = '', $lap1 = 0, $lap2 = 0, $lap3 = 0) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE `weevilGames` SET `total` = ?, `lap1` = ?, `lap2` = ?, `lap3` = ?, `key` = ? WHERE `weevil` = ? AND `game` = ?;");
				$q->bind_param('sssssss', $score, $lap1, $lap2, $lap3, $key, $weevil, $game);
                $q->execute();
                if($q->affected_rows == 1)
                return true;
                else{
                    return false;
                }
            }
            else return false;
		}

		return false;
    }

    function createUserGame($weevil, $game, $key = '') {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $weevilName = $_COOKIE['weevil_name'];
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("INSERT INTO `weevilGames` (`weevil`, `game`, `key`) VALUES (?, ?, ?);");
				$q->bind_param('sss', $weevil, $game, $key);
				$q->execute();

				$res = $q->get_result();

				if($q->affected_rows == 1)
				return true;
			}
		}

		return false;
    }

    function playGame($weevil, $game, $lastplayed) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE `weevilGames` SET `plays` = `plays` + 1, `lastplayed` = ? WHERE `weevil` = ? AND `game` = ?");
				$q->bind_param('sss', $lastplayed, $weevil, $game);
                $q->execute();
                if($q->affected_rows == 1)
                return true;
            }
		}

		return false;
    }

    function getFriendGameLeaderboard($gameId) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $weevilName = $_COOKIE['weevil_name'];
                $q = $db->prepare("SELECT `namesList` FROM buddylist WHERE `ownerName` = ?;");
				$q->bind_param('s', $weevilName);
                $q->execute();

                $res = $q->get_result();
 
                if($res = $res->fetch_array()){
                    $friendData = "";
                    $resp = substr($res[0], 0, -1);
                    $friends = explode(",",$resp);
                    foreach($friends as $friend){
                        $data = getAllWeevilStatsByName($friend);
                        if(hasUserPlayedGame($data['username'], $gameId) == false){
                            createUserGame($data['username'], $gameId);
                        }
                        $friendData .= '"'.$friend.'",';
                    }
                    $friendData .= '"'.$weevilName.'"';
                    //$friendData = substr($friendData, 0, -1);
                    
                    $q = $db->prepare("SELECT * FROM `weevilGames` WHERE `weevil` IN ($friendData) AND `game` = ? ORDER BY `total` DESC;");
					$q->bind_param('s', $gameId);
                    $q->execute();

                    $res = $q->get_result();
                    if($res = $res->fetch_all())
                    return $res;
                }
            }
        }

        return false;
    }

    function GetAllNightClubItems($type) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM `itemtypets` WHERE canBuy = 1 AND internalCategory = ?");
				$q->bind_param('s', $type);
                $q->execute();
                $res = $q->get_result();
                if($res = $res->fetch_all())
                return $res;
            }
		}

		return false;
    }
    
	function getNightclubItemDataById($id) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$q = $db->prepare("SELECT * FROM itemtypets WHERE `itemTypeID` = ?;");
		$q->bind_param('s', $id);
		$q->execute();

		$res = $q->get_result();

		if($res = $res->fetch_array())
		return $res;
    }
    
    function BuyNightclubItem($weevil, $itemId, $colour, $type) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                if(!checkIfNightclubItemIsBuyable($itemId, $type))
				return false;

				$itemData = getNightclubItemDataById($itemId);
				$configLoc = $itemData['fileName'];
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("INSERT INTO `weevilitems` (`weevilID`, `itemId`, `colour`, `internalCategory`, `configName`) VALUES (?, ?, ?, ?, ?)");
				$q->bind_param('sssss', $weevil, $itemId, $colour, $type, $configLoc);
                $q->execute();

				$res = $q->get_result();

				if($q->affected_rows == 1)
				$q = $db->prepare("UPDATE `itemtypets` SET `purchases` = purchases + 1 WHERE `itemtypets`.`itemTypeID` = ?;");
				$q->bind_param('s', $itemId);
				$q->execute();
			
				$res = $q->get_result();
			
				if($q->affected_rows == 1)
				return true;
			}
		}

		return false;
    }

    function checkIfNightclubItemIsBuyable($itemId, $type) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT COUNT(*) FROM itemtypets WHERE `itemTypeID` = ? AND `canBuy` = 1 AND `inventoryCategory` = ?;");
				$q->bind_param('ss', $itemId, $type);
                $q->execute();

                $res = $q->get_result();

                if($res = $res->fetch_array()){
                    if(intval($res['0']) > 0)
					return true;
                }
            }
        }

        return false;
    }

    function getBuddyPosts($weevilName, $offset) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT `namesList` FROM buddylist WHERE `ownerName` = ?;");
				$q->bind_param('s', $weevilName);
                $q->execute();

                $res = $q->get_result();
 
                if($res = $res->fetch_array()){
                    $friendData = "";
                    $resp = substr($res[0], 0, -1);
                    $friends = explode(",",$resp);
                    foreach($friends as $friend){
                        $data = getAllWeevilStatsByName($friend);
                        if(hasUserPlayedGame($data['username'], $gameId) == false){
                            createUserGame($data['username'], $gameId);
                        }
                        $friendData .= '"'.$friend.'",';
                    }
                    $friendData = substr($friendData, 0, -1);
                    
                    $q = $db->prepare("SELECT * FROM `buddyAlerts` WHERE `weevil` IN ($friendData) ORDER BY `id` DESC LIMIT 3 OFFSET {$offset};");
                    $q->execute();

                    $res = $q->get_result();
                    if($res = $res->fetch_all())
                    return $res;
                }
            }
        }

        return false;
    }

    function sendAlert($weevilName, $message, $config, $time) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("INSERT INTO `buddyAlerts` (`weevil`, `message`, `iconPath`, `time`) VALUES (?, ?, ?, ?)");
				$q->bind_param('ssss', $weevilName, $message, $config, $time);
                $q->execute();

				if($q->affected_rows == 1)
				return true;
            }
        }

        return false;
    }

	function sendBusiness($weevilName, $typeID, $name, $currencyType) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("INSERT INTO `tycoonBusinesses` (`businessType`, `businessName`, `belongsTo`, `currencyType`) VALUES (?, ?, ?, ?)");
				$q->bind_param('ssss', $typeID, $name, $weevilName, $currencyType);
                $q->execute();

				if($q->affected_rows == 1)
				return true;
			}
		}

		return false;
	}

	function grabBusinesses($weevilName) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM `tycoonBusinesses` WHERE `belongsTo` = ?");
				$q->bind_param('s', $weevilName);
                $q->execute();

				$res = $q->get_result();

				if($res = $res->fetch_all()) {
					$businessInfo = [];
					$businessNames = [];

					foreach($res as $business) {
						array_push($businessNames, $business[2]);
						array_push($businessInfo, ['businessType' => $business[1], 'businessName' => $business[2], 'currencyType' => ($business[4] == 0 ? "Mulch" : "Dosh"), 'total' => $business[5], 'collected' => $business[6]]);
					}

					return ['businessInfo' => $businessInfo, $businessNames];
				}
			}
		}

		return null;
	}

	function updateBusinessCollected($weevilName, $businessType, $nTotal) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE tycoonBusinesses SET `collected` = `collected` + ? WHERE `belongsTo` = ? AND `businessType` = ?");
				$q->bind_param('sss', $nTotal, $weevilName, $businessType);
                $q->execute();

				if($q->affected_rows == 1)
				return true;
			}
		}

		return false;
	}

	function giveBusinessEarnings($weevilName, $business, $nTotal) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE `tycoonBusinesses` SET total = total + ? WHERE belongsTo = ? AND businessName = ?");
				$q->bind_param('sss', $nTotal, $weevilName, $business);
                $q->execute();

				if($q->affected_rows == 1)
				return true;
			}
		}

		return false;
	}

	function getTrackDetails($track) {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $q = $db->prepare("SELECT * FROM trackDetails WHERE `trackID` = ?;");
		$q->bind_param('s', $track);
        $q->execute();

        $res = $q->get_result();

        if($res = $res->fetch_array())
		return $res;
    }

    function hasUserPlayedMultiplayerGame($weevil, $game) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT COUNT(*) FROM multiplayerGames WHERE weevil = ? AND game = ?;");
				$q->bind_param('ss', $weevil, $game);
                $q->execute();
                
                $res = $q->get_result();

                if($res = $res->fetch_array()) {
                    if(intval($res['0']) > 0) {
                        return true;
                    }
                    else
                    return false;
                }
                else return false;
            }
            else return false;
        }
        else return false;
    }

    function getUserMultiplayerGameData($weevil, $game) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM multiplayerGames WHERE weevil = ? AND game = ?;");
				$q->bind_param('ss', $weevil, $game);
                $q->execute();
                
                $res = $q->get_result();

                if($res = $res->fetch_array()) {
                    return $res;
                }
                else return false;
            }
            else return false;
        }
        else return false;
    }

    function createUserMultiplayerGame($weevil, $game, $key = '') {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $weevilName = $_COOKIE['weevil_name'];
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("INSERT INTO `multiplayerGames` (`weevil`, `game`, `gamekey`) VALUES (?, ?, ?);");
				$q->bind_param('sss', $weevil, $game, $key);
				$q->execute();

				$res = $q->get_result();

				if($q->affected_rows == 1)
				return true;
			}
		}

		return false;
    }

    function setNewGameKeyMultiplayer($weevil, $game, $key) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE `multiplayerGames` SET `gamekey` = ? WHERE `weevil` = ? AND `game` = ?;");
				$q->bind_param('sss', $key, $weevil, $game);
                $q->execute();
                if($q->affected_rows == 1)
                return true;
            }
		}

		return false;
    }

    function setNewGameKey($weevil, $game, $key) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE `weevilGames` SET `key` = ? WHERE `weevil` = ? AND `game` = ?;");
				$q->bind_param('sss', $key, $weevil, $game);
                $q->execute();
                if($q->affected_rows == 1)
                return true;
            }
		}

		return false;
    }

    function playGameMultiplayer($weevil, $game, $lastplayed, $win, $lose) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE `multiplayerGames` SET `lastplayed` = ?, `wins` = wins + ?, `losses` = losses + ? WHERE `weevil` = ? AND `game` = ?");
				$q->bind_param('sssss', $lastplayed, $win, $lose, $weevil, $game);
                $q->execute();
                if($q->affected_rows == 1)
                return true;
            }
		}

		return false;
    }

    function GetMultiplayerDataByKey($key) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM multiplayerGames WHERE gamekey = ?");
				$q->bind_param('s', $key);
                $q->execute();
                
                $res = $q->get_result();

                if($res = $res->fetch_array()) {
                    return $res;
                }
                else return false;
            }
            else return false;
        }
        else return false;
    }

    function CheckForExistingGameReward($user) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT COUNT(*) FROM `game-rewards` WHERE idx = ?");
				$q->bind_param('s', intval($user));
                $q->execute();
                
                $res = $q->get_result();

                if($res = $res->fetch_array()) {
                    if(intval($res['0']) < 1) {
                        $q = $db->prepare("INSERT INTO `game-rewards` (`idx`) VALUES (?)");
						$q->bind_param('s', intval($user));
				        $q->execute();

				        $res = $q->get_result();

				        if($q->affected_rows == 1)
						return true;
                    }
                }
            }
        }

		return false;
    }

    function getSeedRewardData($user) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT tinkSeed FROM `game-rewards` WHERE idx = ?");
				$q->bind_param('s', intval($user));
                $q->execute();
                
                $res = $q->get_result();

                if($res = $res->fetch_array())
				return $res;
            }
        }

		return false;
    }

	function getAreaRewardData($user) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM `game-rewards` WHERE idx = ?");
				$q->bind_param('s', intval($user));
                $q->execute();
                
                $res = $q->get_result();

                if($res = $res->fetch_array())
				return $res;
            }
        }

		return false;
	}

    function GrantBan($time, $id, $api = "") {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE `users` SET bannedUntil = ? WHERE id = ?");
				$q->bind_param("ss", $time, $id);
                $q->execute();

                if($q->affected_rows == 1) {
					$q = $db->prepare("INSERT INTO `game-logs` (`weevilName`, `apiExecuted`, `bannedUntil`, `bannedFrom`, `type`) VALUES (?, ?, ?, ?, ?)");
					$q->bind_param('sssss', $_COOKIE['weevil_name'], $api, strval($time), strval(time()), 1);
					$q->execute();
					return true;
				}
            }
		}

		return false;
    }

    function setNewRewardTimeTinks($time, $id) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE `game-rewards` SET tinkSeed = ? WHERE idx = ?");
				$q->bind_param('ss', intval($time), intval($id));
                $q->execute();

                if($q->affected_rows == 1)
                return true;
            }
		}

		return false;
    }

	function setNewRewardTimeGam($time, $id) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE `game-rewards` SET castleMulch = ? WHERE idx = ?");
				$q->bind_param('ss', intval($time), intval($id));
                $q->execute();

                if($q->affected_rows == 1)
                return true;
            }
		}

		return false;
    }

	function setNewRewardTimeDoshs($time, $id) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE `game-rewards` SET doshMulch = ? WHERE idx = ?");
				$q->bind_param('ss', intval($time), intval($id));
                $q->execute();

                if($q->affected_rows == 1)
                return true;
            }
		}

		return false;
    }

	function setNewRewardTimeFling($time, $id) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE `game-rewards` SET flingXp = ? WHERE idx = ?");
				$q->bind_param('ss', intval($time), intval($id));
                $q->execute();

                if($q->affected_rows == 1)
                return true;
            }
		}

		return false;
    }

	function setNewRewardTimeFlumsXp($time, $id) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE `game-rewards` SET flumsXp = ? WHERE idx = ?");
				$q->bind_param('ss', intval($time), intval($id));
                $q->execute();

                if($q->affected_rows == 1)
                return true;
            }
		}

		return false;
    }
	function setNewRewardTimeFlumsMulch($time, $id) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE `game-rewards` SET flumsMulch = ? WHERE idx = ?");
				$q->bind_param('ss', intval($time), intval($id));
                $q->execute();

                if($q->affected_rows == 1)
                return true;
            }
		}

		return false;
    }

	function isItemRewardableById($itemId) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$weevilStats = getAllWeevilStatsByName($_COOKIE['weevil_name']);
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT canReward FROM `itemtype` WHERE itemTypeID = ?");
				$q->bind_param('s', intval($itemId));
                $q->execute();

				$res = $q->get_result();

				if($res = $res->fetch_array()) {
					if(intval($res['canReward']) == 1)
					return true;
				}
            }
		}

		return false;
	}

	function doesUserHaveNestItemById($itemId) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$weevilStats = getAllWeevilStatsByName($_COOKIE['weevil_name']);
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT COUNT(*) FROM `weevilitems` WHERE itemId = ? AND weevilID = ?");
				$q->bind_param('ss', intval($itemId), $weevilStats["id"]);
                $q->execute();

				$res = $q->get_result();

				if($res = $res->fetch_array()) {
					if(intval($res['0']) > 0)
					return true;
				}
            }
		}

		return false;
	}

	function checkPlazaEarningTimer() {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT plazaTimer FROM nest WHERE ownerName = ?");
				$q->bind_param('s', $_COOKIE['weevil_name']);
                $q->execute();

				$res = $q->get_result();

				if($res = $res->fetch_array()) {
					$timeUntil = json_decode(time_until(time(), $res['plazaTimer']));

					if($timeUntil->minutes == 0 && $timeUntil->seconds == 0)
					return true;
				}
            }
		}

		return false;
	}

	function updatePlazaEarningTimer($time) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE nest SET plazaTimer = ? WHERE ownerName = ?");
				$q->bind_param('ss', $time, $_COOKIE['weevil_name']);
                $q->execute();

				if($q->affected_rows == 1)
				return true;
            }
		}

		return false;
	}

    function GetWeevilItemByID($itemid){
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
			if($loggedIn == true) {
                $weevilId = getAllWeevilStatsByName($_COOKIE['weevil_name'])['id'];
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM `weevilitems` WHERE `ID` = ? AND weevilID = ?;");
				$q->bind_param('ss', $itemid, $weevilId);
                $q->execute();

                $res = $q->get_result();
                if($res = $res->fetch_array())
                return $res;
			}
		}
		return false;
    }

    function GetWeevilItemCountByID($itemid){
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
			if($loggedIn == true) {
                $weevilId = getAllWeevilStatsByName($_COOKIE['weevil_name'])['id'];
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT COUNT(*) FROM `weevilitems` WHERE `ID` = ? AND weevilID = ?;");
				$q->bind_param('ss', $itemid, $weevilId);
                $q->execute();

                $res = $q->get_result();
                if($res = $res->fetch_array())
                return $res['0'];
			}
		}
		return false;
    }

    function addNestCoolness($coolness){
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE nest SET score = score + ? WHERE ownerName = ?");
				$q->bind_param('ss', $coolness, $_COOKIE['weevil_name']);
                $q->execute();

				if($q->affected_rows == 1)
				return true;
            }
		}

		return false;
    }

    function getUserGameDataByKey($weevil, $key) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM weevilGames WHERE weevil = ? AND `key` = ?;");
				$q->bind_param('ss', $weevil, $key);
                $q->execute();
                
                $res = $q->get_result();

                if($res = $res->fetch_array()){
                    return $res;
                }
                else return false;
            }
            else return false;
        }
        else return false;
    }

    function getWeevilRaceData($track) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM weevilGames WHERE weevil = ? AND `game` = ?;");
				$q->bind_param('ss', $_COOKIE['weevil_name'], $track);
                $q->execute();
                
                $res = $q->get_result();

                if($res = $res->fetch_array()){
                    return $res;
                }
                else return false;
            }
            else return false;
        }
        else return false;
    }

    function setRaceStatus($track, $type){
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("UPDATE weevilGames SET `$type` = 1 WHERE weevil = ? AND game = ?");
				$q->bind_param('ss', $_COOKIE['weevil_name'], $track);
                $q->execute();
				if($q->affected_rows == 1)
				return true;
            }
		}

		return false;
    }

    function getFriendRaceLeaderboard($gameId) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $weevilName = $_COOKIE['weevil_name'];
                $q = $db->prepare("SELECT `namesList` FROM buddylist WHERE `ownerName` = ?;");
				$q->bind_param('s', $weevilName);
                $q->execute();

                $res = $q->get_result();
 
                if($res = $res->fetch_array()){
                    $friendData = "";
                    $resp = substr($res[0], 0, -1);
                    $friends = explode(",",$resp);
                    foreach($friends as $friend){
                        $data = getAllWeevilStatsByName($friend);
                        if(hasUserPlayedGame($data['username'], $gameId) == true){
                            $friendData .= '"'.$friend.'",';
                        }
                    }
                    $friendData .= '"'.$weevilName.'"';
                    //$friendData = substr($friendData, 0, -1);
                    
                    $q = $db->prepare("SELECT * FROM `weevilGames` WHERE `weevil` IN ($friendData) AND `game` = ? AND `total` != 0 ORDER BY `total` ASC;");
					$q->bind_param('s', $gameId);
                    $q->execute();

                    $res = $q->get_result();
                    if($res = $res->fetch_all())
                    return $res;
                }
            }
        }

        return false;
    }

    function deleteNestItemFromInventory($weevilid, $itemid) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("DELETE FROM `weevilitems` WHERE `ID` = ? AND `weevilID` = ?");
				$q->bind_param('ss', $itemid, $weevilid);
				$q->execute();
				
				if($q->affected_rows == 1) {
					return true;
				}
            }
		}

		return false;
	}

	function deleteNestItemsFromHaggle($weevilid, $itemarray) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("DELETE FROM `weevilitems` WHERE `weevilID` = ? AND `ID` IN (" . implode(',', $itemarray) . ")");
				$q->bind_param('s', $weevilid);
				$q->execute();
				
				if($q->affected_rows == 1) {
					return true;
				}
            }
		}

		return false;
	}

	function deleteGardenItemsFromHaggle($weevilid, $itemarray) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("DELETE FROM `gardenInventory` WHERE `ownerName` = ? AND `id` IN (" . implode(',', $itemarray) . ")");
				$q->bind_param('s', $weevilid);
				$q->execute();
				
				if($q->affected_rows == 1) {
					return true;
				}
            }
		}

		return false;
	}

    function deleteGardenItemFromInventory($weevilName, $itemid) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("DELETE FROM `gardenInventory` WHERE `id` = ? AND `ownerName` = ?");
				$q->bind_param('ss', $itemid, $weevilName);
				$q->execute();
				
				if($q->affected_rows == 1) {
					return true;
				}
            }
		}

		return false;
	}

    function HasUserCompletedTask($taskID, $username){
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT COUNT(*) FROM tasksCompletedByUsers WHERE `weevilName` = ? AND `tasks` = ?;");
				$q->bind_param('ss', $username, $taskID);
                $q->execute();

                $res = $q->get_result();

                if($res = $res->fetch_array()) {
                    if(intval($res['0']) > 0)
					return true;
                    else return false;
                }
                else return false;
            }
            else return false;
        }
        else return false;
    }

    function CompleteTask($taskID, $username, $idx, $questID) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
			if($loggedIn == true) {

                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				if($questID != NULL || $questID != "") {
					$q = $db->prepare("INSERT INTO `tasksCompletedByUsers` (`weevilName`, `tasks`, `idx`, `questID`) VALUES (?, ?, ?, ?)");
					$q->bind_param('ssss', $username, $taskID, $idx, $questID);
				}
				else {
					$q = $db->prepare("INSERT INTO `tasksCompletedByUsers` (`weevilName`, `tasks`, `idx`) VALUES (?, ?, ?)");
					$q->bind_param('sss', $username, $taskID, $idx);
				}
				
                $q->execute();

                $res = $q->get_result();

                if($q->affected_rows == 1)
				return true;
			}
            return false;
		}
		return false;
    }

    function GetTaskDetails($taskID){
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);
			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT * FROM `task-completed` WHERE taskID = ?;");
				$q->bind_param('s', $taskID);
				$q->execute();
				$res = $q->get_result();
				if($res = $res->fetch_array())
				{
					return $res;
				}
			}
		}
		return false;
    }

	function getListOfCompletedTasks() {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT tasks FROM `tasksCompletedByUsers` WHERE weevilName = ?");
				$q->bind_param('s', $_COOKIE['weevil_name']);
				$q->execute();
				$res = $q->get_result();
				if($res = $res->fetch_all(MYSQLI_ASSOC))
				{
					return $res;
				}
			}
		}

		return null;
	}

	function hasUserFoundHuntItem($compID, $itemID) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT * FROM `bubbleHunts` WHERE userID = ? AND compID = ? AND itemID = ?");
				$q->bind_param('sss', $_COOKIE['weevil_name'], $compID, $itemID);
				$q->execute();
				$res = $q->get_result();
				if($res = $res->fetch_array())
				return true;
			}
		}

		return false;
	}

	function getAmountOfHuntItemsFoundById($compID) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT COUNT(*) FROM `bubbleHunts` WHERE `userID` = ? AND `compID` = ?");
				$q->bind_param('ss', $_COOKIE['weevil_name'], $compID);
				$q->execute();
				$res = $q->get_result();
				if($res = $res->fetch_array())
				if(intval($res['0']) > 0)
				return intval($res['0']);
			}
		}

		return 0;
	}

	function setUserFoundHuntItem($compID, $itemID) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("INSERT INTO `bubbleHunts` (`userID`, `compID`, `itemID`) VALUES (?, ?, ?)");
				$q->bind_param('sss', $_COOKIE['weevil_name'], $compID, $itemID);
				$q->execute();

				if($q->affected_rows == 1)
				return true;
			}
		}

		return false;
	}

	function isHuntAvailable($compID) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT * FROM `bubbleCompetitions` WHERE compID = ?");
				$q->bind_param('s', $compID);
				$q->execute();
				$res = $q->get_result();
				if($res = $res->fetch_array())
				if($res['active'] == 1)
				return true;
			}
		}

		return false;
	}

	function getHuntItemsFoundById($compID) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT itemID FROM `bubbleHunts` WHERE userID = ? AND compID = ?");
				$q->bind_param('ss', $_COOKIE['weevil_name'], $compID);
				$q->execute();
				$res = $q->get_result();
				if($res = $res->fetch_all(MYSQLI_ASSOC))
				return $res;
			}
		}

		return null;
	}

	function getPuzzleTypeData($typeID) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT * FROM puzzleTypes WHERE id = ?");
				$q->bind_param('s', $typeID);
				$q->execute();

				$res = $q->get_result();

				if($res = $res->fetch_array())
				return $res;
			}
		}

		return null;
	}

	function getPuzzleListData($table1, $table2) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT minLevel, name, configPath, completed FROM $table1 LEFT JOIN (SELECT * FROM $table2 WHERE userID = ?) AS myPrgress ON $table1.id = myPrgress.gridID ORDER BY $table1.minLevel");
				$q->bind_param('s', $_COOKIE['weevil_name']);
				$q->execute();

				$res = $q->get_result();

				if($res = $res->fetch_all(MYSQLI_ASSOC))
				return $res;
			}
		}

		return null;
	}

	function getCrosswordData($gridID) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT minLevel, mulchReward, xpReward FROM crosswords WHERE id = ? LIMIT 1");
				$q->bind_param('s', $gridID);
				$q->execute();

				$res = $q->get_result();

				if($res = $res->fetch_array())
				return $res;
			}
		}

		return null;
	}

	function getCrosswordProgress($gridID) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT progress, completed FROM crosswordUserProgress WHERE gridID = ? AND userID = ? LIMIT 1");
				$q->bind_param('ss', $gridID, $_COOKIE['weevil_name']);
				$q->execute();

				$res = $q->get_result();

				if($res = $res->fetch_array())
				return $res;
			}
		}

		return null;
	}

	function setCrosswordProgress($progress, $gridID, $completed = 0) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				if($completed == 0) {
					$q = $db->prepare("UPDATE crosswordUserProgress SET progress = ? WHERE gridID = ? AND userID = ?");
					$q->bind_param('sss', $progress, $gridID, $_COOKIE['weevil_name']);
				}
				else {
					$q = $db->prepare("UPDATE crosswordUserProgress SET progress = ?, completed = ? WHERE gridID = ? AND userID = ?");
					$q->bind_param('ssss', $progress, $completed, $gridID, $_COOKIE['weevil_name']);
				}
				$q->execute();

				if($q->affected_rows > 0)
				return true;
			}
		}

		return false;
	}

	function addNewCrosswordProgress($progress, $gridID) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("INSERT INTO crosswordUserProgress (`userID`, `gridID`, `progress`, `completed`) VALUES (?, ?, ?, '0')");
				$q->bind_param('sss', $_COOKIE['weevil_name'], $gridID, $progress);
				$q->execute();

				if($q->affected_rows > 0)
				return true;
			}
		}

		return false;
	}

	function getWordsearchData($gridID) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT minLevel FROM wordSearches WHERE id = ? LIMIT 1");
				$q->bind_param('s', $gridID);
				$q->execute();

				$res = $q->get_result();

				if($res = $res->fetch_array())
				return $res;
			}
		}

		return null;
	}

	function getWordsearchProgress($gridID) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT progress, wordsFound, completed FROM wordSearchUserProgress WHERE gridID = ? AND userID = ? LIMIT 1");
				$q->bind_param('ss', $gridID, $_COOKIE['weevil_name']);
				$q->execute();

				$res = $q->get_result();

				if($res = $res->fetch_array())
				return $res;
			}
		}

		return null;
	}

	function setWordsearchProgress($progress, $gridID, $completed = 0) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				if($completed == 0) {
					$q = $db->prepare("UPDATE wordSearchUserProgress SET progress = ?, wordsFound = ? WHERE gridID = ? AND userID = ?");
					$q->bind_param('ssss', $progress, count(explode('|', $progress)), $gridID, $_COOKIE['weevil_name']);
				}
				else {
					$q = $db->prepare("UPDATE wordSearchUserProgress SET progress = ?, completed = ?, wordsFound = ? WHERE gridID = ? AND userID = ?");
					$q->bind_param('sssss', $progress, $completed, count(explode('|', $progress)), $gridID, $_COOKIE['weevil_name']);
				}
				$q->execute();

				if($q->affected_rows > 0)
				return true;
			}
		}

		return false;
	}

	function addNewWordsearchProgress($progress, $gridID) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("INSERT INTO wordSearchUserProgress (`userID`, `gridID`, `progress`, `completed`, `wordsFound`) VALUES (?, ?, ?, '0', ?)");
				$q->bind_param('ssss', $_COOKIE['weevil_name'], $gridID, $progress, count(explode('|', $progress)));
				$q->execute();

				if($q->affected_rows > 0)
				return true;
			}
		}

		return false;
	}

	function getWordsearchProgressMod($gridID, $weevilName) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT progress, wordsFound, completed FROM wordSearchUserProgress WHERE gridID = ? AND userID = ? LIMIT 1");
				$q->bind_param('ss', $gridID, $weevilName);
				$q->execute();

				$res = $q->get_result();

				if($res = $res->fetch_array())
				return $res;
		
		return null;
	}

	function setWordsearchProgressMod($progress, $gridID, $weevilName, $completed = 0) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				if($completed == 0) {
					$q = $db->prepare("UPDATE wordSearchUserProgress SET progress = ?, wordsFound = ? WHERE gridID = ? AND userID = ?");
					$q->bind_param('ssss', $progress, count(explode('|', $progress)), $gridID, $weevilName);
				}
				else {
					$q = $db->prepare("UPDATE wordSearchUserProgress SET progress = ?, completed = ?, wordsFound = ? WHERE gridID = ? AND userID = ?");
					$q->bind_param('sssss', $progress, $completed, count(explode('|', $progress)), $gridID, $weevilName);
				}
				$q->execute();

				if($q->affected_rows > 0)
				return true;

		return false;
	}

	function addNewWordsearchProgressMod($userID, $progress, $gridID) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("INSERT INTO wordSearchUserProgress (`userID`, `gridID`, `progress`, `completed`, `wordsFound`) VALUES (?, ?, ?, '0', ?)");
				$q->bind_param('ssss', $userID, $gridID, $progress, count(explode('|', $progress)));
				$q->execute();

				if($q->affected_rows > 0)
				return true;

		return false;
	}

	function getUserPets() {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT * FROM pets WHERE ownerID = ?");
				$q->bind_param('s', $_COOKIE['weevil_name']);
				$q->execute();

				$res = $q->get_result();

				if($res = $res->fetch_all(MYSQLI_ASSOC))
				return $res;
			}
		}

		return null;
	}

	function getPetSkills($petID) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT skillID, obedience, skillLevel FROM petAcquiredSkills WHERE ownerID = ? AND petID = ?");
				$q->bind_param('ss', $_COOKIE['weevil_name'], $petID);
				$q->execute();

				$res = $q->get_result();

				if($res = $res->fetch_all(MYSQLI_ASSOC))
				return $res;
			}
		}

		return null;
	}

	function getTop10RichestPlayers() {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId']))
        {
            $loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

            if($loggedIn == true)
            {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT username, mulch FROM users WHERE isModerator = 0 ORDER BY mulch DESC LIMIT 10");
                $q->execute();

                $res = $q->get_result();

                if($res = $res->fetch_all(MYSQLI_ASSOC)) {
                    $returnVal = [];

                    foreach($res as $row)
                    array_push($returnVal, [$row["username"] => $row["mulch"]]);

                    return $returnVal;
                }
            }
        }

        return null;
    }

	function getStrainLevels($weevilId) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT `qType`, `level` FROM `gameBrainTraining` WHERE `idx` = ?");
				$q->bind_param('s', $weevilId);
                $q->execute();

                $res = $q->get_result();

				if($res = $res->fetch_all(MYSQLI_ASSOC)) {
					$arr = [];

					foreach($res as $x => $data)
					$arr[] = $data["qType"] . '|' . $data["level"];

					return join(',', $arr);
				}
			}
		}

		return '';
	}

	function updateStrainLevels($weevilId, $levels) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$data = decodeLevels($levels);
				$q = null;
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

				foreach($data as $qType => $level) {
					$q = $db->prepare("INSERT INTO `gameBrainTraining` SET `idx` = ?, `qType` = ?, `level` = ? ON DUPLICATE KEY UPDATE `qType` = ?, `level` = ?");
					$q->bind_param('sssss', $weevilId, $qType, $level, $qType, $level);
					$q->execute();
				}

				if($q->affected_rows > 0)
				return getStrainLevels($weevilId);
			}
		}

		return null;
	}

	function getGameData($gameId) {
		$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    	$q = $db->prepare("SELECT * FROM singlePlayerGames WHERE gameID = ?");
		$q->bind_param('s', $gameId);
        $q->execute();

        $res = $q->get_result();

		if($res = $res->fetch_array(MYSQLI_ASSOC))
		return $res;

		return null;
	}

	function getSinglePlayerUserData($weevilId, $gameId) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT * FROM singlePlayerGames_Stats WHERE userIdx = ? AND gameID = ?");
				$q->bind_param('ss', $weevilId, $gameId);
                $q->execute();

                $res = $q->get_result();

				if($res = $res->fetch_array(MYSQLI_ASSOC))
				return $res;
			}
		}

		return null;
	}

	function setSinglePlayerUserData($weevilId, $gameId, $score, $time = "") {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				
				if($time != "" && $time != null) {
					$q = $db->prepare("INSERT INTO singlePlayerGames_Stats SET userIdx = ?, gameID = ?, numPlays = 1, bestScore = ?, averageScore = ?, last_played = ? ON DUPLICATE KEY UPDATE bestScore = (IF((bestScore < ?), ?, bestScore)), numPlays = numPlays + 1, averageScore = (((numPlays - 1) * averageScore) + ?) / (numPlays), last_played = ?");
					$q->bind_param('sssssssss', $weevilId, $gameId, $score, $score, $time, $score, $score, $score, $time);
				}
				else {
					$q = $db->prepare("INSERT INTO singlePlayerGames_Stats SET userIdx = ?, gameID = ?, numPlays = 1, bestScore = ?, averageScore = ? ON DUPLICATE KEY UPDATE bestScore = (IF((bestScore < ?), ?, bestScore)), numPlays = numPlays + 1, averageScore = (((numPlays - 1) * averageScore) + ?) / (numPlays)");
					$q->bind_param('sssssss', $weevilId, $gameId, $score, $score, $score, $score, $score);
				}

                $q->execute();

				if($q->affected_rows > 0)
				return getSinglePlayerUserData($weevilId, $gameId);
			}
		}

		return null;
	}

	function buyTokenItem($weevilidx, $itemId, $cat, $configLoc) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                if(!checkIfItemIsBuyableWithTokens($itemId)) return false;

				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("INSERT INTO `weevilitems` (`weevilID`, `itemId`, `category`, `configName`) VALUES (?, ?, ?, ?)");
				$q->bind_param('ssss', $weevilidx, $itemId, $cat, $configLoc);
				$q->execute();

				$res = $q->get_result();

				if($q->affected_rows == 1) {
                    $q = $db->prepare("UPDATE `itemtype` SET `purchases` = purchases + 1 WHERE `itemtype`.`itemTypeID` = ?;");
					$q->bind_param('s', $itemId);
                    $q->execute();
    
                    $res = $q->get_result();
    
                    if($q->affected_rows == 1)
					return true;
				}
			}
		}

		return false;
    }

	function checkIfItemIsBuyableWithTokens($itemId) {
        if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $q = $db->prepare("SELECT COUNT(*) FROM itemtype WHERE `itemTypeID` = ? AND `canBuy` = 1 AND `shopType` = 'popUpShop';");
				$q->bind_param('s', $itemId);
                $q->execute();

                $res = $q->get_result();

                if($res = $res->fetch_array()) {
                    if(intval($res['0']) > 0)
					return true;
                }
            }
        }

        return false;
    }

	function getQuestsForMissions($hasRoom, $weevilId, $room = null) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT q.id, q.name, q.UIpath, q.tycoonOnly, q.scoreBronze, q.scoreSilver, q.scoreGold, q.scorePlatinum, 0 minLevel, 0 price, IFNULL(qtc.isComplete, 0) complete, qtc.highScore, q.room FROM quests q LEFT JOIN questsCompleted qc ON q.id = qc.questID AND qc.idx = ? LEFT JOIN questTasks qt ON q.id = qt.questID AND qt.taskType = 'complete' LEFT JOIN tasksCompletedByUsers qtc ON qt.id = qtc.tasks AND qtc.idx = ? " . ($hasRoom ? "WHERE q.room = ? " : "") . "AND q.isLive = 1 ORDER BY q.id ASC;");
				
				if($hasRoom)
				$q->bind_param('sss', $weevilId, $weevilId, $room);
				else
				$q->bind_param('ss', $weevilId, $weevilId);

                $q->execute();

				$res = $q->get_result();

				if($res = $res->fetch_all(MYSQLI_ASSOC))
				return $res;
			}
		}

		return null;
	}
	function getSpecialMoves($username, $idx) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT moves FROM `specialMoves` WHERE weevilID = ? AND idx = ?");
				$q->bind_param('ss', $_COOKIE['weevil_name'], $idx);
				$q->execute();
				$res = $q->get_result();
				if($res = $res->fetch_all(MYSQLI_ASSOC))
				return $res;
			}
		}

		return null;
	}

	function rewardSpecialMoves($username, $idx, $moveId) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId']))
		{
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true)
			{
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("INSERT INTO `specialMoves` (`weevilID`, `moves`, `idx`) VALUES (?, ?, ?)");
				$q->bind_param('sss', $_COOKIE['weevil_name'], $moveId, $idx);
				$q->execute();
				$res = $q->get_result();
    
				if($q->affected_rows == 1)
				return true;
			}
		}

		return null;
	}


	function canMushroomBeClaimed($type, $rewardAmt) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT * FROM mushrooms WHERE mushroomType = ? AND rewardAmount = ? AND validUntil > ?");
				$q->bind_param("sss", $type, $rewardAmt, time());
				$q->execute();

				$res = $q->get_result();

				if($res = $res->fetch_array())
				return true;
			}
		}

		return false;
	}

	function checkForExistingMushroomData($idx, $type) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT * FROM claimedmushrooms WHERE idx = ? AND mushroomType = ?");
				$q->bind_param("ss", $idx, $type);
				$q->execute();

				$res = $q->get_result();

				if($res = $res->fetch_array())
				return true;
				else {
					$q = $db->prepare("INSERT INTO claimedmushrooms (`idx`, `mushroomType`) VALUES (?, ?)");
					$q->bind_param("ss", $idx, $type);
					$q->execute();

					if($q->affected_rows == 1)
                	return true;
				}
			}
		}

		return false;
	}

	function getMushroomData($type) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT * FROM mushrooms WHERE mushroomType = ?");
				$q->bind_param("s", $type);
				$q->execute();

				$res = $q->get_result();

				if($res = $res->fetch_array())
				return $res;
			}
		}

		return null;
	}

	function getMushroomRewardData($idx, $type) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("SELECT * FROM claimedmushrooms WHERE idx = ? AND mushroomType = ?");
				$q->bind_param("ss", $idx, $type);
				$q->execute();

				$res = $q->get_result();

				if($res = $res->fetch_array())
				return $res;
			}
		}

		return null;
	}

	function setNewMushroomRewardTime($newUnix, $type, $idx) {
		if(isset($_COOKIE['weevil_name']) && isset($_COOKIE['sessionId'])) {
			$loggedIn = confirmSessionKey($_COOKIE['weevil_name'], $_COOKIE['sessionId']);

			if($loggedIn == true) {
				$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
				$q = $db->prepare("UPDATE claimedmushrooms SET lastClaimed = ? WHERE idx = ? AND mushroomType = ?");
				$q->bind_param("sss", $newUnix, $idx, $type);
				$q->execute();

				if($q->affected_rows == 1)
                return true;
			}
		}

		return null;
	}


?>