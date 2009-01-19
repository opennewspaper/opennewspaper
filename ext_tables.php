<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';


t3lib_extMgm::addPlugin(array('LLL:EXT:newspaper/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Display Ressorts/Articles");


if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_newspaper_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_newspaper_pi1_wizicon.php';


if (TYPO3_MODE == 'BE')	{
		
	t3lib_extMgm::addModule('web','txnewspaperM1','',t3lib_extMgm::extPath($_EXTKEY).'mod1/');
}


t3lib_extMgm::addToInsertRecords('tx_newspaper_extra_image');

$TCA["tx_newspaper_extra_image"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra_image.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, fe_group, extra_field, title, image, caption",
	)
);


t3lib_extMgm::allowTableOnStandardPages('tx_newspaper_section');


t3lib_extMgm::addToInsertRecords('tx_newspaper_section');

$TCA["tx_newspaper_section"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_section',		
		'label'     => 'section_name',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_section.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, fe_group, section_name",
	)
);


t3lib_extMgm::addToInsertRecords('tx_newspaper_page');

$TCA["tx_newspaper_page"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_page',		
		'label'     => 'type_name',	
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
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_page.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, section, type_name, get_var, get_value",
	)
);


t3lib_extMgm::allowTableOnStandardPages('tx_newspaper_pagezone');


t3lib_extMgm::addToInsertRecords('tx_newspaper_pagezone');

$TCA["tx_newspaper_pagezone"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagezone',		
		'label'     => 'name',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'type' => 'pagezone_table',	
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_pagezone.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, fe_group, name, page_id, pagezone_table, pagezone_uid",
	)
);


t3lib_extMgm::allowTableOnStandardPages('tx_newspaper_pagezone_page');


t3lib_extMgm::addToInsertRecords('tx_newspaper_pagezone_page');

$TCA["tx_newspaper_pagezone_page"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagezone_page',		
		'label'     => 'name',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_pagezone_page.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, fe_group, name, pagezone_id",
	)
);


t3lib_extMgm::allowTableOnStandardPages('tx_newspaper_pagezone_article');


t3lib_extMgm::addToInsertRecords('tx_newspaper_pagezone_article');

$TCA["tx_newspaper_pagezone_article"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagezone_article',		
		'label'     => 'name',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_pagezone_article.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, fe_group, name, pagezone_id",
	)
);


t3lib_extMgm::allowTableOnStandardPages('tx_newspaper_article');


t3lib_extMgm::addToInsertRecords('tx_newspaper_article');

$TCA["tx_newspaper_article"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_article.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, fe_group, title, extras",
	)
);


t3lib_extMgm::allowTableOnStandardPages('tx_newspaper_extra');


t3lib_extMgm::addToInsertRecords('tx_newspaper_extra');

$TCA["tx_newspaper_extra"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra',		
		'label'     => 'extra_uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'type' => 'extra_table',	
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, fe_group, extra_table, extra_uid",
	)
);

$tempColumns = Array (
	"tx_newspaper_extra" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:newspaper/locallang_db.xml:tt_content.tx_newspaper_extra",		
		"config" => Array (
			"type" => "none",
		)
	),
);


t3lib_div::loadTCA("tt_content");
t3lib_extMgm::addTCAcolumns("tt_content",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tt_content","tx_newspaper_extra;;;;1-1-1");

$tempColumns = Array (
	"tx_newspaper_associated_section" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:newspaper/locallang_db.xml:pages.tx_newspaper_associated_section",		
		"config" => Array (
			"type"     => "input",
			"size"     => "4",
			"max"      => "4",
			"eval"     => "int",
			"checkbox" => "0",
			"range"    => Array (
				"upper" => "1000",
				"lower" => "10"
			),
			"default" => 0
		)
	),
);


t3lib_div::loadTCA("pages");
t3lib_extMgm::addTCAcolumns("pages",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("pages","tx_newspaper_associated_section;;;;1-1-1");
?>