<?php

if (!defined('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE == 'BE') {

		// first include the class file
	include_once(t3lib_extMgm::extPath('newspaper').'mod_role/class.tx_newspaper_role.php');

		// now register the class as toolbar item
	$GLOBALS['TYPO3backend']->addToolbarItem('newspaper_role', 'tx_newspaper_role');
}

?>