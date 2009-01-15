<?php

// modifications after generating with the kickstarter (bottom of file ext_localconf.php)
// require_once(PATH_typo3conf . 'ext/newspaper/ext_localconf_addon.php');


	// register save hook
//TODO still neeeded???
	$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:newspaper/util/class.savehook.php:user_savehook_extra';


	// register hook to add javascript and css to BE (loaded to top)
	$GLOBALS['TYPO3_CONF_VARS']['typo3/backend.php']['additionalBackendItems'][] = PATH_typo3conf . 'ext/newspaper/util/additionalBackendItems.php';





// this is just test: import and register in tca only doesn't make Extras available in content (article or tt_content)
require_once(BASEPATH . '/typo3conf/ext/newspaper/classes/class.tx_newspaper_extraimpl.php');
// Extra: Image
require_once(BASEPATH . '/typo3conf/ext/newspaper/classes/extra/class.tx_newspaper_extra_image.php');
tx_newspaper_ExtraImpl::registerExtra(new tx_newspaper_extra_image()); // register Extra "Image"


?>
