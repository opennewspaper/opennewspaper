<?php

	if (!defined('TYPO3_MODE'))
		die ('Access denied.');

	if (TYPO3_MODE == 'BE') {
		require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra_be.php');
		tx_newspaper_ExtraBE::addAdditionalScriptToBackend(); // add additional script (if needed) to top of backend
	}

?>