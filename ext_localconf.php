<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_newspaper_pi1.php', '_pi1', 'list_type', 1);

t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_newspaper_section=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_newspaper_page=1
');
t3lib_extMgm::addPageTSConfig('

	# ***************************************************************************************
	# CONFIGURATION of RTE in table "tx_newspaper_article", field "text_910b25c266"
	# ***************************************************************************************
RTE.config.tx_newspaper_article.text_910b25c266 {
  hidePStyleItems = H1, H4, H5, H6
  proc.exitHTMLparser_db=1
  proc.exitHTMLparser_db {
    keepNonMatchedTags=1
    tags.font.allowedAttribs= color
    tags.font.rmTagIfNoAttrib = 1
    tags.font.nesting = global
  }
}
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_newspaper_pagetype=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_newspaper_pagezonetype=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_newspaper_articletype=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_newspaper_extra_textbox=1
');
t3lib_extMgm::addPageTSConfig('

	# ***************************************************************************************
	# CONFIGURATION of RTE in table "tx_newspaper_extra_textbox", field "text_a431c4d31b"
	# ***************************************************************************************
RTE.config.tx_newspaper_extra_textbox.text_a431c4d31b {
  hidePStyleItems = H1, H4, H5, H6
  proc.exitHTMLparser_db=1
  proc.exitHTMLparser_db {
    keepNonMatchedTags=1
    tags.font.allowedAttribs= color
    tags.font.rmTagIfNoAttrib = 1
    tags.font.nesting = global
  }
}
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_newspaper_externallinks=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_newspaper_tag=1
');
t3lib_extMgm::addPageTSConfig('

	# ***************************************************************************************
	# CONFIGURATION of RTE in table "tx_newspaper_extra_bio", field "bio_text"
	# ***************************************************************************************
RTE.config.tx_newspaper_extra_bio.bio_text {
  hidePStyleItems = H1, H4, H5, H6
  proc.exitHTMLparser_db=1
  proc.exitHTMLparser_db {
    keepNonMatchedTags=1
    tags.font.allowedAttribs= color
    tags.font.rmTagIfNoAttrib = 1
    tags.font.nesting = global
  }
}
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_newspaper_tag_zone=1
');
require_once(PATH_typo3conf . 'ext/newspaper/ext_localconf_addon.php');
?>
