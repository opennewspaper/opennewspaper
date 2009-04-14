<?php

// modifications after generating with the kickstarter (bottom of file tca.php)
// require_once(PATH_typo3conf . 'ext/newspaper/tca_addon.php');


require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper.php');
// base class for extras
require_once(PATH_typo3conf. 'ext/newspaper/classes/class.tx_newspaper_extra.php');


// modify fields set in tca.php

// set sorting for dropdown article type in article
$TCA['tx_newspaper_article']['columns']['articletype_id']['config']['foreign_table_where'] = 'ORDER BY tx_newspaper_articletype.sorting';


/// add user function for page type and page zone type in section records 
unset($TCA['tx_newspaper_section']['columns']['pagetype_pagezone']['config']);
$TCA['tx_newspaper_section']['columns']['pagetype_pagezone']['config']['type'] = 'user';
$TCA['tx_newspaper_section']['columns']['pagetype_pagezone']['config']['userFunc'] = 'tx_newspaper_be->renderPageList';


/// add article liste dropdown
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


// /switch Extra field 'extras' in article (created by kickstrater) to a userFunc field (displaying a list of associated Extras)
$TCA['tx_newspaper_article']['columns']['extras']['config']['type'] = 'user';
$TCA['tx_newspaper_article']['columns']['extras']['config']['userFunc'] = 'tx_newspaper->renderList';

/// \to do: is there a better way to include the js to the backend (without creating a field); otherwise all Extras MUST have this field
unset($TCA['tx_newspaper_extra_image']['columns']['extra_field']['config']);
$TCA['tx_newspaper_extra_image']['columns']['extra_field']['config']['type'] = 'user';
$TCA['tx_newspaper_extra_image']['columns']['extra_field']['config']['userFunc'] = 'tx_newspaper->getCodeForBackend';
$TCA['tx_newspaper_extra_image']['columns']['extra_field']['config']['noTableWrapping'] = true;


// fix ranges artificially imposed by kickstarter
/// \todo: check, if upper value can remain empty for "no upper limit"?"
$TCA["tx_newspaper_page"]["columns"]["get_value"]["config"]["range"]["lower"] = "1";
unset($TCA["tx_newspaper_page"]["columns"]["get_value"]["config"]["range"]["upper"]);

$TCA["tx_newspaper_extra"]["columns"]["extra_uid"]["config"]["range"] = array (
//	"upper" => "1000000000",
	"lower" => "1"
);
$TCA["tx_newspaper_pagezone"]["columns"]["pagezone_uid"]["config"]["range"] = array (
//	"upper" => "1000000000",
	"lower" => "1"
);
$TCA["tx_newspaper_articlelist"]["columns"]["list_uid"]["config"]["range"] = array (
//	"upper" => "1000000000",
	"lower" => "1"
);

?>