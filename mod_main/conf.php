<?php

	// DO NOT REMOVE OR CHANGE THESE 3 LINES:
define('TYPO3_MOD_PATH', '../typo3conf/ext/newspaper/mod_main/');
$BACK_PATH='../../../../typo3/';
$MCONF['name']='txtazmodulMmain';

	
$MCONF['access']='user,group';

$MLANG['default']['tabs_images']['tab'] = 'moduleicon.gif';
$MLANG['default']['ll_ref']='LLL:EXT:newspaper/mod_main/locallang_mod.xml';


$MCONF['navFrameScript']='../mod3/class.tx_newspaper_SectionTree.php';
?>