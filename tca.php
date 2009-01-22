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



$TCA["tx_newspaper_pagezone"] = array (
	"ctrl" => $TCA["tx_newspaper_pagezone"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,starttime,endtime,fe_group,name,page_id,pagezone_table,pagezone_uid"
	),
	"feInterface" => $TCA["tx_newspaper_pagezone"]["feInterface"],
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
		"name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagezone.name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"page_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagezone.page_id",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_newspaper_page",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"pagezone_table" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagezone.pagezone_table",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"pagezone_uid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagezone.pagezone_uid",		
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
		"0" => array("showitem" => "hidden;;1;;1-1-1, name, page_id, pagezone_table, pagezone_uid")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime, fe_group")
	)
);



$TCA["tx_newspaper_pagezone_page"] = array (
	"ctrl" => $TCA["tx_newspaper_pagezone_page"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,starttime,endtime,fe_group,name,pagezone_id,extras"
	),
	"feInterface" => $TCA["tx_newspaper_pagezone_page"]["feInterface"],
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
		"name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagezone_page.name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"pagezone_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagezone_page.pagezone_id",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"extras" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagezone_page.extras",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_newspaper_extra",	
				"foreign_table_where" => "ORDER BY tx_newspaper_extra.uid",	
				"size" => 5,	
				"minitems" => 0,
				"maxitems" => 100,	
				"MM" => "tx_newspaper_pagezone_page_extras_mm",	
				"wizards" => Array(
					"_PADDING" => 2,
					"_VERTICAL" => 1,
					"add" => Array(
						"type" => "script",
						"title" => "Create new record",
						"icon" => "add.gif",
						"params" => Array(
							"table"=>"tx_newspaper_extra",
							"pid" => "###CURRENT_PID###",
							"setValue" => "prepend"
						),
						"script" => "wizard_add.php",
					),
					"list" => Array(
						"type" => "script",
						"title" => "List",
						"icon" => "list.gif",
						"params" => Array(
							"table"=>"tx_newspaper_extra",
							"pid" => "###CURRENT_PID###",
						),
						"script" => "wizard_list.php",
					),
					"edit" => Array(
						"type" => "popup",
						"title" => "Edit",
						"script" => "wizard_edit.php",
						"popup_onlyOpenIfSelected" => 1,
						"icon" => "edit2.gif",
						"JSopenParams" => "height=350,width=580,status=0,menubar=0,scrollbars=1",
					),
				),
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, name, pagezone_id, extras")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime, fe_group")
	)
);



$TCA["tx_newspaper_pagezone_article"] = array (
	"ctrl" => $TCA["tx_newspaper_pagezone_article"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,starttime,endtime,fe_group,name,pagezone_id,extras"
	),
	"feInterface" => $TCA["tx_newspaper_pagezone_article"]["feInterface"],
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
		"name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagezone_article.name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"pagezone_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagezone_article.pagezone_id",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"extras" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagezone_article.extras",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_newspaper_extra",	
				"foreign_table_where" => "ORDER BY tx_newspaper_extra.uid",	
				"size" => 5,	
				"minitems" => 0,
				"maxitems" => 100,	
				"MM" => "tx_newspaper_pagezone_article_extras_mm",	
				"wizards" => Array(
					"_PADDING" => 2,
					"_VERTICAL" => 1,
					"add" => Array(
						"type" => "script",
						"title" => "Create new record",
						"icon" => "add.gif",
						"params" => Array(
							"table"=>"tx_newspaper_extra",
							"pid" => "###CURRENT_PID###",
							"setValue" => "prepend"
						),
						"script" => "wizard_add.php",
					),
					"list" => Array(
						"type" => "script",
						"title" => "List",
						"icon" => "list.gif",
						"params" => Array(
							"table"=>"tx_newspaper_extra",
							"pid" => "###CURRENT_PID###",
						),
						"script" => "wizard_list.php",
					),
					"edit" => Array(
						"type" => "popup",
						"title" => "Edit",
						"script" => "wizard_edit.php",
						"popup_onlyOpenIfSelected" => 1,
						"icon" => "edit2.gif",
						"JSopenParams" => "height=350,width=580,status=0,menubar=0,scrollbars=1",
					),
				),
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, name, pagezone_id, extras")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime, fe_group")
	)
);



$TCA["tx_newspaper_article"] = array (
	"ctrl" => $TCA["tx_newspaper_article"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,starttime,endtime,fe_group,title,kicker,teaser,text,author,redsys_id,extras,sections"
	),
	"feInterface" => $TCA["tx_newspaper_article"]["feInterface"],
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
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"kicker" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.kicker",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"teaser" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.teaser",		
			"config" => Array (
				"type" => "text",
				"cols" => "40",	
				"rows" => "5",
			)
		),
		"text" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.text",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"author" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.author",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"redsys_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.redsys_id",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"extras" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.extras",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_newspaper_extra",	
				"foreign_table_where" => "ORDER BY tx_newspaper_extra.uid",	
				"size" => 5,	
				"minitems" => 0,
				"maxitems" => 100,	
				"MM" => "tx_newspaper_article_extras_mm",	
				"wizards" => Array(
					"_PADDING" => 2,
					"_VERTICAL" => 1,
					"add" => Array(
						"type" => "script",
						"title" => "Create new record",
						"icon" => "add.gif",
						"params" => Array(
							"table"=>"tx_newspaper_extra",
							"pid" => "###CURRENT_PID###",
							"setValue" => "prepend"
						),
						"script" => "wizard_add.php",
					),
					"list" => Array(
						"type" => "script",
						"title" => "List",
						"icon" => "list.gif",
						"params" => Array(
							"table"=>"tx_newspaper_extra",
							"pid" => "###CURRENT_PID###",
						),
						"script" => "wizard_list.php",
					),
					"edit" => Array(
						"type" => "popup",
						"title" => "Edit",
						"script" => "wizard_edit.php",
						"popup_onlyOpenIfSelected" => 1,
						"icon" => "edit2.gif",
						"JSopenParams" => "height=350,width=580,status=0,menubar=0,scrollbars=1",
					),
				),
			)
		),
		"sections" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.sections",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_newspaper_section",	
				"foreign_table_where" => "ORDER BY tx_newspaper_section.uid",	
				"size" => 3,	
				"minitems" => 0,
				"maxitems" => 100,	
				"MM" => "tx_newspaper_article_sections_mm",	
				"wizards" => Array(
					"_PADDING" => 2,
					"_VERTICAL" => 1,
					"list" => Array(
						"type" => "script",
						"title" => "List",
						"icon" => "list.gif",
						"params" => Array(
							"table"=>"tx_newspaper_section",
							"pid" => "###CURRENT_PID###",
						),
						"script" => "wizard_list.php",
					),
					"edit" => Array(
						"type" => "popup",
						"title" => "Edit",
						"script" => "wizard_edit.php",
						"popup_onlyOpenIfSelected" => 1,
						"icon" => "edit2.gif",
						"JSopenParams" => "height=350,width=580,status=0,menubar=0,scrollbars=1",
					),
				),
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, kicker;;;;3-3-3, teaser, text;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_newspaper/rte/], author, redsys_id, extras, sections")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime, fe_group")
	)
);



$TCA["tx_newspaper_extra"] = array (
	"ctrl" => $TCA["tx_newspaper_extra"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,starttime,endtime,fe_group,extra_table,extra_uid"
	),
	"feInterface" => $TCA["tx_newspaper_extra"]["feInterface"],
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
		"extra_table" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra.extra_table",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"extra_uid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra.extra_uid",		
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
		"0" => array("showitem" => "hidden;;1;;1-1-1, extra_table, extra_uid")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime, fe_group")
	)
);
require_once(PATH_typo3conf . 'ext/newspaper/tca_addon.php');
?>
