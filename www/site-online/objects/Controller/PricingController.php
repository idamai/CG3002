<?php
	class PricingController {
		private $connection;
		
		function __construct($conn) {
			$this->connection = $conn;
		}
		
		function retrieveTotalPricing(){
			$sql = "SELECT COUNT(*) AS `total` FROM `price_modifier`";
			$res = mysql_query($sql, $this->connection);
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			$totalItems =  mysql_result($res,0,'total');
			return $totalItems;
		}
     
		function retrievePricingList($offset) {      
			$sql = "SELECT `barcode`, `margin_multiplier`, `q_star` FROM `price_modifier` LIMIT 70 OFFSET ".$offset;
			$res = mysql_query($sql, $this->connection);
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			$rows = mysql_num_rows($res);
			$pricingList =  array();
			for ($j = 0 ; $j < $rows ; $j++)
			{
				$pricingList[$j] = array(
												"barcode" => mysql_result($res,$j,'barcode'),
												"margin_multiplier" => mysql_result($res,$j,'margin_multiplier'),
												"q_star" => mysql_result($res,$j,'q_star')
											);		
			}
			return $pricingList;
		}
		
		function updatePricing($availableStocks) {
			$sql = "SELECT `barcode`, `margin_multiplier`, `min_multiplier`, `max_multiplier`, `q_star` ,`update_date` FROM `price_modifier`";
			$res = mysql_query($sql, $this->connection);
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
			$rows = mysql_num_rows($res);
			$pricingList =  array();
			for ($j = 0 ; $j < $rows ; $j++)
			{
				$pricingList[mysql_result($res,$j,'barcode')] = array(
													"margin_multiplier" => mysql_result($res,$j,'margin_multiplier'),
													"min_multiplier" => mysql_result($res,$j,'min_multiplier'),
													"max_multiplier" => mysql_result($res,$j,'max_multiplier'),
													"q_star" => mysql_result($res,$j,'q_star'),
													"update_date" => mysql_result($res,$j,'update_date')
												);
			}
			$sql = 'SELECT `barcode`, `minimal_stock` FROM `product`';
			$res = mysql_query($sql, $this->connection);
			if (!$res) throw new Exception("Database access failed: " . mysql_error());			
			$rows = mysql_num_rows($res);
			$minimalStock =  array();
			for ($j = 0 ; $j < $rows ; $j++)
			{
				$minimalStock[mysql_result($res,$j,'barcode')] = mysql_result($res,$j,'minimal_stock');
			}
			for ($i = 0; $i< count($availableStocks); $i++) {
				$barcode = $availableStocks[$i]["barcode"];
				$current_stock = $availableStocks[$i]["stock"];
				$margin_multiplier = $pricingList[$barcode]['margin_multiplier'];
				$min_multiplier = $pricingList[$barcode]['min_multiplier'];
				$max_multiplier = $pricingList[$barcode]['max_multiplier'];
				$q_star = $pricingList[$barcode]['q_star'];
				$update_date = $pricingList[$barcode]['update_date'];
				$new_multiplier = $this->pricingCalculator($barcode, $current_stock, $q_star, $update_date, $margin_multiplier, $min_multiplier, $max_multiplier,$minimalStock[$barcode]);
				if ($margin_multiplier != round($new_multiplier,2)) {
					$this->insertNewPricing($barcode, $new_multiplier);
				}
			}
		}
		
		private function pricingCalculator($barcode, $current_stock, $q_star, $update_date, $margin_multiplier, $min_multiplier, $max_multiplier, $minimal_stock) {
			$newMultiplier = 0;
			$datefrom = $update_date;
			$dateto = date('Y-m-d');
			$datefrom = strtotime($datefrom, 0);
			$dateto = strtotime($dateto, 0);    
			$difference = $dateto - $datefrom;
			if ($difference < 86400.00)
				$difference = 86400.00;
			$numOfWeeksPassed = $difference / 604800.000;
			$currentGradient = ($q_star - $current_stock)/$numOfWeeksPassed/1.000;
			$recommendedGradient = ($q_star - $minimal_stock)/4.000;
			$delta = ($recommendedGradient-$currentGradient)/$recommendedGradient;
			//demo purpose simulation
			$delta = $delta / 5.00;
			//changed to negative delta
			$newMultiplier = $margin_multiplier - $delta;
			if ($newMultiplier > $max_multiplier)
				return $max_multiplier;
			else if ($newMultiplier < $min_multiplier)
				return $min_multiplier;
			else
				return $newMultiplier;
		}
		
		private function insertNewPricing($barcode, $new_multiplier) {
			$sql = 'UPDATE `price_modifier` SET `margin_multiplier` = '.$new_multiplier.' WHERE `barcode` = '.$barcode;
			$res = mysql_query($sql, $this->connection);
			if (!$res) throw new Exception("Database access failed: " . mysql_error());
		}
	}
?>