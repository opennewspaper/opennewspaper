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
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getMainFieldsClass'][] = 'EXT:newspaper/classes/class.tx_newspaper_typo3hook.php:tx_newspaper_Typo3Hook';

	// register hook to add javascript and css to BE (loaded to top)
	$GLOBALS['TYPO3_CONF_VARS']['typo3/backend.php']['additionalBackendItems'][] = PATH_typo3conf . 'ext/newspaper/res/be/additionalBackendItems.php';

	// register list module hook
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/class.db_list_extra.inc']['getTable'][] = 'EXT:newspaper/classes/class.tx_newspaper_typo3hook.php:tx_newspaper_Typo3Hook';

	// register extension manager hook
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/mod/tools/em/index.php']['tsStyleConfigForm'][] = 'EXT:newspaper/classes/class.tx_newspaper_typo3hook.php:tx_newspaper_Typo3Hook->tsStyleConfigForm';


	// register log off hook
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_pre_processing'][] = 'tx_newspaper_BE->cleanUpBeforeLogoff';


	// XCLASS extending typo3/alt_doc.php
	$GLOBALS['TYPO3_CONF_VARS']['BE']['XCLASS']['typo3/alt_doc.php'] = t3lib_extMgm::extPath('newspaper') . 'classes/class.ux_alt_doc.php';



	if (TYPO3_MODE == 'BE') {

		// some backend modifications

		$sysfolder = ''; // \todo, see #603

		// hide some fields
		t3lib_extMgm::addPageTSConfig('
			TCEFORM.tx_newspaper_article.hidden.disabled = 1
			TCEFORM.tx_newspaper_article.source_id.disabled = 1
			TCEFORM.tx_newspaper_article.source_object.disabled = 1
			TCEFORM.tx_newspaper_article.is_template.disabled = 1
			TCEFORM.tx_newspaper_article.pagezonetype_id.disabled = 1
			TCEFORM.tx_newspaper_article.name.disabled = 1
			TCEFORM.tx_newspaper_article.inherits_from.disabled = 1
			TCEFORM.tx_newspaper_article.modification_user.disabled = 1
			TCEFORM.tx_newspaper_articlelist.section_id.disabled = 1
			TCEFORM.pages.tx_newspaper_module.disabled = 1

			// hide filter fields for manuel articlelist (activate when article browser can use filters)
			TCEFORM.tx_newspaper_articlelist_manual.filter_sections.disabled = 1
			TCEFORM.tx_newspaper_articlelist_manual.filter_tags_include.disabled = 1
			TCEFORM.tx_newspaper_articlelist_manual.filter_tags_exclude.disabled = 1
			TCEFORM.tx_newspaper_articlelist_manual.filter_articlelist_exclude.disabled = 1
			TCEFORM.tx_newspaper_articlelist_manual.filter_sql_table.disabled = 1
			TCEFORM.tx_newspaper_articlelist_manual.filter_sql_where.disabled = 1
			TCEFORM.tx_newspaper_articlelist_manual.filter_sql_order_by.disabled = 1

			// hide starttime and endtime for abstract articlelist (\todo: remove in kickstarter; what could be a useful usage?)
			TCEFORM.tx_newspaper_articlelist.starttime.disabled = 1
			TCEFORM.tx_newspaper_articlelist.endtime.disabled = 1
			TCEFORM.tx_newspaper_articlelist.hidden.disabled = 1

			// hide starttime and endtime for extras
			TCEFORM.tx_newspaper_extra_ad.starttime.disabled = 1
			TCEFORM.tx_newspaper_extra_ad.endtime.disabled = 1
			TCEFORM.tx_newspaper_extra_ad.hidden.disabled = 1
			TCEFORM.tx_newspaper_extra_articlelinks.hidden.disabled = 1
			TCEFORM.tx_newspaper_extra_articlelist.starttime.disabled = 1
			TCEFORM.tx_newspaper_extra_articlelist.endtime.disabled = 1
			TCEFORM.tx_newspaper_extra_bio.starttime.disabled = 1
			TCEFORM.tx_newspaper_extra_bio.endtime.disabled = 1
			TCEFORM.tx_newspaper_extra_bio.hidden.disabled = 1
			TCEFORM.tx_newspaper_extra_combolinkbox.starttime.disabled = 1
			TCEFORM.tx_newspaper_extra_combolinkbox.endtime.disabled = 1
			TCEFORM.tx_newspaper_extra_combolinkbox.hidden.disabled = 1
			TCEFORM.tx_newspaper_extra_container.starttime.disabled = 1
			TCEFORM.tx_newspaper_extra_container.endtime.disabled = 1
			TCEFORM.tx_newspaper_extra_container.hidden.disabled = 1
			TCEFORM.tx_newspaper_extra_controltagzone.starttime.disabled = 1
			TCEFORM.tx_newspaper_extra_controltagzone.endtime.disabled = 1
			TCEFORM.tx_newspaper_extra_controltagzone.hidden.disabled = 1
			TCEFORM.tx_newspaper_extra_displayarticles.starttime.disabled = 1
			TCEFORM.tx_newspaper_extra_displayarticles.endtime.disabled = 1
			TCEFORM.tx_newspaper_extra_externallinks.starttime.disabled = 1
			TCEFORM.tx_newspaper_extra_externallinks.endtime.disabled = 1
			TCEFORM.tx_newspaper_extra_generic.starttime.disabled = 1
			TCEFORM.tx_newspaper_extra_generic.endtime.disabled = 1
			TCEFORM.tx_newspaper_extra_generic.hidden.disabled = 1
			TCEFORM.tx_newspaper_extra_image.starttime.disabled = 1
			TCEFORM.tx_newspaper_extra_image.endtime.disabled = 1
			TCEFORM.tx_newspaper_extra_image.hidden.disabled = 1
			TCEFORM.tx_newspaper_extra_mostcommented.starttime.disabled = 1
			TCEFORM.tx_newspaper_extra_mostcommented.endtime.disabled = 1
			TCEFORM.tx_newspaper_extra_mostcommented.hidden.disabled = 1
			TCEFORM.tx_newspaper_extra_searchresults.starttime.disabled = 1
			TCEFORM.tx_newspaper_extra_searchresults.endtime.disabled = 1
			TCEFORM.tx_newspaper_extra_sectionlist.starttime.disabled = 1
			TCEFORM.tx_newspaper_extra_sectionlist.endtime.disabled = 1
			TCEFORM.tx_newspaper_extra_sectionlist.hidden.disabled = 1
			TCEFORM.tx_newspaper_extra_textbox.starttime.disabled = 1
			TCEFORM.tx_newspaper_extra_textbox.endtime.disabled = 1
			TCEFORM.tx_newspaper_extra_typo3_ce.starttime.disabled = 1
			TCEFORM.tx_newspaper_extra_typo3_ce.endtime.disabled = 1
			TCEFORM.tx_newspaper_extra_typo3_ce.hidden.disabled = 1
	
			// \todo enable after functionality is implemented, see #1111		
			TCEFORM.tx_newspaper_articlelist_semiautomatic.subsequent_sections.disabled = 1

			// \todo enable after functionality is available, see #943
			TCEFORM.tx_newspaper_extra_textbox.image.disabled = 1

			// \todo: see #1520
			TCEFORM.tx_newspaper_pagetype.is_article_page.disabled = 1

			mod.web_list.hideTables = tx_newspaper_extra,tx_newspaper_page,tx_newspaper_pagezone,tx_newspaper_pagezone_page

			user.options.hideRecords.pages = ' . $sysfolder . '
		');

		// hide template sets in sections, if they shouldn't be used
		if (!tx_newspaper_be::useTemplateSetsForSections()) {
			t3lib_extMgm::addPageTSConfig('
				TCEFORM.tx_newspaper_section.template_set.disabled = 1
			');
		}
		// always hide template sets in article
		t3lib_extMgm::addPageTSConfig('
			TCEFORM.tx_newspaper_article.template_set.disabled = 1
		');



		// set start module
		t3lib_extMgm::addUserTSConfig('
			setup.default.startModule = txnewspaperMmain_txnewspaperM2
			setup.override.startModule = txnewspaperMmain_txnewspaperM2
		');


		// remove some save&new buttons
		t3lib_extMgm::addUserTSConfig('
			options.saveDocNew.tx_newspaper_article=0
		');
	}


//$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_recordlocking.php']['pre_release_lock'] = 'tx_newspaper_Typo3Hook->releaseLocks';
?>