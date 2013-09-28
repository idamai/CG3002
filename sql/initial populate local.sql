INSERT INTO `product` (`name`,`category`,`manufacturer`,`barcode`,`cost`) VALUES
("BHC Golf Visor With Magnetic Marker","Stop Smoking","Kit E Kat",59030623,26.85),
("CORPORATE COLOUR FIESTA FRUITS IN 6CM CANISTER","Team Sports","Chux",26398554,91.40),
("Brushed Heavy Cotton Visor With Sandwich","Moisturiser","Burgen",38545539,65.30),
("Carpenter Pencil","Home Improvement","Old El Paso",23418003,22.75);

INSERT INTO `batch` (`batch`,`barcode`,`stock`) VALUES
(59030623,3325),
(26398554,1770),
(38545539,2200),
(23418003,2960);

INSERT INTO `local_stores` VALUES
(1,"Bedok 1101", "Bedok Street 1920"),
(2,"Changi Expo", "Changi Lane 5");