<?php
/*
 * Created on Oct 27, 2008
 *
 * Author: helge
 */

require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_section.php');

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
	
	public function test_getList() {
		$list = $this->section->getList();
		$this->assertEquals($list, 
							tx_newspaper_ArticleList_Factory::getInstance()->create(1, $this->section));
		t3lib_div::debug($list);
	}
	
	public function test_getParentPage() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$parent = $this->section->getParentPage();
	}
	
	private $section = null;					///< the object
	private $section_uid = 1;					///< uid of stored object
	private $pid = 2473;						///< pid of stored object
	private $section_name = 'Testressort';		///< section_name of stored object
	
}
?>
