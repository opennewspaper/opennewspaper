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
	
	/** Just a quick hack to see anything
	 *  \todo everything
	 */
	public function render($template_set = '') {
		$articles = $this->articlelist->getArticles($this->getAttribute('num_articles'), 
													$this->getAttribute('first_article'));
		
		$this->smarty->assign('articles', $articles);
	}

	public function getTitle() {
		return 'SectionList';
	}

	static function getModuleName() {
		return 'np_sect_ls'; 
	}
	
	private $articlelist;
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_extra_SectionList());

?>