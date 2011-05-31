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
    
    public function test_SetAbstractArticleListAttribute() {
        $al = new tx_newspaper_ArticleList_Semiautomatic(0, $this->dummySection);
		try {
	        $al->setAttribute('notes', 'dummy-section-al');
	        $al->store();
		} catch (tx_newspaper_Exception $e) {
			$this->fail('Could not set attribute \'notes\' correctly: ' . $e->getMessage());
		}

    }

    public function test_StoreArticleListTwice() {

        $row = tx_newspaper::selectRows('uid', 'tx_newspaper_articlelist');
        $old_count = count($row);
        $latest = array_pop($row);

        $al = tx_newspaper_ArticleList_Factory::getInstance()->create($latest['uid']);
        $al->store();

        $row = tx_newspaper::selectRows('*', 'tx_newspaper_articlelist');
        $this->assertEquals($old_count, count($row), 'Articlelist was stored only once.');

    }

    private $dummySection;

}

?>