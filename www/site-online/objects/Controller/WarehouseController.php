<?php
	class  WarehouseController {
		private $connection;
		function __construct($conn){
			$this->connection = $conn;
		}
		
		function retrieveStockDetails($barcode){
			$barcode = mysql_real_escape_string($barcode);
			$sql = "SELECT `batchdate`, `stock` FROM `warehouse` WHERE barcode = ".$barcode." AND `stock` > 0";
			$res = mysql_query($sql,$this->connection);
			
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			$rows = mysql_num_rows($res);
			$stockDetails =  array();
			for ($j = 0 ; $j < $rows ; $j++)
			{
				$stockDetails[$j] = array(
												"batchdate" => mysql_result($res,$j,'batchdate'),
												"stock" => mysql_result($res,$j,'stock')
											);		
			}
			return $stockDetails;
		}
		
		function addNewStock($barcode, $quantity, $date) {
			$barcode = mysql_real_escape_string($barcode);
			$date = mysql_real_escape_string($date);
			$quantity = mysql_real_escape_string($quantity);
			$sql = "UPDATE `warehouse` SET `stock` = ".$quantity." WHERE `batchdate` = '".$date."' AND `barcode` = ".$barcode;
			$res = mysql_query($sql,$this->connection);
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
		}
		
		function addStockForAll() {
			$sql = 'SELECT `barcode`, `q_star` FROM `price_modifier`';
			$res = mysql_query($sql,$this->connection);
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			$rows = mysql_num_rows($res);
			$productStock = array();
			for ($j = 0 ; $j < $rows ; $j++){
				$productStock[$j] = array(
												"barcode" => mysql_result($res,$j,'barcode'),
												"fullstock" => mysql_result($res,$j,'q_star')
											);		
			}
			foreach($productStock as $data) {
				$bar = $data['barcode'];
				$full = $data['fullstock'];
				$full = (7 * $full)/16;
				$sql = "SELECT sum(`stock`) as `holding` FROM `warehouse` WHERE `barcode` = ".$bar ;
				$res = mysql_query($sql,$this->connection);
				if (!$res) throw new Exception("Database access failed: " . mysql_error());
				while($rows = mysql_fetch_array($res)) {
					if(($data['fullstock'] - $rows['holding']) > 0) {
						$sql2 = "INSERT INTO `warehouse` (`barcode`, `batchdate`, `stock` )VALUES (".$data['barcode'].",CURDATE(),".($full-$rows[0]).")" ;
						mysql_query($sql2,$this->connection);
						$sql3 = "UPDATE `price_modifier` SET `update_date` = CURDATE() WHERE `barcode` = ".$data['barcode'] ;
						mysql_query($sql3,$this->connection);
					}
				}
			}
		}		
		
		function retrieveStockForOrder($toBeOrdered) {
			$sql = "SELECT `barcode`, SUM(`stock`) as `stock` FROM `warehouse` WHERE `barcode` in (";
			for ($j = 0; $j < count($toBeOrdered) ; $j++){
				if ($j > 0) {
					$sql.=" , ";
				}
				$sql.=$toBeOrdered[$j]["barcode"];
			}
			$sql .= ") GROUP BY `barcode`";
			$res = mysql_query($sql,$this->connection);
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			$rows = mysql_num_rows($res);
			$availableBarcode = array();
			for ($j = 0 ; $j < $rows ; $j++){
				$availableBarcode[$j] = array(
												"barcode" => mysql_result($res,$j,'barcode'),
												"stock" => mysql_result($res,$j,'stock')
											);		
			}
			return $availableBarcode;
		}
		
		function retrieveTotalProductStock() {
			$sql = 'SELECT `barcode`, SUM(`stock`) as `stock` FROM `warehouse` GROUP BY `stock`';
			$res = mysql_query($sql,$this->connection);
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			$rows = mysql_num_rows($res);
			$productStocks = array();
			for ($j = 0 ; $j < $rows ; $j++){
				$productStocks[$j] = array(
												"barcode" => mysql_result($res,$j,'barcode'),
												"stock" => mysql_result($res,$j,'stock')
											);		
			}
			return $productStocks;
		}
	}
		
?>