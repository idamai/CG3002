<?php session_start(); ?>
<?php
function dbconnect()
{
	$con = mysql_connect("127.0.0.1", "root", "password");
	mysql_select_db("regional", $con);
	if(!$con)
	{
		die("Connection Failed!");
	}
	else{
		return $con;
	}
}

function dbclose($con)
{
	mysql_close($con);
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Sales - Regional Server</title>
		<link href="../css/bootstrap.min.css" rel="stylesheet" media="screen">
		<link href="../css/regCustom.css" rel="stylesheet" media="screen">
		<script src="../js/jquery-1.10.2.min.js"></script>
		<script src="../js/bootstrap.min.js"></script>
	</head>
	<body>
		<div class = "container">
			<div class = "hero-unit">
				<h2>Sales - Regional Server</h2>
			</div>
			<?php
				if (isset($_SESSION['username'])) {
				$con  = dbconnect();
			?>
			<h5>Hi <?php echo $_SESSION['username']; ?> (<a href="/logout.php">Logout</a>)</h5>
			<a href="../index.php" class="btn btn-info">Home</a>
			<h3>Filter Results</h3>
			<form action="sales.php" method="get">
				<input type="text" name="store" placeholder="Enter StoreID">
				<input type="text" name="barcode" placeholder="Enter Barcode">
				<input type="hidden" name="page" value="1">
				<button type="submit" class="btn btn-primary" style="margin-top:-10px;">Filter List</button>
			</form>
			<table class="table table-hover">
				<tr class='sales-head'>
					<td id="sales-center">StoreID</td>
					<td id="sales-center">Week Of</td>
					<td id="sales-center">Barcode</td>
					<td id="sales-center">Sales</td>
					<td id="sales-center">WriteOffs</td>
				</tr>
				<?php
					$store = mysql_real_escape_string($_GET['store']);
					$barcode = mysql_real_escape_string($_GET['barcode']);
					$page_no = mysql_real_escape_string($_GET['page']);
					$next = $page_no + 1;
					$prev = $page_no - 1;
					$limit = 10;
					$count = 0;
					$offset = ($page_no - 1) * $limit;
					if ($store !=null && $barcode != null) {
						$query = mysql_query("SELECT * FROM product_sales where store_id=$store and barcode = '$barcode' order by date desc LIMIT $limit OFFSET $offset");
					} else if ($store !=null && $barcode == null) {
						$query = mysql_query("SELECT * FROM product_sales where store_id=$store order by date desc LIMIT $limit OFFSET $offset");
					} else if ($store ==null && $barcode != null) {
						$query = mysql_query("SELECT * FROM product_sales where barcode = '$barcode' order by date desc LIMIT $limit OFFSET $offset");
					} else {
						$query = mysql_query("SELECT * FROM product_sales order by date desc LIMIT $limit OFFSET $offset");
					}
					while($row = mysql_fetch_array($query))
					{
						echo("<tr><td id='sales-center'>".$row['store_id']."</td><td id='sales-center'>".$row['date']."</td><td id='sales-center'>".$row['barcode']."</td><td id='sales-center'>".$row['sales']."</td><td id='sales-center'>".$row['writeoff']."</td></tr>");
						$count++;
					}
					dbclose($con);
				?>
  			</table>
  			
  			<?php if($prev>=1){ ?>
			<a class = "btn btn-small btn-primary" href='sales.php?store=<?php echo $store; ?>&barcode=<?php echo $barcode; ?>&page=<?php echo $prev; ?>'>&#60; PREV</a>
			<?php }?> 
			<?php if($count == 10){ ?>
			<a class = "btn btn-small btn-primary" href='sales.php?store=<?php echo $store; ?>&barcode=<?php echo $barcode; ?>&page=<?php echo $next; ?>'>NEXT &#62;</a>
			<?php } ?>	
			
			<?php } else{ ?>
				<script>window.location.href = "../login.php"; </script>
			<?php	 } ?>
		</div>
	</body>
</html>