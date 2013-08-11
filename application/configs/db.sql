# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.5.24-0ubuntu0.12.04.1)
# Database: vehikel
# Generation Time: 2013-05-13 01:55:45 +0000
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



# Dump of table log
# ------------------------------------------------------------

CREATE TABLE `log` (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table people
# ------------------------------------------------------------

CREATE TABLE `people` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` char(15) NOT NULL,
  `picture_id` char(64) DEFAULT '',
  `email` char(60) DEFAULT '',
  `membership` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `name` char(50) NOT NULL,
  `private_email` tinyint(1) NOT NULL DEFAULT '1',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `address` text,
  `account_type` set('retail','private') NOT NULL DEFAULT 'private',
  `post_template` text NOT NULL,
  `post_template_html_escaped` text NOT NULL,
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
  `picture_id` char(64) DEFAULT NULL,
  `email` char(60) DEFAULT '',
  `membership` timestamp NULL DEFAULT NULL,
  `name` char(50) NOT NULL,
  `private_email` tinyint(1) NOT NULL DEFAULT '1',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `address` text,
  `change_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `account_type` set('retail','private') NOT NULL DEFAULT 'private',
  `post_template` text NOT NULL,
  `post_template_html_escaped` text NOT NULL,
  PRIMARY KEY (`history_id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table pictures
# ------------------------------------------------------------

CREATE TABLE `pictures` (
  `picture_id` char(64) NOT NULL,
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) unsigned DEFAULT NULL,
  `post_id` bigint(20) unsigned DEFAULT NULL,
  `meta` text NOT NULL,
  `options` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` set('active','removed') NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `picture_id` (`picture_id`),
  KEY `uid` (`uid`),
  KEY `post_id` (`post_id`)
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
  `engine` char(6) NOT NULL DEFAULT '',
  `transmission` set('automatic','manual','other') NOT NULL DEFAULT '',
  `fuel` set('gasoline','ethanol','diesel','flex','other') NOT NULL DEFAULT '',
  `km` int(11) DEFAULT NULL,
  `armor` tinyint(1) NOT NULL,
  `handicapped` tinyint(1) NOT NULL,
  `pictures_sorting_order` text NOT NULL,
  `equipment` text NOT NULL,
  `status` set('staging','active','end') NOT NULL DEFAULT 'staging',
  `traction` set('front','rear','4x4') NOT NULL DEFAULT '',
  `youtube_video` varchar(20) NOT NULL DEFAULT '',
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
  `engine` char(6) DEFAULT NULL,
  `transmission` set('automatic','manual','other') DEFAULT NULL,
  `fuel` set('gasoline','ethanol','diesel','flex','other') DEFAULT NULL,
  `km` int(11) DEFAULT NULL,
  `armor` tinyint(1) DEFAULT NULL,
  `handicapped` tinyint(1) DEFAULT NULL,
  `pictures_sorting_order` varchar(600) DEFAULT NULL,
  `equipment` varchar(600) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `traction` set('front','rear','4x4') DEFAULT '',
  `youtube_video` varchar(20) DEFAULT NULL,
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
