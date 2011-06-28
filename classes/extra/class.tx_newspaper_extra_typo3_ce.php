<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

/// tx_newspaper_Extra displaying one or several Typo3 Content Elements
/** Insert this Extra on Page Zones (or Articles, if you find a use for that) to
 *  display an existing Typo3 Content Element. Content Elements can be grouped 
 *  to show more than one CE.
 * 
 *  Attributes:
 *  - \p pool (bool)
 *  - \p content_elements (UIDs to table \p tt_content)
 */
class tx_newspaper_Extra_Typo3_CE extends tx_newspaper_Extra {

	public function __construct($uid = 0) {
		if ($uid) {
			parent::__construct($uid); 
		}
	}
	
	public function __toString() {
		try {
		return 'Extra: UID ' . $this->getExtraUid() . ', Typo3 CE Extra: UID ' . $this->getUid() .
				' (Content Element(s): ' . $this->getAttribute('content_elements') . ')';
		} catch(Exception $e) {
			return "Typo3 CE: Exception thrown!" . $e;
		}	
	}
	
	/** Use TypoScript to render the selected Content Elements
	 * 
	 *  \param $template_set Ignored
	 * 
	 *  \todo It's probably not wise to print the whole content of the DB records
	 *  	inside a HTML comment.
	 */
	public function render($template_set = '') {

        tx_newspaper::startExecutionTimer();

        $this->prepare_render($template_set);

        $template = $this->getAttribute('template');
        if ($template) {
            if (strpos($template, '.tmpl') === false) {
                $template .= '.tmpl';
            }
        } else {
            $template = $this;
        }

		$ret = '';
        /** @var tslib_cObj $cObj  */
		$cObj = t3lib_div::makeInstance('tslib_cObj');

		foreach (explode(',', $this->getAttribute('content_elements')) as $ce_uid) {
			$ret .= "\n" . '<!-- ' . 
				print_r( tx_newspaper::selectOneRow('*', 'tt_content', "uid = $ce_uid"), 1) .
				' -->' . "\n";
			
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
			
			//	In BE (for unit tests), RECORDS() can't be used. Just show something.
			if (TYPO3_MODE == 'BE') {
				$row = tx_newspaper::selectOneRow('*', 'tt_content', "uid = $ce_uid");
				$ret .= '<h2>' . $row['header'] . "</h2>\n" .
					'<img src="uploads/pics/' . $row['image'] . '" />' . "\n" .
					$row['text'] . "\n";
			} else {
				$ret .= $cObj->RECORDS($tt_content_conf);
			}
		}
		
        tx_newspaper::logExecutionTime();
		
		return $ret;
	}

	/** If the CE has a header, display the header. Else if it has a titleText,
	 *  display that. Else just display its UID. 
	 *  \todo This could probably be improved, especially for multiple CEs.
	 */
	public function getDescription() {
		try {
			$row = tx_newspaper::selectOneRow('*', 'tt_content', 'uid = ' . $this->getAttribute('content_elements'));
			if ($row['header']) return $row['header'];
			if ($row['titleText']) return $row['titleText'];
		} catch (tx_newspaper_DBException $e) { }
		
		return 'Content Element ' . $this->getAttribute('content_elements');
	}

// title for module
	public static function getModuleName() {
		return 'np_typo3_ce';
	}
	
	public static function dependsOnArticle() { return false; }
	
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_Typo3_CE());

?>