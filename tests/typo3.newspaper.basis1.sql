-- phpMyAdmin SQL Dump
-- version 2.6.4-pl3
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Erstellungszeit: 09. September 2009 um 19:45
-- Server Version: 5.0.41
-- PHP-Version: 5.2.3
-- 
-- Datenbank: `typo3`
-- 

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `be_groups`
-- 

DROP TABLE IF EXISTS `be_groups`;
CREATE TABLE  `be_groups` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `pid` int(11) unsigned NOT NULL default '0',
  `tstamp` int(11) unsigned NOT NULL default '0',
  `title` varchar(50) NOT NULL default '',
  `non_exclude_fields` text NOT NULL,
  `explicit_allowdeny` text NOT NULL,
  `allowed_languages` varchar(255) default '',
  `custom_options` text NOT NULL,
  `db_mountpoints` varchar(255) NOT NULL default '',
  `pagetypes_select` varchar(255) default '',
  `tables_select` text NOT NULL,
  `tables_modify` text NOT NULL,
  `crdate` int(11) unsigned NOT NULL default '0',
  `cruser_id` int(11) unsigned NOT NULL default '0',
  `groupMods` text NOT NULL,
  `file_mountpoints` varchar(255) NOT NULL default '',
  `hidden` tinyint(1) unsigned NOT NULL default '0',
  `inc_access_lists` tinyint(3) unsigned NOT NULL default '0',
  `description` text NOT NULL,
  `lockToDomain` varchar(50) NOT NULL default '',
  `deleted` tinyint(1) unsigned NOT NULL default '0',
  `TSconfig` text NOT NULL,
  `subgroup` varchar(255) default '',
  `hide_in_lists` tinyint(4) NOT NULL default '0',
  `workspace_perms` tinyint(3) NOT NULL default '1',
  `tx_tinyrte_tinyrte_plugins` blob NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `be_groups`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `be_sessions`
-- 

DROP TABLE IF EXISTS `be_sessions`;
CREATE TABLE  `be_sessions` (
  `ses_id` varchar(32) NOT NULL default '',
  `ses_name` varchar(32) NOT NULL default '',
  `ses_iplock` varchar(39) NOT NULL default '',
  `ses_hashlock` int(11) NOT NULL default '0',
  `ses_userid` int(11) unsigned NOT NULL default '0',
  `ses_tstamp` int(11) unsigned NOT NULL default '0',
  `ses_data` longtext NOT NULL,
  `ses_backuserid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ses_id`,`ses_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `be_users`
-- 

DROP TABLE IF EXISTS `be_users`;
CREATE TABLE  `be_users` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `pid` int(11) unsigned NOT NULL default '0',
  `tstamp` int(11) unsigned NOT NULL default '0',
  `username` varchar(50) NOT NULL default '',
  `password` varchar(40) NOT NULL default '',
  `admin` tinyint(4) unsigned NOT NULL default '0',
  `usergroup` varchar(255) default '',
  `disable` tinyint(1) unsigned NOT NULL default '0',
  `starttime` int(11) unsigned NOT NULL default '0',
  `endtime` int(11) unsigned NOT NULL default '0',
  `lang` char(2) NOT NULL default '',
  `email` varchar(80) NOT NULL default '',
  `db_mountpoints` varchar(255) NOT NULL default '',
  `options` tinyint(4) unsigned NOT NULL default '0',
  `crdate` int(11) unsigned NOT NULL default '0',
  `cruser_id` int(11) unsigned NOT NULL default '0',
  `realName` varchar(80) NOT NULL default '',
  `userMods` varchar(255) default '',
  `allowed_languages` varchar(255) default '',
  `uc` text NOT NULL,
  `file_mountpoints` varchar(255) NOT NULL default '',
  `fileoper_perms` tinyint(4) NOT NULL default '0',
  `workspace_perms` tinyint(3) NOT NULL default '1',
  `lockToDomain` varchar(50) NOT NULL default '',
  `disableIPlock` tinyint(1) unsigned NOT NULL default '0',
  `deleted` tinyint(1) unsigned NOT NULL default '0',
  `TSconfig` text NOT NULL,
  `lastlogin` int(10) unsigned NOT NULL default '0',
  `createdByAction` int(11) NOT NULL default '0',
  `usergroup_cached_list` varchar(255) default '',
  `workspace_id` int(11) NOT NULL default '0',
  `workspace_preview` tinyint(3) NOT NULL default '1',
  `tx_mydashboard_config` text NOT NULL,
  `tx_mydashboard_selfadmin` tinyint(3) NOT NULL default '0',
  `tx_tinyrte_tinyrte_plugins` blob NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`),
  KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `cache_extensions`
-- 

DROP TABLE IF EXISTS `cache_extensions`;
CREATE TABLE  `cache_extensions` (
  `extkey` varchar(60) NOT NULL default '',
  `version` varchar(10) NOT NULL default '',
  `alldownloadcounter` int(11) unsigned NOT NULL default '0',
  `downloadcounter` int(11) unsigned NOT NULL default '0',
  `title` varchar(150) NOT NULL default '',
  `description` mediumtext NOT NULL,
  `state` int(4) NOT NULL default '0',
  `reviewstate` int(4) NOT NULL default '0',
  `category` int(4) NOT NULL default '0',
  `lastuploaddate` int(11) unsigned NOT NULL default '0',
  `dependencies` mediumtext NOT NULL,
  `authorname` varchar(100) NOT NULL default '',
  `authoremail` varchar(100) NOT NULL default '',
  `ownerusername` varchar(50) NOT NULL default '',
  `t3xfilemd5` varchar(35) NOT NULL default '',
  `uploadcomment` mediumtext NOT NULL,
  `authorcompany` varchar(100) NOT NULL default '',
  `intversion` int(11) NOT NULL default '0',
  `lastversion` int(3) NOT NULL default '0',
  `lastreviewedversion` int(3) NOT NULL default '0',
  PRIMARY KEY  (`extkey`,`version`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


-- 
-- Tabellenstruktur f?r Tabelle `cache_hash`
-- 

DROP TABLE IF EXISTS `cache_hash`;
CREATE TABLE  `cache_hash` (
  `hash` varchar(32) NOT NULL default '',
  `content` mediumblob NOT NULL,
  `tstamp` int(11) unsigned NOT NULL default '0',
  `ident` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- 
-- Tabellenstruktur f?r Tabelle `cache_imagesizes`
-- 

DROP TABLE IF EXISTS `cache_imagesizes`;
CREATE TABLE  `cache_imagesizes` (
  `md5hash` varchar(32) NOT NULL default '',
  `md5filename` varchar(32) NOT NULL default '',
  `tstamp` int(11) NOT NULL default '0',
  `filename` varchar(255) default '',
  `imagewidth` mediumint(11) unsigned NOT NULL default '0',
  `imageheight` mediumint(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`md5filename`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `cache_md5params`
-- 

DROP TABLE IF EXISTS `cache_md5params`;
CREATE TABLE  `cache_md5params` (
  `md5hash` varchar(20) NOT NULL default '',
  `tstamp` int(11) NOT NULL default '0',
  `type` tinyint(3) NOT NULL default '0',
  `params` text NOT NULL,
  PRIMARY KEY  (`md5hash`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Daten f?r Tabelle `cache_md5params`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `cache_pages`
-- 

DROP TABLE IF EXISTS `cache_pages`;
CREATE TABLE  `cache_pages` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `hash` varchar(32) NOT NULL default '',
  `page_id` int(11) unsigned NOT NULL default '0',
  `reg1` int(11) unsigned NOT NULL default '0',
  `HTML` mediumtext,
  `temp_content` int(1) NOT NULL default '0',
  `tstamp` int(11) unsigned NOT NULL default '0',
  `expires` int(10) unsigned NOT NULL default '0',
  `cache_data` mediumblob NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `page_id` (`page_id`),
  KEY `sel` (`hash`,`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `cache_pages`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `cache_pagesection`
-- 

DROP TABLE IF EXISTS `cache_pagesection`;
CREATE TABLE  `cache_pagesection` (
  `page_id` int(11) unsigned NOT NULL default '0',
  `mpvar_hash` int(11) unsigned NOT NULL default '0',
  `content` blob NOT NULL,
  `tstamp` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`page_id`,`mpvar_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Daten f?r Tabelle `cache_pagesection`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `cache_typo3temp_log`
-- 

DROP TABLE IF EXISTS `cache_typo3temp_log`;
CREATE TABLE  `cache_typo3temp_log` (
  `md5hash` varchar(32) NOT NULL default '',
  `tstamp` int(11) NOT NULL default '0',
  `filename` varchar(255) default '',
  `orig_filename` varchar(255) default '',
  PRIMARY KEY  (`md5hash`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Daten f?r Tabelle `cache_typo3temp_log`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `fe_groups`
-- 

DROP TABLE IF EXISTS `fe_groups`;
CREATE TABLE  `fe_groups` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `pid` int(11) unsigned NOT NULL default '0',
  `tstamp` int(11) unsigned NOT NULL default '0',
  `title` varchar(50) NOT NULL default '',
  `hidden` tinyint(3) unsigned NOT NULL default '0',
  `lockToDomain` varchar(50) NOT NULL default '',
  `deleted` tinyint(3) unsigned NOT NULL default '0',
  `description` text NOT NULL,
  `subgroup` tinytext,
  `TSconfig` text,
  `crdate` int(11) unsigned NOT NULL default '0',
  `cruser_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `fe_groups`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `fe_session_data`
-- 

DROP TABLE IF EXISTS `fe_session_data`;
CREATE TABLE  `fe_session_data` (
  `hash` varchar(32) NOT NULL default '',
  `content` mediumblob NOT NULL,
  `tstamp` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Daten f?r Tabelle `fe_session_data`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `fe_sessions`
-- 

DROP TABLE IF EXISTS `fe_sessions`;
CREATE TABLE  `fe_sessions` (
  `ses_id` varchar(32) NOT NULL default '',
  `ses_name` varchar(32) NOT NULL default '',
  `ses_iplock` varchar(39) NOT NULL default '',
  `ses_hashlock` int(11) NOT NULL default '0',
  `ses_userid` int(11) unsigned NOT NULL default '0',
  `ses_tstamp` int(11) unsigned NOT NULL default '0',
  `ses_data` blob NOT NULL,
  `ses_permanent` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ses_id`,`ses_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Daten f?r Tabelle `fe_sessions`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `fe_users`
-- 

DROP TABLE IF EXISTS `fe_users`;
CREATE TABLE  `fe_users` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `pid` int(11) unsigned NOT NULL default '0',
  `tstamp` int(11) unsigned NOT NULL default '0',
  `username` varchar(50) NOT NULL default '',
  `password` varchar(40) NOT NULL default '',
  `usergroup` tinytext,
  `disable` tinyint(4) unsigned NOT NULL default '0',
  `starttime` int(11) unsigned NOT NULL default '0',
  `endtime` int(11) unsigned NOT NULL default '0',
  `name` varchar(80) NOT NULL default '',
  `address` varchar(255) default '',
  `telephone` varchar(20) NOT NULL default '',
  `fax` varchar(20) NOT NULL default '',
  `email` varchar(80) NOT NULL default '',
  `crdate` int(11) unsigned NOT NULL default '0',
  `cruser_id` int(11) unsigned NOT NULL default '0',
  `lockToDomain` varchar(50) NOT NULL default '',
  `deleted` tinyint(3) unsigned NOT NULL default '0',
  `uc` blob NOT NULL,
  `title` varchar(40) NOT NULL default '',
  `zip` varchar(10) NOT NULL default '',
  `city` varchar(50) NOT NULL default '',
  `country` varchar(40) NOT NULL default '',
  `www` varchar(80) NOT NULL default '',
  `company` varchar(80) NOT NULL default '',
  `image` tinytext,
  `TSconfig` text,
  `fe_cruser_id` int(10) unsigned NOT NULL default '0',
  `lastlogin` int(10) unsigned NOT NULL default '0',
  `is_online` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `username` (`username`),
  KEY `is_online` (`is_online`),
  KEY `parent` (`pid`,`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `fe_users`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `index_config`
-- 

DROP TABLE IF EXISTS `index_config`;
CREATE TABLE  `index_config` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `hidden` tinyint(4) NOT NULL default '0',
  `starttime` int(11) NOT NULL default '0',
  `set_id` int(11) NOT NULL default '0',
  `session_data` mediumtext NOT NULL,
  `title` varchar(255) default '',
  `description` text NOT NULL,
  `type` varchar(30) NOT NULL default '',
  `depth` int(11) unsigned NOT NULL default '0',
  `table2index` varchar(255) default '',
  `alternative_source_pid` int(11) unsigned NOT NULL default '0',
  `get_params` varchar(255) default '',
  `fieldlist` varchar(255) default '',
  `externalUrl` varchar(255) default '',
  `indexcfgs` text NOT NULL,
  `chashcalc` tinyint(3) unsigned NOT NULL default '0',
  `filepath` varchar(255) default '',
  `extensions` varchar(255) default '',
  `timer_next_indexing` int(11) NOT NULL default '0',
  `timer_frequency` int(11) NOT NULL default '0',
  `timer_offset` int(11) NOT NULL default '0',
  `url_deny` text NOT NULL,
  `recordsbatch` int(11) NOT NULL default '0',
  `records_indexonchange` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `index_config`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `index_debug`
-- 

DROP TABLE IF EXISTS `index_debug`;
CREATE TABLE  `index_debug` (
  `phash` int(11) NOT NULL default '0',
  `debuginfo` mediumtext NOT NULL,
  PRIMARY KEY  (`phash`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Daten f?r Tabelle `index_debug`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `index_fulltext`
-- 

DROP TABLE IF EXISTS `index_fulltext`;
CREATE TABLE  `index_fulltext` (
  `phash` int(11) NOT NULL default '0',
  `fulltextdata` mediumtext NOT NULL,
  PRIMARY KEY  (`phash`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `index_grlist`
-- 

DROP TABLE IF EXISTS `index_grlist`;
CREATE TABLE  `index_grlist` (
  `phash` int(11) NOT NULL default '0',
  `phash_x` int(11) NOT NULL default '0',
  `hash_gr_list` int(11) NOT NULL default '0',
  `gr_list` varchar(255) default '',
  `uniqid` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`uniqid`),
  KEY `joinkey` (`phash`,`hash_gr_list`),
  KEY `phash_grouping` (`phash_x`,`hash_gr_list`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `index_phash`
-- 

DROP TABLE IF EXISTS `index_phash`;
CREATE TABLE  `index_phash` (
  `phash` int(11) NOT NULL default '0',
  `phash_grouping` int(11) NOT NULL default '0',
  `cHashParams` tinyblob NOT NULL,
  `data_filename` varchar(255) default '',
  `data_page_id` int(11) unsigned NOT NULL default '0',
  `data_page_reg1` int(11) unsigned NOT NULL default '0',
  `data_page_type` tinyint(3) unsigned NOT NULL default '0',
  `data_page_mp` varchar(255) default '',
  `gr_list` varchar(255) default '',
  `item_type` varchar(5) NOT NULL default '',
  `item_title` varchar(255) default '',
  `item_description` varchar(255) default '',
  `item_mtime` int(11) NOT NULL default '0',
  `tstamp` int(11) unsigned NOT NULL default '0',
  `item_size` int(11) NOT NULL default '0',
  `contentHash` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `parsetime` int(11) NOT NULL default '0',
  `sys_language_uid` int(11) NOT NULL default '0',
  `item_crdate` int(11) NOT NULL default '0',
  `externalUrl` tinyint(3) NOT NULL default '0',
  `recordUid` int(11) NOT NULL default '0',
  `freeIndexUid` int(11) NOT NULL default '0',
  `freeIndexSetId` int(11) NOT NULL default '0',
  PRIMARY KEY  (`phash`),
  KEY `phash_grouping` (`phash_grouping`),
  KEY `freeIndexUid` (`freeIndexUid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `index_rel`
-- 

DROP TABLE IF EXISTS `index_rel`;
CREATE TABLE  `index_rel` (
  `phash` int(11) NOT NULL default '0',
  `wid` int(11) NOT NULL default '0',
  `count` tinyint(3) unsigned NOT NULL default '0',
  `first` tinyint(3) unsigned NOT NULL default '0',
  `freq` smallint(5) unsigned NOT NULL default '0',
  `flags` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`phash`,`wid`),
  KEY `wid` (`wid`,`phash`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `index_section`
-- 

DROP TABLE IF EXISTS `index_section`;
CREATE TABLE  `index_section` (
  `phash` int(11) NOT NULL default '0',
  `phash_t3` int(11) NOT NULL default '0',
  `rl0` int(11) unsigned NOT NULL default '0',
  `rl1` int(11) unsigned NOT NULL default '0',
  `rl2` int(11) unsigned NOT NULL default '0',
  `page_id` int(11) NOT NULL default '0',
  `uniqid` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`uniqid`),
  KEY `joinkey` (`phash`,`rl0`),
  KEY `page_id` (`page_id`),
  KEY `rl0` (`rl0`,`rl1`,`phash`),
  KEY `rl0_2` (`rl0`,`phash`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `index_stat_search`
-- 

DROP TABLE IF EXISTS `index_stat_search`;
CREATE TABLE  `index_stat_search` (
  `uid` int(11) NOT NULL auto_increment,
  `searchstring` varchar(255) default '',
  `searchoptions` blob NOT NULL,
  `tstamp` int(11) NOT NULL default '0',
  `feuser_id` int(11) unsigned NOT NULL default '0',
  `cookie` varchar(10) NOT NULL default '',
  `IP` varchar(255) default '',
  `hits` int(11) NOT NULL default '0',
  PRIMARY KEY  (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `index_stat_search`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `index_stat_word`
-- 

DROP TABLE IF EXISTS `index_stat_word`;
CREATE TABLE  `index_stat_word` (
  `uid` int(11) NOT NULL auto_increment,
  `word` varchar(30) NOT NULL default '',
  `index_stat_search_id` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `pageid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `tstamp` (`tstamp`,`word`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `index_stat_word`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `index_words`
-- 

DROP TABLE IF EXISTS `index_words`;
CREATE TABLE  `index_words` (
  `wid` int(11) NOT NULL default '0',
  `baseword` varchar(60) NOT NULL default '',
  `metaphone` int(11) NOT NULL default '0',
  `is_stopword` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`wid`),
  KEY `baseword` (`baseword`,`wid`),
  KEY `metaphone` (`metaphone`,`wid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `pages`
-- 

DROP TABLE IF EXISTS `pages`;
CREATE TABLE  `pages` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `t3ver_oid` int(11) NOT NULL default '0',
  `t3ver_id` int(11) NOT NULL default '0',
  `t3ver_wsid` int(11) NOT NULL default '0',
  `t3ver_label` varchar(30) NOT NULL default '',
  `t3ver_state` tinyint(4) NOT NULL default '0',
  `t3ver_stage` tinyint(4) NOT NULL default '0',
  `t3ver_count` int(11) NOT NULL default '0',
  `t3ver_tstamp` int(11) NOT NULL default '0',
  `t3ver_swapmode` tinyint(4) NOT NULL default '0',
  `t3_origuid` int(11) NOT NULL default '0',
  `tstamp` int(11) unsigned NOT NULL default '0',
  `sorting` int(11) unsigned NOT NULL default '0',
  `deleted` tinyint(1) unsigned NOT NULL default '0',
  `perms_userid` int(11) unsigned NOT NULL default '0',
  `perms_groupid` int(11) unsigned NOT NULL default '0',
  `perms_user` tinyint(4) unsigned NOT NULL default '0',
  `perms_group` tinyint(4) unsigned NOT NULL default '0',
  `perms_everybody` tinyint(4) unsigned NOT NULL default '0',
  `editlock` tinyint(4) unsigned NOT NULL default '0',
  `crdate` int(11) unsigned NOT NULL default '0',
  `cruser_id` int(11) unsigned NOT NULL default '0',
  `title` varchar(255) default '',
  `doktype` tinyint(3) unsigned NOT NULL default '0',
  `TSconfig` text NOT NULL,
  `storage_pid` int(11) NOT NULL default '0',
  `is_siteroot` tinyint(4) NOT NULL default '0',
  `php_tree_stop` tinyint(4) NOT NULL default '0',
  `tx_impexp_origuid` int(11) NOT NULL default '0',
  `url` varchar(255) default '',
  `hidden` tinyint(4) unsigned NOT NULL default '0',
  `starttime` int(11) unsigned NOT NULL default '0',
  `endtime` int(11) unsigned NOT NULL default '0',
  `urltype` tinyint(4) unsigned NOT NULL default '0',
  `shortcut` int(10) unsigned NOT NULL default '0',
  `shortcut_mode` int(10) unsigned NOT NULL default '0',
  `no_cache` int(10) unsigned NOT NULL default '0',
  `fe_group` varchar(100) NOT NULL default '0',
  `subtitle` varchar(255) default '',
  `layout` tinyint(3) unsigned NOT NULL default '0',
  `target` varchar(20) NOT NULL default '',
  `media` text,
  `lastUpdated` int(10) unsigned NOT NULL default '0',
  `keywords` text NOT NULL,
  `cache_timeout` int(10) unsigned NOT NULL default '0',
  `newUntil` int(10) unsigned NOT NULL default '0',
  `description` text NOT NULL,
  `no_search` tinyint(3) unsigned NOT NULL default '0',
  `SYS_LASTCHANGED` int(10) unsigned NOT NULL default '0',
  `abstract` text NOT NULL,
  `module` varchar(10) NOT NULL default '',
  `extendToSubpages` tinyint(3) unsigned NOT NULL default '0',
  `author` varchar(255) default '',
  `author_email` varchar(80) NOT NULL default '',
  `nav_title` varchar(255) default '',
  `nav_hide` tinyint(4) NOT NULL default '0',
  `content_from_pid` int(10) unsigned NOT NULL default '0',
  `mount_pid` int(10) unsigned NOT NULL default '0',
  `mount_pid_ol` tinyint(4) NOT NULL default '0',
  `alias` varchar(32) NOT NULL default '',
  `l18n_cfg` tinyint(4) NOT NULL default '0',
  `fe_login_mode` tinyint(4) NOT NULL default '0',
  `t3ver_move_id` int(11) NOT NULL default '0',
  `tx_newspaper_associated_section` blob NOT NULL,
  `tx_newspaper_module` tinytext NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `t3ver_oid` (`t3ver_oid`,`t3ver_wsid`),
  KEY `alias` (`alias`),
  KEY `parent` (`pid`,`sorting`,`deleted`,`hidden`)
) ENGINE=MyISAM AUTO_INCREMENT=3214 DEFAULT CHARSET=latin1 AUTO_INCREMENT=3214 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `pages_language_overlay`
-- 

DROP TABLE IF EXISTS `pages_language_overlay`;
CREATE TABLE  `pages_language_overlay` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `t3ver_oid` int(11) NOT NULL default '0',
  `t3ver_id` int(11) NOT NULL default '0',
  `t3ver_wsid` int(11) NOT NULL default '0',
  `t3ver_label` varchar(30) NOT NULL default '',
  `t3ver_state` tinyint(4) NOT NULL default '0',
  `t3ver_stage` tinyint(4) NOT NULL default '0',
  `t3ver_count` int(11) NOT NULL default '0',
  `t3ver_tstamp` int(11) NOT NULL default '0',
  `t3_origuid` int(11) NOT NULL default '0',
  `tstamp` int(11) unsigned NOT NULL default '0',
  `crdate` int(11) unsigned NOT NULL default '0',
  `cruser_id` int(11) unsigned NOT NULL default '0',
  `sys_language_uid` int(11) unsigned NOT NULL default '0',
  `title` varchar(255) default '',
  `hidden` tinyint(4) unsigned NOT NULL default '0',
  `starttime` int(11) unsigned NOT NULL default '0',
  `endtime` int(11) unsigned NOT NULL default '0',
  `deleted` tinyint(3) unsigned NOT NULL default '0',
  `subtitle` varchar(255) default '',
  `nav_title` varchar(255) default '',
  `media` tinytext,
  `keywords` text NOT NULL,
  `description` text NOT NULL,
  `abstract` text NOT NULL,
  `author` varchar(255) default '',
  `author_email` varchar(80) NOT NULL default '',
  `tx_impexp_origuid` int(11) NOT NULL default '0',
  `l18n_diffsource` mediumblob NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `t3ver_oid` (`t3ver_oid`,`t3ver_wsid`),
  KEY `parent` (`pid`,`sys_language_uid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `pages_language_overlay`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `static_template`
-- 

DROP TABLE IF EXISTS `static_template`;
CREATE TABLE  `static_template` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `pid` int(11) unsigned NOT NULL default '0',
  `tstamp` int(11) unsigned NOT NULL default '0',
  `crdate` int(11) unsigned NOT NULL default '0',
  `title` varchar(255) default '',
  `include_static` tinytext,
  `constants` text,
  `config` text,
  `description` text NOT NULL,
  `editorcfg` text,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=98 DEFAULT CHARSET=latin1 AUTO_INCREMENT=98 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `static_tsconfig_help`
-- 

DROP TABLE IF EXISTS `static_tsconfig_help`;
CREATE TABLE  `static_tsconfig_help` (
  `uid` int(11) NOT NULL auto_increment,
  `guide` int(11) NOT NULL default '0',
  `md5hash` varchar(32) NOT NULL default '',
  `description` text NOT NULL,
  `obj_string` varchar(255) default '',
  `appdata` blob NOT NULL,
  `title` varchar(255) default '',
  PRIMARY KEY  (`uid`),
  KEY `guide` (`guide`,`md5hash`)
) ENGINE=MyISAM AUTO_INCREMENT=118 DEFAULT CHARSET=latin1 AUTO_INCREMENT=118 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `sys_be_shortcuts`
-- 

DROP TABLE IF EXISTS `sys_be_shortcuts`;
CREATE TABLE  `sys_be_shortcuts` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `userid` int(11) unsigned NOT NULL default '0',
  `module_name` varchar(255) default '',
  `url` text NOT NULL,
  `description` varchar(255) default '',
  `sorting` int(11) NOT NULL default '0',
  `sc_group` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `event` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `sys_be_shortcuts`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `sys_domain`
-- 

DROP TABLE IF EXISTS `sys_domain`;
CREATE TABLE  `sys_domain` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `pid` int(11) unsigned NOT NULL default '0',
  `tstamp` int(11) unsigned NOT NULL default '0',
  `hidden` tinyint(4) unsigned NOT NULL default '0',
  `domainName` varchar(80) NOT NULL default '',
  `redirectTo` varchar(120) NOT NULL default '',
  `sorting` int(10) unsigned NOT NULL default '0',
  `prepend_params` int(10) NOT NULL default '0',
  `crdate` int(11) unsigned NOT NULL default '0',
  `cruser_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `sys_domain`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `sys_filemounts`
-- 

DROP TABLE IF EXISTS `sys_filemounts`;
CREATE TABLE  `sys_filemounts` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `pid` int(11) unsigned NOT NULL default '0',
  `tstamp` int(11) unsigned NOT NULL default '0',
  `title` varchar(30) NOT NULL default '',
  `path` varchar(120) NOT NULL default '',
  `base` tinyint(4) unsigned NOT NULL default '0',
  `hidden` tinyint(3) unsigned NOT NULL default '0',
  `deleted` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `sys_history`
-- 

DROP TABLE IF EXISTS `sys_history`;
CREATE TABLE  `sys_history` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `sys_log_uid` int(11) NOT NULL default '0',
  `history_data` mediumtext NOT NULL,
  `fieldlist` text NOT NULL,
  `recuid` int(11) NOT NULL default '0',
  `tablename` varchar(40) NOT NULL default '',
  `tstamp` int(11) NOT NULL default '0',
  `history_files` mediumtext NOT NULL,
  `snapshot` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `recordident` (`tablename`,`recuid`,`tstamp`),
  KEY `sys_log_uid` (`sys_log_uid`)
) ENGINE=MyISAM AUTO_INCREMENT=109 DEFAULT CHARSET=latin1 AUTO_INCREMENT=109 ;


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `sys_language`
-- 

DROP TABLE IF EXISTS `sys_language`;
CREATE TABLE  `sys_language` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `pid` int(11) unsigned NOT NULL default '0',
  `tstamp` int(11) unsigned NOT NULL default '0',
  `hidden` tinyint(4) unsigned NOT NULL default '0',
  `title` varchar(80) NOT NULL default '',
  `flag` varchar(20) NOT NULL default '',
  `static_lang_isocode` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `sys_language`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `sys_lockedrecords`
-- 

DROP TABLE IF EXISTS `sys_lockedrecords`;
CREATE TABLE  `sys_lockedrecords` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `userid` int(11) unsigned NOT NULL default '0',
  `tstamp` int(11) unsigned NOT NULL default '0',
  `record_table` varchar(40) NOT NULL default '',
  `record_uid` int(11) NOT NULL default '0',
  `record_pid` int(11) NOT NULL default '0',
  `username` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`uid`),
  KEY `event` (`userid`,`tstamp`)
) ENGINE=MyISAM AUTO_INCREMENT=297 DEFAULT CHARSET=latin1 AUTO_INCREMENT=297 ;

-- 
-- Daten f?r Tabelle `sys_lockedrecords`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `sys_log`
-- 

DROP TABLE IF EXISTS `sys_log`;
CREATE TABLE  `sys_log` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `userid` int(11) unsigned NOT NULL default '0',
  `action` tinyint(4) unsigned NOT NULL default '0',
  `recuid` int(11) unsigned NOT NULL default '0',
  `tablename` varchar(40) NOT NULL default '',
  `recpid` int(11) NOT NULL default '0',
  `error` tinyint(4) unsigned NOT NULL default '0',
  `details` varchar(255) default '',
  `tstamp` int(11) unsigned NOT NULL default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `details_nr` tinyint(3) unsigned NOT NULL default '0',
  `IP` varchar(39) NOT NULL default '',
  `log_data` varchar(255) default '',
  `event_pid` int(11) NOT NULL default '-1',
  `workspace` int(11) NOT NULL default '0',
  `NEWid` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`uid`),
  KEY `event` (`userid`,`event_pid`),
  KEY `recuidIdx` (`recuid`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1042 ;


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `sys_note`
-- 

DROP TABLE IF EXISTS `sys_note`;
CREATE TABLE  `sys_note` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `pid` int(11) unsigned NOT NULL default '0',
  `deleted` tinyint(3) unsigned NOT NULL default '0',
  `tstamp` int(11) unsigned NOT NULL default '0',
  `crdate` int(11) unsigned NOT NULL default '0',
  `cruser` int(11) unsigned NOT NULL default '0',
  `author` varchar(80) NOT NULL default '',
  `email` varchar(80) NOT NULL default '',
  `subject` varchar(255) default '',
  `message` text NOT NULL,
  `personal` tinyint(3) unsigned NOT NULL default '0',
  `category` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `sys_note`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `sys_preview`
-- 

DROP TABLE IF EXISTS `sys_preview`;
CREATE TABLE  `sys_preview` (
  `keyword` varchar(32) NOT NULL default '',
  `tstamp` int(11) NOT NULL default '0',
  `endtime` int(11) NOT NULL default '0',
  `config` text NOT NULL,
  PRIMARY KEY  (`keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Daten f?r Tabelle `sys_preview`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `sys_refindex`
-- 

DROP TABLE IF EXISTS `sys_refindex`;
CREATE TABLE  `sys_refindex` (
  `hash` varchar(32) NOT NULL default '',
  `tablename` varchar(40) NOT NULL default '',
  `recuid` int(11) NOT NULL default '0',
  `field` varchar(40) NOT NULL default '',
  `flexpointer` varchar(255) default '',
  `softref_key` varchar(30) NOT NULL default '',
  `softref_id` varchar(40) NOT NULL default '',
  `sorting` int(11) NOT NULL default '0',
  `deleted` tinyint(1) NOT NULL default '0',
  `ref_table` varchar(40) NOT NULL default '',
  `ref_uid` int(11) NOT NULL default '0',
  `ref_string` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`hash`),
  KEY `lookup_rec` (`tablename`,`recuid`),
  KEY `lookup_uid` (`ref_table`,`ref_uid`),
  KEY `lookup_string` (`ref_table`,`ref_string`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `sys_refindex_rel`
-- 

DROP TABLE IF EXISTS `sys_refindex_rel`;
CREATE TABLE  `sys_refindex_rel` (
  `rid` int(11) NOT NULL default '0',
  `wid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`rid`,`wid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Daten f?r Tabelle `sys_refindex_rel`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `sys_refindex_res`
-- 

DROP TABLE IF EXISTS `sys_refindex_res`;
CREATE TABLE  `sys_refindex_res` (
  `rid` int(11) NOT NULL default '0',
  `tablename` varchar(100) NOT NULL default '',
  `recuid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`rid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Daten f?r Tabelle `sys_refindex_res`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `sys_refindex_words`
-- 

DROP TABLE IF EXISTS `sys_refindex_words`;
CREATE TABLE  `sys_refindex_words` (
  `wid` int(11) NOT NULL default '0',
  `baseword` varchar(60) NOT NULL default '',
  PRIMARY KEY  (`wid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Daten f?r Tabelle `sys_refindex_words`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `sys_template`
-- 

DROP TABLE IF EXISTS `sys_template`;
CREATE TABLE  `sys_template` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `t3ver_oid` int(11) NOT NULL default '0',
  `t3ver_id` int(11) NOT NULL default '0',
  `t3ver_wsid` int(11) NOT NULL default '0',
  `t3ver_label` varchar(30) NOT NULL default '',
  `t3ver_state` tinyint(4) NOT NULL default '0',
  `t3ver_stage` tinyint(4) NOT NULL default '0',
  `t3ver_count` int(11) NOT NULL default '0',
  `t3ver_tstamp` int(11) NOT NULL default '0',
  `t3_origuid` int(11) NOT NULL default '0',
  `tstamp` int(11) unsigned NOT NULL default '0',
  `sorting` int(11) unsigned NOT NULL default '0',
  `crdate` int(11) unsigned NOT NULL default '0',
  `cruser_id` int(11) unsigned NOT NULL default '0',
  `title` varchar(255) default '',
  `sitetitle` varchar(255) default '',
  `hidden` tinyint(4) unsigned NOT NULL default '0',
  `starttime` int(11) unsigned NOT NULL default '0',
  `endtime` int(11) unsigned NOT NULL default '0',
  `root` tinyint(4) unsigned NOT NULL default '0',
  `clear` tinyint(4) unsigned NOT NULL default '0',
  `include_static` tinytext,
  `include_static_file` text,
  `constants` text,
  `config` text,
  `editorcfg` text,
  `resources` text,
  `nextLevel` varchar(5) NOT NULL default '',
  `description` text NOT NULL,
  `basedOn` tinytext,
  `deleted` tinyint(3) unsigned NOT NULL default '0',
  `includeStaticAfterBasedOn` tinyint(4) unsigned NOT NULL default '0',
  `static_file_mode` tinyint(4) unsigned NOT NULL default '0',
  `tx_impexp_origuid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `t3ver_oid` (`t3ver_oid`,`t3ver_wsid`),
  KEY `parent` (`pid`,`sorting`,`deleted`,`hidden`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `sys_workspace`
-- 

DROP TABLE IF EXISTS `sys_workspace`;
CREATE TABLE  `sys_workspace` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `deleted` tinyint(1) NOT NULL default '0',
  `title` varchar(30) NOT NULL default '',
  `description` varchar(255) default '',
  `adminusers` varchar(255) default '',
  `members` text NOT NULL,
  `reviewers` text NOT NULL,
  `db_mountpoints` varchar(255) NOT NULL default '',
  `file_mountpoints` varchar(255) NOT NULL default '',
  `publish_time` int(11) NOT NULL default '0',
  `unpublish_time` int(11) NOT NULL default '0',
  `freeze` tinyint(3) NOT NULL default '0',
  `live_edit` tinyint(3) NOT NULL default '0',
  `review_stage_edit` tinyint(3) NOT NULL default '0',
  `vtypes` tinyint(3) NOT NULL default '0',
  `disable_autocreate` tinyint(1) NOT NULL default '0',
  `swap_modes` tinyint(3) NOT NULL default '0',
  `publish_access` tinyint(3) NOT NULL default '0',
  `stagechg_notification` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `sys_workspace`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tt_content`
-- 

DROP TABLE IF EXISTS `tt_content`;
CREATE TABLE  `tt_content` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `t3ver_oid` int(11) NOT NULL default '0',
  `t3ver_id` int(11) NOT NULL default '0',
  `t3ver_wsid` int(11) NOT NULL default '0',
  `t3ver_label` varchar(30) NOT NULL default '',
  `t3ver_state` tinyint(4) NOT NULL default '0',
  `t3ver_stage` tinyint(4) NOT NULL default '0',
  `t3ver_count` int(11) NOT NULL default '0',
  `t3ver_tstamp` int(11) NOT NULL default '0',
  `t3_origuid` int(11) NOT NULL default '0',
  `tstamp` int(11) unsigned NOT NULL default '0',
  `hidden` tinyint(4) unsigned NOT NULL default '0',
  `sorting` int(11) unsigned NOT NULL default '0',
  `CType` varchar(30) NOT NULL default '',
  `header` varchar(255) default '',
  `header_position` varchar(6) NOT NULL default '',
  `bodytext` mediumtext NOT NULL,
  `image` text,
  `imagewidth` mediumint(11) unsigned NOT NULL default '0',
  `imageorient` tinyint(4) unsigned NOT NULL default '0',
  `imagecaption` text NOT NULL,
  `imagecols` tinyint(4) unsigned NOT NULL default '0',
  `imageborder` tinyint(4) unsigned NOT NULL default '0',
  `media` text,
  `layout` tinyint(3) unsigned NOT NULL default '0',
  `deleted` tinyint(4) unsigned NOT NULL default '0',
  `cols` tinyint(3) unsigned NOT NULL default '0',
  `records` text,
  `pages` tinytext,
  `starttime` int(11) unsigned NOT NULL default '0',
  `endtime` int(11) unsigned NOT NULL default '0',
  `colPos` tinyint(3) unsigned NOT NULL default '0',
  `subheader` varchar(255) default '',
  `spaceBefore` tinyint(4) unsigned NOT NULL default '0',
  `spaceAfter` tinyint(4) unsigned NOT NULL default '0',
  `fe_group` varchar(100) NOT NULL default '0',
  `header_link` varchar(255) default '',
  `imagecaption_position` varchar(6) NOT NULL default '',
  `image_link` varchar(255) default '',
  `image_zoom` tinyint(3) unsigned NOT NULL default '0',
  `image_noRows` tinyint(3) unsigned NOT NULL default '0',
  `image_effects` tinyint(3) unsigned NOT NULL default '0',
  `image_compression` tinyint(3) unsigned NOT NULL default '0',
  `altText` text NOT NULL,
  `titleText` text NOT NULL,
  `longdescURL` text NOT NULL,
  `header_layout` varchar(30) NOT NULL default '0',
  `text_align` varchar(6) NOT NULL default '',
  `text_face` tinyint(3) unsigned NOT NULL default '0',
  `text_size` tinyint(3) unsigned NOT NULL default '0',
  `text_color` tinyint(3) unsigned NOT NULL default '0',
  `text_properties` tinyint(3) unsigned NOT NULL default '0',
  `menu_type` varchar(30) NOT NULL default '0',
  `list_type` varchar(36) NOT NULL default '0',
  `table_border` tinyint(3) unsigned NOT NULL default '0',
  `table_cellspacing` tinyint(3) unsigned NOT NULL default '0',
  `table_cellpadding` tinyint(3) unsigned NOT NULL default '0',
  `table_bgColor` tinyint(3) unsigned NOT NULL default '0',
  `select_key` varchar(80) NOT NULL default '',
  `sectionIndex` tinyint(3) unsigned NOT NULL default '0',
  `linkToTop` tinyint(3) unsigned NOT NULL default '0',
  `filelink_size` tinyint(3) unsigned NOT NULL default '0',
  `section_frame` tinyint(3) unsigned NOT NULL default '0',
  `date` int(10) unsigned NOT NULL default '0',
  `splash_layout` varchar(30) NOT NULL default '0',
  `multimedia` tinytext,
  `image_frames` tinyint(3) unsigned NOT NULL default '0',
  `recursive` tinyint(3) unsigned NOT NULL default '0',
  `imageheight` mediumint(8) unsigned NOT NULL default '0',
  `rte_enabled` tinyint(4) NOT NULL default '0',
  `sys_language_uid` int(11) NOT NULL default '0',
  `tx_impexp_origuid` int(11) NOT NULL default '0',
  `pi_flexform` mediumtext NOT NULL,
  `l18n_parent` int(11) NOT NULL default '0',
  `l18n_diffsource` mediumblob NOT NULL,
  `t3ver_move_id` int(11) NOT NULL default '0',
  `crdate` int(11) unsigned NOT NULL default '0',
  `cruser_id` int(11) unsigned NOT NULL default '0',
  `tx_newspaper_extra` tinytext NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `t3ver_oid` (`t3ver_oid`,`t3ver_wsid`),
  KEY `parent` (`pid`,`sorting`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=latin1 AUTO_INCREMENT=20 ;

-- 
-- Daten f?r Tabelle `tt_content`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_impexp_presets`
-- 

DROP TABLE IF EXISTS `tx_impexp_presets`;
CREATE TABLE  `tx_impexp_presets` (
  `uid` int(11) NOT NULL auto_increment,
  `user_uid` int(11) NOT NULL default '0',
  `title` varchar(255) default '',
  `public` tinyint(3) NOT NULL default '0',
  `item_uid` int(11) NOT NULL default '0',
  `preset_data` blob NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `lookup` (`item_uid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `tx_impexp_presets`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_article`
-- 

DROP TABLE IF EXISTS `tx_newspaper_article`;
CREATE TABLE  `tx_newspaper_article` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `hidden` tinyint(4) NOT NULL default '0',
  `starttime` int(11) NOT NULL default '0',
  `endtime` int(11) NOT NULL default '0',
  `articletype_id` int(11) NOT NULL default '0',
  `title` tinytext NOT NULL,
  `title_list` tinytext NOT NULL,
  `kicker` tinytext NOT NULL,
  `kicker_list` tinytext NOT NULL,
  `teaser` text NOT NULL,
  `teaser_list` tinytext NOT NULL,
  `text` text NOT NULL,
  `author` tinytext NOT NULL,
  `source_id` tinytext NOT NULL,
  `source_object` blob NOT NULL,
  `extras` int(11) NOT NULL default '0',
  `sections` int(11) NOT NULL default '0',
  `name` tinytext NOT NULL,
  `is_template` tinyint(3) NOT NULL default '0',
  `template_set` tinytext NOT NULL,
  `pagezonetype_id` int(11) NOT NULL default '0',
  `inherits_from` int(11) NOT NULL default '0',
  `publish_date` int(11) NOT NULL default '0',
  `workflow_status` int(11) NOT NULL default '0',
  `modification_user` blob NOT NULL,
  `tags` int(11) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_article_extras_mm`
-- 

DROP TABLE IF EXISTS `tx_newspaper_article_extras_mm`;
CREATE TABLE  `tx_newspaper_article_extras_mm` (
  `uid_local` int(11) NOT NULL default '0',
  `uid_foreign` int(11) NOT NULL default '0',
  `tablenames` varchar(30) NOT NULL default '',
  `sorting` int(11) NOT NULL default '0',
  KEY `uid_local` (`uid_local`),
  KEY `uid_foreign` (`uid_foreign`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Daten f?r Tabelle `tx_newspaper_article_extras_mm`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_article_sections_mm`
-- 

DROP TABLE IF EXISTS `tx_newspaper_article_sections_mm`;
CREATE TABLE  `tx_newspaper_article_sections_mm` (
  `uid_local` int(11) NOT NULL default '0',
  `uid_foreign` int(11) NOT NULL default '0',
  `tablenames` varchar(30) NOT NULL default '',
  `sorting` int(11) NOT NULL default '0',
  KEY `uid_local` (`uid_local`),
  KEY `uid_foreign` (`uid_foreign`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Daten f?r Tabelle `tx_newspaper_article_sections_mm`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_article_tags_mm`
-- 

DROP TABLE IF EXISTS `tx_newspaper_article_tags_mm`;
CREATE TABLE  `tx_newspaper_article_tags_mm` (
  `uid_local` int(11) NOT NULL default '0',
  `uid_foreign` int(11) NOT NULL default '0',
  `tablenames` varchar(30) NOT NULL default '',
  `sorting` int(11) NOT NULL default '0',
  KEY `uid_local` (`uid_local`),
  KEY `uid_foreign` (`uid_foreign`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Daten f?r Tabelle `tx_newspaper_article_tags_mm`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_articlelist`
-- 

DROP TABLE IF EXISTS `tx_newspaper_articlelist`;
CREATE TABLE  `tx_newspaper_articlelist` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `sorting` int(10) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `hidden` tinyint(4) NOT NULL default '0',
  `starttime` int(11) NOT NULL default '0',
  `endtime` int(11) NOT NULL default '0',
  `list_table` tinytext NOT NULL,
  `list_uid` int(11) NOT NULL default '0',
  `section_id` blob NOT NULL,
  `notes` tinytext NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_articlelist_auto`
-- 

DROP TABLE IF EXISTS `tx_newspaper_articlelist_auto`;
CREATE TABLE  `tx_newspaper_articlelist_auto` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `sorting` int(10) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `hidden` tinyint(4) NOT NULL default '0',
  `starttime` int(11) NOT NULL default '0',
  `endtime` int(11) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_articlelist_manual`
-- 

DROP TABLE IF EXISTS `tx_newspaper_articlelist_manual`;
CREATE TABLE  `tx_newspaper_articlelist_manual` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `articles` int(11) NOT NULL default '0',
  `num_articles` int(11) NOT NULL default '0',
  `filter_sections` blob NOT NULL,
  `filter_tags_include` blob NOT NULL,
  `filter_tags_exclude` blob NOT NULL,
  `filter_articlelist_exclude` blob NOT NULL,
  `filter_sql_table` text NOT NULL,
  `filter_sql_where` text NOT NULL,
  `filter_sql_order_by` tinytext NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_articlelist_manual_articles_mm`
-- 

DROP TABLE IF EXISTS `tx_newspaper_articlelist_manual_articles_mm`;
CREATE TABLE  `tx_newspaper_articlelist_manual_articles_mm` (
  `uid_local` int(11) NOT NULL default '0',
  `uid_foreign` int(11) NOT NULL default '0',
  `tablenames` varchar(30) NOT NULL default '',
  `sorting` int(11) NOT NULL default '0',
  KEY `uid_local` (`uid_local`),
  KEY `uid_foreign` (`uid_foreign`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Daten f?r Tabelle `tx_newspaper_articlelist_manual_articles_mm`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_articlelist_semiautomatic`
-- 

DROP TABLE IF EXISTS `tx_newspaper_articlelist_semiautomatic`;
CREATE TABLE  `tx_newspaper_articlelist_semiautomatic` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `articles` int(11) NOT NULL default '0',
  `num_articles` int(11) NOT NULL default '0',
  `filter_sections` blob NOT NULL,
  `filter_tags_include` blob NOT NULL,
  `filter_tags_exclude` blob NOT NULL,
  `filter_articlelist_exclude` blob NOT NULL,
  `filter_sql_table` text NOT NULL,
  `filter_sql_where` text NOT NULL,
  `filter_sql_order_by` tinytext NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_articlelist_semiautomatic_articles_mm`
-- 

DROP TABLE IF EXISTS `tx_newspaper_articlelist_semiautomatic_articles_mm`;
CREATE TABLE  `tx_newspaper_articlelist_semiautomatic_articles_mm` (
  `uid_local` int(11) NOT NULL default '0',
  `uid_foreign` int(11) NOT NULL default '0',
  `tablenames` varchar(30) NOT NULL default '',
  `sorting` int(11) NOT NULL default '0',
  `offset` int(11) default '0',
  KEY `uid_local` (`uid_local`),
  KEY `uid_foreign` (`uid_foreign`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Daten f?r Tabelle `tx_newspaper_articlelist_semiautomatic_articles_mm`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_articletype`
-- 

DROP TABLE IF EXISTS `tx_newspaper_articletype`;
CREATE TABLE  `tx_newspaper_articletype` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `sorting` int(10) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `title` tinytext NOT NULL,
  `normalized_name` tinytext NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_comment_cache`
-- 

DROP TABLE IF EXISTS `tx_newspaper_comment_cache`;
CREATE TABLE  `tx_newspaper_comment_cache` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `hidden` tinyint(4) NOT NULL default '0',
  `article` blob NOT NULL,
  `kicker` tinytext NOT NULL,
  `title` tinytext NOT NULL,
  `author` tinytext NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `tx_newspaper_comment_cache`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_controltag_to_extra`
-- 

DROP TABLE IF EXISTS `tx_newspaper_controltag_to_extra`;
CREATE TABLE  `tx_newspaper_controltag_to_extra` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `tag` blob NOT NULL,
  `tag_type` tinytext NOT NULL,
  `tag_zone` blob NOT NULL,
  `extra_table` tinytext NOT NULL,
  `extra_uid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `tx_newspaper_controltag_to_extra`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_externallinks`
-- 

DROP TABLE IF EXISTS `tx_newspaper_externallinks`;
CREATE TABLE  `tx_newspaper_externallinks` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `text` tinytext NOT NULL,
  `url` tinytext NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `tx_newspaper_externallinks`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_extra`
-- 

DROP TABLE IF EXISTS `tx_newspaper_extra`;
CREATE TABLE  `tx_newspaper_extra` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `hidden` tinyint(4) NOT NULL default '0',
  `starttime` int(11) NOT NULL default '0',
  `endtime` int(11) NOT NULL default '0',
  `extra_table` tinytext NOT NULL,
  `extra_uid` int(11) NOT NULL default '0',
  `position` int(11) NOT NULL default '0',
  `paragraph` int(11) NOT NULL default '0',
  `origin_uid` int(11) NOT NULL default '0',
  `is_inheritable` tinyint(3) NOT NULL default '0',
  `show_extra` tinyint(3) NOT NULL default '0',
  `gui_hidden` tinyint(3) NOT NULL default '0',
  `notes` text NOT NULL,
  `template_set` tinytext NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=latin1 AUTO_INCREMENT=24 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_extra_articlelist`
-- 

DROP TABLE IF EXISTS `tx_newspaper_extra_articlelist`;
CREATE TABLE  `tx_newspaper_extra_articlelist` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `description` tinytext NOT NULL,
  `articlelist` blob NOT NULL,
  `first_article` int(11) NOT NULL default '0',
  `num_articles` int(11) NOT NULL default '0',
  `template` tinytext NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `tx_newspaper_extra_articlelist`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_extra_bio`
-- 

DROP TABLE IF EXISTS `tx_newspaper_extra_bio`;
CREATE TABLE  `tx_newspaper_extra_bio` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `hidden` tinyint(4) NOT NULL default '0',
  `template_set` tinytext NOT NULL,
  `pool` tinyint(3) NOT NULL default '0',
  `author_name` tinytext NOT NULL,
  `author_id` tinytext NOT NULL,
  `image_file` blob NOT NULL,
  `photo_source` tinytext NOT NULL,
  `bio_text` text NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `tx_newspaper_extra_bio`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_extra_controltagzone`
-- 

DROP TABLE IF EXISTS `tx_newspaper_extra_controltagzone`;
CREATE TABLE  `tx_newspaper_extra_controltagzone` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `hidden` tinyint(4) NOT NULL default '0',
  `tag_zone` blob NOT NULL,
  `tag_type` tinytext NOT NULL,
  `default_extra` blob NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `tx_newspaper_extra_controltagzone`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_extra_displayarticles`
-- 

DROP TABLE IF EXISTS `tx_newspaper_extra_displayarticles`;
CREATE TABLE  `tx_newspaper_extra_displayarticles` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `todo` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `tx_newspaper_extra_displayarticles`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_extra_externallinks`
-- 

DROP TABLE IF EXISTS `tx_newspaper_extra_externallinks`;
CREATE TABLE  `tx_newspaper_extra_externallinks` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `pool` tinyint(3) NOT NULL default '0',
  `title` tinytext NOT NULL,
  `links` blob NOT NULL,
  `template` tinytext NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `tx_newspaper_extra_externallinks`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_extra_image`
-- 

DROP TABLE IF EXISTS `tx_newspaper_extra_image`;
CREATE TABLE  `tx_newspaper_extra_image` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `hidden` tinyint(4) NOT NULL default '0',
  `starttime` int(11) NOT NULL default '0',
  `endtime` int(11) NOT NULL default '0',
  `pool` tinyint(3) NOT NULL default '0',
  `title` tinytext NOT NULL,
  `image_file` blob NOT NULL,
  `caption` tinytext NOT NULL,
  `normalized_filename` tinytext NOT NULL,
  `kicker` tinytext NOT NULL,
  `credit` tinytext NOT NULL,
  `source` tinytext NOT NULL,
  `type` int(11) NOT NULL default '0',
  `alttext` tinytext NOT NULL,
  `tags` int(11) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `tx_newspaper_extra_image`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_extra_image_tags_mm`
-- 

DROP TABLE IF EXISTS `tx_newspaper_extra_image_tags_mm`;
CREATE TABLE  `tx_newspaper_extra_image_tags_mm` (
  `uid_local` int(11) NOT NULL default '0',
  `uid_foreign` int(11) NOT NULL default '0',
  `tablenames` varchar(30) NOT NULL default '',
  `sorting` int(11) NOT NULL default '0',
  KEY `uid_local` (`uid_local`),
  KEY `uid_foreign` (`uid_foreign`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Daten f?r Tabelle `tx_newspaper_extra_image_tags_mm`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_extra_mostcommented`
-- 

DROP TABLE IF EXISTS `tx_newspaper_extra_mostcommented`;
CREATE TABLE  `tx_newspaper_extra_mostcommented` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `hidden` tinyint(4) NOT NULL default '0',
  `hours` int(11) NOT NULL default '0',
  `num_favorites` int(11) NOT NULL default '0',
  `display_num` tinyint(3) NOT NULL default '0',
  `display_time` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `tx_newspaper_extra_mostcommented`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_extra_sectionlist`
-- 

DROP TABLE IF EXISTS `tx_newspaper_extra_sectionlist`;
CREATE TABLE  `tx_newspaper_extra_sectionlist` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `sorting` int(10) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `hidden` tinyint(4) NOT NULL default '0',
  `starttime` int(11) NOT NULL default '0',
  `endtime` int(11) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `tx_newspaper_extra_sectionlist`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_extra_textbox`
-- 

DROP TABLE IF EXISTS `tx_newspaper_extra_textbox`;
CREATE TABLE  `tx_newspaper_extra_textbox` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `title` tinytext NOT NULL,
  `text` text NOT NULL,
  `pool` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `tx_newspaper_extra_textbox`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_extra_typo3_ce`
-- 

DROP TABLE IF EXISTS `tx_newspaper_extra_typo3_ce`;
CREATE TABLE  `tx_newspaper_extra_typo3_ce` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `hidden` tinyint(4) NOT NULL default '0',
  `starttime` int(11) NOT NULL default '0',
  `endtime` int(11) NOT NULL default '0',
  `pool` tinyint(3) NOT NULL default '0',
  `content_elements` blob NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `tx_newspaper_extra_typo3_ce`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_log`
-- 

DROP TABLE IF EXISTS `tx_newspaper_log`;
CREATE TABLE  `tx_newspaper_log` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `table_name` tinytext NOT NULL,
  `table_uid` int(11) NOT NULL default '0',
  `be_user` blob NOT NULL,
  `action` tinytext NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `tx_newspaper_log`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_page`
-- 

DROP TABLE IF EXISTS `tx_newspaper_page`;
CREATE TABLE  `tx_newspaper_page` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `section` blob NOT NULL,
  `pagetype_id` blob NOT NULL,
  `inherit_pagetype_id` int(11) NOT NULL default '0',
  `template_set` tinytext NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_pagetype`
-- 

DROP TABLE IF EXISTS `tx_newspaper_pagetype`;
CREATE TABLE  `tx_newspaper_pagetype` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `sorting` int(10) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `type_name` tinytext NOT NULL,
  `normalized_name` tinytext NOT NULL,
  `is_article_page` tinyint(3) NOT NULL default '0',
  `get_var` tinytext NOT NULL,
  `get_value` tinytext,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_pagezone`
-- 

DROP TABLE IF EXISTS `tx_newspaper_pagezone`;
CREATE TABLE  `tx_newspaper_pagezone` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `sorting` int(10) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `name` tinytext NOT NULL,
  `page_id` blob NOT NULL,
  `pagezone_table` tinytext NOT NULL,
  `pagezone_uid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=latin1 AUTO_INCREMENT=24 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_pagezone_page`
-- 

DROP TABLE IF EXISTS `tx_newspaper_pagezone_page`;
CREATE TABLE  `tx_newspaper_pagezone_page` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `pagezonetype_id` int(11) NOT NULL default '0',
  `pagezone_id` tinytext NOT NULL,
  `extras` int(11) NOT NULL default '0',
  `template_set` tinytext NOT NULL,
  `inherits_from` int(11) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=latin1 AUTO_INCREMENT=18 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_pagezone_page_extras_mm`
-- 

DROP TABLE IF EXISTS `tx_newspaper_pagezone_page_extras_mm`;
CREATE TABLE  `tx_newspaper_pagezone_page_extras_mm` (
  `uid_local` int(11) NOT NULL default '0',
  `uid_foreign` int(11) NOT NULL default '0',
  `tablenames` varchar(30) NOT NULL default '',
  `sorting` int(11) NOT NULL default '0',
  KEY `uid_local` (`uid_local`),
  KEY `uid_foreign` (`uid_foreign`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Daten f?r Tabelle `tx_newspaper_pagezone_page_extras_mm`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_pagezonetype`
-- 

DROP TABLE IF EXISTS `tx_newspaper_pagezonetype`;
CREATE TABLE  `tx_newspaper_pagezonetype` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `sorting` int(10) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `type_name` tinytext NOT NULL,
  `normalized_name` tinytext NOT NULL,
  `is_article` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_section`
-- 

DROP TABLE IF EXISTS `tx_newspaper_section`;
CREATE TABLE  `tx_newspaper_section` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `sorting` int(10) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `section_name` tinytext NOT NULL,
  `parent_section` int(11) NOT NULL default '0',
  `default_articletype` int(11) NOT NULL default '0',
  `articlelist` int(11) NOT NULL default '0',
  `template_set` tinytext NOT NULL,
  `pagetype_pagezone` tinytext NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_tag`
-- 

DROP TABLE IF EXISTS `tx_newspaper_tag`;
CREATE TABLE  `tx_newspaper_tag` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `tag` tinytext NOT NULL,
  `tag_type` tinytext NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `tx_newspaper_tag`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspaper_tag_zone`
-- 

DROP TABLE IF EXISTS `tx_newspaper_tag_zone`;
CREATE TABLE  `tx_newspaper_tag_zone` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `hidden` tinyint(4) NOT NULL default '0',
  `name` tinytext NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `tx_newspaper_tag_zone`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspapertaz_extra_adr`
-- 

DROP TABLE IF EXISTS `tx_newspapertaz_extra_adr`;
CREATE TABLE  `tx_newspapertaz_extra_adr` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `included_sections` blob NOT NULL,
  `excluded_sections` blob NOT NULL,
  `subsection` tinyint(3) NOT NULL default '0',
  `show_subsection_menu` tinyint(3) NOT NULL default '0',
  `exclude_from_subsection_menu` blob NOT NULL,
  `num_articles_per_section` int(11) NOT NULL default '0',
  `num_images` int(11) NOT NULL default '0',
  `num_teasers` int(11) NOT NULL default '0',
  `template` tinytext NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `tx_newspapertaz_extra_adr`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_newspapertaz_extra_headlines`
-- 

DROP TABLE IF EXISTS `tx_newspapertaz_extra_headlines`;
CREATE TABLE  `tx_newspapertaz_extra_headlines` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `num_articles` int(11) NOT NULL default '0',
  `first_article` int(11) NOT NULL default '0',
  `section` blob NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten f?r Tabelle `tx_newspapertaz_extra_headlines`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur f?r Tabelle `tx_rtehtmlarea_acronym`
-- 

DROP TABLE IF EXISTS `tx_rtehtmlarea_acronym`;
CREATE TABLE  `tx_rtehtmlarea_acronym` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `pid` int(11) unsigned NOT NULL default '0',
  `deleted` tinyint(4) unsigned NOT NULL default '0',
  `hidden` tinyint(4) unsigned NOT NULL default '0',
  `starttime` int(11) unsigned NOT NULL default '0',
  `endtime` int(11) unsigned NOT NULL default '0',
  `sorting` int(11) unsigned NOT NULL default '0',
  `sys_language_uid` int(11) NOT NULL default '0',
  `type` tinyint(3) unsigned NOT NULL default '1',
  `term` varchar(255) default '',
  `acronym` varchar(255) default '',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


--
-- Tabellenstruktur f�r Tabelle `tx_hptazarticle_list`
--
DROP TABLE IF EXISTS `tx_hptazarticle_list`;
CREATE TABLE IF NOT EXISTS `tx_hptazarticle_list` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `t3ver_oid` int(11) NOT NULL default '0',
  `t3ver_id` int(11) NOT NULL default '0',
  `t3ver_wsid` int(11) NOT NULL default '0',
  `t3ver_label` varchar(30) NOT NULL default '',
  `t3ver_state` tinyint(4) NOT NULL default '0',
  `t3ver_stage` tinyint(4) NOT NULL default '0',
  `t3ver_count` int(11) NOT NULL default '0',
  `t3ver_tstamp` int(11) NOT NULL default '0',
  `t3_origuid` int(11) NOT NULL default '0',
  `sys_language_uid` int(11) NOT NULL default '0',
  `l18n_parent` int(11) NOT NULL default '0',
  `l18n_diffsource` mediumblob NOT NULL,
  `deleted` tinyint(4) NOT NULL default '0',
  `hidden` tinyint(4) NOT NULL default '0',
  `starttime` int(11) NOT NULL default '0',
  `endtime` int(11) NOT NULL default '0',
  `fe_group` int(11) NOT NULL default '0',
  `article_source` tinytext NOT NULL,
  `ressort` tinytext NOT NULL,
  `article_date` tinytext NOT NULL,
  `article_id` tinytext NOT NULL,
  `article_title` tinytext NOT NULL,
  `article_title2` text NOT NULL,
  `article_author` tinytext NOT NULL,
  `article_text` text NOT NULL,
  `article_lines` int(11) NOT NULL default '0',
  `article_type` tinytext NOT NULL,
  `plugin_photo_1_img` blob NOT NULL,
  `plugin_photo_1_link` tinytext NOT NULL,
  `plugin_photo_1_source` tinytext NOT NULL,
  `plugin_photo_1_footer` text NOT NULL,
  `plugin_photo_1_paragraph` int(11) NOT NULL default '0',
  `plugin_photo_1_placement` int(11) NOT NULL default '0',
  `plugin_photo_2_img` blob NOT NULL,
  `plugin_photo_2_link` tinytext NOT NULL,
  `plugin_photo_2_source` tinytext NOT NULL,
  `plugin_photo_2_footer` text NOT NULL,
  `plugin_photo_2_paragraph` int(11) NOT NULL default '0',
  `plugin_photo_2_placement` int(11) NOT NULL default '0',
  `plugin_shorty_1_article_title` tinytext NOT NULL,
  `plugin_shorty_1_article_id` text NOT NULL,
  `plugin_shorty_1_img` blob NOT NULL,
  `plugin_shorty_1_source` tinytext NOT NULL,
  `plugin_shorty_1_footer` text NOT NULL,
  `plugin_shorty_1_paragraph` int(11) NOT NULL default '0',
  `plugin_shorty_1_placement` int(11) NOT NULL default '0',
  `plugin_shorty_2_article_title` tinytext NOT NULL,
  `plugin_shorty_2_article_id` text NOT NULL,
  `plugin_shorty_2_img` blob NOT NULL,
  `plugin_shorty_2_source` tinytext NOT NULL,
  `plugin_shorty_2_footer` text NOT NULL,
  `plugin_shorty_2_paragraph` int(11) NOT NULL default '0',
  `plugin_shorty_2_placement` int(11) NOT NULL default '0',
  `plugin_morelinks_1_link_1` tinytext NOT NULL,
  `plugin_morelinks_1_link_2` tinytext NOT NULL,
  `plugin_morelinks_1_link_3` tinytext NOT NULL,
  `plugin_morelinks_1_link_4` tinytext NOT NULL,
  `plugin_morelinks_1_link_5` tinytext NOT NULL,
  `plugin_morelinks_1_paragraph` int(11) NOT NULL default '0',
  `plugin_morelinks_1_placement` int(11) NOT NULL default '0',
  `plugin_morearticles_1_link_1` tinytext NOT NULL,
  `plugin_morearticles_1_link_2` tinytext NOT NULL,
  `plugin_morearticles_1_link_3` tinytext NOT NULL,
  `plugin_morearticles_1_link_4` tinytext NOT NULL,
  `plugin_morearticles_1_link_5` tinytext NOT NULL,
  `plugin_morearticles_1_paragraph` int(11) NOT NULL default '0',
  `plugin_morearticles_1_placement` int(11) NOT NULL default '0',
  `article_priority` int(11) NOT NULL default '0',
  `article_mainpage` tinyint(3) NOT NULL default '0',
  `article_importtime` int(11) NOT NULL default '0',
  `article_manualtext` text NOT NULL,
  `article_manualtitle` tinytext NOT NULL,
  `article_pagetitle` tinytext NOT NULL,
  `article_kind` tinytext NOT NULL,
  `article_photo` blob NOT NULL,
  `plugin_quote_1_text` tinytext NOT NULL,
  `plugin_quote_1_paragraph` int(11) NOT NULL default '0',
  `plugin_quote_1_placement` int(11) NOT NULL default '0',
  `plugin_quote_2_text` tinytext NOT NULL,
  `plugin_quote_2_paragraph` int(11) NOT NULL default '0',
  `plugin_quote_2_placement` int(11) NOT NULL default '0',
  `plugin_quote_3_text` tinytext NOT NULL,
  `plugin_quote_3_paragraph` int(11) NOT NULL default '0',
  `plugin_quote_3_placement` int(11) NOT NULL default '0',
  `plugin_quote_4_text` tinytext NOT NULL,
  `plugin_quote_4_paragraph` int(11) NOT NULL default '0',
  `plugin_quote_4_placement` int(11) NOT NULL default '0',
  `plugin_quote_5_text` tinytext NOT NULL,
  `plugin_quote_5_paragraph` int(11) NOT NULL default '0',
  `article_teaser` tinyint(3) NOT NULL default '0',
  `plugin_quote_5_placement` int(11) NOT NULL default '0',
  `article_mainpage_teaser` tinyint(3) NOT NULL default '0',
  `article_show_plugins` int(11) NOT NULL default '0',
  `article_mainpage_priority` int(11) NOT NULL default '0',
  `article_mainpage_toparticle` tinyint(3) NOT NULL default '0',
  `article_style_1` tinytext NOT NULL,
  `article_style_2` tinytext NOT NULL,
  `plugin_morearticles_1_searchold_1` tinyint(3) NOT NULL default '0',
  `plugin_morearticles_1_searchold_2` tinyint(3) NOT NULL default '0',
  `plugin_morearticles_1_searchold_3` tinyint(3) NOT NULL default '0',
  `plugin_morearticles_1_searchold_4` tinyint(3) NOT NULL default '0',
  `plugin_morearticles_1_searchold_5` tinyint(3) NOT NULL default '0',
  `plugin_morearticles_1_article_1` tinytext NOT NULL,
  `plugin_morearticles_1_article_2` tinytext NOT NULL,
  `plugin_morearticles_1_article_3` tinytext NOT NULL,
  `plugin_morearticles_1_article_4` tinytext NOT NULL,
  `plugin_morearticles_1_article_5` tinytext NOT NULL,
  `plugin_morelinks_1_text_1` tinytext NOT NULL,
  `plugin_morelinks_1_text_2` tinytext NOT NULL,
  `plugin_morelinks_1_text_3` tinytext NOT NULL,
  `plugin_morelinks_1_text_4` tinytext NOT NULL,
  `plugin_morelinks_1_text_5` tinytext NOT NULL,
  `plugin_morearticles_1_text_1` tinytext NOT NULL,
  `plugin_morearticles_1_text_2` tinytext NOT NULL,
  `plugin_morearticles_1_text_3` tinytext NOT NULL,
  `plugin_morearticles_1_text_4` tinytext NOT NULL,
  `plugin_morearticles_1_text_5` tinytext NOT NULL,
  `article_uniquekey` tinytext NOT NULL,
  `article_dontshow` tinyint(3) NOT NULL default '0',
  `article_seitenbereich` tinytext NOT NULL,
  `article_superressort` tinyint(3) NOT NULL default '0',
  `article_css_style` tinytext NOT NULL,
  `article_no_hitlist` tinyint(3) NOT NULL default '0',
  `article_caption` tinytext NOT NULL,
  `article_roof` tinytext NOT NULL,
  `article_changed` tinyint(3) NOT NULL default '0',
  `article_ad_placement_1` int(11) NOT NULL default '0',
  `article_ad_placement_2` int(11) NOT NULL default '0',
  `article_ad_placement_3` int(11) NOT NULL default '0',
  `article_teaser_short` text NOT NULL,
  `article_mainteaser_image` blob NOT NULL,
  `article_mainteaser_image_source` tinytext NOT NULL,
  `article_teaser_image_source` tinytext NOT NULL,
  `article_correction_link` tinytext NOT NULL,
  `article_editor_print` tinytext NOT NULL,
  `article_editor_print_email` tinytext NOT NULL,
  `article_editor_online` tinytext NOT NULL,
  `article_editor_online_email` tinytext NOT NULL,
  `plugin_yetmorelinks_link_1` tinytext NOT NULL,
  `plugin_yetmorelinks_link_2` tinytext NOT NULL,
  `plugin_yetmorelinks_link_3` tinytext NOT NULL,
  `plugin_yetmorelinks_link_4` tinytext NOT NULL,
  `plugin_yetmorelinks_link_5` tinytext NOT NULL,
  `plugin_yetmorelinks_paragraph` int(11) NOT NULL default '0',
  `plugin_yetmorelinks_placement` int(11) NOT NULL default '0',
  `article_5questions_page` tinytext NOT NULL,
  `article_5questions_text` text NOT NULL,
  `article_5questions_pic` blob NOT NULL,
  `article_further_ressorts` blob NOT NULL,
  `article_teaser_image_alttext` tinytext NOT NULL,
  `article_mainteaser_image_alttext` tinytext NOT NULL,
  `plugin_photo_1_alttext` tinytext NOT NULL,
  `plugin_photo_2_alttext` tinytext NOT NULL,
  `article_mainteaser_image_alternativecredit` tinytext NOT NULL,
  `plugin_photo_1_alternativecredit` tinytext NOT NULL,
  `plugin_photo_2_alternativecredit` tinytext NOT NULL,
  `article_rededit_leadtext` text NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`),
  KEY `t3ver_oid` (`t3ver_oid`,`t3ver_wsid`),
  FULLTEXT KEY `article_text_2` (`article_text`,`article_manualtext`,`article_title`,`article_manualtitle`,`article_title2`,`article_roof`,`article_teaser_short`),
  FULLTEXT KEY `article_title_2` (`article_title`,`article_manualtitle`),
  FULLTEXT KEY `article_text_3` (`article_text`,`article_manualtext`),
  FULLTEXT KEY `article_text` (`article_text`,`article_manualtext`,`article_title`,`article_manualtitle`,`article_title2`,`article_roof`,`article_teaser_short`,`article_author`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16065 ;
