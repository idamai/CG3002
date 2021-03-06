<?php
	class ProductListController{
		private $connection;
		function __construct($conn) {
			$this->connection = $conn;
		}
		
		function retrieveTotalProducts() {
			$sql = "SELECT COUNT(*) as `total` FROM `product` WHERE `deleted` = 0";
			$res = mysql_query($sql, $this->connection);
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			$totalItems =  mysql_result($res,0,'total');
			return $totalItems;
		}
		
		function retrieveProductList($offset) {
			$offset = mysql_real_escape_string($offset);
			$sql = "SELECT `barcode`,`name`,`category`,`manufacturer`,`cost` FROM `product` WHERE `deleted` = 0 LIMIT 70 OFFSET ".$offset;
			$res = mysql_query($sql,$this->connection);
			
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			$rows = mysql_num_rows($res);
			$productList =  array();
			for ($j = 0 ; $j < $rows ; $j++)
			{
				$productList[$j] = array(
												"barcode" => mysql_result($res,$j,'barcode'),
												"name" => mysql_result($res,$j,'name'),
												"category" => mysql_result($res,$j,'category'),
												"manufacturer" => mysql_result($res,$j,'manufacturer'),
												"cost" => mysql_result($res,$j,'cost')
											);
			}
			return $productList;
		}
		
		function addNewProduct($barcode, $name, $category, $manufacturer, $cost, $minimal_stock){
			$barcode = mysql_real_escape_string($barcode);
			$name = mysql_real_escape_string($name);
			$category = mysql_real_escape_string($category);
			$manufacturer = mysql_real_escape_string($manufacturer);
			$cost  = mysql_real_escape_string($cost);
			$minimal_stock  = mysql_real_escape_string($minimal_stock);
			$sql = 'INSERT INTO `product` (`barcode` , `name`, `category`, `manufacturer`, `cost`, `minimal_stock`) VALUES ( '.$barcode.' , "'.$name. '" , "'.$category.'" , "'.$manufacturer.'" , '. $cost.' , '.$minimal_stock.' )';
			$res = mysql_query($sql,$this->connection);
		
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			
			$sql = 'INSERT INTO `price_modifier` (`barcode` , `margin_multiplier`, `tax` , `q_star` , `update_date` ) VALUES ( '.$barcode.' , 1.25 , 7 , '.($minimal_stock*5).' , CURDATE() )';
			$res = mysql_query($sql,$this->connection);
			
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
		}
		
		function retrieveProductInfo($barcode) {
			$sql = 'SELECT`barcode` , `name`, `category`, `manufacturer`, `cost`, `minimal_stock` FROM `product` WHERE `barcode` = '.$barcode;
			$res = mysql_query($sql,$this->connection);
		
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			$rows = mysql_num_rows($res);
			$productList =  array();
			for ($j = 0 ; $j < $rows ; $j++)
			{
				$productList[$j] = array(
												"barcode" => mysql_result($res,$j,'barcode'),
												"name" => strip_tags (mysql_result($res,$j,'name')),
												"category" => strip_tags (mysql_result($res,$j,'category')),
												"manufacturer" => strip_tags (mysql_result($res,$j,'manufacturer')),
												"cost" => mysql_result($res,$j,'cost'),
												"minimal_stock" => mysql_result($res,$j,'minimal_stock')
											);
			}
			return $productList[0];
			
		}
		function retrieveAllBarcode() {
			$sql = "SELECT `barcode` FROM `product` WHERE `deleted` = 0";
			$res = mysql_query($sql,$this->connection);
			
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			$rows = mysql_num_rows($res);
			$barcodeList =  array();
			for ($j = 0 ; $j < $rows ; $j++)
			{
				$barcodeList[$j] = mysql_result($res,$j,'barcode');
			}
			return $barcodeList;
		}
		function editProductInformation($barcode, $name, $category, $manufacturer, $cost, $minimal_stock) {
			$barcode = mysql_real_escape_string($barcode);
			$name = mysql_real_escape_string($name);
			$category = mysql_real_escape_string($category);
			$manufacturer = mysql_real_escape_string($manufacturer);
			$cost = mysql_real_escape_string($cost);
			$minimal_stock = mysql_real_escape_string($minimal_stock);
			$sql = 'UPDATE `product` SET  `name` = "'.$name.'" , `category` = "'.$category.'" , `manufacturer` = "'.$manufacturer.'" , `cost` = '.$cost.' , `minimal_stock` = '.$minimal_stock.' WHERE `barcode` = '.$barcode;
			$res = mysql_query($sql,$this->connection);

			if (!$res) throw new Exception("Database access failed: " . mysql_error());
		}
		
		function deleteProduct($barcode) {
			$barcode = mysql_real_escape_string($barcode);
			$sql = 'UPDATE `product` SET  `deleted` = 1 WHERE `barcode` = '.$barcode;
			$res = mysql_query($sql,$this->connection);

			if (!$res) throw new Exception("Database access failed: " . mysql_error());
		}
		
	}
	
?>