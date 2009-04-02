<?php
/*
 * Created on Oct 27, 2008
 *
 * Author: helge
 */

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_section.php');

/// testsuite for class tx_newspaper_department
class test_Section_testcase extends tx_phpunit_testcase {

	function setUp() {
		$this->section = new tx_newspaper_Section($this->section_uid);
	}

	public function test_createSection() {
		$temp = new tx_newspaper_Section($this->section_uid);
		$this->assertTrue(is_object($temp));
		$this->assertTrue($temp instanceof tx_newspaper_Section);
	}
	
	public function test_getAttribute() {
		$this->assertEquals($this->section->getAttribute('uid'), 1);
		$this->assertEquals($this->section->getAttribute('pid'), $this->pid);
		$this->assertEquals($this->section->getAttribute('section_name'), $this->section_name);
		$this->setExpectedException('tx_newspaper_Exception');
		$this->section->getAttribute('es gibt mich nicht, schmeiss ne exception!');
	}
	
	public function test_setAttribute() {
		$this->section->setAttribute('uid', -1);
		$this->assertEquals($this->section->getAttribute('uid'), -1);
		$this->section->setAttribute('pid', -1);
		$this->assertEquals($this->section->getAttribute('pid'), -1);
		$this->section->setAttribute('section_name', 'my unique section name');
		$this->assertEquals($this->section->getAttribute('section_name'), 'my unique section name');
		$this->section->setAttribute('es gibt mich nicht', 'aber jetzt gibt es mich');
		$this->assertEquals($this->section->getAttribute('es gibt mich nicht'), 'aber jetzt gibt es mich');
	}

	public function test_store() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$this->section->store();
	}
	
	public function test_Title() {
		global $LANG;
		$LANG->lang = 'default';
		$this->assertEquals($this->section->getTitle(), 'Section');
		/*
		//  setting the language does not work this way. disabled the test until i know how to do it.
		$LANG->lang = 'de';
		$this->assertEquals($this->section->getTitle(), 'Ressort');
		*/
	}
	
	public function test_getArticleList() {
		$list = $this->section->getArticleList();
		$this->assertEquals($list, 
							tx_newspaper_ArticleList_Factory::getInstance()->create(1, $this->section));
		
		$this->assertEquals($list->getTitle(), 'Automatic article list');
		$this->assertEquals($list->getUid(), 1);
		
		// section 1 has currently 7 articles associated with it.
		$articles = $list->getArticles(7);
		$this->assertTrue(sizeof($articles) == 7);
		foreach ($articles as $article) {
			$this->assertTrue($article instanceof tx_newspaper_Article);
			t3lib_div::debug($article->getAttribute('title'));
		}
		
		$article = $list->getArticle(1);
		$this->assertTrue($article instanceof tx_newspaper_Article);
		$this->assertEquals($article->getAttribute('title'), 'Nummer zwei');
		
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$list->store();
	}
	
	public function test_ArticleList() {
		$registered = tx_newspaper_ArticleList::getRegisteredArticleLists();
		$this->assertTrue($registered[0] instanceof tx_newspaper_ArticleList_Auto);
		
		$list = $this->section->getArticleList();
		$list->setAttribute('new attribute', 1);
		$this->assertEquals($list->getAttribute('new attribute'), 1);
		$this->assertEquals($list->getAttribute('uid'), 1);
		$this->setExpectedException('tx_newspaper_WrongAttributeException');
		$list->getAttribute('wrong attribute');
	}
	
	public function test_getParentSection() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$parent = $this->section->getParentSection();
	}
	
	public function test_getSubPages() {
		$subpages = $this->section->getSubPages();
		t3lib_div::debug($subpages);
	}

	private $section = null;					///< the object
	private $section_uid = 1;					///< uid of stored object
	private $pid = 2828;						///< pid of stored object
	private $section_name = 'Testressort';		///< section_name of stored object
	
}
?>
