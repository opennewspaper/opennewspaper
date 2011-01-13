<?php

class tx_newspaper_CachablePage {
    
    public function __construct(tx_newspaper_Page $page, tx_newspaper_article $article = null) {
        $this->newspaper_page = $page;
        $this->newspaper_article = $article;
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
    
    public function getURL() {
        if (class_exists('tx_newspaper_taz_URLGenerator')) {
            if (!$this->article) {
                tx_newspaper::devlog('getURL()', array($this->newspaper_article, $this->newspaper_page));
            } else {
                $generator = new tx_newspaper_taz_URLGenerator($this->newspaper_article);
                return $generator->getCanonicalUrl();
            }
        }
        else throw new tx_newspaper_NotYetImplementedException();
    }
    
    public function getTypo3Page() {
        throw new tx_newspaper_NotYetImplementedException();
    }
    
    public function getGETParameters() {
        throw new tx_newspaper_NotYetImplementedException();
    }
    
    ////////////////////////////////////////////////////////////////////////////
    
    private $newspaper_page = null;
    private $newspaper_article = null;
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
    
    /// Generates the tree of pages that change when a tx_newspaper_Article changes.
    /** \param $article The article which is changed.
     */
    static public function generateFromArticle(tx_newspaper_Article $article) {
        tx_newspaper::startExecutionTimer();
        $tree = new tx_newspaper_DependencyTree;

        $tree->addArticlePages($article);
        $tree->addSectionPages($article->getSections());
        $tree->addRelatedArticles($article);
        $tree->addArticleListPages(getAffectedArticleLists($article));
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
    static public function registerAction($action) {
        if (is_callable($action)) {
#            die('callable:'. print_r($action,1));
            self::$registered_actions[] = $action;
        } else {
#!!            die('not callable:'. print_r($action,1));
        }
    }
    
    /// Executes the registered actions on all pages in the tree up to a specified depth.
    public function executeActionsOnPages($depth = 0) {
        tx_newspaper::startExecutionTimer();
        foreach ($this->getPages($depth) as $page) {
            foreach (self::$registered_actions as $action) {
                call_user_func($action, $page);
            }
        }
        tx_newspaper::logExecutionTime('executeActionsOnPages()');
    }
    
    public function getArticlePages() {
        return $this->article_pages;
    }
    
    public function getSectionPages() {
        return $this->section_pages;
    }
    
    public function getRelatedArticlePages() {
        return $this->related_article_pages;
    }
    
    public function getArticlelistPages() {
        return $this->articlelist_pages;
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
        
        return $pages;
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
        tx_newspaper::logExecutionTime('addArticlePages()');
    }
    
    private function addSectionPages(array $sections) {
        tx_newspaper::startExecutionTimer();
        foreach ($sections as $section) {
            $pages = getAllPagesWithSectionListExtra($section);
            $this->section_pages = array_merge($this->section_pages, makeCachablePages($pages));
        }
        $this->section_pages = array_unique($this->section_pages);
        tx_newspaper::logExecutionTime('addSectionPages()');
    }
    
    private function addRelatedArticles(tx_newspaper_Article $article) {
        $related = $article->getRelatedArticles();
        foreach ($related as $related_article) {
            $sections = $related_article->getSections();
            $pages = getAllArticlePages($sections);
            $this->related_article_pages = array_merge($this->related_article_pages, makeCachablePages($pages, $article));
            foreach ($sections as $section) {
                $pages = getAllPagesWithSectionListExtra($section);
                $this->related_article_pages = array_merge($this->related_article_pages, makeCachablePages($pages));
            }
        }
        $this->related_article_pages = array_unique($this->related_article_pages);
    }
    
    /// Adds all pages which display an article list in the supplied array
    private function addArticleListPages(array $article_lists) {
        $pages = getAllArticleListPages($article_lists);
        $this->articlelist_pages = array_merge($this->articlelist_pages, makeCachablePages($pages));
        $this->articlelist_pages = array_unique($this->articlelist_pages);
    }
    
    /// Ensure that a dependency tree is not created other than by the generator functions.
    private function __construct() { }
    
    private $article_pages = array();   ///< Article pages of the sections containing the article
    private $section_pages = array();   ///< Section overview pages containing the article
    private $related_article_pages = array();   ///< Pages showing articles related to the article
    private $articlelist_pages = array();   ///< Pages displaying article lists containing the article
        
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
    
    tx_newspaper::startExecutionTimer();
    
    static $section_list_pages = array();
    
    if (!isset($section_list_pages[$section->__toString()])) {
        $all_pages = $section->getActivePages();
        $pages = array();
        
        foreach ($all_pages as $page) {
            if (doesContainSectionListExtra($page)) $pages[] = $page;
        }
        
        $section_list_pages[$section->__toString()] = $pages;
    }
    
    tx_newspaper::logExecutionTime('getAllPagesWithSectionListExtra()');
    
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