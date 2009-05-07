<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

class tx_newspaper_Extra_Typo3_CE extends tx_newspaper_Extra {

	public function __construct($uid = 0) {
		if ($uid) {
			parent::__construct($uid); 
		}
	}
	
	public function __toString() {
		return 'Extra: UID ' . $this->getExtraUid() . ', Image: UID ' . $this->getUid() .
				' (Content Element(s): ' . $this->getAttribute('content_elements') . ')';	
	}
	
	/** Just a quick hack to see anything
	 */
	public function render($template_set = '') {

		//  \see http://www.nabble.com/rendering-a-modified-tt_content-record-inside-of-plugin-td22804683.html
		/** Render the TypoScript equivalent of
		 *  \code
		 *  ce = RECORDS
		 *  ce {
		 *		tables = tt_content 
		 *		source = ... [, ...] 
		 *		dontCheckPid = 1
		 *	}
		 *  \endcode
		 */
		$tt_content_conf = array(
			'tables' => 'tt_content',
			'source' => $this->getAttribute('content_elements'),
			'dontCheckPid' => 1
		);
		$cObj = t3lib_div::makeInstance('tslib_cObj'); 
		return 'tx_newspaper_Extra_Typo3_CE::render(): ' . $cObj->RECORDS($tt_content_conf); 		
	}

//TODO: getLLL
	public function getTitle() {
		return 'Typo3 Content Element(s)';
	}

// title for module
	static function getModuleName() {
		return 'np_typo3_ce';
	}
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_Typo3_CE());

?>