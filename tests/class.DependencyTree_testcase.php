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
        $this->assertTrue(!(getArticlePage($section) == null));
        
    }

    public function test_getPages() {
        
        $uid = $this->fixture->getArticleUid();
        $article = new tx_newspaper_Article($uid);
        
        $tree = tx_newspaper_DependencyTree::generateFromArticle($article);
        print_r($tree);
        
        $pages = $tree->getPages();
        
        print_r($pages);
    }
    private $dummySection;

}

?>