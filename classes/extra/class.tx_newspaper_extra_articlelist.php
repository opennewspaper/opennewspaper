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
 *  
 *  Attributes:
 *  - \p description (string)
 *  - \p articlelist (UID of abstract record for displayed article list)
 *  - \p first_article (int)
 *  - \p num_articles (int)
 *  - \p template (string)
 */
class tx_newspaper_extra_ArticleList extends tx_newspaper_Extra {

	/// Boa Constructor ;-)
	/** Instantiates the associated Article List too. */
	public function __construct($uid = 0) { 
		if (intval($uid)) {
			parent::__construct($uid); 
		}
	}
	
	function getDescription() {
		try {
		$this->readArticleList();
		
		return 'Display a list of articles: ' . 
			$this->articlelist->getDescription();
		} catch (tx_newspaper_DBException $e) {
			return 
				'<table><tr><td valign="middle">' .
					tx_newspaper_BE::renderIcon('gfx/icon_warning2.gif', '') .
				'</td><td>' . 
					tx_newspaper::getTranslation('message_articlelist_missing_deleted') . '<br />' .
					tx_newspaper::getTranslation('message_select_another_articlelist') .
				'</td></tr></table>';
		}
		
	}
	
	/** Assign the list of articles to a Smarty template. The template must 
	 *  contain all the logic to display the articles.
	 *  \param $template_set Template set to use
	 * 
	 *  Smarty template:
	 *  \include res/templates/tx_newspaper_extra_articlelist.tmpl
	 */
	public function render($template_set = '') {
        
        tx_newspaper::startExecutionTimer();
        
		$this->prepare_render($template_set);
		
		try {
			$this->readArticleList();
		} catch (tx_newspaper_EmptyResultException $e) {
			return $this->smarty->fetch('error_articlelist_missing.tmpl');
		}
		
		$num = $this->getAttribute('num_articles')? $this->getAttribute('num_articles'): 1;
		$first = $this->getAttribute('first_article')? $this->getAttribute('first_article')-1: 0;
		$articles = $this->articlelist->getArticles($num, $first);
		
		$template = $this->getAttribute('template');
		if ($template) {
			if (strpos($template, '.tmpl') === false) $template .= '.tmpl';
		} else {
			$template = $this;
		}

		/*		
		foreach ($articles as $art) $art->getAttribute('uid');		
		t3lib_div::devlog('tx_newspaper_extra_ArticleList::render()', 'newspaper', 0, 
			array(
				'uid' => $this->getUid(), 
				'extra uid' => $this->getExtraUid(),
				'article list' => $this->articlelist
				'articles' => $articles
			)
		);
		*/
		$this->smarty->assign('articles', $articles);
 
        $rendered = $this->smarty->fetch($template);
        
        tx_newspaper::logExecutionTime();
        
        return $rendered;
	}

	public function getSearchFields() {
		return array('description');
	}

	public static function getModuleName() {
		return 'np_artlist'; 
	}
	
	public static function dependsOnArticle() { return false; }
	
	private function readArticlelist() {
		
		if ($this->articlelist instanceof tx_newspaper_ArticleList) return;
		
		$this->articlelist = tx_newspaper_ArticleList_Factory::getInstance()->create(
			$this->getAttribute('articlelist')
		);
	}
		
	private $articlelist;
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_extra_ArticleList());

?>