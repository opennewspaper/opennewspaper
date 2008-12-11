<?php

// modifications after generating with the kickstarter (at bottom of file ext_tables.php)
// require_once(PATH_typo3conf . 'ext/extra/ext_tables_addon.php');



// overwrite data set in ext_tables.php

// make field a userFunc field (displaying a list of associated extras)
$TCA['tt_content']['columns']['tx_extra_extra']['config']['type'] = 'user';
$TCA['tt_content']['columns']['tx_extra_extra']['config']['userFunc'] = 'tx_extra->renderList';



// include some classes
// TODO: check if needed in "real" class structure
require_once('class.extra.php'); // extra class
require_once('class.tx_extra.php'); // class for extras in be

?>