<?php

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

/** Levels of dependency for articles:
 *  # article pages displaying the article
 *  #- as URL or GET parameters
 *  # section pages displaying the article
 *  #- contain a sectionlist Extra which displays an article list which has a
 *     section_id pointing to a non-hidden, non-deleted section
 *  # article pages displaying articles related to the article
 *  # any pages displaying article lists other than section lists which contain
 *    the article
 */
class tx_newspaper_DependencyTree {

    const article_list_length = 10;

    const ACT_ON_ARTICLES = 1;
    const ACT_ON_SECTION_PAGES = 2;
    const ACT_ON_RELATED_ARTICLES = 4;
    const ACT_ON_DOSSIER_PAGES = 8;
    const ACT_ON_ARTICLE_LIST_PAGES = 16;

    /// Generates the tree of pages that change when a tx_newspaper_Article changes.
    /** \param $article The article which is changed.
     */
    static public function generateFromArticle(tx_newspaper_Article $article) {
        tx_newspaper::startExecutionTimer();
        $tree = new tx_newspaper_DependencyTree($article);
        tx_newspaper::logExecutionTime('generateFromArticle()');

        return $tree;
    }

    /// Generates the tree of pages that change when a tx_newspaper_Extra changes.
    /** \param $extra The web element which is changed.
     */
    static public function generateFromExtra(tx_newspaper_Extra $extra) {
        // if in article(s): generateFromArticle() for all articles
        // if on page zone directly: all pages which contain all page zones
        throw new tx_newspaper_NotYetImplementedException();
    }

    /// Registers an action that is executed for every page in the tree on demand.
    /** \param $action A function that can be called via call_user_func() (see
     *    http://php.net/manual/en/function.call-user-func.php) and takes a
     *    tx_newspaper_Page as argument.
     */
    static public function registerAction($action,
                                          $when = 3) {
        if (is_callable($action)) {
            self::$registered_actions[] = array(
                'function' => $action,
                'when' => $when
            );
        }
    }

    static public function clearRegisteredActions() {
        self::$registered_actions = array();
    }
    
    /// Executes the registered actions on all pages in the tree up to a specified depth.
    public function executeActionsOnPages($depth = 0) {

        tx_newspaper::startExecutionTimer();

        foreach (self::$registered_actions as $action) {
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

        tx_newspaper::logExecutionTime('executeActionsOnPages()');
    }

    public function getArticlePages() {
        if (!$this->article_pages_filled) {
            $this->addArticlePages($this->article);
        }
        return $this->article_pages;
    }

    public function getSectionPages() {
        if (!$this->section_pages_filled) {
            $this->addSectionPages($this->article->getSections());
        }
        return $this->section_pages;
    }

    public function getRelatedArticlePages() {
        if (!$this->related_article_pages_filled) {
            $this->addRelatedArticles($this->article);
        }
        return $this->related_article_pages;
    }

    public function getArticlelistPages() {
        if (!$this->articlelist_pages_filled) {
            $this->addArticleListPages(getAffectedArticleLists($this->article));
        }
        return $this->articlelist_pages;
    }

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

    ////////////////////////////////////////////////////////////////////////////

    /// Adds article pages of all sections $article is in
    /** \todo Only clear cache for the affected article, not the entire page
     */
    private function addArticlePages(tx_newspaper_Article $article) {
        tx_newspaper::startExecutionTimer();
        $sections = $article->getSections();
        $pages = getAllArticlePages($sections);
        $this->article_pages = array_merge($this->article_pages, makeCachablePages($pages, $article));
        $this->article_pages = array_unique($this->article_pages);
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

        $tags = $article->getTags(tx_newspaper_Tag::getControlTagType());
        if (empty($tags)) return;

        $temp = array();
        try {
            $dossier_page = getDossierPage();
        } catch (Exception $e) {
            tx_newspaper::devlog('addDossierPages Error');
        }
/*        foreach ($tags as $tag) {
            $page = new tx_newspaper_CachablePage(
                $dossier_page, null, array(tx_newspaper::getDossierGETParameter() => $tag->getUid())
            );
            $this->dossier_pages[] = $page;
            $temp[] = $page->getGETParameters();
        }
 
 */
        tx_newspaper::devlog('addDossierPages 2', $dossier_page->getUid());

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

    /// Ensure that a dependency tree is not created other than by the generator functions.
    private function __construct(tx_newspaper_Article $article) {
        $this->article = $article;
    }

    private $article = null;

    private $article_pages = array();   ///< Article pages of the sections containing the article
    private $article_pages_filled = false;
    private $section_pages = array();   ///< Section overview pages containing the article
    private $section_pages_filled = false;
    private $related_article_pages = array();   ///< Pages showing articles related to the article
    private $related_article_pages_filled = false;
    private $dossier_pages = array();   ///< Pages showing article as part of a dossier
    private $dossier_pages_filled = false;
    private $articlelist_pages = array();   ///< Pages displaying article lists containing the article
    private $articlelist_pages_filled = false;

    private static $registered_actions = array();

}

function makeCachablePages(array $pages, tx_newspaper_Article $article = null) {
    $cachable_pages = array();
    foreach($pages as $page) {
        $cachable_pages[] = new tx_newspaper_CachablePage($page, $article);
    }
    return $cachable_pages;
}

function getAllArticlePages(array $sections) {
	$article_pages = array();
    foreach ($sections as $section) {
        $article_page = getArticlePage($section);
        $article_pages[] = $article_page;
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
    tx_newspaper::devlog('getDossierPage section', $dossier_section->getUid());

    return new tx_newspaper_Page($dossier_section);
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

function debugPage(tx_newspaper_CachablePage $page) {
    $np_page = $page->getNewspaperPage();
    tx_newspaper::devlog($np_page->getUid(),
    					 $np_page->getParentSection()->getAttribute('section_name') . $np_page->getPageType()->getAttribute('type_name'));
}

// tx_newspaper_DependencyTree::registerAction('debugPage');

?>