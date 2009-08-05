<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

class tx_newspaper_extra_SectionList extends tx_newspaper_Extra {

	public function __construct($uid = 0) { 
		if ($uid) {
			parent::__construct($uid); 
			$this->attributes = $this->readExtraItem($uid, $this->getTable());		
		}
	}
	
	/** Just a quick hack to see anything
	 *  \todo make number of articles displayed variable
	 *  \todo smarty template
	 */
	public function render($template_set = '') {
		$this->prepare_render($template_set);
		
		$list = tx_newspaper::getSection()->getArticleList();

		$articles = $list->getArticles($this->getAttribute('num_articles'), 
									   $this->getAttribute('first_article'));
		
		$this->smarty->assign('articles', $articles);
		
		return $this->smarty->fetch($this);
	}

	public function getTitle() {
		return 'SectionList';
	}

	public static function getModuleName() {
		return 'np_sect_ls'; 
	}

	public static function dependsOnArticle() { return false; }
	
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_extra_SectionList());

?>