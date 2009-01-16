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
	options.saveDocNew.tx_newspaper_pagezone_page=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_newspaper_pagezone_article=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_newspaper_article=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_newspaper_extra=1
');
?>