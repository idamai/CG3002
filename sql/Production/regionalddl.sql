-- phpMyAdmin SQL Dump
-- version 4.0.9
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Dec 08, 2013 at 06:38 PM
-- Server version: 5.6.14
-- PHP Version: 5.5.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `regional`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE IF NOT EXISTS `accounts` (
  `code` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `debit_credit` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`code`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE IF NOT EXISTS `admin` (
  `username` varchar(255) NOT NULL DEFAULT '',
  `password` char(40) DEFAULT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `balance_sheet`
--

CREATE TABLE IF NOT EXISTS `balance_sheet` (
  `date` date NOT NULL DEFAULT '0000-00-00',
  `account` int(10) unsigned NOT NULL DEFAULT '0',
  `store_id` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`date`,`account`,`store_id`),
  KEY `account` (`account`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `local_stores`
--

CREATE TABLE IF NOT EXISTS `local_stores` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) DEFAULT NULL,
  `location` char(40) DEFAULT NULL,
  `password` char(32) DEFAULT NULL,
  `deleted` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `price_modifier`
--

CREATE TABLE IF NOT EXISTS `price_modifier` (
  `barcode` bigint(20) unsigned NOT NULL DEFAULT '0',
  `margin_multiplier` decimal(10,2) NOT NULL,
  `tax` int(11) NOT NULL,
  `q_star` int(11) NOT NULL,
  `min_multiplier` decimal(10,2) NOT NULL DEFAULT '1.00',
  `max_multiplier` decimal(10,2) NOT NULL DEFAULT '2.00',
  `update_date` date NOT NULL DEFAULT '1970-01-01',
  PRIMARY KEY (`barcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE IF NOT EXISTS `product` (
  `barcode` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `manufacturer` varchar(255) NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `minimal_stock` int(11) NOT NULL,
  `deleted` int(11) DEFAULT '0',
  PRIMARY KEY (`barcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `product_order`
--

CREATE TABLE IF NOT EXISTS `product_order` (
  `barcode` bigint(20) unsigned NOT NULL DEFAULT '0',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `store_id` int(10) unsigned NOT NULL DEFAULT '0',
  `quantity` int(11) DEFAULT NULL,
  `processed` bit(1) DEFAULT b'0',
  PRIMARY KEY (`barcode`,`date`,`store_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `product_sales`
--

CREATE TABLE IF NOT EXISTS `product_sales` (
  `barcode` bigint(20) unsigned NOT NULL DEFAULT '0',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `store_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sales` int(11) DEFAULT NULL,
  `writeoff` int(11) DEFAULT NULL,
  PRIMARY KEY (`barcode`,`date`,`store_id`),
  KEY `store_id` (`store_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `product_shipped`
--

CREATE TABLE IF NOT EXISTS `product_shipped` (
  `barcode` bigint(20) unsigned NOT NULL DEFAULT '0',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `store_id` int(10) unsigned NOT NULL DEFAULT '0',
  `quantity` int(11) DEFAULT NULL,
  `processed` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`barcode`,`date`,`store_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `warehouse`
--

CREATE TABLE IF NOT EXISTS `warehouse` (
  `barcode` bigint(20) unsigned NOT NULL DEFAULT '0',
  `batchdate` date NOT NULL DEFAULT '0000-00-00',
  `stock` int(11) DEFAULT NULL,
  PRIMARY KEY (`barcode`,`batchdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `webstore_order`
--

CREATE TABLE IF NOT EXISTS `webstore_order` (
  `barcode` bigint(20) unsigned NOT NULL DEFAULT '0',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `quantity` int(11) DEFAULT NULL,
  `processed` bit(1) DEFAULT b'0',
  PRIMARY KEY (`barcode`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `balance_sheet`
--
ALTER TABLE `balance_sheet`
  ADD CONSTRAINT `balance_sheet_ibfk_1` FOREIGN KEY (`account`) REFERENCES `accounts` (`code`),
  ADD CONSTRAINT `balance_sheet_ibfk_2` FOREIGN KEY (`store_id`) REFERENCES `local_stores` (`id`);

--
-- Constraints for table `price_modifier`
--
ALTER TABLE `price_modifier`
  ADD CONSTRAINT `price_modifier_ibfk_1` FOREIGN KEY (`barcode`) REFERENCES `product` (`barcode`);

--
-- Constraints for table `product_order`
--
ALTER TABLE `product_order`
  ADD CONSTRAINT `product_order_ibfk_1` FOREIGN KEY (`barcode`) REFERENCES `product` (`barcode`),
  ADD CONSTRAINT `product_order_ibfk_2` FOREIGN KEY (`store_id`) REFERENCES `local_stores` (`id`);

--
-- Constraints for table `product_shipped`
--
ALTER TABLE `product_shipped`
  ADD CONSTRAINT `product_shipped_ibfk_1` FOREIGN KEY (`barcode`) REFERENCES `product` (`barcode`),
  ADD CONSTRAINT `product_shipped_ibfk_2` FOREIGN KEY (`store_id`) REFERENCES `local_stores` (`id`);

--
-- Constraints for table `warehouse`
--
ALTER TABLE `warehouse`
  ADD CONSTRAINT `warehouse_ibfk_1` FOREIGN KEY (`barcode`) REFERENCES `product` (`barcode`);

--
-- Constraints for table `webstore_order`
--
ALTER TABLE `webstore_order`
  ADD CONSTRAINT `webstore_order_ibfk_1` FOREIGN KEY (`barcode`) REFERENCES `product` (`barcode`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
