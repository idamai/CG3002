<?php
	require_once("../../objects/Model/Product.php");
	class ProductListController{
		private $connection;
		function __construct($conn) {
			$this->connection = $conn;
		}
		
		function retrieveProductList() {
			$sql = "SELECT `barcode`,`name`,`category`,`manufacturer`,`cost` FROM `product`";
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
			
		}
		
	}
	
?>