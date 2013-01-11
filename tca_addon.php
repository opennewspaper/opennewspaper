<?php

// @todo: re-arrage!


// modifications after generating with the kickstarter (bottom of file tca.php)
// require_once(PATH_typo3conf . 'ext/newspaper/tca_addon.php');


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
#	$GLOBALS['TCA']['tx_newspaper_extra_sectionteaser']['columns']['section']['config']['foreign_table_where'] = 'ORDER BY tx_newspaper_section.section_name';
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

?>