# ************************************************************
# Sequel Pro SQL dump
# Version 4004
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.5.24-0ubuntu0.12.04.1)
# Database: vehikel
# Generation Time: 2013-03-09 03:47:58 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table contact_seller
# ------------------------------------------------------------

CREATE TABLE `contact_seller` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table credentials
# ------------------------------------------------------------

CREATE TABLE `credentials` (
  `uid` bigint(20) unsigned NOT NULL,
  `credential` char(200) NOT NULL DEFAULT '',
  UNIQUE KEY `user` (`uid`),
  CONSTRAINT `credentials_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table list_of_makes
# ------------------------------------------------------------

CREATE TABLE `list_of_makes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `make` varchar(30) NOT NULL DEFAULT '',
  `car` tinyint(1) NOT NULL,
  `boat` tinyint(1) NOT NULL,
  `motorcycle` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `make` (`make`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table list_of_models
# ------------------------------------------------------------

CREATE TABLE `list_of_models` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `make` varchar(30) NOT NULL DEFAULT '',
  `model` varchar(30) DEFAULT NULL,
  `type` set('car','motorcycle','boat') NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `make` (`make`,`model`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table people
# ------------------------------------------------------------

CREATE TABLE `people` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` char(15) NOT NULL,
  `email` char(60) DEFAULT '',
  `membership` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `name` char(50) NOT NULL,
  `avatar_info` varchar(600) NOT NULL DEFAULT '',
  `private_email` tinyint(1) NOT NULL DEFAULT '1',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `address` varchar(600) DEFAULT NULL,
  `account_type` set('retail','private') NOT NULL DEFAULT 'private',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table people_history
# ------------------------------------------------------------

CREATE TABLE `people_history` (
  `history_id` char(36) NOT NULL DEFAULT '',
  `id` bigint(20) unsigned NOT NULL,
  `username` char(15) NOT NULL,
  `email` char(60) DEFAULT '',
  `membership` timestamp NULL DEFAULT NULL,
  `name` char(50) NOT NULL,
  `avatar_info` varchar(600) NOT NULL DEFAULT '',
  `private_email` tinyint(1) NOT NULL DEFAULT '1',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `address` varchar(600) DEFAULT NULL,
  `change_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `account_type` set('retail','private') NOT NULL DEFAULT 'private',
  PRIMARY KEY (`history_id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table posts
# ------------------------------------------------------------

CREATE TABLE `posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `universal_id` varchar(20) NOT NULL DEFAULT '',
  `uid` bigint(20) unsigned NOT NULL,
  `creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `name` varchar(30) NOT NULL DEFAULT '',
  `type` set('car','motorcycle','boat') NOT NULL DEFAULT '',
  `make` char(30) NOT NULL DEFAULT '',
  `model` char(30) NOT NULL DEFAULT '',
  `price` bigint(11) DEFAULT NULL,
  `model_year` int(11) DEFAULT NULL,
  `engine` char(3) NOT NULL DEFAULT '',
  `transmission` set('automatic','manual','other') NOT NULL DEFAULT '',
  `fuel` set('gasoline','ethanol','diesel','flex','other') NOT NULL DEFAULT '',
  `km` int(11) DEFAULT NULL,
  `armor` tinyint(1) NOT NULL,
  `handicapped` tinyint(1) NOT NULL,
  `pictures` varchar(800) NOT NULL DEFAULT '',
  `equipment` varchar(600) NOT NULL DEFAULT '',
  `status` set('staging','active','end') NOT NULL DEFAULT 'staging',
  `traction` set('front','rear','4x4') NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `description_html_escaped` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `universal_id` (`universal_id`),
  KEY `uid` (`uid`),
  KEY `type` (`type`),
  KEY `price` (`price`),
  KEY `model_year` (`model_year`),
  KEY `transmission` (`transmission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table posts_history
# ------------------------------------------------------------

CREATE TABLE `posts_history` (
  `history_id` char(36) NOT NULL DEFAULT '',
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `universal_id` varchar(20) NOT NULL DEFAULT '',
  `uid` bigint(20) unsigned NOT NULL,
  `creation` timestamp NULL DEFAULT NULL,
  `name` varchar(30) NOT NULL DEFAULT '',
  `type` set('car','motorcycle') NOT NULL DEFAULT '',
  `make` char(30) DEFAULT NULL,
  `model` char(30) DEFAULT NULL,
  `price` bigint(11) DEFAULT NULL,
  `model_year` int(11) DEFAULT NULL,
  `engine` char(20) DEFAULT NULL,
  `transmission` set('automatic','manual','other') DEFAULT NULL,
  `fuel` set('gasoline','ethanol','diesel','flex','other') DEFAULT NULL,
  `km` int(11) DEFAULT NULL,
  `armor` tinyint(1) DEFAULT NULL,
  `handicapped` tinyint(1) DEFAULT NULL,
  `pictures` varchar(600) DEFAULT NULL,
  `equipment` varchar(600) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `traction` set('front','rear','4x4') DEFAULT '',
  `description` text NOT NULL,
  `description_html_escaped` text NOT NULL,
  `change_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`history_id`),
  KEY `uid` (`uid`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table user_sessions_lookup
# ------------------------------------------------------------

CREATE TABLE `user_sessions_lookup` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `session` char(40) NOT NULL DEFAULT '',
  `uid` bigint(20) unsigned NOT NULL,
  `status` enum('open','close','close_gc','close_remote') DEFAULT NULL,
  `creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `creation_remote_addr` varchar(40) DEFAULT NULL,
  `end_remote_addr` varchar(40) DEFAULT NULL,
  `end` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `uid_2` (`uid`,`status`),
  CONSTRAINT `user_sessions_lookup_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `people` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
