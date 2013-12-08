<?php
	class  WebStoreController {
		private $connection;
		function __construct($conn){
			$this->connection = $conn;
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


		function readJson()	{
			date_default_timezone_set("Asia/Singapore");
			$date = date('Y-m-d');
			//webstore is always id 0
			$shop_id = 0;
			$file = 'receive/'.$shop_id.'.json';
			$json = json_decode(file_get_contents($file), true);
			$orderList = array();
			$i=0;
			//Webstore does not have writeoff
			foreach($json['products'] as $data)
			{
				$barcode = $data['barcode'];
				$quantity = $this->decrypt($data['quantity']);
				$sales = $this->decrypt($data['sales']);
				if($quantity>0) {
					$orderList[$i] = array(
						"barcode" => $barcode,
						"date" => date("Y-m-d"),
						"store_id" => 0,
						"quantity" => $quantity
					);
				}
				mysql_query("INSERT INTO product_sales(barcode, date, store_id, sales, writeoff) VALUES ('$barcode', '$date', '$shop_id', '$sales', 0)",$this->connection);
			}
			unlink('receive/'.$shop_id.'.json');
		}
		
		function retrieveWebStoreOrders() {
			$toBeShipped = $this->readJson;
			return $toBeShipped;
		}
		//assumption the warehouse always have enough stock for webstore
		function processToBeShipped($toBeShipped) {
			if (count($toBeShipped) > 0) {
					$sql_shipped = "INSERT INTO `product_shipped` (`barcode`, `date`, `store_id`, `quantity`) VALUES ";
					for ($j = 0; $j < count($toBeShipped); $j++) {
						$this->processBarcodeOrder($toBeShipped[$j]["barcode"],$toBeShipped[$j]["quantity"]);
						$sql_shipped.='( '.$toBeShipped[$j]["barcode"].' , CURDATE() , 0 , '.$toBeShipped[$j]["quantity"].' ) ';
						if ($j< count($toBeShipped)-1) {
							$sql_shipped.=" , ";
						}						
					}

					$res = mysql_query($sql_shipped,$this->connection);
					if (!$res) throw new Exception("Database access failed: " . mysql_error());	
			}
		}
		
		function processBarcodeShipment( $barcode, $quantity){
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
		
		function sendStatistics(){
			$posts = array();
			$response = array();
			
			//webstore is always store 0, thus this store is special and will only receive the total amount of stocks in the system
			$sql = 'SELECT `barcode`, SUM(`stock`) as `stock` FROM `warehouse` GROUP BY `barcode`';
			$res = mysql_query($sql,$this->connection);
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			$rows = mysql_num_rows($res);
			$shipment = array();
			for ($j = 0 ; $j < $rows ; $j++)
			{
				$barcode = mysql_result($res,$j,'barcode');
				$quantity = mysql_result($res,$j,'stock');
					$shipment[0][] = array( 
												'barcode' => $barcode,
												'quantity' => $this->encrypt($quantity)
												);
			}
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
											"name" =>  $this->encrypt(mysql_result($res,$j,'name')),
											"category" =>  $this->encrypt(mysql_result($res,$j,'category')),
											"manufacturer" =>  $this->encrypt(mysql_result($res,$j,'manufacturer')),
											"costprice" => $this->encrypt(mysql_result($res,$j,'costprice')),
											"deleted" =>  $this->encrypt(mysql_result($res,$j,'deleted'))
											);
			}
			$sql = 'SELECT `id`, `password` FROM `local_stores` WHERE `id` = 0 AND `deleted` = 0';
			$res = mysql_query($sql,$this->connection);
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			$passwords = array();
			$passwords[mysql_result($res,0,'id')] = mysql_result($res,0,'password');
			foreach ($shipment as $store => $barcodeShipped){
				$filename = 'download/'.$store.'-'.$passwords[$store].'.json';
				$response['shipment_list'] = $barcodeShipped;
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
		
	}
?>