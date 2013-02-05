<?php
if (!defined ('TYPO3_MODE'))     die ('Access denied.');

$TCA["tx_newspaper_extra_image"] = array (
    "ctrl" => $TCA["tx_newspaper_extra_image"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "hidden,starttime,endtime,short_description,pool,title,image_file,credit,caption,normalized_filename,kicker,source,image_type,alttext,image_url,tags,width_set,template"
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
        "short_description" => Array (        
            "exclude" => 0,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image.short_description",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "pool" => Array (        
            "exclude" => 0,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image.pool",        
            "config" => Array (
                "type" => "check",
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
        "image_file" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image.image_file",        
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
        "credit" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image.credit",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "caption" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image.caption",        
            "config" => Array (
                "type" => "text",
                "cols" => "40",    
                "rows" => "3",
            )
        ),
        "normalized_filename" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image.normalized_filename",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "kicker" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image.kicker",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "source" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image.source",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "image_type" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image.image_type",        
            "config" => Array (
                "type" => "select",
                "items" => Array (
                    Array("LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image.image_type.I.0", "0"),
                    Array("LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image.image_type.I.1", "1"),
                    Array("LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image.image_type.I.2", "2"),
                    Array("LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image.image_type.I.3", "3"),
                ),
                "size" => 1,    
                "maxitems" => 1,
            )
        ),
        "alttext" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image.alttext",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "image_url" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image.image_url",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "tags" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image.tags",        
            "config" => Array (
                "type" => "group",    
                "internal_type" => "db",    
                "allowed" => "tx_newspaper_tag",    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 100,    
                "MM" => "tx_newspaper_extra_image_tags_mm",
            )
        ),
        "width_set" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image.width_set",        
            "config" => Array (
                "type" => "select",
                "items" => Array (
                    Array("LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image.width_set.I.0", "0"),
                ),
                "size" => 1,    
                "maxitems" => 1,
            )
        ),
        "template" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image.template",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "hidden;;1;;1-1-1, short_description, pool, title;;;;2-2-2, image_file;;;;3-3-3, credit, caption, normalized_filename, kicker, source, image_type, alttext, image_url, tags, width_set, template")
    ),
    "palettes" => array (
        "1" => array("showitem" => "starttime, endtime")
    )
);



$TCA["tx_newspaper_section"] = array (
    "ctrl" => $TCA["tx_newspaper_section"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "section_name,description,show_in_list,parent_section,default_articletype,pagetype_pagezone,articlelist,template_set"
    ),
    "feInterface" => $TCA["tx_newspaper_section"]["feInterface"],
    "columns" => array (
        "section_name" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_section.section_name",        
            "config" => Array (
                "type" => "input",    
                "size" => "48",    
                "max" => "80",    
                "eval" => "required",
            )
        ),
        "show_in_list" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_section.show_in_list",        
            "config" => Array (
                "type" => "check",
                "default" => 1,
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
        "pagetype_pagezone" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_section.pagetype_pagezone",        
            "config" => Array (
                "type" => "none",
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
        "description" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_section.description",
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
                        "title" => "Full screen Rich Text Editing",
                        "icon" => "wizard_rte2.gif",
                        "script" => "wizard_rte.php",
                    ),
                ),
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "section_name, description;;;;1-1-1, show_in_list, parent_section, default_articletype, pagetype_pagezone, articlelist, template_set")
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
        "showRecordFieldList" => "hidden,starttime,endtime,articletype_id,author,kicker_list,kicker,title_list,title,teaser_list,teaser,bodytext,no_rte,url,publish_date,modification_user,source_id,source_object,sections,is_template,extras,name,pagezonetype_id,template_set,inherits_from,tags,related,workflow_status"
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
        "author" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.author",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
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
        "title" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.title",        
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
        "kicker_list" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.kicker_list",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "title_list" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.title_list",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "teaser_list" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.teaser_list",        
            "config" => Array (
                "type" => "text",
                "cols" => "40",    
                "rows" => "5",
            )
        ),
        "bodytext" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.bodytext",        
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
        "no_rte" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.no_rte",        
            "config" => Array (
                "type" => "check",
            )
        ),
        "url" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.url",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",    
                "wizards" => Array(
                    "_PADDING" => 2,
                    "link" => Array(
                        "type" => "popup",
                        "title" => "Link",
                        "icon" => "link_popup.gif",
                        "script" => "browse_links.php?mode=wizard",
                        "JSopenParams" => "height=300,width=500,status=0,menubar=0,scrollbars=1"
                    ),
                ),
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
        "source_id" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.source_id",        
            "config" => Array (
                "type" => "none",
            )
        ),
        "source_object" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.source_object",        
            "config" => Array (
                "type" => "none",
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
            )
        ),
        "is_template" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.is_template",        
            "config" => Array (
                "type" => "check",
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
        "name" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.name",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
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
        "tags" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.tags",        
            "config" => Array (
                "type" => "select",    
                "foreign_table" => "tx_newspaper_tag",    
                "foreign_table_where" => "ORDER BY tx_newspaper_tag.uid",    
                "size" => 3,    
                "minitems" => 0,
                "maxitems" => 100,    
                "MM" => "tx_newspaper_article_tags_mm",
            )
        ),
        "related" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article.related",        
            "config" => Array (
                "type" => "group",    
                "internal_type" => "db",    
                "allowed" => "tx_newspaper_article",    
                "size" => 5,    
                "minitems" => 0,
                "maxitems" => 100,    
                "MM" => "tx_newspaper_article_related_mm",
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
    ),
    "types" => array (
        "0" => array("showitem" => "hidden;;1;;1-1-1, articletype_id, author, kicker_list, kicker, title_list, title;;;;2-2-2, teaser_list, teaser;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_newspaper/rte/], bodytext;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_newspaper/rte/], no_rte, url, publish_date, modification_user, source_id, source_object, sections, is_template, extras, name, pagezonetype_id, template_set, inherits_from, tags, related, workflow_status")
    ),
    "palettes" => array (
        "1" => array("showitem" => "starttime, endtime")
    )
);



$TCA["tx_newspaper_extra"] = array (
    "ctrl" => $TCA["tx_newspaper_extra"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "hidden,starttime,endtime,extra_table,extra_uid,position,paragraph,origin_uid,is_inheritable,show_extra,gui_hidden,notes,template_set"
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
        "gui_hidden" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra.gui_hidden",        
            "config" => Array (
                "type" => "check",
            )
        ),
        "notes" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra.notes",        
            "config" => Array (
                "type" => "text",
                "cols" => "30",    
                "rows" => "5",
            )
        ),
        "template_set" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra.template_set",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "hidden;;1;;1-1-1, extra_table, extra_uid, position, paragraph, origin_uid, is_inheritable, show_extra, gui_hidden, notes, template_set")
    ),
    "palettes" => array (
        "1" => array("showitem" => "starttime, endtime")
    )
);



$TCA["tx_newspaper_extra_sectionlist"] = array (
    "ctrl" => $TCA["tx_newspaper_extra_sectionlist"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "hidden,starttime,endtime,short_description,first_article,num_articles,template"
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
        "short_description" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_sectionlist.short_description",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "first_article" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_sectionlist.first_article",        
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
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_sectionlist.num_articles",        
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
        "template" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_sectionlist.template",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "hidden;;1;;1-1-1, short_description, first_article, num_articles, template")
    ),
    "palettes" => array (
        "1" => array("showitem" => "starttime, endtime")
    )
);



$TCA["tx_newspaper_articlelist"] = array (
    "ctrl" => $TCA["tx_newspaper_articlelist"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "hidden,starttime,endtime,notes,list_table,list_uid,section_id"
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
        "notes" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist.notes",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",    
                "eval" => "required",
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
        "0" => array("showitem" => "hidden;;1;;1-1-1, notes, list_table, list_uid, section_id")
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
                "eval" => "required,alphanum,nospace,uniqueInPid",
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
                "type" => "input",    
                "size" => "30",
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
                "eval" => "required,alphanum,nospace,uniqueInPid",
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
        "showRecordFieldList" => "table_name,table_uid,be_user,operation,comment,details"
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
        "operation" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_log.operation",        
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
        "details" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_log.details",        
            "config" => Array (
                "type" => "text",
                "cols" => "30",    
                "rows" => "5",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "table_name;;;;1-1-1, table_uid, be_user, operation, comment, details")
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
        "showRecordFieldList" => "hidden,starttime,endtime,short_description,pool,content_elements,template"
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
        "short_description" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_typo3_ce.short_description",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "pool" => Array (        
            "exclude" => 0,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_typo3_ce.pool",        
            "config" => Array (
                "type" => "check",
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
        "template" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_typo3_ce.template",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "hidden;;1;;1-1-1, short_description, pool, content_elements, template")
    ),
    "palettes" => array (
        "1" => array("showitem" => "starttime, endtime")
    )
);



$TCA["tx_newspaper_extra_articlelist"] = array (
    "ctrl" => $TCA["tx_newspaper_extra_articlelist"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "starttime,endtime,short_description,articlelist,first_article,num_articles,header,image,template"
    ),
    "feInterface" => $TCA["tx_newspaper_extra_articlelist"]["feInterface"],
    "columns" => array (
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
        "short_description" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_articlelist.short_description",        
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
        "header" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_articlelist.header",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "image" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_articlelist.image",        
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
        "template" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_articlelist.template",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "starttime;;;;1-1-1, endtime, short_description, articlelist, first_article, num_articles, header, image, template")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);



$TCA["tx_newspaper_extra_textbox"] = array (
    "ctrl" => $TCA["tx_newspaper_extra_textbox"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "starttime,endtime,short_description,pool,title,bodytext,image_file,template"
    ),
    "feInterface" => $TCA["tx_newspaper_extra_textbox"]["feInterface"],
    "columns" => array (
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
        "short_description" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_textbox.short_description",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "pool" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_textbox.pool",        
            "config" => Array (
                "type" => "check",
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
        "bodytext" => Array (        
            "exclude" => 0,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_textbox.bodytext",        
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
        "image_file" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_textbox.image_file",        
            "config" => Array (
                "type" => "group",
                "internal_type" => "file",
                "allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],    
                "max_size" => 1000,    
                "uploadfolder" => "uploads/tx_newspaper",
                "show_thumbs" => 1,    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 1,
            )
        ),
        "template" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_textbox.template",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "starttime;;;;1-1-1, endtime, short_description, pool, title;;;;2-2-2, bodytext;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_newspaper/rte/];3-3-3, image_file, template")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);



$TCA["tx_newspaper_extra_externallinks"] = array (
    "ctrl" => $TCA["tx_newspaper_extra_externallinks"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "starttime,endtime,short_description,pool,title,links,template"
    ),
    "feInterface" => $TCA["tx_newspaper_extra_externallinks"]["feInterface"],
    "columns" => array (
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
        "short_description" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_externallinks.short_description",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "pool" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_externallinks.pool",        
            "config" => Array (
                "type" => "check",
            )
        ),
        "title" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_externallinks.title",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "links" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_externallinks.links",        
            "config" => Array (
                "type" => "select",    
                "foreign_table" => "tx_newspaper_externallinks",    
                "foreign_table_where" => "ORDER BY tx_newspaper_externallinks.uid",    
                "size" => 5,    
                "minitems" => 0,
                "maxitems" => 100,    
                "wizards" => Array(
                    "_PADDING" => 2,
                    "_VERTICAL" => 1,
                    "add" => Array(
                        "type" => "script",
                        "title" => "Create new record",
                        "icon" => "add.gif",
                        "params" => Array(
                            "table"=>"tx_newspaper_externallinks",
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
                            "table"=>"tx_newspaper_externallinks",
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
        "template" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_externallinks.template",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "starttime;;;;1-1-1, endtime, short_description, pool, title;;;;2-2-2, links;;;;3-3-3, template")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);



$TCA["tx_newspaper_externallinks"] = array (
    "ctrl" => $TCA["tx_newspaper_externallinks"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "starttime,endtime,url"
    ),
    "feInterface" => $TCA["tx_newspaper_externallinks"]["feInterface"],
    "columns" => array (
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
        "url" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_externallinks.url",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",    
                "wizards" => Array(
                    "_PADDING" => 2,
                    "link" => Array(
                        "type" => "popup",
                        "title" => "Link",
                        "icon" => "link_popup.gif",
                        "script" => "browse_links.php?mode=wizard",
                        "JSopenParams" => "height=300,width=500,status=0,menubar=0,scrollbars=1"
                    ),
                ),
                "eval" => "required",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "starttime;;;;1-1-1, endtime, url")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);



$TCA["tx_newspaper_extra_displayarticles"] = array (
    "ctrl" => $TCA["tx_newspaper_extra_displayarticles"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "starttime,endtime,short_description,template"
    ),
    "feInterface" => $TCA["tx_newspaper_extra_displayarticles"]["feInterface"],
    "columns" => array (
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
        "short_description" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_displayarticles.short_description",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "template" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_displayarticles.template",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "starttime;;;;1-1-1, endtime, short_description, template")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);



$TCA["tx_newspaper_articlelist_manual"] = array (
    "ctrl" => $TCA["tx_newspaper_articlelist_manual"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "articles,num_articles,filter_sections,filter_tags_include,filter_tags_exclude,filter_articlelist_exclude,filter_sql_table,filter_sql_where,filter_sql_order_by"
    ),
    "feInterface" => $TCA["tx_newspaper_articlelist_manual"]["feInterface"],
    "columns" => array (
        "articles" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist_manual.articles",        
            "config" => Array (
                "type" => "group",    
                "internal_type" => "db",    
                "allowed" => "tx_newspaper_article",    
                "size" => 10,    
                "minitems" => 0,
                "maxitems" => 100,    
                "MM" => "tx_newspaper_articlelist_manual_articles_mm",
            )
        ),
        "num_articles" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist_manual.num_articles",        
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
        "filter_sections" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist_manual.filter_sections",        
            "config" => Array (
                "type" => "group",    
                "internal_type" => "db",    
                "allowed" => "tx_newspaper_section",    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 100,
            )
        ),
        "filter_tags_include" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist_manual.filter_tags_include",        
            "config" => Array (
                "type" => "group",    
                "internal_type" => "db",    
                "allowed" => "tx_newspaper_tag",    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 100,
            )
        ),
        "filter_tags_exclude" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist_manual.filter_tags_exclude",        
            "config" => Array (
                "type" => "group",    
                "internal_type" => "db",    
                "allowed" => "tx_newspaper_tag",    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 100,
            )
        ),
        "filter_articlelist_exclude" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist_manual.filter_articlelist_exclude",        
            "config" => Array (
                "type" => "group",    
                "internal_type" => "db",    
                "allowed" => "tx_newspaper_articlelist",    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 100,
            )
        ),
        "filter_sql_table" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist_manual.filter_sql_table",        
            "config" => Array (
                "type" => "text",
                "cols" => "30",    
                "rows" => "5",
            )
        ),
        "filter_sql_where" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist_manual.filter_sql_where",        
            "config" => Array (
                "type" => "text",
                "cols" => "30",    
                "rows" => "5",
            )
        ),
        "filter_sql_order_by" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist_manual.filter_sql_order_by",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "articles;;;;1-1-1, num_articles, filter_sections, filter_tags_include, filter_tags_exclude, filter_articlelist_exclude, filter_sql_table, filter_sql_where, filter_sql_order_by")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);



$TCA["tx_newspaper_articlelist_semiautomatic"] = array (
    "ctrl" => $TCA["tx_newspaper_articlelist_semiautomatic"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "articles,num_articles,filter_sections,subsequent_sections,filter_tags_include,filter_tags_exclude,filter_articlelist_exclude,filter_sql_table,filter_sql_where,filter_sql_order_by"
    ),
    "feInterface" => $TCA["tx_newspaper_articlelist_semiautomatic"]["feInterface"],
    "columns" => array (
        "articles" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist_semiautomatic.articles",        
            "config" => Array (
                "type" => "group",    
                "internal_type" => "db",    
                "allowed" => "tx_newspaper_article",    
                "size" => 10,    
                "minitems" => 0,
                "maxitems" => 100,    
                "MM" => "tx_newspaper_articlelist_semiautomatic_articles_mm",
            )
        ),
        "num_articles" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist_semiautomatic.num_articles",        
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
        "filter_sections" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist_semiautomatic.filter_sections",        
            "config" => Array (
                "type" => "group",    
                "internal_type" => "db",    
                "allowed" => "tx_newspaper_section",    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 100,
            )
        ),
        "subsequent_sections" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist_semiautomatic.subsequent_sections",        
            "config" => Array (
                "type" => "check",
            )
        ),
        "filter_tags_include" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist_semiautomatic.filter_tags_include",        
            "config" => Array (
                "type" => "group",    
                "internal_type" => "db",    
                "allowed" => "tx_newspaper_tag",    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 100,
            )
        ),
        "filter_tags_exclude" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist_semiautomatic.filter_tags_exclude",        
            "config" => Array (
                "type" => "group",    
                "internal_type" => "db",    
                "allowed" => "tx_newspaper_tag",    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 100,
            )
        ),
        "filter_articlelist_exclude" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist_semiautomatic.filter_articlelist_exclude",        
            "config" => Array (
                "type" => "group",    
                "internal_type" => "db",    
                "allowed" => "tx_newspaper_articlelist",    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 100,
            )
        ),
        "filter_sql_table" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist_semiautomatic.filter_sql_table",        
            "config" => Array (
                "type" => "text",
                "cols" => "30",    
                "rows" => "5",
            )
        ),
        "filter_sql_where" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist_semiautomatic.filter_sql_where",        
            "config" => Array (
                "type" => "text",
                "cols" => "30",    
                "rows" => "5",
            )
        ),
        "filter_sql_order_by" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist_semiautomatic.filter_sql_order_by",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "articles;;;;1-1-1, num_articles, filter_sections, subsequent_sections, filter_tags_include, filter_tags_exclude, filter_articlelist_exclude, filter_sql_table, filter_sql_where, filter_sql_order_by")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);



$TCA["tx_newspaper_tag"] = array (
    "ctrl" => $TCA["tx_newspaper_tag"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "tag_type,title,tag,ctrltag_cat,section,deactivated"
    ),
    "feInterface" => $TCA["tx_newspaper_tag"]["feInterface"],
    "columns" => array (
        "tag_type" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_tag.tag_type",        
            "config" => Array (
                "type" => "select",
                "items" => Array (
                    Array("LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_tag.tag_type.I.0", "1"),
                    Array("LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_tag.tag_type.I.1", "2"),
                ),
                "size" => 1,    
                "maxitems" => 1,
            )
        ),
        "title" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_tag.title",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "tag" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_tag.tag",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "ctrltag_cat" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_tag.ctrltag_cat",        
            "config" => Array (
                "type" => "select",    
                "foreign_table" => "tx_newspaper_ctrltag_category",    
                "foreign_table_where" => "ORDER BY tx_newspaper_ctrltag_category.uid",    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 1,
            )
        ),
        "section" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_tag.section",        
            "config" => Array (
                "type" => "group",    
                "internal_type" => "db",    
                "allowed" => "tx_newspaper_section",    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 1,
            )
        ),
        "deactivated" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_tag.deactivated",        
            "config" => Array (
                "type" => "check",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "tag_type;;;;1-1-1, title;;;;2-2-2, tag;;;;3-3-3, ctrltag_cat, section, deactivated")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);



$TCA["tx_newspaper_extra_mostcommented"] = array (
    "ctrl" => $TCA["tx_newspaper_extra_mostcommented"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "hidden,starttime,endtime,short_description,hours,num_favorites,display_num,display_time,template"
    ),
    "feInterface" => $TCA["tx_newspaper_extra_mostcommented"]["feInterface"],
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
        "short_description" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_mostcommented.short_description",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "hours" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_mostcommented.hours",        
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
        "num_favorites" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_mostcommented.num_favorites",        
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
        "display_num" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_mostcommented.display_num",        
            "config" => Array (
                "type" => "check",
            )
        ),
        "display_time" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_mostcommented.display_time",        
            "config" => Array (
                "type" => "check",
            )
        ),
        "template" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_mostcommented.template",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "hidden;;1;;1-1-1, short_description, hours, num_favorites, display_num, display_time, template")
    ),
    "palettes" => array (
        "1" => array("showitem" => "starttime, endtime")
    )
);



$TCA["tx_newspaper_comment_cache"] = array (
    "ctrl" => $TCA["tx_newspaper_comment_cache"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "hidden,article,kicker,title,author"
    ),
    "feInterface" => $TCA["tx_newspaper_comment_cache"]["feInterface"],
    "columns" => array (
        'hidden' => array (        
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config'  => array (
                'type'    => 'check',
                'default' => '0'
            )
        ),
        "article" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_comment_cache.article",        
            "config" => Array (
                "type" => "group",    
                "internal_type" => "db",    
                "allowed" => "tx_newspaper_article",    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 1,
            )
        ),
        "kicker" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_comment_cache.kicker",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "title" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_comment_cache.title",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "author" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_comment_cache.author",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "hidden;;1;;1-1-1, article, kicker, title;;;;2-2-2, author;;;;3-3-3")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);



$TCA["tx_newspaper_extra_bio"] = array (
    "ctrl" => $TCA["tx_newspaper_extra_bio"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "hidden,starttime,endtime,short_description,template_set,pool,author_name,is_author,author_id,image_file,photo_source,bio_text,template"
    ),
    "feInterface" => $TCA["tx_newspaper_extra_bio"]["feInterface"],
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
        "short_description" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_bio.short_description",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "template_set" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_bio.template_set",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "pool" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_bio.pool",        
            "config" => Array (
                "type" => "check",
            )
        ),
        "author_name" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_bio.author_name",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "is_author" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_bio.is_author",        
            "config" => Array (
                "type" => "check",
            )
        ),
        "author_id" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_bio.author_id",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "image_file" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_bio.image_file",        
            "config" => Array (
                "type" => "group",
                "internal_type" => "file",
                "allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],    
                "max_size" => 1000,    
                "uploadfolder" => "uploads/tx_newspaper",
                "show_thumbs" => 1,    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 1,
            )
        ),
        "photo_source" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_bio.photo_source",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "bio_text" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_bio.bio_text",        
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
        "template" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_bio.template",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "hidden;;1;;1-1-1, short_description, template_set, pool, author_name, is_author, author_id, image_file, photo_source, bio_text;;;richtext[cut|copy|paste|formatblock|textcolor|bold|italic|underline|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image|line|chMode]:rte_transform[mode=ts_css|imgpath=uploads/tx_newspaper/rte/], template")
    ),
    "palettes" => array (
        "1" => array("showitem" => "starttime, endtime")
    )
);



$TCA["tx_newspaper_tag_zone"] = array (
    "ctrl" => $TCA["tx_newspaper_tag_zone"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "hidden,name"
    ),
    "feInterface" => $TCA["tx_newspaper_tag_zone"]["feInterface"],
    "columns" => array (
        'hidden' => array (        
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config'  => array (
                'type'    => 'check',
                'default' => '0'
            )
        ),
        "name" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_tag_zone.name",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "hidden;;1;;1-1-1, name")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);



$TCA["tx_newspaper_extra_controltagzone"] = array (
    "ctrl" => $TCA["tx_newspaper_extra_controltagzone"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "hidden,starttime,endtime,short_description,tag_zone,default_extra,template"
    ),
    "feInterface" => $TCA["tx_newspaper_extra_controltagzone"]["feInterface"],
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
        "short_description" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_controltagzone.short_description",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "tag_zone" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_controltagzone.tag_zone",        
            "config" => Array (
                "type" => "select",    
                "items" => Array (
                    Array("",0),
                ),
                "foreign_table" => "tx_newspaper_tag_zone",    
                "foreign_table_where" => "ORDER BY tx_newspaper_tag_zone.uid",    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 1,
            )
        ),
        "default_extra" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_controltagzone.default_extra",        
            "config" => Array (
                "type" => "group",    
                "internal_type" => "db",    
                "allowed" => "tx_newspaper_extra",    
                "size" => 3,    
                "minitems" => 0,
                "maxitems" => 10,
            )
        ),
        "template" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_controltagzone.template",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "hidden;;1;;1-1-1, short_description, tag_zone, default_extra, template")
    ),
    "palettes" => array (
        "1" => array("showitem" => "starttime, endtime")
    )
);



$TCA["tx_newspaper_controltag_to_extra"] = array (
    "ctrl" => $TCA["tx_newspaper_controltag_to_extra"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "tag,tag_zone,extra"
    ),
    "feInterface" => $TCA["tx_newspaper_controltag_to_extra"]["feInterface"],
    "columns" => array (
        "tag" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_controltag_to_extra.tag",        
            "config" => Array (
                "type" => "group",    
                "internal_type" => "db",    
                "allowed" => "tx_newspaper_tag",    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 1,
            )
        ),
        "tag_zone" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_controltag_to_extra.tag_zone",        
            "config" => Array (
                "type" => "group",    
                "internal_type" => "db",    
                "allowed" => "tx_newspaper_tag_zone",    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 1,
            )
        ),
        "extra" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_controltag_to_extra.extra",        
            "config" => Array (
                "type" => "group",    
                "internal_type" => "db",    
                "allowed" => "tx_newspaper_extra",    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 1,
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "tag;;;;1-1-1, tag_zone, extra")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);



$TCA["tx_newspaper_extra_combolinkbox"] = array (
    "ctrl" => $TCA["tx_newspaper_extra_combolinkbox"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "hidden,starttime,endtime,short_description,title,show_related_articles,manually_selected_articles,internal_links,external_links,template"
    ),
    "feInterface" => $TCA["tx_newspaper_extra_combolinkbox"]["feInterface"],
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
        "short_description" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_combolinkbox.short_description",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "title" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_combolinkbox.title",
            "config" => Array (
                "type" => "input",
                "size" => "30",
            )
        ),
        "show_related_articles" => Array (
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_combolinkbox.show_related_articles",        
            "config" => Array (
                "type" => "check",
                "default" => 1,
            )
        ),
        "manually_selected_articles" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_combolinkbox.manually_selected_articles",        
            "config" => Array (
                "type" => "group",    
                "internal_type" => "db",    
                "allowed" => "tx_newspaper_article",    
                "size" => 3,    
                "minitems" => 0,
                "maxitems" => 100,
            )
        ),
        "internal_links" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_combolinkbox.internal_links",        
            "config" => Array (
                "type" => "select",    
                "foreign_table" => "tx_newspaper_externallinks",    
                "foreign_table_where" => "ORDER BY tx_newspaper_externallinks.uid",    
                "size" => 3,    
                "minitems" => 0,
                "maxitems" => 100,    
                "wizards" => Array(
                    "_PADDING" => 2,
                    "_VERTICAL" => 1,
                    "add" => Array(
                        "type" => "script",
                        "title" => "Create new record",
                        "icon" => "add.gif",
                        "params" => Array(
                            "table"=>"tx_newspaper_externallinks",
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
                            "table"=>"tx_newspaper_externallinks",
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
        "external_links" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_combolinkbox.external_links",        
            "config" => Array (
                "type" => "select",    
                "foreign_table" => "tx_newspaper_externallinks",    
                "foreign_table_where" => "ORDER BY tx_newspaper_externallinks.uid",    
                "size" => 3,    
                "minitems" => 0,
                "maxitems" => 100,    
                "wizards" => Array(
                    "_PADDING" => 2,
                    "_VERTICAL" => 1,
                    "add" => Array(
                        "type" => "script",
                        "title" => "Create new record",
                        "icon" => "add.gif",
                        "params" => Array(
                            "table"=>"tx_newspaper_externallinks",
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
                            "table"=>"tx_newspaper_externallinks",
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
        "template" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_combolinkbox.template",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "hidden;;1;;1-1-1, short_description, title, show_related_articles, manually_selected_articles, internal_links, external_links, template")
    ),
    "palettes" => array (
        "1" => array("showitem" => "starttime, endtime")
    )
);



$TCA["tx_newspaper_extra_searchresults"] = array (
    "ctrl" => $TCA["tx_newspaper_extra_searchresults"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "starttime,endtime,short_description,sections,search_term,tags,template"
    ),
    "feInterface" => $TCA["tx_newspaper_extra_searchresults"]["feInterface"],
    "columns" => array (
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
        "short_description" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_searchresults.short_description",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "sections" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_searchresults.sections",        
            "config" => Array (
                "type" => "group",    
                "internal_type" => "db",    
                "allowed" => "tx_newspaper_section",    
                "size" => 2,    
                "minitems" => 0,
                "maxitems" => 100,
            )
        ),
        "search_term" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_searchresults.search_term",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "tags" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_searchresults.tags",        
            "config" => Array (
                "type" => "group",    
                "internal_type" => "db",    
                "allowed" => "tx_newspaper_tag",    
                "size" => 2,    
                "minitems" => 0,
                "maxitems" => 100,
            )
        ),
        "template" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_searchresults.template",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "starttime;;;;1-1-1, endtime, short_description, sections, search_term, tags, template")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);



$TCA["tx_newspaper_extra_container"] = array (
    "ctrl" => $TCA["tx_newspaper_extra_container"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "hidden,starttime,endtime,short_description,extras,template"
    ),
    "feInterface" => $TCA["tx_newspaper_extra_container"]["feInterface"],
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
        "short_description" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_container.short_description",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "extras" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_container.extras",        
            "config" => Array (
                "type" => "group",    
                "internal_type" => "db",    
                "allowed" => "tx_newspaper_extra",    
                "size" => 4,    
                "minitems" => 0,
                "maxitems" => 100,
            )
        ),
        "template" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_container.template",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "hidden;;1;;1-1-1, short_description, extras, template")
    ),
    "palettes" => array (
        "1" => array("showitem" => "starttime, endtime")
    )
);



$TCA["tx_newspaper_extra_ad"] = array (
    "ctrl" => $TCA["tx_newspaper_extra_ad"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "hidden,starttime,endtime,short_description,template"
    ),
    "feInterface" => $TCA["tx_newspaper_extra_ad"]["feInterface"],
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
        "short_description" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_ad.short_description",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "template" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_ad.template",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "hidden;;1;;1-1-1, short_description, template")
    ),
    "palettes" => array (
        "1" => array("showitem" => "starttime, endtime")
    )
);



$TCA["tx_newspaper_extra_generic"] = array (
    "ctrl" => $TCA["tx_newspaper_extra_generic"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "hidden,starttime,endtime,short_description,template"
    ),
    "feInterface" => $TCA["tx_newspaper_extra_generic"]["feInterface"],
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
        "short_description" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_generic.short_description",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "template" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_generic.template",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "hidden;;1;;1-1-1, short_description, template")
    ),
    "palettes" => array (
        "1" => array("showitem" => "starttime, endtime")
    )
);



$TCA["tx_newspaper_ctrltag_category"] = array (
    "ctrl" => $TCA["tx_newspaper_ctrltag_category"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "title"
    ),
    "feInterface" => $TCA["tx_newspaper_ctrltag_category"]["feInterface"],
    "columns" => array (
        "title" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_ctrltag_category.title",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",    
                "eval" => "required,unique",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "title;;;;2-2-2")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);



$TCA["tx_newspaper_extra_html"] = array (
    "ctrl" => $TCA["tx_newspaper_extra_html"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "short_description,html,template"
    ),
    "feInterface" => $TCA["tx_newspaper_extra_html"]["feInterface"],
    "columns" => array (
        "short_description" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_html.short_description",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "html" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_html.html",        
            "config" => Array (
                "type" => "text",
                "wrap" => "OFF",
                "cols" => "30",    
                "rows" => "5",
            )
        ),
        "template" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_html.template",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "short_description;;;;1-1-1, html, template")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);


$TCA["tx_newspaper_extra_flexform"] = array (
    "ctrl" => $TCA["tx_newspaper_extra_flexform"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "short_description,flexform,template"
    ),
    "feInterface" => $TCA["tx_newspaper_extra_flexform"]["feInterface"],
    "columns" => array (
        "short_description" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_flexform.short_description",
            "config" => Array (
                "type" => "input",
                "size" => "30",
            )
        ),
        "ds_file" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_flexform.ds_file",
            "config" => Array (
                "type" => "select",
                "items" => Array (), // Filled by itemsProcFunc
                "size" => 1,
                "maxitems" => 1,
                'itemsProcFunc' => 'tx_newspaper_Extra_Flexform->addFlexformTemplateDropdownEntries',
            )
        ),
        "flexform" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_flexform.html",
            "config" => Array (
                "type" => "flex",
                "ds" => array(
                        'default' => '' // filled by: tx_newspaper_Extra_Flexform->addFlexformTemplateDropdownEntries
                )
            )
        ),
        "template" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_flexform.template",
            "config" => Array (
                "type" => "input",
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "short_description;;;;1-1-1, ds_file, flexform, template")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);

$TCA["tx_newspaper_extra_phpinclude"] = array (
    "ctrl" => $TCA["tx_newspaper_extra_phpinclude"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "short_description,file"
    ),
    "feInterface" => $TCA["tx_newspaper_extra_phpinclude"]["feInterface"],
    "columns" => array (
        "short_description" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_phpinclude.short_description",
            "config" => Array (
                "type" => "input",
                "size" => "30",
            )
        ),
        "file" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_phpinclude.file",
            "config" => Array (
                "type" => "select",
                "items" => Array (), // Filled by itemsProcFunc
                "size" => 1,
                "maxitems" => 1,
                'itemsProcFunc' => 'tx_newspaper_Extra_PHPInclude->addFileDropdownEntries',
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "short_description;;;;1-1-1, file")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);

$TCA["tx_newspaper_extra_freeformimage"] = array (
    "ctrl" => $TCA["tx_newspaper_extra_freeformimage"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "hidden,short_description,image_file,image_width,image_height,template"
    ),
    "feInterface" => $TCA["tx_newspaper_extra_freeformimage"]["feInterface"],
    "columns" => array (
        'hidden' => array (        
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config'  => array (
                'type'    => 'check',
                'default' => '0'
            )
        ),
        "short_description" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_freeformimage.short_description",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "image_file" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_freeformimage.image_file",        
            "config" => Array (
                "type" => "group",
                "internal_type" => "file",
                "allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],    
                "max_size" => 1000,    
                "uploadfolder" => "uploads/tx_newspaper",
                "show_thumbs" => 1,    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 1,
            )
        ),
        "image_width" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_freeformimage.image_width",        
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
        "image_height" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_freeformimage.image_height",        
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
        "template" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_freeformimage.template",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "hidden;;1;;1-1-1, short_description, image_file, image_width, image_height, template")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);



$TCA["tx_newspaper_extra_sectionteaser"] = array (
    "ctrl" => $TCA["tx_newspaper_extra_sectionteaser"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "short_description,description_text,is_ctrltag,section,ctrltag_cat,ctrltag,num_articles,num_articles_w_image,template"
    ),
    "feInterface" => $TCA["tx_newspaper_extra_sectionteaser"]["feInterface"],
    "columns" => array (
        "short_description" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_sectionteaser.short_description",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "description_text" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_sectionteaser.description_text",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "is_ctrltag" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_sectionteaser.is_ctrltag",        
            "config" => Array (
                "type" => "select",
                "items" => Array (
                    Array("LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_sectionteaser.is_ctrltag.I.0", "0"),
                    Array("LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_sectionteaser.is_ctrltag.I.1", "1"),
                ),
                "size" => 1,    
                "maxitems" => 1,
            )
        ),
        "section" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_sectionteaser.section",        
            "config" => Array (
                "type" => "select",    
                "foreign_table" => "tx_newspaper_section",    
                "foreign_table_where" => "ORDER BY tx_newspaper_section.uid",    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 1,
            )
        ),
        "ctrltag_cat" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_sectionteaser.ctrltag_cat",        
            "config" => Array (
                "type" => "select",    
                "foreign_table" => "tx_newspaper_ctrltag_category",    
                "foreign_table_where" => "ORDER BY tx_newspaper_ctrltag_category.uid",    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 1,
            )
        ),
        "ctrltag" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_sectionteaser.ctrltag",        
            "config" => Array (
                "type" => "select",    
                "foreign_table" => "tx_newspaper_tag",    
                "foreign_table_where" => "ORDER BY tx_newspaper_tag.uid",    
                "size" => 1,    
                "minitems" => 0,
                "maxitems" => 1,
            )
        ),
        "num_articles" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_sectionteaser.num_articles",        
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
        "num_articles_w_image" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_sectionteaser.num_articles_w_image",        
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
        "template" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_sectionteaser.template",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "short_description;;;;1-1-1, description_text, is_ctrltag, section, ctrltag_cat, ctrltag, num_articles, num_articles_w_image, template")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);



$TCA["tx_newspaper_specialhit"] = array (
    "ctrl" => $TCA["tx_newspaper_specialhit"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "hidden,title,teaser,words,url"
    ),
    "feInterface" => $TCA["tx_newspaper_specialhit"]["feInterface"],
    "columns" => array (
        'hidden' => array (        
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config'  => array (
                'type'    => 'check',
                'default' => '0'
            )
        ),
        "title" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_specialhit.title",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",    
                "eval" => "required",
            )
        ),
        "teaser" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_specialhit.teaser",        
            "config" => Array (
                "type" => "text",
                "cols" => "30",    
                "rows" => "5",
            )
        ),
        "words" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_specialhit.words",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "url" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_specialhit.url",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, teaser;;;;3-3-3, words, url")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);



$TCA["tx_newspaper_extra_specialhits"] = array (
    "ctrl" => $TCA["tx_newspaper_extra_specialhits"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "hidden,short_description,template"
    ),
    "feInterface" => $TCA["tx_newspaper_extra_specialhits"]["feInterface"],
    "columns" => array (
        'hidden' => array (        
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config'  => array (
                'type'    => 'check',
                'default' => '0'
            )
        ),
        "short_description" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_specialhits.short_description",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "template" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_specialhits.template",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "hidden;;1;;1-1-1, short_description, template")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);

########################################################################################################################
################################################################################################################### snip
########################################################################################################################



// @todo: re-arrage!


// modifications after generating with the kickstarter (bottom of file tca.php)


require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper.php');
require_once(PATH_typo3conf. 'ext/newspaper/Classes/class.tx_newspaper_extra.php');


// Set default sorting for articles in list module
$TCA['tx_newspaper_article']['ctrl']['default_sortby'] = 'ORDER BY tstamp DESC';
unset($TCA['tx_newspaper_article']['columns']['template_set']['config']['items']['0']);
$TCA['tx_newspaper_article']['columns']['template_set']['config']['itemsProcFunc'] = 'tx_newspaper_BE->addTemplateSetDropdownEntries';

// Switch Extra field 'extras' in article (created by kickstarter) to a userFunc field (displaying a list of associated Extras)
unset($TCA['tx_newspaper_article']['columns']['extras']['config']);
$TCA['tx_newspaper_article']['columns']['extras']['config']['type'] = 'user';
$TCA['tx_newspaper_article']['columns']['extras']['config']['userFunc'] = 'tx_newspaper_be->renderExtraInArticle';
// Switch Extra field 'extras' in article (created by kickstarter)) to a userFunc field (displaying buttons according to workflow_status and be_users.tx_np_role)
unset($TCA['tx_newspaper_article']['columns']['workflow_status']['config']);
$TCA['tx_newspaper_article']['columns']['workflow_status']['config']['type'] = 'user';
$TCA['tx_newspaper_article']['columns']['workflow_status']['config']['userFunc'] = 'tx_newspaper_be->getWorkflowCommentBackend';

// Do not load  tags initially and let custom code handle it
$TCA['tx_newspaper_article']['columns']['tags']['config']['foreign_table_where'] = 'AND tx_newspaper_tag.uid = 0';
// Switch field tag in article to a userFunc field (allowing auto completion)
$TCA['tx_newspaper_article']['columns']['tags']['config']['itemsProcFunc'] = 'tx_newspaper_be->getArticleTags';
$TCA["tx_newspaper_article"]["columns"]["pagezonetype_id"]["config"]["range"] = array ("lower" => "1");
$TCA["tx_newspaper_article"]["columns"]["inherits_from"]["config"]["range"] = array ("lower" => "1");
$TCA["tx_newspaper_article"]["columns"]["workflow_status"]["config"]["range"] = array ("lower" => "0");

// Newspaper textarea field for teaser
// Might be replaced with RTE in hook by TSConfig setting newspaper.be.useRTE.teaser.forArticleTypes = uid[s]
unset($TCA['tx_newspaper_article']['columns']['teaser']['config']);
$TCA['tx_newspaper_article']['columns']['teaser']['config'] = array(
    'type' => 'user',
    'userFunc' => 'tx_newspaper_be->renderTextarea',
    'width' => '350',
    'height' => '46',
    'maxLen' => '500',
    'useCountdown' => '0',
);
unset($TCA['tx_newspaper_article']['columns']['teaser_list']['config']);
$TCA['tx_newspaper_article']['columns']['teaser_list']['config'] = array(
    'type' => 'user',
    'userFunc' => 'tx_newspaper_be->renderTextarea',
    'width' => '350',
    'height' => '31',
    'maxLen' => '500',
    'useCountdown' => '0',
);

// Article backend modifications
//t3lib_div::devlog('TCA', 'np', 0, $GLOBALS['TCA']['tx_newspaper_article']['types'][0]);

    // Remove kickstarter generated palette configuration
    $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem'] = str_replace(', title;;;;2-2-2, ', ', title, ', $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem']);
    $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem'] = str_replace(', teaser;;;;3-3-3, ', ', teaser, ', $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem']);

    // Detach starttime and endtime from hidden field palette
    $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem'] = str_replace('hidden;;1;;1-1-1, ', 'hidden, ', $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem']);


    // Append tab "time control" (starttime and endtime were attached to a secondary option, so they have to added here too)
    $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem'] .= ', --div--;LLL:EXT:newspaper/locallang_newspaper.xml:label_tab_article_timecontrol, starttime, endtime';
    // append empty tab "additional data"
    $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem'] .= ', --div--;LLL:EXT:newspaper/locallang_newspaper.xml:label_tab_article_additional';
    // add tabs article, web elements, tags, workflow, additional data
    $GLOBALS['TCA']['tx_newspaper_article']['ctrl']['dividers2tabs'] = 1; ;// yes, do use tabs
    $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem'] = str_replace('hidden, ', '--div--;LLL:EXT:newspaper/locallang_newspaper.xml:label_tab_article_article, hidden, ', $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem']);
    $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem'] = str_replace(', extras,', ',--div--;LLL:EXT:newspaper/locallang_newspaper.xml:label_tab_article_extra, extras,', $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem']);
    $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem'] = str_replace(', workflow_status,', ',--div--;LLL:EXT:newspaper/locallang_newspaper.xml:label_tab_article_workflow, workflow_status,', $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem']);
    $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem'] = str_replace(', tags,', ',--div--;LLL:EXT:newspaper/locallang_newspaper.xml:label_tab_article_tags, tags,', $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem']);
    $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem'] = str_replace(', related,', ',--div--;LLL:EXT:newspaper/locallang_newspaper.xml:label_tab_article_related, related,', $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem']);

//t3lib_div::devlog('TCA modified', 'np', 0, $GLOBALS['TCA']['tx_newspaper_article']['types'][0]);




    // Force reloading the article backend when the "no_rte" checkbox is changed
    $TCA['tx_newspaper_article']['ctrl']['requestUpdate'] = 'no_rte';
    // Insert mode=no_rte to make usage of RTE configurable per article
    $TCA['tx_newspaper_article']['types']['0']['showitem'] = str_replace('rte_transform[mode=ts_css', 'rte_transform[flag=no_rte|mode=ts_css', $TCA['tx_newspaper_article']['types']['0']['showitem']);






// Add time to starttime and endtime for newspaper records
$GLOBALS['TCA']['tx_newspaper_article']['columns']['starttime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_article']['columns']['endtime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_article']['columns']['publish_date']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_article']['columns']['starttime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_article']['columns']['endtime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_article']['columns']['publish_date']['config']['size'] = '12';


$TCA['tx_newspaper_section']['columns']['default_articletype']['config']['foreign_table_where'] = 'ORDER BY tx_newspaper_articletype.sorting';
/// Add user function for page type and page zone type in section records
unset($TCA['tx_newspaper_section']['columns']['pagetype_pagezone']['config']);
$TCA['tx_newspaper_section']['columns']['pagetype_pagezone']['config']['type'] = 'user';
$TCA['tx_newspaper_section']['columns']['pagetype_pagezone']['config']['userFunc'] = 'tx_newspaper_be->renderPagePageZoneList';
/// Add article list dropdown
unset($TCA['tx_newspaper_section']['columns']['articlelist']['config']);
$TCA['tx_newspaper_section']['columns']['articlelist']['config']['type'] = 'user';
$TCA['tx_newspaper_section']['columns']['articlelist']['config']['userFunc'] = 'tx_newspaper_be->renderArticleList';
/// Add entries for template set dropdowns
unset($TCA['tx_newspaper_section']['columns']['template_set']['config']['items']['0']);
$TCA['tx_newspaper_section']['columns']['template_set']['config']['itemsProcFunc'] = 'tx_newspaper_BE->addTemplateSetDropdownEntries';
$TCA["tx_newspaper_section"]["columns"]["articlelist"]["config"]["range"] = array ("lower" => "1");


unset($TCA['tx_newspaper_page']['columns']['template_set']['config']['items']['0']);
$TCA['tx_newspaper_page']['columns']['template_set']['config']['itemsProcFunc'] = 'tx_newspaper_BE->addTemplateSetDropdownEntries';
$TCA["tx_newspaper_page"]["columns"]["get_value"]["config"]["range"] = array ("lower" => "1");


$TCA["tx_newspaper_pagezone"]["columns"]["pagezone_uid"]["config"]["range"] = array ("lower" => "1");
$TCA["tx_newspaper_pagezone_page"]["columns"]["inherits_from"]["config"]["range"] = array ("lower" => "1");
unset($TCA['tx_newspaper_pagezone_page']['columns']['template_set']['config']['items']['0']);
$TCA['tx_newspaper_pagezone_page']['columns']['template_set']['config']['itemsProcFunc'] = 'tx_newspaper_BE->addTemplateSetDropdownEntries';


$TCA['tx_newspaper_articlelist']['columns']['list_uid']['config']['range'] = array ('lower' => '1');
$GLOBALS['TCA']['tx_newspaper_articlelist']['columns']['starttime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_articlelist']['columns']['endtime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_articlelist']['columns']['starttime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_articlelist']['columns']['endtime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_articlelist']['columns']['list_table']['config']['readOnly'] = '1';
$GLOBALS['TCA']['tx_newspaper_articlelist']['columns']['list_uid']['config']['readOnly'] = '1';
$GLOBALS['TCA']['tx_newspaper_articlelist']['columns']['section_id']['config']['readOnly'] = '1';
$TCA['tx_newspaper_articlelist_manual']['columns']['num_articles']['config']['range'] = array ('lower' => '1');
$TCA['tx_newspaper_articlelist_semiautomatic']['columns']['num_articles']['config']['range'] = array ('lower' => '1');
$TCA['tx_newspaper_articlelist_manual']['columns']['articles']['config']['size'] = '10';
$TCA['tx_newspaper_articlelist_manual']['columns']['articles']['config']['maxitems'] = '100';
$TCA['tx_newspaper_articlelist_semiautomatic']['columns']['articles']['config']['size'] = '10';
$TCA['tx_newspaper_articlelist_semiautomatic']['columns']['articles']['config']['maxitems'] = '100';
$TCA['tx_newspaper_articlelist_semiautomatic']['columns']['articles']['config']['type'] = 'user';
$TCA['tx_newspaper_articlelist_semiautomatic']['columns']['articles']['config']['userFunc'] = 'tx_newspaper_articlelist_semiautomatic->displayListedArticles';

// Set label for abstract extras
$TCA['tx_newspaper_extra']['ctrl']['label_userFunc'] = 'tx_newspaper_be->getAbstractExtraLabel';

// Set label for sections
$TCA['tx_newspaper_section']['ctrl']['label_userFunc'] = 'tx_newspaper_section->getSectionBackendLabel';

// In case the label field "title" is not visible in the backend
$TCA['tx_newspaper_extra_image']['ctrl']['label_alt'] = 'uid';


$TCA["tx_newspaper_extra"]["columns"]["extra_uid"]["config"]["range"] = array ("lower" => "1");
$TCA["tx_newspaper_extra"]["columns"]["position"]["config"]["range"] = array ("lower" => "0");
$TCA["tx_newspaper_extra"]["columns"]["paragraph"]["config"]["range"] = array ("lower" => "0");
$TCA["tx_newspaper_extra"]["columns"]["origin_uid"]["config"]["range"] = array ("lower" => "0");
$GLOBALS['TCA']['tx_newspaper_extra']['columns']['starttime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra']['columns']['endtime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra']['columns']['starttime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_extra']['columns']['endtime']['config']['size'] = '12';

$TCA["tx_newspaper_extra_articlelist"]["columns"]["first_article"]["config"]["range"] = array ("lower" => "1");
$TCA["tx_newspaper_extra_articlelist"]["columns"]["num_articles"]["config"]["range"] = array ("lower" => "1");
$TCA["tx_newspaper_extra_articlelist"]["columns"]["articlelist"]["config"]["minitems"] = 1;
$GLOBALS['TCA']['tx_newspaper_extra_articlelist']['columns']['starttime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_articlelist']['columns']['endtime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_articlelist']['columns']['starttime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_extra_articlelist']['columns']['endtime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_extra_articlelist']['columns']['first_article']['config']['eval'] = 'int,required';
$GLOBALS['TCA']['tx_newspaper_extra_articlelist']['columns']['num_articles']['config']['eval'] = 'int,required';

$GLOBALS['TCA']['tx_newspaper_extra_bio']['columns']['starttime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_bio']['columns']['endtime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_bio']['columns']['starttime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_extra_bio']['columns']['endtime']['config']['size'] = '12';

$GLOBALS['TCA']['tx_newspaper_extra_combolinkbox']['columns']['starttime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_combolinkbox']['columns']['endtime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_combolinkbox']['columns']['starttime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_extra_combolinkbox']['columns']['endtime']['config']['size'] = '12';

$GLOBALS['TCA']['tx_newspaper_extra_container']['columns']['starttime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_container']['columns']['endtime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_container']['columns']['starttime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_extra_container']['columns']['endtime']['config']['size'] = '12';

$GLOBALS['TCA']['tx_newspaper_extra_controltagzone']['columns']['starttime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_controltagzone']['columns']['endtime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_controltagzone']['columns']['starttime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_extra_controltagzone']['columns']['endtime']['config']['size'] = '12';

$GLOBALS['TCA']['tx_newspaper_extra_displayarticles']['columns']['starttime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_displayarticles']['columns']['endtime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_displayarticles']['columns']['starttime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_extra_displayarticles']['columns']['endtime']['config']['size'] = '12';

/// add entries for template set dropdowns for Extras
$GLOBALS['TCA']['tx_newspaper_extra_externallinks']['columns']['starttime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_externallinks']['columns']['endtime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_externallinks']['columns']['starttime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_extra_externallinks']['columns']['endtime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_externallinks']['columns']['endtime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_externallinks']['columns']['starttime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_externallinks']['columns']['endtime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_externallinks']['columns']['starttime']['config']['eval'] = 'datetime';

$GLOBALS['TCA']['tx_newspaper_extra_image']['columns']['starttime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_image']['columns']['endtime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_image']['columns']['starttime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_extra_image']['columns']['endtime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_extra_image']['columns']['image_file']['config']['max_size'] = tx_newspaper_Image::getMaxImageFileSize();
$GLOBALS['TCA']['tx_newspaper_extra_image']['columns']['width_set']['config']['items'] = tx_newspaper_ImageSizeSet::getDataForFormatDropdown();

$TCA["tx_newspaper_extra_mostcommented"]["columns"]["hours"]["config"]["range"] = array ( "lower" => "1" );
$TCA["tx_newspaper_extra_mostcommented"]["columns"]["num_favorites"]["config"]["range"] = array ( "lower" => "1" );

$GLOBALS['TCA']['tx_newspaper_extra_searchresults']['columns']['starttime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_searchresults']['columns']['endtime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_searchresults']['columns']['starttime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_extra_searchresults']['columns']['endtime']['config']['size'] = '12';

$GLOBALS['TCA']['tx_newspaper_extra_sectionlist']['columns']['starttime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_sectionlist']['columns']['endtime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_sectionlist']['columns']['starttime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_extra_sectionlist']['columns']['endtime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_extra_sectionlist']['columns']['first_article']['config']['eval'] = 'int,required';
$GLOBALS['TCA']['tx_newspaper_extra_sectionlist']['columns']['first_article']['config']['range']['lower'] = 1;
$GLOBALS['TCA']['tx_newspaper_extra_sectionlist']['columns']['num_articles']['config']['eval'] = 'int,required';
$GLOBALS['TCA']['tx_newspaper_extra_sectionlist']['columns']['num_articles']['config']['range']['lower'] = 1;

unset($GLOBALS['TCA']['tx_newspaper_extra_sectionteaser']['columns']['section']['config']);
$GLOBALS['TCA']['tx_newspaper_extra_sectionteaser']['columns']['section']['config'] = array(
    "type" => "select",
    'itemsProcFunc' => 'tx_newspaper_Section->addSectionsToDropdown',
    "size" => 1,
    "maxitems" => 1,
);

$GLOBALS['TCA']['tx_newspaper_extra_textbox']['columns']['starttime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_textbox']['columns']['endtime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_textbox']['columns']['starttime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_extra_textbox']['columns']['endtime']['config']['size'] = '12';

$GLOBALS['TCA']['tx_newspaper_extra_typo3_ce']['columns']['starttime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_typo3_ce']['columns']['endtime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_typo3_ce']['columns']['starttime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_extra_typo3_ce']['columns']['endtime']['config']['size'] = '12';


$TCA["tx_newspaper_extra_sectionteaser"]["columns"]["num_articles"]["config"]["range"] = array (
    "lower" => "1"
);
$TCA["tx_newspaper_extra_sectionteaser"]["columns"]["num_articles_w_image"]["config"]["range"] = array (
    "lower" => "1"
);


$TCA["tx_newspaper_controltag_to_extra"]["columns"]["extra_uid"]["config"]["range"] = array (
    "lower" => "1"
);

// set control tag category sorting
$TCA['tx_newspaper_tag']['columns']['ctrltag_cat']['config']['foreign_table_where'] =
    'ORDER BY tx_newspaper_ctrltag_category.sorting';
// type 1 = content tag (no control tag category, no section), type 2 = control tag
$TCA['tx_newspaper_tag']['types']['1']['showitem'] = 'tag_type;;;;1-1-1, title;;;;2-2-2, tag;;;;3-3-3';
$TCA['tx_newspaper_tag']['types']['2']['showitem'] = 'tag_type;;;;1-1-1, title;;;;2-2-2, tag;;;;3-3-3, ctrltag_cat, section';



// extra section teaser
    // dropdown sorting
#    $GLOBALS['TCA']['tx_newspaper_extra_sectionteaser']['columns']['section']['config']['foreign_table_where'] = 'ORDER BY tx_newspaper_section.section_name';
    $GLOBALS['TCA']['tx_newspaper_extra_sectionteaser']['columns']['ctrltag_cat']['config']['foreign_table_where'] = 'ORDER BY tx_newspaper_ctrltag_category.sorting';
    // dropdown sorting AND filter control tags depending on selected control tag category
    $GLOBALS['TCA']['tx_newspaper_extra_sectionteaser']['columns']['ctrltag']['config']['foreign_table_where'] = 'AND ctrltag_cat=###REC_FIELD_ctrltag_cat### ORDER BY tx_newspaper_tag.tag';
    // reload backend when changing teaser type or control tag category
    $GLOBALS['TCA']['tx_newspaper_extra_sectionteaser']['ctrl']['requestUpdate'] = 'is_ctrltag,ctrltag_cat';
    // show backend fields depending on value in field "is_ctrltag"
    $GLOBALS['TCA']['tx_newspaper_extra_sectionteaser']['columns']['section']['displayCond'] = 'FIELD:is_ctrltag:=:0';
    $GLOBALS['TCA']['tx_newspaper_extra_sectionteaser']['columns']['ctrltag_cat']['displayCond'] = 'FIELD:is_ctrltag:=:1';
    $GLOBALS['TCA']['tx_newspaper_extra_sectionteaser']['columns']['ctrltag']['displayCond'] = 'FIELD:is_ctrltag:=:1';

    // @todo: make configurable?
    $GLOBALS['TCA']['tx_newspaper_specialhit']['columns']['url']['config']['eval'] = 'required';


    // add cps_tcatree to field section in article, if extension "cps_tcatree" is available
    if (t3lib_extMgm::isLoaded('cps_tcatree')) {

        // Load some files, auto loading not working in Typo3 4.2.x
        // @todo: Remove when newspaper is working with higher Typo3 version where autoloading is working?
        $extensionPath = t3lib_extMgm::extPath('cps_devlib') . 'Classes/';
        $autoloadFiles = array(
            'tx_cpsdevlib_db' => $extensionPath.'class.tx_cpsdevlib_db.php',
            'tx_cpsdevlib_debug' => $extensionPath.'class.tx_cpsdevlib_debug.php',
            'tx_cpsdevlib_div' => $extensionPath.'class.tx_cpsdevlib_div.php',
            'tx_cpsdevlib_extmgm' => $extensionPath.'class.tx_cpsdevlib_extmgm.php',
            'tx_cpsdevlib_parser' => $extensionPath.'class.tx_cpsdevlib_parser.php',
        );
        foreach ($autoloadFiles as $key => $value) {
            if (!class_exists($key)) {
                if (file_exists($value)) {
                    require_once($value);
                }
            }
        }
        require_once PATH_t3lib . 'class.t3lib_tceforms.php';

        // get storage folder for sections
        $sectionPid = tx_newspaper_Sysfolder::getInstance()->getPid(new tx_newspaper_section());

        $GLOBALS['TCA']['tx_newspaper_section']['ctrl']['treeParentField'] = 'parent_section';

        $GLOBALS['TCA']['tx_newspaper_article']['columns']['sections']['config']['form_type'] = 'user';
        $GLOBALS['TCA']['tx_newspaper_article']['columns']['sections']['config']['userFunc'] = 'tx_cpstcatree->getTree';
        $GLOBALS['TCA']['tx_newspaper_article']['columns']['sections']['config']['foreign_table'] = 'tx_newspaper_section';
        $GLOBALS['TCA']['tx_newspaper_article']['columns']['sections']['config']['foreign_table_where'] = ' AND tx_newspaper_section.pid=' . $sectionPid;
        $GLOBALS['TCA']['tx_newspaper_article']['columns']['sections']['config']['treeView'] = 1;
        $GLOBALS['TCA']['tx_newspaper_article']['columns']['sections']['config']['expandable'] = 1;
        $GLOBALS['TCA']['tx_newspaper_article']['columns']['sections']['config']['expandFirst'] = 1;
        $GLOBALS['TCA']['tx_newspaper_article']['columns']['sections']['config']['expandAll'] = 0;
        $GLOBALS['TCA']['tx_newspaper_article']['columns']['sections']['config']['minitems'] = 0;
        $GLOBALS['TCA']['tx_newspaper_article']['columns']['sections']['config']['trueMaxItems'] = 3;
    }
// make sure the size of the select box for sections in articles is set to at least 4; ff/mac bug: no proper scrollbars if size<=3
$TCA['tx_newspaper_article']['columns']['sections']['config']['size'] = max(4, $TCA['tx_newspaper_article']['columns']['sections']['config']['size']);

// quick fix for PHP 5.2 bug that resets the range of int fields to (10, 1000) if "max" is set (https://bugs.php.net/bug.php?id=37773)
unset($TCA["tx_newspaper_section"]["columns"]["articlelist"]["config"]["max"]);
unset($TCA["tx_newspaper_pagezone"]["columns"]["inherits_from"]["config"]["max"]);
# evaluates to date; not sure if this should be unset
#unset($TCA["tx_newspaper_article"]["columns"]["publish_date"]["config"]["max"]);
unset($TCA["tx_newspaper_article"]["columns"]["pagezonetype_id"]["config"]["max"]);
unset($TCA["tx_newspaper_article"]["columns"]["inherits_from"]["config"]["max"]);
unset($TCA["tx_newspaper_article"]["columns"]["workflow_status"]["config"]["max"]);
unset($TCA["tx_newspaper_extra"]["columns"]["extra_uid"]["config"]["max"]);
unset($TCA["tx_newspaper_extra"]["columns"]["position"]["config"]["max"]);
unset($TCA["tx_newspaper_extra"]["columns"]["paragraph"]["config"]["max"]);
unset($TCA["tx_newspaper_extra"]["columns"]["origin_uid"]["config"]["max"]);
unset($TCA["tx_newspaper_extra_sectionlist"]["columns"]["first_article"]["config"]["max"]);
unset($TCA["tx_newspaper_extra_sectionlist"]["columns"]["num_articles"]["config"]["max"]);
unset($TCA["tx_newspaper_articlelist"]["columns"]["list_uid"]["config"]["max"]);
unset($TCA["tx_newspaper_log"]["columns"]["table_uid"]["config"]["max"]);
unset($TCA["tx_newspaper_extra_articlelist"]["columns"]["first_article"]["config"]["max"]);
unset($TCA["tx_newspaper_extra_articlelist"]["columns"]["num_articles"]["config"]["max"]);
unset($TCA["tx_newspaper_articlelist_manual"]["columns"]["num_articles"]["config"]["max"]);
unset($TCA["tx_newspaper_articlelist_semiautomatic"]["columns"]["num_articles"]["config"]["max"]);
unset($TCA["tx_newspaper_extra_mostcommented"]["columns"]["hours"]["config"]["max"]);
unset($TCA["tx_newspaper_extra_mostcommented"]["columns"]["num_favorites"]["config"]["max"]);
unset($TCA["tx_newspaper_extra_freeformimage"]["columns"]["image_width"]["config"]["max"]);
unset($TCA["tx_newspaper_extra_freeformimage"]["columns"]["image_height"]["config"]["max"]);
unset($TCA["tx_newspaper_extra_sectionteaser"]["columns"]["num_articles"]["config"]["max"]);
unset($TCA["tx_newspaper_extra_sectionteaser"]["columns"]["num_articles_w_image"]["config"]["max"]);



// Article type settings: user function to check if article types are not allowed for a BE user
unset($TCA['tx_newspaper_article']['columns']['articletype_id']['config']['foreign_table']);
unset($TCA['tx_newspaper_article']['columns']['articletype_id']['config']['foreign_table_where']);
$TCA['tx_newspaper_article']['columns']['articletype_id']['config']['itemsProcFunc'] = 'tx_newspaper_ArticleType->processArticleTypesForArticleBackend';



// Newspaper TSConfig

$tsc = tx_newspaper::getTSConfig(); // Read TSConfig

// Article: Modify article backend depending on newspaper.articleTypeAsUrl setting
// If articletype uid matches setting, field "URL" is shown, fields "RTE" and checkbox "Use RTE" are hidden and vice versa
if ($tsc['newspaper.']['articleTypeAsUrl']) {
        // Article as URL
        $GLOBALS['TCA']['tx_newspaper_article']['columns']['url']['displayCond'] =
               'FIELD:articletype_id:IN:' . $tsc['newspaper.']['articleTypeAsUrl'];
        $GLOBALS['TCA']['tx_newspaper_article']['columns']['bodytext']['displayCond'] =
               'FIELD:articletype_id:!IN:' . $tsc['newspaper.']['articleTypeAsUrl'];
        $GLOBALS['TCA']['tx_newspaper_article']['columns']['no_rte']['displayCond'] =
               'FIELD:articletype_id:!IN:' . $tsc['newspaper.']['articleTypeAsUrl'];

        // Append article type to request update fields
        if (strpos($GLOBALS['TCA']['tx_newspaper_article']['ctrl']['requestUpdate'], 'articletype_id') === false) {
            $GLOBALS['TCA']['tx_newspaper_article']['ctrl']['requestUpdate'] .= ',articletype_id';
        }

    } else {
        // Plain article only, no article as URL configured
        unset($GLOBALS['TCA']['tx_newspaper_article']['columns']['url']);
    }







// todo: add hook to make article tca modification possible for other newspaper extensions (see: t3lib_div::loadTCA())

// for testing image upload sizes (in extra image)
//$TCA['tx_newspaper_extra_image']['columns']['image_file']['config']['max_size'] = 1000;



    tx_newspaper::loadSubTca(); // make sure modifications in sub extension are loaded too
