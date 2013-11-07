<?php session_start(); ?>
<!DOCTYPE html>
<html>
	<head>
		<title>HyperMarket - Regional Server</title>
		<link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
		<link href="css/regCustom.css" rel="stylesheet" media="screen">
		<script src="js/jquery-1.10.2.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src="js/regional_controller.js"></script>

	</head>
	<body>
		<div class="hidden" id ="loading-screen">
			<div id = "loading-text">Loading</div>
		</div>
		<div class = "container">
			<div class = "hero-unit">
				<h2>HyperMarket - Regional Server</h2>
			</div>
			<?php
				if (isset($_SESSION['username'])) {
			?>
			<h5>Hi <?php echo $_SESSION['username']; ?> (<a href="logout.php">Logout</a>)</h5>
			<div class="clearfix"></div>
			<div class = "menu left">
				<div class = "btn btn-primary btn-block" id="product-btn">Product List</div>
				<div class = "btn btn-primary btn-block" id="store-btn">Store List</div>
				<div class = "btn btn-primary btn-block" id="order-btn">Order List</div>
				<div class = "btn btn-primary btn-block" id="shipment-btn">Shipment List</div>
				<div class = "btn btn-primary btn-block" id="pricing-btn">Pricing Details</div>
				<div class = "btn btn-info btn-block" id="sales-btn" onclick="window.open('/api/sales.php?store=&date=&page=1','mywindow');" style="cursor: hand;">Sales Data</div>
				<div class = "btn btn-inverse btn-block" id="finance-btn">Financial Report</div>
			</div>
			<div class = "content right">
				<input type = "text" id = "search-bar" placeholder="Search here" ></input>
				<div class = "content-item" id= "content-container">
				Please select one of the menu on the left.
				</div>
				<div class = "hidden" id = "add-new-product-holder">
					<div class = "btn btn-primary left" id="add-new-product-btn">Add New Product</div>
					<div class = "btn btn-warning left" id="restock-all-product-btn">Restock all Products</div>
				</div>
				<div class = "hidden" id = "add-new-store-holder">
					<div class = "btn btn-primary left" id="add-new-store-btn">Add New Store</div>
				</div>
				<div class="hidden" id="process-order-btns">
					<div class = "btn btn-success left" id="import-all-btn">Import All Orders</div> 
					<div class = "btn btn-primary left" id="process-all-btn">Process All Orders</div> 
					<div class = "btn btn-primary left" id="process-date-btn">Process Date</div> 
					<div class = "btn btn-primary left" id="process-barcode-btn">Process Barcodes</div>
				</div>
			</div>
			<div class = "clearfix">
			</div>
		</div>
		<div class = "popup hidden" id = "process-date-popup">
			<h2>Select a Date to Process</h2>
			<div id = "order-date-input-selection"></div>
			<div class = "clearfix"></div>
			<div class = "btn btn-primary left" id = "process-date-cfm">Process</div>
			<div class = "btn btn-primary right" id = "process-date-cancel">Cancel</div>
			<div class = "clearfix"></div>
		</div>
		<div class = "popup hidden" id = "process-barcode-popup">
			<h2>Select a Barcode to Process</h2>
			<div id = "order-barcode-input-selection"></div>
			<div class = "clearfix"></div>
			<div class = "btn btn-primary left" id = "process-barcode-cfm">Process</div>
			<div class = "btn btn-primary right" id = "process-barcode-cancel">Cancel</div>
			<div class = "clearfix"></div>
		</div>
		<div class= "popup hidden" id ="view-stock-popup">
			<div id ="stock-list-container">
				
			</div>
			<div class = "clearfix"></div>
			<div id ="add-stock-btn" class ="btn btn-success left">Add Stock</div>
			<!--<div id ="discard-stock-btn" class = "btn btn-danger right">Discard Stock </div>-->
			<div class = "clearfix"></div>
			<div id ="close-stock-popup" class = "middle btn btn-primary">Close</div>
		</div>
		<div class= "popup hidden" id ="add-stock-popup">
			<h2 class = "stock-item-barcode"></h2>
			<form id = "add-stock-form">
				<table>
					<tr>
						<td>Quantity:</td>
						<td><input id = "stock-quantity" type="number" name="quantity"></td>
					</tr>
					<tr>
						<td>Batch Date:</td>
						<td><input id = "stock-batchdate" type="date" name="batchdate"></td>
					</tr>
				</table>
			</form>
			<div class = "clearfix"></div>
			<div id ="add-stock-cfm" class ="btn btn-success left">Add Stock</div>
			<div id ="add-stock-cncl" class = "btn btn-danger right">Cancel</div>
			<div class = "clearfix"></div>
		</div>
		<div class= "popup hidden" id ="discard-stock-popup">
			<h2 class = "stock-item-barcode"></h2>
			<h2 class = "stock-item-batchdate"></h2>
			<form id = "discard-stock-form">
				<table>
					<tr>
						<td>Quantity:</td>
						<td><input id = "discard-quantity" type="number" name="quantity"></td>
					</tr>
				</table>
			</form>
			<div class = "clearfix"></div>
			<div id ="discard-stock-cfm" class ="btn btn-danger left">Discard Stock</div>
			<div id ="discard-stock-cncl" class = "btn btn-primary right">Cancel</div>
			<div class = "clearfix"></div>
		</div>
		<div class = "popup hidden" id = "add-edit-product">
			<h2>Please input your new product info</h2>
			<table>
				<tr>
					<td>Barcode</td>
					<td><input type = "number" id = "new-product-barcode"></input></td>
				</tr>
				<tr>
					<td>Name</td>
					<td><input type = "text" id = "new-product-name"></input></td>
				</tr>
				<tr>
					<td>Category</td>
					<td><input type = "text" id = "new-product-category"></input></td>
				</tr>
				<tr>
					<td>Manufacturer</td>
					<td><input type = "text" id = "new-product-manufacturer"></input></td>
				</tr>
				<tr>
					<td>Cost</td>
					<td><input type = "number" id = "new-product-cost"></input></td>
				</tr>
				<tr>
					<td>Minimal Stock</td>
					<td><input type = "number" id = "new-product-minstock"></input></td>
				</tr>
			</table>
			<div class = "btn btn-success left" id = "add-edit-product-cfm">Add Product</div>
			<div class = "btn btn-danger right" id = "add-product-cncl">Cancel</div>
		</div>
		<div class = "popup hidden" id = "add-edit-store">
			<h2>Please input your new store info</h2>
			<table>
				<tr>
					<td>Store ID</td>
					<td><input type = "number" id = "new-store-id"></input></td>
				</tr>
				<tr>
					<td>Name</td>
					<td><input type = "text" id = "new-store-name"></input></td>
				</tr>
				<tr>
					<td>Location</td>
					<td><input type = "text" id = "new-store-location"></input><div class = "hidden edit-store-msg">Leave blank to keep your old password<div/></td>
				</tr>
				<tr>
					<td>Password</td>
					<td><input type = "password" id = "new-store-password"></input></td>
				</tr>
				<tr>
					<td>Retype Password</td>
					<td><input type = "password" id = "new-store-confirmPass"></input></td>
				</tr>
			</table>
			<div class = "btn btn-success left" id = "add-edit-store-cfm">Add Store</div>
			<div class = "btn btn-danger right" id = "add-store-cncl">Cancel</div>
		</div>
		<?php } else{ ?>
		<script>window.location.href = "login.php"; </script>
		<?php	 } ?>

		<div id = "footer" class = "clearfix">
		</div>
	</body>
</html>