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
	 *  \todo everything
	 */
	public function render($template_set = '') {
		$list = tx_newspaper::getSection()->getArticleList();
		foreach ($list->getArticles(10) as $article) {
			t3lib_div::devlog('article', 'newspaper', 0, $article);			
			$ret .= '<a href="' . $article->getLink() . '">';
			$ret .= "<h1>".$article->getAttribute('title')."</h1>\n";
			$ret .= "<p>".$article->getAttribute('teaser')."</p>\n";
			$ret .= "</a>\n";
			$ret .= "<hr>\n";
		}
		return "<h1>Section List Plugin - coming soon to a page near you</h1>\n".$ret;
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