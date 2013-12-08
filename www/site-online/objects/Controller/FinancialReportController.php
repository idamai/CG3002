<?php
class FinancialReportController {
	private $connection;
	
	public function __construct($conn) {
		$this->connection = $conn;
	}
	
	public function generateReport() {
		$sql = "SELECT `acc`.`name` as `account`,  SUM(`bs`.`amount`) as `amount`, `acc`.`debit_credit` as `debit_credit`
				FROM `balance_sheet` `bs`
				INNER JOIN `accounts` `acc` ON `acc`.`code` = `bs`.`account`
				WHERE YEAR(`bs`.`date`) =  YEAR(CURDATE())
				GROUP BY `acc`.`name`, YEAR(`bs`.`date`)
				ORDER BY YEAR(`bs`.`date`) DESC";
		$res = mysql_query($sql, $this->connection);
		if (!$res) throw new Exception("Database access failed: " . mysql_error());
		$financialReport =  array();
		$rows = mysql_num_rows($res);
		for ($j = 0 ; $j < $rows ; $j++)
		{
			$debit_credit = mysql_result($res,$j,'debit_credit');
			if ($debit_credit == 0){
				$financialReport[$j] = array(
											"account" =>mysql_result($res,$j,'account'),
											"debit" =>mysql_result($res,$j,'amount'),
											"credit" => 0											
									);
			} else {
				$financialReport[$j] = array(
											"account" =>mysql_result($res,$j,'account'),
											"debit" => 0,
											"credit" =>mysql_result($res,$j,'amount')
									);
			}
		}
		return $financialReport;
	}
	
	
	
}
?>