<?php
require_once ("../../objects/settings.php");
require_once("../../objects/login.php");


$OK = "ok";
$FAIL = "fail";
$ERROR = "error";
//preparation for further implementation
$LOGIN = "login";
$ACTIVE_NONE = "none-active";
$ACTIVE_PRODUCT = "product-list";
$ACTIVE_STORE = "store-list";
$ACTIVE_ORDER = "order-list";
$ACTIVE_SHIPPED = "shipped-list";
$ACTIVE_PRICING = "price-list";

$conn = dbconnect($dbconn);
$webInput = $_GET["data"];
if (!$webInput){
	$webInput = $_POST["data"];
}
$p = $webInput;
if (!$webInput){
	$webInput = $_GET["json_parameter"];
	if ($webInput)
		$p = json_decode($webInput, true);
}
if (!$webInput){
	$webInput = $_POST["json_parameter"];
	if ($webInput)
		$p = json_decode($webInput, true);
}


$retArr = array();
$retArr["_handle"] = $p["_handle"]; //An echo'd key that helps the api handler to uniquely reference requests

//debug purpose
$retArr["captured"] = $p;

// setup -------------------------------------------------

// -------------------------------------------------------

try{
	switch ($p["action"]){
	case "retrieve_product":
		require_once("../../objects/Controller/ProductListController.php");
		$plc = new ProductListController($conn);
		$retArr["result"] = $plc->retrieveProductList();
		$retArr["status"] = $OK;
		break;
	case "restock_all_product":
		require_once("../../objects/Controller/ProductListController.php");
		require_once("../../objects/Controller/WarehouseController.php");
		$plc = new ProductListController($conn);
		$wc = new WarehouseController($conn);
		$wc->addStockForAll();
		 $retArr["result"] = $plc->retrieveProductList();
		$retArr["status"] = $OK;
		break;
	case "add_new_product":
		require_once("../../objects/Controller/ProductListController.php");
		$barcode = $p["barcode"];
		$name = $p["name"];
		$category = $p["category"];
		$manufacturer = $p["manufacturer"];
		$cost = $p["cost"];
		$minimal_stock = $p["minimal_stock"];
		$plc = new ProductListController($conn);
		$plc->addNewProduct($barcode, $name, $category, $manufacturer, $cost, $minimal_stock);
		$retArr["result"] = $plc->retrieveProductList();
		$retArr["status"] = $OK;
		break;
	case "retrieve_product_info":
		require_once("../../objects/Controller/ProductListController.php");
		$barcode = $p["barcode"];
		$plc = new ProductListController($conn);
		$retArr["result"] = $plc->retrieveProductInfo($barcode);
		$retArr["status"] = $OK;
		break;
	case "edit_product":
		require_once("../../objects/Controller/ProductListController.php");
		$barcode = $p["barcode"];
		$name = $p["name"];
		$category = $p["category"];
		$manufacturer = $p["manufacturer"];
		$cost = $p["cost"];
		$minimal_stock = $p["minimal_stock"];
		$plc = new ProductListController($conn);
		$plc->editProductInformation($barcode, $name, $category, $manufacturer, $cost, $minimal_stock);
		$retArr["result"] = $plc->retrieveProductList($barcode);
		$retArr["status"] = $OK;
		break;
	case "delete_product":
		require_once("../../objects/Controller/ProductListController.php");
		$barcode = $p["barcode"];
		$plc = new ProductListController($conn);
		$plc->deleteProduct($barcode, $name, $category, $manufacturer, $cost, $minimal_stock);
		$retArr["deletedBarcode"] = $barcode;
		$retArr["result"] = $plc->retrieveProductList($barcode);
		$retArr["status"] = $OK;
		break;
	case "retrieve_stock":
		require_once("../../objects/Controller/WarehouseController.php");
		$wc = new WarehouseController($conn);
		
		$barcode = $p["barcode"];
		
		$retArr["result"] =  $wc->retrieveStockDetails($barcode);
		$retArr["barcode"]= $barcode;
		
		$retArr["status"] = $OK;
		break;
	
	case "update_batch_stock":
		require_once("../../objects/Controller/WarehouseController.php");
		$wc = new WarehouseController($conn);
		
		$barcode = $p["barcode"];
		$quantity = $p["quantity"];
		$date =  $p["date"];
		
		$wc->addNewStock($barcode, $quantity, $date);
		
		$availableStocks = $wc->retrieveStockDetails($barcode);
		$retArr["barcode"] = $barcode;
		$retArr["result"] = $availableStocks;
		$retArr["status"] = $OK;
		//-----not finished----
		break;
	case "retrieve_order_list":
		require_once("../../objects/Controller/OrderController.php");
		$oc = new OrderController($conn);
		$retArr["result"] =  $oc->getAllUnprocessedOrder($conn);
		$retArr["status"] = $OK;
		break;
    case "import_order_list":
		require_once("../../objects/Controller/OrderController.php");
		$oc = new OrderController($conn);
		$oc->readJson();
		$retArr["result"] =  $oc->getAllUnprocessedOrder($conn);
		$retArr["status"] = $OK;
		break;
	case "retrieve_shipped_list":
		$sql = 'SELECT * FROM `product_shipped` ORDER BY `date` DESC';
		$res = mysql_query($sql,$conn);
		if (!$res) throw new Exception("Database access failed: " . mysql_error());
		$rows = mysql_num_rows($res);
		$retArr["result"] =  array();
		for ($j = 0 ; $j < $rows ; $j++)
		{
			$retArr["result"][$j] = array(
											"barcode" => mysql_result($res,$j,'barcode'),
											"date" => mysql_result($res,$j,'date'),
											"store_id" => mysql_result($res,$j,'store_id'),
											"quantity" => mysql_result($res,$j,'quantity')
										);		
		}
		$retArr["status"] = $OK;
		break;
	//this is for receiving stock for 1 item. mutilple item version not updateed
	case "receive_stock":
		$barcode = $p["barcode"];
		$date = $p["batchdate"];
		$quantity = $p["quantity"];				
		$date = date('Y-m-d',strtotime($date));	
		
		$date = mysql_real_escape_string($date);
		$barcode = mysql_real_escape_string($barcode);
		$quantity = mysql_real_escape_string($quantity);
		//insert new stock
		$sql = 'INSERT INTO `warehouse` (`barcode`, `stock`, `batchdate`) VALUES ('.$barcode.' , '.$quantity.' , "'.$date.'")';
		$res = mysql_query($sql,$conn);
		if (!$res) throw new Exception("Database access failed: " . mysql_error());
		
		//view stock
		$sql = "SELECT `batchdate`, `stock` FROM `warehouse` WHERE barcode = ".$barcode;
		$res = mysql_query($sql,$conn);
		if (!$res) throw new Exception("Database access failed: " . mysql_error());
		$rows = mysql_num_rows($res);
		$retArr["result"] =  array();
		for ($j = 0 ; $j < $rows ; $j++)
		{
			$retArr["result"][$j] = array(
											"batchdate" => mysql_result($res,$j,'batchdate'),
											"stock" => mysql_result($res,$j,'stock')
										);		
		}
		$retArr["barcode"] = $p["barcode"];;
		$retArr["status"] = $OK;
		break;
	case "update_stock_batch":
		break;
	//function to run stock recording for multiple products
	case "record_stock":
		//load from file
		$file=fopen("welcome.txt","r") or exit("Unable to open file!");
		break;
	case "populate_unprocessed_order_date":
		require_once("../../objects/Controller/OrderController.php");
		$oc = new OrderController($conn);
		$retArr["result"] = $oc->getUnprocessedOrderDates();
		$retArr["status"] = $OK;
		break;
	case "populate_unprocessed_order_barcode":
		require_once("../../objects/Controller/OrderController.php");
		$oc = new OrderController($conn);
		$retArr["result"] = $oc->getUnprocessedOrderBarcodes();
		$retArr["status"] = $OK;
		break;
	case "process_order_date":
		require_once("../../objects/Controller/OrderController.php");
		require_once("../../objects/Controller/WarehouseController.php");
		$oc = new OrderController($conn);
		$wc = new WarehouseController($conn);
		
		$date = date('Y-m-d',strtotime($p["date"]));
		
		$toBeOrdered = $oc->getAllOrderForDate($date);		
		
		$availableBarcode = $wc->retrieveStockForOrder($toBeOrdered);
		
		//check whether all of the stocks are sufficient
		$processableList = $oc->checkProcessableOrder($availableBarcode, $toBeOrdered);
		
		//process the sufficient stocks
		$oc->processOrder($processableList["canBeProcessed"],$date);
		
		
		require_once("../../objects/Controller/PricingController.php");		
		$pc  = new PricingController($conn);
		
		
		$retArr["result"] = $oc->getAllUnprocessedOrder();
		$retArr["notProcessed"]= $processableList["cannotBeProcessed"];
		if (count($processableList["cannotBeProcessed"])>0)
			$retArr["leftover_order"] = true;
		else
			$retArr["leftover_order"] = false;
		$retArr["status"] = $OK;
		break;
		break;
	case "process_order_unprocessed":
		//grab the total number of orders per barcode
		require_once("../../objects/Controller/OrderController.php");
		require_once("../../objects/Controller/WarehouseController.php");
		$oc = new OrderController($conn);
		$wc = new WarehouseController($conn);
		
		$toBeOrdered = $oc->getAllOrderPerBarcode();
		//grab the total available stocks per barcode
		$availableBarcode = $wc->retrieveStockForOrder($toBeOrdered);
		
		//check whether all of the stocks are sufficient
		$processableList = $oc->checkProcessableOrder($availableBarcode, $toBeOrdered);
		
		//process the sufficient stocks
		//$oc->processOrder($processableList["canBeProcessed"],null,$conn);
		//leave the not processed barcode
		
		require_once("../../objects/Controller/PricingController.php");		
		$pc  = new PricingController($conn);
		
		$availableStocks = $wc->retrieveTotalProductStock();
		$pc->updatePricing($availableStocks);
		//BUGFIX : call after update pricing
		$oc->processOrder($processableList["canBeProcessed"],null,$conn);
		
		$retArr["result"] = $oc->getAllUnprocessedOrder();
		$retArr["notProcessed"]= $processableList["cannotBeProcessed"];
		if (count($processableList["cannotBeProcessed"])>0)
			$retArr["leftover_order"] = true;
		else
			$retArr["leftover_order"] = false;
		$retArr["status"] = $OK;
		break;
	case "process_order_barcode":
		require_once("../../objects/Controller/OrderController.php");
		require_once("../../objects/Controller/WarehouseController.php");
		$oc = new OrderController($conn);
		$wc = new WarehouseController($conn);
		
		$barcode = $p["barcode"];
		$toBeOrdered = $oc->getAllOrderForBarcode($barcode);
		//grab the total available stocks per barcode
		$availableBarcode = $wc->retrieveStockForOrder($toBeOrdered);
		
		//check whether all of the stocks are sufficient
		$processableList = $oc->checkProcessableOrder($availableBarcode, $toBeOrdered);		
		//process the sufficient stocks
		$oc->processOrder($processableList["canBeProcessed"],null);
		require_once("../../objects/Controller/PricingController.php");		
		$pc  = new PricingController($conn);
		$availableStocks = $wc->retrieveTotalProductStock();
		$pc->updatePricing($availableStocks);
		$retArr["result"] = $oc->getAllUnprocessedOrder();
		$retArr["notProcessed"]= $processableList["cannotBeProcessed"];
		if (count($processableList["cannotBeProcessed"])>0)
			$retArr["leftover_order"] = true;
		else
			$retArr["leftover_order"] = false;
		$retArr["status"] = $OK;
		break;
	case "retrieve_store":
		require_once("../../objects/Controller/StoreListController.php");
		$slc = new StoreListController($conn);
		$retArr["result"] = $slc->retrieveStoreList();
		$retArr["status"] = $OK;
		break;
	case "add_store":
		$store_id = $p["store_id"];
		$name = $p["name"];
		$location  = $p["location"];
		$password = $p["password"];
		require_once("../../objects/Controller/StoreListController.php");
		$slc = new StoreListController($conn);
		$slc->addNewStore($store_id, $name, $location, $password);
		$retArr["result"] = $slc->retrieveStoreList();
		$retArr["status"] = $OK;
		break;
	case "retrieve_store_info":
		require_once("../../objects/Controller/StoreListController.php");
		$slc = new StoreListController($conn);
		$store_id = $p["store_id"];
		$retArr["result"] = $slc->retrieveStoreInfo($store_id);
		$retArr["status"] = $OK;
		break;
	case "edit_store":
		$store_id = $p["store_id"];
		$name = $p["name"];
		$location  = $p["location"];
		$password = $p["password"];
		require_once("../../objects/Controller/StoreListController.php");
		$slc = new StoreListController($conn);
		$store_id = $p["store_id"];
		$slc->editStoreInformation($store_id, $name, $location, $password);
		$retArr["result"] = $slc->retrieveStoreList();
		$retArr["status"] = $OK;
		break;
	case "delete_store":
		require_once("../../objects/Controller/StoreListController.php");
		$slc = new StoreListController($conn);
		$store_id = $p["store_id"];
		$slc->deleteStore($store_id);
		$retArr["store_id"] = $store_id;
		$retArr["result"] =  $slc->retrieveStoreList();
		$retArr["status"] = $OK;
		break;
	case "retrieve_pricing_list":
		require_once("../../objects/Controller/PricingController.php");
		$pc  = new PricingController($conn);
		$retArr["result"] = $pc->retrievePricingList();
		$retArr["status"] = $OK;
		break;
	case "update_pricing":
		require_once("../../objects/Controller/PricingController.php");		
		require_once("../../objects/Controller/WarehouseController.php");
		$wc = new WarehouseController($conn);
		$pc  = new PricingController($conn);
		$availableStocks = $wc->retrieveTotalProductStock();
		$pc->updatePricing($availableStocks);
		$retArr["result"] = $pc->retrievePricingList();		
		$retArr["status"] = $OK;
		break;
	case "read_order":
		require_once("../../objects/Controller/OrderController.php");
		$oc = new OrderController($conn);
		
		break;
	case "search_data_base":
		$key = $p["key"];
		$key = mysql_real_escape_string($key);
		$mode = $p["mode"];
		/*
			Supported modes:
						   ACTIVE_NONE : "none-active",
						   ACTIVE_PRODUCT : "product-list",
						   ACTIVE_STORE : "store-list",
						   ACTIVE_ORDER : "order-list",
						   ACTIVE_SHIPPED : "shipped-list",
						   ACTIVE_PRICING : "price-list"
		*/
		switch($mode){
			case $ACTIVE_PRODUCT:
				$sql = "SELECT `barcode`,`name`,`category`,`manufacturer`,`cost` FROM `product` WHERE `deleted` = 0 AND ( `barcode` LIKE '%$key%' OR `name` LIKE '%$key%' OR `category` LIKE '%$key%' OR `manufacturer` LIKE '%$key%' OR `cost` LIKE '%$key%')";
				$res = mysql_query($sql,$conn);
				
				if (!$res) throw new Exception("Database access failed: " . mysql_error());
				$rows = mysql_num_rows($res);
				$result =  array();
				for ($j = 0 ; $j < $rows ; $j++)
				{
					$result[$j] = array(
													"barcode" => mysql_result($res,$j,'barcode'),
													"name" => mysql_result($res,$j,'name'),
													"category" => mysql_result($res,$j,'category'),
													"manufacturer" => mysql_result($res,$j,'manufacturer'),
													"cost" => mysql_result($res,$j,'cost')
												);
				}
				break;
			case $ACTIVE_STORE:
				$sql = "SELECT `id`,`name`,`location` FROM `local_stores` WHERE `deleted` = 0  AND ( `id` LIKE '%$key%' OR `name` LIKE '%$key%' OR `location` LIKE '%$key%')";
				$res = mysql_query($sql,$conn);
				
				if (!$res) throw new Exception("Database access failed: " . mysql_error());
				$rows = mysql_num_rows($res);
				$result =  array();
				for ($j = 0 ; $j < $rows ; $j++)
				{
					$result[$j] = array(
													"store_id" => mysql_result($res,$j,'id'),
													"store_name" => mysql_result($res,$j,'name'),
													"store_loc" => mysql_result($res,$j,'location')
												);		
				}
				break;
			case $ACTIVE_ORDER:
				$sql = "SELECT * FROM `product_order` WHERE `processed` = 0 AND ( `barcode` LIKE '%$key%' OR `date` LIKE '%$key%' OR `store_id` LIKE '%$key%' OR `quantity` LIKE '%$key%')";
				$res = mysql_query($sql,$conn);
				if (!$res) throw new Exception("Database access failed: " . mysql_error());
				$rows = mysql_num_rows($res);
				$result =  array();
				for ($j = 0 ; $j < $rows ; $j++){
					$result[$j] = array(
													"barcode" => mysql_result($res,$j,'barcode'),
													"date" => mysql_result($res,$j,'date'),
													"store_id" => mysql_result($res,$j,'store_id'),
													"quantity" => mysql_result($res,$j,'quantity')
												);		
				}
				break;
			case $ACTIVE_SHIPPED:
				$sql = "SELECT * FROM `product_shipped` WHERE  `barcode` LIKE '%$key%' OR `date` LIKE '%$key%' OR `store_id` LIKE '%$key%' OR `quantity` LIKE '%$key%'";
				$res = mysql_query($sql,$conn);
				if (!$res) throw new Exception("Database access failed: " . mysql_error());
				$rows = mysql_num_rows($res);
				$result =  array();
				for ($j = 0 ; $j < $rows ; $j++){
					$result[$j] = array(
													"barcode" => mysql_result($res,$j,'barcode'),
													"date" => mysql_result($res,$j,'date'),
													"store_id" => mysql_result($res,$j,'store_id'),
													"quantity" => mysql_result($res,$j,'quantity')
												);		
				}
				break;
			case $ACTIVE_PRICING:
				$sql = "SELECT `barcode`, `margin_multiplier`, `q_star` FROM `price_modifier` WHERE  `barcode` LIKE '%$key%' OR `margin_multiplier` LIKE '%$key%' OR `);` LIKE '%$key%'";
				$res = mysql_query($sql, $conn);
				if (!$res) throw new Exception("Database access failed: " . mysql_error());
				$rows = mysql_num_rows($res);
				$result =  array();
				for ($j = 0 ; $j < $rows ; $j++)
				{
					$result[$j] = array(
													"barcode" => mysql_result($res,$j,'barcode'),
													"margin_multiplier" => mysql_result($res,$j,'margin_multiplier'),
													"q_star" => mysql_result($res,$j,'q_star')
												);		
				}
				break;
			default:
				break;
		}
		$retArr["result"] = $result;
		$retArr["status"] = $OK;
	}
}catch(Exception $e){
	switch ($e->getMessage()){
	case $ERROR:
		$status = $e->getMessage();
		break;
	default:
		$status = $FAIL;
		break;
	}
	$retArr["status"] = $status;
	$retArr["debug"] = $e->getMessage();
	$retArr["stack"] = $e->getTraceAsString();
 }
dbclose($conn);
echo json_encode($retArr);


?>