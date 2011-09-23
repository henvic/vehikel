# ************************************************************
# Sequel Pro SQL dump
# Version 3408
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.1.41-3ubuntu12.10)
# Database: medialab
# Generation Time: 2011-09-26 18:40:23 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table abuse
# ------------------------------------------------------------

CREATE TABLE `abuse` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `referer` varchar(512) NOT NULL,
  `report_uid` bigint(20) unsigned DEFAULT NULL,
  `description` text NOT NULL,
  `byUid` bigint(20) unsigned DEFAULT NULL,
  `byAddr` varchar(100) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `solution` enum('unsolved','solved','notabuse') NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table agenda
# ------------------------------------------------------------

CREATE TABLE `agenda` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



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



# Dump of table contacts
# ------------------------------------------------------------

CREATE TABLE `contacts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) unsigned NOT NULL,
  `has` bigint(20) unsigned NOT NULL,
  `friend` tinyint(1) NOT NULL,
  `since` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid_2` (`uid`,`has`),
  KEY `uid` (`uid`),
  KEY `has` (`has`),
  CONSTRAINT `contacts_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `people` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contacts_ibfk_2` FOREIGN KEY (`has`) REFERENCES `people` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table coupons
# ------------------------------------------------------------

CREATE TABLE `coupons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `hash` char(16) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `amount` bigint(20) unsigned NOT NULL,
  `sack` enum('cents_usd') NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `unique_use` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table credentials
# ------------------------------------------------------------

CREATE TABLE `credentials` (
  `uid` bigint(20) unsigned NOT NULL,
  `credential` char(60) NOT NULL DEFAULT '' COMMENT 'Credential is the concatenation of some values',
  UNIQUE KEY `user` (`uid`),
  CONSTRAINT `credentials_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table email_change
# ------------------------------------------------------------

CREATE TABLE `email_change` (
  `uid` bigint(20) unsigned NOT NULL,
  `email` char(60) NOT NULL,
  `securitycode` char(40) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uid`),
  CONSTRAINT `email_change_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `people` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table favorites
# ------------------------------------------------------------

CREATE TABLE `favorites` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) unsigned NOT NULL COMMENT 'who is favoriting',
  `share` bigint(20) unsigned NOT NULL,
  `byUid` bigint(20) unsigned NOT NULL COMMENT 'whom is favorited',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `share` (`uid`,`share`),
  KEY `share_2` (`uid`),
  KEY `uid` (`share`),
  KEY `sharer_uid` (`byUid`),
  CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `people` (`id`) ON DELETE CASCADE,
  CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`share`) REFERENCES `share` (`id`) ON DELETE CASCADE,
  CONSTRAINT `favorites_ibfk_3` FOREIGN KEY (`byUid`) REFERENCES `people` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table ignore
# ------------------------------------------------------------

CREATE TABLE `ignore` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) unsigned NOT NULL,
  `ignore` bigint(20) unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `ignore` (`ignore`),
  CONSTRAINT `ignore_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `people` (`id`),
  CONSTRAINT `ignore_ibfk_2` FOREIGN KEY (`ignore`) REFERENCES `people` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table invites
# ------------------------------------------------------------

CREATE TABLE `invites` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `hash` char(8) NOT NULL,
  `uid` bigint(20) unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `used` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table log
# ------------------------------------------------------------

CREATE TABLE `log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `remote_addr` varchar(40) NOT NULL,
  `cookies` varchar(256) NOT NULL,
  `dump` text NOT NULL,
  `uid` bigint(20) unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reason_type` enum('transaction','login','logout','remote_logout') NOT NULL,
  `reason_id` bigint(20) unsigned DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table new_users
# ------------------------------------------------------------

CREATE TABLE `new_users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` char(60) NOT NULL,
  `name` varchar(100) NOT NULL,
  `securitycode` char(40) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table oauth_consumer_registry
# ------------------------------------------------------------

CREATE TABLE `oauth_consumer_registry` (
  `ocr_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ocr_usa_id_ref` bigint(20) unsigned DEFAULT NULL,
  `ocr_consumer_key` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ocr_consumer_secret` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ocr_signature_methods` varchar(255) NOT NULL DEFAULT 'HMAC-SHA1,PLAINTEXT',
  `ocr_server_uri` varchar(255) NOT NULL,
  `ocr_server_uri_host` varchar(128) NOT NULL,
  `ocr_server_uri_path` varchar(128) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ocr_request_token_uri` varchar(255) NOT NULL,
  `ocr_authorize_uri` varchar(255) NOT NULL,
  `ocr_access_token_uri` varchar(255) NOT NULL,
  `ocr_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ocr_id`),
  UNIQUE KEY `ocr_consumer_key` (`ocr_consumer_key`,`ocr_usa_id_ref`),
  KEY `ocr_server_uri` (`ocr_server_uri`),
  KEY `ocr_server_uri_host` (`ocr_server_uri_host`,`ocr_server_uri_path`),
  KEY `ocr_usa_id_ref` (`ocr_usa_id_ref`),
  CONSTRAINT `oauth_consumer_registry_ibfk_1` FOREIGN KEY (`ocr_usa_id_ref`) REFERENCES `people` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table oauth_consumer_token
# ------------------------------------------------------------

CREATE TABLE `oauth_consumer_token` (
  `oct_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `oct_ocr_id_ref` bigint(20) unsigned NOT NULL,
  `oct_usa_id_ref` bigint(20) unsigned NOT NULL,
  `oct_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `oct_token` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `oct_token_secret` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `oct_token_type` enum('request','authorized','access') DEFAULT NULL,
  `oct_token_ttl` datetime NOT NULL DEFAULT '9999-12-31 00:00:00',
  `oct_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`oct_id`),
  UNIQUE KEY `oct_ocr_id_ref` (`oct_ocr_id_ref`,`oct_token`),
  UNIQUE KEY `oct_usa_id_ref` (`oct_usa_id_ref`,`oct_ocr_id_ref`,`oct_token_type`,`oct_name`),
  KEY `oct_token_ttl` (`oct_token_ttl`),
  CONSTRAINT `oauth_consumer_token_ibfk_1` FOREIGN KEY (`oct_ocr_id_ref`) REFERENCES `oauth_consumer_registry` (`ocr_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table oauth_log
# ------------------------------------------------------------

CREATE TABLE `oauth_log` (
  `olg_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `olg_osr_consumer_key` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `olg_ost_token` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `olg_ocr_consumer_key` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `olg_oct_token` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `olg_usa_id_ref` bigint(20) unsigned DEFAULT NULL,
  `olg_received` text NOT NULL,
  `olg_sent` text NOT NULL,
  `olg_base_string` text NOT NULL,
  `olg_notes` text NOT NULL,
  `olg_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `olg_remote_ip` bigint(20) NOT NULL,
  PRIMARY KEY (`olg_id`),
  KEY `olg_osr_consumer_key` (`olg_osr_consumer_key`,`olg_id`),
  KEY `olg_ost_token` (`olg_ost_token`,`olg_id`),
  KEY `olg_ocr_consumer_key` (`olg_ocr_consumer_key`,`olg_id`),
  KEY `olg_oct_token` (`olg_oct_token`,`olg_id`),
  KEY `olg_usa_id_ref` (`olg_usa_id_ref`,`olg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table oauth_server_nonce
# ------------------------------------------------------------

CREATE TABLE `oauth_server_nonce` (
  `osn_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `osn_consumer_key` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `osn_token` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `osn_timestamp` bigint(20) NOT NULL,
  `osn_nonce` varchar(80) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`osn_id`),
  UNIQUE KEY `osn_consumer_key` (`osn_consumer_key`,`osn_token`,`osn_timestamp`,`osn_nonce`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table oauth_server_registry
# ------------------------------------------------------------

CREATE TABLE `oauth_server_registry` (
  `osr_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `osr_usa_id_ref` bigint(20) unsigned DEFAULT NULL,
  `osr_consumer_key` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `osr_consumer_secret` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `osr_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `osr_status` varchar(16) NOT NULL,
  `osr_requester_name` varchar(64) NOT NULL,
  `osr_requester_email` varchar(64) NOT NULL,
  `osr_callback_uri` varchar(255) NOT NULL,
  `osr_application_uri` varchar(255) NOT NULL,
  `osr_application_title` varchar(80) NOT NULL,
  `osr_application_descr` text NOT NULL,
  `osr_application_notes` text NOT NULL,
  `osr_application_type` varchar(20) NOT NULL,
  `osr_application_commercial` tinyint(1) NOT NULL DEFAULT '0',
  `osr_issue_date` datetime NOT NULL,
  `osr_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`osr_id`),
  UNIQUE KEY `osr_consumer_key` (`osr_consumer_key`),
  KEY `osr_usa_id_ref` (`osr_usa_id_ref`),
  CONSTRAINT `oauth_server_registry_ibfk_1` FOREIGN KEY (`osr_usa_id_ref`) REFERENCES `people` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table oauth_server_token
# ------------------------------------------------------------

CREATE TABLE `oauth_server_token` (
  `ost_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ost_osr_id_ref` bigint(20) unsigned NOT NULL,
  `ost_usa_id_ref` bigint(20) unsigned NOT NULL,
  `ost_token` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ost_token_secret` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ost_token_type` enum('request','access') DEFAULT NULL,
  `ost_authorized` tinyint(1) NOT NULL DEFAULT '0',
  `ost_referrer_host` varchar(128) NOT NULL,
  `ost_token_ttl` datetime NOT NULL DEFAULT '9999-12-31 00:00:00',
  `ost_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ost_id`),
  UNIQUE KEY `ost_token` (`ost_token`),
  KEY `ost_osr_id_ref` (`ost_osr_id_ref`),
  KEY `ost_token_ttl` (`ost_token_ttl`),
  KEY `ost_usa_id_ref` (`ost_usa_id_ref`),
  CONSTRAINT `oauth_server_token_ibfk_1` FOREIGN KEY (`ost_osr_id_ref`) REFERENCES `oauth_server_registry` (`osr_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `oauth_server_token_ibfk_2` FOREIGN KEY (`ost_usa_id_ref`) REFERENCES `people` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table people
# ------------------------------------------------------------

CREATE TABLE `people` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `alias` char(15) NOT NULL,
  `email` char(60) NOT NULL,
  `membershipdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `name` char(50) NOT NULL,
  `avatarInfo` varchar(320) NOT NULL,
  `private_email` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`,`alias`),
  UNIQUE KEY `alias` (`alias`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table people_deleted
# ------------------------------------------------------------

CREATE TABLE `people_deleted` (
  `id` bigint(20) unsigned NOT NULL,
  `alias` char(15) NOT NULL,
  `email` char(60) NOT NULL,
  `membershipdate` datetime NOT NULL,
  `name` char(50) NOT NULL,
  `private_email` tinyint(1) NOT NULL,
  `delete_timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`,`alias`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table phonelist
# ------------------------------------------------------------

CREATE TABLE `phonelist` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `agenda` bigint(20) NOT NULL,
  `phone` bigint(20) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `eid` varchar(100) DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `phone` (`phone`),
  UNIQUE KEY `eid` (`eid`),
  KEY `agenda` (`agenda`),
  CONSTRAINT `phonelist_ibfk_1` FOREIGN KEY (`agenda`) REFERENCES `agenda` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table profile
# ------------------------------------------------------------

CREATE TABLE `profile` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'uid (people.id)',
  `website` varchar(100) NOT NULL,
  `location` varchar(40) NOT NULL,
  `about` text NOT NULL COMMENT 'RAW user input',
  `about_filtered` text NOT NULL COMMENT 'filtered about',
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `profile_ibfk_1` FOREIGN KEY (`id`) REFERENCES `people` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table recover
# ------------------------------------------------------------

CREATE TABLE `recover` (
  `uid` bigint(20) unsigned NOT NULL,
  `securitycode` char(40) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uid`),
  CONSTRAINT `recover_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `people` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table remove_shares_files
# ------------------------------------------------------------

CREATE TABLE `remove_shares_files` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `share` bigint(20) unsigned NOT NULL,
  `byUid` bigint(20) unsigned NOT NULL,
  `alias` char(15) NOT NULL,
  `download_secret` bigint(20) unsigned NOT NULL,
  `filename` char(60) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `byUid` (`byUid`),
  KEY `alias` (`alias`),
  KEY `share` (`share`)
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
  CONSTRAINT `share_ibfk_1` FOREIGN KEY (`id`) REFERENCES `upload_history` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `share_ibfk_2` FOREIGN KEY (`byUid`) REFERENCES `people` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='share';



# Dump of table tags
# ------------------------------------------------------------

CREATE TABLE `tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `share` bigint(20) unsigned NOT NULL,
  `people` bigint(20) unsigned NOT NULL,
  `clean` varchar(45) NOT NULL,
  `raw` varchar(45) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `share` (`share`,`clean`),
  KEY `people` (`people`),
  CONSTRAINT `tags_ibfk_1` FOREIGN KEY (`share`) REFERENCES `share` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tags_ibfk_2` FOREIGN KEY (`people`) REFERENCES `people` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table transactions
# ------------------------------------------------------------

CREATE TABLE `transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pid` char(16) NOT NULL COMMENT 'Public ID',
  `uid` bigint(20) unsigned NOT NULL,
  `amount` bigint(20) NOT NULL,
  `sack` enum('cents_usd') NOT NULL,
  `reason_type` enum('transfer','redeem') NOT NULL,
  `reason_id` bigint(20) unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pid` (`pid`),
  KEY `uid` (`uid`,`amount`,`sack`,`reason_type`,`reason_id`,`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table twitter
# ------------------------------------------------------------

CREATE TABLE `twitter` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'as of Twitter',
  `uid` bigint(20) unsigned NOT NULL,
  `oauth_token` char(60) NOT NULL,
  `oauth_token_secret` char(60) NOT NULL,
  `screen_name` char(15) NOT NULL,
  `name` char(20) NOT NULL,
  `timestamp` datetime NOT NULL COMMENT 'the last check of data (not necessarily when was last changed)',
  `authorizedSince` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`uid`),
  KEY `id` (`id`),
  CONSTRAINT `twitter_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `people` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table upload_history
# ------------------------------------------------------------

CREATE TABLE `upload_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `byUid` bigint(20) unsigned NOT NULL,
  `fileSize` bigint(20) unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `filename` char(60) NOT NULL,
  `uploadError` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `byUid` (`byUid`)
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
