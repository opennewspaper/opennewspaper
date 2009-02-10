<?php

// modifications after generating with the kickstarter (at bottom of file ext_tables.php)
// require_once(PATH_typo3conf . 'ext/newspaper/ext_tables_addon.php');

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper.php');

// overwrite data set in ext_tables.php

// make field a userFunc field (displaying a list of associated extras)
$TCA['tt_content']['columns']['tx_newspaper_extra']['config']['type'] = 'user';
$TCA['tt_content']['columns']['tx_newspaper_extra']['config']['userFunc'] = 'tx_newspaper->renderList';


if (TYPO3_MODE == 'BE') {
	$sysfolder = tx_newspaper_Sysfolder::getInstance();
	$pid = $sysfolder->getPid(new tx_newspaper());
	
	$tsconfig = t3lib_BEfunc::getPagesTSconfig($pid);
#t3lib_div::devlog('tsc', 'newspaper', 0, $tsconfig);

	// read other pages to be hidden
	if (isset($tsconfig['options.']['hideRecords.']['pages']) && $tsconfig['tx_newspaper.']['showFolder']) {
		$pid .= ',' . $tsconfig['options.']['hideRecords.']['pages']; 
	}

	if (
		!isset($tsconfig['tx_newspaper.']['showFolder']) || 
		(isset($tsconfig['tx_newspaper.']['showFolder']) && $tsconfig['tx_newspaper.']['showFolder'] != 1)
	) {
		t3lib_extMgm::addUserTSConfig('
			options.hideRecords.pages = ' . $pid . '
		');
	}

}

?>