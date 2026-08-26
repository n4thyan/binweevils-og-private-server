<?php
    error_reporting(0);
    
    if(session_status() === PHP_SESSION_NONE)
    session_start();
    
    header("X-XSS-Protection: 1; mode=block");
    header("X-Content-Type-Options: nosniff");
    //header_status(500);
    /*foreach($_POST as $data){
        echo $data;
    }*/
    include_once(dirname(__FILE__) . '/checksum.php');
    if(isset($_POST))
    $checksum = new Checksum($_POST);
    /*if($checksum != $_POST['checksum']){
        header_status(500);
    }*/


	include_once(dirname(__FILE__) . '/config.php');
	include_once(dirname(__FILE__) . '/internal.php');
	include_once(dirname(__FILE__) . '/protections.php'); // Their anti reverse-engineer system rewritten!
    include_once(dirname(__FILE__) . '/aes256.php');
    include_once(dirname(__FILE__) . '/funcs.php');

    // Checkpoint A (A7/A8): generic IP-based sliding-window rate limiter.
    // Key is a privacy-preserving HMAC of the client IP + a namespace, never the raw IP.
    // Uses a per-key file cache in the system temp dir. Returns true if the action is
    // allowed, false if the limit (max) within windowSec has been exceeded.
    function rateLimit($namespace, $max, $windowSec) {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
        $key = hash_hmac('sha256', $ip . '|' . $namespace, IP_HASH_SECRET);
        $dir = sys_get_temp_dir() . '/bw-ratelimit';
        if (!is_dir($dir)) @mkdir($dir, 0700, true);
        $file = $dir . '/' . $key . '.json';
        $now = time();
        $hits = [];
        if (file_exists($file)) {
            $raw = @file_get_contents($file);
            if ($raw) { $dec = json_decode($raw, true); if (is_array($dec)) $hits = $dec; }
        }
        // drop timestamps outside the window
        $hits = array_values(array_filter($hits, function($t) use ($now, $windowSec) { return ($now - $t) < $windowSec; }));
        if (count($hits) >= $max) return false;
        $hits[] = $now;
        @file_put_contents($file, json_encode($hits));
        return true;
    }

    include_once(dirname(__FILE__) . '/sock.php');
    //echo $checksum;
?>