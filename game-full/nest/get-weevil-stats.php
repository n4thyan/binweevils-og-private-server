<?php
	error_reporting(0);
	// level=2&mulch=1000&dosh=0&xp=30&xp1=30&xp2=60&food=74&age=0&sex=&activated=0&daysRemaining=365&cs=0&key=0&displayActivation=1&email=0&newsVersion=0&st=30936000&hash=44b2b3e7fbfefd5a69bc0c72a70589d6
	include('../essential/backbone.php');
	
	if($_POST) {
		$hash = $_POST['hash'];
		$idx = $_POST['idx'];
		$key = $_POST['key'];
		$st = $_POST['st'];

		if(checkHash(["hash" => $hash, "idx" => $idx, "key" => $key, "st" => $st]))
		echo getWeevilStats();
		else
		echo 'res=999';
	}
	else
	echo 'res=999';
?>