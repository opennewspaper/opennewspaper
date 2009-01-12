<?php

require_once(BASEPATH . '/typo3conf/ext/newspaper/classes/class.tx_newspaper_extraimpl.php');

class tx_newspaper_extra_image extends tx_newspaper_ExtraImpl {




// internal name
	static function getName() {
		return 'tx_newspaper_extra_image';
	}

//TODO: getLLL
	static function getTitle() {
		return 'Image';
	}

// title for module
	static function getModuleName() {
		return 'npe_image'; // this is the default folder for data associated with newspaper etxension, overwrite in conrete Extras
	}

}

?>