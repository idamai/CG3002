<?php

require_once("library.php");


function readJson()
{
	$con  = dbconnect();
	$query = mysql_query("SELECT id,deleted FROM local_stores");
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
				$quantity = decrypt($data['quantity']);
				mysql_query("INSERT INTO product_order(barcode, date, store_id, quantity, processed) VALUES ('$barcode', '$date', '$shop_id', '$quantity', 0)");
				echo $data['barcode']."[".decrypt($data['quantity'])."]<br>";
			}
		}
	}
	dbclose($con);
}

readJson();

?>