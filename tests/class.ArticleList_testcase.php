<?php

require_once(PATH_typo3conf . 'ext/newspaper/tests/class.tx_newspaper_database_testcase.php');
/// testsuite for class tx_newspaper_pagezone
class test_ArticleList_testcase extends tx_newspaper_database_testcase {

    public function setUp() {
        parent::setUp();
        $sectionUid = tx_newspaper::insertRows('tx_newspaper_section', array('section_name' => 'dunmy'));
        $this->dummy_section = new tx_newspaper_Section($sectionUid);
    }

    public function tearDown() {
        $this->clearDatabase();
    }

    const note_to_test = 'dummy-section-al';
    public function test_SetAbstractArticleListAttribute() {
        $al = tx_newspaper_ArticleList_Factory::getInstance()->create(self::getLastArticleListUid());
		try {
	        $al->setAttribute('notes', self::note_to_test);
	        $al->store();
		} catch (tx_newspaper_Exception $e) {
			$this->fail('Could not set attribute \'notes\' correctly: ' . $e->getMessage());
		}
        $al_copy = tx_newspaper_ArticleList_Factory::getInstance()->create($al->getAbstractUid());
        try {
            $this->assertEquals(
                $al_copy->getAttribute('notes'), self::note_to_test,
                $al_copy->getAttribute('notes') . ' == ' . self::note_to_test);
        } catch (tx_newspaper_Exception $e) {
            $this->fail('Could not read attribute \'notes\' correctly: ' . $e->getMessage());
        }


    }

    public function test_StoreArticleListTwice() {

        $al = tx_newspaper_ArticleList_Factory::getInstance()->create(self::getLastArticleListUid());
        $al->store();

        $row = tx_newspaper::selectRows('*', 'tx_newspaper_articlelist');
        $this->assertEquals(self::$num_article_lists, count($row), 'Articlelist was stored only once.');

    }

    public function test_automaticArticleListGetArticles() {

        $row = tx_newspaper::selectRows('uid', 'tx_newspaper_articlelist' , "list_table = 'tx_newspaper_articlelist_semiautomatic'");
        $this->assertGreaterThan(0, intval($row['uid']), 'No automatic article list found');

        $al = tx_newspaper_ArticleList_Factory::getInstance()->create($row['uid']);

        $this->fail(print_r($al->getArticles(10), 1));
    }

    ////////////////////////////////////////////////////////////////////////////

    private static function getLastArticleListUid() {
        $row = tx_newspaper::selectRows('uid', 'tx_newspaper_articlelist');
        self::$num_article_lists = count($row);
        $latest = array_pop($row);
        return intval($latest['uid']);
    }


    private $dummy_section;

    private static $num_article_lists;

}

?>