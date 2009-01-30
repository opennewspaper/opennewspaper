<?php

require_once(BASEPATH . '/typo3conf/ext/newspaper/classes/class.tx_newspaper_extraimpl.php');

class tx_newspaper_extra_SectionList extends tx_newspaper_ExtraImpl {

	public function __construct($uid = 0) { if ($uid) parent::__construct($uid); }
	
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


	static function getName() {
		return 'tx_newspaper_extra_sectionlist';
	}

	static function getTitle() {
		return 'SectionList';
	}

	static function getModuleName() {
		return 'npe_sect_l'; 
	}
}

tx_newspaper_ExtraImpl::registerExtra(new tx_newspaper_extra_ArticleRenderer());

?>