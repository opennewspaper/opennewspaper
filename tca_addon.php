<?php

// modifications after generating with the kickstarter (bottom of file tca.php)
// require_once(PATH_typo3conf . 'ext/newspaper/tca_addon.php');


require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper.php');
// base class for extras
require_once(PATH_typo3conf. 'ext/newspaper/classes/class.tx_newspaper_extra.php');


// set sorting for dropdown article type in article
$TCA['tx_newspaper_article']['columns']['articletype_id']['config']['foreign_table_where'] = 'ORDER BY tx_newspaper_articletype.sorting';
// set default sorting for articles in list module
$TCA['tx_newspaper_article']['ctrl']['default_sortby'] = 'ORDER BY tstamp DESC';
unset($TCA['tx_newspaper_article']['columns']['template_set']['config']['items']['0']);
$TCA['tx_newspaper_article']['columns']['template_set']['config']['itemsProcFunc'] = 'tx_newspaper_BE->addTemplateSetDropdownEntries';
// /switch Extra field 'extras' in article (created by kickstarter) to a userFunc field (displaying a list of associated Extras)
unset($TCA['tx_newspaper_article']['columns']['extras']['config']);
$TCA['tx_newspaper_article']['columns']['extras']['config']['type'] = 'user';
$TCA['tx_newspaper_article']['columns']['extras']['config']['userFunc'] = 'tx_newspaper_be->renderExtraInArticle';
// /switch Extra field 'extras' in article (created by kickstarter)) to a userFunc field (displaying buttons according to workflow_status and be_users.tx_np_role)
unset($TCA['tx_newspaper_article']['columns']['workflow_status']['config']);
$TCA['tx_newspaper_article']['columns']['workflow_status']['config']['type'] = 'user';
$TCA['tx_newspaper_article']['columns']['workflow_status']['config']['userFunc'] = 'tx_newspaper_be->getWorkflowButtons';
// initially load no tags and let custom code handle it
$TCA['tx_newspaper_article']['columns']['tags']['config']['foreign_table_where'] = 'AND tx_newspaper_tag.uid = 0';
// switch field tag in article to a userfunc field (allowing auto completion)
$TCA['tx_newspaper_article']['columns']['tags']['config']['itemsProcFunc'] = 'tx_newspaper_be->getArticleTags';
$TCA["tx_newspaper_article"]["columns"]["pagezonetype_id"]["config"]["range"] = array ("lower" => "1");
$TCA["tx_newspaper_article"]["columns"]["inherits_from"]["config"]["range"] = array ("lower" => "1");
$TCA["tx_newspaper_article"]["columns"]["workflow_status"]["config"]["range"] = array ("lower" => "0");
// make sure the size of the selectbox for sections in articles is set to at least 4; ff/mac bug: no proper scrollbars if size<= 3
$TCA['tx_newspaper_article']['columns']['sections']['config']['size'] = max(4, $TCA['tx_newspaper_article']['columns']['sections']['config']['size']);
// article backend modifications
//t3lib_div::devlog('TCA', 'np', 0, $GLOBALS['TCA']['tx_newspaper_article']['types'][0]);
// remove kickerstarter generated palette configuration
$GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem'] = str_replace(', title;;;;2-2-2, ', ', title, ', $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem']);
$GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem'] = str_replace(', title_list;;;;3-3-3, ', ', title_list, ', $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem']);	
// detach starttime and endtime from hidden field palette
$GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem'] = str_replace('hidden;;1;;1-1-1, ', 'hidden, ', $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem']);
// attach starttime and endtime from field publish_date
$GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem'] = str_replace(', publish_date, ', ', publish_date;;1, ', $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem']);
// add kicker, title and teaser to a palette, move kicker, title and teaser for list views in secondary option
// add main fields kicker, title and teaser to palette 2 (in order to render these 3 fields in 1 row) 
$GLOBALS['TCA']['tx_newspaper_article']['palettes'][2]['showitem'] = 'kicker;;3, title, teaser';
$GLOBALS['TCA']['tx_newspaper_article']['palettes'][2]['canNotCollapse'] = 1;  
// add list view fields to palette 3
$GLOBALS['TCA']['tx_newspaper_article']['palettes'][3]['showitem'] = 'kicker_list, title_list, teaser_list';
// create virtual field "kicker_title_teaser", use to attach palette 2 (kicker, title and teaser in 1 row) ... 
$GLOBALS['TCA']['tx_newspaper_article']['columns']['kicker_title_teaser'] = array(
	'label' => 'LLL:EXT:newspaper/locallang_newspaper.xml:label_kicker_title_teaser',
	'config' => array(
		'type' => 'user',
		'userFunc' => 'tx_newspaper_be->renderArticleKickerTtitleTeaser',
	)
);
// ... and replace field kicker with virtial field kicker_title_teaser (and remove fields kicker, title and teaser from main form)
$GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem'] = str_replace(', kicker, ', ', kicker_title_teaser;;2, ', $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem']);
$GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem'] = str_replace(', title, ', ', ', $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem']);
$GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem'] = str_replace(', teaser, ', ', ', $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem']);
// create virtual field "kicker_title_teaser_for_listview", use to attach palette 2 (kicker, title and teaser for list viwes in 1 row) ... 
$GLOBALS['TCA']['tx_newspaper_article']['columns']['kicker_title_teaser_for_list_views'] = array(
	'label' => 'LLL:EXT:newspaper/locallang_newspaper.xml:label_kicker_title_teaser_for_list_views',
	'config' => array(
		'type' => 'user',
		'userFunc' => 'tx_newspaper_be->renderArticleKickerTtitleTeaserForListviews',
	)
);
// ... and replace field kicker with virtial field kicker_title_teaser (and remove fields kicker, title and teaser from main form)
$GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem'] = str_replace(', kicker_list, ', ', kicker_title_teaser_for_list_views;;3, ', $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem']);
$GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem'] = str_replace(', title_list, ', ', ', $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem']);
$GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem'] = str_replace(', teaser_list, ', ', ', $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem']);
// add tabs
$GLOBALS['TCA']['tx_newspaper_article']['ctrl']['dividers2tabs'] = 1; ;// yes, do use tabs
$GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem'] = str_replace('hidden, ', '--div--;LLL:EXT:newspaper/locallang_newspaper.xml:label_tab_article_article, hidden, ', $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem']);
$GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem'] = str_replace(', template_set,', ',--div--;LLL:EXT:newspaper/locallang_newspaper.xml:label_tab_article_additional, template_set,', $GLOBALS['TCA']['tx_newspaper_article']['types'][0]['showitem']);
//t3lib_div::devlog('TCA', 'np', 0, $GLOBALS['TCA']['tx_newspaper_article']['types'][0]);
// add time to starttime and endtime for newspaper records
$GLOBALS['TCA']['tx_newspaper_article']['columns']['starttime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_article']['columns']['endtime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_article']['columns']['publish_date']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_article']['columns']['starttime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_article']['columns']['endtime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_article']['columns']['publish_date']['config']['size'] = '12';  


$TCA['tx_newspaper_section']['columns']['default_articletype']['config']['foreign_table_where'] = 'ORDER BY tx_newspaper_articletype.sorting';
/// add user function for page type and page zone type in section records 
unset($TCA['tx_newspaper_section']['columns']['pagetype_pagezone']['config']);
$TCA['tx_newspaper_section']['columns']['pagetype_pagezone']['config']['type'] = 'user';
$TCA['tx_newspaper_section']['columns']['pagetype_pagezone']['config']['userFunc'] = 'tx_newspaper_be->renderPagePageZoneList';
/// add article list dropdown
unset($TCA['tx_newspaper_section']['columns']['articlelist']['config']);
$TCA['tx_newspaper_section']['columns']['articlelist']['config']['type'] = 'user';
$TCA['tx_newspaper_section']['columns']['articlelist']['config']['userFunc'] = 'tx_newspaper_be->renderArticleList';
/// add entries for template set dropdowns
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
$GLOBALS['TCA']['tx_newspaper_extra_articlelist']['columns']['first_article']['config']['eval'] = 'required';  
$GLOBALS['TCA']['tx_newspaper_extra_articlelist']['columns']['num_articles']['config']['eval'] = 'required';  

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
// hide field "tag type" in form "extra: control tag zone" and store value for type control tag
$TCA['tx_newspaper_extra_controltagzone']['columns']['tag_type']['config'] = array(
	'type' => 'user',
	'userFunc' => 'tx_newspaper_Extra_ControlTagZone->renderBackendFieldTagType',
	'noTableWrapping' => 1
);

$GLOBALS['TCA']['tx_newspaper_extra_displayarticles']['columns']['starttime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_displayarticles']['columns']['endtime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_displayarticles']['columns']['starttime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_extra_displayarticles']['columns']['endtime']['config']['size'] = '12';  

/// add entries for template set dropdowns for Extras
unset($TCA['tx_newspaper_extra_externallinks']['columns']['template']['config']);
$TCA['tx_newspaper_extra_externallinks']['columns']['template']['config']['itemsProcFunc'] = 'tx_newspaper_BE->addTemplateSetDropdownEntries';
$TCA['tx_newspaper_extra_externallinks']['columns']['template']['config']['size'] = 1;	
$TCA['tx_newspaper_extra_externallinks']['columns']['template']['config']['maxitems'] = 1;
$TCA['tx_newspaper_extra_externallinks']['columns']['template']['config']['type'] = 'select';
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
$GLOBALS['TCA']['tx_newspaper_extra_image']['columns']['image_file']['config']['max_size'] = 10240; // 10 mb \todo: make configurable

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
$GLOBALS['TCA']['tx_newspaper_extra_sectionlist']['columns']['first_article']['config']['eval'] = 'required';  
$GLOBALS['TCA']['tx_newspaper_extra_sectionlist']['columns']['first_article']['config']['range']['lower'] = 1;  
$GLOBALS['TCA']['tx_newspaper_extra_sectionlist']['columns']['num_articles']['config']['eval'] = 'required';  
$GLOBALS['TCA']['tx_newspaper_extra_sectionlist']['columns']['num_articles']['config']['range']['lower'] = 1;  

$GLOBALS['TCA']['tx_newspaper_extra_textbox']['columns']['starttime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_textbox']['columns']['endtime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_textbox']['columns']['starttime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_extra_textbox']['columns']['endtime']['config']['size'] = '12';  

$GLOBALS['TCA']['tx_newspaper_extra_typo3_ce']['columns']['starttime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_typo3_ce']['columns']['endtime']['config']['eval'] = 'datetime';
$GLOBALS['TCA']['tx_newspaper_extra_typo3_ce']['columns']['starttime']['config']['size'] = '12';
$GLOBALS['TCA']['tx_newspaper_extra_typo3_ce']['columns']['endtime']['config']['size'] = '12';  


$TCA["tx_newspaper_log"]["columns"]["table_uid"]["config"]["range"] = array (
	"lower" => "1"
);


$TCA["tx_newspaper_controltag_to_extra"]["columns"]["extra_uid"]["config"]["range"] = array (
	"lower" => "1"
);

?>