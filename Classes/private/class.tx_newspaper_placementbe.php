<?php
/**
 * @author Lene Preuss <lene.preuss@gmail.com>
 */


/**
 *  article list functions (for mod7/mod9)
 */
class tx_newspaper_PlacementBE {

    /**
     *  Possible GET-variables:
     *  - fullrecord
     *  - sectionid
     *  - articlelistid
     *  - sections_selected
     *  - placearticleuid
     *  - ajaxcontroller == 'showplacementandsavesections'
     *
     *  @param $input
     */
    public function __construct($input) {
        $this->input = $input;
        $this->smarty = new tx_newspaper_Smarty();
        $this->smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod7/res/'));
        $this->smarty->assign('ICON', tx_newspaper_BE::getArticlelistIcons());
        $this->smarty->assign('T3PATH', tx_newspaper::getAbsolutePath(true));
        $this->smarty->assign('AL_HEIGHT', self::getArticleListHeight());
        $this->smarty->assign('lang', self::getLocallangLabels());
        $this->smarty->assign('isde', tx_newspaper_workflow::isDutyEditor());
        $this->smarty->assign('allowed_placement_level', tx_newspaper_Workflow::placementAllowedLevel());
        $this->smarty->assign('input', $this->input);
    }

    public function renderSingle() {
        if (intval($this->input['fullrecord'])) return $this->renderListviewBE();

        if (intval($this->input['sectionid'])) return $this->renderSectionList();

        if (intval($this->input['articlelistid'])) return $this->renderArticleList();

        throw new tx_newspaper_IllegalUsageException(
            'tx_newspaper_PlacementBE::renderSingle() called neither for section article list nor free articlelist: ' . print_r($this->input, 1)
        );
    }

    /**
     *  Render the placement mask for all selected sections for article.
     *
     *  If $input['articleid'] is a valid uid an add/remove button for this article will be rendered,
     *  if not, a button to call the article browser is displayed.
     */
    public function render() {
        $this->smarty->assign('tree', $this->getSectionTree($this->input));
        $this->smarty->assign('article', self::getArticleForPlacement($this->input));

        return $this->smarty->fetch('mod7_placement_section.tpl');
    }

    ////////////////////////////////////////////////////////////////////////////

    private function renderListviewBE() {
        if (intval($this->input['sectionid'])) {
            $s = new tx_newspaper_Section($this->input['sectionid']);
            $al = $s->getArticleList();
        } else {
            $al = tx_newspaper_ArticleList_Factory::getInstance()->create(intval($this->input['articlelistid']));
        }

        $this->smarty->assign('AL_BACKEND', self::getArticlelistListviewBackend($al));
        return $this->smarty->fetch('mod7_listview.tmpl');
    }

    private function renderSectionList() {
        $this->smarty->assign(
            'rendered_al',
            $this->renderSectionObject(new tx_newspaper_Section($this->input['sectionid']), array('buttons' => true))
        );
        return $this->smarty->fetch('mod7_placement_single.tmpl');
    }

    private function renderArticleList() {
        $al = tx_newspaper_ArticleList_Factory::getInstance()->create(intval($this->input['articlelistid']));
        if (!is_null($al)) {
            $this->smarty->assign('articlelist', $al);
            $this->smarty->assign('articlelist_type', strtolower($al->getTable()));
            $this->smarty->assign('articles', self::getArticlesFromListForPlacement($al));
        }
        return $this->smarty->fetch('mod7_placement_non_section.tpl');
    }

    private function renderSectionObject(tx_newspaper_Section $section, array $additional_options) {
        $this->smarty->assign('sect', $section);
        $this->smarty->assign('level', sizeof($section->getRootLine())+1);
        foreach ($additional_options as $key => $value) {
            $this->smarty->assign($key, $value);
        }

        return $this->smarty->fetch('mod7_section_object.tmpl');
    }

    private function getSectionTree(array $input) {
        if (!self::sectionArticleListRequested($input) && !self::singleArticlePlacementRequested($input)) return array();

        $tree = array_reverse(self::calculatePlacementTreeFromSelection($input['sections_selected']));
        return $this->fillPlacementWithData($tree, $input['placearticleuid']); // is called no matter if $input['placearticleuid'] is set or not
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
    private static function getArticlelistListviewBackend(tx_newspaper_ArticleList $al) {

		if (!is_null($al)) return $al->getAndProcessTceformBasedBackend(); // Render backend, store if saved, close if closed

		return 'Error'; // \todo: localization
    }

    /// Gets the height (rows) for an article list select box
    private static function getArticleListHeight() {
        return 10; // \todo: make tsconfigurable
    }


    private static function sectionArticleListRequested(array $input) {
        return (isset($input['sections_selected']) && sizeof($input['sections_selected']) > 0);
    }

    private static function singleArticlePlacementRequested(array $input) {
        return (isset($input['ajaxcontroller']) && $input['ajaxcontroller'] == 'showplacementandsavesections');
    }

    /// calculate a "minimal" (tree-)list of sections
    private static function calculatePlacementTreeFromSelection(array $selections) {
        $result = array();
tx_newspaper::devlog('selections', $selections);
        //\todo: re-arrange sorting here to achieve different positioning in frontend
        foreach ($selections as $selection) {
            $ressort = array();
            foreach (explode('|', $selection) as $level=> $section_id) {
                $ressort[] = $section_id;
                if(!isset($result[$level]) || !in_array($ressort, $result[$level])) {
                    $result[$level][] = $ressort;
#                if(!isset($result[$level]) || !in_array($section_id, $result[$level])) {
#                    $result[$level][] = $section_id;
                }
            }
        }
tx_newspaper::devlog('tree', $result);
        return $result;
    }

    /// get article and offset lists for a set of sections
    private function fillPlacementWithData($tree, $articleId) {
        for ($i = 0; $i < count($tree); ++$i) {
            for ($j = 0; $j < count($tree[$i]); ++$j) {
                $k = count($tree[$i][$j])-1;
                $tree[$i][$j][$k] = $this->fillPlacementElementWithData($tree[$i][$j][$k], $articleId);
            }
        }

        return $tree;
    }

    /// get data (for title display) for each section
    private function fillPlacementElementWithData($element, $articleId) {

        $section = new tx_newspaper_Section($element);
        $article = new tx_newspaper_Article($articleId);

        return array(
            'section' => $section,
            'rendered_section' => $this->renderSectionObject(
                        $section,
                        array('placed_article' => $article, 'article_placed_already' => self::isArticleInSectionList($section, $article))
                    )
        );
    }

    private static function isArticleInSectionList(tx_newspaper_Section $section, tx_newspaper_Article $article) {
        foreach ($section->getArticleList()->getArticles(self::getArticleListHeight()) as $art) {
            if ($art->getUid() == $article->getUid()) return true;
        }
        return false;
    }

    private $input = array();
    /** @var tx_newspaper_Smarty */
    private $smarty = null;
}