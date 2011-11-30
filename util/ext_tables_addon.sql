








#
# ext_tables_addon.sql
#


CREATE TABLE tx_newspaper_section (
	template_set tinytext NOT NULL,
	KEY parent_section (parent_section),
	KEY articlelist (articlelist)
);

CREATE TABLE tx_newspaper_page (
	section int(11) DEFAULT '0' NOT NULL,
	pagetype_id int(11) DEFAULT '0' NOT NULL,
	template_set tinytext NOT NULL,
	KEY section (section),
	KEY pagetype_id (pagetype_id),
	KEY inherit_pagetype_id (inherit_pagetype_id)
);

CREATE TABLE tx_newspaper_pagezone (
	page_id int(11) DEFAULT '0' NOT NULL,
	KEY page_id (page_id)
);

CREATE TABLE tx_newspaper_pagezone_page (
	template_set tinytext NOT NULL,
	pagezone_id int(11) DEFAULT '0' NOT NULL
);

CREATE TABLE tx_newspaper_article (
	modification_user text NOT NULL,
    bodytext longtext NOT NULL,
	template_set tinytext NOT NULL,
	FULLTEXT KEY title (title,kicker,title_list,kicker_list),
	FULLTEXT KEY text (teaser,teaser_list,bodytext,author),
	KEY articletype_id (articletype_id)
);

CREATE TABLE tx_newspaper_articlelist (
	section_id int(11) DEFAULT '0' NOT NULL,
	KEY section_id (section_id)
);

CREATE TABLE tx_newspaper_controltag_to_extra (
	tag int(11) DEFAULT '0' NOT NULL,
	tag_zone int(11) DEFAULT '0' NOT NULL,
	extra text NOT NULL,
	KEY tag (tag),
	KEY tag_zone (tag_zone)
);

CREATE TABLE tx_newspaper_articlelist_semiautomatic_articles_mm (
	offset int(11) DEFAULT '0' NOT NULL
);

CREATE TABLE tx_newspaper_tag (
	section int(11) DEFAULT '0' NOT NULL,
	KEY section (section),
	KEY ctrltag_cat (ctrltag_cat)
);

CREATE TABLE tx_newspaper_extra (
	KEY origin_uid (origin_uid)
);

CREATE TABLE tx_newspaper_extra_articlelist (
	articlelist int(11) DEFAULT '0' NOT NULL,
	image text NOT NULL,
	KEY articlelist (articlelist)
);

CREATE TABLE tx_newspaper_extra_controltagzone (
	default_extra text NOT NULL,
	KEY tag_zone (tag_zone)
);

CREATE TABLE tx_newspaper_extra_sectionteaser (
	KEY ctrltag (ctrltag),
	KEY section (section)
);

# Fulltext index for Extra: Image
CREATE TABLE tx_newspaper_extra_image (
	image_file text NOT NULL,
	FULLTEXT KEY title (title,kicker,caption)
);

# Fulltext index for Extra: Textbox
CREATE TABLE tx_newspaper_extra_textbox (
	image text NOT NULL,
	FULLTEXT KEY title (title,bodytext)
);

# Fulltext index for Extra: Bio
CREATE TABLE tx_newspaper_extra_bio (
	image_file text NOT NULL,
	FULLTEXT KEY title (author_name,bio_text)
);

# Index on pagetype.get_var to speed up lookup of page types
CREATE TABLE tx_newspaper_pagetype (
  INDEX get_var (get_var(8)),
  INDEX get_value (get_value(20)),
  INDEX get_params (get_var(8),get_value(20))
);

# InnoDB for log
CREATE TABLE tx_newspaper_log (
	be_user text NOT NULL,
	INDEX table_key (table_name(30),table_uid)
) ENGINE=InnoDB;

CREATE TABLE tx_newspaper_extra_typo3_ce (
	content_elements text NOT NULL
);

CREATE TABLE tx_newspaper_extra_externallinks (
	links text NOT NULL
);

CREATE TABLE tx_newspaper_articlelist_manual (
	filter_sections text NOT NULL,
	filter_tags_include text NOT NULL,
	filter_tags_exclude text NOT NULL,
	filter_articlelist_exclude text NOT NULL
);

CREATE TABLE tx_newspaper_articlelist_semiautomatic (
	filter_sections text NOT NULL,
	filter_tags_include text NOT NULL,
	filter_tags_exclude text NOT NULL,
	filter_articlelist_exclude text NOT NULL
);

CREATE TABLE tx_newspaper_comment_cache (
	article text NOT NULL
);

CREATE TABLE tx_newspaper_extra_combolinkbox (
	manually_selected_articles text NOT NULL,
	internal_links text NOT NULL,
	external_links text NOT NULL
);

CREATE TABLE tx_newspaper_extra_searchresults (
	sections text NOT NULL,
	tags text NOT NULL
);

CREATE TABLE tx_newspaper_extra_container (
	extras text NOT NULL
);

CREATE TABLE pages (
	tx_newspaper_associated_section text NOT NULL
);
