<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

/// tx_newspaper_Extra that displays the contents of an article
/** This Extra must be inserted on a Page Zone wherever an Article must be 
 *  displayed.
 */  
class tx_newspaper_Extra_DisplayArticles extends tx_newspaper_Extra {

	/// Constructor
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
	
	/// Display the article denoted by <tt>$_GET['art']</tt>
	/** ...or more accurately, <tt>$_GET[tx_newspaper::GET_article()]</tt>,
	 *  where \p tx_newspaper::GET_article() defaults to 'art'.
	 *  
	 *  \param template_set The template set used to render. This is overridden
	 *  	by the template set of the default article of the current section,
	 * 		if it is set. So it probably doesn't make much sense to use 
	 * 		\p $template_set.
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

	public function getDescription() {
		return $this->getTitle();
	}

	/// title for module
	public static function getModuleName() {
		return 'np_displayarticles';
	}
	
	public static function dependsOnArticle() { return false; }
	
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_DisplayArticles());

?>