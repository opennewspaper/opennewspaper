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
	public function render($template = '') {
		$list = tx_newspaper::getSection()->getList();
		foreach ($list->getArticles(10) as $article) {
			$ret .= "<p>".print_r($article, 1)."</p>\n";
		}
		return "<h1>Section List Plugin - coming soon to a page near you</h1>\n".
		"<p>".print_r($list, 1)."</p>\n".$ret;
	}

	static function getTitle() {
		return 'SectionList';
	}

	static function getModuleName() {
		return 'np_sect_ls'; 
	}
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_extra_SectionList());

?>