<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_newspaper_extra_image"] = array (
	"ctrl" => $TCA["tx_newspaper_extra_image"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,starttime,endtime,pool,template_set,title,image,caption"
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
		"pool" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image.pool",		
			"config" => Array (
				"type" => "check",
			)
		),
		"template_set" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image.template_set",		
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
				"allowed" => "gif,png,jpeg,jpg",	
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
		"0" => array("showitem" => "hidden;;1;;1-1-1, pool, template_set, title;;;;2-2-2, image;;;;3-3-3, caption")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime")
	)
);



$TCA["tx_newspaper_section"] = array (
	"ctrl" => $TCA["tx_newspaper_section"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "section_name,parent_section,default_articletype,articlelist,template_set,pagetype_pagezone"
	),
	"feInterface" => $TCA["tx_newspaper_section"]["feInterface"],
	"columns" => array (
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
		"parent_section" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_section.parent_section",		
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("",0),
				),
				"foreign_table" => "tx_newspaper_section",	
				"foreign_table_where" => "ORDER BY tx_newspaper_section.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"default_articletype" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_section.default_articletype",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_newspaper_articletype",	
				"foreign_table_where" => "ORDER BY tx_newspaper_articletype.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"articlelist" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_section.articlelist",		
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
		"template_set" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_section.template_set",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_section.template_set.I.0", "0"),
				),
				"size" => 1,	
				"maxitems" => 1,
			)
		),
		"pagetype_pagezone" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_section.pagetype_pagezone",		
			"config" => Array (
				"type" => "none",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "section_name;;;;1-1-1, parent_section, default_articletype, articlelist, template_set, pagetype_pagezone")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_newspaper_page"] = array (
	"ctrl" => $TCA["tx_newspaper_page"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "section,pagetype_id,inherit_pagetype_id,template_set"
	),
	"feInterface" => $TCA["tx_newspaper_page"]["feInterface"],
	"columns" => array (
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
		"pagetype_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_page.pagetype_id",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_newspaper_pagetype",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"inherit_pagetype_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_page.inherit_pagetype_id",		
			"config" => Array (
				"type" => "select",	
				"items" => Array (
					Array("",0),
				),
				"foreign_table" => "tx_newspaper_pagetype",	
				"foreign_table_where" => "ORDER BY tx_newspaper_pagetype.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"template_set" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_page.template_set",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_page.template_set.I.0", "0"),
				),
				"size" => 1,	
				"maxitems" => 1,
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "section;;;;1-1-1, pagetype_id, inherit_pagetype_id, template_set")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_newspaper_pagezone"] = array (
	"ctrl" => $TCA["tx_newspaper_pagezone"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "name,page_id,pagezone_table,pagezone_uid"
	),
	"feInterface" => $TCA["tx_newspaper_pagezone"]["feInterface"],
	"columns" => array (
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
		"0" => array("showitem" => "name;;;;1-1-1, page_id, pagezone_table, pagezone_uid")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_newspaper_pagezone_page"] = array (
	"ctrl" => $TCA["tx_newspaper_pagezone_page"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "pagezonetype_id,pagezone_id,extras,template_set,inherits_from"
	),
	"feInterface" => $TCA["tx_newspaper_pagezone_page"]["feInterface"],
	"columns" => array (
		"pagezonetype_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagezone_page.pagezonetype_id",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_newspaper_pagezonetype",	
				"foreign_table_where" => "ORDER BY tx_newspaper_pagezonetype.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
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
		"template_set" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagezone_page.template_set",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagezone_page.template_set.I.0", "0"),
				),
				"size" => 1,	
				"maxitems" => 1,
			)
		),
		"inherits_from" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagezone_page.inherits_from",		
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
		"0" => array("showitem" => "pagezonetype_id;;;;1-1-1, pagezone_id, extras, template_set, inherits_from")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_newspaper_article"] = array (
	"ctrl" => $TCA["tx_newspaper_article"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,starttime,endtime,articletype_id,title,kicker,teaser,text,author,source_id,source_object,extras,sections,name,is_template,template_set,pagezonetype_id,inherits_from,publish_date,workflow_status,modification_user"
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
		"articletype_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.articletype_id",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_newspaper_articletype",	
				"foreign_table_where" => "ORDER BY tx_newspaper_articletype.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
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
			"exclude" => 0,		
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
		"source_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.source_id",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"source_object" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.source_object",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],	
				"max_size" => 500,	
				"uploadfolder" => "uploads/tx_newspaper",
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
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
		"name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"is_template" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.is_template",		
			"config" => Array (
				"type" => "check",
			)
		),
		"template_set" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.template_set",		
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.template_set.I.0", "0"),
				),
				"size" => 1,	
				"maxitems" => 1,
			)
		),
		"pagezonetype_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.pagezonetype_id",		
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
		"inherits_from" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.inherits_from",		
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
		"publish_date" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.publish_date",		
			"config" => Array (
				"type"     => "input",
				"size"     => "8",
				"max"      => "20",
				"eval"     => "date",
				"checkbox" => "0",
				"default"  => "0"
			)
		),
		"workflow_status" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.workflow_status",		
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
		"modification_user" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.modification_user",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "be_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, articletype_id, title;;;;2-2-2, kicker;;;;3-3-3, teaser, text;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_newspaper/rte/], author, source_id, source_object, extras, sections, name, is_template, template_set, pagezonetype_id, inherits_from, publish_date, workflow_status, modification_user")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime")
	)
);



$TCA["tx_newspaper_extra"] = array (
	"ctrl" => $TCA["tx_newspaper_extra"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,starttime,endtime,extra_table,extra_uid,position,paragraph,origin_uid,is_inheritable,show_extra"
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
		"position" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra.position",		
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
		"paragraph" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra.paragraph",		
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
		"origin_uid" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra.origin_uid",		
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
		"is_inheritable" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra.is_inheritable",		
			"config" => Array (
				"type" => "check",
			)
		),
		"show_extra" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra.show_extra",		
			"config" => Array (
				"type" => "check",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, extra_table, extra_uid, position, paragraph, origin_uid, is_inheritable, show_extra")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime")
	)
);



$TCA["tx_newspaper_extra_sectionlist"] = array (
	"ctrl" => $TCA["tx_newspaper_extra_sectionlist"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,starttime,endtime,template_set"
	),
	"feInterface" => $TCA["tx_newspaper_extra_sectionlist"]["feInterface"],
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
		"template_set" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_sectionlist.template_set",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, template_set")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime")
	)
);



$TCA["tx_newspaper_articlelist"] = array (
	"ctrl" => $TCA["tx_newspaper_articlelist"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,starttime,endtime,list_table,list_uid,section_id"
	),
	"feInterface" => $TCA["tx_newspaper_articlelist"]["feInterface"],
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
		"list_table" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist.list_table",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"list_uid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist.list_uid",		
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
		"section_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist.section_id",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_newspaper_section",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, list_table, list_uid, section_id")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime")
	)
);



$TCA["tx_newspaper_articlelist_auto"] = array (
	"ctrl" => $TCA["tx_newspaper_articlelist_auto"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,starttime,endtime"
	),
	"feInterface" => $TCA["tx_newspaper_articlelist_auto"]["feInterface"],
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
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime")
	)
);



$TCA["tx_newspaper_pagetype"] = array (
	"ctrl" => $TCA["tx_newspaper_pagetype"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "type_name,normalized_name,is_article_page,get_var,get_value"
	),
	"feInterface" => $TCA["tx_newspaper_pagetype"]["feInterface"],
	"columns" => array (
		"type_name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagetype.type_name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,uniqueInPid",
			)
		),
		"normalized_name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagetype.normalized_name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "alphanum,nospace",
			)
		),
		"is_article_page" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagetype.is_article_page",		
			"config" => Array (
				"type" => "check",
			)
		),
		"get_var" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagetype.get_var",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"get_value" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagetype.get_value",		
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
		"0" => array("showitem" => "type_name;;;;1-1-1, normalized_name, is_article_page, get_var, get_value")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_newspaper_pagezonetype"] = array (
	"ctrl" => $TCA["tx_newspaper_pagezonetype"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "type_name,normalized_name,is_article"
	),
	"feInterface" => $TCA["tx_newspaper_pagezonetype"]["feInterface"],
	"columns" => array (
		"type_name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagezonetype.type_name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,uniqueInPid",
			)
		),
		"normalized_name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagezonetype.normalized_name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "alphanum,nospace",
			)
		),
		"is_article" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagezonetype.is_article",		
			"config" => Array (
				"type" => "check",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "type_name;;;;1-1-1, normalized_name, is_article")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_newspaper_log"] = array (
	"ctrl" => $TCA["tx_newspaper_log"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "table_name,table_uid,be_user,action,comment"
	),
	"feInterface" => $TCA["tx_newspaper_log"]["feInterface"],
	"columns" => array (
		"table_name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_log.table_name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"table_uid" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_log.table_uid",		
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
		"be_user" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_log.be_user",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "be_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"action" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_log.action",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"max" => "30",	
				"eval" => "required",
			)
		),
		"comment" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_log.comment",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "table_name;;;;1-1-1, table_uid, be_user, action, comment")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_newspaper_articletype"] = array (
	"ctrl" => $TCA["tx_newspaper_articletype"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "title,normalized_name"
	),
	"feInterface" => $TCA["tx_newspaper_articletype"]["feInterface"],
	"columns" => array (
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articletype.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,uniqueInPid",
			)
		),
		"normalized_name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articletype.normalized_name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required,alphanum,nospace,uniqueInPid",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "title;;;;2-2-2, normalized_name;;;;3-3-3")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_newspaper_extra_typo3_ce"] = array (
	"ctrl" => $TCA["tx_newspaper_extra_typo3_ce"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,starttime,endtime,pool,template_set,content_elements"
	),
	"feInterface" => $TCA["tx_newspaper_extra_typo3_ce"]["feInterface"],
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
		"pool" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_typo3_ce.pool",		
			"config" => Array (
				"type" => "check",
			)
		),
		"template_set" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_typo3_ce.template_set",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"content_elements" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_typo3_ce.content_elements",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tt_content",	
				"size" => 5,	
				"minitems" => 0,
				"maxitems" => 32,
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, pool, template_set, content_elements")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime")
	)
);



$TCA["tx_newspaper_extra_articlelist"] = array (
	"ctrl" => $TCA["tx_newspaper_extra_articlelist"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "description,articlelist,first_article,num_articles,template_set,template"
	),
	"feInterface" => $TCA["tx_newspaper_extra_articlelist"]["feInterface"],
	"columns" => array (
		"description" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_articlelist.description",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"articlelist" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_articlelist.articlelist",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_newspaper_articlelist",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"first_article" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_articlelist.first_article",		
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
		"num_articles" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_articlelist.num_articles",		
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
		"template_set" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_articlelist.template_set",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"template" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_articlelist.template",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "description;;;;1-1-1, articlelist, first_article, num_articles, template_set, template")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_newspaper_extra_textbox"] = array (
	"ctrl" => $TCA["tx_newspaper_extra_textbox"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "template_set,title,text,pool"
	),
	"feInterface" => $TCA["tx_newspaper_extra_textbox"]["feInterface"],
	"columns" => array (
		"template_set" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_textbox.template_set",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"title" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_textbox.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"text" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_textbox.text",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
			)
		),
		"pool" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_textbox.pool",		
			"config" => Array (
				"type" => "check",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "template_set;;;;1-1-1, title;;;;2-2-2, text;;;richtext[*];3-3-3, pool")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);
require_once(PATH_typo3conf . 'ext/newspaper/tca_addon.php');
?>
