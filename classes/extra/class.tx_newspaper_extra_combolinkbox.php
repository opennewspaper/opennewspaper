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
		
		$this->prepare_render($template_set);

		if ($this->getAttribute('show_related_articles') &&
			intval(t3lib_div::_GP(tx_newspaper::GET_article()))) {
			$current_article = new tx_newspaper_article(t3lib_div::_GP(tx_newspaper::GET_article()));

			$rows = tx_newspaper::selectRows(
				tx_newspaper_Article::article_related_table . '.uid_local, ' . tx_newspaper_Article::article_related_table .'.uid_foreign',
				tx_newspaper_Article::article_related_table .
					'JOIN tx_newspaper_article AS a_local
ON tx_newspaper_article_related_mm.uid_local = a_local.uid
JOIN tx_newspaper_article AS a_foreign
ON tx_newspaper_article_related_mm.uid_foreign= a_foreign.uid',

				'(uid_local = ' . $current_article->getUid() .
				' OR uid_foreign = ' . $current_article->getUid() . ')' .
				' AND (a_foreign.hidden = 0 AND a_local.hidden = 0)'
			);

			$articles = array();
			
			foreach ($rows as $row) {
				if (intval($row['uid_local']) == $current_article->getUid()) {
					if (intval($row['uid_foreign']) != $current_article->getUid()) {
						$articles[] = new tx_newspaper_Article(intval($row['uid_foreign']));
					}
				} else if ($row['uid_foreign'] == $current_article->getUid()) {
					if (intval($row['uid_local']) != $current_article->getUid()) {
						$articles[] = new tx_newspaper_Article(intval($row['uid_local']));
					}
				}
			}
			
			$this->smarty->assign('related_articles', $articles);
		}
	
		if ($this->getAttribute('manually_selected_articles')) {
			$articles = array();
			foreach (explode(',', $this->getAttribute('manually_selected_articles')) as $article_uid) {
				$articles[] = new tx_newspaper_Article(intval(trim($article_uid)));
			}
			$this->smarty->assign('manually_selected_articles', $articles);
		}
		
		if ($this->getAttribute('internal_links')) {
			$links = array();
			foreach (explode(',', trim($this->getAttribute('internal_links'))) as $link_uid) {
				$links[] = new tx_newspaper_ExternalLink(intval(trim($link_uid)));
			}
			$this->smarty->assign('internal_links', $links);
		}

		if ($this->getAttribute('external_links')) {
			$links = array();
			foreach (explode(',', trim($this->getAttribute('external_links'))) as $link_uid) {
				$links[] = new tx_newspaper_ExternalLink(intval(trim($link_uid)));
			}
			$this->smarty->assign('external_links', $links);
		}

		return $this->smarty->fetch($this);
	}

	/// Displays the Tag Zone operating on.
	public function getDescription() {
		return $this->getTitle() . '(' . $this->getUid() . ')';
	}

	public static function getModuleName() {
		return 'np_combo_link_box';
	}
	
	public static function dependsOnArticle() { return true; }
	
	////////////////////////////////////////////////////////////////////////////
		
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_ComboLinkBox());

?>