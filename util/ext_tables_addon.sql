
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
