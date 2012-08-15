<?php

require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_extra.php');

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

	const DEFAULT_NUM_ARTICLES = 10;

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

		$first = $this->getAttribute('first_article')? $this->getAttribute('first_article')-1: 0;
		$num = $this->getAttribute('num_articles')? $this->getAttribute('num_articles'): self::DEFAULT_NUM_ARTICLES;
		$articles = $list->getArticles($num, $first);

		$this->smarty->assign('articles', $articles);
        $this->smarty->assign('section_id', tx_newspaper::getSection()->getUid());
        $this->smarty->assign('typo3_page', tx_newspaper::getSection()->getTypo3PageID());

        $this->smarty->assign('rootline', self::getRootline());

        $rendered = $this->smarty->fetch($this->getSmartyTemplate());

        return $rendered;
	}

	public function getDescription() {
		return $this->getAttribute('short_description');
	}

	public static function getModuleName() {
		return 'np_sect_ls';
	}

	public static function dependsOnArticle() { return false; }

    public static function getRootline() {
        $rootline = tx_newspaper::getSection()->getSectionPath();
        foreach ($rootline as $key => $section) {
            $rootline[$key] = $section->getAttribute('section_name');
        }

        return $rootline;
    }

}
tx_newspaper_Extra::registerExtra(new tx_newspaper_extra_SectionList());

?>