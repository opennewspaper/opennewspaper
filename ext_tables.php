<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';


t3lib_extMgm::addPlugin(array('LLL:EXT:newspaper/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Display Ressorts/Articles");


if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_newspaper_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_newspaper_pi1_wizicon.php';


if (TYPO3_MODE == 'BE')	{
		
}


if (TYPO3_MODE == 'BE')	{
		
}


if (TYPO3_MODE == 'BE')	{
		
}


if (TYPO3_MODE == 'BE')	{
		
}


if (TYPO3_MODE == 'BE')	{
		
}


if (TYPO3_MODE == 'BE')	{
		
}


if (TYPO3_MODE == 'BE')	{
		
}


if (TYPO3_MODE == 'BE')	{
		
}


if (TYPO3_MODE == 'BE')	{
		
}


t3lib_extMgm::addToInsertRecords('tx_newspaper_extra_image');

$TCA["tx_newspaper_extra_image"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_image',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra_image.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, pool, title, image_file, credit, caption, normalized_filename, kicker, source, type, alttext, tags",
	)
);

$TCA["tx_newspaper_section"] = array (
	"ctrl" => array (
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
	"feInterface" => array (
		"fe_admin_fieldList" => "section_name, articles_allowed, parent_section, default_articletype, pagetype_pagezone, articlelist, template_set",
	)
);


t3lib_extMgm::addToInsertRecords('tx_newspaper_page');

$TCA["tx_newspaper_page"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_page',		
		'label'     => 'pagetype_id',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_page.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "section, pagetype_id, inherit_pagetype_id, template_set",
	)
);

$TCA["tx_newspaper_pagezone"] = array (
	"ctrl" => array (
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
	"feInterface" => array (
		"fe_admin_fieldList" => "name, page_id, pagezone_table, pagezone_uid",
	)
);

$TCA["tx_newspaper_pagezone_page"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagezone_page',		
		'label'     => 'pagezone_id',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_pagezone_page.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "pagezonetype_id, pagezone_id, extras, template_set, inherits_from",
	)
);

$TCA["tx_newspaper_article"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_article',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate DESC",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_article.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, articletype_id, author, kicker, title, teaser, kicker_list, title_list, teaser_list, text, no_rte, publish_date, modification_user, source_id, source_object, sections, extras, name, is_template, pagezonetype_id, template_set, inherits_from, tags, related, workflow_status",
	)
);

$TCA["tx_newspaper_extra"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra',		
		'label'     => 'extra_uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, extra_table, extra_uid, position, paragraph, origin_uid, is_inheritable, show_extra, gui_hidden, notes, template_set",
	)
);

$TCA["tx_newspaper_extra_sectionlist"] = array (
	"ctrl" => array (
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
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, first_article, num_articles",
	)
);

$TCA["tx_newspaper_articlelist"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist',		
		'label'     => 'notes',	
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
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, notes, list_table, list_uid, section_id",
	)
);

$TCA["tx_newspaper_pagetype"] = array (
	"ctrl" => array (
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
	"feInterface" => array (
		"fe_admin_fieldList" => "type_name, normalized_name, is_article_page, get_var, get_value",
	)
);

$TCA["tx_newspaper_pagezonetype"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_pagezonetype',		
		'label'     => 'type_name',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_pagezonetype.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "type_name, normalized_name, is_article",
	)
);

$TCA["tx_newspaper_log"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_log',		
		'label'     => 'action',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate DESC",	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_log.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "table_name, table_uid, be_user, action, comment",
	)
);

$TCA["tx_newspaper_articletype"] = array (
	"ctrl" => array (
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
	"feInterface" => array (
		"fe_admin_fieldList" => "title, normalized_name",
	)
);


t3lib_extMgm::allowTableOnStandardPages('tx_newspaper_extra_typo3_ce');


t3lib_extMgm::addToInsertRecords('tx_newspaper_extra_typo3_ce');

$TCA["tx_newspaper_extra_typo3_ce"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_typo3_ce',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra_typo3_ce.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, pool, content_elements",
	)
);

$TCA["tx_newspaper_extra_articlelist"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_articlelist',		
		'label'     => 'description',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra_articlelist.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "starttime, endtime, description, articlelist, first_article, num_articles, template, header, image",
	)
);

$TCA["tx_newspaper_extra_textbox"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_textbox',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra_textbox.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "starttime, endtime, pool, title, text, image",
	)
);

$TCA["tx_newspaper_extra_externallinks"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_externallinks',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra_externallinks.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "starttime, endtime, pool, title, links, template",
	)
);

$TCA["tx_newspaper_externallinks"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_externallinks',		
		'label'     => 'url',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_externallinks.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "starttime, endtime, url",
	)
);

$TCA["tx_newspaper_extra_displayarticles"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_displayarticles',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra_displayarticles.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "starttime, endtime, todo",
	)
);

$TCA["tx_newspaper_articlelist_manual"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist_manual',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'type' => 'filter_sections',	
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_articlelist_manual.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "articles, num_articles, filter_sections, filter_tags_include, filter_tags_exclude, filter_articlelist_exclude, filter_sql_table, filter_sql_where, filter_sql_order_by",
	)
);

$TCA["tx_newspaper_articlelist_semiautomatic"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist_semiautomatic',		
		'label'     => 'filter_sections',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_articlelist_semiautomatic.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "articles, num_articles, filter_sections, subsequent_sections, filter_tags_include, filter_tags_exclude, filter_articlelist_exclude, filter_sql_table, filter_sql_where, filter_sql_order_by",
	)
);

$TCA["tx_newspaper_tag"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_tag',		
		'label'     => 'tag',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'type' => 'tag_type',	
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_tag.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "tag_type, title, tag, ctrltag_cat, section",
	)
);

$TCA["tx_newspaper_extra_mostcommented"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_mostcommented',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra_mostcommented.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, hours, num_favorites, display_num, display_time, template",
	)
);

$TCA["tx_newspaper_comment_cache"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_comment_cache',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_comment_cache.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, article, kicker, title, author",
	)
);

$TCA["tx_newspaper_extra_bio"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_bio',		
		'label'     => 'author_name',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra_bio.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, template_set, pool, author_name, is_author, author_id, image_file, photo_source, bio_text",
	)
);

$TCA["tx_newspaper_tag_zone"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_tag_zone',		
		'label'     => 'name',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_tag_zone.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, name",
	)
);

$TCA["tx_newspaper_extra_controltagzone"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_controltagzone',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra_controltagzone.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, tag_zone, default_extra",
	)
);

$TCA["tx_newspaper_controltag_to_extra"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_controltag_to_extra',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_controltag_to_extra.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "tag, tag_zone, extra",
	)
);

$TCA["tx_newspaper_extra_combolinkbox"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_combolinkbox',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra_combolinkbox.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, show_related_articles, manually_selected_articles, internal_links, external_links",
	)
);

$TCA["tx_newspaper_extra_searchresults"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_searchresults',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra_searchresults.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "starttime, endtime, sections, search_term, tags",
	)
);

$TCA["tx_newspaper_extra_container"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_container',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra_container.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, extras, template",
	)
);

$TCA["tx_newspaper_extra_ad"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_ad',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra_ad.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, template",
	)
);

$TCA["tx_newspaper_extra_generic"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_generic',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra_generic.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, template",
	)
);

$TCA["tx_newspaper_ctrltag_category"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_ctrltag_category',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_ctrltag_category.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "title",
	)
);

$TCA["tx_newspaper_extra_html"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_html',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra_html.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "html, template, description_text",
	)
);

$TCA["tx_newspaper_extra_freeformimage"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_freeformimage',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra_freeformimage.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, image_file, image_width, image_height",
	)
);

$TCA["tx_newspaper_extra_sectionteaser"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_sectionteaser',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra_sectionteaser.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "template, description_text, is_ctrltag, ctrltag_cat, num_articles, num_articles_w_image",
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
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "tx_newspaper_section",	
			"size" => 1,	
			"minitems" => 0,
			"maxitems" => 1,
		)
	),
	"tx_newspaper_module" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:newspaper/locallang_db.xml:pages.tx_newspaper_module",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",
		)
	),
);


t3lib_div::loadTCA("pages");
t3lib_extMgm::addTCAcolumns("pages",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("pages","tx_newspaper_associated_section;;;;1-1-1, tx_newspaper_module");

$tempColumns = Array (
	"tx_newspaper_role" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:newspaper/locallang_db.xml:be_users.tx_newspaper_role",		
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


t3lib_div::loadTCA("be_users");
t3lib_extMgm::addTCAcolumns("be_users",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("be_users","tx_newspaper_role;;;;1-1-1");
require_once(PATH_typo3conf . 'ext/newspaper/ext_tables_addon.php');
?>
