<?php
	class  OrderController {
		private $connection;
		function __construct($conn){
			$this->connection = $conn;
		}
		
		// function to load JSON file
		function loadOrder() {
		}
		
		function processBarcodeOrder( $barcode, $quantity){
			$sql = "SELECT `batchdate`, `stock` FROM `warehouse` WHERE `barcode` = ".$barcode." AND STOCK > 0 ORDER BY `batchdate`";
			$res = mysql_query($sql,$this->connection);
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			$rows = mysql_num_rows($res);
			$batches =  array();
			for ($j = 0 ; $j < $rows ; $j++)
			{
				$batches[$j] = array(
												"batchdate" => mysql_result($res,$j,'batchdate'),
												"stock" => mysql_result($res,$j,'stock')
											);		
			}
			$j = 0;
			while ($quantity > 0){
				if ($quantity > $batches[$j]["stock"]){
					$quantity -= $batches[$j]["stock"];
					$batches[$j]["stock"]=0;
				} else {
					$batches[$j]["stock"]-=$quantity;
					$quantity = 0;
				}
				$j++;
			}
			for ($i = 0; $i < $j; $i++) {
				$sql = 'UPDATE `warehouse` SET `stock` = '.$batches[$i]["stock"].' WHERE `barcode` = '.$barcode.' AND `batchdate` = "'.$batches[$i]["batchdate"].'"';
				$res = mysql_query($sql,$this->connection);
				if (!$res) throw new Exception("Database access failed: " . mysql_error());
			}
		}
		
		function getUnprocessedOrderDates() {
			$sql = "SELECT DISTINCT `date` FROM `product_order` WHERE `processed` = 0";
			$res = mysql_query($sql, $this->connection );
			$rows = mysql_num_rows($res);
			$dates =  array();
			for ($j = 0 ; $j < $rows ; $j++) {
				$dates[$j] = mysql_result($res,$j,'date');									
			}
			return $dates;
		}
		
		function getUnprocessedOrderBarcodes() {
			$sql = "SELECT DISTINCT `barcode` FROM `product_order` WHERE `processed` = 0";
			$res = mysql_query($sql, $this->connection );
			$rows = mysql_num_rows($res);
			$barcodes =  array();
			for ($j = 0 ; $j < $rows ; $j++) {
				$barcodes[$j] = mysql_result($res,$j,'barcode');									
			}
			return $barcodes;
		}

		function getAllUnprocessedOrder() {
			$sql = "SELECT * FROM `product_order` WHERE `processed` = 0";
			$res = mysql_query($sql,$this->connection);
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			$rows = mysql_num_rows($res);
			$retArr =  array();
			for ($j = 0 ; $j < $rows ; $j++){
				$retArr[$j] = array(
												"barcode" => mysql_result($res,$j,'barcode'),
												"date" => mysql_result($res,$j,'date'),
												"store_id" => mysql_result($res,$j,'store_id'),
												"quantity" => mysql_result($res,$j,'quantity')
											);		
			}
			return $retArr;
		}
		
		function getAllOrderPerBarcode() {
			$sql = "SELECT `barcode`, sum(`quantity`) as `quantity` FROM `product_order` WHERE `processed` = 0 GROUP BY `barcode`";
			$res = mysql_query($sql,$this->connection);
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			$rows = mysql_num_rows($res);
			$toBeOrdered =  array();
			for ($j = 0 ; $j < $rows ; $j++){
				$toBeOrdered[$j] = array(
												"barcode" => mysql_result($res,$j,'barcode'),
												"quantity" => mysql_result($res,$j,'quantity')
											);		
			}
			return $toBeOrdered;
		}
		
		function getAllOrderForBarcode($barcode) {
			$barcode = mysql_real_escape_string($barcode);
			$sql = "SELECT `barcode`, sum(`quantity`) as `quantity` FROM `product_order` WHERE `processed` = 0 AND `barcode` = ".$barcode." GROUP BY `barcode`";
			$res = mysql_query($sql,$this->connection);
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			$rows = mysql_num_rows($res);
			$toBeOrdered =  array();
			for ($j = 0 ; $j < $rows ; $j++){
				$toBeOrdered[$j] = array(
												"barcode" => mysql_result($res,$j,'barcode'),
												"quantity" => mysql_result($res,$j,'quantity')
											);		
			}
			return $toBeOrdered;
		}
		
		function getALlOrderForDate($date) {
			$date = mysql_real_escape_string($date);
			$sql = 'SELECT `barcode`, sum(`quantity`) as `quantity` FROM `product_order` WHERE `processed` = 0 AND `date` = "'.$date.'" GROUP BY `barcode`';
			$res = mysql_query($sql,$this->connection);
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			$rows = mysql_num_rows($res);
			$toBeOrdered =  array();
			for ($j = 0 ; $j < $rows ; $j++){
				$toBeOrdered[$j] = array(
												"barcode" => mysql_result($res,$j,'barcode'),
												"quantity" => mysql_result($res,$j,'quantity')
											);		
			}
			return $toBeOrdered;
		}
		
		function processOrder($canBeProcessed,$date) {
			if (count($canBeProcessed) > 0) {
					$sql = "UPDATE `product_order` SET `processed` = 1 WHERE `barcode` IN ( ";
					$sql_shipped = "INSERT INTO `product_shipped` (`barcode`, `date`, `store_id`, `quantity`) SELECT `barcode`, `date`, `store_id`, `quantity` FROM `product_order` WHERE `barcode` IN ( ";
					for ($j = 0; $j < count($canBeProcessed); $j++) {
						$this->processBarcodeOrder($canBeProcessed[$j]["barcode"],$canBeProcessed[$j]["quantity"]);
						if ($j > 0) {
							$sql.=" , ";
							$sql_shipped.=" , ";
						}
						$sql.=$canBeProcessed[$j]["barcode"];
						$sql_shipped.=$canBeProcessed[$j]["barcode"];
					}
					$sql.=" ) AND `processed` = 0";
					$sql_shipped.=" ) AND `processed` = 0"; 
					
					if ($date != null) {
						$sql.='  AND `date` = "'.$date.'"';
						$sql_shipped.='  AND `date` = "'.$date.'"';
					}
					
					$res = mysql_query($sql_shipped,$this->connection);
					if (!$res) throw new Exception("Database access failed: " . mysql_error());
					
					$res = mysql_query($sql,$this->connection);
					if (!$res) throw new Exception("Database access failed: " . mysql_error());
			}
		}

		function checkProcessableOrder($availableBarcode, $toBeOrdered){
			$canBeProcessed = array();
			$cannotBeProcessed = array();
			$cannotBeProcessedIndex = array();
			$normalizing = 0;
			if (count($toBeOrdered)> count($availableBarcode)){
				for ($j  = 0; $j < count($toBeOrdered); $j++) {
					if ($toBeOrdered[$j]["barcode"]!= $availableBarcode[$j-$normalizing]["barcode"]) {
						$cannotBeProcessed[] = array(
							"barcode" => $toBeOrdered[$j]["barcode"],
							"quantity" => $toBeOrdered[$j]["quantity"]
						);
						$normalizing+=1;				
					} else if($toBeOrdered[$j]["quantity"]> $availableBarcode[$j-$normalizing]["stock"]){
						$cannotBeProcessed[]= array(
							"barcode" => $toBeOrdered[$j]["barcode"],
							"quantity" => $toBeOrdered[$j]["quantity"]
						);
					} else
						$canBeProcessed[] = array(
							"barcode" => $toBeOrdered[$j]["barcode"],
							"quantity" => $toBeOrdered[$j]["quantity"]
						);
				}
			} else if (count($toBeOrdered)== count($availableBarcode)) {
				for ($j  = 0; $j < count($toBeOrdered); $j++){
					if($toBeOrdered[$j]["quantity"]> $availableBarcode[$j]["stock"]){
						$cannotBeProcessed[] =  array(
							"barcode" => $toBeOrdered[$j]["barcode"],
							"quantity" => $toBeOrdered[$j]["quantity"]
						);
					} else {
						$canBeProcessed[] = array(
							"barcode" => $toBeOrdered[$j]["barcode"],
							"quantity" => $toBeOrdered[$j]["quantity"]
						);
					}
				}
			}
			$retArr = array (
								"canBeProcessed" => $canBeProcessed,
								"cannotBeProcessed" => $cannotBeProcessed
							);
			return $retArr;
		}
	}
?>