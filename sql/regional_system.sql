CREATE TABLE IF NOT EXISTS `product` (
	`barcode` BIGINT UNSIGNED PRIMARY KEY,
	`name` VARCHAR (255) NOT NULL,
	`category` VARCHAR (255) NOT NULL,
	`manufacturer` VARCHAR (255) NOT NULL,
	`cost` NUMERIC (10,2) NOT NULL	
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `warehouse` (
	`barcode` BIGINT UNSIGNED,
	`stock` INT,
	PRIMARY KEY (`barcode`),
	FOREIGN KEY (`barcode`) REFERENCES `product`(`barcode`)	
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;


CREATE TABLE IF NOT EXISTS `local_stores` (
	`id` INT UNSIGNED,
	`name` VARCHAR(255),
	`location` CHAR(40),
	PRIMARY KEY (`id`)
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `price` (
	`barcode` 			BIGINT UNSIGNED,
	`margin_multiplier`	INT NOT NULL,
	`tax`	  			INT NOT NULL,
	PRIMARY KEY (`barcode`),
	FOREIGN KEY (`barcode`) REFERENCES `product`(`barcode`)
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `product_order` (
	`barcode` BIGINT UNSIGNED,
	`date` DATE,
	`store` INT UNSIGNED,
	`qty` INT,
	`price` NUMERIC(3,2),
	PRIMARY KEY (`barcode`,`date`,`store`),
	FOREIGN KEY (`barcode`) REFERENCES `product`(`barcode`),
	FOREIGN KEY (`store`) REFERENCES `local_stores`(`id`)
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;
