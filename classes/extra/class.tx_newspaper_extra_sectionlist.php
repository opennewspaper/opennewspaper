<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

/// tx_newspaper_Extra displaying articles belonging to a tx_newspaper_Section
/** This Extra displays articles belonging to the Section that belongs to the 
 *  page on which this Extra is inserted. 
 *
 *  Insert this Extra on all Page Zones which show an overview of the articles 
 *  in the current Section.
 * 
 *  Attributes:
 *  
 *  \todo  make number of articles displayed variable
 */
class tx_newspaper_extra_SectionList extends tx_newspaper_Extra {

	public function __construct($uid = 0) { 
		if ($uid) {
			parent::__construct($uid); 
			$this->attributes = $this->readExtraItem($uid, $this->getTable());		
		}
	}
	
	/** Display articles belonging to the current section.
	 * 
	 *  Smarty template:
	 *  \include res/templates/tx_newspaper_extra_sectionlist.tmpl
	 * 
	 *  \todo make number of articles displayed variable
	 *  \todo WHat if the current section has no article list? (is this even possible?)
	 */
	public function render($template_set = '') {
		$this->prepare_render($template_set);
		
		$list = tx_newspaper::getSection()->getArticleList();

		$articles = $list->getArticles(10, 0);
		
		$this->smarty->assign('articles', $articles);
		
		return $this->smarty->fetch($this);
	}

	public static function getModuleName() {
		return 'np_sect_ls'; 
	}

	public static function dependsOnArticle() { return false; }
	
}
tx_newspaper_Extra::registerExtra(new tx_newspaper_extra_SectionList());

?>