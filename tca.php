<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_newspaper_extra_image"] = array (
	"ctrl" => $TCA["tx_newspaper_extra_image"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,starttime,endtime,fe_group,extra_field,title,image,caption"
	),
	"feInterface" => $TCA["tx_newspaper_extra_image"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'default'  => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(0, 0, 0, 12, 31, 2020),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		'fe_group' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		"extra_field" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image.extra_field",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"image" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image.image",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],	
				"max_size" => 500,	
				"uploadfolder" => "uploads/tx_newspaper",
				"show_thumbs" => 1,	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"caption" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image.caption",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, extra_field, title;;;;2-2-2, image;;;;3-3-3, caption")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime, fe_group")
	)
);



$TCA["tx_newspaper_section"] = array (
	"ctrl" => $TCA["tx_newspaper_section"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,starttime,endtime,fe_group,section_name"
	),
	"feInterface" => $TCA["tx_newspaper_section"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'default'  => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(0, 0, 0, 12, 31, 2020),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		'fe_group' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		"section_name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_section.section_name",		
			"config" => Array (
				"type" => "input",	
				"size" => "40",	
				"max" => "40",	
				"eval" => "required",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, section_name")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime, fe_group")
	)
);



$TCA["tx_newspaper_page"] = array (
	"ctrl" => $TCA["tx_newspaper_page"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,starttime,endtime,section,type_name,get_var,get_value"
	),
	"feInterface" => $TCA["tx_newspaper_page"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'default'  => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(0, 0, 0, 12, 31, 2020),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		"section" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_page.section",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_newspaper_section",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"type_name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_page.type_name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"get_var" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_page.get_var",		
			"config" => Array (
				"type" => "input",	
				"size" => "10",	
				"max" => "10",	
				"eval" => "trim",
			)
		),
		"get_value" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_page.get_value",		
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
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, section, type_name, get_var, get_value")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime")
	)
);
require_once(PATH_typo3conf . 'ext/newspaper/tca_addon.php');
?>
