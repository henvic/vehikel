# ************************************************************
# Sequel Pro SQL dump
# Version 3408
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.5.24-0ubuntu0.12.04.1)
# Database: vehikel
# Generation Time: 2012-09-18 05:48:43 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table antiattack
# ------------------------------------------------------------

CREATE TABLE `antiattack` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ip` char(40) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `annotations` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table comments
# ------------------------------------------------------------

CREATE TABLE `comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) unsigned NOT NULL,
  `share` bigint(20) unsigned NOT NULL,
  `byUid` bigint(20) unsigned NOT NULL,
  `timestamp` datetime NOT NULL,
  `lastModified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `comments` text NOT NULL,
  `comments_filtered` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `share` (`share`,`byUid`),
  KEY `uid` (`uid`),
  KEY `byUid` (`byUid`),
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`share`) REFERENCES `share` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`byUid`) REFERENCES `people` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table credentials
# ------------------------------------------------------------

CREATE TABLE `credentials` (
  `uid` bigint(20) unsigned NOT NULL,
  `credential` char(200) NOT NULL DEFAULT '',
  UNIQUE KEY `user` (`uid`),
  CONSTRAINT `credentials_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table people
# ------------------------------------------------------------

CREATE TABLE `people` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` char(15) NOT NULL,
  `email` char(60) DEFAULT '',
  `membership` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `name` char(50) NOT NULL,
  `avatar_info` varchar(600) NOT NULL DEFAULT 'a:0:{}',
  `private_email` tinyint(1) NOT NULL DEFAULT '1',
  `active` tinyint(1) NOT NULL DEFAULT '1',
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
  `membership` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `name` char(50) NOT NULL,
  `avatar_info` varchar(600) NOT NULL DEFAULT 'a:0:{}',
  `private_email` tinyint(1) NOT NULL DEFAULT '1',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `change_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`history_id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table profile
# ------------------------------------------------------------

CREATE TABLE `profile` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'uid (people.id)',
  `website` varchar(100) DEFAULT '',
  `location` varchar(40) DEFAULT '',
  `about` text COMMENT 'RAW user input',
  `about_filtered` text COMMENT 'filtered about',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `profile_ibfk_1` FOREIGN KEY (`id`) REFERENCES `people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table profile_history
# ------------------------------------------------------------

CREATE TABLE `profile_history` (
  `history_id` char(36) NOT NULL,
  `id` bigint(20) unsigned NOT NULL COMMENT 'uid (people.id)',
  `website` varchar(100) DEFAULT '',
  `location` varchar(40) DEFAULT '',
  `about` text COMMENT 'RAW user input',
  `about_filtered` text COMMENT 'filtered about',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `change_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`history_id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table share
# ------------------------------------------------------------

CREATE TABLE `share` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `byUid` bigint(20) unsigned NOT NULL,
  `secret` bigint(20) unsigned NOT NULL,
  `download_secret` bigint(20) unsigned NOT NULL,
  `uploadedTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastChange` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `privacy` set('me','friend','family','coworker') DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `filename` varchar(60) NOT NULL,
  `type` varchar(50) NOT NULL,
  `fileSize` bigint(20) unsigned NOT NULL,
  `md5` char(32) NOT NULL,
  `description` text NOT NULL,
  `description_filtered` text NOT NULL,
  `short` varchar(120) NOT NULL,
  `views` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `byUid` (`byUid`),
  KEY `filename` (`filename`),
  KEY `title` (`title`),
  KEY `tweet` (`short`),
  CONSTRAINT `share_ibfk_2` FOREIGN KEY (`byUid`) REFERENCES `people` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='share';



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
