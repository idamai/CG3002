
INSERT INTO `warehouse` (`barcode`,`stock`) VALUES
(59030623,3325),
(26398554,1770),
(38545539,2200),
(23418003,2960);

INSERT INTO `local_stores` VALUES
(1,"Bedok 1101", "Bedok Street 1920", MD5("helloworld"),0),
(2,"Changi Expo", "Changi Lane 5", MD5("helloworld"),0);

INSERT INTO `admin` VALUES ("admin",MD5("iamsexy#1"));

INSERT INTO `product_order` VALUES
(23418003,"2013-09-30",1,100,1),
(23418003,"2013-09-30",2,400,1),
(26398554,"2013-09-30",1,600,1),
(59030623,"2013-09-30",2,1000,1),
(23418003,"2013-10-01",1,100,0),
(38545539,"2013-10-01",2,400,0),
(26398554,"2013-10-01",1,600,0),
(59030623,"2013-10-01",2,1000,0);

INSERT INTO `product_shipped` VALUES
(23418003,"2013-09-30",1,100),
(23418003,"2013-09-30",2,400),
(26398554,"2013-09-30",1,600),
(59030623,"2013-09-30",2,1000);

INSERT INTO `price_modifier`
	SELECT `barcode`,1.25,7,`minimal_stock`*4,1.00,2.00,2013-10-01 FROM `product`;