<?php

require_once(PATH_typo3conf . 'ext/newspaper/tests/class.tx_newspaper_database_testcase.php');
/// testsuite for class tx_newspaper_pagezone
class test_DependencyTree_testcase extends tx_newspaper_database_testcase {

    public function setUp() {
        parent::setUp();
//        self::$skip_setup_because_no_data_have_changed = true;
    }

    // to do: organize tests so that tests of dependent functions are executed after those they depend on

    // Tests related to getArticlePages()

    public function test_getArticlePage() {
        $section_uid = $this->fixture->getParentSectionUid();
        $section = new tx_newspaper_Section($section_uid);
        $article_page = getArticlePage($section);

        $this->checkIsValidNewspaperPage($article_page);
    }

    public function test_getArticlePages() {

        $tree = $this->createTree();

        $pages = $tree->getArticlePages();

        $this->checkIsPageArray($pages);
    }

    // Tests related to getSectionPages()

    public function test_getSectionPages() {

        $tree = $this->createTree();

        $pages = $tree->getSectionPages();

        $this->checkIsPageArray($pages);
    }

    // Tests related to getRelatedArticlePages()

    public function test_getRelatedArticlePages() {
        $tree = $this->createTree();

        $pages = $tree->getRelatedArticlePages();

        $this->checkIsPageArray($pages);

        // check that the related article appears in different sections than the
        // original, as defined in the fixture
        $article_pages = $tree->getArticlePages();
        foreach ($pages as $related_page) {
            foreach ($article_pages as $article_page) {
                $this->assertFalse($related_page->equals($article_page));
            }
        }
    }

    // Tests related to getArticlelistPages()

    public function test_getAllArticleLists() {
        $article_lists = getAllArticleLists();
        $this->checkIsfilledArray($article_lists);
    }

    public function test_getAffectedArticleLists() {
        $uid = $this->fixture->getArticleUid();
        $article = new tx_newspaper_Article($uid);

        $affected_article_lists = getAffectedArticleLists($article);

        $this->checkIsfilledArray($affected_article_lists);

        foreach ($affected_article_lists as $list) {
            $this->assertTrue($list->doesContainArticle($article, tx_newspaper_DependencyTree::article_list_length));
        }

    }

    public function test_getAllExtras() {

        $article_list = $this->createArticleList();

        $extras = getAllExtras($article_list);

        $this->checkIsfilledArray($extras, 2);
        foreach ($extras as $extra) {
            $this->assertTrue(is_object($extra));
            $this->assertTrue($extra instanceof tx_newspaper_Extra_ArticleList);
            $this->assertTrue($extra->getAttribute('articlelist') == $this->fixture->getArticlelistUid());
        }
    }

    public function test_getAllPageZones() {

        $article_list = $this->createArticleList();
        $extras = getAllExtras($article_list);

        foreach ($extras as $extra) {

            $pagezones = getAllPageZones($extra);

            $this->checkIsfilledArray($pagezones);
            foreach ($pagezones as $pagezone) {
                $this->assertTrue(is_object($pagezone));
                $this->assertTrue($pagezone instanceof tx_newspaper_Pagezone);
                $this->assertGreaterThan(0, intval($pagezone->getAttribute('uid')));
            }
        }
    }

    public function test_getPage() {
        $article_list = $this->createArticleList();
        $extras = getAllExtras($article_list);

        foreach ($extras as $extra) {

            $pagezones = getAllPageZones($extra);

            foreach ($pagezones as $pagezone) {
                $page = getPage($pagezone);
                $this->checkIsValidNewspaperPage($page);
            }
        }

    }

    public function test_getArticleListPages() {
        $al_uid = $this->fixture->getArticlelistUid();
        $article_list = tx_newspaper_ArticleList_Factory::getInstance()->create($al_uid);

        $pages = getArticleListPages($article_list);

        $this->checkIsPageArray($pages);

        $tree = $this->createTree();

        $pages = $tree->getArticleListPages($article_list);

        $this->checkIsPageArray($pages);
    }

    // Tests related to executeActionsOnPages()

    private $called_pages = array();

    public function pageActionIsExecuted(array $pages) {
    	$this->called_pages = array_merge($this->called_pages, $pages);
    }

    public function test_executeActionsOnPages() {
        tx_newspaper_DependencyTree::registerAction(array($this, 'pageActionIsExecuted'));

        $tree = $this->createTree();
        $tree->executeActionsOnPages();


        $this->checkIsPageArray($this->called_pages);

        foreach ($this->fixture->getPages() as $checked_page) {
            if (false)	// it is not really true that ALL pages are called; disabling this test until i implement a better one
            $this->assertTrue(
                in_array($checked_page, $this->called_pages),
                'Action not called for page ' . $checked_page
            );
        }
    }

    public function test_ActionsForDifferentPageTypes_Article() {
        tx_newspaper_DependencyTree::registerAction(
            array($this, 'pageActionIsExecuted'),
            tx_newspaper_DependencyTree::ACT_ON_ARTICLES
        );

        $tree = $this->createTree();

        $tree->executeActionsOnPages();

        $this->checkIsPageArray($this->called_pages);

        foreach ($this->called_pages as $page) {
            $this->checkIsArticlePageForSection($page);
        }
    }

    public function test_ActionsForDifferentPageTypes_Section() {
        tx_newspaper_DependencyTree::registerAction(
            array($this, 'pageActionIsExecuted'),
            tx_newspaper_DependencyTree::ACT_ON_SECTION_PAGES
        );

        $tree = $this->createTree();

        $tree->executeActionsOnPages();

        $this->checkIsPageArray($this->called_pages);

        foreach ($this->called_pages as $page) {
            $this->checkIsSectionPage($page);
        }
    }

    // Tests related to getPages()

    public function test_getPages() {

        $tree = $this->createTree();

        $pages = $tree->getPages();

        $this->assertTrue(sizeof($pages) > 0);

        $this->checkIsArticlePageForSection($pages[0]);
    }




    ////////////////////////////////////////////////////////////////////////////

    private function checkIsfilledArray($thing, $size = 1) {
        $this->assertTrue(is_array($thing), 'Not an array');
        $this->assertGreaterThanOrEqual($size, sizeof($thing), 'Array size < ' . $size);
    }

    private function checkIsValidNewspaperPage($page) {
        $this->assertTrue(is_object($page), '$page is not an object: ' . print_r($page, 1));
        $this->assertTrue($page instanceof tx_newspaper_Page);
        $this->assertGreaterThan(0, intval($page->getAttribute('uid')));
    }

    private function checkIsValidCachablePage($page) {
        $this->assertTrue(is_object($page), '$page is not an object: ' . print_r($page, 1));
        $this->assertTrue($page instanceof tx_newspaper_CachablePage);
        $this->checkIsValidNewspaperPage($page->getNewspaperPage());
    }

    private function checkIsPageArray(array $pages) {
        $this->checkIsFilledArray($pages);
        foreach ($pages as $page) {
            $this->assertTrue(is_object($page), '$page is not an object: ' . print_r($page, 1));
            if ($page instanceof tx_newspaper_Page) {
                $this->checkIsValidNewspaperPage($page);
            } else $this->checkIsValidCachablePage($page);
        }
    }

    /// assert that affected page is article page of affected section
    private function checkIsArticlePageForSection(tx_newspaper_CachablePage $page) {
        $this->checkSectionMatches($page);

        $pagetype = $page->getNewspaperPage()->getPageType();
        $this->assertTrue((bool)$pagetype->getAttribute('is_article_page'));
    }

    private function checkIsSectionPage(tx_newspaper_CachablePage $page) {
        $this->checkSectionMatches($page);

        $pagetype = $page->getNewspaperPage()->getPageType();
        $this->assertFalse((bool)$pagetype->getAttribute('is_article_page'));
        $this->assertEquals('', $pagetype->getAttribute('get_var'), 'Pagetype is Section Page');
        $this->assertEquals('', $pagetype->getAttribute('get_value'), 'Pagetype is Section Page');
    }

    private function checkSectionMatches(tx_newspaper_CachablePage $page) {
        $article = $this->createArticle();
        $section = $page->getNewspaperPage()->getParentSection();
        $this->assertEquals($section, $article->getPrimarySection());
    }

    private function createArticle() {
        $uid = $this->fixture->getArticleUid();
        $article = new tx_newspaper_Article($uid);

        return $article;
    }

    private function createArticleList() {

        $al_uid = $this->fixture->getArticlelistUid();
        $article_list = tx_newspaper_ArticleList_Factory::getInstance()->create($al_uid);

        return $article_list;

    }

    private function createTree() {
        $article = $this->createArticle();
        $tree = tx_newspaper_DependencyTree::generateFromArticle($article);

        return $tree;
    }

    private $is_fixture_set_up = false;
}

function pageActionIsExecuted(tx_newspaper_Page $page) {
    echo $page->getUid() . "<br >";
}

function debugStuff($stuff) {
    echo '<p>'.
        str_replace(' ', '&nbsp;',
            str_replace("\n", "<br/>\n", print_r($stuff, 1))
        ) .
        '</p>';
}

?>