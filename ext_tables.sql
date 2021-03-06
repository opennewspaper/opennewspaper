
#
# Table structure for table 'be_users'
#
CREATE TABLE be_users (
    tx_newspaper_role int(11) DEFAULT '0' NOT NULL
);

#
# Table structure for table 'pages'
#
CREATE TABLE pages (
    tx_newspaper_associated_section text NOT NULL
    tx_newspaper_module tinytext NOT NULL
) ENGINE=InnoDB;

#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
    tx_newspaper_extra tinytext NOT NULL
) ENGINE=InnoDB;

### A ##########################################################################

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
    author tinytext NOT NULL,
    kicker tinytext NOT NULL,
    title tinytext NOT NULL,
    teaser text NOT NULL,
    kicker_list tinytext NOT NULL,
    title_list tinytext NOT NULL,
    teaser_list text NOT NULL,
    bodytext longtext NOT NULL,
    no_rte tinyint(3) DEFAULT '0' NOT NULL,
    url tinytext NOT NULL,
    publish_date int(11) DEFAULT '0' NOT NULL,
    modification_user text NOT NULL,
    source_id tinytext NOT NULL,
    source_object tinytext NOT NULL,
    sections int(11) DEFAULT '0' NOT NULL,
    is_template tinyint(3) DEFAULT '0' NOT NULL,
    extras int(11) DEFAULT '0' NOT NULL,
    name tinytext NOT NULL,
    pagezonetype_id int(11) DEFAULT '0' NOT NULL,
    template_set tinytext NOT NULL,
    inherits_from int(11) DEFAULT '0' NOT NULL,
    tags int(11) DEFAULT '0' NOT NULL,
    related int(11) DEFAULT '0' NOT NULL,
    workflow_status int(11) DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    FULLTEXT KEY title (title,kicker,title_list,kicker_list),
    FULLTEXT KEY text (teaser,teaser_list,bodytext,author),
    KEY articletype_id (articletype_id),
    FULLTEXT KEY author_text (author),
    INDEX author (author(64)),
    KEY publish_date (publish_date)
) ENGINE = MyISAM;

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
# Table structure for table 'tx_newspaper_article_related_mm'
#
#
CREATE TABLE tx_newspaper_article_related_mm (
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
# Table structure for table 'tx_newspaper_article_tags_mm'
#
#
CREATE TABLE tx_newspaper_article_tags_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
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
    notes tinytext NOT NULL,
    list_table tinytext NOT NULL,
    list_uid int(11) DEFAULT '0' NOT NULL,
    section_id int(11) DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY section_id (section_id)
) ENGINE=InnoDB;

#
# Table structure for table 'tx_newspaper_articlelist_manual'
#
CREATE TABLE tx_newspaper_articlelist_manual (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    articles int(11) DEFAULT '0' NOT NULL,
    num_articles int(11) DEFAULT '0' NOT NULL,
    filter_sections text NOT NULL,
    filter_tags_include text NOT NULL,
    filter_tags_exclude text NOT NULL,
    filter_articlelist_exclude text NOT NULL
    filter_sql_table text NOT NULL,
    filter_sql_where text NOT NULL,
    filter_sql_order_by tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
) ENGINE=InnoDB;

#
# Table structure for table 'tx_newspaper_articlelist_manual_articles_mm'
#
#
CREATE TABLE tx_newspaper_articlelist_manual_articles_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_newspaper_articlelist_semiautomatic'
#
CREATE TABLE tx_newspaper_articlelist_semiautomatic (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    articles int(11) DEFAULT '0' NOT NULL,
    num_articles int(11) DEFAULT '0' NOT NULL,
    filter_sections text NOT NULL,
    subsequent_sections tinyint(3) DEFAULT '0' NOT NULL,
    filter_tags_include text NOT NULL,
    filter_tags_exclude text NOT NULL,
    filter_articlelist_exclude text NOT NULL
    filter_sql_table text NOT NULL,
    filter_sql_where text NOT NULL,
    filter_sql_order_by tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
) ENGINE=InnoDB;

#
# Table structure for table 'tx_newspaper_articlelist_semiautomatic_articles_mm'
#
#
CREATE TABLE tx_newspaper_articlelist_semiautomatic_articles_mm (
    uid_local int(11) DEFAULT '0' NOT NULL,
    uid_foreign int(11) DEFAULT '0' NOT NULL,
    tablenames varchar(30) DEFAULT '' NOT NULL,
    sorting int(11) DEFAULT '0' NOT NULL,
    offset int(11) DEFAULT '0' NOT NULL
    KEY uid_local (uid_local),
    KEY uid_foreign (uid_foreign)
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
    title tinytext NOT NULL,
    normalized_name tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

### C ##########################################################################

#
# Table structure for table 'tx_newspaper_comment_cache'
#
CREATE TABLE tx_newspaper_comment_cache (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    article text NOT NULL,
    kicker tinytext NOT NULL,
    title tinytext NOT NULL,
    author tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

#
# Table structure for table 'tx_newspaper_controltag_to_extra'
#
CREATE TABLE tx_newspaper_controltag_to_extra (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    sorting int(10) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    tag int(11) DEFAULT '0' NOT NULL,
    tag_zone int(11) DEFAULT '0' NOT NULL,
    extra text NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY tag (tag),
    KEY tag_zone (tag_zone)
);

#
# Table structure for table 'tx_newspaper_ctrltag_category'
#
CREATE TABLE tx_newspaper_ctrltag_category (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    sorting int(10) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    title tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

### E ##########################################################################

#
# Table structure for table 'tx_newspaper_externallinks'
#
CREATE TABLE tx_newspaper_externallinks (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    starttime int(11) DEFAULT '0' NOT NULL,
    endtime int(11) DEFAULT '0' NOT NULL,
    url tinytext NOT NULL,

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
    extra_table tinytext NOT NULL,
    extra_uid int(11) DEFAULT '0' NOT NULL,
    position int(11) DEFAULT '0' NOT NULL,
    paragraph int(11) DEFAULT '0' NOT NULL,
    origin_uid int(11) DEFAULT '0' NOT NULL,
    is_inheritable tinyint(3) DEFAULT '0' NOT NULL,
    show_extra tinyint(3) DEFAULT '0' NOT NULL,
    gui_hidden tinyint(3) DEFAULT '0' NOT NULL,
    notes text NOT NULL,
    template_set tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY origin_uid (origin_uid),
    KEY extra_uid (extra_uid),
    INDEX extra_table (extra_table(30))
) ENGINE=InnoDB;

#
# Table structure for table 'tx_newspaper_extra_ad'
#
CREATE TABLE tx_newspaper_extra_ad (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    starttime int(11) DEFAULT '0' NOT NULL,
    endtime int(11) DEFAULT '0' NOT NULL,
    short_description tinytext NOT NULL,
    template tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

#
# Table structure for table 'tx_newspaper_extra_articlelist'
#
CREATE TABLE tx_newspaper_extra_articlelist (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    starttime int(11) DEFAULT '0' NOT NULL,
    endtime int(11) DEFAULT '0' NOT NULL,
    short_description tinytext NOT NULL,
    articlelist int(11) DEFAULT '0' NOT NULL,
    first_article int(11) DEFAULT '0' NOT NULL,
    num_articles int(11) DEFAULT '0' NOT NULL,
    header tinytext NOT NULL,
    image text NOT NULL,
    template tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY articlelist (articlelist)
);

#
# Table structure for table 'tx_newspaper_extra_bio'
#
CREATE TABLE tx_newspaper_extra_bio (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    starttime int(11) DEFAULT '0' NOT NULL,
    endtime int(11) DEFAULT '0' NOT NULL,
    short_description tinytext NOT NULL,
    template_set tinytext NOT NULL,
    pool tinyint(3) DEFAULT '0' NOT NULL,
    author_name tinytext NOT NULL,
    is_author tinyint(3) DEFAULT '0' NOT NULL,
    author_id tinytext NOT NULL,
    image_file text NOT NULL,
    photo_source tinytext NOT NULL,
    bio_text text NOT NULL,
    template tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    FULLTEXT KEY title (author_name,bio_text)
) ENGINE = MyISAM;

#
# Table structure for table 'tx_newspaper_extra_combolinkbox'
#
CREATE TABLE tx_newspaper_extra_combolinkbox (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    starttime int(11) DEFAULT '0' NOT NULL,
    endtime int(11) DEFAULT '0' NOT NULL,
    short_description tinytext NOT NULL,
    title tinytext NOT NULL,
    show_related_articles tinyint(3) DEFAULT '0' NOT NULL,
    manually_selected_articles text NOT NULL,
    internal_links text NOT NULL,
    external_links text NOT NULL
    template tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

#
# Table structure for table 'tx_newspaper_extra_container'
#
CREATE TABLE tx_newspaper_extra_container (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    starttime int(11) DEFAULT '0' NOT NULL,
    endtime int(11) DEFAULT '0' NOT NULL,
    short_description tinytext NOT NULL,
    extras text NOT NULL
    template tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

#
# Table structure for table 'tx_newspaper_extra_controltagzone'
#
CREATE TABLE tx_newspaper_extra_controltagzone (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    starttime int(11) DEFAULT '0' NOT NULL,
    endtime int(11) DEFAULT '0' NOT NULL,
    short_description tinytext NOT NULL,
    tag_zone int(11) DEFAULT '0' NOT NULL,
    default_extra text NOT NULL,
    template tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY tag_zone (tag_zone)
);

#
# Table structure for table 'tx_newspaper_extra_displayarticles'
#
CREATE TABLE tx_newspaper_extra_displayarticles (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    starttime int(11) DEFAULT '0' NOT NULL,
    endtime int(11) DEFAULT '0' NOT NULL,
    short_description tinytext NOT NULL,
    template tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

#
# Table structure for table 'tx_newspaper_extra_externallinks'
#
CREATE TABLE tx_newspaper_extra_externallinks (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    starttime int(11) DEFAULT '0' NOT NULL,
    endtime int(11) DEFAULT '0' NOT NULL,
    short_description tinytext NOT NULL,
    pool tinyint(3) DEFAULT '0' NOT NULL,
    title tinytext NOT NULL,
    links text NOT NULL
    template tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

#
# Table structure for table 'tx_newspaper_extra_flexform'
#
CREATE TABLE tx_newspaper_extra_flexform (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    short_description tinytext NOT NULL,
    ds_file tinytext NOT NULL,
    flexform mediumtext NOT NULL,
    template tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

#
# Table structure for table 'tx_newspaper_extra_freeformimage'
#
CREATE TABLE tx_newspaper_extra_freeformimage (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    short_description tinytext NOT NULL,
    image_file blob NOT NULL,
    image_width int(11) DEFAULT '0' NOT NULL,
    image_height int(11) DEFAULT '0' NOT NULL,
    template tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

#
# Table structure for table 'tx_newspaper_extra_generic'
#
CREATE TABLE tx_newspaper_extra_generic (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    starttime int(11) DEFAULT '0' NOT NULL,
    endtime int(11) DEFAULT '0' NOT NULL,
    short_description tinytext NOT NULL,
    template tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

#
# Table structure for table 'tx_newspaper_extra_html'
#
CREATE TABLE tx_newspaper_extra_html (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    short_description tinytext NOT NULL,
    html text NOT NULL,
    template tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

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
    short_description tinytext NOT NULL,
    pool tinyint(3) DEFAULT '0' NOT NULL,
    title tinytext NOT NULL,
    image_file text NOT NULL,
    credit tinytext NOT NULL,
    caption text NOT NULL,
    normalized_filename tinytext NOT NULL,
    kicker tinytext NOT NULL,
    source tinytext NOT NULL,
    image_type int(11) DEFAULT '0' NOT NULL,
    alttext tinytext NOT NULL,
    image_url tinytext NOT NULL,
    tags int(11) DEFAULT '0' NOT NULL,
    width_set int(11) DEFAULT '0' NOT NULL,
    template tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    FULLTEXT KEY title (title,kicker,caption)
) ENGINE = MyISAM;

#
# Table structure for table 'tx_newspaper_extra_image_tags_mm'
#
#
CREATE TABLE tx_newspaper_extra_image_tags_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

#
# Table structure for table 'tx_newspaper_extra_mostcommented'
#
CREATE TABLE tx_newspaper_extra_mostcommented (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    starttime int(11) DEFAULT '0' NOT NULL,
    endtime int(11) DEFAULT '0' NOT NULL,
    short_description tinytext NOT NULL,
    hours int(11) DEFAULT '0' NOT NULL,
    num_favorites int(11) DEFAULT '0' NOT NULL,
    display_num tinyint(3) DEFAULT '0' NOT NULL,
    display_time tinyint(3) DEFAULT '0' NOT NULL,
    template tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

#
# Table structure for table 'tx_newspaper_extra_phpinclude'
#
CREATE TABLE tx_newspaper_extra_phpinclude (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    short_description tinytext NOT NULL,
    file tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

#
# Table structure for table 'tx_newspaper_extra_searchresults'
#
CREATE TABLE tx_newspaper_extra_searchresults (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    starttime int(11) DEFAULT '0' NOT NULL,
    endtime int(11) DEFAULT '0' NOT NULL,
    short_description tinytext NOT NULL,
    sections text NOT NULL,
    search_term tinytext NOT NULL,
    tags text NOT NULL
    template tinytext NOT NULL,

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
    short_description tinytext NOT NULL,
    first_article int(11) DEFAULT '0' NOT NULL,
    num_articles int(11) DEFAULT '0' NOT NULL,
    template tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

#
# Table structure for table 'tx_newspaper_extra_sectionteaser'
#
CREATE TABLE tx_newspaper_extra_sectionteaser (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    short_description tinytext NOT NULL,
    description_text tinytext NOT NULL,
    is_ctrltag int(11) DEFAULT '0' NOT NULL,
    section int(11) DEFAULT '0' NOT NULL,
    ctrltag_cat int(11) DEFAULT '0' NOT NULL,
    ctrltag int(11) DEFAULT '0' NOT NULL,
    num_articles int(11) DEFAULT '0' NOT NULL,
    num_articles_w_image int(11) DEFAULT '0' NOT NULL,
    template tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY ctrltag (ctrltag),
    KEY section (section)
);

#
# Table structure for table 'tx_newspaper_extra_specialhits'
#
CREATE TABLE tx_newspaper_extra_specialhits (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    short_description tinytext NOT NULL,
    template tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

#
# Table structure for table 'tx_newspaper_extra_textbox'
#
CREATE TABLE tx_newspaper_extra_textbox (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    starttime int(11) DEFAULT '0' NOT NULL,
    endtime int(11) DEFAULT '0' NOT NULL,
    short_description tinytext NOT NULL,
    pool tinyint(3) DEFAULT '0' NOT NULL,
    title tinytext NOT NULL,
    bodytext text NOT NULL,
    image_file text NOT NULL,
    template tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    FULLTEXT KEY title (title,bodytext)
) ENGINE = MyISAM;

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
    short_description tinytext NOT NULL,
    pool tinyint(3) DEFAULT '0' NOT NULL,
    content_elements text NOT NULL
    template tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);


### L ##########################################################################

#
# Table structure for table 'tx_newspaper_log'
#
CREATE TABLE tx_newspaper_log (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    table_name tinytext NOT NULL,
    table_uid int(11) DEFAULT '0' NOT NULL,
    be_user text NOT NULL,
    operation tinytext NOT NULL,
    comment text NOT NULL,
    details longtext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    INDEX table_key (table_name(30),table_uid)
) ENGINE=InnoDB;

### P ##########################################################################

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
    section int(11) DEFAULT '0' NOT NULL,
    pagetype_id int(11) DEFAULT '0' NOT NULL,
    inherit_pagetype_id int(11) DEFAULT '0' NOT NULL,
    template_set tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY section (section),
    KEY pagetype_id (pagetype_id),
    KEY inherit_pagetype_id (inherit_pagetype_id)
) ENGINE=InnoDB;

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
    type_name tinytext NOT NULL,
    normalized_name tinytext NOT NULL,
    is_article_page tinyint(3) DEFAULT '0' NOT NULL,
    get_var tinytext NOT NULL,
    get_value tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    INDEX get_var (get_var(8)),
    INDEX get_value (get_value(20)),
    INDEX get_params (get_var(8),get_value(20))
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
    name tinytext NOT NULL,
    page_id int(11) DEFAULT '0' NOT NULL,
    pagezone_table tinytext NOT NULL,
    pagezone_uid int(11) DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY page_id (page_id)
) ENGINE=InnoDB;

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
    pagezone_id int(11) DEFAULT '0' NOT NULL
    extras int(11) DEFAULT '0' NOT NULL,
    template_set tinytext NOT NULL,
    inherits_from int(11) DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
) ENGINE=InnoDB;

#
# Table structure for table 'tx_newspaper_pagezone_page_extras_mm'
#
CREATE TABLE tx_newspaper_pagezone_page_extras_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
) ENGINE=InnoDB;

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
    type_name tinytext NOT NULL,
    normalized_name tinytext NOT NULL,
    is_article tinyint(3) DEFAULT '0' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

### S ##########################################################################

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
    section_name tinytext NOT NULL,
    show_in_list tinyint(3) DEFAULT '0' NOT NULL,
    parent_section int(11) DEFAULT '0' NOT NULL,
    default_articletype int(11) DEFAULT '0' NOT NULL,
    pagetype_pagezone tinytext NOT NULL,
    articlelist int(11) DEFAULT '0' NOT NULL,
    template_set tinytext NOT NULL,
    description text NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY parent_section (parent_section),
    KEY articlelist (articlelist)
) ENGINE=InnoDB;

#
# Table structure for table 'tx_newspaper_specialhit'
#
CREATE TABLE tx_newspaper_specialhit (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    sorting int(10) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    title tinytext NOT NULL,
    teaser text NOT NULL,
    words tinytext NOT NULL,
    url tinytext NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

### T ##########################################################################

#
# Table structure for table 'tx_newspaper_tag'
#
CREATE TABLE tx_newspaper_tag (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    tag_type int(11) DEFAULT '0' NOT NULL,
    title tinytext NOT NULL,
    tag tinytext NOT NULL,
    ctrltag_cat int(11) DEFAULT '0' NOT NULL,
    section int(11) DEFAULT '0' NOT NULL,
    deactivated tinyint(3) DEFAULT '0' NOT NULL,
    
    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY section (section),
    KEY ctrltag_cat (ctrltag_cat)
);

#
# Table structure for table 'tx_newspaper_tag_zone'
#
CREATE TABLE tx_newspaper_tag_zone (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    name tinytext NOT NULL,
    
    PRIMARY KEY (uid),
    KEY parent (pid)
);
