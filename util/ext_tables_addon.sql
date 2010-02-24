
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
    text LONGTEXT NOT NULL
);
CREATE TABLE tx_newspaper_article (
	template_set tinytext NOT NULL
);
CREATE TABLE tx_newspaper_article (
	kram TEXT
);
CREATE TABLE tx_newspaper_article (
	FULLTEXT KEY `title` (`title`, `kicker`, `title_list`, `kicker_list`)
);
CREATE TABLE tx_newspaper_article (
	FULLTEXT KEY `text` (`teaser`, `teaser_list`, `text`, `author`)
);

# Modifications for table 'tx_newspaper_articlelist_semiautomatic_articles_mm'
CREATE TABLE tx_newspaper_articlelist_semiautomatic_articles_mm (
	offset int(11) DEFAULT '0' NOT NULL
);

# Fulltext index for Extra: Image
CREATE TABLE `tx_newspaper_extra_image` (
  FULLTEXT KEY `title` (`title`,`kicker`,`caption`)
);

# Fulltext index for Extra: Textbox
CREATE TABLE `tx_newspaper_extra_textbox` (
  FULLTEXT KEY `title` (`title`,`text`)
);

# Fulltext index for Extra: Image
CREATE TABLE `tx_newspaper_extra_bio` (
  FULLTEXT KEY `title` (`author_name`, `bio_text`)
);

# Index on pagetype.get_var to speed up lookup of page types
CREATE TABLE `tx_newspaper_pagetype` (
  INDEX `get_var` ( `get_var` ( 8 ) )
); 