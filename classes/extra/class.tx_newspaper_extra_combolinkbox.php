<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/extra/class.tx_newspaper_extra_externallinks.php');

/// tx_newspaper_Extra displaying a box of links of various type
/** Contains links:
 *  - to Articles which are grouped with the current article automatically
 *  - to manually selected Articles
 *  - to links on the same site which are not to Articles
 *  - to external URLs.
 *  Articles which are grouped as "related" are selected in the GUI for every
 *  Article. Internal and external links are technically the same, but separated
 *  for layout reasons.
 *
 *  Attributes:
 *  - \p show_related_articles (bool)
 *  - \p manually_selected_articles (comma-separated list of article UIDs)
 *  - \p internal_links (comma-separated list of tx_newspaper_ExternalLink UIDs)
 *  - \p external_links (comma-separated list of tx_newspaper_ExternalLink UIDs)
 */
class tx_newspaper_Extra_ComboLinkBox extends tx_newspaper_Extra {

	const article_table = 'tx_newspaper_article';

	public function __construct($uid = 0) {
		if ($uid) {
			parent::__construct($uid);
		}
	}

	public function __toString() {
		try {
		return 'Extra: UID ' . $this->getExtraUid() . ', Combo link box Extra: UID ' . $this->getUid();
		} catch(Exception $e) {
			return "ComboLinkBox: Exception thrown!" . $e;
		}
	}

	/// Assigns Articles and Links to the smarty template and renders it.
	/** Smarty template:
	 *  \include res/templates/tx_newspaper_extra_combolinkbox.tmpl
	 */
	public function render($template_set = '') {

        tx_newspaper_ExecutionTimer::start();

		$this->getAttribute('uid');

		$this->prepare_render($template_set);

		if ($this->getAttribute('show_related_articles') &&
			intval(t3lib_div::_GP(tx_newspaper::GET_article()))) {

			$this->smarty->assign('related_articles', $this->getRelatedArticles());
		}

		if ($this->getAttribute('manually_selected_articles')) {
			$this->smarty->assign('manually_selected_articles', $this->getManuallySelectedArticles());
		}

		if ($this->getAttribute('internal_links')) {
			$this->smarty->assign('internal_links', $this->getInternalLinks());
		}

		if ($this->getAttribute('external_links')) {
			$this->smarty->assign('external_links', $this->getExternalLinks());
		}

        $rendered = $this->smarty->fetch($this->getSmartyTemplate());

        tx_newspaper_ExecutionTimer::logExecutionTime();

        return $rendered;
	}

	/// Displays the Tag Zone operating on.
	public function getDescription() {
		return $this->getAttribute('short_description');
	}

	public static function getModuleName() {
		return 'np_combo_link_box';
	}

	public static function dependsOnArticle() { return true; }

	////////////////////////////////////////////////////////////////////////////

	private function getRelatedArticles() {
        $current_article = new tx_newspaper_Article(t3lib_div::_GP(tx_newspaper::GET_article()));

        return $current_article->getRelatedArticles();
	}

	private function getManuallySelectedArticles() {
        $articles = array();
        foreach ($this->getValidArticleUids() as $article_uid) {
            $articles[] = new tx_newspaper_Article(intval(trim($article_uid)));
        }
        return $articles;
	}

    private function getValidArticleUids() {
        $rows = tx_newspaper::selectRows(
            'uid',
            'tx_newspaper_article',
            'uid IN (' . $this->getAttribute('manually_selected_articles') . ')'
        );

        return array_map('array_pop', $rows);
    }

    private function getInternalLinks() {
        return self::getLinks($this->getAttribute('internal_links'));
    }

    private function getExternalLinks() {
    	return self::getLinks($this->getAttribute('external_links'));
    }

    private static function getLinks($links_csv) {
        $links = array();
        foreach (explode(',', trim($links_csv)) as $link_uid) {
            $links[] = new tx_newspaper_ExternalLink(intval(trim($link_uid)));
        }
        return $links;
    }

}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_ComboLinkBox());

?>