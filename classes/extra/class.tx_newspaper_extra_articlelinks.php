<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

/// A tx_newspaper_Extra that can display a tx_newspaper_ArticleList
/** A rather generic class that displays an article list using a specified 
 *  smarty template.
 * 
 *  This Extra must be inserted on a Page Zone wherever a list of Articles is
 *  displayed, except for the cases which have specialized Extras:
 *  - tx_newspaper_Extra_SectionList: The list of articles belonging to a
 * 		tx_newspaper_Section 
 */
class tx_newspaper_extra_ArticleLinks extends tx_newspaper_Extra {

	/// Boa Constructor ;-)
	public function __construct($uid = 0) { 
		if (intval($uid)) {
			parent::__construct($uid); 
		}
	}
	
	/** Assign the list of articles to a Smarty template. The template must 
	 *  contain all the logic to display the articles.
	 *  \param $template_set Template set to use
	 */
	public function render($template_set = '') {
		t3lib_div::devlog('tx_newspaper_extra_ArticleLinks::render()', 'newspaper', 0, 
			array(
				'uid' => $this->getUid(), 
				'extra uid' => $this->getExtraUid(),
				'article list' => $this->articlelist
			)
		);

		$this->prepare_render($template_set);
		
		$articles = $this->articlelist->getArticles($this->getAttribute('num_articles'), 
													$this->getAttribute('first_article'));
		$template = $this->getAttribute('template');
		if ($template) {
			if (strpos($template, '.tmpl') === false) $template .= '.tmpl';
		} else {
			$template = $this;
		}
		
		$this->smarty->assign('articles', $articles);
		
		return $this->smarty->fetch($template);
	}

	public static function getModuleName() {
		return 'np_artlinks'; 
	}
	
	public static function dependsOnArticle() { return false; }
		
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_extra_ArticleLinks());

?>