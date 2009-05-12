<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

class tx_newspaper_Extra_Typo3_CE extends tx_newspaper_Extra {

	public function __construct($uid = 0) {
		if ($uid) {
			parent::__construct($uid); 
		}
	}
	
	public function __toString() {
		return 'Extra: UID ' . $this->getExtraUid() . ', Typo3 CE Extra: UID ' . $this->getUid() .
				' (Content Element(s): ' . $this->getAttribute('content_elements') . ')';	
	}
	
	/** Just a quick hack to see anything
	 */
	public function render($template_set = '') {

		$ret = '';
		$cObj = t3lib_div::makeInstance('tslib_cObj');

		foreach (explode(',', $this->getAttribute('content_elements')) as $ce_uid) {
			$raw_data = tx_newspaper::selectOneRow('*', 'tt_content', "uid = $ce_uid");
			$ret .= "\n" . '<-- ' . print_r($raw_data, 1) . ' -->' . "\n";
			
			//  \see http://www.nabble.com/rendering-a-modified-tt_content-record-inside-of-plugin-td22804683.html
			/** Render the TypoScript equivalent of
			 *  \code
			 *  ce = RECORDS
			 *  ce {
			 *		tables = tt_content 
			 *		source = ...  
			 *		dontCheckPid = 1
			 *	}
			 *  \endcode
			 *  I could render them all at once as a comma-separated list of 
			 *  UIDs instead of in a foreach-loop, but I don't trust that 
			 *  feature...
			 */
			$tt_content_conf = array(
				'tables' => 'tt_content',
				'source' => intval($ce_uid),
				'dontCheckPid' => 1
			);
			if (TYPO3_MODE == 'BE') {
				$row = tx_newspaper::selectOneRow('*', 'tt_content', "uid = $ce_uid");
				$ret .= '<h2>' . $row['header'] . "</h2>\n" .
					'<img src="uploads/pics/' . $row['image'] . '" />' . "\n" .
					$row['text'] . "\n";
			} else {
				$ret .= $cObj->RECORDS($tt_content_conf);
			}
		}
		return $ret;
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