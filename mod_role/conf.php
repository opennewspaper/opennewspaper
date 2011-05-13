<?php
define('TYPO3_MOD_PATH', '../typo3conf/ext/newspaper/mod_role/');
$BACK_PATH='../../../';

$MLANG['default']['tabs_images']['tab'] = 'document.gif';
$MLANG['default']['ll_ref'] = 'LLL:EXT:newspaper/mod_role/locallang_mod.php';

$MCONF['script'] = $BACK_PATH . 'alt_doc.php';
$MCONF['access'] = 'group,user';
$MCONF['name'] = 'newspaper_role';
?>
