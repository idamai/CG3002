<?php

require_once ("settings.php");
$mysql = new mysql($dbconn->get("server"), $dbconn->get("username"), $dbconn->get("password"), $dbconn->get("database"));
		if ($mysqli->connect_errno){
			//Connection failure
			throw new Exception("Failed to connect to MySQL: ".$mysqli->connect_error);
		}
?>