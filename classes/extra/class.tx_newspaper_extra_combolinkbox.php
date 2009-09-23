<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/extra/class.tx_newspaper_extra_externallinks.php');

/// tx_newspaper_Extra displaying a box of links of various type
/** 
 */
class tx_newspaper_Extra_ComboLinkBox extends tx_newspaper_Extra {
		
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
	
	/// Assigns extras to be rendered to the smarty template and renders it.
	public function render($template_set = '') {
		
		$this->prepare_render($template_set);

		if ($this->getAttribute('show_related_articles') &&
			intval(t3lib_div::_GP(tx_newspaper::GET_article()))) {
			$current_article = new tx_newspaper_article(t3lib_div::_GP(tx_newspaper::GET_article()));

			$rows = tx_newspaper::selectRows(
				'uid_local, uid_foreign',
				tx_newspaper_Article::article_related_table,
				'uid_local = ' . $current_article->getUid() .
				' OR uid_foreign = ' . $current_article->getUid()
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