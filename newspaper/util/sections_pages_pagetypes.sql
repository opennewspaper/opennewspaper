-- phpMyAdmin SQL Dump
-- version 2.6.4-pl3
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Jul 24, 2009 at 07:32 PM
-- Server version: 5.0.32
-- PHP Version: 5.2.0-8+etch11
-- 
-- Database: `onlinetaz_2_hel`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_article`
-- 

CREATE TABLE `tx_newspaper_article` (
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
  `title` tinytext collate utf8_unicode_ci NOT NULL,
  `title_list` tinytext collate utf8_unicode_ci NOT NULL,
  `kicker` tinytext collate utf8_unicode_ci NOT NULL,
  `kicker_list` tinytext collate utf8_unicode_ci NOT NULL,
  `teaser` text collate utf8_unicode_ci NOT NULL,
  `teaser_list` tinytext collate utf8_unicode_ci NOT NULL,
  `text` text collate utf8_unicode_ci NOT NULL,
  `author` tinytext collate utf8_unicode_ci NOT NULL,
  `source_id` tinytext collate utf8_unicode_ci NOT NULL,
  `source_object` blob NOT NULL,
  `extras` int(11) NOT NULL default '0',
  `sections` int(11) NOT NULL default '0',
  `name` tinytext collate utf8_unicode_ci NOT NULL,
  `is_template` tinyint(3) NOT NULL default '0',
  `template_set` tinytext collate utf8_unicode_ci NOT NULL,
  `pagezonetype_id` int(11) NOT NULL default '0',
  `inherits_from` int(11) NOT NULL default '0',
  `publish_date` int(11) NOT NULL default '0',
  `workflow_status` int(11) NOT NULL default '0',
  `modification_user` blob NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;

-- 
-- Dumping data for table `tx_newspaper_article`
-- 

INSERT INTO `tx_newspaper_article` VALUES (1, 2574, 0, 0, 0, 0, 0, 0, 0, 0, '', '', '', '', '', '', '', '', '', '', 0, 0, '', 1, '', 0, 0, 0, 0, '');
INSERT INTO `tx_newspaper_article` VALUES (2, 2574, 0, 0, 0, 0, 0, 0, 0, 0, '', '', '', '', '', '', '', '', '', '', 0, 0, '', 1, '', 0, 0, 0, 0, '');
INSERT INTO `tx_newspaper_article` VALUES (3, 2574, 0, 0, 0, 0, 0, 0, 0, 0, '', '', '', '', '', '', '', '', '', '', 0, 0, '', 1, '', 0, 0, 0, 0, '');
INSERT INTO `tx_newspaper_article` VALUES (4, 2574, 0, 0, 0, 0, 0, 0, 0, 0, '', '', '', '', '', '', '', '', '', '', 0, 0, '', 1, '', 0, 0, 0, 0, '');
INSERT INTO `tx_newspaper_article` VALUES (5, 2574, 0, 0, 0, 0, 0, 0, 0, 0, '', '', '', '', '', '', '', '', '', '', 0, 0, '', 1, '', 0, 0, 0, 0, '');
INSERT INTO `tx_newspaper_article` VALUES (6, 2574, 0, 0, 0, 0, 0, 0, 0, 0, '', '', '', '', '', '', '', '', '', '', 0, 0, '', 1, '', 0, 0, 0, 0, '');
INSERT INTO `tx_newspaper_article` VALUES (7, 2574, 0, 0, 0, 0, 0, 0, 0, 0, '', '', '', '', '', '', '', '', '', '', 0, 0, '', 1, '', 0, 0, 0, 0, '');
INSERT INTO `tx_newspaper_article` VALUES (8, 2574, 0, 0, 0, 0, 0, 0, 0, 0, '', '', '', '', '', '', '', '', '', '', 0, 0, '', 1, '', 0, 0, 0, 0, '');

-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_article_extras_mm`
-- 

CREATE TABLE `tx_newspaper_article_extras_mm` (
  `uid_local` int(11) NOT NULL default '0',
  `uid_foreign` int(11) NOT NULL default '0',
  `tablenames` varchar(30) collate utf8_unicode_ci NOT NULL default '',
  `sorting` int(11) NOT NULL default '0',
  KEY `uid_local` (`uid_local`),
  KEY `uid_foreign` (`uid_foreign`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `tx_newspaper_article_extras_mm`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_article_sections_mm`
-- 

CREATE TABLE `tx_newspaper_article_sections_mm` (
  `uid_local` int(11) NOT NULL default '0',
  `uid_foreign` int(11) NOT NULL default '0',
  `tablenames` varchar(30) collate utf8_unicode_ci NOT NULL default '',
  `sorting` int(11) NOT NULL default '0',
  KEY `uid_local` (`uid_local`),
  KEY `uid_foreign` (`uid_foreign`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `tx_newspaper_article_sections_mm`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_articlelist`
-- 

CREATE TABLE `tx_newspaper_articlelist` (
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
  `list_table` tinytext collate utf8_unicode_ci NOT NULL,
  `list_uid` int(11) NOT NULL default '0',
  `section_id` blob NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=8 ;

-- 
-- Dumping data for table `tx_newspaper_articlelist`
-- 



-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_articlelist_manual`
-- 

CREATE TABLE `tx_newspaper_articlelist_manual` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `articles` int(11) NOT NULL default '0',
  `sql_condition` tinytext collate utf8_unicode_ci NOT NULL,
  `sql_order_by` tinytext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `tx_newspaper_articlelist_manual`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_articlelist_manual_articles_mm`
-- 

CREATE TABLE `tx_newspaper_articlelist_manual_articles_mm` (
  `uid_local` int(11) NOT NULL default '0',
  `uid_foreign` int(11) NOT NULL default '0',
  `tablenames` varchar(30) collate utf8_unicode_ci NOT NULL default '',
  `sorting` int(11) NOT NULL default '0',
  KEY `uid_local` (`uid_local`),
  KEY `uid_foreign` (`uid_foreign`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `tx_newspaper_articlelist_manual_articles_mm`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_articlelist_semiautomatic`
-- 

CREATE TABLE `tx_newspaper_articlelist_semiautomatic` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `articles` int(11) NOT NULL default '0',
  `sql_condition` tinytext collate utf8_unicode_ci NOT NULL,
  `sql_order_by` tinytext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `tx_newspaper_articlelist_semiautomatic`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_articlelist_semiautomatic_articles_mm`
-- 

CREATE TABLE `tx_newspaper_articlelist_semiautomatic_articles_mm` (
  `uid_local` int(11) NOT NULL default '0',
  `uid_foreign` int(11) NOT NULL default '0',
  `tablenames` varchar(30) collate utf8_unicode_ci NOT NULL default '',
  `sorting` int(11) NOT NULL default '0',
  KEY `uid_local` (`uid_local`),
  KEY `uid_foreign` (`uid_foreign`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `tx_newspaper_articlelist_semiautomatic_articles_mm`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_articletype`
-- 

CREATE TABLE `tx_newspaper_articletype` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `sorting` int(10) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `title` tinytext collate utf8_unicode_ci NOT NULL,
  `normalized_name` tinytext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

-- 
-- Dumping data for table `tx_newspaper_articletype`
-- 

INSERT INTO `tx_newspaper_articletype` VALUES (1, 2993, 1248448971, 1248448971, 1, 256, 0, 'Standard', 'standard');
INSERT INTO `tx_newspaper_articletype` VALUES (2, 2993, 1248448981, 1248448981, 1, 512, 0, 'Interview', 'interview');
INSERT INTO `tx_newspaper_articletype` VALUES (3, 2993, 1248448990, 1248448990, 1, 768, 0, 'Kommentar', 'kommentar');
INSERT INTO `tx_newspaper_articletype` VALUES (4, 2993, 1248449000, 1248449000, 1, 1024, 0, 'Kolumne', 'kolumne');

-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_externallinks`
-- 

CREATE TABLE `tx_newspaper_externallinks` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `text` tinytext collate utf8_unicode_ci NOT NULL,
  `url` tinytext collate utf8_unicode_ci NOT NULL,
  `target` varchar(6) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `tx_newspaper_externallinks`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_extra`
-- 

CREATE TABLE `tx_newspaper_extra` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `hidden` tinyint(4) NOT NULL default '0',
  `starttime` int(11) NOT NULL default '0',
  `endtime` int(11) NOT NULL default '0',
  `extra_table` tinytext collate utf8_unicode_ci NOT NULL,
  `extra_uid` int(11) NOT NULL default '0',
  `position` int(11) NOT NULL default '0',
  `paragraph` int(11) NOT NULL default '0',
  `origin_uid` int(11) NOT NULL default '0',
  `is_inheritable` tinyint(3) NOT NULL default '0',
  `show_extra` tinyint(3) NOT NULL default '0',
  `gui_hidden` tinyint(3) NOT NULL default '0',
  `notes` text collate utf8_unicode_ci NOT NULL,
  `template_set` tinytext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=37 ;

-- 
-- Dumping data for table `tx_newspaper_extra`
-- 

INSERT INTO `tx_newspaper_extra` VALUES (1, 2476, 1248450990, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 1, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (2, 2476, 1248451013, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 2, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (3, 2476, 1248451024, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 3, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (4, 2476, 1248451026, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 4, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (5, 2574, 1248451028, 0, 0, 0, 0, 0, 0, 'tx_newspaper_article', 1, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (6, 2476, 1248451213, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 5, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (7, 2476, 1248451214, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 6, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (8, 2476, 1248451402, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 7, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (9, 2476, 1248451403, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 8, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (10, 2574, 1248451407, 0, 0, 0, 0, 0, 0, 'tx_newspaper_article', 2, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (11, 2476, 1248451431, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 9, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (12, 2476, 1248451432, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 10, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (13, 2476, 1248452360, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 11, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (14, 2476, 1248452362, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 12, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (15, 2574, 1248452364, 0, 0, 0, 0, 0, 0, 'tx_newspaper_article', 3, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (16, 2476, 1248452384, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 13, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (17, 2476, 1248452386, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 14, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (18, 2476, 1248452394, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 15, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (19, 2476, 1248452396, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 16, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (20, 2574, 1248452398, 0, 0, 0, 0, 0, 0, 'tx_newspaper_article', 4, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (21, 2476, 1248456567, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 17, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (22, 2476, 1248456569, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 18, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (23, 2574, 1248456571, 0, 0, 0, 0, 0, 0, 'tx_newspaper_article', 5, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (24, 2476, 1248456586, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 19, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (25, 2476, 1248456587, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 20, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (26, 2574, 1248456589, 0, 0, 0, 0, 0, 0, 'tx_newspaper_article', 6, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (27, 2476, 1248456615, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 21, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (28, 2476, 1248456617, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 22, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (29, 2476, 1248456623, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 23, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (30, 2476, 1248456625, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 24, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (31, 2574, 1248456627, 0, 0, 0, 0, 0, 0, 'tx_newspaper_article', 7, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (32, 2476, 1248456643, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 25, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (33, 2476, 1248456644, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 26, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (34, 2476, 1248456652, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 27, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (35, 2476, 1248456654, 0, 0, 0, 0, 0, 0, 'tx_newspaper_pagezone_page', 28, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO `tx_newspaper_extra` VALUES (36, 2574, 1248456656, 0, 0, 0, 0, 0, 0, 'tx_newspaper_article', 8, 0, 0, 0, 0, 0, 0, '', '');

-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_extra_articlelist`
-- 

CREATE TABLE `tx_newspaper_extra_articlelist` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `description` tinytext collate utf8_unicode_ci NOT NULL,
  `articlelist` blob NOT NULL,
  `first_article` int(11) NOT NULL default '0',
  `num_articles` int(11) NOT NULL default '0',
  `template` tinytext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `tx_newspaper_extra_articlelist`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_extra_displayarticles`
-- 

CREATE TABLE `tx_newspaper_extra_displayarticles` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `todo` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `tx_newspaper_extra_displayarticles`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_extra_externallinks`
-- 

CREATE TABLE `tx_newspaper_extra_externallinks` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `pool` tinyint(3) NOT NULL default '0',
  `title` tinytext collate utf8_unicode_ci NOT NULL,
  `links` blob NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `tx_newspaper_extra_externallinks`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_extra_image`
-- 

CREATE TABLE `tx_newspaper_extra_image` (
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
  `title` tinytext collate utf8_unicode_ci NOT NULL,
  `image` blob NOT NULL,
  `caption` tinytext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `tx_newspaper_extra_image`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_extra_sectionlist`
-- 

CREATE TABLE `tx_newspaper_extra_sectionlist` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `tx_newspaper_extra_sectionlist`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_extra_textbox`
-- 

CREATE TABLE `tx_newspaper_extra_textbox` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `title` tinytext collate utf8_unicode_ci NOT NULL,
  `text` text collate utf8_unicode_ci NOT NULL,
  `pool` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `tx_newspaper_extra_textbox`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_extra_typo3_ce`
-- 

CREATE TABLE `tx_newspaper_extra_typo3_ce` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `tx_newspaper_extra_typo3_ce`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_log`
-- 

CREATE TABLE `tx_newspaper_log` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `table_name` tinytext collate utf8_unicode_ci NOT NULL,
  `table_uid` int(11) NOT NULL default '0',
  `be_user` blob NOT NULL,
  `action` tinytext collate utf8_unicode_ci NOT NULL,
  `comment` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `tx_newspaper_log`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_page`
-- 

CREATE TABLE `tx_newspaper_page` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `section` blob NOT NULL,
  `pagetype_id` blob NOT NULL,
  `inherit_pagetype_id` int(11) NOT NULL default '0',
  `template_set` tinytext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=15 ;

-- 
-- Dumping data for table `tx_newspaper_page`
-- 

INSERT INTO `tx_newspaper_page` VALUES (1, 2474, 0, 0, 0, 0, 0x31, 0x31, 0, '');
INSERT INTO `tx_newspaper_page` VALUES (2, 2474, 0, 0, 0, 0, 0x31, 0x32, 0, '');
INSERT INTO `tx_newspaper_page` VALUES (3, 2474, 0, 0, 0, 0, 0x32, 0x31, 0, '');
INSERT INTO `tx_newspaper_page` VALUES (4, 2474, 0, 0, 0, 0, 0x32, 0x32, 0, '');
INSERT INTO `tx_newspaper_page` VALUES (5, 2474, 0, 0, 0, 0, 0x33, 0x31, 0, '');
INSERT INTO `tx_newspaper_page` VALUES (6, 2474, 0, 0, 0, 0, 0x33, 0x32, 0, '');
INSERT INTO `tx_newspaper_page` VALUES (7, 2474, 0, 0, 0, 0, 0x34, 0x31, 0, '');
INSERT INTO `tx_newspaper_page` VALUES (8, 2474, 0, 0, 0, 0, 0x34, 0x32, 0, '');
INSERT INTO `tx_newspaper_page` VALUES (9, 2474, 0, 0, 0, 0, 0x35, 0x31, 0, '');
INSERT INTO `tx_newspaper_page` VALUES (10, 2474, 0, 0, 0, 0, 0x35, 0x32, 0, '');
INSERT INTO `tx_newspaper_page` VALUES (11, 2474, 0, 0, 0, 0, 0x36, 0x31, 0, '');
INSERT INTO `tx_newspaper_page` VALUES (12, 2474, 0, 0, 0, 0, 0x36, 0x32, 0, '');
INSERT INTO `tx_newspaper_page` VALUES (13, 2474, 0, 0, 0, 0, 0x37, 0x31, 0, '');
INSERT INTO `tx_newspaper_page` VALUES (14, 2474, 0, 0, 0, 0, 0x37, 0x32, 0, '');

-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_pagetype`
-- 

CREATE TABLE `tx_newspaper_pagetype` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `sorting` int(10) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `type_name` tinytext collate utf8_unicode_ci NOT NULL,
  `normalized_name` tinytext collate utf8_unicode_ci NOT NULL,
  `is_article_page` tinyint(3) NOT NULL default '0',
  `get_var` tinytext collate utf8_unicode_ci NOT NULL,
  `get_value` int(11) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

-- 
-- Dumping data for table `tx_newspaper_pagetype`
-- 

INSERT INTO `tx_newspaper_pagetype` VALUES (1, 2827, 1248449042, 1248449042, 1, 256, 0, 'Ressortseite', '', 0, '', 0);
INSERT INTO `tx_newspaper_pagetype` VALUES (2, 2827, 1248449058, 1248449058, 1, 512, 0, 'Artikelseite', '', 1, 'art', 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_pagezone`
-- 

CREATE TABLE `tx_newspaper_pagezone` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `sorting` int(10) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `name` tinytext collate utf8_unicode_ci NOT NULL,
  `page_id` blob NOT NULL,
  `pagezone_table` tinytext collate utf8_unicode_ci NOT NULL,
  `pagezone_uid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=37 ;

-- 
-- Dumping data for table `tx_newspaper_pagezone`
-- 

INSERT INTO `tx_newspaper_pagezone` VALUES (1, 2476, 1248450990, 0, 0, 0, 0, '', 0x31, 'tx_newspaper_pagezone_page', 1);
INSERT INTO `tx_newspaper_pagezone` VALUES (2, 2476, 1248451013, 0, 0, 0, 0, '', 0x31, 'tx_newspaper_pagezone_page', 2);
INSERT INTO `tx_newspaper_pagezone` VALUES (3, 2476, 1248451024, 0, 0, 0, 0, '', 0x32, 'tx_newspaper_pagezone_page', 3);
INSERT INTO `tx_newspaper_pagezone` VALUES (4, 2476, 1248451026, 0, 0, 0, 0, '', 0x32, 'tx_newspaper_pagezone_page', 4);
INSERT INTO `tx_newspaper_pagezone` VALUES (5, 2574, 1248451028, 0, 0, 0, 0, '', 0x32, 'tx_newspaper_article', 1);
INSERT INTO `tx_newspaper_pagezone` VALUES (6, 2476, 1248451213, 0, 0, 0, 0, '', 0x33, 'tx_newspaper_pagezone_page', 5);
INSERT INTO `tx_newspaper_pagezone` VALUES (7, 2476, 1248451214, 0, 0, 0, 0, '', 0x33, 'tx_newspaper_pagezone_page', 6);
INSERT INTO `tx_newspaper_pagezone` VALUES (8, 2476, 1248451402, 0, 0, 0, 0, '', 0x34, 'tx_newspaper_pagezone_page', 7);
INSERT INTO `tx_newspaper_pagezone` VALUES (9, 2476, 1248451403, 0, 0, 0, 0, '', 0x34, 'tx_newspaper_pagezone_page', 8);
INSERT INTO `tx_newspaper_pagezone` VALUES (10, 2574, 1248451407, 0, 0, 0, 0, '', 0x34, 'tx_newspaper_article', 2);
INSERT INTO `tx_newspaper_pagezone` VALUES (11, 2476, 1248451431, 0, 0, 0, 0, '', 0x35, 'tx_newspaper_pagezone_page', 9);
INSERT INTO `tx_newspaper_pagezone` VALUES (12, 2476, 1248451432, 0, 0, 0, 0, '', 0x35, 'tx_newspaper_pagezone_page', 10);
INSERT INTO `tx_newspaper_pagezone` VALUES (13, 2476, 1248452360, 0, 0, 0, 0, '', 0x36, 'tx_newspaper_pagezone_page', 11);
INSERT INTO `tx_newspaper_pagezone` VALUES (14, 2476, 1248452362, 0, 0, 0, 0, '', 0x36, 'tx_newspaper_pagezone_page', 12);
INSERT INTO `tx_newspaper_pagezone` VALUES (15, 2574, 1248452364, 0, 0, 0, 0, '', 0x36, 'tx_newspaper_article', 3);
INSERT INTO `tx_newspaper_pagezone` VALUES (16, 2476, 1248452384, 0, 0, 0, 0, '', 0x37, 'tx_newspaper_pagezone_page', 13);
INSERT INTO `tx_newspaper_pagezone` VALUES (17, 2476, 1248452386, 0, 0, 0, 0, '', 0x37, 'tx_newspaper_pagezone_page', 14);
INSERT INTO `tx_newspaper_pagezone` VALUES (18, 2476, 1248452394, 0, 0, 0, 0, '', 0x38, 'tx_newspaper_pagezone_page', 15);
INSERT INTO `tx_newspaper_pagezone` VALUES (19, 2476, 1248452396, 0, 0, 0, 0, '', 0x38, 'tx_newspaper_pagezone_page', 16);
INSERT INTO `tx_newspaper_pagezone` VALUES (20, 2574, 1248452398, 0, 0, 0, 0, '', 0x38, 'tx_newspaper_article', 4);
INSERT INTO `tx_newspaper_pagezone` VALUES (21, 2476, 1248456511, 0, 0, 0, 0, '', 0x39, 'tx_newspaper_pagezone_page', 17);
INSERT INTO `tx_newspaper_pagezone` VALUES (22, 2476, 1248456569, 0, 0, 0, 0, '', 0x39, 'tx_newspaper_pagezone_page', 18);
INSERT INTO `tx_newspaper_pagezone` VALUES (23, 2574, 1248456571, 0, 0, 0, 1, '', 0x39, 'tx_newspaper_article', 5);
INSERT INTO `tx_newspaper_pagezone` VALUES (24, 2476, 1248456586, 0, 0, 0, 0, '', 0x3130, 'tx_newspaper_pagezone_page', 19);
INSERT INTO `tx_newspaper_pagezone` VALUES (25, 2476, 1248456587, 0, 0, 0, 0, '', 0x3130, 'tx_newspaper_pagezone_page', 20);
INSERT INTO `tx_newspaper_pagezone` VALUES (26, 2574, 1248456589, 0, 0, 0, 0, '', 0x3130, 'tx_newspaper_article', 6);
INSERT INTO `tx_newspaper_pagezone` VALUES (27, 2476, 1248456615, 0, 0, 0, 0, '', 0x3131, 'tx_newspaper_pagezone_page', 21);
INSERT INTO `tx_newspaper_pagezone` VALUES (28, 2476, 1248456617, 0, 0, 0, 0, '', 0x3131, 'tx_newspaper_pagezone_page', 22);
INSERT INTO `tx_newspaper_pagezone` VALUES (29, 2476, 1248456623, 0, 0, 0, 0, '', 0x3132, 'tx_newspaper_pagezone_page', 23);
INSERT INTO `tx_newspaper_pagezone` VALUES (30, 2476, 1248456625, 0, 0, 0, 0, '', 0x3132, 'tx_newspaper_pagezone_page', 24);
INSERT INTO `tx_newspaper_pagezone` VALUES (31, 2574, 1248456627, 0, 0, 0, 0, '', 0x3132, 'tx_newspaper_article', 7);
INSERT INTO `tx_newspaper_pagezone` VALUES (32, 2476, 1248456643, 0, 0, 0, 0, '', 0x3133, 'tx_newspaper_pagezone_page', 25);
INSERT INTO `tx_newspaper_pagezone` VALUES (33, 2476, 1248456644, 0, 0, 0, 0, '', 0x3133, 'tx_newspaper_pagezone_page', 26);
INSERT INTO `tx_newspaper_pagezone` VALUES (34, 2476, 1248456652, 0, 0, 0, 0, '', 0x3134, 'tx_newspaper_pagezone_page', 27);
INSERT INTO `tx_newspaper_pagezone` VALUES (35, 2476, 1248456654, 0, 0, 0, 0, '', 0x3134, 'tx_newspaper_pagezone_page', 28);
INSERT INTO `tx_newspaper_pagezone` VALUES (36, 2574, 1248456656, 0, 0, 0, 0, '', 0x3134, 'tx_newspaper_article', 8);

-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_pagezone_page`
-- 

CREATE TABLE `tx_newspaper_pagezone_page` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `pagezonetype_id` int(11) NOT NULL default '0',
  `pagezone_id` tinytext collate utf8_unicode_ci NOT NULL,
  `extras` int(11) NOT NULL default '0',
  `template_set` tinytext collate utf8_unicode_ci NOT NULL,
  `inherits_from` int(11) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=29 ;

-- 
-- Dumping data for table `tx_newspaper_pagezone_page`
-- 

INSERT INTO `tx_newspaper_pagezone_page` VALUES (1, 2476, 0, 0, 0, 0, 1, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (2, 2476, 0, 0, 0, 0, 2, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (3, 2476, 0, 0, 0, 0, 1, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (4, 2476, 0, 0, 0, 0, 2, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (5, 2476, 0, 0, 0, 0, 1, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (6, 2476, 0, 0, 0, 0, 2, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (7, 2476, 0, 0, 0, 0, 1, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (8, 2476, 0, 0, 0, 0, 2, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (9, 2476, 0, 0, 0, 0, 1, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (10, 2476, 0, 0, 0, 0, 2, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (11, 2476, 0, 0, 0, 0, 1, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (12, 2476, 0, 0, 0, 0, 2, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (13, 2476, 0, 0, 0, 0, 1, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (14, 2476, 0, 0, 0, 0, 2, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (15, 2476, 0, 0, 0, 0, 1, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (16, 2476, 0, 0, 0, 0, 2, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (17, 2476, 0, 0, 0, 0, 1, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (18, 2476, 0, 0, 0, 0, 2, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (19, 2476, 0, 0, 0, 0, 1, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (20, 2476, 0, 0, 0, 0, 2, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (21, 2476, 0, 0, 0, 0, 1, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (22, 2476, 0, 0, 0, 0, 2, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (23, 2476, 0, 0, 0, 0, 1, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (24, 2476, 0, 0, 0, 0, 2, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (25, 2476, 0, 0, 0, 0, 1, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (26, 2476, 0, 0, 0, 0, 2, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (27, 2476, 0, 0, 0, 0, 1, '', 0, '', 0);
INSERT INTO `tx_newspaper_pagezone_page` VALUES (28, 2476, 0, 0, 0, 0, 2, '', 0, '', 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_pagezone_page_extras_mm`
-- 

CREATE TABLE `tx_newspaper_pagezone_page_extras_mm` (
  `uid_local` int(11) NOT NULL default '0',
  `uid_foreign` int(11) NOT NULL default '0',
  `tablenames` varchar(30) collate utf8_unicode_ci NOT NULL default '',
  `sorting` int(11) NOT NULL default '0',
  KEY `uid_local` (`uid_local`),
  KEY `uid_foreign` (`uid_foreign`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 
-- Dumping data for table `tx_newspaper_pagezone_page_extras_mm`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_pagezonetype`
-- 

CREATE TABLE `tx_newspaper_pagezonetype` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `sorting` int(10) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `type_name` tinytext collate utf8_unicode_ci NOT NULL,
  `normalized_name` tinytext collate utf8_unicode_ci NOT NULL,
  `is_article` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

-- 
-- Dumping data for table `tx_newspaper_pagezonetype`
-- 

INSERT INTO `tx_newspaper_pagezonetype` VALUES (1, 2822, 1248449246, 1248449246, 1, 256, 0, 'Hauptspalte', '', 0);
INSERT INTO `tx_newspaper_pagezonetype` VALUES (2, 2822, 1248449255, 1248449255, 1, 512, 0, 'Rechte Spalte', '', 0);
INSERT INTO `tx_newspaper_pagezonetype` VALUES (3, 2822, 1248449263, 1248449263, 1, 768, 0, 'Artikel', '', 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `tx_newspaper_section`
-- 

CREATE TABLE `tx_newspaper_section` (
  `uid` int(11) NOT NULL auto_increment,
  `pid` int(11) NOT NULL default '0',
  `tstamp` int(11) NOT NULL default '0',
  `crdate` int(11) NOT NULL default '0',
  `cruser_id` int(11) NOT NULL default '0',
  `sorting` int(10) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  `section_name` tinytext collate utf8_unicode_ci NOT NULL,
  `parent_section` int(11) NOT NULL default '0',
  `default_articletype` int(11) NOT NULL default '0',
  `articlelist` int(11) NOT NULL default '0',
  `template_set` tinytext collate utf8_unicode_ci NOT NULL,
  `pagetype_pagezone` tinytext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=8 ;

-- 
-- Dumping data for table `tx_newspaper_section`
-- 

INSERT INTO `tx_newspaper_section` VALUES (1, 2828, 1248449732, 1248449732, 1, 256, 0, '1 - Parent', 0, 1, 1, '', '');
INSERT INTO `tx_newspaper_section` VALUES (2, 2828, 1248450028, 1248450028, 1, 512, 0, '2 - Child 1', 1, 1, 2, '', '');
INSERT INTO `tx_newspaper_section` VALUES (3, 2828, 1248450042, 1248450042, 1, 768, 0, '2 - Child 2', 1, 1, 3, '', '');
INSERT INTO `tx_newspaper_section` VALUES (4, 2828, 1248450058, 1248450058, 1, 1024, 0, '3 - Grandchild 1/1', 2, 1, 4, '', '');
INSERT INTO `tx_newspaper_section` VALUES (5, 2828, 1248450081, 1248450081, 1, 1280, 0, '3 - Grandchild 1/2', 2, 1, 5, '', '');
INSERT INTO `tx_newspaper_section` VALUES (6, 2828, 1248450099, 1248450099, 1, 1536, 0, '3 - Grandchild 2/1', 3, 1, 6, '', '');
INSERT INTO `tx_newspaper_section` VALUES (7, 2828, 1248450130, 1248450130, 1, 1792, 0, '4 - Greatgrandchild 1/1/1', 4, 1, 7, '', '');
