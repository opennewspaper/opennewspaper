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


t3lib_extMgm::addToInsertRecords('tx_newspaper_image');

$TCA["tx_newspaper_image"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_image',
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
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_image.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, fe_group, title, image, caption",
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

require_once(PATH_typo3conf . 'ext/newspaper/ext_tables_addon.php');
?>