<?php
require_once("../../objects/login.php");

$OK = "ok";
$FAIL = "fail";
$ERROR = "error";
//preparation for further implementation
$LOGIN = "login";

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
		$res = mysql_query($sql);
		
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
		$sql = "SELECT `batchdate`, `stock` FROM `warehouse` WHERE barcode = ".$barcode;
		$res = mysql_query($sql);
		
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
	//halfbaked stock reducing system
	case "reduce_stock":
		$barcode = mysql_real_escape_string($p["barcode"]);
		$quantity = $p["quantity"];
		$sql = "SELECT `batchdate`, `stock` FROM `warehouse` WHERE barcode = ".$barcode;
		$res = mysql_query($sql);
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
		//-----not finished----
		break;
	case "retreive_order_list":
		$sql = "SELECT * FROM `product_order`";
		$res = mysql_query($sql);
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
	case "retreive_shipped_list":
		$sql = "SELECT * FROM `product_shipped`";
		$res = mysql_query($sql);
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
		$res = mysql_query($sql);
		if (!$res) throw new Exception("Database access failed: " . mysql_error());
		//view stock
		$sql = "SELECT `batchdate`, `stock` FROM `warehouse` WHERE barcode = ".$barcode;
		$res = mysql_query($sql);
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
	case "record_stock":
		break;
	case "record_shipped":
		break;
	case "retreive_store":
		$sql = "SELECT * FROM `local_stores`";
		$res = mysql_query($sql);
		
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
echo json_encode($retArr);

?>