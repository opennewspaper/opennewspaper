<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

class tx_newspaper_Extra_DisplayArticles extends tx_newspaper_Extra {

	public function __construct($uid = 0) {
		if ($uid) {
			parent::__construct($uid); 
		}
	}
	
	public function __toString() {
		try {
		return 'Extra: UID ' . $this->getExtraUid() . ', Display Articles Extra: UID ' . $this->getUid();
		} catch(Exception $e) {
			return "Display Articles: Exception thrown!" . $e;
		}	
	}
	
	/** Display the article denoted by $_GET['art']
	 */
	public function render($template_set = '') {
		/// find current section's default article and read its template set
		$default_article = $this->getPageZone()->getParentPage()->getParentSection()->getDefaultArticle();
		if ($default_article->getAttribute('template_set')) {
			$template_set = $default_article->getAttribute('template_set');
		}
		$article = new tx_newspaper_article(t3lib_div::_GP(tx_newspaper::GET_article()));
		return $article->render($template_set);
	}

	/// \todo getLLL
	public function getTitle() {
		return 'Display Articles';
	}

	public function getDescription() {
		return 'Display Articles';
	}

	/// title for module
	public static function getModuleName() {
		return 'np_displayarticles';
	}
	
	public static function dependsOnArticle() { return false; }
	
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_DisplayArticles());

?>