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
		$sql = "SELECT `barcode`,`name`,`category`,`manufacturer`,`cost` FROM `product`";
		$res = mysql_query($sql,$conn);
		
		if (!$res) throw new Exception("Database access failed: " . mysql_error());
		$rows = mysql_num_rows($res);
		$retArr["result"] =  array();
		for ($j = 0 ; $j < $rows ; $j++)
		{
			$retArr["result"][$j] = array(
											"barcode" => mysql_result($res,$j,'barcode'),
											"name" => mysql_result($res,$j,'name'),
											"category" => mysql_result($res,$j,'category'),
											"manufacturer" => mysql_result($res,$j,'manufacturer'),
											"cost" => mysql_result($res,$j,'cost')
											
										);
		}
		$retArr["status"] = $OK;
		break;
	case "retreive_stock":
		$retArr["barcode"]= $p["barcode"];
		$barcode = mysql_real_escape_string($p["barcode"]);
		$sql = "SELECT `batchdate`, `stock` FROM `warehouse` WHERE barcode = ".$barcode." AND `stock` > 0";
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
		$retArr["status"] = $OK;
		break;
	
	case "update_batch_stock":
		$barcode = mysql_real_escape_string($p["barcode"]);
		$quantity = $p["quantity"];
		$date =  $p["date"];
		$sql = "UPDATE `warehouse` SET `stock` = ".$quantity." WHERE `batchdate` = '".$date."' AND `barcode` = ".$barcode;
		$res = mysql_query($sql,$conn);
		if (!$res) throw new Exception("Database access failed: " . mysql_error());
		
		$sql = "SELECT `batchdate`, `stock` FROM `warehouse` WHERE barcode = ".$barcode;
		$res = mysql_query($sql,$conn);
		
		if (!$res) throw new Exception("Database access failed: " . mysql_error());
		$rows = mysql_num_rows($res);
		$availableStocks =  array();
		for ($j = 0 ; $j < $rows ; $j++)
		{
			$availableStocks[$j] = array(
											"batchdate" => mysql_result($res,$j,'batchdate'),
											"stock" => mysql_result($res,$j,'stock')
										);		
		}
		$retArr["barcode"] = $p["barcode"];
		$retArr["result"] = $availableStocks;
		$retArr["status"] = $OK;
		//-----not finished----
		break;
	case "retreive_order_list":
		$retArr["result"] =  getAllUnprocessedOrder($conn);
		$retArr["status"] = $OK;
		break;
	case "retreive_shipped_list":
		$sql = "SELECT * FROM `product_shipped` ORDER BY `date` DESC";
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
		$barcode = mysql_real_escape_string($p["barcode"]);
		$date = mysql_real_escape_string($p["batchdate"]);
		$date = date('Y-m-d',strtotime($date));		
		$quantity = mysql_real_escape_string($p["quantity"]);
		//insert new stock
		$sql = "INSERT INTO `warehouse` VALUES (".$barcode.",".$quantity.",'".$date."')";
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
		$sql = "SELECT DISTINCT `date` FROM `product_order` WHERE `processed` = 0";
		$res = mysql_query($sql, $conn);
		$rows = mysql_num_rows($res);
		$retArr["result"] =  array();
		for ($j = 0 ; $j < $rows ; $j++) {
			$retArr["result"][$j] = mysql_result($res,$j,'date');									
		}
		$retArr["status"] = $OK;
		break;
	case "populate_unprocessed_order_barcode":
		$sql = "SELECT DISTINCT `barcode` FROM `product_order` WHERE `processed` = 0";
		$res = mysql_query($sql, $conn);
		$rows = mysql_num_rows($res);
		$retArr["result"] =  array();
		for ($j = 0 ; $j < $rows ; $j++) {
			$retArr["result"][$j] = mysql_result($res,$j,'barcode');									
		}
		$retArr["status"] = $OK;
		break;
	case "process_order_date":
		$date = date('Y-m-d',strtotime($p["date"]));
		$date = mysql_real_escape_string($date);
		$sql = "SELECT `barcode`, sum(`quantity`) as `quantity` FROM `product_order` WHERE `processed` = 0 AND `date` = '".$date."' GROUP BY `barcode`";
		$res = mysql_query($sql,$conn);
		if (!$res) throw new Exception("Database access failed: " . mysql_error());
		$rows = mysql_num_rows($res);
		$toBeOrdered =  array();
		for ($j = 0 ; $j < $rows ; $j++){
			$toBeOrdered[$j] = array(
											"barcode" => mysql_result($res,$j,'barcode'),
											"quantity" => mysql_result($res,$j,'quantity')
										);		
		}
		$sql = "SELECT `barcode`, SUM(`stock`) as `stock` FROM `warehouse` WHERE `barcode` in (";
		for ($j = 0; $j < count($toBeOrdered) ; $j++){
			if ($j > 0) {
				$sql.=" , ";
			}
			$sql.=$toBeOrdered[$j]["barcode"];
		}
		$sql .= ") GROUP BY `barcode`";
		$res = mysql_query($sql,$conn);
		if (!$res) throw new Exception("Database access failed: " . mysql_error());
		$rows = mysql_num_rows($res);
		$availableBarcode = array();
		for ($j = 0 ; $j < $rows ; $j++){
			$availableBarcode[$j] = array(
											"barcode" => mysql_result($res,$j,'barcode'),
											"stock" => mysql_result($res,$j,'stock')
										);		
		}
		
		//check whether all of the stocks are sufficient
		$processableList = checkProcessableOrder($availableBarcode, $toBeOrdered,$conn);
		
		//process the sufficient stocks
		processOrder($processableList["canBeProcessed"],$date,$conn);
		
		$retArr["result"] = getAllUnprocessedOrder($conn);
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
		$sql = "SELECT `barcode`, sum(`quantity`) as `quantity` FROM `product_order` WHERE `processed` = 0 GROUP BY `barcode`";
		$res = mysql_query($sql,$conn);
		if (!$res) throw new Exception("Database access failed: " . mysql_error());
		$rows = mysql_num_rows($res);
		$toBeOrdered =  array();
		for ($j = 0 ; $j < $rows ; $j++){
			$toBeOrdered[$j] = array(
											"barcode" => mysql_result($res,$j,'barcode'),
											"quantity" => mysql_result($res,$j,'quantity')
										);		
		}
		//grab the total available stocks per barcode
		$sql = "SELECT `barcode`, SUM(`stock`) as `stock` FROM `warehouse` WHERE `barcode` in (";
		for ($j = 0; $j < count($toBeOrdered) ; $j++){
			if ($j > 0) {
				$sql.=" , ";
			}
			$sql.=$toBeOrdered[$j]["barcode"];
		}
		$sql .= ") GROUP BY `barcode`";
		$res = mysql_query($sql,$conn);
		if (!$res) throw new Exception("Database access failed: " . mysql_error());
		$rows = mysql_num_rows($res);
		$availableBarcode = array();
		for ($j = 0 ; $j < $rows ; $j++){
			$availableBarcode[$j] = array(
											"barcode" => mysql_result($res,$j,'barcode'),
											"stock" => mysql_result($res,$j,'stock')
										);		
		}
		
		//check whether all of the stocks are sufficient
		$processableList = checkProcessableOrder($availableBarcode, $toBeOrdered);
		
		//process the sufficient stocks
		processOrder($processableList["canBeProcessed"],null,$conn);
		//leave the not processed barcode
		
		$retArr["result"] = getAllUnprocessedOrder($conn);
		$retArr["notProcessed"]= $processableList["cannotBeProcessed"];
		if (count($processableList["cannotBeProcessed"])>0)
			$retArr["leftover_order"] = true;
		else
			$retArr["leftover_order"] = false;
		$retArr["status"] = $OK;
		break;
	case "process_order_barcode":
		$barcode = $p["barcode"];
		$sql = "SELECT `barcode`, sum(`quantity`) as `quantity` FROM `product_order` WHERE `processed` = 0 AND `barcode` = ".$barcode." GROUP BY `barcode`";
		$res = mysql_query($sql,$conn);
		if (!$res) throw new Exception("Database access failed: " . mysql_error());
		$rows = mysql_num_rows($res);
		$toBeOrdered =  array();
		for ($j = 0 ; $j < $rows ; $j++){
			$toBeOrdered[$j] = array(
											"barcode" => mysql_result($res,$j,'barcode'),
											"quantity" => mysql_result($res,$j,'quantity')
										);		
		}
		//grab the total available stocks per barcode
		$sql = "SELECT `barcode`, SUM(`stock`) as `stock` FROM `warehouse` WHERE `barcode` in (";
		for ($j = 0; $j < count($toBeOrdered) ; $j++){
			if ($j > 0) {
				$sql.=" , ";
			}
			$sql.=$toBeOrdered[$j]["barcode"];
		}
		$sql .= ") GROUP BY `barcode`";
		$res = mysql_query($sql,$conn);
		if (!$res) throw new Exception("Database access failed: " . mysql_error());
		$rows = mysql_num_rows($res);
		$availableBarcode = array();
		for ($j = 0 ; $j < $rows ; $j++){
			$availableBarcode[$j] = array(
											"barcode" => mysql_result($res,$j,'barcode'),
											"stock" => mysql_result($res,$j,'stock')
										);		
		}
		
		//check whether all of the stocks are sufficient
		$processableList = checkProcessableOrder($availableBarcode, $toBeOrdered);		
		//process the sufficient stocks
		processOrder($processableList["canBeProcessed"],null,$conn);
		$retArr["result"] = getAllUnprocessedOrder($conn);
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

function processBarcodeOrder($connection, $barcode, $quantity){
	$sql = "SELECT `batchdate`, `stock` FROM `warehouse` WHERE `barcode` = ".$barcode." AND STOCK > 0 ORDER BY `batchdate`";
	$res = mysql_query($sql,$connection);
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
		$sql = "UPDATE `warehouse` SET `stock` = ".$batches[$i]["stock"]." WHERE `barcode` = ".$barcode." AND `batchdate` = ".$batches[$i]["batchdate"];
		$res = mysql_query($sql,$connection);
		if (!$res) throw new Exception("Database access failed: " . mysql_error());
	}
}

function getAllUnprocessedOrder($conn) {
	$sql = "SELECT * FROM `product_order` WHERE `processed` = 0";
	$res = mysql_query($sql,$conn);
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

function processOrder($canBeProcessed,$date,$conn) {
	if (count($canBeProcessed) > 0) {
			$sql = "UPDATE `product_order` SET `processed` = 1 WHERE `barcode` IN ( ";
			$sql_shipped = "INSERT INTO `product_shipped` SELECT `barcode`, `date`, `store_id`, `quantity` FROM `product_order` WHERE `barcode` IN ( ";
			for ($j = 0; $j < count($canBeProcessed); $j++) {
				processBarcodeOrder($conn,$canBeProcessed[$j]["barcode"],$canBeProcessed[$j]["quantity"]);
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
				$sql.="  AND `date` = '".$date."'";
				$sql_shipped.="  AND `date` = '".$date."'";
			}
			
			$res = mysql_query($sql_shipped,$conn);
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			
			$res = mysql_query($sql,$conn);
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
?>