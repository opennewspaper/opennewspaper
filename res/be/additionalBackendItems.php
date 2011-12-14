<?php

	if (!defined('TYPO3_MODE'))
		die ('Access denied.');

	if (TYPO3_MODE == 'BE') {
		require_once(PATH_typo3conf . 'ext/newspaper/classes/private/class.tx_newspaper_be.php');
		tx_newspaper_BE::addAdditionalScriptToBackend(); // add additional script (if needed) to top of backend
	}

?>