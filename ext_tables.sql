#
# Table structure for table 'tx_newspaper_extra_image'
#
CREATE TABLE tx_newspaper_extra_image (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	extra_field tinytext NOT NULL,
	title tinytext NOT NULL,
	image blob NOT NULL,
	caption tinytext NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_newspaper_section'
#
CREATE TABLE tx_newspaper_section (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	section_name tinytext NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_newspaper_page'
#
CREATE TABLE tx_newspaper_page (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	section blob NOT NULL,
	type_name tinytext NOT NULL,
	get_var varchar(10) DEFAULT '' NOT NULL,
	get_value int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
	tx_newspaper_extra tinytext NOT NULL
);



#
# Table structure for table 'pages'
#
CREATE TABLE pages (
	tx_newspaper_associated_section int(11) DEFAULT '0' NOT NULL
);
#
# Table structure for table 'tx_newspaper_content_extra_mm'
# This table has to be added manually to this file after using the kickstarter
#
CREATE TABLE tx_newspaper_content_extra_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  extra_type varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) unsigned DEFAULT '0' NOT NULL,
  conf text,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);
