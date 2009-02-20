<?php

// modifications after generating with the kickstarter (bottom of file tca.php)
// require_once(PATH_typo3conf . 'ext/newspaper/tca_addon.php');


require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper.php');
// base class for extras
require_once(PATH_typo3conf. 'ext/newspaper/classes/class.tx_newspaper_extraimpl.php');


// modify fields set in tca.php
// add javascript to extra form (onunload ...) and requice classes for Extra

// Extra: Image

// TODO: moved to ext_localconf.php (so classes and Extras available in ext_tables AND tca)
// if modalbox is working, this part of the code should be deleted


// /switch Extra field 'extras' in article (created by kickstrater) to a userFunc field (displaying a list of associated Extras)
$TCA['tx_newspaper_article']['columns']['extras']['config']['type'] = 'user';
$TCA['tx_newspaper_article']['columns']['extras']['config']['userFunc'] = 'tx_newspaper->renderList';

/// \to do: is there a better way to include the js to the backend (without creating a field); otherwise all Extras MUST have this field
unset($TCA['tx_newspaper_extra_image']['columns']['extra_field']['config']);
$TCA['tx_newspaper_extra_image']['columns']['extra_field']['config']['type'] = 'user';
$TCA['tx_newspaper_extra_image']['columns']['extra_field']['config']['userFunc'] = 'tx_newspaper->getCodeForBackend';
$TCA['tx_newspaper_extra_image']['columns']['extra_field']['config']['noTableWrapping'] = true;


// fix ranges artificially imposed by kickstarter
$TCA["tx_newspaper_page"]["columns"]["get_value"]["config"]["range"] = array (
	"upper" => "1000000000",
	"lower" => "1"
);
$TCA["tx_newspaper_extra"]["columns"]["extra_uid"]["config"]["range"] = array (
	"upper" => "1000000000",
	"lower" => "1"
);
$TCA["tx_newspaper_pagezone"]["columns"]["pagezone_uid"]["config"]["range"] = array (
	"upper" => "1000000000",
	"lower" => "1"
);
$TCA["tx_newspaper_articlelist"]["columns"]["list_uid"]["config"]["range"] = array (
	"upper" => "1000000000",
	"lower" => "1"
);

?>