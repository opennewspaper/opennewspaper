<?php

require_once(PATH_typo3conf . 'ext/newspaper/tests/class.tx_newspaper_database_testcase.php');
/// testsuite for class tx_newspaper_pagezone
class test_DependencyTree_testcase extends tx_newspaper_database_testcase {

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
#        $this->clearDatabase();
    }
    
    public function test_getArticlePage() {
        $section_uid = $this->fixture->getParentSectionUid();
        $section = new tx_newspaper_Section($section_uid);
        $article_page = getArticlePage($section);
        $this->assertFalse($article_page == null);
        
    }

    public function test_getPages() {
        
        $uid = $this->fixture->getArticleUid();
        $article = new tx_newspaper_Article($uid);
        
        $tree = tx_newspaper_DependencyTree::generateFromArticle($article);
        
        $pages = $tree->getPages();

        $this->assertTrue(sizeof($pages) > 0);
        
        $page = $pages[0];

        // assert that affected page is article page of affected section
        $section = $page->getParentSection();
        $this->assertEquals($section, $article->getPrimarySection());

        $pagetype = $page->getPageType();
        $this->assertTrue((bool)$pagetype->getAttribute('is_article_page'));
    }
    
    
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

    public function test_getSectionPages() {
        $this->markTestSkipped('To do');
    }
    
    public function test_getAllExtras() {
        
        $al_uid = $this->fixture->getArticlelistUid();
        $article_list = tx_newspaper_ArticleList_Factory::getInstance()->create($al_uid);
        
        $extras = getAllExtras($article_list);
        debugStuff($extras);
    }
    
    public function test_getArticleListPages() {
        $al_uid = $this->fixture->getArticlelistUid();
        $article_list = tx_newspaper_ArticleList_Factory::getInstance()->create($al_uid);
        
        $pages = getArticleListPages($article_list);
        
        $this->checkIsfilledArray($pages);
        
    }

    ////////////////////////////////////////////////////////////////////////////
    
    private function checkIsfilledArray($thing, $size = 1) {
        $this->assertTrue(is_array($thing), 'Not an array');
        $this->assertGreaterThanOrEqual($size, sizeof($thing), 'Array size < ' . $size);
    }
    
}

    function debugStuff($stuff) {
        echo '<p>'.
        str_replace(' ', '&nbsp;',
            str_replace("\n", "<br/>\n", print_r($stuff, 1))
        ) .
        '</p>';
    }

?>