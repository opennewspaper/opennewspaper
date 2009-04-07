<?php

// modifications after generating with the kickstarter (at bottom of file ext_tables.php)
// require_once(PATH_typo3conf . 'ext/newspaper/ext_tables_addon.php');

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper.php');

// overwrite data set in ext_tables.php

// make Extra field (created by kickstrater) a userFunc field (displaying a list of associated Extras)
# might be needed if Extras will be available for tt_content too
#$TCA['tt_content']['columns']['tx_newspaper_extra']['config']['type'] = 'user';
#$TCA['tt_content']['columns']['tx_newspaper_extra']['config']['userFunc'] = 'tx_newspaper->renderList';


if (TYPO3_MODE == 'BE') {
/// \to do: hide sysfolder (with user tsconfig): options.hideRecords.pages	
	
	$tempColumns["tx_newspaper_associated_section"]["config"]["range"] = array();
	t3lib_extMgm::addTCAcolumns("pages",$tempColumns,1);

	// add main module 'newspaper', add sub modules
	t3lib_extMgm::addModule('txnewspaperMmain','','',t3lib_extMgm::extPath($_EXTKEY).'mod_main/'); // main
	t3lib_extMgm::addModule('txnewspaperMmain','txnewspaperM1','',t3lib_extMgm::extPath($_EXTKEY).'mod1/'); // AJAX stuff
	t3lib_extMgm::addModule('txnewspaperMmain','txnewspaperM2','',t3lib_extMgm::extPath($_EXTKEY).'mod2/'); // moderation list (workflow)


	/// add newspaper to Plugin-in list
	/// records are stored in sysfolders with module set to 'newspaper'
	$TCA['pages']['columns']['module']['config']['items'][] = array('Newspaper', 'newspaper');
	
	/// add icon for newspaper sysfolders
	$ICON_TYPES['newspaper'] = array('icon' => t3lib_extMgm::extRelPath($_EXTKEY) . 'icon_tx_newspaper_sysf.gif');


}

?>