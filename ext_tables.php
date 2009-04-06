<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';


t3lib_extMgm::addPlugin(array(
	'LLL:EXT:newspaper/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');


if (TYPO3_MODE == 'BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_newspaper_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_newspaper_pi1_wizicon.php';
}


if (TYPO3_MODE == 'BE') {
	t3lib_extMgm::addModulePath('web_txnewspaperM1', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
		
}


if (TYPO3_MODE == 'BE') {
	t3lib_extMgm::addModulePath('web_txnewspaperM2', t3lib_extMgm::extPath($_EXTKEY) . 'mod2/');
		
}


t3lib_extMgm::addToInsertRecords('tx_newspaper_extra_image');

$TCA['tx_newspaper_extra_image'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra_image.gif',
	),
);


t3lib_extMgm::addToInsertRecords('tx_newspaper_section');

$TCA['tx_newspaper_section'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_section',		
		'label'     => 'section_name',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_section.gif',
	),
);


t3lib_extMgm::addToInsertRecords('tx_newspaper_page');

$TCA['tx_newspaper_page'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_page',		
		'label'     => 'pagetype_id',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate',	
		'delete' => 'deleted',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_page.gif',
	),
);


t3lib_extMgm::allowTableOnStandardPages('tx_newspaper_pagezone');


t3lib_extMgm::addToInsertRecords('tx_newspaper_pagezone');

$TCA['tx_newspaper_pagezone'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagezone',		
		'label'     => 'name',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_pagezone.gif',
	),
);

$TCA['tx_newspaper_pagezone_page'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagezone_page',		
		'label'     => 'pagezone_id',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate',	
		'delete' => 'deleted',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_pagezone_page.gif',
	),
);

$TCA['tx_newspaper_article'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate DESC',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_article.gif',
	),
);


t3lib_extMgm::allowTableOnStandardPages('tx_newspaper_extra');


t3lib_extMgm::addToInsertRecords('tx_newspaper_extra');

$TCA['tx_newspaper_extra'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra',		
		'label'     => 'extra_uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra.gif',
	),
);


t3lib_extMgm::allowTableOnStandardPages('tx_newspaper_extra_sectionlist');


t3lib_extMgm::addToInsertRecords('tx_newspaper_extra_sectionlist');

$TCA['tx_newspaper_extra_sectionlist'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_sectionlist',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra_sectionlist.gif',
	),
);


t3lib_extMgm::allowTableOnStandardPages('tx_newspaper_articlelist');


t3lib_extMgm::addToInsertRecords('tx_newspaper_articlelist');

$TCA['tx_newspaper_articlelist'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_articlelist.gif',
	),
);


t3lib_extMgm::allowTableOnStandardPages('tx_newspaper_articlelist_auto');


t3lib_extMgm::addToInsertRecords('tx_newspaper_articlelist_auto');

$TCA['tx_newspaper_articlelist_auto'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist_auto',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_articlelist_auto.gif',
	),
);

$TCA['tx_newspaper_pagetype'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagetype',		
		'label'     => 'type_name',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_pagetype.gif',
	),
);

$TCA['tx_newspaper_pagezonetype'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagezonetype',		
		'label'     => 'name',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_pagezonetype.gif',
	),
);

$TCA['tx_newspaper_log'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_log',		
		'label'     => 'action',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate DESC',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_log.gif',
	),
);

$TCA['tx_newspaper_articletype'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articletype',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_articletype.gif',
	),
);

$tempColumns = array (
	'tx_newspaper_extra' => array (		
		'exclude' => 1,		
		'label' => 'LLL:EXT:newspaper/locallang_db.xml:tt_content.tx_newspaper_extra',		
		'config' => array (
			'type' => 'none',
		)
	),
);


t3lib_div::loadTCA('tt_content');
t3lib_extMgm::addTCAcolumns('tt_content',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('tt_content','tx_newspaper_extra;;;;1-1-1');

$tempColumns = array (
	'tx_newspaper_associated_section' => array (		
		'exclude' => 1,		
		'label' => 'LLL:EXT:newspaper/locallang_db.xml:pages.tx_newspaper_associated_section',		
		'config' => array (
			'type'     => 'input',
			'size'     => '4',
			'max'      => '4',
			'eval'     => 'int',
			'checkbox' => '0',
			'range'    => array (
				'upper' => '1000',
				'lower' => '10'
			),
			'default' => 0
		)
	),
	'tx_newspaper_module' => array (		
		'exclude' => 1,		
		'label' => 'LLL:EXT:newspaper/locallang_db.xml:pages.tx_newspaper_module',		
		'config' => array (
			'type' => 'input',	
			'size' => '30',
		)
	),
);


t3lib_div::loadTCA('pages');
t3lib_extMgm::addTCAcolumns('pages',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('pages','tx_newspaper_associated_section;;;;1-1-1, tx_newspaper_module');
require_once(PATH_typo3conf . 'ext/newspaper/ext_tables_addon.php');
?>
