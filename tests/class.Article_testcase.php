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
		$this->article = new tx_newspaper_Article($this->uid);
		$this->source = new tx_newspaper_DBSource();
		$this->extra = tx_newspaper_Extra_Factory::getInstance()->create($this->extra_uid);
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
		$this->checkComesBefore($this->article->render(), 'title[5]', 'Image 2');	 
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
			$this->assertTrue($extra->getAttribute('uid') == $extra->getUid());
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
		$article->setAttribute('text', $random_string);
		$uid = $article->store();
		$data = tx_newspaper::selectOneRow('*', $article->getTable(), 'uid = ' . $uid);
		$this->assertEquals($data['text'], $random_string);
		
		/// delete article
		$GLOBALS['TYPO3_DB']->exec_DELETEquery($article->getTable(), 'uid = ' . $uid);
	}	
	
	////////////////////////////////////////////////////////////////////////////
	
	private function checkOutput($output) {
		$this->doTestContains($output, 'Neuer Artikel');
		$this->doTestContains($output, 'Nummer eins');
		$this->doTestContains($output, 'Artikel ist im Lande');
		$this->doTestContains($output, 'Test Text');
		$this->doTestContains($output, 'Nicht ein einziges sinnvolles Wort');		
	}
	
	private function doTestContains($string, $word) {
		$this->assertRegExp("/.*$word.*/", $string, 
							"Plugin output (expected $word): $string");
	}
	
	private function checkComesBefore($text, $first_string, $second_string) {
		t3lib_div::debug(preg_replace('/"data:image\/png;base64,.*?"/', '"data:image/png;base64,..."', $text));
		$pos1 = strpos($text, $first_string);
		t3lib_div::debug("$first_string at $pos1");
		if ($pos1 === false) return false;	// $first_string not found
		$pos2 = strpos($text, $second_string);
		t3lib_div::debug("$second_string at $pos2");
		if ($pos2 === false) return false;	// $second_string not found
		$this->assertTrue($pos1 < $pos2);
	}
	
	private $article = null;			///< the object
	private $uid = 1;					///< The article we use as test object
	private $source = null;				///< dummy source object
	private $extra = null;
	private $extra_uid = 1;
}
?>
