<?php

require_once(BASEPATH . '/typo3conf/ext/newspaper/classes/class.tx_newspaper_extraimpl.php');

class tx_newspaper_extra_image extends tx_newspaper_ExtraImpl {

	public function __construct($uid = 0) { 
		if ($uid) {
			parent::__construct($uid); 
			$this->attributes = $this->readExtraItem($uid, $this->getName());		
			t3lib_div::debug($this->attributes);
		}
	}
	
	/** Just a quick hack to see anything
	 *  \todo use smarty
	 */
	public function render($template = '') {
		$ret = '<h4>' . $this->getAttribute('title') . "</h4>\n" .
		'<p>' .
		'<img src="data:image/png;base64,'.base64_encode($this->getAttribute('image')) .
		   '" alt="' . $this->getAttribute('caption') . '" />' .
		"</p>\n" . $this->getAttribute('caption') . "\n";
		
		return $ret;
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

tx_newspaper_ExtraImpl::registerExtra(new tx_newspaper_extra_image());

?>