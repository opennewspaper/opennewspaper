<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

class tx_newspaper_Extra_Image extends tx_newspaper_Extra {

	public function __construct($uid = 0) { 
		if ($uid) {
			parent::__construct($uid); 
		}
	}
	
	/** Just a quick hack to see anything
	 *  \todo use smarty
	 */
	public function render($template_set = '') {
		$this->prepare_render($template_set);
		$ret = '<h4>' . $this->getAttribute('title') . "</h4>\n" .
		'<p>' .
		'<img src="data:image/png;base64,'.base64_encode($this->getAttribute('image')) .
		   '" alt="' . $this->getAttribute('caption') . '" />' .
		"</p>\n" . $this->getAttribute('caption') . "\n";
		
		return $ret;
	}

//TODO: getLLL
	public function getTitle() {
		return 'Image';
	}

// title for module
	static function getModuleName() {
		return 'np_image';
	}
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_extra_image());

?>