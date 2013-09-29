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
		
		if (!$res) die ("Database access failed: " . mysql_error());
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
		$barcode = mysql_real_escape_string($p["barcode"]);
		$sql = "SELECT `batchdate`, `stock` FROM `warehouse` WHERE barcode = ".$barcode;
		$res = mysql_query($sql);
		
		if (!$res) die ("Database access failed: " . mysql_error());
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
	case "update_stock":
		break;
	case "retrieve_order":
		break;
	case "ship_order":
		break;
	case "receive_stock":
		break;
	case "record_stock":
		break;
	case "record_shipped":
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