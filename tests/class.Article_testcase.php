<?php
/*
 * Created on Oct 27, 2008
 *
 * Author: helge
 */

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_pagezone.php');

/// testsuite for class tx_newspaper_pagezone
class test_Article_testcase extends tx_phpunit_testcase {

	function setUp() {
		$query = $GLOBALS['TYPO3_DB']->INSERTquery($this->article_table, $this->article_data);
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		if (!$res) die("$query failed!");
	        
	    $this->uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
		
		$query = $GLOBALS['TYPO3_DB']->INSERTquery(
			$this->article2section_table,
			array(
				'uid_local' => $this->uid,
				'uid_foreign' => $this->section_uid
			));
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		if (!$res) die("$query failed!");

		$GLOBALS['TSFE']->page['uid'] = $this->plugin_page;
		$GLOBALS['TSFE']->page['tx_newspaper_associated_section'] = $this->section_uid;

		$this->createExtras();
			
		$this->article = new tx_newspaper_Article($this->uid);
		$this->source = new tx_newspaper_DBSource();
#		$this->extra = tx_newspaper_Extra_Factory::getInstance()->create($this->extra_uid);
	}
	
	function tearDown() {
		
		$this->removeExtras();
		
		//	delete article
		$query = $GLOBALS['TYPO3_DB']->DELETEquery($this->article_table, 'uid = ' . $this->uid);
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		if (!$res) die("$query failed!");		

		//	delete association with section
		$query = $GLOBALS['TYPO3_DB']->DELETEquery($this->article2section_table, 'uid_local = ' . $this->uid);
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		if (!$res) die("$query failed!");		

		//	delete page zone entry for article
		$query = $GLOBALS['TYPO3_DB']->DELETEquery(
			$this->pagezone_table, 
			'pagezone_table = \'' . $this->article_table . '\' AND pagezone_uid = ' . $this->uid
		);
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		if (!$res) die("$query failed!");		
		
		//	delete extra entry for article
		$query = $GLOBALS['TYPO3_DB']->DELETEquery(
			$this->extra_table, 
			'extra_table = \'' . $this->article_table . '\' AND extra_uid = ' . $this->uid
		);
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		if (!$res) die("$query failed!");		
	}

	public function test_createArticle() {
		$temp = new tx_newspaper_Article($this->uid);
		$this->assertTrue(is_object($temp));
		$this->assertTrue($temp instanceof tx_newspaper_Article);
		$this->assertTrue($temp instanceof tx_newspaper_PageZone);
		$this->assertTrue($temp instanceof tx_newspaper_ExtraIface);
		
		$this->checkOutput($temp->render());
	}
	
	public function test_render() {
		$this->checkOutput($this->article->render());
	}

	public function test_renderingOrder() {
		// temporarily disabled
		return;
		
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
		
		/// \todo make paragraph for one extra less than negative number of paragraphs
		$extras[1]->setAttribute('paragraph', -100);
		$this->checkComesBefore($this->article->render(), 'Image 2', 'title[5]');	 
	}
	
	/*
	public function test_import() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$this->article->importieren($this->source);
	}
	
	public function test_export() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$this->article->exportieren($this->source);
	}
	public function test_load() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$this->article->laden();
	}
	public function test_diff() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$this->article->vergleichen();
	}
	public function test_newExtra() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$this->article->extraAnlegen();
	}
	*/
	public function test_getExtras() {
		$extras = $this->article->getExtras();

		$this->assertTrue(is_array($extras));
		if (is_array($extras)) foreach ($extras as $extra) {
			$this->assertTrue($extra instanceof tx_newspaper_Extra);
			$this->assertTrue($extra->getAttribute('uid') > 0);
			$this->assertTrue($extra->getAttribute('extra_uid') == $extra->getUid(), 
							  "Attribute 'extra_uid' (" . $extra->getAttribute('extra_uid') . ") != getUid() (" . $extra->getUid() . ")");
/*			test for PID disabled because $sf->getPid() is not consistent yet
 			$sf = tx_newspaper_Sysfolder::getInstance();
			$this->assertTrue($extra->getAttribute('pid') == $sf->getPid($extra),
				'Extra and Sysfolder give different PIDs: ' . 
				$extra->getAttribute('pid') . ' != ' .
				$sf->getPid($extra));
*/			if ($extra instanceof tx_newspaper_Extra_Image) {
				$this->assertTrue($extra->getAttribute('image') != '');
				$this->assertTrue($extra->getAttribute('title') != '');
				$this->assertTrue($extra->getAttribute('caption') != '');
			} else if ($extra instanceof tx_newspaper_Extra_ArticleRenderer) { 
				
			} else t3lib_div::debug($extra);

		}
		/// \todo check concrete extras in this article for correctness
	}
	/*
	public function test_addExtra() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$this->article->addExtra($this->extra);
		/// \todo check if extra has been added
	}
	*/
	public function test_getSource() {
		/// No source should be returned, because none has been set
		$this->assertNull($this->article->getSource());
		$this->article->setSource($this->source);
		$this->assertEquals($this->article->getSource(), $this->source);
	}
	public function test_getUid() {
		$this->assertEquals($this->article->getUid(), $this->uid);
	}
	public function test_getTitle() {
		$this->assertEquals($this->article->getTitle(), 'Article');
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
		
	public function test_store() {
		$uid = $this->article->store();
		$this->assertEquals($uid, $this->article->getUid());

		/// check that record in DB equals data in memory
		$data = tx_newspaper::selectOneRow(
			'*', $this->article->getTable(), 'uid = ' . $this->article->getUid());
		foreach ($data as $key => $value) {
			$this->assertEquals($this->article->getAttribute($key), $value);
		}
		
		/// \todo check storing of extras with article
		
		/// change an attribute, store and check
		$random_string = md5(time());
		$this->article->setAttribute('text', 
									 $this->article->getAttribute('text') . $random_string);
		$uid = $this->article->store();
		$this->assertEquals($uid, $this->article->getUid());
		$data = tx_newspaper::selectOneRow(
			'*', $this->article->getTable(), 'uid = ' . $this->article->getUid());
		$this->doTestContains($data['text'], $random_string);
		
		/// create an empty article and write it. verify it's been written.
		$article = new tx_newspaper_Article();

		if ($this->article->getParentPage()) $article->setParentPage($this->article->getParentPage());
		
		$article->setAttribute('text', $random_string);
		$uid = $article->store();
		$data = tx_newspaper::selectOneRow('*', $article->getTable(), 'uid = ' . $uid);
		$this->assertEquals($data['text'], $random_string);
		
		/// delete article
		$GLOBALS['TYPO3_DB']->exec_DELETEquery($article->getTable(), 'uid = ' . $uid);
	}	
	
	public function test_getSections() {
		$section = $this->article->getPrimarySection();
		$this->assertTrue($section instanceof tx_newspaper_Section);
		$this->assertEquals($section->getUid(), 1);
	}
	
	public function test_listArticlesWithArticletype() {
		/// many articles are articletype 0 because it's a field that was introduced late
		$articletype = new tx_newspaper_ArticleType(0);
		$articles = tx_newspaper_Article::listArticlesWithArticletype($articletype, 10);
		$this->assertTrue(sizeof($articles) == 10);
		foreach ($articles as $article) {
			$this->assertTrue($article instanceof tx_newspaper_Article);
		}
		
		/// article type 3 is deleted, no articles should have it
		/// \todo select uid from tx_newspaper_articletype where deleted = 1 order by uid asc limit 1
		$articletype = new tx_newspaper_ArticleType(3);
		$articles = tx_newspaper_Article::listArticlesWithArticletype($articletype, 10);
		$this->assertTrue(sizeof($articles) == 0);
	}
	
	////////////////////////////////////////////////////////////////////////////

	private function createExtras() {
		foreach ($this->extra_data as $index => $extra) {
			$query = $GLOBALS['TYPO3_DB']->INSERTquery($this->concrete_extra_table, $extra);
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
			if (!$res) die("$query failed!");
	        
	    	$extra_uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
	    	
	    	$abstract_uid = tx_newspaper_Extra::createExtraRecord($extra_uid, $this->concrete_extra_table);
	    	
	    	///	link extra to article
			$query = $GLOBALS['TYPO3_DB']->INSERTquery(
				$this->article2extra_table,
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
		$rows = tx_newspaper::selectRows('uid_foreign', $this->article2extra_table, 'uid_local = ' . $this->uid);
		foreach ($rows as $row) {
			$abstract_uid = $row['uid_foreign'];
			$extra = tx_newspaper::selectOneRow('extra_uid, extra_table', $this->extra_table, 'uid = ' . $abstract_uid);
			$concrete_uid = $extra['extra_uid'];
			$this->assertEquals($extra['extra_table'], $this->concrete_extra_table);
			
			tx_newspaper::deleteRows($this->extra_table, array($abstract_uid));
			tx_newspaper::deleteRows($this->article2extra_table, 
									 "uid_foreign = $abstract_uid AND uid_local = " . $this->uid);
			tx_newspaper::delete_rows($this->concrete_extra_table, array($concrete_uid));
		}
	}
	
	
	private function checkOutput($output) {
		$this->doTestContains($output, $this->article_data['title']);
		$this->doTestContains($output, $this->article_data['teaser']);
		$this->doTestContains($output, substr($this->article_data['text'], 0, 100));
		$this->doTestContains($output, $this->article_data['author']);
	}
	
	private function doTestContains($string, $word) {
		$this->assertRegExp("/.*$word.*/", $string, 
							"Plugin output (expected $word): $string");
	}
	
	private function checkComesBefore($text, $first_string, $second_string) {
		$pos1 = strpos($text, $first_string);
		if ($pos1 === false) $this->fail("$first_string is not even present");
		$pos2 = strpos($text, $second_string);
		if ($pos2 === false) $this->fail("$second_string is not even present");
		$this->assertTrue($pos1 < $pos2, "$first_string should be before $second_string");
	}
	
	private $section_uid = 1;			///< section we assign new articles to. \todo create my own new section
	private $article = null;			///< the object
	private $uid = 0;					///< The article we use as test object
	private $source = null;				///< dummy source object
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
		'sorting' => 64,
		'deleted' => 0,
		'hidden' => 0,
		'starttime' => 0,
		'endtime' => 0,
		'fe_group' => 0,
		'title' => "Nummer eins!",
		'extras' => 0,
		'teaser' => "Hey, ein neuer Artikel ist im Lande!",
		'text' => "<p>Und was fuer einer! Er besteht zu 100% aus Blindtext! Nicht ein einziges sinnvolles Wort. Das soll mir mal einer nachmachen.</p>\r\n<p>  Hier kommt noch etwas mehr Testtext, so dass die erste Zeile nicht so alleine da steht. Und noch mehr Text und noch mehr und noch mehr und... (ad infinitum), denn wir wollen ja einen realistischen Artikel simulieren und da steht ja meistens auch ziemlich viel Text. In manchen Artikeln stehen sogar noch mehr als zwei Absaetze, und diese auch noch prallvoll mit Text, deshalb muss in diesen Blindtext auch ne ganze Menge Text und da kann ich ja nicht schon jetzt, nach nur zwei Absaetzen, aufhoeren Text zu schreiben.</p>\r\n<p>Also darum noch ein dritter Absatz mit noch mehr Text. Ich frage mich, wie oft das Wort \"Text\" schon in diesem Text aufgetaucht ist? Oh, nach dem letzten Satz kann man gleich noch zwei zum Text-Zaehler hinzuzaehlen. Upps, das hab ich gleich noch mal \"Text\" geschrieben.</p>\r\n<p></p>",
		'author' => "Test Text",
		'sections' => 1,
		'source_id' => 1,
		'source_object' => "",
		'name' => "",
		'is_template' => 0,
		'template_set' => "",
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
			'sorting' => 16,
			'deleted' => 0,
			'hidden' => 0,
			'starttime' => 0,
			'endtime' => 0,
			'fe_group' => 0,
			'extra_field' => "extra_field for tx_newspaper_extra_image[1]\r\n(whatever is this field for?)",
			'title' => "Image 1",	
			'image' => "BSD_-_Daemon_tux_thumb_02.jpg",	
			'caption' => "Caption for image 1",	
			'template_set' => "",	
		),
		array(
			'pid' => 2573,
			'tstamp' => 1234806796,
			'crdate' => 1232647355,
			'cruser_id' => 1,
			'sorting' => 32,
			'deleted' => 0,
			'hidden' => 0,
			'starttime' => 0,
			'endtime' => 0,
			'fe_group' => 0,
			'extra_field' => "tx_newspaper_extra_image[2].extra_field",	
			'title' => "Image 2 Titel",	
			'image' => "kari.080524.gif",	
			'caption' => "Image 2 Caption",	
			'template_set' => "",	
		),
		array(
			'pid' => 2573,
			'tstamp' => 1234806796,
			'crdate' => 1232647355,
			'cruser_id' => 1,
			'sorting' => 96,
			'deleted' => 0,
			'hidden' => 0,
			'starttime' => 0,
			'endtime' => 0,
			'fe_group' => 0,
			'extra_field' => "",	
			'title' => "Image 3",
			'image' => "E3_033009T.jpg",	
			'caption' => "Caption for image 3",	
			'template_set' => "",	
		),
		array(
			'pid' => 2573,
			'tstamp' => 1234806796,
			'crdate' => 1232647355,
			'cruser_id' => 1,
			'sorting' => 128,
			'deleted' => 0,
			'hidden' => 0,
			'starttime' => 0,
			'endtime' => 0,
			'fe_group' => 0,
			'extra_field' => "",	
			'title' => "Image 4",	
			'image' => "120px-GentooFreeBSD-logo.svg_02.png",	
			'caption' => "Daemonic Gentoo",	
			'template_set' => "",	
		),
		array(
			'pid' => 2573,
			'tstamp' => 1234806796,
			'crdate' => 1232647355,
			'cruser_id' => 1,
			'sorting' => 0,
			'deleted' => 0,
			'hidden' => 0,
			'starttime' => 0,
			'endtime' => 0,
			'fe_group' => 0,
			'extra_field' => "extra_field[5]",	
			'title' => "title[5]",	
			'image' => "lolcatsdotcomoh5o6d9hdjcawys6.jpg",	
			'caption' => "caption[5]",	
			'template_set' => "",	
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
