<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

/// A tx_newspaper_Extra that displays a bio for a contributor
/** A photo of an author is displayed along with some biographical text.
 *  \todo Import the box automatically from the pool when the Article is
 * 		imported.
 */
class tx_newspaper_extra_Bio extends tx_newspaper_Extra {

	/// Boa Constructor ;-)
	public function __construct($uid = 0) { 
		if (intval($uid)) {
			parent::__construct($uid); 
		}
	}
	
	/** Assign the attributes to a Smarty template.
	 *  \param $template_set Template set to use
	 */
	public function render($template_set = '') {
		t3lib_div::devlog('tx_newspaper_extra_Bio::render()', 'newspaper', 0, 
			array(
				'uid' => $this->getUid(), 
				'extra uid' => $this->getExtraUid(),
			)
		);

		$this->prepare_render($template_set);
		
		// ...
		
		return $this->smarty->fetch($template);
	}

	public static function getModuleName() {
		return 'np_artlist'; 
	}
	
	public static function dependsOnArticle() { return false; }
		
	private $articlelist;
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_extra_ArticleList());

?>