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