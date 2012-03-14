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

        $row = tx_newspaper::selectRows('*', 'tx_newspaper_articlelist' , "list_table = 'tx_newspaper_articlelist_semiautomatic'");
        $this->assertGreaterThan(0, intval($row[0]['uid']), 'No automatic article list found: ' . print_r($row, 1));

        $al = tx_newspaper_ArticleList_Factory::getInstance()->create($row[0]['uid']);
        $articles_before = self::makeUIDarray($al->getArticles(2));

        $this->assertEquals(2, sizeof($articles_before), 'Need at least 2 articles in list: ' . print_r($articles_before, 1));

        $articles_swapped = array();
        $articles_swapped[0] = $articles_before[1];
        $articles_swapped[1] = $articles_before[0];

        $uids = array(
            array($articles_swapped[0], -1),
            array($articles_swapped[1],  0),
        );

        $al->assembleFromUIDs($uids);

        $articles_after = self::makeUIDarray($al->getArticles(2));
        $this->assertEquals(
            sizeof($articles_swapped), sizeof($articles_after),
            'Size of articles in list after assembleFromUids() (' . sizeof($articles_after) .
            ') does not match size of UID array (' . sizeof($articles_swapped) . ')'
        );

        $mm = tx_newspaper::selectRows('*', 'tx_newspaper_articlelist_semiautomatic_articles_mm');
        for ($i = 0; $i < sizeof($articles_swapped); $i++) {
            $this->assertTrue(
                $articles_swapped[$i] == $articles_after[$i],
                "article $i is not equal after assembleFromUids(): " .
                        print_r($articles_swapped, 1) . " != " . print_r($articles_after, 1) .
                        'MM table: ' . print_r($mm, 1)
            );
        }

    }

    ////////////////////////////////////////////////////////////////////////////

    private static function getLastArticleListUid() {
        $row = tx_newspaper::selectRows('uid', 'tx_newspaper_articlelist');
        self::$num_article_lists = count($row);
        $latest = array_pop($row);
        return intval($latest['uid']);
    }

    private static function makeUIDarray(array $articles) {
        $uids = array();
        foreach ($articles as $article) {
            $uids[] = $article->getUid();
        }
        return $uids;
    }

    private $dummy_section;

    private static $num_article_lists;

}

?>