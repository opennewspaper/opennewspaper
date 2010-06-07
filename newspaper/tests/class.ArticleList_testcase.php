<?php

require_once(PATH_typo3conf . 'ext/newspaper/tests/class.tx_newspaper_database_testcase.php');
/// testsuite for class tx_newspaper_pagezone
class test_ArticleList_testcase extends tx_newspaper_database_testcase {

    public function setUp() {
        parent::setUp();
        $sectionUid = tx_newspaper::insertRows('tx_newspaper_section', array('section_name' => 'dunmy'));
        $this->dummySection = new tx_newspaper_Section($sectionUid);
    }

    public function tearDown() {
        $this->clearDatabase();
    }
    
    public function test_StoreArticleList() {
        $al = new tx_newspaper_ArticleList_Semiautomatic(0, $this->dummySection);
        $al->setAttribute('notes', 'dummy-section-al');
        $al->store();
    }

    public function test_StoreArticleListTwice() {

        $row = tx_newspaper::selectRows('uid', 'tx_newspaper_articlelist');
        $this->assertEquals(1, count($row), 'Expected one entry table tx_newspaper_articlelist.');

        $al = tx_newspaper_ArticleList_Factory::getInstance()->create($row['uid']);
        $al->store();

        $row = tx_newspaper::selectRows('uid', 'tx_newspaper_articlelist');
        $this->assertEquals(1, count($row), 'A duplicated articlelist was stored.');

    }

    private $dummySection;

}

?>