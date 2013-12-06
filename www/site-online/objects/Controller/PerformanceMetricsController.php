<?php

	class PerformanceMetricsController {
		private $connection;
		
		function __construct($conn){
			$this->connection = $conn;
		}
		
		function retreiveProductSalesMetrics($barcode) {
			$sql = "SELECT
						YEAR(`date`) as `year`, MONTH(`date`) as `month` , SUM(`sales`) as `sales`
					FROM
						`product_sales`
					WHERE 
						YEAR(`date`) >= YEAR(CURRENT_DATE - INTERVAL 12 MONTH) AND MONTH(`date`) >= MONTH(CURRENT_DATE - INTERVAL 12 MONTH) AND `barcode` = ".$barcode."
					GROUP BY
						YEAR(`date`), MONTH(`date`)";
			$res = mysql_query($sql,$this->connection);
			if (!$res) throw new Exception("Database access failed: " . mysql_error());			
			$rows = mysql_num_rows($res);
			for ($i = 0; $i < $rows; $i++){				
				$performance[$i]= array(		"year" => mysql_result($res,$i,'year'),
												"month" => mysql_result($res,$i,'month'),
												"sales" => mysql_result($res,$i,'sales')
											);
			}
			return $performance;			
		}
		
	}

?>