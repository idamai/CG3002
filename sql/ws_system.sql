REATE TABLE IF NOT EXISTS `product` (
	`barcode` INT UNSIGNED PRIMARY KEY,
	`name` VARCHAR (255) NOT NULL,
	`category` VARCHAR (255) NOT NULL,
	`manufacturer` VARCHAR (255) NOT NULL,
	`cost` NUMERIC (3,2) NOT NULL	
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `daily_order` (
	`barcode` INT UNSIGNED,	
	`stock` INT,
	PRIMARY KEY (`barcode`),
	FOREIGN KEY (`barcode`) REFERENCES `product`(`barcode`)	
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `promotion` (
	`barcode` INT UNSIGNED,
	`id`	  INT,
	`type`	  INT NOT NULL,
	`value`	  INT NOT NULL,
	PRIMARY KEY (`barcode`, `id`),
	FOREIGN KEY (`barcode`) REFERENCES `product`(`barcode`)
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `transaction` (
	`id` INT,
	`barcode` INT UNSIGNED,
	`date` DATE NOT NULL,
	`promo_id` INT REFERENCES `promotion`(`id`),
	`qty` INT,
	`price` NUMERIC(3,2),
	PRIMARY KEY (`id`,`barcode`),
	FOREIGN KEY (`barcode`) REFERENCES `product`(`barcode`)
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;
