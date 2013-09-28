/* 1. Insert new batch ----------------------------------------------*/
INSERT INTO `warehouse` VALUES ((),(),());
/* 2. Update old batch ----------------------------------------------*/
UPDATE `warehouse` SET `stock` = 3350 WHERE batchdate = "0000-00-00" AND barcode = 59030623;
/* 3. Check a product total stock /retreive_stock ---------------------------------------*/
SELECT `batchdate`, `stock` FROM `warehouse` WHERE barcode = 59030623;
/* 4. update product properties batch ----------------------------------------------*/
UPDATE `product` SET `name` = "" WHERE `barcode` = 59030623;
/* 5. Insert new batch / receive_stock ----------------------------------------------*/
INSERT INTO `warehouse` VALUES (`barcode`,`stock`,`batchdate`);
e.g. INSERT INTO `warehouse` VALUES (26398554,1000,"2013-12-11"),(59030623,1000,"2013-12-11");
/* 6. record order from all local stores "record_order" ----------------------------*/
INSERT INTO `product_order` VALUES (`barcode`,`date`,`store_id`,`units`);

/* 7. record sent stock / record_shipped ---------------------------------------------*/
INSERT INTO `product_shipped` VALUES (`barcode`,`date`,`store_id`,`units`);



