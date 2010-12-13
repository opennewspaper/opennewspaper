<?php

require_once(PATH_typo3conf . 'ext/newspaper/tests/class.tx_newspaper_database_testcase.php');
/// testsuite for class tx_newspaper_pagezone
class test_DependencyTree_testcase extends tx_newspaper_database_testcase {

    public function setUp() {
        parent::setUp();
        $sectionUid = tx_newspaper::insertRows('tx_newspaper_section', array('section_name' => 'dunmy'));
        $this->dummySection = new tx_newspaper_Section($sectionUid);
    }

    public function tearDown() {
        $this->clearDatabase();
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
        $this->assertTrue(is_array($article_lists));
        $this->assertGreaterThanOrEqual(1, sizeof($article_lists));
        debugStuff($article_lists);
    }
    
    public function test_getAffectedArticleLists() {
        $uid = $this->fixture->getArticleUid();
        $article = new tx_newspaper_Article($uid);
      
        $affected_article_lists = getAffectedArticleLists($article);
        
        $this->assertTrue(is_array($affected_article_lists));
        $this->assertGreaterThanOrEqual(1, sizeof($affected_article_lists));
        debugStuff($affected_article_lists);
        
    }

    private $dummySection;


}

    function debugStuff($stuff) {
        echo '<p>'.
        str_replace(' ', '&nbsp;',
            str_replace("\n", "<br/>\n", print_r($stuff, 1))
        ) .
        '</p>';
    }

?>