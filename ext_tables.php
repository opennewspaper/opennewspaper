<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';


t3lib_extMgm::addPlugin(array('LLL:EXT:newspaper/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Display Ressorts/Articles");


if (TYPO3_MODE=="BE")    $TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_newspaper_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_newspaper_pi1_wizicon.php';


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
        "fe_admin_fieldList" => "hidden, starttime, endtime, short_description, pool, title, image_file, credit, caption, normalized_filename, kicker, source, image_type, alttext, image_url, tags, width_set, template",
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
        "fe_admin_fieldList" => "section_name, description, show_in_list, parent_section, default_articletype, pagetype_pagezone, articlelist, template_set",
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
        "fe_admin_fieldList" => "hidden, starttime, endtime, articletype_id, author, kicker_list, kicker, title_list, title, teaser_list, teaser, bodytext, no_rte, url, publish_date, modification_user, source_id, source_object, sections, is_template, extras, name, pagezonetype_id, template_set, inherits_from, tags, related, workflow_status",
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
        'label'     => 'short_description',    
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
        "fe_admin_fieldList" => "hidden, starttime, endtime, short_description, first_article, num_articles, template",
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
        'label'     => 'operation',    
        'tstamp'    => 'tstamp',
        'crdate'    => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => "ORDER BY crdate DESC",    
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
        'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_log.gif',
    ),
    "feInterface" => array (
        "fe_admin_fieldList" => "table_name, table_uid, be_user, operation, comment, details",
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
        'label'     => 'short_description',    
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
        "fe_admin_fieldList" => "hidden, starttime, endtime, short_description, pool, content_elements, template",
    )
);

$TCA["tx_newspaper_extra_articlelist"] = array (
    "ctrl" => array (
        'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_articlelist',        
        'label'     => 'short_description',    
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
        "fe_admin_fieldList" => "starttime, endtime, short_description, articlelist, first_article, num_articles, header, image, template",
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
        "fe_admin_fieldList" => "starttime, endtime, short_description, pool, title, bodytext, image_file, template",
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
        "fe_admin_fieldList" => "starttime, endtime, short_description, pool, title, links, template",
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
        'label'     => 'short_description',    
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
        "fe_admin_fieldList" => "starttime, endtime, short_description, template",
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
        "fe_admin_fieldList" => "tag_type, title, tag, ctrltag_cat, section, deactivated",
    )
);

$TCA["tx_newspaper_extra_mostcommented"] = array (
    "ctrl" => array (
        'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_mostcommented',        
        'label'     => 'short_description',    
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
        "fe_admin_fieldList" => "hidden, starttime, endtime, short_description, hours, num_favorites, display_num, display_time, template",
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
        'label'     => 'short_description',    
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
        "fe_admin_fieldList" => "hidden, starttime, endtime, short_description, template_set, pool, author_name, is_author, author_id, image_file, photo_source, bio_text, template",
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
        'label'     => 'short_description',    
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
        "fe_admin_fieldList" => "hidden, starttime, endtime, short_description, tag_zone, default_extra, template",
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
        'label'     => 'short_description',    
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
        "fe_admin_fieldList" => "hidden, starttime, endtime, short_description, title, show_related_articles, manually_selected_articles, internal_links, external_links, template",
    )
);

$TCA["tx_newspaper_extra_searchresults"] = array (
    "ctrl" => array (
        'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_searchresults',        
        'label'     => 'short_description',    
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
        "fe_admin_fieldList" => "starttime, endtime, short_description, sections, search_term, tags, template",
    )
);

$TCA["tx_newspaper_extra_container"] = array (
    "ctrl" => array (
        'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_container',        
        'label'     => 'short_description',    
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
        "fe_admin_fieldList" => "hidden, starttime, endtime, short_description, extras, template",
    )
);

$TCA["tx_newspaper_extra_ad"] = array (
    "ctrl" => array (
        'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_ad',        
        'label'     => 'short_description',    
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
        "fe_admin_fieldList" => "hidden, starttime, endtime, short_description, template",
    )
);

$TCA["tx_newspaper_extra_generic"] = array (
    "ctrl" => array (
        'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_generic',        
        'label'     => 'short_description',    
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
        "fe_admin_fieldList" => "hidden, starttime, endtime, short_description, template",
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
        'label'     => 'short_description',    
        'tstamp'    => 'tstamp',
        'crdate'    => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => "ORDER BY crdate",    
        'delete' => 'deleted',    
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
        'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra_html.gif',
    ),
    "feInterface" => array (
        "fe_admin_fieldList" => "short_description, html, template",
    )
);

$TCA["tx_newspaper_extra_freeformimage"] = array (
    "ctrl" => array (
        'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_freeformimage',        
        'label'     => 'short_description',    
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
        "fe_admin_fieldList" => "hidden, short_description, image_file, image_width, image_height, template",
    )
);

$TCA["tx_newspaper_extra_sectionteaser"] = array (
    "ctrl" => array (
        'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_sectionteaser',        
        'label'     => 'short_description',    
        'tstamp'    => 'tstamp',
        'crdate'    => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => "ORDER BY crdate",    
        'delete' => 'deleted',    
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
        'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra_sectionteaser.gif',
    ),
    "feInterface" => array (
        "fe_admin_fieldList" => "short_description, description_text, is_ctrltag, section, ctrltag_cat, ctrltag, num_articles, num_articles_w_image, template",
    )
);

$TCA["tx_newspaper_specialhit"] = array (
    "ctrl" => array (
        'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_specialhit',        
        'label'     => 'title',    
        'tstamp'    => 'tstamp',
        'crdate'    => 'crdate',
        'cruser_id' => 'cruser_id',
        'sortby' => 'sorting',    
        'delete' => 'deleted',    
        'enablecolumns' => array (        
            'disabled' => 'hidden',
        ),
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
        'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_specialhit.gif',
    ),
    "feInterface" => array (
        "fe_admin_fieldList" => "hidden, title, teaser, words, url",
    )
);

$TCA["tx_newspaper_extra_specialhits"] = array (
    "ctrl" => array (
        'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_specialhits',        
        'label'     => 'short_description',    
        'tstamp'    => 'tstamp',
        'crdate'    => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => "ORDER BY crdate",    
        'delete' => 'deleted',    
        'enablecolumns' => array (        
            'disabled' => 'hidden',
        ),
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
        'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_newspaper_extra_specialhits.gif',
    ),
    "feInterface" => array (
        "fe_admin_fieldList" => "hidden, short_description, template",
    )
);

$TCA["tx_newspaper_extra_flexform"] = array (
    "ctrl" => array (
        'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_flexform',
        'label'     => 'short_description',
        'tstamp'    => 'tstamp',
        'crdate'    => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => "ORDER BY crdate",
        'delete' => 'deleted',
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
        'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_extra_flexform.gif',
        'requestUpdate' => 'ds_file'
    ),
    "feInterface" => array (
        "fe_admin_fieldList" => "short_description, flexform, template",
    )
);

$TCA["tx_newspaper_extra_phpinclude"] = array (
    "ctrl" => array (
        'title'     => 'LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_extra_phpinclude',
        'label'     => 'short_description',
        'tstamp'    => 'tstamp',
        'crdate'    => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => "ORDER BY crdate",
        'delete' => 'deleted',
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
        'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_extra_phpinclude.gif',
        'requestUpdate' => 'ds_file'
    ),
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


########################################################################################################################
################################################################################################################### snip
########################################################################################################################

// modifications after generating with the kickstarter (at bottom of file ext_tables.php)

require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper.php');

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
$TCA['tx_newspaper_extra_ad']['ctrl']['iconfile'] =
    t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_extra_ad.gif';
$TCA['tx_newspaper_extra_generic']['ctrl']['iconfile'] =
    t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_extra_generic.gif';
$TCA['tx_newspaper_ctrltag_category']['ctrl']['iconfile'] =
    t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_ctrltag_category.gif';
$TCA['tx_newspaper_extra_html']['ctrl']['iconfile'] =
    t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_extra_html.gif';
$TCA['tx_newspaper_extra_freeformimage']['ctrl']['iconfile'] =
    t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_extra_freeformimage.gif';
$TCA['tx_newspaper_extra_sectionteaser']['ctrl']['iconfile'] =
    t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_extra_sectionteaser.gif';
$TCA['tx_newspaper_specialhit']['ctrl']['iconfile'] =
    t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_specialhit.gif';
$TCA['tx_newspaper_extra_specialhits']['ctrl']['iconfile'] =
    t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_newspaper_extra_specialhits.gif';


if (TYPO3_MODE == 'BE') {

    // remove range check for set range for role in be_users in table pages
    unset($TCA['pages']['columns']['set range for role in be_users']['config']['range']);

    // add main module 'newspaper', add sub modules
    t3lib_extMgm::addModule('txnewspaperMmain', '',              '', t3lib_extMgm::extPath($_EXTKEY) . 'mod_main/'); // main
    t3lib_extMgm::addModule('txnewspaperMmain', 'txnewspaperM2', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod2/'); // production list (workflow)
    t3lib_extMgm::addModule('txnewspaperMmain', 'txnewspaperM9', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod9/'); // placing articles in section article lists
    t3lib_extMgm::addModule('txnewspaperMmain', 'txnewspaperM7', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod7/'); // placing articles in article lists (non-section) + backend for manual and semiautmatic article lists
    t3lib_extMgm::addModule('txnewspaperMmain', 'txnewspaperM6', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod6/'); // dossiers
    t3lib_extMgm::addModule('txnewspaperMmain', 'txnewspaperM8', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod8/'); // tag
    t3lib_extMgm::addModule('txnewspaperMmain', 'txnewspaperM3', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod3/'); // placement
    t3lib_extMgm::addModule('txnewspaperMmain', 'txnewspaperM5', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod5/'); // webmaster tools
    t3lib_extMgm::addModule('txnewspaperMmain', 'txnewspaperM4', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod4/'); // admin tools
    t3lib_extMgm::addModule('txnewspaperMmain', 'txnewspaperM1', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/'); // AJAX stuff

    /// add newspaper to Plugin-in list
    /// records are stored in sysfolders with module set to 'newspaper'
    $TCA['pages']['columns']['module']['config']['items'][] = array('Newspaper', 'newspaper');

    /// add icon for newspaper sysfolders
    $ICON_TYPES['newspaper'] = array('icon' => t3lib_extMgm::extRelPath($_EXTKEY) . 'res/icons/icon_tx_newspaper_sysf.gif');


    // set range for role in be_users
    $TCA['be_users']['columns']['tx_newspaper_role']['config']['range'] = array (
        "lower" => "0",
        "upper" => "1000" // NP_ACTIVE_ROLE_NONE
    );


    // Add newspaper role switch module
    $pathRole = t3lib_extMgm::extPath('newspaper') . 'mod_role/';
    // register toolbar item
    $GLOBALS['TYPO3_CONF_VARS']['typo3/backend.php']['additionalBackendItems'][] = $pathRole . 'registerToolbarItem.php';
    // register AJAX calls
    $GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['tx_newspaper_role::changeRoleToEditorialStaff'] = $pathRole . 'class.tx_newspaper_role.php:tx_newspaper_role->changeRoleToEditorialStaff';
    $GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['tx_newspaper_role::changeRoleToDutyEditor'] = $pathRole . 'class.tx_newspaper_role.php:tx_newspaper_role->changeRoleToDutyEditor';


    if (tx_newspaper::getTypo3Version() >= 4005000) {

        // Register extbase Backend Module(s)

        Tx_Extbase_Utility_Extension::registerModule(
            $_EXTKEY,
            'txnewspaperMmain', // Make module a submodule of 'web'
            'sectionmodule',    // Submodule key
            '',                 // Position
            array(              // An array holding the controller-action-combinations that are accessible
                'SectionModule'        => 'new,edit,delete'
            ),
            array(
                'access' => 'user,group',
                'icon'   => 'EXT:'.$_EXTKEY.'/Resources/Public/Images/icon_tx_newspaper_section.gif',
                'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml',
            )
        );

    }

}
