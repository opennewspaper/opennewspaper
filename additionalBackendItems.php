<?php

	if (!defined('TYPO3_MODE'))
		die ('Access denied.');

	if (TYPO3_MODE == 'BE') {
		Extra::addAdditionalScriptToBackend(); // add additional script (if needed) to top of backend
	}

?>