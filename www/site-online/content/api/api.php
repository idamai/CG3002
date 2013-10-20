<?php
require_once ("../../objects/settings.php");
require_once("../../objects/login.php");


$OK = "ok";
$FAIL = "fail";
$ERROR = "error";
//preparation for further implementation
$LOGIN = "login";

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
	case "retreive_product":
		require_once("../../objects/Controller/ProductListController.php");
		$plc = new ProductListController($conn);
		$retArr["result"] = $plc->retrieveProductList();
		$retArr["status"] = $OK;
		break;
	case "retreive_stock":
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
	case "retreive_order_list":
		require_once("../../objects/Controller/OrderController.php");
		$oc = new OrderController($conn);
		$retArr["result"] =  $oc->getAllUnprocessedOrder($conn);
		$retArr["status"] = $OK;
		break;
	case "retreive_shipped_list":
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
		$oc->processOrder($processableList["canBeProcessed"],null,$conn);
		//leave the not processed barcode
		
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
		
		$retArr["result"] = $oc->getAllUnprocessedOrder();
		$retArr["notProcessed"]= $processableList["cannotBeProcessed"];
		
		if (count($processableList["cannotBeProcessed"])>0)
			$retArr["leftover_order"] = true;
		else
			$retArr["leftover_order"] = false;
		$retArr["status"] = $OK;
		break;
	case "retreive_store":
		$sql = "SELECT * FROM `local_stores`";
		$res = mysql_query($sql,$conn);
		
		if (!$res) throw new Exception("Database access failed: " . mysql_error());
		$rows = mysql_num_rows($res);
		$retArr["result"] =  array();
		for ($j = 0 ; $j < $rows ; $j++)
		{
			$retArr["result"][$j] = array(
											"store_id" => mysql_result($res,$j,'id'),
											"store_name" => mysql_result($res,$j,'name'),
											"store_loc" => mysql_result($res,$j,'location')
										);		
		}
		$retArr["status"] = $OK;
		break;
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