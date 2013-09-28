CREATE TABLE IF NOT EXISTS `product` (
	`barcode` BIGINT UNSIGNED PRIMARY KEY,
	`name` VARCHAR (255) NOT NULL,
	`category` VARCHAR (255) NOT NULL,
	`manufacturer` VARCHAR (255) NOT NULL,
	`cost` NUMERIC (10,2) NOT NULL	
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `batch` (
	`barcode` BIGINT UNSIGNED,
	`batch` DATE,
	`stock` INT,
	`expiry` DATE,
	PRIMARY KEY (`barcode`, `batch`),
	FOREIGN KEY (`barcode`) REFERENCES `product`(`barcode`)	
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `promotion` (
	`barcode` BIGINT UNSIGNED,
	`id`	  INT,
	`type`	  INT NOT NULL,
	`value`	  INT NOT NULL,
	PRIMARY KEY (`barcode`, `id`),
	FOREIGN KEY (`barcode`) REFERENCES `product`(`barcode`)
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `transaction` (
	`id` INT,
	`barcode` BIGINT UNSIGNED,
	`date` DATE NOT NULL,
	`promo_id` INT REFERENCES `promotion`(`id`),
	`qty` INT,
	`price` NUMERIC(3,2),
	PRIMARY KEY (`id`,`barcode`),
	FOREIGN KEY (`barcode`) REFERENCES `product`(`barcode`)
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `cashier` (
	`id` INT,
	`name` VARCHAR(255),
	`pwd` CHAR(40),
	PRIMARY KEY (`id`)
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `transactor`(
	`transaction_id` INT,
	`cashier_id` INT NOT NULL,
	PRIMARY KEY (`transaction_id`),
	FOREIGN KEY (`transaction_id`) REFERENCES `transaction`(`id`),
	FOREIGN KEY (`cashier_id`) REFERENCES `cashier`(`id`)
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;