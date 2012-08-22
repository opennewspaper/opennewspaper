<?php
/**
 * @author Lene Preuss <lene.preuss@gmail.com>
 */


/**
 *  article list functions (for mod7/mod9)
 */
class tx_newspaper_PlacementBE {

    public static function renderSingle($input) {

   		if (isset($input['sectionid'])) {       // render section article list
            if (intval($input['fullrecord'])) {
                $smarty = new tx_newspaper_Smarty();
                $smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod7/res/'));
                $smarty->assign('AL_BACKEND', self::getArticlelistFullrecordBackend($input, self::getArticleListForPlacement(array($input['sectionid']))));
                return $smarty->fetch('mod7_listview.tmpl');
            }
   			return self::render(
                array(
   				    'sections_selected' => array($input['sectionid']),
   					'placearticleuid' => intval($input['articleid']),
   					'fullrecord' => intval($input['fullrecord'])
   				), true
            );
   		}

   		if (isset($input['articlelistid'])) {   // render NON-section article list
   			return self::render($input, true);
   		}

        throw new tx_newspaper_IllegalUsageException(
            'tx_newspaper_PlacementBE::renderSingle() called neither for section article list nor free articlelist: ' . print_r($input, 1)
        );
   	}

    /// render the placement editors according to sections selected for article
	/** If $input['articleid'] is a valid uid an add/remove button for this article will be rendered,
	 *  if not, a button to call the article browser is displayed.
	 *  @todo: document $input array types ...
	 *  @param $input \c t3lib_div::GParrayMerged('tx_newspaper_mod7')
	 *  @return ?
	 */
	public static function render($input, $singleMode=false) {

		$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod7/res/'));

        $smarty->assign('tree', self::getSectionTree($input));
        $al = self::getArticleListForPlacement($input);
		if (!is_null($al)) {
			$smarty->assign('articlelist_type', strtolower($al->getTable()));
			$smarty->assign('articles', self::getArticlesFromListForPlacement($al));
		}
        $smarty->assign('article', self::getArticleForPlacement($input));

		$smarty->assign('singlemode', $singleMode);
		$smarty->assign('lang', self::getLocallangLabels());
		$smarty->assign('isde', tx_newspaper_workflow::isDutyEditor());
        $smarty->assign('allowed_placement_level', tx_newspaper_Workflow::placementAllowedLevel());

        $smarty->assign('ICON', tx_newspaper_BE::getArticlelistIcons());
		$smarty->assign('T3PATH', tx_newspaper::getAbsolutePath(true));
		$smarty->assign('SEMIAUTO_AL_FOLDED', true); // \todo: make configurable (tsconfig)
		$smarty->assign('AL_HEIGHT', self::getArticleListHeight());

        $smarty->assign('input', $input);

		return $smarty->fetch(self::getSmartyTemplateForPlacement($input));
	}

    ////////////////////////////////////////////////////////////////////////////

    private static function getSectionTree(array $input) {
        if (self::sectionArticleListRequested($input) || self::singleArticlePlacementRequested($input)) {
            $tree = array_reverse(self::calculatePlacementTreeFromSelection($input['sections_selected']));
            return self::fillPlacementWithData($tree, $input['placearticleuid']); // is called no matter if $input['placearticleuid'] is set or not
        }
        return array();
    }

    private static function getArticleListForPlacement(array $input) {
        if (isset($input['articlelistid']) && $input['articlelistid']) {
            return tx_newspaper_ArticleList_Factory::getInstance()->create(intval($input['articlelistid']));
        }
        return null;
    }

    private static function getArticlesFromListForPlacement(tx_newspaper_ArticleList $al) {
        $articles = array();
        foreach (tx_newspaper_BE::getArticleListMaxArticles($al) as $article) {
            if ($al->getTable() == 'tx_newspaper_articlelist_manual') {
                $articles[$article->getAttribute('uid')] = $article->getAttribute('kicker') . ': ' . $article->getAttribute('title');
            } else if ($al->getTable() == 'tx_newspaper_articlelist_semiautomatic') {
                $articleUids = tx_newspaper_BE::getArticleIdsFromArticleList($al);
                $offsetList = $al->getOffsets($articleUids);

                $offset = $offsetList[$article->getAttribute('uid')];
                if ($offset > 0) {
                    $offset = '+' . $offset;
                }
                $articles[$offsetList[$article->getAttribute('uid')] . '_' . $article->getAttribute('uid')] = $article->getAttribute('kicker') . ': ' . $article->getAttribute('title') . ' (' . $offset . ')';
            }
        }
        return $articles;
    }

    /// grab the article, if an article id was given
    private static function getArticleForPlacement(array $input) {
        if (isset($input['placearticleuid']) && $input['placearticleuid']) {
            return new tx_newspaper_Article($input['placearticleuid']);
        } else {
            return null;
        }
    }

    private static function getLocallangLabels() {
        $localLang = t3lib_div::readLLfile('typo3conf/ext/newspaper/mod7/locallang.xml', $GLOBALS['LANG']->lang);
        return $localLang[$GLOBALS['LANG']->lang];
    }

    /**
     * Render backend for article list configuration form if $input['fullrecord'] is set to 1
     * @param array $input
     * @param null|tx_newspaper_Articlelist $al
     * @return Backend form or empty string, if $input['fullrecord'] is not set to 1
     */
    private static function getArticlelistFullrecordBackend(array $input, tx_newspaper_Articlelist $al = null) {
        if (!intval($input['fullrecord'])) return '';

        if (is_null($al)) {
		    // article list hasn't been read
			if (is_array($input['sections_selected']) && sizeof($input['sections_selected']) > 0) {
				$s = new tx_newspaper_Section(intval($input['sections_selected'][0])); // Get article list for first (and only) section
				$al = $s->getArticleList();
			}
		}

		if (!is_null($al)) return $al->getAndProcessTceformBasedBackend(); // Render backend, store if saved, close if closed

		return 'Error'; // \todo: localization
    }

   	/// Gets the height (rows) for an article list select box
	private static function getArticleListHeight() {
		return 10; // \todo: make tsconfigurable
	}

    private static function getSmartyTemplateForPlacement(array $input) {
        if (self::sectionArticleListRequested($input) || self::singleArticlePlacementRequested($input)) {
            return 'mod7_placement_section.tpl';
        }
        if (isset($input['articlelistid']) && $input['articlelistid']) {
            return 'mod7_placement_non_section.tpl';
        }
        throw new tx_newspaper_IllegalUsageException(
            'tx_newspaper_PlacementBE::render() called neither for section article list, single article nor free articlelist: ' . print_r($input, 1)
        );
    }

    private static function sectionArticleListRequested(array $input) {
        return (isset($input['sections_selected']) && sizeof($input['sections_selected']) > 0);
    }

    private static function singleArticlePlacementRequested(array $input) {
        return (isset($input['ajaxcontroller']) && $input['ajaxcontroller'] == 'showplacementandsavesections');
    }

	/// calculate a "minimal" (tree-)list of sections
	private static function calculatePlacementTreeFromSelection($selection) {
		$result = array();

		//\todo: re-arrange sorting here to achieve different positioning in frontend
		for ($i = 0; $i < count($selection); ++$i) {
			$selection[$i] = explode('|', $selection[$i]);
			$ressort = array();
			for ($j = 0; $j < count($selection[$i]); ++$j) {
				$ressort[]['uid'] = $selection[$i][$j];
				if(!isset($result[$j]) || !in_array($ressort, $result[$j])) {
					$result[$j][] = $ressort;
				}
			}
		}
		return $result;
	}

	/// get article and offset lists for a set of sections
	private static function fillPlacementWithData($tree, $articleId) {
		for ($i = 0; $i < count($tree); ++$i) {
			for ($j = 0; $j < count($tree[$i]); ++$j) {
				for ($k = 0; $k < count($tree[$i][$j]); ++$k) {
					// get data (for title display) for each section
					$tree[$i][$j][$k]['section'] = new tx_newspaper_section($tree[$i][$j][$k]['uid']);
					// add article list and list type to tree structure for last element only
					if (($k+1) == count($tree[$i][$j])) {
						$tree[$i][$j][$k]['listtype'] = get_class($tree[$i][$j][$k]['section']->getArticleList());
						$tree[$i][$j][$k]['articlelist'] = tx_newspaper_BE::getArticleListBySectionId($tree[$i][$j][$k]['uid']);
						if (strtolower($tree[$i][$j][$k]['listtype']) == 'tx_newspaper_articlelist_manual') {
							$tree[$i][$j][$k]['article_placed_already'] = array_key_exists($articleId, $tree[$i][$j][$k]['articlelist']); // flag to indicated if the article to be placed has already been placed in current article list
						} else {
							// semi-auto list: key -> [offset]_[key], so array_key_exists check like for manual list won't work
							// but an article is ALWAYS placed in a semi-auto list ...
							$tree[$i][$j][$k]['article_placed_already'] = true;
						}
					}
				}
			}
		}

		return $tree;
	}

}