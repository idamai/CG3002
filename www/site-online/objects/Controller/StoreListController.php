<?php
	class  StoreListController {
		private $connection;
		function __construct($conn){
			$this->connection = $conn;
		}
		
		function retreiveStoreList() {
			$sql = "SELECT `id`,`name`,`location` FROM `local_stores` WHERE `deleted` = 0";
			$res = mysql_query($sql,$this->connection);
			
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			$rows = mysql_num_rows($res);
			$storeList =  array();
			for ($j = 0 ; $j < $rows ; $j++)
			{
				$storeList[$j] = array(
												"store_id" => mysql_result($res,$j,'id'),
												"store_name" => mysql_result($res,$j,'name'),
												"store_loc" => mysql_result($res,$j,'location')
											);		
			}
			return $storeList;
		}
		
		function addNewStore($store_id, $name, $location, $password){
			$store_id = mysql_real_escape_string($store_id);
			$name = mysql_real_escape_string($name);
			$location = mysql_real_escape_string($location);
			$password = SHA1($password);
			$sql = 'INSERT INTO `local_stores` (`id` , `name`, `location`, `password` ) VALUES ( '.$store_id.' , "'.$name. '" , "'.$location.'" , "'.$password.'" )';
			$res = mysql_query($sql,$this->connection);
		
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
		}
		
		function retreiveStoreInfo($store_id) {
			$sql = 'SELECT `id` , `name`, `location` FROM `local_stores` WHERE `id` = '.$store_id;
			$res = mysql_query($sql,$this->connection);
		
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			$rows = mysql_num_rows($res);
			$storeList =  array();
			for ($j = 0 ; $j < $rows ; $j++)
			{
				$storeList[$j] = array(
												"store_id" => mysql_result($res,$j,'id'),
												"name" => strip_tags (mysql_result($res,$j,'name')),
												"location" => strip_tags (mysql_result($res,$j,'location'))
											);
			}
			return $storeList[0];
			
		}
		
		function editStoreInformation($store_id, $name, $location, $password) {
			$store_id = mysql_real_escape_string($store_id);
			$name = mysql_real_escape_string($name);
			$location = mysql_real_escape_string($location);
			$changePassword = false;
			if (($password!=null)&&($password!='')){
				$password = SHA1($password);
				$changePassword = true;
			}
			$sql = 'UPDATE `local_stores` SET  `name` = "'.$name.'" , `location` = "'.$location.'"';
			if ($changePassword) {
				$sql.=' , `password` = "'.$password.'"';
			}
			$sql.=' WHERE `id` = '.$store_id;
			$res = mysql_query($sql,$this->connection);

			if (!$res) throw new Exception("Database access failed: " . mysql_error());
		}
		
		function deleteStore($store_id) {
			$barcode = mysql_real_escape_string($barcode);
			$sql = 'UPDATE `local_stores` SET  `deleted` = 1 WHERE `id` = '.$store_id;
			$res = mysql_query($sql,$this->connection);

			if (!$res) throw new Exception("Database access failed: " . mysql_error());
		}		
	}
?>
		
		