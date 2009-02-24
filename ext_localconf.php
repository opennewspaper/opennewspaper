<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_newspaper_pi1 = < plugin.tx_newspaper_pi1.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_newspaper_pi1.php','_pi1','list_type',1);

t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_newspaper_section=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_newspaper_page=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_newspaper_pagezone=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_newspaper_article=1
');
t3lib_extMgm::addPageTSConfig('

	# ***************************************************************************************
	# CONFIGURATION of RTE in table "tx_newspaper_article", field "text"
	# ***************************************************************************************
RTE.config.tx_newspaper_article.text {
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
	options.saveDocNew.tx_newspaper_extra=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_newspaper_extra_articlerenderer=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_newspaper_extra_sectionlist=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_newspaper_articlelist=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_newspaper_articlelist_auto=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_newspaper_pagetype=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_newspaper_pagezonetype=1
');
?>