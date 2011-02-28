<?php

	// DO NOT REMOVE OR CHANGE THESE 3 LINES:
define('TYPO3_MOD_PATH', '../typo3conf/ext/newspaper/mod6/');
$BACK_PATH = '../../../../typo3/';
$MCONF['name'] = 'txnewspaperMmain_txnewspaperM6';


$MCONF['access'] = 'user,group';
$MCONF['script'] = 'index.php';

$MLANG['default']['tabs_images']['tab'] = 'moduleicon.gif';

// read locallang file (might be verwritten, hook)
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/newspaper']['mod4']['additionalLocallang']['module'])) {
	$MLANG['default']['ll_ref'] = 'LLL:EXT:newspaper/mod6/locallang_mod.xml';
} else {
	if (strpos($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/newspaper']['mod4']['additionalLocallang']['module'], 'LLL:') !== false) {
		$MLANG['default']['ll_ref'] = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/newspaper']['mod4']['additionalLocallang']['module'];
	} else {
		$MLANG['default']['ll_ref'] = 'LLL:' . $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/newspaper']['mod4']['additionalLocallang']['module'];
	}
}

?>