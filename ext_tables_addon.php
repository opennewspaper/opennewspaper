<?php

// modifications after generating with the kickstarter (at bottom of file ext_tables.php)
// require_once(PATH_typo3conf . 'ext/newspaper/ext_tables_addon.php');

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper.php');

// overwrite data set in ext_tables.php

// set path to icon files
$TCA['tx_newspaper_extra_image']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_extra_image.gif';
$TCA['tx_newspaper_section']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_section.gif';
$TCA['tx_newspaper_page']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_page.gif';
$TCA['tx_newspaper_pagezone']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_pagezone.gif';
$TCA['tx_newspaper_pagezone_page']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_pagezone_page.gif';
$TCA['tx_newspaper_article']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_article.gif';
$TCA['tx_newspaper_extra']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_extra.gif';
$TCA['tx_newspaper_extra_sectionlist']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_extra_sectionlist.gif';
$TCA['tx_newspaper_articlelist']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_articlelist.gif';
$TCA['tx_newspaper_pagetype']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_pagetype.gif';
$TCA['tx_newspaper_pagezonetype']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_pagezonetype.gif';
$TCA['tx_newspaper_log']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_log.gif';
$TCA['tx_newspaper_articletype']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_articletype.gif';
$TCA['tx_newspaper_articlelist_manual']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_articlelist_manual.gif';
$TCA['tx_newspaper_articlelist_semiautomatic']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_articlelist_semiautomatic.gif';
$TCA['tx_newspaper_comment_cache']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_comment_cache.gif';
$TCA['tx_newspaper_externallinks']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_externallinks.gif';
$TCA['tx_newspaper_extra_articlelist']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_extra_articlelist.gif';
$TCA['tx_newspaper_extra_bio']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_extra_bio.gif';
$TCA['tx_newspaper_extra_displayarticles']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_extra_displayarticles.gif';
$TCA['tx_newspaper_extra_externallinks']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_extra_externallinks.gif';
$TCA['tx_newspaper_extra_mostcommented']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_extra_mostcommented.gif';
$TCA['tx_newspaper_extra_textbox']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_extra_textbox.gif';
$TCA['tx_newspaper_extra_typo3_ce']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_extra_typo3_ce.gif';
$TCA['tx_newspaper_tag']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_tag.gif';
$TCA['tx_newspaper_controltag_to_extra']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_controltag_to_extra.gif';
$TCA['tx_newspaper_extra_combolinkbox']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_extra_combolinkbox.gif';
$TCA['tx_newspaper_extra_container']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_extra_container.gif';
$TCA['tx_newspaper_extra_controltagzone']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_extra_controltagzone.gif';
$TCA['tx_newspaper_extra_searchresults']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_extra_searchresults.gif';
$TCA['tx_newspaper_tag_zone']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_tag_zone.gif';
$TCA['tx_newspaper_tag_type']['ctrl']['iconfile'] =
	t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_tag_type.gif';

if (TYPO3_MODE == 'BE') {
	
	// remove range check for set range for role in be_users in table pages
	unset($TCA['pages']['columns']['set range for role in be_users']['config']['range']);

	// add main module 'newspaper', add sub modules
	t3lib_extMgm::addModule('txnewspaperMmain','','',t3lib_extMgm::extPath($_EXTKEY).'mod_main/'); // main
	t3lib_extMgm::addModule('txnewspaperMmain','txnewspaperM5','',t3lib_extMgm::extPath($_EXTKEY).'mod5/'); // wizards
	t3lib_extMgm::addModule('txnewspaperMmain','txnewspaperM2','',t3lib_extMgm::extPath($_EXTKEY).'mod2/'); // moderation list (workflow)
	t3lib_extMgm::addModule('txnewspaperMmain','txnewspaperM3','',t3lib_extMgm::extPath($_EXTKEY).'mod3/'); // placement
    t3lib_extMgm::addModule('txnewspaperMmain','txnewspaperM8','',t3lib_extMgm::extPath($_EXTKEY).'mod8/'); // tag admin
	t3lib_extMgm::addModule('txnewspaperMmain','txnewspaperM6','',t3lib_extMgm::extPath($_EXTKEY).'mod6/'); // control tags
	t3lib_extMgm::addModule('txnewspaperMmain','txnewspaperM9','',t3lib_extMgm::extPath($_EXTKEY).'mod9/'); // placing articles in section article lists
	t3lib_extMgm::addModule('txnewspaperMmain','txnewspaperM7','',t3lib_extMgm::extPath($_EXTKEY).'mod7/'); // placing articles in article lists (non-section) + backend for manual and semiatumatic article lists
	t3lib_extMgm::addModule('txnewspaperMmain','txnewspaperM4','',t3lib_extMgm::extPath($_EXTKEY).'mod4/'); // admin tools
	t3lib_extMgm::addModule('txnewspaperMmain','txnewspaperM1','',t3lib_extMgm::extPath($_EXTKEY).'mod1/'); // AJAX stuff

	/// add newspaper to Plugin-in list
	/// records are stored in sysfolders with module set to 'newspaper'
	$TCA['pages']['columns']['module']['config']['items'][] = array('Newspaper', 'newspaper');
	
	/// add icon for newspaper sysfolders
	$ICON_TYPES['newspaper'] = array('icon' => t3lib_extMgm::extRelPath($_EXTKEY) . 'res/icons/icon_tx_newspaper_sysf.gif');

	// force reloading article backend when article type changes
	$GLOBALS['TCA']['tx_newspaper_article']['ctrl']['requestUpdate'] .= ',articletype_id';
	

// set range for role in be_users
	$TCA['be_users']['columns']['tx_newspaper_role']['config']['range'] = array (
		"lower" => "0",
		"upper" => "1000" // NP_ACTIVE_ROLE_NONE
	);

}

?>