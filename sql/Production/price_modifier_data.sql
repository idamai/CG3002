INSERT INTO `price_modifier`
	SELECT `barcode`,1.25,7,`minimal_stock`*5,1.00,2.00,'2013-11-10' FROM `product`;