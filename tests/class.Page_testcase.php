<?php
/*
 * Created on Oct 27, 2008
 *
 * Author: helge
 */

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_page.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_pagezone_factory.php');

/// testsuite for class tx_newspaper_page
class test_Page_testcase extends tx_phpunit_testcase {

	function setUp() {
		$this->old_page = $GLOBALS['TSFE']->page;
		$GLOBALS['TSFE']->page['uid'] = $this->plugin_page;
		$GLOBALS['TSFE']->page['tx_newspaper_associated_section'] = $this->section_uid;
		$this->section = new tx_newspaper_Section($this->section_uid);
		$this->page = new tx_newspaper_Page($this->section, new tx_newspaper_PageType(1));
		$this->page->store();
	}

	function tearDown() {
		$GLOBALS['TSFE']->page = $this->old_page;
		/// Make sure $_GET is clean
		unset($_GET['art']);
		unset($_GET['type']);
		tx_newspaper::deleteRows($this->page->getTable(), $this->page->getUid());
		
	}

	public function test_createPage() {
#		$temp = new tx_newspaper_Page($this->section, new tx_newspaper_PageType(array()));		
		$temp = new tx_newspaper_Page($this->section, new tx_newspaper_PageType(1));
		$uid= $temp->store();		
		$this->assertTrue(is_object($temp), 'page-object is no object');
		$this->assertTrue($temp instanceof tx_newspaper_Page, 'created object is not of type '.get_class(tx_newspaper_Page));
		$this->assertEquals($temp->getUid(), $uid, 'method getUid does not return uid');
		$this->assertEquals($temp->getAttribute('uid'), $uid, 'method getAttribute does not return uid');		
		tx_newspaper::deleteRows($this->page->getTable(), $uid);
		
		$this->setExpectedException('tx_newspaper_IllegalUsageException');
		$temp = new tx_newspaper_Page('I\'m a string!');
	}
	
	public function testRender() {
		$this->assertRegExp('/.*Testressort.*/', $this->page->render(),
						    'Plugin output: '.$this->page->render());
		$this->assertRegExp('/.*Ressortseite.*/', $this->page->render(),
						    'Plugin output: '.$this->page->render());
	}
	
	public function testPageTypes() {
		$pagetype = new tx_newspaper_PageType(array('page' => 100));
		tx_newspaper::insertRows($pagetype->getTable(), array('get_var' => 'page', 'get_value' => 100));
		$this->page = new tx_newspaper_Page($this->section, 
											$pagetype);
		$this->page->store();											
		$this->assertRegExp('/.*Testressort.*/', $this->page->render('', null),
						    'Plugin output: '.$this->page->render('', null));
		$this->assertRegExp('/.*RSS.*/', $this->page->render('', null),
						    'Plugin output: '.$this->page->render('', null));						    

		$this->page = new tx_newspaper_Page($this->section, new tx_newspaper_PageType(array('art' => 1)));
		/// set an article ID for article renderer extra
		$_GET['art'] = 1;		
		$this->assertRegExp('/.*Testressort.*/', $this->page->render('', null),
						    'Plugin output: '.$this->page->render('', null));
		$this->assertRegExp('/.*Artikelseite.*/', $this->page->render('', null),
						    'Plugin output: '.preg_replace('/"data:image\/png;base64,.*?"/', '"data:image/png;base64,..."', $this->page->render('', null)));
		
		/// \todo tx_newspaper_PageType::getAvailablePageTypes()
		
	}
	
	public function testEmptyPageZones() {
		/// This test page is guaranteed to have no page zones
		$this->page = new tx_newspaper_Page($this->section, 
											new tx_newspaper_PageType(array('page' => 666)));
		$this->page->store();											
		$this->assertFalse(is_array($this->page->getPageZones()));
		$this->assertTrue($this->page->getPageZones());
	}

	public function testPageZones() {
		$this->assertTrue(is_array($this->page->getPageZones()));
		$this->assertTrue(sizeof($this->page->getPageZones()) > 0);
		$pagezones = $this->page->getPageZones();
		$this->assertEquals($pagezones[0]->getAttribute('name'), 
							'Test-Seitenbereich auf Ressortseite - 1',
							 $pagezones[0]->getAttribute('name'));
		$this->assertEquals($pagezones[1]->getAttribute('name'), 
							'Test-Seitenbereich auf Ressortseite - 2',
							 $pagezones[1]->getAttribute('name'));
	}
	
	public function test_getParentSection() {
		$this->assertEquals($this->page->getParentSection(), $this->section);
	}
	
	public function test_getTable() {
		$this->assertEquals($this->page->getTable(), 'tx_newspaper_page');
	}
	
	public function test_cloneAndStore() {
		/// clone current page
		$temp_page = clone $this->page;
		$this->assertGreaterThan($this->page->getAttribute('crdate'), $temp_page->getAttribute('crdate'));
		$this->assertGreaterThan($this->page->getAttribute('tstamp'), $temp_page->getAttribute('tstamp'));
		$this->assertEquals($temp_page->getUid(), 0);
	}
	
	public function test_toString() {
		$this->page->getAttribute('uid');
		$string = strval($this->page);
		$this->doTestContains($string, 'UID: 1');
	}
	
	////////////////////////////////////////////////////////////////////////////

	private function doTestContains($string, $word) {
		$this->assertRegExp("/.*$word.*/", $string, 
							"Plugin output (expected $word): $string");
	}

	private $section = null;
	private $page = null;					///< the object
	private $section_uid = 1;
	private $page_uid = null;				///< id of create page
}
?>
