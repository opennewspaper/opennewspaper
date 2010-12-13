<?php

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
    	tx_newspaper::devlog('generate from article', $article->getUid());
        $tree = new tx_newspaper_DependencyTree;

        $tree->addArticlePages($article->getSections());
        $tree->addArticleListPages(getAffectedArticleLists($article));
        
        return $tree;
    }
    
    /// Generates the tree of pages that change when a tx_newspaper_Extra changes.
    /** \param $extra The web element which is changed.
     */ 
    static public function generateFromExtra(tx_newspaper_Extra $extra) {
        // if in article(s): generateFromArticle() for all articles
        // if on page zone directly: all pages which contain all page zones
    }
    
    /// Registers an action that is executed for every page in the tree on demand.
    /** \param $action A function that can be called via call_user_func() (see 
     *    http://php.net/manual/en/function.call-user-func.php) and takes a
     *    tx_newspaper_Page as argument.
     */ 
    static public function registerAction($action) {
        if (isCallback($action)) self::addAction($action);
    }
    
    /// Executes the registered actions on all pages in the tree up to a specified depth.
    public function executeActionsOnPages($depth = 0) {
        foreach ($this->getPages($depth) as $page) {
            foreach (self::$registered_actions as $action) {
                call_user_func($action, $page);
            }
        }
    }
    
    /// Returns all affected pages up to a specified depth.
    public function getPages($depth = 0) {
        if ($depth == 0) $depth = sizeof($this->pages_on_level);
        $pages = array();
        for ($level = 1; $level <= $depth; $level++) {
            $pages += $this->pages_on_level[$level];
        }
        return $pages;
    }
    
    ////////////////////////////////////////////////////////////////////////////
    
    /// Adds article pages of all sections $article is in
    private function addArticlePages(array $sections) {
        if (!is_array($this->pages_on_level[1])) $this->pages_on_level[1] = array();
        foreach ($sections as $section) {
            $article_page = getArticlePage($section);
            $this->pages_on_level[1][] = $article_page;
        }
    }
    
    /// Adds all pages which display an article list in the supplied array
    private function addArticleListPages(array $article_lists) {
        
    }
    
    /** \code 
     *  array(
     *    1 => array ( pages on first level ),
     *    2 => array ( ... ),
     *    ...
     *  )
     * \endcode
     */
    private $pages_on_level = array();
    
    private static function addAction($action) {
        self::$registered_actions[] = $action;
    }
    
    private static $registered_actions = array();
    
}

/// Returns the article page associated with \p $section
function getArticlePage(tx_newspaper_Section $section) {
    $articlepagetype = tx_newspaper_PageType::getArticlePageType();
    return $section->getSubPage($articlepagetype);
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
    
    return array();
    
}

function isCallback($action) {
    if (is_string($action)) {
        return (function_exists($action));
    }
        
    if (is_array($action) && sizeof($action) > 1) {
        if (isClassOrObject($action[0])) {
            return (method_exists($action[0], $action[1]));
        }
    }
    
    return false;
}

function isClassOrObject($thing) {
    return ((is_string($thing) && class_exists($thing)) || is_object($thing));
}

function debugPage(tx_newspaper_Page $page) {
    tx_newspaper::devlog($page->getUid(), 
    					 $page->getParentSection()->getAttribute('section_name') . $page->getPageType()->getAttribute('type_name'));
}

tx_newspaper_DependencyTree::registerAction('debugPage');
?>