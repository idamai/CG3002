<?php
	class  OrderController {
		private $connection;
		function __construct($conn){
			$this->connection = $conn;
		}
		
		// function to load JSON file
		function loadOrder() {
			
		}
		function encrypt($sData, $sKey='54h6vhcg4c4gl'){ 
			$sResult = ''; 
			for($i=0;$i<strlen($sData);$i++){ 
				$sChar    = substr($sData, $i, 1); 
				$sKeyChar = substr($sKey, ($i % strlen($sKey)) - 1, 1); 
				$sChar    = chr(ord($sChar) + ord($sKeyChar)); 
				$sResult .= $sChar; 
			} 
			return $this->encode_base64($sResult); 
		} 

		//call to decrypt. Use same sKey for encrypt and decrypt
		function decrypt($sData, $sKey='54h6vhcg4c4gl'){ 
			$sResult = ''; 
			$sData   = $this->decode_base64($sData); 
			for($i=0;$i<strlen($sData);$i++){ 
				$sChar    = substr($sData, $i, 1); 
				$sKeyChar = substr($sKey, ($i % strlen($sKey)) - 1, 1); 
				$sChar    = chr(ord($sChar) - ord($sKeyChar)); 
				$sResult .= $sChar; 
			} 
			return $sResult; 
		} 


		function encode_base64($sData){ 
			$sBase64 = base64_encode($sData); 
			return strtr($sBase64, '+/', '-_'); 
		} 

		function decode_base64($sData){ 
			$sBase64 = strtr($sData, '-_', '+/'); 
			return base64_decode($sBase64); 
		} 


		function readJson()
		{
			$query = mysql_query("SELECT id,deleted FROM local_stores WHERE id != 0",$this->connection);
			date_default_timezone_set("Asia/Singapore");
			$date = date('Y-m-d');
			while($row = mysql_fetch_array($query))
			{
				if ($row['deleted'] == 0) 
				{
					$shop_id = $row['id'];
					$file = 'receive/'.$row['id'].'.json';
					$json = json_decode(file_get_contents($file), true);
					foreach($json['products'] as $data)
					{
						$barcode = $data['barcode'];
						$quantity = $this->decrypt($data['quantity']);
						$sales = $this->decrypt($data['sales']);
						$writeoff = $this->decrypt($data['write-off']);
						if($quantity>0) {
							mysql_query("INSERT INTO product_order(barcode, date, store_id, quantity, processed) VALUES ('$barcode', '$date', '$shop_id', '$quantity', b'0')",$this->connection);
						}
						mysql_query("INSERT INTO product_sales(barcode, date, store_id, sales, writeoff) VALUES ('$barcode', '$date', '$shop_id', '$sales', '$writeoff')",$this->connection);
					}
					unlink('receive/'.$shop_id.'.json');
				}
			}
		}
		
		function manualAddOrder($barcode,$date,$store_id,$quantity) {
			$barcode = mysql_real_escape_string($barcode);
			$date = mysql_real_escape_string($date);
			$store_id = mysql_real_escape_string($store_id);
			$quantity = mysql_real_escape_string($quantity);
			$sql = "INSERT INTO `product_order` (`barcode`, `date`, `store_id`, `quantity`, `processed`) VALUES ( ".$barcode." , '".$date."' , ".$store_id." , ".$quantity." , 0 )";
			$res = mysql_query($sql, $this->connection);
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
		}

		function processShipment(){
			$posts = array();
			$response = array();
			
			$sql = 'SELECT `barcode`,`quantity`, `store_id` FROM `product_shipped` WHERE ';
			$sql.='`processed` = 0';
			$res = mysql_query($sql,$this->connection);
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			$rows = mysql_num_rows($res);
			$shipment =  array();
			for ($j = 0 ; $j < $rows ; $j++)
			{
				$store_id = mysql_result($res,$j,'store_id');
				$barcode = mysql_result($res,$j,'barcode');
				$quantity = mysql_result($res,$j,'quantity');
				$shipment[$store_id][] = array( 
											'barcode' => $barcode,
											'quantity' => $this->encrypt($quantity)
											);
			}
			//retreive and send updated product list 
			$sql = 'UPDATE `product_shipped` SET  `processed` = 1 WHERE `processed` = 0';
			$res = mysql_query($sql,$this->connection);
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			
			$sql = 'SELECT p.`barcode`, p.`name`, p.`category`, p.`manufacturer`, (p.`cost` * pm.`margin_multiplier`) AS `costprice`, `deleted` FROM `product` p INNER JOIN `price_modifier` pm ON pm.`barcode` = p.`barcode`';
			$res = mysql_query($sql,$this->connection);
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			$rows = mysql_num_rows($res);
			$productsList = array();
			for ($j = 0 ; $j < $rows ; $j++){
				$productList[$j] = array(	
											"barcode" => mysql_result($res,$j,'barcode'),
											"name" => mysql_result($res,$j,'name'),
											"category" => mysql_result($res,$j,'category'),
											"manufacturer" => mysql_result($res,$j,'manufacturer'),
											"costprice" => $this->encrypt(mysql_result($res,$j,'costprice')),
											"deleted" => mysql_result($res,$j,'deleted')
											);
			}
			$sql = 'SELECT `id`, `password` FROM `local_stores` WHERE `id` != 0 AND `deleted` = 0';
			$res = mysql_query($sql,$this->connection);
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			$rows = mysql_num_rows($res);
			$passwords = array();
			for ($j = 0 ; $j < $rows ; $j++){
				$passwords[mysql_result($res,$j,'id')] = mysql_result($res,$j,'password');
			}
			foreach ($shipment as $store => $barcodeShipped){
				$filename = 'download/'.$store.'-'.$passwords[$store].'.json';
				$response['shipment_list'] = $barcodeShipped;
				//balance sheet operation
				$sql= "SELECT `barcode`, `cost` from `product`";
				$res = mysql_query($sql,$this->connection);
				if (!$res) throw new Exception("Database access failed: " . mysql_error());
				$rows = mysql_num_rows($res);
				$productCost = array();
				for ($i=0; $i<$rows;$i++) {
						$productCost[mysql_result($res,$j,'barcode')] = mysql_result($res,$j,'cost');
				}
				$totalCost = 0;
				for ($i=0; $i < count($barcodeShipped); $i++) {
						$totalCost+=$productCost[$barcodeShipped[$i]['barcode']]*$barcodeShipped[$i]['quantity'];
				}
				$sql= "SELECT * FROM `balance_sheet` WHERE `date` = CURDATE() AND `account` = 701 AND `store_id` = ".$store;
				$res = mysql_query($sql,$this->connection);
				if (!$res) throw new Exception("Database access failed: " . mysql_error());
				$rows = mysql_num_rows($res);
				if  ($rows == 0) {
					$sql = "INSERT INTO `balance_sheet` ( `date` , `account` , `store_id` , `amount` ) VALUES ( CURDATE() , 701, ".$store." , ".$totalCost." )";
					$res = mysql_query($sql,$this->connection);
					if (!$res) throw new Exception("Database access failed: " . mysql_error());
				} else {
					$sql = "UPDATE `balance_sheet` SET `amount` = `amount` + ".$totalCost." WHERE `store_id` = ".$store." AND `account` = 701 AND `date` = CURDATE()";
					$res = mysql_query($sql,$this->connection);
					if (!$res) throw new Exception("Database access failed: " . mysql_error());
				}
				//next we calculate profit
				$sql= "SELECT * FROM `balance_sheet` WHERE `date` = CURDATE() AND `account` = 601 AND `store_id` = ".$store;
				$res = mysql_query($sql,$this->connection);
				if (!$res) throw new Exception("Database access failed: " . mysql_error());
				$rows = mysql_num_rows($res);
				$profit = 0;
				if  ($rows == 0) {
					$profit = 0 - $totalCost;
				} else {
					$revenue = mysql_result($res,0,'amount');
					$profit = $revenue - $totalCost;
				}
				
				$sql= "SELECT * FROM `balance_sheet` WHERE `date` = CURDATE() AND `account` = 801 AND `store_id` = ".$store;
				$res = mysql_query($sql,$this->connection);
				if (!$res) throw new Exception("Database access failed: " . mysql_error());
				$rows = mysql_num_rows($res);
				if  ($rows == 0) {
					$sql = "INSERT INTO `balance_sheet` ( `date` , `account` , `store_id` , `amount` ) VALUES ( CURDATE() , 801, ".$store." , ".$profit." )";
					$res = mysql_query($sql,$this->connection);
					if (!$res) throw new Exception("Database access failed: " . mysql_error());
				} else {
					$sql = "UPDATE `balance_sheet` SET `amount` = `amount` + ".$profit." WHERE `store_id` = ".$store." AND `account` = 801 AND `date` = CURDATE()";
					$res = mysql_query($sql,$this->connection);
					if (!$res) throw new Exception("Database access failed: " . mysql_error());
				}
				
				$response['product_list'] = $productList;
				$fp = fopen($filename, 'w');
				//fwrite($fp, $response);
				//print_r($response);
				fwrite($fp, '{"shipment_list":[');				
				fclose($fp);
				$fp = fopen($filename, 'a');
				$max_length = count ($response['shipment_list']);
				$counter = 0;
				foreach ($response['shipment_list'] as $index => $shipment){
					fwrite($fp, json_encode($shipment));
					if ($counter!= $max_length-1)
						fwrite($fp, ',');
					$counter++;
				}
				fwrite($fp,'],"product_list":[');
				$max_length = count ($response['product_list']);
				$counter = 0;
				foreach ($response['product_list'] as $index => $product){
					fwrite($fp, json_encode($product));
					if ($counter!= $max_length-1)
						fwrite($fp, ',');
					else
						fwrite($fp, ']}');					
					$counter++;
				}
				fclose($fp);
			}	
		}

		function retreiveTotalShippedOrder(){
		   $sql = "SELECT COUNT(*) AS `total` FROM `product_shipped`";
		   $res = mysql_query($sql, $this->connection);
		  if (!$res) throw new Exception("Database access failed: " . mysql_error());
		  $totalItems =  mysql_result($res,0,'total');
		  return $totalItems;
		}
		function getAllShippedOrder($offset) {
		  $offset = mysql_real_escape_string($offset);
		  $sql = "SELECT * FROM `product_shipped` LIMIT 70 OFFSET ".$offset;
		  $res = mysql_query($sql,$this->connection);
		  if (!$res) throw new Exception("Database access failed: " . mysql_error());
		  $rows = mysql_num_rows($res);
		  $shippedList =  array();
		  for ($j = 0 ; $j < $rows ; $j++)
		  {
			$shippedList[$j] = array(
							"barcode" => mysql_result($res,$j,'barcode'),
							"date" => mysql_result($res,$j,'date'),
							"store_id" => mysql_result($res,$j,'store_id'),
							"quantity" => mysql_result($res,$j,'quantity')
						  );    
		  }
		  return $shippedList;
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

		function retreiveTotalUnprocessedOrder(){
		   $sql = "SELECT COUNT(*) AS `total` FROM `product_order` WHERE `processed` = 0 ";
		   $res = mysql_query($sql, $this->connection);
		   if (!$res) throw new Exception("Database access failed: " . mysql_error());
		   $totalItems =  mysql_result($res,0,'total');
		   return $totalItems;
		 }
		 
		 function getAllUnprocessedOrder($offset) {    
		    $offset = mysql_real_escape_string($offset);
		    $sql = "SELECT * FROM `product_order` WHERE `processed` = 0 LIMIT 70 OFFSET ".$offset;
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