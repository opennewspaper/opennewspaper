
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
    bodytext longtext NOT NULL
	template_set tinytext NOT NULL
	FULLTEXT KEY title (title,kicker,title_list,kicker_list)
	FULLTEXT KEY text (teaser,teaser_list,bodytext,author)
);

# Modifications for table 'tx_newspaper_articlelist_semiautomatic_articles_mm'
CREATE TABLE tx_newspaper_articlelist_semiautomatic_articles_mm (
	offset int(11) DEFAULT '0' NOT NULL
);

# Fulltext index for Extra: Image
CREATE TABLE tx_newspaper_extra_image (
  FULLTEXT KEY title (title,kicker,caption)
);

# Fulltext index for Extra: Textbox
CREATE TABLE tx_newspaper_extra_textbox (
  FULLTEXT KEY title (title,bodytext)
);

# Fulltext index for Extra: Image
CREATE TABLE tx_newspaper_extra_bio (
  FULLTEXT KEY title (author_name,bio_text)
);

# Index on pagetype.get_var to speed up lookup of page types
CREATE TABLE tx_newspaper_pagetype (
  INDEX get_var (get_var(8))
);


# (kickstarter 0.3.8) blob -> text
CREATE TABLE tx_newspaper_extra_image (
	image_file text NOT NULL,
);
CREATE TABLE tx_newspaper_page (
	section text NOT NULL,
	pagetype_id text NOT NULL
);
CREATE TABLE tx_newspaper_pagezone (
	page_id text NOT NULL
);
CREATE TABLE tx_newspaper_article (
	modification_user text NOT NULL
);
CREATE TABLE tx_newspaper_articlelist (
	section_id text NOT NULL
);
CREATE TABLE tx_newspaper_log (
	be_user text NOT NULL
);
CREATE TABLE tx_newspaper_extra_typo3_ce (
	content_elements text NOT NULL
);
CREATE TABLE tx_newspaper_extra_articlelist (
	articlelist text NOT NULL,
	image text NOT NULL
);
CREATE TABLE tx_newspaper_extra_textbox (
	image text NOT NULL
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
CREATE TABLE tx_newspaper_tag (
	section text NOT NULL
);
CREATE TABLE tx_newspaper_comment_cache (
	article text NOT NULL
);
CREATE TABLE tx_newspaper_extra_bio (
	image_file text NOT NULL
);
CREATE TABLE tx_newspaper_extra_controltagzone (
	default_extra text NOT NULL
);
CREATE TABLE tx_newspaper_controltag_to_extra (
	tag text NOT NULL,
	tag_zone text NOT NULL,
	extra text NOT NULL
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



CREATE TABLE tx_newspaper_log (
	INDEX table_key (table_name(30), table_uid)
) ENGINE=InnoDB;

