<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

class tx_newspaper_extra_ArticleList extends tx_newspaper_Extra {

	public function __construct($uid = 0) { 
		if ($uid) {
			parent::__construct($uid); 
			$this->attributes = $this->readExtraItem($uid, $this->getTable());
			$this->articlelist = tx_newspaper_ArticleList_Factory::getInstance()->create(
				$this->getAttribute('articlelist')
			);
			if (!$this->articlelist instanceof tx_newspaper_ArticleList) {
				throw new tx_newspaper_InconsistencyException(
					'Extra ArticleList has associated article list set to UID ' . $this->getUid() .
					', which does not resolve to a valid article list.'
				);
			}
		}
	}
	
	/** Assign the list of articles to a Smarty template. The template must 
	 *  contain all the logic to display the articles.
	 */
	public function render($template_set = '') {
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

	public function getTitle() {
		return 'ArticleList';
	}

	public static function getModuleName() {
		return 'np_artlist'; 
	}
	
	public static function dependsOnArticle() { return false; }
		
	private $articlelist;
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_extra_ArticleList());

?>