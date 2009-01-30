<?php

require_once(BASEPATH . '/typo3conf/ext/newspaper/classes/class.tx_newspaper_extraimpl.php');

class tx_newspaper_extra_SectionList extends tx_newspaper_ExtraImpl {

	public function __construct($uid = 0) { if ($uid) parent::__construct($uid); }
	
	/** Just a quick hack to see anything
	 *  \todo everything
	 */
	public function render($template = '') {
		$section = tx_newspaper::getSection();
		return "<h1>Section List Plugin - coming soon to a page near you</h1>\n".
		"<p>".print_r($section, 1)."</p>\n";
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