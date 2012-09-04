-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 03, 2012 at 07:24 PM
-- Server version: 5.5.24
-- PHP Version: 5.3.10-1ubuntu3.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `onlinetaz`
--

-- --------------------------------------------------------

--
-- Table structure for table `backend_layout`
--

DROP TABLE IF EXISTS `backend_layout`;
CREATE TABLE IF NOT EXISTS `backend_layout` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `t3ver_oid` int(11) NOT NULL DEFAULT '0',
  `t3ver_id` int(11) NOT NULL DEFAULT '0',
  `t3ver_wsid` int(11) NOT NULL DEFAULT '0',
  `t3ver_label` varchar(255) NOT NULL DEFAULT '',
  `t3ver_state` tinyint(4) NOT NULL DEFAULT '0',
  `t3ver_stage` int(11) NOT NULL DEFAULT '0',
  `t3ver_count` int(11) NOT NULL DEFAULT '0',
  `t3ver_tstamp` int(11) NOT NULL DEFAULT '0',
  `t3ver_move_id` int(11) NOT NULL DEFAULT '0',
  `t3_origuid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) unsigned NOT NULL DEFAULT '0',
  `crdate` int(11) unsigned NOT NULL DEFAULT '0',
  `cruser_id` int(11) unsigned NOT NULL DEFAULT '0',
  `hidden` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `sorting` int(11) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `config` text NOT NULL,
  `icon` text NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `t3ver_oid` (`t3ver_oid`,`t3ver_wsid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `be_groups`
--

DROP TABLE IF EXISTS `be_groups`;
CREATE TABLE IF NOT EXISTS `be_groups` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  `tstamp` int(11) unsigned NOT NULL DEFAULT '0',
  `title` varchar(50) DEFAULT '',
  `non_exclude_fields` text,
  `explicit_allowdeny` text,
  `allowed_languages` varchar(255) DEFAULT '',
  `custom_options` text,
  `db_mountpoints` varchar(255) DEFAULT '',
  `pagetypes_select` varchar(255) DEFAULT '',
  `tables_select` text,
  `tables_modify` text,
  `crdate` int(11) unsigned NOT NULL DEFAULT '0',
  `cruser_id` int(11) unsigned NOT NULL DEFAULT '0',
  `groupMods` text,
  `file_mountpoints` varchar(255) DEFAULT '',
  `hidden` tinyint(1) unsigned DEFAULT '0',
  `inc_access_lists` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `lockToDomain` varchar(50) NOT NULL DEFAULT '',
  `deleted` tinyint(1) unsigned DEFAULT '0',
  `TSconfig` text,
  `subgroup` varchar(255) DEFAULT '',
  `hide_in_lists` tinyint(4) NOT NULL DEFAULT '0',
  `workspace_perms` tinyint(3) NOT NULL DEFAULT '1',
  `tx_clbecache_pagecache` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `tt_news_categorymounts` tinytext NOT NULL,
  `tx_tinyrte_tinyrte_plugins` blob NOT NULL,
  `fileoper_perms` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

--
-- Table structure for table `be_sessions`
--

DROP TABLE IF EXISTS `be_sessions`;
CREATE TABLE IF NOT EXISTS `be_sessions` (
  `ses_id` varchar(32) NOT NULL DEFAULT '',
  `ses_name` varchar(32) NOT NULL DEFAULT '',
  `ses_iplock` varchar(39) NOT NULL DEFAULT '',
  `ses_hashlock` int(11) NOT NULL DEFAULT '0',
  `ses_userid` int(11) unsigned NOT NULL DEFAULT '0',
  `ses_tstamp` int(11) unsigned NOT NULL DEFAULT '0',
  `ses_data` longtext,
  `ses_backuserid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ses_id`,`ses_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `be_users`
--

DROP TABLE IF EXISTS `be_users`;
CREATE TABLE IF NOT EXISTS `be_users` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  `tstamp` int(11) unsigned NOT NULL DEFAULT '0',
  `username` varchar(50) DEFAULT '',
  `password` varchar(40) NOT NULL DEFAULT '',
  `admin` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `usergroup` varchar(255) DEFAULT '',
  `disable` tinyint(1) unsigned DEFAULT '0',
  `starttime` int(11) unsigned NOT NULL DEFAULT '0',
  `endtime` int(11) unsigned NOT NULL DEFAULT '0',
  `lang` char(2) DEFAULT '',
  `email` varchar(80) NOT NULL DEFAULT '',
  `db_mountpoints` varchar(255) DEFAULT '',
  `options` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `crdate` int(11) unsigned NOT NULL DEFAULT '0',
  `cruser_id` int(11) unsigned NOT NULL DEFAULT '0',
  `realName` varchar(80) NOT NULL DEFAULT '',
  `userMods` varchar(255) DEFAULT '',
  `allowed_languages` varchar(255) DEFAULT '',
  `uc` mediumtext,
  `file_mountpoints` varchar(255) DEFAULT '',
  `fileoper_perms` tinyint(4) NOT NULL DEFAULT '0',
  `workspace_perms` tinyint(3) NOT NULL DEFAULT '1',
  `lockToDomain` varchar(50) NOT NULL DEFAULT '',
  `disableIPlock` tinyint(1) unsigned DEFAULT '0',
  `deleted` tinyint(1) unsigned DEFAULT '0',
  `TSconfig` text,
  `lastlogin` int(10) unsigned NOT NULL DEFAULT '0',
  `createdByAction` int(11) NOT NULL DEFAULT '0',
  `usergroup_cached_list` varchar(255) DEFAULT '',
  `workspace_id` int(11) NOT NULL DEFAULT '0',
  `workspace_preview` tinyint(3) NOT NULL DEFAULT '1',
  `tt_news_categorymounts` tinytext NOT NULL,
  `tx_tinyrte_tinyrte_plugins` blob NOT NULL,
  `tx_newspaper_role` int(11) NOT NULL DEFAULT '0',
  `tx_mydashboard_config` text NOT NULL,
  `tx_mydashboard_selfadmin` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=295 ;

-- --------------------------------------------------------

--
-- Table structure for table `cache_extensions`
--

DROP TABLE IF EXISTS `cache_extensions`;
CREATE TABLE IF NOT EXISTS `cache_extensions` (
  `extkey` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `version` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `alldownloadcounter` int(11) unsigned NOT NULL DEFAULT '0',
  `downloadcounter` int(11) unsigned NOT NULL DEFAULT '0',
  `title` varchar(150) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` mediumtext COLLATE utf8_unicode_ci,
  `state` int(4) NOT NULL DEFAULT '0',
  `reviewstate` int(4) NOT NULL DEFAULT '0',
  `category` int(4) NOT NULL DEFAULT '0',
  `lastuploaddate` int(11) unsigned NOT NULL DEFAULT '0',
  `dependencies` mediumtext COLLATE utf8_unicode_ci,
  `authorname` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `authoremail` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ownerusername` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `t3xfilemd5` varchar(35) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `uploadcomment` mediumtext COLLATE utf8_unicode_ci,
  `authorcompany` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `intversion` int(11) NOT NULL DEFAULT '0',
  `lastversion` int(3) NOT NULL DEFAULT '0',
  `lastreviewedversion` int(3) NOT NULL DEFAULT '0',
  `repository` int(11) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`extkey`,`version`,`repository`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_hash`
--

DROP TABLE IF EXISTS `cache_hash`;
CREATE TABLE IF NOT EXISTS `cache_hash` (
  `hash` varchar(32) NOT NULL DEFAULT '',
  `content` mediumblob NOT NULL,
  `tstamp` int(11) unsigned NOT NULL DEFAULT '0',
  `ident` varchar(32) DEFAULT '',
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=90 ;

-- --------------------------------------------------------

--
-- Table structure for table `cache_imagesizes`
--

DROP TABLE IF EXISTS `cache_imagesizes`;
CREATE TABLE IF NOT EXISTS `cache_imagesizes` (
  `md5hash` varchar(32) NOT NULL DEFAULT '',
  `md5filename` varchar(32) NOT NULL DEFAULT '',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `filename` varchar(255) DEFAULT '',
  `imagewidth` mediumint(11) unsigned NOT NULL DEFAULT '0',
  `imageheight` mediumint(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`md5filename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cache_md5params`
--

DROP TABLE IF EXISTS `cache_md5params`;
CREATE TABLE IF NOT EXISTS `cache_md5params` (
  `md5hash` varchar(20) NOT NULL DEFAULT '',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `type` tinyint(3) NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  PRIMARY KEY (`md5hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cache_pages`
--

DROP TABLE IF EXISTS `cache_pages`;
CREATE TABLE IF NOT EXISTS `cache_pages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(32) NOT NULL DEFAULT '',
  `page_id` int(11) unsigned NOT NULL DEFAULT '0',
  `reg1` int(11) unsigned NOT NULL DEFAULT '0',
  `HTML` mediumblob NOT NULL,
  `temp_content` int(1) NOT NULL DEFAULT '0',
  `tstamp` int(11) unsigned NOT NULL DEFAULT '0',
  `expires` int(10) unsigned NOT NULL DEFAULT '0',
  `cache_data` mediumblob NOT NULL,
  `tx_oelib_is_dummy_record` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `page_id` (`page_id`),
  KEY `sel` (`hash`,`page_id`),
  KEY `dummy` (`tx_oelib_is_dummy_record`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cache_pagesection`
--

DROP TABLE IF EXISTS `cache_pagesection`;
CREATE TABLE IF NOT EXISTS `cache_pagesection` (
  `page_id` int(11) unsigned NOT NULL DEFAULT '0',
  `mpvar_hash` int(11) unsigned NOT NULL DEFAULT '0',
  `content` blob NOT NULL,
  `tstamp` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`page_id`,`mpvar_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cache_treelist`
--

DROP TABLE IF EXISTS `cache_treelist`;
CREATE TABLE IF NOT EXISTS `cache_treelist` (
  `md5hash` char(32) NOT NULL DEFAULT '',
  `pid` int(11) NOT NULL DEFAULT '0',
  `treelist` text,
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `expires` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`md5hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cache_typo3temp_log`
--

DROP TABLE IF EXISTS `cache_typo3temp_log`;
CREATE TABLE IF NOT EXISTS `cache_typo3temp_log` (
  `md5hash` varchar(32) NOT NULL DEFAULT '',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `filename` varchar(255) DEFAULT '',
  `orig_filename` varchar(255) DEFAULT '',
  PRIMARY KEY (`md5hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cachingframework_cache_hash`
--

DROP TABLE IF EXISTS `cachingframework_cache_hash`;
CREATE TABLE IF NOT EXISTS `cachingframework_cache_hash` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(128) NOT NULL DEFAULT '',
  `crdate` int(11) unsigned NOT NULL DEFAULT '0',
  `content` mediumblob,
  `lifetime` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `cache_id` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cachingframework_cache_hash_tags`
--

DROP TABLE IF EXISTS `cachingframework_cache_hash_tags`;
CREATE TABLE IF NOT EXISTS `cachingframework_cache_hash_tags` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(128) NOT NULL DEFAULT '',
  `tag` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `cache_id` (`identifier`),
  KEY `cache_tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cachingframework_cache_pages`
--

DROP TABLE IF EXISTS `cachingframework_cache_pages`;
CREATE TABLE IF NOT EXISTS `cachingframework_cache_pages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(128) NOT NULL DEFAULT '',
  `crdate` int(11) unsigned NOT NULL DEFAULT '0',
  `content` mediumblob,
  `lifetime` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `cache_id` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cachingframework_cache_pagesection`
--

DROP TABLE IF EXISTS `cachingframework_cache_pagesection`;
CREATE TABLE IF NOT EXISTS `cachingframework_cache_pagesection` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(128) NOT NULL DEFAULT '',
  `crdate` int(11) unsigned NOT NULL DEFAULT '0',
  `content` mediumblob,
  `lifetime` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `cache_id` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cachingframework_cache_pagesection_tags`
--

DROP TABLE IF EXISTS `cachingframework_cache_pagesection_tags`;
CREATE TABLE IF NOT EXISTS `cachingframework_cache_pagesection_tags` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(128) NOT NULL DEFAULT '',
  `tag` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `cache_id` (`identifier`),
  KEY `cache_tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cachingframework_cache_pages_tags`
--

DROP TABLE IF EXISTS `cachingframework_cache_pages_tags`;
CREATE TABLE IF NOT EXISTS `cachingframework_cache_pages_tags` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(128) NOT NULL DEFAULT '',
  `tag` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `cache_id` (`identifier`),
  KEY `cache_tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `fe_groups`
--

DROP TABLE IF EXISTS `fe_groups`;
CREATE TABLE IF NOT EXISTS `fe_groups` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  `tstamp` int(11) unsigned NOT NULL DEFAULT '0',
  `title` varchar(50) DEFAULT '',
  `hidden` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `lockToDomain` varchar(50) NOT NULL DEFAULT '',
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `subgroup` tinytext,
  `TSconfig` text,
  `crdate` int(11) unsigned NOT NULL DEFAULT '0',
  `cruser_id` int(11) unsigned NOT NULL DEFAULT '0',
  `tx_oelib_is_dummy_record` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `tx_extbase_type` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `dummy` (`tx_oelib_is_dummy_record`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `fe_sessions`
--

DROP TABLE IF EXISTS `fe_sessions`;
CREATE TABLE IF NOT EXISTS `fe_sessions` (
  `ses_id` varchar(32) NOT NULL DEFAULT '',
  `ses_name` varchar(32) NOT NULL DEFAULT '',
  `ses_iplock` varchar(39) NOT NULL DEFAULT '',
  `ses_hashlock` int(11) NOT NULL DEFAULT '0',
  `ses_userid` int(11) unsigned NOT NULL DEFAULT '0',
  `ses_tstamp` int(11) unsigned NOT NULL DEFAULT '0',
  `ses_data` blob NOT NULL,
  `ses_permanent` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ses_id`,`ses_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fe_session_data`
--

DROP TABLE IF EXISTS `fe_session_data`;
CREATE TABLE IF NOT EXISTS `fe_session_data` (
  `hash` varchar(32) NOT NULL DEFAULT '',
  `content` mediumblob NOT NULL,
  `tstamp` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`hash`),
  KEY `tstamp` (`tstamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fe_users`
--

DROP TABLE IF EXISTS `fe_users`;
CREATE TABLE IF NOT EXISTS `fe_users` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  `tstamp` int(11) unsigned NOT NULL DEFAULT '0',
  `username` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(40) NOT NULL DEFAULT '',
  `usergroup` tinytext,
  `disable` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `starttime` int(11) unsigned NOT NULL DEFAULT '0',
  `endtime` int(11) unsigned NOT NULL DEFAULT '0',
  `name` varchar(80) NOT NULL DEFAULT '',
  `address` varchar(255) DEFAULT '',
  `telephone` varchar(20) NOT NULL DEFAULT '',
  `fax` varchar(20) NOT NULL DEFAULT '',
  `email` varchar(80) NOT NULL DEFAULT '',
  `crdate` int(11) unsigned NOT NULL DEFAULT '0',
  `cruser_id` int(11) unsigned NOT NULL DEFAULT '0',
  `lockToDomain` varchar(50) NOT NULL DEFAULT '',
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `uc` blob NOT NULL,
  `title` varchar(40) NOT NULL DEFAULT '',
  `zip` varchar(10) NOT NULL DEFAULT '',
  `city` varchar(50) NOT NULL DEFAULT '',
  `country` varchar(40) NOT NULL DEFAULT '',
  `www` varchar(80) NOT NULL DEFAULT '',
  `company` varchar(80) NOT NULL DEFAULT '',
  `image` tinytext,
  `TSconfig` text,
  `fe_cruser_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lastlogin` int(10) unsigned NOT NULL DEFAULT '0',
  `is_online` int(10) unsigned NOT NULL DEFAULT '0',
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `middle_name` varchar(50) NOT NULL DEFAULT '',
  `last_name` varchar(50) NOT NULL DEFAULT '',
  `tx_oelib_is_dummy_record` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `tx_extbase_type` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`uid`),
  KEY `username` (`username`),
  KEY `is_online` (`is_online`),
  KEY `parent` (`pid`,`username`),
  KEY `dummy` (`tx_oelib_is_dummy_record`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
CREATE TABLE IF NOT EXISTS `pages` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `t3ver_oid` int(11) NOT NULL DEFAULT '0',
  `t3ver_id` int(11) NOT NULL DEFAULT '0',
  `t3ver_wsid` int(11) NOT NULL DEFAULT '0',
  `t3ver_label` varchar(255) DEFAULT '',
  `t3ver_state` tinyint(4) NOT NULL DEFAULT '0',
  `t3ver_stage` int(11) DEFAULT '0',
  `t3ver_count` int(11) NOT NULL DEFAULT '0',
  `t3ver_tstamp` int(11) NOT NULL DEFAULT '0',
  `t3ver_swapmode` tinyint(4) NOT NULL DEFAULT '0',
  `t3_origuid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) unsigned NOT NULL DEFAULT '0',
  `sorting` int(11) unsigned NOT NULL DEFAULT '0',
  `deleted` tinyint(1) unsigned DEFAULT '0',
  `perms_userid` int(11) unsigned NOT NULL DEFAULT '0',
  `perms_groupid` int(11) unsigned NOT NULL DEFAULT '0',
  `perms_user` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `perms_group` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `perms_everybody` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `editlock` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `crdate` int(11) unsigned NOT NULL DEFAULT '0',
  `cruser_id` int(11) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) DEFAULT '',
  `doktype` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `TSconfig` text,
  `storage_pid` int(11) NOT NULL DEFAULT '0',
  `is_siteroot` tinyint(4) NOT NULL DEFAULT '0',
  `php_tree_stop` tinyint(4) NOT NULL DEFAULT '0',
  `tx_impexp_origuid` int(11) NOT NULL DEFAULT '0',
  `url` varchar(255) DEFAULT '',
  `hidden` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `starttime` int(11) unsigned NOT NULL DEFAULT '0',
  `endtime` int(11) unsigned NOT NULL DEFAULT '0',
  `urltype` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `shortcut` int(10) unsigned NOT NULL DEFAULT '0',
  `shortcut_mode` int(10) unsigned NOT NULL DEFAULT '0',
  `no_cache` int(10) unsigned NOT NULL DEFAULT '0',
  `fe_group` varchar(100) NOT NULL DEFAULT '0',
  `subtitle` varchar(255) DEFAULT '',
  `layout` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `target` varchar(80) DEFAULT '',
  `media` text,
  `lastUpdated` int(10) unsigned NOT NULL DEFAULT '0',
  `keywords` text NOT NULL,
  `cache_timeout` int(10) unsigned NOT NULL DEFAULT '0',
  `newUntil` int(10) unsigned NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `no_search` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `SYS_LASTCHANGED` int(10) unsigned NOT NULL DEFAULT '0',
  `abstract` text NOT NULL,
  `module` varchar(10) NOT NULL DEFAULT '',
  `extendToSubpages` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `author` varchar(255) DEFAULT '',
  `author_email` varchar(80) NOT NULL DEFAULT '',
  `nav_title` varchar(255) DEFAULT '',
  `nav_hide` tinyint(4) NOT NULL DEFAULT '0',
  `content_from_pid` int(10) unsigned NOT NULL DEFAULT '0',
  `mount_pid` int(10) unsigned NOT NULL DEFAULT '0',
  `mount_pid_ol` tinyint(4) NOT NULL DEFAULT '0',
  `alias` varchar(32) NOT NULL DEFAULT '',
  `l18n_cfg` tinyint(4) NOT NULL DEFAULT '0',
  `fe_login_mode` tinyint(4) NOT NULL DEFAULT '0',
  `tx_dmchttps_https` int(11) unsigned NOT NULL DEFAULT '0',
  `t3ver_move_id` int(11) NOT NULL DEFAULT '0',
  `tx_newspaper_associated_section` text,
  `tx_newspaper_module` tinytext NOT NULL,
  `url_scheme` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `backend_layout` int(10) NOT NULL DEFAULT '0',
  `backend_layout_next_level` int(10) NOT NULL DEFAULT '0',
  `tx_oelib_is_dummy_record` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `t3ver_oid` (`t3ver_oid`,`t3ver_wsid`),
  KEY `alias` (`alias`),
  KEY `parent` (`pid`,`sorting`,`deleted`,`hidden`),
  KEY `dummy` (`tx_oelib_is_dummy_record`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4189 ;

-- --------------------------------------------------------

--
-- Table structure for table `pages_language_overlay`
--

DROP TABLE IF EXISTS `pages_language_overlay`;
CREATE TABLE IF NOT EXISTS `pages_language_overlay` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `t3ver_oid` int(11) NOT NULL DEFAULT '0',
  `t3ver_id` int(11) NOT NULL DEFAULT '0',
  `t3ver_wsid` int(11) NOT NULL DEFAULT '0',
  `t3ver_label` varchar(255) DEFAULT '',
  `t3ver_state` tinyint(4) NOT NULL DEFAULT '0',
  `t3ver_stage` int(11) DEFAULT '0',
  `t3ver_count` int(11) NOT NULL DEFAULT '0',
  `t3ver_tstamp` int(11) NOT NULL DEFAULT '0',
  `t3_origuid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) unsigned NOT NULL DEFAULT '0',
  `crdate` int(11) unsigned NOT NULL DEFAULT '0',
  `cruser_id` int(11) unsigned NOT NULL DEFAULT '0',
  `sys_language_uid` int(11) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) DEFAULT '',
  `hidden` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `starttime` int(11) unsigned NOT NULL DEFAULT '0',
  `endtime` int(11) unsigned NOT NULL DEFAULT '0',
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `subtitle` varchar(255) DEFAULT '',
  `nav_title` varchar(255) DEFAULT '',
  `media` tinytext,
  `keywords` text NOT NULL,
  `description` text NOT NULL,
  `abstract` text NOT NULL,
  `author` varchar(255) DEFAULT '',
  `author_email` varchar(80) NOT NULL DEFAULT '',
  `tx_impexp_origuid` int(11) NOT NULL DEFAULT '0',
  `l18n_diffsource` mediumblob NOT NULL,
  `doktype` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `url` varchar(255) NOT NULL DEFAULT '',
  `urltype` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `shortcut` int(10) unsigned NOT NULL DEFAULT '0',
  `shortcut_mode` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `t3ver_oid` (`t3ver_oid`,`t3ver_wsid`),
  KEY `parent` (`pid`,`sys_language_uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `static_tsconfig_help`
--

DROP TABLE IF EXISTS `static_tsconfig_help`;
CREATE TABLE IF NOT EXISTS `static_tsconfig_help` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `guide` int(11) NOT NULL DEFAULT '0',
  `md5hash` varchar(32) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `obj_string` varchar(255) DEFAULT '',
  `appdata` blob NOT NULL,
  `title` varchar(255) DEFAULT '',
  PRIMARY KEY (`uid`),
  KEY `guide` (`guide`,`md5hash`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=118 ;

-- --------------------------------------------------------

--
-- Table structure for table `sys_be_shortcuts`
--

DROP TABLE IF EXISTS `sys_be_shortcuts`;
CREATE TABLE IF NOT EXISTS `sys_be_shortcuts` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) unsigned NOT NULL DEFAULT '0',
  `module_name` varchar(255) DEFAULT '',
  `url` text NOT NULL,
  `description` varchar(255) DEFAULT '',
  `sorting` int(11) NOT NULL DEFAULT '0',
  `sc_group` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `event` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=42 ;

-- --------------------------------------------------------

--
-- Table structure for table `sys_domain`
--

DROP TABLE IF EXISTS `sys_domain`;
CREATE TABLE IF NOT EXISTS `sys_domain` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  `tstamp` int(11) unsigned NOT NULL DEFAULT '0',
  `hidden` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `domainName` varchar(80) NOT NULL DEFAULT '',
  `redirectTo` varchar(120) NOT NULL DEFAULT '',
  `sorting` int(10) unsigned NOT NULL DEFAULT '0',
  `prepend_params` int(10) NOT NULL DEFAULT '0',
  `crdate` int(11) unsigned NOT NULL DEFAULT '0',
  `cruser_id` int(11) unsigned NOT NULL DEFAULT '0',
  `redirectHttpStatusCode` int(4) unsigned NOT NULL DEFAULT '301',
  `forced` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `sys_filemounts`
--

DROP TABLE IF EXISTS `sys_filemounts`;
CREATE TABLE IF NOT EXISTS `sys_filemounts` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  `tstamp` int(11) unsigned NOT NULL DEFAULT '0',
  `title` varchar(30) NOT NULL DEFAULT '',
  `path` varchar(120) NOT NULL DEFAULT '',
  `base` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `hidden` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `deleted` tinyint(1) unsigned DEFAULT '0',
  `sorting` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Table structure for table `sys_history`
--

DROP TABLE IF EXISTS `sys_history`;
CREATE TABLE IF NOT EXISTS `sys_history` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sys_log_uid` int(11) NOT NULL DEFAULT '0',
  `history_data` mediumtext,
  `fieldlist` text,
  `recuid` int(11) NOT NULL DEFAULT '0',
  `tablename` varchar(255) DEFAULT '',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `history_files` mediumtext,
  `snapshot` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `sys_log_uid` (`sys_log_uid`),
  KEY `recordident_1` (`tablename`,`recuid`),
  KEY `recordident_2` (`tablename`,`tstamp`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1006623 ;

-- --------------------------------------------------------

--
-- Table structure for table `sys_language`
--

DROP TABLE IF EXISTS `sys_language`;
CREATE TABLE IF NOT EXISTS `sys_language` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  `tstamp` int(11) unsigned NOT NULL DEFAULT '0',
  `hidden` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `title` varchar(80) NOT NULL DEFAULT '',
  `flag` varchar(20) NOT NULL DEFAULT '',
  `static_lang_isocode` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `sys_lockedrecords`
--

DROP TABLE IF EXISTS `sys_lockedrecords`;
CREATE TABLE IF NOT EXISTS `sys_lockedrecords` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) unsigned NOT NULL DEFAULT '0',
  `tstamp` int(11) unsigned NOT NULL DEFAULT '0',
  `record_table` varchar(255) DEFAULT '',
  `record_uid` int(11) NOT NULL DEFAULT '0',
  `record_pid` int(11) NOT NULL DEFAULT '0',
  `username` varchar(50) DEFAULT '',
  `feuserid` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `event` (`userid`,`tstamp`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1515738 ;

-- --------------------------------------------------------

--
-- Table structure for table `sys_log`
--

DROP TABLE IF EXISTS `sys_log`;
CREATE TABLE IF NOT EXISTS `sys_log` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) unsigned NOT NULL DEFAULT '0',
  `action` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `recuid` int(11) unsigned NOT NULL DEFAULT '0',
  `tablename` varchar(255) DEFAULT '',
  `recpid` int(11) NOT NULL DEFAULT '0',
  `error` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `details` text,
  `tstamp` int(11) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `details_nr` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `IP` varchar(39) NOT NULL DEFAULT '',
  `log_data` varchar(255) DEFAULT '',
  `event_pid` int(11) NOT NULL DEFAULT '-1',
  `workspace` int(11) NOT NULL DEFAULT '0',
  `NEWid` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`uid`),
  KEY `event` (`userid`,`event_pid`),
  KEY `recuidIdx` (`recuid`,`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1578260 ;

-- --------------------------------------------------------

--
-- Table structure for table `sys_news`
--

DROP TABLE IF EXISTS `sys_news`;
CREATE TABLE IF NOT EXISTS `sys_news` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  `tstamp` int(11) unsigned NOT NULL DEFAULT '0',
  `crdate` int(11) unsigned NOT NULL DEFAULT '0',
  `cruser_id` int(11) unsigned NOT NULL DEFAULT '0',
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `hidden` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `starttime` int(11) unsigned NOT NULL DEFAULT '0',
  `endtime` int(11) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `content` mediumtext,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `sys_note`
--

DROP TABLE IF EXISTS `sys_note`;
CREATE TABLE IF NOT EXISTS `sys_note` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `tstamp` int(11) unsigned NOT NULL DEFAULT '0',
  `crdate` int(11) unsigned NOT NULL DEFAULT '0',
  `cruser` int(11) unsigned NOT NULL DEFAULT '0',
  `author` varchar(80) NOT NULL DEFAULT '',
  `email` varchar(80) NOT NULL DEFAULT '',
  `subject` varchar(255) DEFAULT '',
  `message` text NOT NULL,
  `personal` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `category` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `sys_preview`
--

DROP TABLE IF EXISTS `sys_preview`;
CREATE TABLE IF NOT EXISTS `sys_preview` (
  `keyword` varchar(32) NOT NULL DEFAULT '',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `config` text NOT NULL,
  PRIMARY KEY (`keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sys_refindex`
--

DROP TABLE IF EXISTS `sys_refindex`;
CREATE TABLE IF NOT EXISTS `sys_refindex` (
  `hash` varchar(32) NOT NULL DEFAULT '',
  `tablename` varchar(255) DEFAULT '',
  `recuid` int(11) NOT NULL DEFAULT '0',
  `field` varchar(40) NOT NULL DEFAULT '',
  `flexpointer` varchar(255) DEFAULT '',
  `softref_key` varchar(30) NOT NULL DEFAULT '',
  `softref_id` varchar(40) NOT NULL DEFAULT '',
  `sorting` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  `ref_table` varchar(255) DEFAULT '',
  `ref_uid` int(11) NOT NULL DEFAULT '0',
  `ref_string` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`hash`),
  KEY `lookup_rec` (`tablename`,`recuid`),
  KEY `lookup_uid` (`ref_table`,`ref_uid`),
  KEY `lookup_string` (`ref_string`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sys_registry`
--

DROP TABLE IF EXISTS `sys_registry`;
CREATE TABLE IF NOT EXISTS `sys_registry` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `entry_namespace` varchar(128) NOT NULL DEFAULT '',
  `entry_key` varchar(128) NOT NULL DEFAULT '',
  `entry_value` blob,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `entry_identifier` (`entry_namespace`,`entry_key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `sys_template`
--

DROP TABLE IF EXISTS `sys_template`;
CREATE TABLE IF NOT EXISTS `sys_template` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `t3ver_oid` int(11) NOT NULL DEFAULT '0',
  `t3ver_id` int(11) NOT NULL DEFAULT '0',
  `t3ver_wsid` int(11) NOT NULL DEFAULT '0',
  `t3ver_label` varchar(255) DEFAULT '',
  `t3ver_state` tinyint(4) NOT NULL DEFAULT '0',
  `t3ver_stage` int(11) DEFAULT '0',
  `t3ver_count` int(11) NOT NULL DEFAULT '0',
  `t3ver_tstamp` int(11) NOT NULL DEFAULT '0',
  `t3_origuid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) unsigned NOT NULL DEFAULT '0',
  `sorting` int(11) unsigned NOT NULL DEFAULT '0',
  `crdate` int(11) unsigned NOT NULL DEFAULT '0',
  `cruser_id` int(11) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) DEFAULT '',
  `sitetitle` varchar(255) DEFAULT '',
  `hidden` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `starttime` int(11) unsigned NOT NULL DEFAULT '0',
  `endtime` int(11) unsigned NOT NULL DEFAULT '0',
  `root` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `clear` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `include_static_file` text,
  `constants` text,
  `config` text,
  `editorcfg` text,
  `resources` text,
  `nextLevel` varchar(5) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `basedOn` tinytext,
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `includeStaticAfterBasedOn` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `static_file_mode` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `tx_impexp_origuid` int(11) NOT NULL DEFAULT '0',
  `tx_oelib_is_dummy_record` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `t3ver_oid` (`t3ver_oid`,`t3ver_wsid`),
  KEY `parent` (`pid`,`sorting`,`deleted`,`hidden`),
  KEY `dummy` (`tx_oelib_is_dummy_record`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=491 ;

-- --------------------------------------------------------

--
-- Table structure for table `sys_ter`
--

DROP TABLE IF EXISTS `sys_ter`;
CREATE TABLE IF NOT EXISTS `sys_ter` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL DEFAULT '',
  `description` mediumtext,
  `wsdl_url` varchar(100) NOT NULL DEFAULT '',
  `mirror_url` varchar(100) NOT NULL DEFAULT '',
  `lastUpdated` int(11) unsigned NOT NULL DEFAULT '0',
  `extCount` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `tt_content`
--

DROP TABLE IF EXISTS `tt_content`;
CREATE TABLE IF NOT EXISTS `tt_content` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `t3ver_oid` int(11) NOT NULL DEFAULT '0',
  `t3ver_id` int(11) NOT NULL DEFAULT '0',
  `t3ver_wsid` int(11) NOT NULL DEFAULT '0',
  `t3ver_label` varchar(255) DEFAULT '',
  `t3ver_state` tinyint(4) NOT NULL DEFAULT '0',
  `t3ver_stage` int(11) DEFAULT '0',
  `t3ver_count` int(11) NOT NULL DEFAULT '0',
  `t3ver_tstamp` int(11) NOT NULL DEFAULT '0',
  `t3_origuid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) unsigned NOT NULL DEFAULT '0',
  `hidden` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `sorting` int(11) unsigned NOT NULL DEFAULT '0',
  `CType` varchar(30) NOT NULL DEFAULT '',
  `header` varchar(255) DEFAULT '',
  `header_position` varchar(6) NOT NULL DEFAULT '',
  `bodytext` mediumtext NOT NULL,
  `image` text,
  `imagewidth` mediumint(11) unsigned NOT NULL DEFAULT '0',
  `imageorient` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `imagecaption` text NOT NULL,
  `imagecols` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `imageborder` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `media` text,
  `layout` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `deleted` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `cols` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `records` text,
  `pages` tinytext,
  `starttime` int(11) unsigned NOT NULL DEFAULT '0',
  `endtime` int(11) unsigned NOT NULL DEFAULT '0',
  `colPos` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `subheader` varchar(255) DEFAULT '',
  `spaceBefore` smallint(5) unsigned DEFAULT '0',
  `spaceAfter` smallint(5) unsigned DEFAULT '0',
  `fe_group` varchar(100) NOT NULL DEFAULT '0',
  `header_link` varchar(255) DEFAULT '',
  `imagecaption_position` varchar(6) NOT NULL DEFAULT '',
  `image_link` text,
  `image_zoom` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `image_noRows` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `image_effects` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `image_compression` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `altText` text NOT NULL,
  `titleText` text NOT NULL,
  `longdescURL` text NOT NULL,
  `header_layout` varchar(30) NOT NULL DEFAULT '0',
  `text_align` varchar(6) NOT NULL DEFAULT '',
  `text_face` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `text_size` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `text_color` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `text_properties` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `menu_type` varchar(30) NOT NULL DEFAULT '0',
  `list_type` varchar(36) NOT NULL DEFAULT '0',
  `table_border` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `table_cellspacing` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `table_cellpadding` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `table_bgColor` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `select_key` varchar(80) NOT NULL DEFAULT '',
  `sectionIndex` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `linkToTop` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `filelink_size` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `section_frame` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `date` int(10) unsigned NOT NULL DEFAULT '0',
  `splash_layout` varchar(30) NOT NULL DEFAULT '0',
  `multimedia` tinytext,
  `image_frames` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `recursive` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `imageheight` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rte_enabled` tinyint(4) NOT NULL DEFAULT '0',
  `sys_language_uid` int(11) NOT NULL DEFAULT '0',
  `tx_impexp_origuid` int(11) NOT NULL DEFAULT '0',
  `pi_flexform` mediumtext NOT NULL,
  `l18n_parent` int(11) NOT NULL DEFAULT '0',
  `l18n_diffsource` mediumblob NOT NULL,
  `tx_gooffotoboek_function` varchar(7) DEFAULT '',
  `tx_gooffotoboek_path` mediumtext,
  `tx_gooffotoboek_webpath` mediumtext,
  `tx_srincludepages_pages` tinyblob NOT NULL,
  `tx_srincludepages_column` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `tx_pagephpcontent_php_code` text NOT NULL,
  `tx_pagephpcontent_include_path` text NOT NULL,
  `t3ver_move_id` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) unsigned NOT NULL DEFAULT '0',
  `cruser_id` int(11) unsigned NOT NULL DEFAULT '0',
  `tx_newspaper_extra` tinytext NOT NULL,
  `tx_oelib_is_dummy_record` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `t3ver_oid` (`t3ver_oid`,`t3ver_wsid`),
  KEY `parent` (`pid`,`sorting`),
  KEY `language` (`l18n_parent`,`sys_language_uid`),
  KEY `dummy` (`tx_oelib_is_dummy_record`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=22981 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_devlog`
--

DROP TABLE IF EXISTS `tx_devlog`;
CREATE TABLE IF NOT EXISTS `tx_devlog` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  `crdate` int(11) unsigned NOT NULL DEFAULT '0',
  `crmsec` bigint(11) unsigned NOT NULL DEFAULT '0',
  `cruser_id` int(11) unsigned NOT NULL DEFAULT '0',
  `severity` int(11) NOT NULL DEFAULT '0',
  `extkey` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `msg` text COLLATE utf8_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `line` int(11) NOT NULL DEFAULT '0',
  `data_var` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `crdate` (`crdate`),
  KEY `crmsec` (`crmsec`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=180 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_extbase_cache_object`
--

DROP TABLE IF EXISTS `tx_extbase_cache_object`;
CREATE TABLE IF NOT EXISTS `tx_extbase_cache_object` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(250) NOT NULL DEFAULT '',
  `crdate` int(11) unsigned NOT NULL DEFAULT '0',
  `content` mediumtext,
  `tags` mediumtext,
  `lifetime` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `cache_id` (`identifier`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1009 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_extbase_cache_object_tags`
--

DROP TABLE IF EXISTS `tx_extbase_cache_object_tags`;
CREATE TABLE IF NOT EXISTS `tx_extbase_cache_object_tags` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(128) NOT NULL DEFAULT '',
  `tag` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `cache_id` (`identifier`),
  KEY `cache_tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_extbase_cache_reflection`
--

DROP TABLE IF EXISTS `tx_extbase_cache_reflection`;
CREATE TABLE IF NOT EXISTS `tx_extbase_cache_reflection` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(250) NOT NULL DEFAULT '',
  `crdate` int(11) unsigned NOT NULL DEFAULT '0',
  `content` mediumtext,
  `tags` mediumtext,
  `lifetime` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `cache_id` (`identifier`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=30 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_extbase_cache_reflection_tags`
--

DROP TABLE IF EXISTS `tx_extbase_cache_reflection_tags`;
CREATE TABLE IF NOT EXISTS `tx_extbase_cache_reflection_tags` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(128) NOT NULL DEFAULT '',
  `tag` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `cache_id` (`identifier`),
  KEY `cache_tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_impexp_presets`
--

DROP TABLE IF EXISTS `tx_impexp_presets`;
CREATE TABLE IF NOT EXISTS `tx_impexp_presets` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `user_uid` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) DEFAULT '',
  `public` tinyint(3) NOT NULL DEFAULT '0',
  `item_uid` int(11) NOT NULL DEFAULT '0',
  `preset_data` blob NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `lookup` (`item_uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaperblaettertaz_extra_article`
--

DROP TABLE IF EXISTS `tx_newspaperblaettertaz_extra_article`;
CREATE TABLE IF NOT EXISTS `tx_newspaperblaettertaz_extra_article` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `short_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `template` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaperblaettertaz_extra_list`
--

DROP TABLE IF EXISTS `tx_newspaperblaettertaz_extra_list`;
CREATE TABLE IF NOT EXISTS `tx_newspaperblaettertaz_extra_list` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `short_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `template` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `is_menu` tinyint(4) NOT NULL DEFAULT '0',
  `show_tom` tinyint(4) NOT NULL DEFAULT '0',
  `show_letters` tinyint(4) NOT NULL DEFAULT '0',
  `current_day_only` tinyint(4) NOT NULL DEFAULT '0',
  `start_year` int(11) NOT NULL DEFAULT '0',
  `start_month` int(11) NOT NULL DEFAULT '0',
  `end_year` int(11) NOT NULL DEFAULT '0',
  `end_month` int(11) NOT NULL DEFAULT '0',
  `days_back` int(11) NOT NULL DEFAULT '0',
  `show_teaser` tinyint(4) NOT NULL DEFAULT '0',
  `show_taz` tinyint(4) NOT NULL DEFAULT '0',
  `show_tazb` tinyint(4) NOT NULL DEFAULT '0',
  `show_tazn` tinyint(4) NOT NULL DEFAULT '0',
  `show_tazm` tinyint(4) NOT NULL DEFAULT '0',
  `show_monde` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspapermigration_logging`
--

DROP TABLE IF EXISTS `tx_newspapermigration_logging`;
CREATE TABLE IF NOT EXISTS `tx_newspapermigration_logging` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `hidden` tinyint(4) NOT NULL DEFAULT '0',
  `type` tinytext,
  `source` tinytext,
  `message` text,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspapertaz_extra_adr`
--

DROP TABLE IF EXISTS `tx_newspapertaz_extra_adr`;
CREATE TABLE IF NOT EXISTS `tx_newspapertaz_extra_adr` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `included_sections` text COLLATE utf8_unicode_ci,
  `excluded_sections` text COLLATE utf8_unicode_ci,
  `subsection` tinyint(3) NOT NULL DEFAULT '0',
  `show_subsection_menu` tinyint(3) NOT NULL DEFAULT '0',
  `exclude_from_subsection_menu` text COLLATE utf8_unicode_ci,
  `num_articles_per_section` int(11) NOT NULL DEFAULT '0',
  `num_images` int(11) NOT NULL DEFAULT '0',
  `num_teasers` int(11) NOT NULL DEFAULT '0',
  `template` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `short_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspapertaz_extra_headlines`
--

DROP TABLE IF EXISTS `tx_newspapertaz_extra_headlines`;
CREATE TABLE IF NOT EXISTS `tx_newspapertaz_extra_headlines` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `num_articles` int(11) NOT NULL DEFAULT '0',
  `first_article` int(11) NOT NULL DEFAULT '0',
  `section` int(11) DEFAULT '0',
  `short_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `template` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `section` (`section`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspapertaz_extra_poll`
--

DROP TABLE IF EXISTS `tx_newspapertaz_extra_poll`;
CREATE TABLE IF NOT EXISTS `tx_newspapertaz_extra_poll` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `short_description` tinytext NOT NULL,
  `poll` int(11) NOT NULL DEFAULT '0',
  `template` tinytext NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspapertaz_extra_sectionmeta`
--

DROP TABLE IF EXISTS `tx_newspapertaz_extra_sectionmeta`;
CREATE TABLE IF NOT EXISTS `tx_newspapertaz_extra_sectionmeta` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `template` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `short_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspapertaz_extra_tagselect`
--

DROP TABLE IF EXISTS `tx_newspapertaz_extra_tagselect`;
CREATE TABLE IF NOT EXISTS `tx_newspapertaz_extra_tagselect` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `template` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `ctrltag_cat` int(11) NOT NULL DEFAULT '0',
  `short_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `ctrltag_cat` (`ctrltag_cat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspapertaz_extra_tomkari`
--

DROP TABLE IF EXISTS `tx_newspapertaz_extra_tomkari`;
CREATE TABLE IF NOT EXISTS `tx_newspapertaz_extra_tomkari` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `template` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `image_file1` blob NOT NULL,
  `image_file2` blob NOT NULL,
  `short_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `starttime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspapertaz_np2taz`
--

DROP TABLE IF EXISTS `tx_newspapertaz_np2taz`;
CREATE TABLE IF NOT EXISTS `tx_newspapertaz_np2taz` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `newspaper_uid` int(11) NOT NULL DEFAULT '0',
  `taz_uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `newspaper_uid` (`newspaper_uid`),
  KEY `taz_uid` (`taz_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspapertaz_poll`
--

DROP TABLE IF EXISTS `tx_newspapertaz_poll`;
CREATE TABLE IF NOT EXISTS `tx_newspapertaz_poll` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `hidden` tinyint(4) NOT NULL DEFAULT '0',
  `title` tinytext NOT NULL,
  `question` text NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_article`
--

DROP TABLE IF EXISTS `tx_newspaper_article`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_article` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `hidden` tinyint(4) NOT NULL DEFAULT '0',
  `starttime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `articletype_id` int(11) NOT NULL DEFAULT '0',
  `publish_date` int(11) NOT NULL DEFAULT '0',
  `title` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `title_list` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `kicker` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `kicker_list` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `teaser` text COLLATE utf8_unicode_ci NOT NULL,
  `teaser_list` text COLLATE utf8_unicode_ci NOT NULL,
  `author` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `source_id` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `source_object` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `extras` int(11) NOT NULL DEFAULT '0',
  `sections` int(11) NOT NULL DEFAULT '0',
  `name` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `is_template` tinyint(3) NOT NULL DEFAULT '0',
  `template_set` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `pagezonetype_id` int(11) NOT NULL DEFAULT '0',
  `inherits_from` int(11) NOT NULL DEFAULT '0',
  `workflow_status` int(11) NOT NULL DEFAULT '0',
  `modification_user` text COLLATE utf8_unicode_ci,
  `tags` int(11) NOT NULL DEFAULT '0',
  `related` int(11) NOT NULL DEFAULT '0',
  `no_rte` tinyint(3) NOT NULL DEFAULT '0',
  `tx_newspapertaz_kicker2` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `tx_newspapertaz_unique_key` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `tx_newspapertaz_alternative_url` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `bodytext` longtext COLLATE utf8_unicode_ci NOT NULL,
  `tx_newspapertaz_vgwort_public_id` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `url` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `tx_newspapertaz_style` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `tx_newspapertaz_unique_key` (`tx_newspapertaz_unique_key`(30)),
  KEY `articletype_id` (`articletype_id`),
  FULLTEXT KEY `title` (`title`,`kicker`,`title_list`,`kicker_list`),
  FULLTEXT KEY `text` (`teaser`,`teaser_list`,`bodytext`,`author`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=95753 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_articlelist`
--

DROP TABLE IF EXISTS `tx_newspaper_articlelist`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_articlelist` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `sorting` int(10) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `hidden` tinyint(4) NOT NULL DEFAULT '0',
  `starttime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `notes` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `list_table` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `list_uid` int(11) NOT NULL DEFAULT '0',
  `section_id` int(11) DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `section_id` (`section_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=312 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_articlelist_manual`
--

DROP TABLE IF EXISTS `tx_newspaper_articlelist_manual`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_articlelist_manual` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `articles` int(11) NOT NULL DEFAULT '0',
  `num_articles` int(11) NOT NULL DEFAULT '0',
  `filter_sections` text COLLATE utf8_unicode_ci,
  `filter_tags_include` text COLLATE utf8_unicode_ci,
  `filter_tags_exclude` text COLLATE utf8_unicode_ci,
  `filter_articlelist_exclude` text COLLATE utf8_unicode_ci,
  `filter_sql_table` text COLLATE utf8_unicode_ci NOT NULL,
  `filter_sql_where` text COLLATE utf8_unicode_ci NOT NULL,
  `filter_sql_order_by` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=43 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_articlelist_manual_articles_mm`
--

DROP TABLE IF EXISTS `tx_newspaper_articlelist_manual_articles_mm`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_articlelist_manual_articles_mm` (
  `uid_local` int(11) NOT NULL DEFAULT '0',
  `uid_foreign` int(11) NOT NULL DEFAULT '0',
  `tablenames` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `sorting` int(11) NOT NULL DEFAULT '0',
  KEY `uid_local` (`uid_local`),
  KEY `uid_foreign` (`uid_foreign`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_articlelist_semiautomatic`
--

DROP TABLE IF EXISTS `tx_newspaper_articlelist_semiautomatic`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_articlelist_semiautomatic` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `articles` int(11) NOT NULL DEFAULT '0',
  `num_articles` int(11) NOT NULL DEFAULT '0',
  `filter_sections` text COLLATE utf8_unicode_ci,
  `filter_tags_include` text COLLATE utf8_unicode_ci,
  `filter_tags_exclude` text COLLATE utf8_unicode_ci,
  `filter_articlelist_exclude` text COLLATE utf8_unicode_ci,
  `filter_sql_table` text COLLATE utf8_unicode_ci NOT NULL,
  `filter_sql_where` text COLLATE utf8_unicode_ci NOT NULL,
  `filter_sql_order_by` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `subsequent_sections` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=151 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_articlelist_semiautomatic_articles_mm`
--

DROP TABLE IF EXISTS `tx_newspaper_articlelist_semiautomatic_articles_mm`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_articlelist_semiautomatic_articles_mm` (
  `uid_local` int(11) NOT NULL DEFAULT '0',
  `uid_foreign` int(11) NOT NULL DEFAULT '0',
  `tablenames` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `sorting` int(11) NOT NULL DEFAULT '0',
  `offset` int(11) NOT NULL DEFAULT '0',
  KEY `uid_local` (`uid_local`),
  KEY `uid_foreign` (`uid_foreign`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_articletype`
--

DROP TABLE IF EXISTS `tx_newspaper_articletype`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_articletype` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `sorting` int(10) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `title` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `normalized_name` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_article_extras_mm`
--

DROP TABLE IF EXISTS `tx_newspaper_article_extras_mm`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_article_extras_mm` (
  `uid_local` int(11) NOT NULL DEFAULT '0',
  `uid_foreign` int(11) NOT NULL DEFAULT '0',
  `tablenames` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `sorting` int(11) NOT NULL DEFAULT '0',
  KEY `uid_local` (`uid_local`),
  KEY `uid_foreign` (`uid_foreign`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_article_related_mm`
--

DROP TABLE IF EXISTS `tx_newspaper_article_related_mm`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_article_related_mm` (
  `uid_local` int(11) NOT NULL DEFAULT '0',
  `uid_foreign` int(11) NOT NULL DEFAULT '0',
  `tablenames` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `sorting` int(11) NOT NULL DEFAULT '0',
  KEY `uid_local` (`uid_local`),
  KEY `uid_foreign` (`uid_foreign`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_article_sections_mm`
--

DROP TABLE IF EXISTS `tx_newspaper_article_sections_mm`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_article_sections_mm` (
  `uid_local` int(11) NOT NULL DEFAULT '0',
  `uid_foreign` int(11) NOT NULL DEFAULT '0',
  `tablenames` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `sorting` int(11) NOT NULL DEFAULT '0',
  KEY `uid_local` (`uid_local`),
  KEY `uid_foreign` (`uid_foreign`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_article_tags_mm`
--

DROP TABLE IF EXISTS `tx_newspaper_article_tags_mm`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_article_tags_mm` (
  `uid_local` int(11) NOT NULL DEFAULT '0',
  `uid_foreign` int(11) NOT NULL DEFAULT '0',
  `tablenames` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `sorting` int(11) NOT NULL DEFAULT '0',
  KEY `uid_local` (`uid_local`),
  KEY `uid_foreign` (`uid_foreign`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_comment_cache`
--

DROP TABLE IF EXISTS `tx_newspaper_comment_cache`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_comment_cache` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `hidden` tinyint(4) NOT NULL DEFAULT '0',
  `article` text COLLATE utf8_unicode_ci,
  `kicker` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `title` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `author` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_controltag_to_extra`
--

DROP TABLE IF EXISTS `tx_newspaper_controltag_to_extra`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_controltag_to_extra` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `tag` int(11) DEFAULT '0',
  `tag_zone` int(11) DEFAULT '0',
  `sorting` int(10) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `extra` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `tag` (`tag`),
  KEY `tag_zone` (`tag_zone`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=374 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_ctrltag_category`
--

DROP TABLE IF EXISTS `tx_newspaper_ctrltag_category`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_ctrltag_category` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `sorting` int(10) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `title` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_externallinks`
--

DROP TABLE IF EXISTS `tx_newspaper_externallinks`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_externallinks` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `starttime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `url` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1898 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_extra`
--

DROP TABLE IF EXISTS `tx_newspaper_extra`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_extra` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `hidden` tinyint(4) NOT NULL DEFAULT '0',
  `starttime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `extra_table` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `extra_uid` int(11) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  `paragraph` int(11) NOT NULL DEFAULT '0',
  `origin_uid` int(11) NOT NULL DEFAULT '0',
  `is_inheritable` tinyint(3) NOT NULL DEFAULT '0',
  `show_extra` tinyint(3) NOT NULL DEFAULT '0',
  `gui_hidden` tinyint(3) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8_unicode_ci NOT NULL,
  `template_set` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `origin_uid` (`origin_uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=383423 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_extra_ad`
--

DROP TABLE IF EXISTS `tx_newspaper_extra_ad`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_extra_ad` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `hidden` tinyint(4) NOT NULL DEFAULT '0',
  `starttime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `template` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `short_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=102011 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_extra_articlelist`
--

DROP TABLE IF EXISTS `tx_newspaper_extra_articlelist`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_extra_articlelist` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `starttime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `articlelist` int(11) DEFAULT '0',
  `first_article` int(11) NOT NULL DEFAULT '0',
  `num_articles` int(11) NOT NULL DEFAULT '0',
  `template` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `header` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `image` text COLLATE utf8_unicode_ci,
  `short_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `articlelist` (`articlelist`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_extra_bio`
--

DROP TABLE IF EXISTS `tx_newspaper_extra_bio`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_extra_bio` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `hidden` tinyint(4) NOT NULL DEFAULT '0',
  `starttime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `template_set` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `pool` tinyint(3) NOT NULL DEFAULT '0',
  `author_name` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `is_author` tinyint(3) NOT NULL DEFAULT '0',
  `author_id` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `image_file` text COLLATE utf8_unicode_ci,
  `photo_source` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `bio_text` text COLLATE utf8_unicode_ci NOT NULL,
  `short_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `template` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  FULLTEXT KEY `title` (`author_name`,`bio_text`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=12474 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_extra_combolinkbox`
--

DROP TABLE IF EXISTS `tx_newspaper_extra_combolinkbox`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_extra_combolinkbox` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `hidden` tinyint(4) NOT NULL DEFAULT '0',
  `starttime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `show_related_articles` tinyint(3) NOT NULL DEFAULT '0',
  `manually_selected_articles` text COLLATE utf8_unicode_ci,
  `internal_links` text COLLATE utf8_unicode_ci,
  `external_links` text COLLATE utf8_unicode_ci,
  `short_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `template` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=46704 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_extra_container`
--

DROP TABLE IF EXISTS `tx_newspaper_extra_container`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_extra_container` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `hidden` tinyint(4) NOT NULL DEFAULT '0',
  `starttime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `extras` text COLLATE utf8_unicode_ci,
  `template` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `short_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=86 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_extra_controltagzone`
--

DROP TABLE IF EXISTS `tx_newspaper_extra_controltagzone`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_extra_controltagzone` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `hidden` tinyint(4) NOT NULL DEFAULT '0',
  `starttime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `tag_zone` int(11) DEFAULT '0',
  `default_extra` text COLLATE utf8_unicode_ci,
  `short_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `template` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `tag_zone` (`tag_zone`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_extra_displayarticles`
--

DROP TABLE IF EXISTS `tx_newspaper_extra_displayarticles`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_extra_displayarticles` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `starttime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `short_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `template` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_extra_externallinks`
--

DROP TABLE IF EXISTS `tx_newspaper_extra_externallinks`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_extra_externallinks` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `starttime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `pool` tinyint(3) NOT NULL DEFAULT '0',
  `title` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `links` text COLLATE utf8_unicode_ci,
  `template` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `short_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_extra_freeformimage`
--

DROP TABLE IF EXISTS `tx_newspaper_extra_freeformimage`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_extra_freeformimage` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `hidden` tinyint(4) NOT NULL DEFAULT '0',
  `image_file` blob NOT NULL,
  `image_width` int(11) NOT NULL DEFAULT '0',
  `image_height` int(11) NOT NULL DEFAULT '0',
  `short_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `template` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=235 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_extra_generic`
--

DROP TABLE IF EXISTS `tx_newspaper_extra_generic`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_extra_generic` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `hidden` tinyint(4) NOT NULL DEFAULT '0',
  `starttime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `template` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `short_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=180 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_extra_html`
--

DROP TABLE IF EXISTS `tx_newspaper_extra_html`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_extra_html` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `html` text COLLATE utf8_unicode_ci NOT NULL,
  `template` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `short_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=309 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_extra_image`
--

DROP TABLE IF EXISTS `tx_newspaper_extra_image`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_extra_image` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `hidden` tinyint(4) NOT NULL DEFAULT '0',
  `starttime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `pool` tinyint(3) NOT NULL DEFAULT '0',
  `title` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `image_file` text COLLATE utf8_unicode_ci,
  `caption` text COLLATE utf8_unicode_ci,
  `normalized_filename` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `kicker` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `credit` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `source` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `alttext` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `tags` int(11) NOT NULL DEFAULT '0',
  `image_type` int(11) NOT NULL DEFAULT '0',
  `short_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `image_url` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `template` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `width_set` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  FULLTEXT KEY `title` (`title`,`kicker`,`caption`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=88318 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_extra_image_tags_mm`
--

DROP TABLE IF EXISTS `tx_newspaper_extra_image_tags_mm`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_extra_image_tags_mm` (
  `uid_local` int(11) NOT NULL DEFAULT '0',
  `uid_foreign` int(11) NOT NULL DEFAULT '0',
  `tablenames` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `sorting` int(11) NOT NULL DEFAULT '0',
  KEY `uid_local` (`uid_local`),
  KEY `uid_foreign` (`uid_foreign`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_extra_mostcommented`
--

DROP TABLE IF EXISTS `tx_newspaper_extra_mostcommented`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_extra_mostcommented` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `hidden` tinyint(4) NOT NULL DEFAULT '0',
  `starttime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `short_description` tinytext NOT NULL,
  `hours` int(11) NOT NULL DEFAULT '0',
  `num_favorites` int(11) NOT NULL DEFAULT '0',
  `display_num` tinyint(3) NOT NULL DEFAULT '0',
  `display_time` tinyint(3) NOT NULL DEFAULT '0',
  `template` tinytext NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_extra_searchresults`
--

DROP TABLE IF EXISTS `tx_newspaper_extra_searchresults`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_extra_searchresults` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `starttime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `sections` text COLLATE utf8_unicode_ci,
  `search_term` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `tags` text COLLATE utf8_unicode_ci,
  `short_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `template` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_extra_sectionlist`
--

DROP TABLE IF EXISTS `tx_newspaper_extra_sectionlist`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_extra_sectionlist` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `sorting` int(10) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `hidden` tinyint(4) NOT NULL DEFAULT '0',
  `starttime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `first_article` int(11) NOT NULL DEFAULT '0',
  `num_articles` int(11) NOT NULL DEFAULT '0',
  `short_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `template` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_extra_sectionteaser`
--

DROP TABLE IF EXISTS `tx_newspaper_extra_sectionteaser`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_extra_sectionteaser` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `template` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `description_text` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `is_ctrltag` int(11) NOT NULL DEFAULT '0',
  `section` int(11) NOT NULL DEFAULT '0',
  `ctrltag_cat` int(11) NOT NULL DEFAULT '0',
  `ctrltag` int(11) NOT NULL DEFAULT '0',
  `num_articles` int(11) NOT NULL DEFAULT '0',
  `num_articles_w_image` int(11) NOT NULL DEFAULT '0',
  `short_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `ctrltag` (`ctrltag`),
  KEY `section` (`section`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=253 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_extra_specialhits`
--

DROP TABLE IF EXISTS `tx_newspaper_extra_specialhits`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_extra_specialhits` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `hidden` tinyint(4) NOT NULL DEFAULT '0',
  `short_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `template` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_extra_textbox`
--

DROP TABLE IF EXISTS `tx_newspaper_extra_textbox`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_extra_textbox` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `starttime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `pool` tinyint(3) NOT NULL DEFAULT '0',
  `title` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `image` text COLLATE utf8_unicode_ci,
  `bodytext` text COLLATE utf8_unicode_ci NOT NULL,
  `short_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `template` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `image_file` blob NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  FULLTEXT KEY `title` (`title`,`bodytext`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=14163 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_extra_typo3_ce`
--

DROP TABLE IF EXISTS `tx_newspaper_extra_typo3_ce`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_extra_typo3_ce` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `hidden` tinyint(4) NOT NULL DEFAULT '0',
  `starttime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `pool` tinyint(3) NOT NULL DEFAULT '0',
  `content_elements` text COLLATE utf8_unicode_ci,
  `template` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `short_description` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=37 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_log`
--

DROP TABLE IF EXISTS `tx_newspaper_log`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_log` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `table_name` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `table_uid` int(11) NOT NULL DEFAULT '0',
  `be_user` text COLLATE utf8_unicode_ci,
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  `operation` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `details` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `table_key` (`table_name`(30),`table_uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=185785 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_page`
--

DROP TABLE IF EXISTS `tx_newspaper_page`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_page` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `section` int(11) DEFAULT '0',
  `pagetype_id` int(11) DEFAULT '0',
  `inherit_pagetype_id` int(11) NOT NULL DEFAULT '0',
  `template_set` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `inherit_pagetype_id` (`inherit_pagetype_id`),
  KEY `section` (`section`),
  KEY `pagetype_id` (`pagetype_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=411 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_pagetype`
--

DROP TABLE IF EXISTS `tx_newspaper_pagetype`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_pagetype` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `sorting` int(10) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `type_name` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `normalized_name` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `is_article_page` tinyint(3) NOT NULL DEFAULT '0',
  `get_var` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `get_value` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `get_var` (`get_var`(8)),
  KEY `get_value` (`get_value`(20)),
  KEY `get_params` (`get_var`(8),`get_value`(20))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_pagezone`
--

DROP TABLE IF EXISTS `tx_newspaper_pagezone`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_pagezone` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `sorting` int(10) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `name` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `page_id` int(11) DEFAULT '0',
  `pagezone_table` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `pagezone_uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `page_id` (`page_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=107046 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_pagezonetype`
--

DROP TABLE IF EXISTS `tx_newspaper_pagezonetype`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_pagezonetype` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `sorting` int(10) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `type_name` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `normalized_name` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `is_article` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_pagezone_page`
--

DROP TABLE IF EXISTS `tx_newspaper_pagezone_page`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_pagezone_page` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `pagezonetype_id` int(11) NOT NULL DEFAULT '0',
  `pagezone_id` int(11) DEFAULT '0',
  `extras` int(11) NOT NULL DEFAULT '0',
  `template_set` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `inherits_from` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=613 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_pagezone_page_extras_mm`
--

DROP TABLE IF EXISTS `tx_newspaper_pagezone_page_extras_mm`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_pagezone_page_extras_mm` (
  `uid_local` int(11) NOT NULL DEFAULT '0',
  `uid_foreign` int(11) NOT NULL DEFAULT '0',
  `tablenames` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `sorting` int(11) NOT NULL DEFAULT '0',
  KEY `uid_local` (`uid_local`),
  KEY `uid_foreign` (`uid_foreign`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_section`
--

DROP TABLE IF EXISTS `tx_newspaper_section`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_section` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `sorting` int(10) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `section_name` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `parent_section` int(11) NOT NULL DEFAULT '0',
  `default_articletype` int(11) NOT NULL DEFAULT '0',
  `pagetype_pagezone` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `articlelist` int(11) NOT NULL DEFAULT '0',
  `template_set` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `show_in_list` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `parent_section` (`parent_section`),
  KEY `articlelist` (`articlelist`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=171 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_specialhit`
--

DROP TABLE IF EXISTS `tx_newspaper_specialhit`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_specialhit` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `hidden` tinyint(4) NOT NULL DEFAULT '0',
  `title` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `teaser` text COLLATE utf8_unicode_ci NOT NULL,
  `words` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `url` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `sorting` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=62 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_tag`
--

DROP TABLE IF EXISTS `tx_newspaper_tag`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_tag` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `tag` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `tag_type` int(11) DEFAULT '0',
  `title` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `ctrltag_cat` int(11) NOT NULL DEFAULT '0',
  `section` int(11) DEFAULT '0',
  `deactivated` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `ctrltag_cat` (`ctrltag_cat`),
  KEY `section` (`section`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=206 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_tag_type`
--

DROP TABLE IF EXISTS `tx_newspaper_tag_type`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_tag_type` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `name` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_newspaper_tag_zone`
--

DROP TABLE IF EXISTS `tx_newspaper_tag_zone`;
CREATE TABLE IF NOT EXISTS `tx_newspaper_tag_zone` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `hidden` tinyint(4) NOT NULL DEFAULT '0',
  `name` tinytext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_skpagecomments_comments`
--

DROP TABLE IF EXISTS `tx_skpagecomments_comments`;
CREATE TABLE IF NOT EXISTS `tx_skpagecomments_comments` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `t3ver_oid` int(11) NOT NULL DEFAULT '0',
  `t3ver_id` int(11) NOT NULL DEFAULT '0',
  `t3ver_wsid` int(11) NOT NULL DEFAULT '0',
  `t3ver_label` varchar(30) NOT NULL DEFAULT '',
  `t3ver_state` tinyint(4) NOT NULL DEFAULT '0',
  `t3ver_stage` tinyint(4) NOT NULL DEFAULT '0',
  `t3ver_count` int(11) NOT NULL DEFAULT '0',
  `t3ver_tstamp` int(11) NOT NULL DEFAULT '0',
  `t3_origuid` int(11) NOT NULL DEFAULT '0',
  `sys_language_uid` int(11) NOT NULL DEFAULT '0',
  `l18n_parent` int(11) NOT NULL DEFAULT '0',
  `l18n_diffsource` mediumblob NOT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `hidden` tinyint(4) NOT NULL DEFAULT '0',
  `starttime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `fe_group` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `homepage` varchar(255) NOT NULL DEFAULT '',
  `comment` text NOT NULL,
  `pageid` int(11) NOT NULL DEFAULT '0',
  `pivar` tinytext NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `pivar` (`pivar`(10))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=536896 ;

