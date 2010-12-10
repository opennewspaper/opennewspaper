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

    public function test_getPages() {
        
        $uid = $this->fixture->getArticleUid();
        print_r($uid);
        $article = new tx_newspaper_Article($this->uid);
        print_r($this->fixture->getParentSectionUid());
        print_r($article->getSections());
        print_r(tx_newspaper::selectRowsDirect('uid_local, uid_foreign', 'tx_newspaper_article_sections_mm'));
        
        $tree = tx_newspaper_DependencyTree::generateFromArticle($article);
        
        $pages = $tree->getPages();
        
        print_r($pages);
    }
    private $dummySection;

}

?>