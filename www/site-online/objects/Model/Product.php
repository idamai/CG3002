<?php
	class Product{
		private $barcode;
		private $name;
		private $category;
		private $manufacturer;
		private $cost;
		private $minimal_stock;
		function __construct($barcode, $name, $category, $manufacturer, $cost, $minimal_stock) {
			$this->barcode = $barcode;
			$this->name = $name;
			$this->category = $category;
			$this->manufacturer = $manufacturer;
			$this->cost = $cost;
			$this->minimal_stock = $minimal_stock;
			
		}
		
		function editProductInfo($conn, $name, $category, $manufacturer, $cost, $minimal_stock) {
		}
		
		function addProductToDatabase($conn, $name, $category, $manufacturer, $cost, $minimal_stock){
		}
	}
	
?>