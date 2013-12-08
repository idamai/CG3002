CREATE TABLE IF NOT EXISTS `product` (
	`barcode` BIGINT UNSIGNED PRIMARY KEY,
	`name` VARCHAR (255) NOT NULL,
	`category` VARCHAR (255) NOT NULL,
	`manufacturer` VARCHAR (255) NOT NULL,
	`cost` NUMERIC (10,2) NOT NULL,
	`minimal_stock` INTEGER NOT NULL,
	`deleted` BIT DEFAULT 0
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `warehouse` (
	`barcode` BIGINT UNSIGNED,
	`batchdate` DATE DEFAULT "0000-00-00",
	`stock` INT,	
	PRIMARY KEY (`barcode`,`batchdate`),
	FOREIGN KEY (`barcode`) REFERENCES `product`(`barcode`)	
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;


CREATE TABLE IF NOT EXISTS `local_stores` (
	`id` INT UNSIGNED,
	`name` VARCHAR(255),
	`location` CHAR(40),
	`password` CHAR(32),
	`deleted` BIT NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `price_modifier` (
	`barcode` 			BIGINT UNSIGNED,
	`margin_multiplier`	DECIMAL(10,2) NOT NULL,
	`tax`	  			INT NOT NULL,
	`q_star`			INT NOT NULL,
	`min_multiplier`	DECIMAL(10,2) NOT NULL DEFAULT 1.00,
	`max_multiplier`	DECIMAL(10,2) NOT NULL DEFAULT 2.00,
	`update_date`		DATE NOT NULL DEFAULT '1970-01-01',
	PRIMARY KEY (`barcode`),
	FOREIGN KEY (`barcode`) REFERENCES `product`(`barcode`)
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `product_order` (
	`barcode` BIGINT UNSIGNED,
	`date` DATE,
	`store_id` INT UNSIGNED,
	`quantity` INT,
	`processed` BIT DEFAULT 0,
	PRIMARY KEY (`barcode`,`date`,`store_id`),
	FOREIGN KEY (`barcode`) REFERENCES `product`(`barcode`),
	FOREIGN KEY (`store_id`) REFERENCES `local_stores`(`id`)
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `product_shipped` (
	`barcode` BIGINT UNSIGNED,
	`date` DATE,
	`store_id` INT UNSIGNED,
	`quantity` INT,
	`proceessed` INT,
	PRIMARY KEY (`barcode`,`date`,`store_id`),
	FOREIGN KEY (`barcode`) REFERENCES `product`(`barcode`),
	FOREIGN KEY (`store_id`) REFERENCES `local_stores`(`id`)
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `accounts` (
	`code` INT UNSIGNED,
	`name` VARCHAR(255) UNIQUE NOT NULL,
	PRIMARY KEY (`code`)
)   ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;

create table if not exists `balance_sheet` (
	`date` DATE,
	`account` INT(10) UNSIGNED,
	`store_id` INT(10) UNSIGNED,
	`amount` DECIMAL(10,2) NOT NULL,
	PRIMARY KEY(`date`,`account`,`store_id`),
	FOREIGN KEY (`account`) REFERENCES `accounts`(`code`),
	FOREIGN KEY (`store_id`) REFERENCES `local_stores`(`id`)
) ENGINE  = InnoDB CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `admin` (
	`username` VARCHAR(255),
	`password` CHAR(40),
	PRIMARY KEY (`username`)
)  ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;