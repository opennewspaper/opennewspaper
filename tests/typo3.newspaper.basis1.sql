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
  `TSconfig` varchar(255),
  `storage_pid` int(11) NOT NULL DEFAULT '0',
  `is_siteroot` tinyint(4) NOT NULL DEFAULT '0',
  `php_tree_stop` tinyint(4) NOT NULL DEFAULT '0',
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
  `media` varchar(255),
  `lastUpdated` int(10) unsigned NOT NULL DEFAULT '0',
  `keywords` varchar(255) NOT NULL,
  `cache_timeout` int(10) unsigned NOT NULL DEFAULT '0',
  `newUntil` int(10) unsigned NOT NULL DEFAULT '0',
  `description` varchar(255) NOT NULL,
  `no_search` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `SYS_LASTCHANGED` int(10) unsigned NOT NULL DEFAULT '0',
  `abstract` varchar(255) NOT NULL,
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
  `t3ver_move_id` int(11) NOT NULL DEFAULT '0',
  `tx_newspaper_associated_section` varchar(255),
  `tx_newspaper_module` varchar(255) NOT NULL,
  `url_scheme` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `backend_layout` int(10) NOT NULL DEFAULT '0',
  `backend_layout_next_level` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `t3ver_oid` (`t3ver_oid`,`t3ver_wsid`),
  KEY `alias` (`alias`),
  KEY `parent` (`pid`,`sorting`,`deleted`,`hidden`)
) ENGINE=MEMORY  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4189 ;


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
  `include_static_file` varchar(255),
  `constants` varchar(255),
  `config` varchar(255),
  `editorcfg` varchar(255),
  `resources` varchar(255),
  `nextLevel` varchar(5) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL,
  `basedOn` varchar(255),
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `includeStaticAfterBasedOn` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `static_file_mode` tinyint(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `t3ver_oid` (`t3ver_oid`,`t3ver_wsid`),
  KEY `parent` (`pid`,`sorting`,`deleted`,`hidden`)
) ENGINE=MEMORY  DEFAULT CHARSET=utf8 AUTO_INCREMENT=491 ;

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
  `bodytext` varchar(255) NOT NULL,
  `image` varchar(255),
  `imagewidth` mediumint(11) unsigned NOT NULL DEFAULT '0',
  `imageorient` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `imagecaption` varchar(255) NOT NULL,
  `imagecols` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `imageborder` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `media` varchar(255),
  `layout` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `deleted` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `cols` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `records` varchar(255),
  `pages` varchar(255),
  `starttime` int(11) unsigned NOT NULL DEFAULT '0',
  `endtime` int(11) unsigned NOT NULL DEFAULT '0',
  `colPos` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `subheader` varchar(255) DEFAULT '',
  `spaceBefore` smallint(5) unsigned DEFAULT '0',
  `spaceAfter` smallint(5) unsigned DEFAULT '0',
  `fe_group` varchar(100) NOT NULL DEFAULT '0',
  `header_link` varchar(255) DEFAULT '',
  `imagecaption_position` varchar(6) NOT NULL DEFAULT '',
  `image_link` varchar(255),
  `image_zoom` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `image_noRows` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `image_effects` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `image_compression` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `altText` varchar(255) NOT NULL,
  `titleText` varchar(255) NOT NULL,
  `longdescURL` varchar(255) NOT NULL,
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
  `multimedia` varchar(255),
  `image_frames` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `recursive` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `imageheight` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rte_enabled` tinyint(4) NOT NULL DEFAULT '0',
  `sys_language_uid` int(11) NOT NULL DEFAULT '0',
  `pi_flexform` varchar(255) NOT NULL,
  `l18n_parent` int(11) NOT NULL DEFAULT '0',
  `l18n_diffsource` varchar(255) NOT NULL,
  `t3ver_move_id` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) unsigned NOT NULL DEFAULT '0',
  `cruser_id` int(11) unsigned NOT NULL DEFAULT '0',
  `tx_newspaper_extra` varchar(255) NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `t3ver_oid` (`t3ver_oid`,`t3ver_wsid`),
  KEY `parent` (`pid`,`sorting`),
  KEY `language` (`l18n_parent`,`sys_language_uid`)
) ENGINE=MEMORY  DEFAULT CHARSET=utf8 AUTO_INCREMENT=22981 ;

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
  `extra_table` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `extra_uid` int(11) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  `paragraph` int(11) NOT NULL DEFAULT '0',
  `origin_uid` int(11) NOT NULL DEFAULT '0',
  `is_inheritable` tinyint(3) NOT NULL DEFAULT '0',
  `show_extra` tinyint(3) NOT NULL DEFAULT '0',
  `gui_hidden` tinyint(3) NOT NULL DEFAULT '0',
  `notes` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `template_set` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `origin_uid` (`origin_uid`)
) ENGINE=MEMORY  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=383423 ;

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
  `template` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `header` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci,
  `short_description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `articlelist` (`articlelist`)
) ENGINE=MEMORY  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;

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
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `image_file` varchar(255) COLLATE utf8_unicode_ci,
  `caption` varchar(255) COLLATE utf8_unicode_ci,
  `normalized_filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `kicker` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `credit` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `source` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `alttext` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tags` int(11) NOT NULL DEFAULT '0',
  `image_type` int(11) NOT NULL DEFAULT '0',
  `short_description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `image_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `template` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `width_set` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `title` (`title`,`kicker`,`caption`)
) ENGINE=MEMORY  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=88318 ;

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
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `page_id` int(11) DEFAULT '0',
  `pagezone_table` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pagezone_uid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`),
  KEY `page_id` (`page_id`)
) ENGINE=MEMORY  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=107046 ;

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
  `template_set` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `inherits_from` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MEMORY  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=613 ;

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
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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


CREATE TABLE `cache_hash` (
  `hash` varchar(32) NOT NULL DEFAULT '',
  `content` mediumblob NOT NULL,
  `tstamp` int(11) unsigned NOT NULL DEFAULT '0',
  `ident` varchar(32) DEFAULT '',
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`)
) ENGINE=InnoDB AUTO_INCREMENT=79342 DEFAULT CHARSET=utf8 AUTO_INCREMENT=79342 ;

