<?php

/// A structure for a newspaper object representable as a combination of GET-parameters
class tx_newspaper_CachablePage {

    public function __construct(tx_newspaper_Page $page,
                                tx_newspaper_article $article = null,
                                $additional_parameters = array()) {
        $this->newspaper_page = $page;
        $this->newspaper_article = $article;
        $this->get_parameters = $additional_parameters;
    }

    public function __toString() {
        $string = $this->newspaper_page->__toString();
        if ($this->newspaper_article) $string .= $this->newspaper_article->__toString();
        return $string;
    }

    public function equals(tx_newspaper_CachablePage $other) {
        if ($this->getNewspaperPage()->getUid() != $other->getNewspaperPage()->getUid()) return false;
        if ($this->newspaper_article) {
            return ($other->newspaper_article &&
                    ($this->newspaper_article->getUid() == $other->newspaper_article->getUid()));
        }
        if ($other->newspaper_article) return false;
        return true;
    }

    public function getNewspaperPage() {
        return $this->newspaper_page;
    }

    public function getGETParameters() {

        $parameters = array(
            'id' => $this->getTypo3PageID(),
        );
        if ($this->newspaper_article) {
            $parameters[tx_newspaper::article_get_parameter] = $this->newspaper_article->getUid();
        }
        if ($this->get_parameters) {
            $parameters = array_merge($parameters, $this->get_parameters);
        }

        /// \todo page type
        $type = $this->newspaper_page->getPageType();
#        t3lib_div::devlog('getGETParameters',$type->getCondition());

        return $parameters;
    }

    public function getURL() {
        throw new tx_newspaper_NotYetImplementedException();
    }

    public function getTypo3PageID() {
        if (!$this->newspaper_page) {
            throw new tx_newspaper_IllegalUsageException(
                'tx_newspaper_CachablePage::getTypo3Page() called without a Newspaper page'
            );
        }
        return $this->newspaper_page->getTypo3PageID();
    }

    ////////////////////////////////////////////////////////////////////////////

    private $newspaper_page = null;
    private $newspaper_article = null;
    private $get_parameters = array();

}

/// A structure which traces objects which are affected by changes on another object and performs actions on them.
/** Because all newspaper rendering takes place independent of Typo3, a mechanism
 *  is needed to tell Typo3 to clear the page cache for all newspaper page which
 *  are affected by a change in an object.
 *
 *  A change that can affect other newspaper pages can be either to an article
 *  or to a web element ("extra"). [1] E.g. a change in an article title must be
 *  reflected on the section overview page which features the article. A change
 *  in an Extra displayed on an article page must be reflected in all articles
 *  which are rendered on that page. And many others.
 *
 *  A dependency tree is built either for an article or an extra. The article
 *  which triggered the generation of the dependency tree is from now on called
 *  "the affected article", and the extra "the affected extra".
 *
 *  In order to be as flexible as possible, the dependency tree does not restrict
 *  itself to clearing the Typo3 Page Cache. Instead, it allows actions to be
 *  registered which are executed for specific objects which are changed by a
 *  change in the affected object. That makes the control of more advanced
 *  caching mechanisms possible.
 *
 *  Objects which are changed by a change in the affected article:
 *  -# article pages displaying the article
 *  -# section pages displaying the article
 *    -# contain a tx_newspaper_Extra_Sectionlist which displays an article list
 *       which has a section_id pointing to a non-hidden, non-deleted section
 *  -# article pages displaying articles related to the article
 *  -# dossier pages where the dossier contains the article
 *  -# any pages displaying article lists other than section lists which contain
 *     the article
 *
 *  Objects which are changed by a change in the affected extra:
 *  -# If the extra is on a section page, this page and all pages which inherit
 *     from it in the page hierarchy.
 *  -# If the extra is on an article page, this page and all articles which are
 *     rendered on this page. Also, the same for all article pages which inherit
 *     from the page.
 *
 *  [1] In fact such a change can also happen on a PageZone, a Page or a Section,
 *    but we are not concerned with those here.
 */
class tx_newspaper_DependencyTree {

    /// Length up to which an article is searched for on article lists displayed on pages.
    const article_list_length = 10;

    /// Maximum number of articles which are retroactively re-rendered when the placement of an article page is changed.
    const limit_for_articles_on_placement_change = 50;

    /// An action marked with this flag is executed on dependent articles.
    const ACT_ON_ARTICLES = 1;
    /// An action marked with this flag is executed on the section page(s) the affected article appears upon.
    const ACT_ON_SECTION_PAGES = 2;
    /// An action marked with this flag is executed on articles related to the affected article.
    const ACT_ON_RELATED_ARTICLES = 4;
    /// An action marked with this flag is executed on dossier pages the affected article appears upon.
    const ACT_ON_DOSSIER_PAGES = 8;
    /// An action marked with this flag is executed on pages where the affected article appears in an article list.
    const ACT_ON_ARTICLE_LIST_PAGES = 16;

    public static function useDependencyTree() {
        $ts_config = tx_newspaper::getTSConfig();
        return (boolean)$ts_config['newspaper.']['use_dependency_tree'];
    }

    /// Generates the tree of pages that change when a tx_newspaper_Article changes.
    /** @param $article The article which is changed. \p $article is from now on
     *    called "the affected article". 
     */
    static public function generateFromArticle(tx_newspaper_Article $article, array $removed_tags = array()) {
        tx_newspaper::startExecutionTimer();
        $tree = new tx_newspaper_DependencyTree();
        $tree->setArticle($article);
        if (!empty($removed_tags)) {
            $tree->setDeletedContentTags($removed_tags);
        }
        tx_newspaper::logExecutionTime('generateFromArticle()');

        return $tree;
    }

    static public function generateFromArticlelist(tx_newspaper_Articlelist $list) {
        tx_newspaper::startExecutionTimer();
        $tree = new tx_newspaper_DependencyTree();
        if ($list->isSectionList()) {
            $tree->setList($list);
        }
        tx_newspaper::logExecutionTime('generateFromArticlelist()');

        return $tree;
    }

    /// Generates the tree of pages that change when a tx_newspaper_Extra changes.
    /** @param $extra The web element which is changed. \p $extra is from now on
     *   called "the affected extra".
     */
    static public function generateFromExtra(tx_newspaper_Extra $extra) {
        tx_newspaper::startExecutionTimer();
        $pagezone = $extra->getPageZone();
        $tree = new tx_newspaper_DependencyTree();
        if ($pagezone instanceof tx_newspaper_Article) {
            /// \todo or maybe not: if in article(s): generateFromArticle() for all articles.
            $tree->markAsCleared();
        } else if ($pagezone instanceof tx_newspaper_PageZone_Page) {
            $tree->setExtra($extra);
        } else {
            $tree->markAsCleared();
            # throw new tx_newspaper_InconsistencyException('Page zone is neither article nor page: ' . get_class($pagezone));
        }
        tx_newspaper::logExecutionTime('generateFromExtra()');

        return $tree;
    }

    static public function generateFromPagezone(tx_newspaper_Pagezone_Page $pagezone) {
        $tree = new tx_newspaper_DependencyTree();

        if ($pagezone instanceof tx_newspaper_PageZone_Page) {
            $tree->addAllExtraPagesForPagezone($pagezone);
        } else {
            $tree->markAsCleared();
        }

        return $tree;
    }

    /// Registers an action that is executed for every page in the tree on demand.
    /** The actions are stored in an array whose entries are arrays of the form
     *  \code array(
     *     'function' => <the registered function>,
     *     'when' => <flag mask describing on which objects the function is applied>
     *   ) \endcode
     *  @param $action A function that can be called via call_user_func() (see
     *    http://php.net/manual/en/function.call-user-func.php) and takes a
     *    tx_newspaper_Page as argument.
     *  @param $when A combination of flags that describes for which types of
     *    affected objects the registered action is executed. See
     *    \c tx_newspaper_DependencyTree::ACT_ON_ARTICLES, \c tx_newspaper_DependencyTree::ACT_ON_SECTION_PAGES,
     *    \c tx_newspaper_DependencyTree::ACT_ON_RELATED_ARTICLES, \c tx_newspaper_DependencyTree::ACT_ON_DOSSIER_PAGES,
     *    \c tx_newspaper_DependencyTree::ACT_ON_ARTICLE_LIST_PAGES.
     *    Defaults to \c ACT_ON_ARTICLES|ACT_ON_SECTION_PAGES.
     */
    static public function registerAction($action,
                                          $when = 3,
                                          $key = '') {
        if (is_callable($action)) {
            $new_action = array(
                'function' => $action,
                'when' => $when
            );
            if ($key) {
                self::$registered_actions[$key] =  $new_action;
            } else {
                self::$registered_actions[] =  $new_action;
            }
        }
    }

    /// Unregister all registered actions.
    static public function clearRegisteredActions() {
        self::$registered_actions = array();
    }
    
    /// Executes the registered actions on all pages in the tree for which they are registered.
    public function executeActionsOnPages($key = '') {
        if ($key) {
            if (isset(self::$registered_actions[$key])) {
                $this->executeActionOnPages(self::$registered_actions[$key]);
            }
        } else {
            foreach (self::$registered_actions as $action) {
                $this->executeActionOnPages($action);
            }
        }
    }

    /// Returns all article pages on which the affected article is shown.
    public function getArticlePages() {
        if (!$this->article_pages_filled) {
            $this->addArticlePages($this->article);
        }
        return $this->article_pages;
    }

    /// Returns all section pages on which the affected article is shown.
    public function getSectionPages() {
        if (!$this->section_pages_filled) {
            $this->addSectionPages($this->article->getSections());
        }
        return $this->section_pages;
    }

    /// Returns all article pages on which articles related to the affected article are shown.
    public function getRelatedArticlePages() {
        if (!$this->related_article_pages_filled) {
            $this->addRelatedArticles($this->article);
        }
        return $this->related_article_pages;
    }

    /// Returns all pages which feature an article list displaying the affected article.
    public function getArticlelistPages() {
        if (!$this->articlelist_pages_filled) {
            $this->addArticleListPages(getAffectedArticleLists($this->article));
        }
        return $this->articlelist_pages;
    }

    /// Returns all dossier pages which display a dossier containing the affected article.
    public function getDossierPages() {
        if (!$this->dossier_pages_filled) {
            $this->addDossierPages($this->article);
        }
        return $this->dossier_pages;
    }

    /// Returns all affected pages up to a specified depth.
    /** Kept only for backwards compatibility.
     */
    public function getPages($depth = 0) {

        if ($depth == 0) $depth = 4;

        $pages = array();
        if ($depth >= 1) {
            $pages = array_merge($pages, $this->getArticlePages());
        }
        if ($depth >= 2) {
            $pages = array_merge($pages, $this->getSectionPages());
        }
        if ($depth >= 3) {
            $pages = array_merge($pages, $this->getRelatedArticlePages());
        }
        if ($depth >= 4) {
            $pages = array_merge($pages, $this->getArticlelistPages());
        }

        return array_unique($pages);
    }

    /// Returns number of articles which are retroactively re-rendered when the placement of an article page is changed.
    public static function limitForArticlesOnPlacementChange() {
        $tsconfig = tx_newspaper::getTSConfig();
        $limit = intval($tsconfig['newspaper.']['limit_for_articles_on_placement_change']);
        if ($limit) return $limit;
        return self::limit_for_articles_on_placement_change;
    }

    ////////////////////////////////////////////////////////////////////////////

    /// Ensure that a dependency tree is not created other than by the generator functions.
    private function __construct() { }

    private function setArticle(tx_newspaper_Article $article) {
        $this->article = $article;
    }

    private function setDeletedContentTags(array $tags) {
        $this->removed_dossier_tags = $tags;
    }

    private function setList(tx_newspaper_Articlelist $list) {
        $this->addSectionPages(array($list->getSection()));
        $this->markAsCleared();
    }

    private function setExtra(tx_newspaper_Extra $extra) {
        $this->extra = $extra;

        $pagezone = $this->extra->getPageZone();
        $this->addAllExtraPagesForPagezone($pagezone);
    }

    private function executeActionOnPages(array $action) {
        $function = $action['function'];
        $when = $action['when'];
        $pages = array();

        if ($when & self::ACT_ON_ARTICLES) $pages = array_merge($pages, $this->getArticlePages());
        if ($when & self::ACT_ON_SECTION_PAGES) $pages = array_merge($pages, $this->getSectionPages());
        if ($when & self::ACT_ON_RELATED_ARTICLES) $pages = array_merge($pages, $this->getRelatedArticlePages());
        if ($when & self::ACT_ON_DOSSIER_PAGES) $pages = array_merge($pages, $this->getDossierPages());
        if ($when & self::ACT_ON_ARTICLE_LIST_PAGES) $pages = array_merge($pages, $this->getArticlelistPages());

        call_user_func($function, $pages);
    }

    /// Adds article pages of all sections \p $article is in
    private function addArticlePages(tx_newspaper_Article $article = null) {
        tx_newspaper::startExecutionTimer();
        if ($article) {
            $sections = $article->getSections();
            $pages = getAllArticlePages($sections);
            $this->article_pages = array_merge($this->article_pages, makeCachablePages($pages, $article));
            $this->article_pages = array_unique($this->article_pages);
        }
        $this->article_pages_filled = true;
        tx_newspaper::logExecutionTime('addArticlePages()');
    }

    private function addSectionPages(array $sections) {
        tx_newspaper::startExecutionTimer();
        foreach ($sections as $section) {
            $pages = getAllPagesWithSectionListExtra($section);
            $this->section_pages = array_merge($this->section_pages, makeCachablePages($pages));
        }
        $this->section_pages = array_unique($this->section_pages);
        $this->section_pages_filled = true;
        tx_newspaper::logExecutionTime('addSectionPages()');
    }

    private function addRelatedArticles(tx_newspaper_Article $article) {
        tx_newspaper::startExecutionTimer();
        $related = $article->getRelatedArticles();
        foreach ($related as $related_article) {
            $sections = $related_article->getSections();
            $pages = getAllArticlePages($sections);
            $this->related_article_pages = array_merge($this->related_article_pages, makeCachablePages($pages, $article));
        }
        $this->related_article_pages = array_unique($this->related_article_pages);
        $this->related_article_pages_filled = true;
        tx_newspaper::logExecutionTime('addRelatedArticles()');
    }

    private function addDossierPages(tx_newspaper_Article $article) {
        tx_newspaper::startExecutionTimer();

        $tags = array_merge(
            $article->getTags(tx_newspaper_Tag::getControlTagType()),
            $this->removed_dossier_tags
        );
        if (empty($tags)) return;

        $dossier_page = getDossierPage();
        if (!$dossier_page instanceof tx_newspaper_Page) return;

        foreach ($tags as $tag) {
            $page = new tx_newspaper_CachablePage(
                $dossier_page, null, array(tx_newspaper::getDossierGETParameter() => $tag->getUid())
            );
            $this->dossier_pages[] = $page;
        }

        $this->dossier_pages_filled = true;

        tx_newspaper::logExecutionTime('addDossierPages()');
    }

    /// Adds all pages which display an article list in the supplied array
    private function addArticleListPages(array $article_lists) {
        tx_newspaper::startExecutionTimer();
        $pages = getAllArticleListPages($article_lists);
        $this->articlelist_pages = array_merge($this->articlelist_pages, makeCachablePages($pages));
        $this->articlelist_pages = array_unique($this->articlelist_pages);
        $this->articlelist_pages_filled = true;
        tx_newspaper::logExecutionTime('addArticleListPages()');
    }

    private function addAllExtraPagesForPagezone(tx_newspaper_Pagezone_Page $pagezone) {

        foreach ($pagezone->getInheritanceHierarchyDown() as $current_pagezone) {
            $this->addCachablePagesForPage(getPage($current_pagezone));
        }

        $this->markAsCleared();
    }

    private function markAsCleared() {
        $this->article_pages_filled = true;
        $this->section_pages_filled = true;
        $this->related_article_pages = true;
        $this->dossier_pages_filled = true;
        $this->articlelist_pages_filled = true;
    }

    private function addCachablePagesForPage(tx_newspaper_Page $page) {
        if ($page->getPageType() == tx_newspaper_PageType::getArticlePageType()) {
            $this->article_pages = array_merge($this->article_pages, allArticlePagesForPage($page));
        } else if ($page->getPageType() == tx_newspaper_PageType::getSectionPageType()) {
            $this->section_pages[] = new tx_newspaper_CachablePage($page);
        } else if ($page->getTypo3PageID() == tx_newspaper::getDossierPageID()) {
            /// \todo add dossier pages
            tx_newspaper::devlog('add dossier page', $page->getUid());
        }
    }

    ////////////////////////////////////////////////////////////////////////////

    private $article = null;
    private $extra = null;

    private $article_pages = array();   ///< Article pages of the sections containing the article
    private $article_pages_filled = false;
    private $section_pages = array();   ///< Section overview pages containing the article
    private $section_pages_filled = false;
    private $related_article_pages = array();   ///< Pages showing articles related to the article
    private $related_article_pages_filled = false;
    private $dossier_pages = array();   ///< Pages showing article as part of a dossier
    private $dossier_pages_filled = false;
    private $removed_dossier_tags = array();    ///< Dossier tags which have been deleted
    private $articlelist_pages = array();   ///< Pages displaying article lists containing the article
    private $articlelist_pages_filled = false;

    private static $registered_actions = array();

}

function makeCachablePages(array $pages, tx_newspaper_Article $article = null) {
    $cachable_pages = array();
    foreach($pages as $page) {
        if ($page instanceof tx_newspaper_Page) {
            $cachable_pages[] = new tx_newspaper_CachablePage($page, $article);
        }
    }
    return $cachable_pages;
}

function getAllArticlePages(array $sections) {
	$article_pages = array();
    foreach ($sections as $section) {
        $article_page = getArticlePage($section);
        if ($article_page instanceof tx_newspaper_Page) $article_pages[] = $article_page;
    }
    return $article_pages;
}

/// Returns the article page associated with \p $section
function getArticlePage(tx_newspaper_Section $section) {
    $articlepagetype = tx_newspaper_PageType::getArticlePageType();
    return $section->getSubPage($articlepagetype);
}

function getAllPagesWithSectionListExtra(tx_newspaper_Section $section) {

#    tx_newspaper::startExecutionTimer();

    static $section_list_pages = array();

    if (!isset($section_list_pages[$section->__toString()])) {
        $all_pages = $section->getActivePages();
        $pages = array();

        foreach ($all_pages as $page) {
            if (doesContainSectionListExtra($page)) $pages[] = $page;
        }

        $section_list_pages[$section->__toString()] = $pages;
    }

#    tx_newspaper::logExecutionTime('getAllPagesWithSectionListExtra()');

    return $section_list_pages[$section->__toString()];
}

function doesContainSectionListExtra(tx_newspaper_Page $page) {
    $pagezones = $page->getPageZones();
    foreach ($pagezones as $pagezone) {
        $extras = $pagezone->getExtrasOf('tx_newspaper_extra_SectionList');
        foreach ($extras as $extra) {
            if ($extra instanceof tx_newspaper_extra_SectionList) return true;
        }
    }
    return false;
}

function getDossierPage() {
    $typo3page = tx_newspaper::getDossierPageID();
    $dossier_section = tx_newspaper_Section::getSectionForTypo3Page($typo3page);
    if (!$dossier_section instanceof tx_newspaper_Section) {
        throw new tx_newspaper_IllegalUsageException('Typo3 page ' . $typo3page . ' is not associated with a newspaper section');
    }

    $row = tx_newspaper::selectOneRow(
        'uid', 'tx_newspaper_page',
        'section = ' . $dossier_section->getUid() .
        ' AND pagetype_id = ' . '1'
    );
    $uid = intval($row['uid']);

    return new tx_newspaper_Page($uid);
}

function getAllArticleListPages(array $article_lists) {
	$pages = array();
	foreach ($article_lists as $list) {
		$pages = array_merge($pages, getArticleListPages($list));
	}
	return array_unique($pages);
}

function getArticleListPages(tx_newspaper_ArticleList $article_list) {

	$extras = getAllExtras($article_list);

	$pagezones = array();
	foreach ($extras as $extra) {
		$pagezones = array_merge($pagezones, getAllPageZones($extra));
	}
	$pagezones = array_unique($pagezones);

	$pages = array();
	foreach ($pagezones as $pagezone) {
		$pages[] = getPage($pagezone);
	}
	$pages = array_unique($pages);

	return $pages;
}

/// get all extras that reference $article_list
function getAllExtras(tx_newspaper_ArticleList $article_list) {
	return getAllExtrasOfType('tx_newspaper_extra_articlelist', $article_list);
}

function getAllExtrasOfType($extra_type, tx_newspaper_ArticleList $article_list) {

	$article_list_extra_uids = tx_newspaper::selectRows(
		'uid', $extra_type,
		'articlelist = ' . $article_list->getUid()
	);

	$extras = array();

	foreach ($article_list_extra_uids as $record) {
		$extras = array_merge($extras, getAbstractExtras($record['uid'], $extra_type));
	}

	return $extras;
}

function getAbstractExtras($concrete_extra_uid, $extra_table) {

	$extra_uids = tx_newspaper::selectRows(
			'uid', 'tx_newspaper_extra',
			'extra_uid = ' . $concrete_extra_uid . ' AND extra_table = \'' . $extra_table . '\''
	);

	$extras = array();

	foreach ($extra_uids as $uid) {
		$extras[] = tx_newspaper_Extra_Factory::getInstance()->create($uid['uid']);
	}

	return $extras;
}

/// get all page zones that contain \p $extra
function getAllPageZones(tx_newspaper_Extra $extra) {

	$pagezone_uids = tx_newspaper::selectRows(
		'uid_local', 'tx_newspaper_pagezone_page_extras_mm',
		'uid_foreign = ' . $extra->getExtraUid()
	);

	$pagezones = array();
	foreach ($pagezone_uids as $uid) {
		$pagezones[] = new tx_newspaper_Pagezone_Page($uid['uid_local']);
	}

	return $pagezones;
}

/// get all pages that contain $pagezone
function getPage(tx_newspaper_Pagezone $pagezone) {
	return new tx_newspaper_Page(intval($pagezone->getAttribute('page_id')));
}

/// Returns array of all article lists \p $article belongs to
function getAffectedArticleLists(tx_newspaper_Article $article) {

    $all_article_lists = getAllArticleLists();
    $article_lists = array();

    foreach ($all_article_lists as $list) {
        if ($list->doesContainArticle($article, tx_newspaper_DependencyTree::article_list_length)) {
            $article_lists[] = $list;
        }
    }
    return $article_lists;
}

function getAllArticleLists() {

    static $all_article_lists = array();

    if (empty($all_article_lists)) {
        $article_list_uids = tx_newspaper::selectRows('uid', 'tx_newspaper_articlelist');

        foreach ($article_list_uids as $record) {
            $all_article_lists[] = tx_newspaper_ArticleList_Factory::getInstance()->create($record['uid']);
        }
    }

    return $all_article_lists;

}

/// Returns the page and all articles that are displayed on it.
/** Condition: \p $page is an article page. This is not checked. */
function allArticlePagesForPage(tx_newspaper_Page $page) {

    $section = $page->getParentSection();
    $articles = $section->getArticles(tx_newspaper_DependencyTree::limitForArticlesOnPlacementChange());

    $pages = array();
    foreach ($articles as $article) {
        $pages[] = new tx_newspaper_CachablePage($page, $article);
    }
    return $pages;
}

function debugPage(tx_newspaper_CachablePage $page) {
    $np_page = $page->getNewspaperPage();
    tx_newspaper::devlog($np_page->getUid(),
    					 $np_page->getParentSection()->getAttribute('section_name') . $np_page->getPageType()->getAttribute('type_name'));
}

// tx_newspaper_DependencyTree::registerAction('debugPage');

?>