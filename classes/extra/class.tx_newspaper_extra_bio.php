<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

/// A tx_newspaper_Extra that displays a bio for a contributor
/** A photo of an author is displayed along with some biographical text.
 *  \todo Import the box automatically from the pool when the Article is
 * 		imported.
 */
class tx_newspaper_extra_Bio extends tx_newspaper_Extra_Image {

	const description_length = 50; 

	/// Boa Constructor ;-)
	public function __construct($uid = 0) { 
		if (intval($uid)) {
			parent::__construct($uid); 
		}
	}
	
	/** Assign the attributes to a Smarty template.
	 *  \param $template_set Template set to use
	 */
/*	public function render($template_set = '') {
		t3lib_div::devlog('tx_newspaper_extra_Bio::render()', 'newspaper', 0, 
			array(
				'uid' => $this->getUid(), 
				'extra uid' => $this->getExtraUid(),
			)
		);

		$this->prepare_render($template_set);
		
		// ...
		
		return $this->smarty->fetch($this);
	}
*/
	/// A description to identify the bio box in the BE
	/** Shows the author's name and the start of the text.
	 */
	public function getDescription() {
		return substr(
			'<strong>' . $this->getAttribute('author_name') . '</strong> ' .
				$this->getAttribute('bio_text'), 
			0, self::description_length+2*strlen('<strong>')+1) .
			(strlen($this->getAttribute('author_name') . ' ' . $this->getAttribute('bio_text')) > self::description_length?
				'...': '');
	}

	public static function getModuleName() {
		return 'np_bio'; 
	}
	
	public static function dependsOnArticle() { return true; }
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_extra_Bio());

?>