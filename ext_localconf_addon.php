<?php

// modifications after generating with the kickstarter (bottom of file ext_localconf.php)
// require_once(PATH_typo3conf . 'ext/newspaper/ext_localconf_addon.php');


	// register save hooks
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:newspaper/util/class.savehook.php:tx_newspaper_SaveHook';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] = 'EXT:newspaper/util/class.savehook.php:tx_newspaper_SaveHook';

	// register hook to add javascript and css to BE (loaded to top)
	$GLOBALS['TYPO3_CONF_VARS']['typo3/backend.php']['additionalBackendItems'][] = PATH_typo3conf . 'ext/newspaper/res/be/additionalBackendItems.php';

	// include newspaper classes and interfaces
	require_once(PATH_typo3conf . 'ext/newspaper/tx_newspaper_include.php');

	
?>