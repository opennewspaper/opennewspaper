
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

# Modifications for table 'tx_newspaper_articlelist_semiautomatic_articles_mm'
CREATE TABLE tx_newspaper_articlelist_semiautomatic_articles_mm (
	offset int(11) DEFAULT '0' NOT NULL
);
