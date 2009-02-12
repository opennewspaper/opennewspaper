<?php
/*
 * Created on Oct 27, 2008
 *
 * Author: helge
 */

require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_pagezone.php');

/// testsuite for class tx_newspaper_pagezone
class test_ArticleImpl_testcase extends tx_phpunit_testcase {

	function setUp() {
		$this->article = new tx_newspaper_ArticleImpl($this->uid);
		$this->source = new tx_newspaper_DBSource();
		$this->extra = tx_newspaper_Extra_Factory::getInstance()->create($this->extra_uid);
	}

	public function test_createArticle() {
		$temp = new tx_newspaper_ArticleImpl($this->uid);
		$this->assertTrue(is_object($temp));
		$this->assertTrue($temp instanceof tx_newspaper_ArticleImpl);
		
		$this->checkOutput($temp->render());
	}
	
	public function test_render() {
		$this->checkOutput($this->article->render());		
	}
	
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
	public function test_save() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$this->article->speichern();
	}
	public function test_diff() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$this->article->vergleichen();
	}
	public function test_newExtra() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$this->article->extraAnlegen();
	}
	public function test_getExtras() {
		$extras = $this->article->getExtras();

		if (is_array($extras)) foreach ($extras as $extra) {
			$this->assertTrue($extra instanceof tx_newspaper_Extra);
		}
		/// \todo check concrete extras in this article for correctness
	}
	public function test_addExtra() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$this->article->addExtra($this->extra);
		/// \todo check if extra has been added
	}
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
		$this->assertEquals($this->article->getTitle(), 'ArticleImpl');
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
	
	public function test_relateExtra2Article() {
		tx_newspaper_ArticleImpl::relateExtra2Article('extra_table', 0, 0);
	}
	
	public function test_store() {
		$this->article->store();
		/// \todo check that record in DB equals data in memory
		/// \todo change an attribute, store and check
		/// \todo create an empty article and write it. verify it's been written.
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
	
	private $article = null;			///< the object
	private $uid = 1;					///< The article we use as test object
	private $source = null;				///< dummy source object
	private $extra = null;
	private $extra_uid = 1;
}
?>
