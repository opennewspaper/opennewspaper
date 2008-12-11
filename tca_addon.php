<?php

// modifications after generating with the kickstarter (bottom of file tca.php)
// require_once(PATH_typo3conf . 'ext/extra/tca_addon.php');


// modify fields set in tca.php
// add javascript to extra form (onunload ...)
unset($TCA['tx_newspaper_image']['columns']['extra_field']['config']);
$TCA['tx_newspaper_image']['columns']['extra_field']['config']['type'] = 'user';
$TCA['tx_newspaper_image']['columns']['extra_field']['config']['userFunc'] = 'tx_newspaper->getCodeForBackend';
$TCA['tx_newspaper_image']['columns']['extra_field']['config']['noTableWrapping'] = true;

Extra::registerExtra('tx_newspaper_image'); // register Extra "Text with Image"

?>
