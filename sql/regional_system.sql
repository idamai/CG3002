CREATE TABLE IF NOT EXISTS `product` (
	`barcode` BIGINT UNSIGNED PRIMARY KEY,
	`name` VARCHAR (255) NOT NULL,
	`category` VARCHAR (255) NOT NULL,
	`manufacturer` VARCHAR (255) NOT NULL,
	`cost` NUMERIC (10,2) NOT NULL,
	`minimal_stock` INTEGER NOT NULL
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `warehouse` (
	`barcode` BIGINT UNSIGNED,
	`stock` INT,
	`batchdate` DATE DEFAULT "0000-00-00",
	PRIMARY KEY (`barcode`,`batchdate`),
	FOREIGN KEY (`barcode`) REFERENCES `product`(`barcode`)	
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;


CREATE TABLE IF NOT EXISTS `local_stores` (
	`id` INT UNSIGNED,
	`name` VARCHAR(255),
	`location` CHAR(40),
	PRIMARY KEY (`id`)
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `price_modifier` (
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
	`price` NUMERIC(10,2),
	PRIMARY KEY (`barcode`,`date`,`store`),
	FOREIGN KEY (`barcode`) REFERENCES `product`(`barcode`),
	FOREIGN KEY (`store`) REFERENCES `local_stores`(`id`)
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `accounts` (
	`code` INT UNSIGNED,
	`name` VARCHAR(255) UNIQUE NOT NULL,
	PRIMARY KEY (`code`)
)   ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `balance_sheet` (
	`code` INT UNSIGNED,
	`date` DATE NOT NULL,
	`amount` NUMERIC(10,2),
	PRIMARY KEY (`code`),
	FOREIGN KEY (`code`) REFERENCES `accounts`(`code`)
)   ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;