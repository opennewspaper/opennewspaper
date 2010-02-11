<?php

// modifications after generating with the kickstarter (bottom of file tca.php)
// require_once(PATH_typo3conf . 'ext/newspaper/tca_addon.php');


require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper.php');
// base class for extras
require_once(PATH_typo3conf. 'ext/newspaper/classes/class.tx_newspaper_extra.php');


// modify fields set in tca.php

// set sorting for dropdown article type in article
$TCA['tx_newspaper_article']['columns']['articletype_id']['config']['foreign_table_where'] = 'ORDER BY tx_newspaper_articletype.sorting';
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
unset($TCA['tx_newspaper_page']['columns']['template_set']['config']['items']['0']);
$TCA['tx_newspaper_page']['columns']['template_set']['config']['itemsProcFunc'] = 'tx_newspaper_BE->addTemplateSetDropdownEntries';
unset($TCA['tx_newspaper_pagezone_page']['columns']['template_set']['config']['items']['0']);
$TCA['tx_newspaper_pagezone_page']['columns']['template_set']['config']['itemsProcFunc'] = 'tx_newspaper_BE->addTemplateSetDropdownEntries';
unset($TCA['tx_newspaper_article']['columns']['template_set']['config']['items']['0']);
$TCA['tx_newspaper_article']['columns']['template_set']['config']['itemsProcFunc'] = 'tx_newspaper_BE->addTemplateSetDropdownEntries';

/// add entries for template set dropdowns for Extras
unset($TCA['tx_newspaper_extra_externallinks']['columns']['template']['config']);
$TCA['tx_newspaper_extra_externallinks']['columns']['template']['config']['itemsProcFunc'] = 'tx_newspaper_BE->addTemplateSetDropdownEntries';
$TCA['tx_newspaper_extra_externallinks']['columns']['template']['config']['size'] = 1;	
$TCA['tx_newspaper_extra_externallinks']['columns']['template']['config']['maxitems'] = 1;
$TCA['tx_newspaper_extra_externallinks']['columns']['template']['config']['type'] = 'select';


// /switch Extra field 'extras' in article (created by kickstarter) to a userFunc field (displaying a list of associated Extras)
$TCA['tx_newspaper_article']['columns']['extras']['config']['type'] = 'user';
$TCA['tx_newspaper_article']['columns']['extras']['config']['userFunc'] = 'tx_newspaper_be->renderExtraInArticle';

// /switch Extra field 'extras' in article (created by kickstrater) to a userFunc field (displaying buttons according to workflow_status and be_group permission)
unset($TCA['tx_newspaper_article']['columns']['workflow_status']['config']);
$TCA['tx_newspaper_article']['columns']['workflow_status']['config']['type'] = 'user';
$TCA['tx_newspaper_article']['columns']['workflow_status']['config']['userFunc'] = 'tx_newspaper_be->getWorkflowButtons';



// fix ranges artificially imposed by kickstarter
$TCA["tx_newspaper_section"]["columns"]["articlelist"]["config"]["range"] = array (
	"lower" => "1"
);
$TCA["tx_newspaper_pagezone"]["columns"]["pagezone_uid"]["config"]["range"] = array (
	"lower" => "1"
);
$TCA["tx_newspaper_pagezone_page"]["columns"]["inherits_from"]["config"]["range"] = array (
	"lower" => "1"
);
$TCA["tx_newspaper_article"]["columns"]["pagezonetype_id"]["config"]["range"] = array (
	"lower" => "1"
);
$TCA["tx_newspaper_article"]["columns"]["inherits_from"]["config"]["range"] = array (
	"lower" => "1"
);
$TCA["tx_newspaper_article"]["columns"]["workflow_status"]["config"]["range"] = array (
	"lower" => "1"
);
$TCA["tx_newspaper_extra"]["columns"]["extra_uid"]["config"]["range"] = array (
	"lower" => "1"
);
$TCA["tx_newspaper_extra"]["columns"]["position"]["config"]["range"] = array (
	"lower" => "0"
);
$TCA["tx_newspaper_extra"]["columns"]["paragraph"]["config"]["range"] = array (
	"lower" => "0"
);
$TCA["tx_newspaper_extra"]["columns"]["origin_uid"]["config"]["range"] = array (
	"lower" => "0"
);
$TCA["tx_newspaper_articlelist"]["columns"]["list_uid"]["config"]["range"] = array (
	"lower" => "1"
);
$TCA["tx_newspaper_log"]["columns"]["table_uid"]["config"]["range"] = array (
	"lower" => "1"
);
$TCA["tx_newspaper_extra_articlelist"]["columns"]["first_article"]["config"]["range"] = array (
	"lower" => "0"
);
$TCA["tx_newspaper_extra_articlelist"]["columns"]["num_articles"]["config"]["range"] = array (
	"lower" => "0"
);
$TCA["tx_newspaper_page"]["columns"]["get_value"]["config"]["range"] = array (
	"lower" => "1"
);
$TCA["tx_newspaper_articlelist_manual"]["columns"]["num_articles"]["config"]["range"] = array (
	"lower" => "1"
);
$TCA["tx_newspaper_articlelist_semiautomatic"]["columns"]["num_articles"]["config"]["range"] = array (
	"lower" => "1"
);
$TCA["tx_newspaper_extra_mostcommented"]["columns"]["hours"]["config"]["range"] = array (
	"lower" => "1"
);
$TCA["tx_newspaper_extra_mostcommented"]["columns"]["num_favorites"]["config"]["range"] = array (
	"lower" => "1"
);
$TCA["tx_newspaper_controltag_to_extra"]["columns"]["extra_uid"]["config"]["range"] = array (
	"lower" => "1"
);
$TCA['be_users']['columns']['tx_newspaper_role']['config']['range'] = array (
	"lower" => "0",
	"upper" => "1000"
);


// modify some other values
$TCA["tx_newspaper_articlelist"]["columns"]["list_uid"]["config"]['type'] = 'user';
$TCA["tx_newspaper_articlelist"]["columns"]["list_uid"]["config"]['userFunc'] = 'tx_newspaper->includeConcreteList';

$TCA["tx_newspaper_articlelist_manual"]["columns"]["articles"]["config"]["size"] = "10";
$TCA["tx_newspaper_articlelist_manual"]["columns"]["articles"]["config"]["maxitems"] = "100";

$TCA["tx_newspaper_articlelist_semiautomatic"]["columns"]["articles"]["config"]["size"] = "10";
$TCA["tx_newspaper_articlelist_semiautomatic"]["columns"]["articles"]["config"]["maxitems"] = "100";

$TCA["tx_newspaper_articlelist_semiautomatic"]["columns"]["articles"]["config"]['type'] = 'user';
$TCA["tx_newspaper_articlelist_semiautomatic"]["columns"]["articles"]["config"]['userFunc'] = 'tx_newspaper_articlelist_semiautomatic->displayListedArticles';


// make sure the size of the selectbox for sections in articles is set to at least 4; ff/mac bug: no proper scrollbars if size<= 3
$TCA['tx_newspaper_article']['columns']['sections']['config']['size'] = max(4, $TCA['tx_newspaper_article']['columns']['sections']['config']['size']);



// hide field "tag type" in form "extra: control tag zone" and store value for type control tag
$TCA['tx_newspaper_extra_controltagzone']['columns']['tag_type']['config'] = array(
	'type' => 'user',
	'userFunc' => 'tx_newspaper_Extra_ControlTagZone->renderBackendFieldTagType',
	'noTableWrapping' => 1
);


?>