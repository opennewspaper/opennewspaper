<?php

	// DO NOT REMOVE OR CHANGE THESE 2 LINES:
$MCONF['name'] = 'txnewspaperMmain_txnewspaperM9';
$MCONF['script'] = '_DISPATCH';

define('TYPO3_MOD_PATH', '../typo3conf/ext/newspaper/mod9/');
$BACK_PATH = '../../../../typo3/';
	
$MCONF['access'] = 'user,group';
$MCONF['navFrameScript']='class.tx_newspaper_sectiontree_mod9.php';

$MLANG['default']['tabs_images']['tab'] = 'moduleicon.gif';
$MLANG['default']['ll_ref'] = 'LLL:EXT:newspaper/mod9/locallang_mod.xml';

?>