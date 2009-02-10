<?php

// modifications after generating with the kickstarter (at bottom of file ext_tables.php)
// require_once(PATH_typo3conf . 'ext/newspaper/ext_tables_addon.php');

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper.php');

// overwrite data set in ext_tables.php

// make field a userFunc field (displaying a list of associated extras)
$TCA['tt_content']['columns']['tx_newspaper_extra']['config']['type'] = 'user';
$TCA['tt_content']['columns']['tx_newspaper_extra']['config']['userFunc'] = 'tx_newspaper->renderList';


if (TYPO3_MODE == 'BE') {
/// \to do: hide sysfolder (with user tsconfig): options.hideRecords.pages	
}

?>