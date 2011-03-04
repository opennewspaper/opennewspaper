<?php
 
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');
 
/// tx_newspaper_Extra that renders 
/** This Extra is used to place HTML in an article 
 */

class tx_newspaper_Extra_HTML extends tx_newspaper_Extra {

	const description_length = 50; 
	
	/// Constructor
	public function __construct($uid = 0) {
		if ($uid) {
			parent::__construct($uid); 
		}
	}
	
	public function __toString() {
		try {
			return 'Extra: UID ' . $this->getExtraUid() . ', Ad Extra: UID ' . $this->getUid();
		} catch(Exception $e) {
			return "Ad: Exception thrown!" . $e;
		}	
	}
	
	/** Render a piece of HTML.
	/*  Smarty template:
	 *  \include res/templates/tx_newspaper_extra_html.tmpl
	 */	
	public function render($template_set = '') {
				
		$rendered = $this->getAttribute('html');
		
		## t3 developer log error handling 
		#  tx_newspaper::devlog("render", $rendered); 
		
		$this->prepare_render($template_set);
		
		$template = $this->getAttribute('template');
		if ($template) {
			if (strpos($template, '.tmpl') === false) {
				$template .= '.tmpl';
			}
		} else {
			$template = $this;
		}

        $rendered = $this->smarty->fetch($template);   
        
        return $rendered;
	}

	/** Displays the title and the beginning of the text.
	 */
	public function getDescription() {
		return substr(
		
			'<strong>' . $this->getAttribute('title') . '</strong> ' . $this->getAttribute('notes'), 
			0, self::description_length+2*strlen('<strong>')+1);
	}
	
		/// title for module
	public static function getModuleName() {
		return 'np_extra_html';
	}
	
	public static function dependsOnArticle() { return false; }
	
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_HTML());

?>