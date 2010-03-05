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
	
	
	
	
	
	// hide some fields
	// hide some tables
	t3lib_extMgm::addPageTSConfig('
		TCEFORM.tx_newspaper_article.hidden.disabled = 1 
		TCEFORM.tx_newspaper_article.source_id.disabled = 1
		TCEFORM.tx_newspaper_article.source_object.disabled = 1
		TCEFORM.tx_newspaper_article.is_template.disabled = 1
		TCEFORM.tx_newspaper_article.pagezonetype_id.disabled = 1
		TCEFORM.tx_newspaper_article.name.disabled = 1
		TCEFORM.tx_newspaper_article.inherits_from.disabled = 1
		TCEFORM.pages.tx_newspaper_module.disabled = 1

		mod.web_list.hideTables = tx_newspaper_extra,tx_newspaper_page,tx_newspaper_pagezone,tx_newspaper_pagezone_page
				
		user.options.hideRecords.pages = ' . $sysfolder . '
	');
	
	// hide template sets, if they shouldn't be used
	if (!tx_newspaper::USE_TEMPLATE_SETS) {
		t3lib_extMgm::addPageTSConfig('
			TCEFORM.tx_newspaper_section.template_set.disabled = 1 
			TCEFORM.tx_newspaper_page.template_set.disabled = 1 
			TCEFORM.tx_newspaper_pagezone_page.template_set.disabled = 1 
			TCEFORM.tx_newspaper_article.template_set.disabled = 1 
		');
	}
	
	// set start module
	t3lib_extMgm::addUserTSConfig('
		setup.default.startModule = txnewspaperMmain_txnewspaperM5
		setup.override.startModule = txnewspaperMmain_txnewspaperM5
	');
	
?>