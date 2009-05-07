#
# Table structure for table 'tx_newspaper_extra_image'
#
CREATE TABLE tx_newspaper_extra_image (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	extra_field tinytext,
	pool tinyint(3) DEFAULT '0' NOT NULL,
	template_set tinytext,
	title tinytext,
	image text,
	caption tinytext,
	
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
	section_name tinytext,
	parent_section int(11) DEFAULT '0' NOT NULL,
	articlelist int(11) DEFAULT '0' NOT NULL,
	template_set int(11) DEFAULT '0' NOT NULL,
	pagetype_pagezone tinytext,
	
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
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	section text,
	pagetype_id text,
	inherit_pagetype_id int(11) DEFAULT '0' NOT NULL,
	template_set int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_newspaper_pagezone'
#
CREATE TABLE tx_newspaper_pagezone (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	name tinytext,
	page_id text,
	pagezone_table tinytext,
	pagezone_uid int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);




#
# Table structure for table 'tx_newspaper_pagezone_page_extras_mm'
# 
#
CREATE TABLE tx_newspaper_pagezone_page_extras_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);



#
# Table structure for table 'tx_newspaper_pagezone_page'
#
CREATE TABLE tx_newspaper_pagezone_page (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	pagezonetype_id int(11) DEFAULT '0' NOT NULL,
	pagezone_id tinytext,
	extras int(11) DEFAULT '0' NOT NULL,
	template_set int(11) DEFAULT '0' NOT NULL,
	inherits_from int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);




#
# Table structure for table 'tx_newspaper_article_extras_mm'
# 
#
CREATE TABLE tx_newspaper_article_extras_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);




#
# Table structure for table 'tx_newspaper_article_sections_mm'
# 
#
CREATE TABLE tx_newspaper_article_sections_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);



#
# Table structure for table 'tx_newspaper_article'
#
CREATE TABLE tx_newspaper_article (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	articletype_id int(11) DEFAULT '0' NOT NULL,
	title tinytext,
	kicker tinytext,
	teaser text,
	text text,
	author tinytext,
	source_id tinytext,
	source_object text,
	extras int(11) DEFAULT '0' NOT NULL,
	sections int(11) DEFAULT '0' NOT NULL,
	name tinytext,
	is_template tinyint(3) DEFAULT '0' NOT NULL,
	template_set int(11) DEFAULT '0' NOT NULL,
	pagezonetype_id int(11) DEFAULT '0' NOT NULL,
	workflow_status int(11) DEFAULT '0' NOT NULL,
	inherits_from int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_newspaper_extra'
#
CREATE TABLE tx_newspaper_extra (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	extra_table tinytext,
	extra_uid int(11) DEFAULT '0' NOT NULL,
	position int(11) DEFAULT '0' NOT NULL,
	paragraph int(11) DEFAULT '0' NOT NULL,
	origin_uid int(11) DEFAULT '0' NOT NULL,
	is_inheritable tinyint(3) DEFAULT '0' NOT NULL,
	show_extra tinyint(3) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_newspaper_extra_sectionlist'
#
CREATE TABLE tx_newspaper_extra_sectionlist (
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
	template_set tinytext,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_newspaper_articlelist'
#
CREATE TABLE tx_newspaper_articlelist (
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
	list_table tinytext,
	list_uid int(11) DEFAULT '0' NOT NULL,
	section_id text,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_newspaper_articlelist_auto'
#
CREATE TABLE tx_newspaper_articlelist_auto (
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
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_newspaper_pagetype'
#
CREATE TABLE tx_newspaper_pagetype (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	type_name tinytext,
	normalized_name tinytext,
	get_var tinytext,
	get_value int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_newspaper_pagezonetype'
#
CREATE TABLE tx_newspaper_pagezonetype (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	type_name tinytext,
	normalized_name tinytext,
	is_article tinyint(3) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_newspaper_log'
#
CREATE TABLE tx_newspaper_log (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	table_name tinytext,
	table_uid int(11) DEFAULT '0' NOT NULL,
	be_user text,
	action tinytext,
	comment text,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_newspaper_articletype'
#
CREATE TABLE tx_newspaper_articletype (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	title tinytext,
	normalized_name tinytext,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_newspaper_extra_typo3_ce'
#
CREATE TABLE tx_newspaper_extra_typo3_ce (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	pool tinyint(3) DEFAULT '0' NOT NULL,
	template_set tinytext,
	content_elements text,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
	tx_newspaper_extra tinytext
);



#
# Table structure for table 'pages'
#
CREATE TABLE pages (
	tx_newspaper_associated_section int(11) DEFAULT '0' NOT NULL,
	tx_newspaper_module tinytext
);
# Modifications for table 'tx_newspaper_section'
CREATE TABLE tx_newspaper_section (
	template_set tinytext NOT NULL
);

# Modifications for table 'tx_newspaper_page'
CREATE TABLE tx_newspaper_page (
	template_set tinytext NOT NULL
);

# Modifications for table 'tx_newspaper_pagezone_page'
CREATE TABLE tx_newspaper_pagezone_page (
	template_set tinytext NOT NULL
);

# Modifications for table 'tx_newspaper_article'
CREATE TABLE tx_newspaper_article (
	template_set tinytext NOT NULL
);