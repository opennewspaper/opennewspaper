<?php

// modifications after generating with the kickstarter (bottom of file ext_localconf.php)
// require_once(PATH_typo3conf . 'ext/newspaper/ext_localconf_addon.php');


	// include newspaper classes and interfaces
	require_once(PATH_typo3conf . 'ext/newspaper/tx_newspaper_include.php');


	// register tcemain hooks (so called save hooks)
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:newspaper/classes/class.tx_newspaper_typo3hook.php:tx_newspaper_Typo3Hook';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] = 'EXT:newspaper/classes/class.tx_newspaper_typo3hook.php:tx_newspaper_Typo3Hook';

	// register tceforms hooks
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getSingleFieldClass'][] = 'EXT:newspaper/classes/class.tx_newspaper_typo3hook.php:tx_newspaper_Typo3Hook';

	// register hook to add javascript and css to BE (loaded to top)
	$GLOBALS['TYPO3_CONF_VARS']['typo3/backend.php']['additionalBackendItems'][] = PATH_typo3conf . 'ext/newspaper/res/be/additionalBackendItems.php';

	// register list module hook
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/class.db_list_extra.inc']['getTable'][] = 'EXT:newspaper/classes/class.tx_newspaper_typo3hook.php:tx_newspaper_Typo3Hook';

	// register extension manager hook
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/mod/tools/em/index.php']['tsStyleConfigForm'][] = 'EXT:newspaper/classes/class.tx_newspaper_typo3hook.php:tx_newspaper_Typo3Hook->tsStyleConfigForm';
	
?>