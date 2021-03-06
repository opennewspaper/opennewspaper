<?php
/*
 * Created on Oct 27, 2008
 *
 * Author: lene
 */

require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_pagezone.php');
require_once(PATH_typo3conf . 'ext/newspaper/tests/class.tx_newspaper_database_testcase.php');
/// testsuite for class tx_newspaper_pagezone
class test_Article_testcase extends tx_newspaper_database_testcase {

    /** setting up a typo3 FE to render correctly is extremely laborious and currently disabled. */
    const do_test_rendering = false;

    function setUp() {
        $GLOBALS['TSFE']->page['uid'] = $this->plugin_page;
        $GLOBALS['TSFE']->page['tx_newspaper_associated_section'] = $this->section_uid;
        parent::setUp();

        $this->uid = $this->fixture->getFirstUidOf('tx_newspaper_article');
        $this->article = new tx_newspaper_Article($this->uid);
    }

    function tearDown() {
        parent::tearDown();
    }

    /**
     *  storeHiddenStatusWithHooks() MUST set the 'hidden' attribute in the
     *  object and the DB.
     *  If publish_date was 0 before and hidden is set to false, publish_date
     *  MUST be set.
     *  it MUST NOT change any other attributes.
     */
    public function test_storeHiddenStatusWithHooksComplicated() {
        try{
        $temp = new tx_newspaper_Article($this->uid);
        $this->assertTrue($temp->getAttribute('publish_date') == 0);

        $saved_attributes = self::createSavedAttributesArray($temp);

        $temp->storeHiddenStatusWithHooksComplicated(true);
        $this->assertEquals(true, $temp->getAttribute('hidden'));
        $this->compareAllAttributesExceptHiddenAndPublishDate($saved_attributes, $temp);
        $this->ensureSavedHiddenStatusIs(true, $temp);
        $this->assertTrue($temp->getAttribute('publish_date') == 0);

        $temp->storeHiddenStatusWithHooksComplicated(false);
        $this->assertEquals(false, $temp->getAttribute('hidden'));
        $this->compareAllAttributesExceptHiddenAndPublishDate($saved_attributes, $temp);
        $this->ensureSavedHiddenStatusIs(false, $temp);
        $publish_date = $temp->getAttribute('publish_date');
        $this->assertFalse($publish_date == 0);

        $temp->storeHiddenStatusWithHooksComplicated(true);
        $this->assertEquals(true, $temp->getAttribute('hidden'));
        $this->compareAllAttributesExceptHiddenAndPublishDate($saved_attributes, $temp);
        $this->ensureSavedHiddenStatusIs(true, $temp);
        $this->assertEquals($publish_date, $temp->getAttribute('publish_date'));
        } catch (tx_newspaper_Exception $e) {
            $this->fail($e->getMessage() . "<br .>\n" . $e->getTraceAsString());
        }
    }

    private static function createSavedAttributesArray(tx_newspaper_Article $temp) {
        $saved_attributes = array();
        foreach (tx_newspaper_DB::getInstance()->getFields('tx_newspaper_article') as $attribute) {
            $saved_attributes[$attribute] = $temp->getAttribute($attribute);
        }
        return $saved_attributes;
    }

    private function compareAllAttributesExceptHiddenAndPublishDate(array $saved_attributes, tx_newspaper_Article $temp) {
        foreach ($saved_attributes as $attribute => $value) {
            if ($attribute == 'hidden') continue;
            if ($attribute == 'publish_date') continue;
            $this->assertEquals($saved_attributes[$attribute], $temp->getAttribute($attribute));
        }
    }

    private function ensureSavedHiddenStatusIs($what, tx_newspaper_Article $article) {
        $saved_article = new tx_newspaper_Article($article->getUid());
        $this->assertEquals($what, $saved_article->getAttribute('hidden'));
    }

    public function test_createArticle() {
		$temp = new tx_newspaper_Article($this->uid);
		$this->assertTrue(is_object($temp));
		$this->assertTrue($temp instanceof tx_newspaper_Article);
		$this->assertTrue($temp instanceof tx_newspaper_PageZone);
		$this->assertTrue($temp instanceof tx_newspaper_ExtraIface);
	}
	
	public function test_render() {

		if (!self::do_test_rendering) return;

        tx_newspaper::buildTSFE(true);
		try {
			$this->checkOutput($this->article->render());
		} catch (tx_newspaper_Exception $e) {

			$this->fail($e->getMessage()." ". $e->getTraceAsString());
		}
	}

	public function test_renderingOrder() {

        if (!self::do_test_rendering) return;
		
		/** this test relies on extras bound to article 1 having a certain order.
		 *  At the beginning of the test, this order is:
		 *  - Extra 1, paragraph 0, position 0  ('Image 1')
		 *  - Extra 4, paragraph 1, position 2  ('Image 4')
		 *  - Extra 3, paragraph 1, position 4  ('Image 3')
		 *  - Extra 2, paragraph -2, position 0 ('Image 2')
		 *  - Extra 5, paragraph -1, position 0 ('title[5]')
		 * 
		 */
		$output = $this->article->render();
		
		/// Test order of Extras among each other
		$this->checkComesBefore($output, 'Image 1', 'Image 4');	 
		$this->checkComesBefore($output, 'Image 1', 'Image 3');	 
		$this->checkComesBefore($output, 'Image 1', 'Image 2');	 
		$this->checkComesBefore($output, 'Image 1', 'title[5]');	 
		$this->checkComesBefore($output, 'Image 4', 'Image 3');	 
		$this->checkComesBefore($output, 'Image 4', 'Image 2');	 
		$this->checkComesBefore($output, 'Image 4', 'title[5]');
		$this->checkComesBefore($output, 'Image 3', 'Image 2');	 
		$this->checkComesBefore($output, 'Image 3', 'title[5]');	 
		$this->checkComesBefore($output, 'Image 2', 'title[5]');	 

		/// Test order of Extras inside text
		$this->checkComesBefore($output, 'Image 1', 'Und was fuer einer');	 
		$this->checkComesBefore($output, 'Image 4', 'Hier kommt noch etwas mehr Testtext');	 
		$this->checkComesBefore($output, 'Also darum noch ein dritter Absatz mit noch mehr Text', 'Image 2');	 

		$extras = $this->article->getExtras();
		
		/// change paragraph for one extra
		$extras[1]->setAttribute('paragraph', 1);
		$this->checkComesBefore($this->article->render(), 'Image 1', 'Image 2');	 
		$this->checkComesBefore($this->article->render(), 'Image 2', 'Image 4');	 
		
		/// change position for one extra after paragraph 1
		$extras[1]->setAttribute('position', 6);
		$this->checkComesBefore($this->article->render(), 'Image 4', 'Image 2');	 
		$this->checkComesBefore($this->article->render(), 'Image 2', 'title[5]');	 
		
		/// make paragraph for one extra greater than number of paragraphs
		$extras[1]->setAttribute('paragraph', 100);
		$this->checkComesBefore($this->article->render(), 'title[5]', 'Image 2');	 
		
		/// @todo make paragraph for one extra less than negative number of paragraphs
		$extras[1]->setAttribute('paragraph', -100);
		$this->checkComesBefore($this->article->render(), 'Image 2', 'title[5]');	 
	}
	
	public function test_getExtras() {
		$extras = $this->article->getExtras();

		$this->assertTrue(is_array($extras));
		foreach ($extras as $extra) {
			$this->assertTrue($extra instanceof tx_newspaper_Extra);
			$this->assertTrue($extra->getAttribute('uid') > 0);
			$this->assertTrue($extra->getAttribute('extra_uid') == $extra->getUid(), 
							  "Attribute 'extra_uid' (" . $extra->getAttribute('extra_uid') . ") != getUid() (" . $extra->getUid() . ")");

    	    $sf = tx_newspaper_Sysfolder::getInstance();
		    $this->assertTrue($extra->getAttribute('pid') == $sf->getPid($extra),
				    'Extra and Sysfolder give different PIDs: ' .
				    $extra->getAttribute('pid') . ' != ' .
				    $sf->getPid($extra)
            );

			if ($extra instanceof tx_newspaper_Extra_Image) {
				$this->assertTrue($extra->getAttribute('image_file') != '');
				$this->assertTrue($extra->getAttribute('title') != '');
				$this->assertTrue($extra->getAttribute('caption') != '');
			} else t3lib_div::debug($extra);

		}
		/// @todo check concrete extras in this article for correctness
	}

	public function test_addExtra() {
        $extra = new tx_newspaper_Extra_Image();
        $uid = $extra->store();
		$this->article->addExtra($extra);

        $found = false;
        foreach ($this->article->getExtras() as $article_extra) {
            if ($article_extra->getUid() == $uid) $found = true;
        }

        $this->assertTrue($found, 'added extra not found in getExtras()');
	}
	
	public function test_getSource() {
		/// No source should be returned, because none has been set
		$this->assertNull($this->article->getSource());
		$source = new tx_newspaper_DBSource();
		$this->article->setSource(array($source));
		$this->assertEquals($this->article->getSource(), array($source));
	}

	public function test_getUid() {
		$this->assertEquals($this->article->getUid(), $this->uid);
	}
	public function test_getTitle() {
		$this->assertEquals($this->article->getTitle(), 'Artikel');
	}
	public function test_getModuleName() {
		$this->assertEquals($this->article->getModuleName(), 'np_article');
	}
	
	/// Test functions in tx_newspaper_ArticleBehavior which are not covered yet
	public function test_behavior() {
		$behavior = new tx_newspaper_ArticleBehavior($this->article);
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$behavior->render();
		$behavior->getAttributeList();
	}

	public function test_store_uid() {

        if ($this->skipTestBecauseDatamap()) return;

        $this->assertTrue(tx_newspaper_DB::getInstance()->isPresent('tx_newspaper_article', 'uid = ' . $this->article->getUid()));
		$uid = $this->article->store();
		$this->assertEquals($uid, $this->article->getUid());

		/// @todo check storing of extras with article
	}

    public function test_store_AttributesEqual() {

        if ($this->skipTestBecauseDatamap()) return;

        $this->assertTrue(tx_newspaper_DB::getInstance()->isPresent('tx_newspaper_article', 'uid = ' . $this->article->getUid()));
        $uid = $this->article->store();

        /// check that record in DB equals data in memory
        $data = tx_newspaper::selectOneRow(
            '*', $this->article->getTable(), 'uid = ' . $this->article->getUid());
        foreach ($data as $key => $value) {
            $this->assertEquals($this->article->getAttribute($key), $value);
        }

    }

    public function test_store_changed() {

        if ($this->skipTestBecauseDatamap()) return;

        $this->assertTrue(tx_newspaper_DB::getInstance()->isPresent('tx_newspaper_article', 'uid = ' . $this->article->getUid()));
		/// change an attribute, store and check
		$random_string = md5(time());
		$this->article->setAttribute('bodytext',
									 $this->article->getAttribute('bodytext') . $random_string);
		$uid = $this->article->store();
		$this->assertEquals($uid, $this->article->getUid());
		$data = tx_newspaper::selectOneRow(
			'*', $this->article->getTable(), 'uid = ' . $this->article->getUid());
		$this->doTestContains($data['bodytext'], $random_string);
    }

    public function test_store_NewArticle() {

        if ($this->skipTestBecauseDatamap()) return;

        $this->assertTrue(tx_newspaper_DB::getInstance()->isPresent('tx_newspaper_article', 'uid = ' . $this->article->getUid()));
		/// create an empty article and write it. verify it's been written.
		$article = new tx_newspaper_Article();
        $random_string = md5(time());

		if ($this->article->getParentPage()) $article->setParentPage($this->article->getParentPage());

		$article->setAttribute('bodytext', $random_string);
		$uid = $article->store();
		$data = tx_newspaper::selectOneRow('*', $article->getTable(), 'uid = ' . $uid);
		$this->assertEquals($data['bodytext'], $random_string);

    }

	public function test_getSections() {
		$section = $this->article->getPrimarySection();
		$this->assertTrue($section instanceof tx_newspaper_Section);
		$this->assertEquals($section->getUid(), $this->fixture->getParentSectionUid());
	}

    const articletype_id = 0;

	public function test_listArticlesWithArticletype() {

        $articles = self::getArticlesWithArticleType();
        $num_articles = self::getNumArticlesWithArticleType();
		$this->assertEquals($num_articles, sizeof($articles), "Expected number of articles in list wrong: ". sizeof($articles));

		foreach ($articles as $article) {
			$this->assertTrue($article instanceof tx_newspaper_Article);
		}

		/// @todo ensure article type 3 is deleted, no articles should have it
		$articletype = new tx_newspaper_ArticleType(3);
		$articles = tx_newspaper_Article::listArticlesWithArticletype($articletype, 0);
		$this->assertTrue(sizeof($articles) == 0);
	}

    private static function getArticlesWithArticleType() {
        $articletype = new tx_newspaper_ArticleType(self::articletype_id);
        return tx_newspaper_Article::listArticlesWithArticletype($articletype, 0);
    }

    private static function getNumArticlesWithArticleType() {
        $row = tx_newspaper::selectOneRow('COUNT(*)', 'tx_newspaper_article', 'articletype_id = ' . self::articletype_id);
        return $row['COUNT(*)'];
    }

    public function test_getTags() {
        $tagnames = array('test-tag-1', 'test-tag-2', 'test-tag-3');
        $tagType = tx_newspaper_Tag::getContentTagType();
        $articleId = $this->article->getUid();

        $tags = $this->article->getTags($tagType);
        $this->assertEquals(0, count($tags), "No tags expected, got " . count($tags));

        $this->insertTag($articleId, $tagnames[0], $tagType);
        $tags = $this->article->getTags($tagType);
        $this->assertEquals(1, count($tags), "One tag expected, got " . count($tags));

        $this->insertTag($articleId, $tagnames[1], $tagType);
        $tags = $this->article->getTags($tagType);
        $this->assertEquals(2, count($tags), "Two tags expected, got " . count($tags));

        $this->insertTag($articleId, $tagnames[2], $tagType);
        $tags = $this->article->getTags($tagType);
        $this->assertEquals(3, count($tags), "Three tags expected, got " . count($tags));

        foreach($tags as $i => $tag) {
            $this->assertEquals($tagnames[$i], $tag->getAttribute('tag'));
        }

        $tags = $this->article->getTags(tx_newspaper_Tag::getControlTagType());
        $this->assertEquals(1, count($tags),
                            'One Tag expected. Article created with one control tag in tx_newspaper_fixture::createControlTag(); got ' . count($tags));

        $this->insertTag($articleId, 'ctrl-tag', tx_newspaper_Tag::getControlTagType());
        $tags = $this->article->getTags(tx_newspaper_Tag::getControlTagType());
        $this->assertEquals(2, count($tags), 'Two Controltags expected, got ' . count($tags));
    }

    public function test_asynchronousDepTree() {
        $this->assertTrue(t3lib_extMgm::isLoaded('asynchronous_task'));
        tx_newspaper_ExecutionTimer::start();
        tx_newspaper_Article::updateDependencyTree($this->article);
        $time = tx_newspaper_ExecutionTimer::getExecutionTime();
        $this->fail("Time: $time");
    }
	
	////////////////////////////////////////////////////////////////////////////

    private function insertTag($articleId, $tag, $tagType) {
        $tagId = tx_newspaper::insertRows('tx_newspaper_tag', array('tag' => $tag, 'tag_type' => $tagType));
        tx_newspaper::insertRows('tx_newspaper_article_tags_mm', array('uid_local' => $articleId, 'uid_foreign' => $tagId));
    }

	private function createExtras() {
		foreach ($this->extra_data as $index => $extra) {
			$query = $GLOBALS['TYPO3_DB']->INSERTquery($this->concrete_extra_table, $extra);
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
			if (!$res) die("$query failed!");
	        
	    	$extra_uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
	    	
	    	$abstract_uid = tx_newspaper_Extra::createExtraRecord($extra_uid, $this->concrete_extra_table);
	    	
	    	///	link extra to article
			$query = $GLOBALS['TYPO3_DB']->INSERTquery(
				$this->extra2article_table,
				array(
					'uid_local' => $this->uid,
					'uid_foreign' => $abstract_uid
				));
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
			if (!$res) die("$query failed!");
	    	
	    	/// set position and paragraph of extra
	    	$row = array();
	    	$row['paragraph'] = $this->extra_par_pos[$index][0];
	    	$row['position'] = $this->extra_par_pos[$index][1];
			$query = $GLOBALS['TYPO3_DB']->UPDATEquery(
				$this->extra_table, 'uid = ' . $abstract_uid, $row
			);
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
			if (!$res) die("$query failed!");
		}	
	}
	
	private function removeExtras() {
		$rows = tx_newspaper::selectRows('uid_foreign', $this->extra2article_table, 'uid_local = ' . $this->uid);
		foreach ($rows as $row) {
			$abstract_uid = $row['uid_foreign'];
			$extra = tx_newspaper::selectOneRow('extra_uid, extra_table', $this->extra_table, 'uid = ' . $abstract_uid);
			$concrete_uid = $extra['extra_uid'];
			$this->assertEquals($extra['extra_table'], $this->concrete_extra_table);
			
			tx_newspaper::deleteRows($this->extra_table, array($abstract_uid));
			tx_newspaper::deleteRows($this->extra2article_table, 
									 "uid_foreign = $abstract_uid AND uid_local = " . $this->uid);
			tx_newspaper::deleteRows($this->concrete_extra_table, array($concrete_uid));
		}
	}
	
	
	private function checkOutput($output) {
		$this->doTestContains($output, $this->article_data['title']);
		$this->doTestContains($output, $this->article_data['teaser']);
		$this->doTestContains($output, substr($this->article_data['bodytext'], 0, 100));
		$this->doTestContains($output, $this->article_data['author']);
	}
		
	private function checkComesBefore($text, $first_string, $second_string) {
		$pos1 = strpos($text, $first_string);
		if ($pos1 === false) $this->fail("$first_string is not even present");
		$pos2 = strpos($text, $second_string);
		if ($pos2 === false) $this->fail("$second_string is not even present");
		$this->assertTrue($pos1 < $pos2, "$first_string should be before $second_string");
	}

    /// tries to perform a datamap operation to see whether it works with the current DB
    private function checkDatamapWorks() {
        $this->assertTrue(tx_newspaper_DB::getInstance()->isPresent('tx_newspaper_article', 'uid = ' . $this->article->getUid()));

        $datamap['tx_newspaper_article'][$this->article->getUid()] = array('tstamp' => time());

        // use datamap, so all save hooks get called
        $tce = t3lib_div::makeInstance('t3lib_TCEmain');
        $tce->start($datamap, array());
        $tce->process_datamap();
        return (count($tce->errorLog) == 0);

    }

    private function skipTestBecauseDatamap() {
        if (!$this->checkDatamapWorks()) {
            $this->skipTest('t3lib_tcemain::process_datamap does not work correctly on this installation');
            return true;
        }
        return false;
    }

	private $section_uid = 1;			///< section we assign new articles to. @todo create my own new section
    /** @var tx_newspaper_Article */
	private $article = null;
	private $uid = null;					///< The article we use as test object
    /** @var tx_newspaper_Extra */
	private $extra = null;
	private $extra_uid = 1;
	private $plugin_page = 2472;		///< a Typo3 page containing the Plugin
	
	private $extra_table = 'tx_newspaper_extra';
	private $concrete_extra_table = 'tx_newspaper_extra_image';
	private $extra2article_table = 'tx_newspaper_article_extras_mm';
	private $pagezone_table = 'tx_newspaper_pagezone';
	
	private $article_table = 'tx_newspaper_article';
	private $article2section_table = 'tx_newspaper_article_sections_mm';
	private $article_data = array(
		'pid' => 2574,
		'tstamp' => 1234806796,
		'crdate' => 1232647355,
		'cruser_id' => 1,
		'deleted' => 0,
		'hidden' => 0,
		'starttime' => 0,
		'endtime' => 0,
		'title' => "Nummer eins!",
		'extras' => 0,
		'teaser' => "Hey, ein neuer Artikel ist im Lande!",
		'bodytext' => "<p>Und was fuer einer! Er besteht zu 100% aus Blindtext! Nicht ein einziges sinnvolles Wort. Das soll mir mal einer nachmachen.</p>\r\n<p>  Hier kommt noch etwas mehr Testtext, so dass die erste Zeile nicht so alleine da steht. Und noch mehr Text und noch mehr und noch mehr und... (ad infinitum), denn wir wollen ja einen realistischen Artikel simulieren und da steht ja meistens auch ziemlich viel Text. In manchen Artikeln stehen sogar noch mehr als zwei Absaetze, und diese auch noch prallvoll mit Text, deshalb muss in diesen Blindtext auch ne ganze Menge Text und da kann ich ja nicht schon jetzt, nach nur zwei Absaetzen, aufhoeren Text zu schreiben.</p>\r\n<p>Also darum noch ein dritter Absatz mit noch mehr Text. Ich frage mich, wie oft das Wort \"Text\" schon in diesem Text aufgetaucht ist? Oh, nach dem letzten Satz kann man gleich noch zwei zum Text-Zaehler hinzuzaehlen. Upps, das hab ich gleich noch mal \"Text\" geschrieben.</p>\r\n<p></p>",
		'author' => "Test Text",
		'sections' => 1,
		'source_id' => 1,
		'source_object' => "",
		'name' => "",
		'is_template' => 0,
		'pagezonetype_id' => 1,
		'workflow_status' => 0,
		'articletype_id' => 0,
		'inherits_from' => 0,
	);
		 
	private $extra_data = array(
		array(
			'pid' => 2573,
			'tstamp' => 1234806796,
			'crdate' => 1232647355,
			'cruser_id' => 1,
			'deleted' => 0,
			'hidden' => 0,
			'starttime' => 0,
			'endtime' => 0,
			'title' => "Image 1",	
			'image_file' => "BSD_-_Daemon_tux_thumb_02.jpg",	
			'caption' => "Caption for image 1",	
		),
		array(
			'pid' => 2573,
			'tstamp' => 1234806796,
			'crdate' => 1232647355,
			'cruser_id' => 1,
			'deleted' => 0,
			'hidden' => 0,
			'starttime' => 0,
			'endtime' => 0,
			'title' => "Image 2 Titel",	
			'image_file' => "kari.080524.gif",	
			'caption' => "Image 2 Caption",	
		),
		array(
			'pid' => 2573,
			'tstamp' => 1234806796,
			'crdate' => 1232647355,
			'cruser_id' => 1,
			'deleted' => 0,
			'hidden' => 0,
			'starttime' => 0,
			'endtime' => 0,
			'title' => "Image 3",
			'image_file' => "E3_033009T.jpg",	
			'caption' => "Caption for image 3",	
		),
		array(
			'pid' => 2573,
			'tstamp' => 1234806796,
			'crdate' => 1232647355,
			'cruser_id' => 1,
			'deleted' => 0,
			'hidden' => 0,
			'starttime' => 0,
			'endtime' => 0,
			'title' => "Image 4",	
			'image_file' => "120px-GentooFreeBSD-logo.svg_02.png",	
			'caption' => "Daemonic Gentoo",	
		),
		array(
			'pid' => 2573,
			'tstamp' => 1234806796,
			'crdate' => 1232647355,
			'cruser_id' => 1,
			'deleted' => 0,
			'hidden' => 0,
			'starttime' => 0,
			'endtime' => 0,
			'title' => "title[5]",	
			'image_file' => "lolcatsdotcomoh5o6d9hdjcawys6.jpg",	
			'caption' => "caption[5]",	
		),
	);
	
	private $extra_par_pos = array(
		array(0, 0),
		array(-2, 0),
		array(1, 4),
		array(1, 2),
		array(-1, 0),
	);
	
}
?>
