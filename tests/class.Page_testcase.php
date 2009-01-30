<?php
/*
 * Created on Oct 27, 2008
 *
 * Author: helge
 */

require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_page.php');
require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_pagezone_factory.php');

/// testsuite for class tx_newspaper_page
class test_Page_testcase extends tx_phpunit_testcase {

	function setUp() {
		$this->section = new tx_newspaper_Section($this->section_uid);
		$this->page = new tx_newspaper_Page($this->section, 'NOT get_var');
	}

	public function test_createPage() {
		$temp = new tx_newspaper_Page($this->section);
		$this->assertTrue(is_object($temp));
		$this->assertTrue($temp instanceof tx_newspaper_Page);
	}
	
	public function testRender() {
		$this->assertRegExp('/.*Testressort.*/', $this->page->render(),
						    'Plugin output: '.$this->page->render());
		$this->assertRegExp('/.*Ressortseite.*/', $this->page->render(),
						    'Plugin output: '.$this->page->render());
	}
	
	public function testPageTypes() {
		$this->page = new tx_newspaper_Page($this->section, 'get_var = \'type\' AND get_value = 100');
		$this->assertRegExp('/.*Testressort.*/', $this->page->render('', null),
						    'Plugin output: '.$this->page->render('', null));
		$this->assertRegExp('/.*RSS.*/', $this->page->render('', null),
						    'Plugin output: '.$this->page->render('', null));						    

		$this->page = new tx_newspaper_Page($this->section, 'get_var = \'art\'');
		$this->assertRegExp('/.*Testressort.*/', $this->page->render('', null),
						    'Plugin output: '.$this->page->render('', null));
		$this->assertRegExp('/.*Artikelseite.*/', $this->page->render('', null),
						    'Plugin output: '.$this->page->render('', null));
		
	}
	
	public function testEmptyPageZones() {
		/// This test page is guaranteed to have no page zones
		$this->page = new tx_newspaper_Page($this->section, 'get_var = \'type\' AND get_value = 666');
		$this->assertTrue(is_array($this->page->getPageZones()));
		$this->assertEquals($this->page->getPageZones(), array());
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
	
	public function test_getParent() {
		$this->assertEquals($this->page->getParent(), $this->section);
	}
	
	public function test_getName() {
		$this->assertEquals($this->page->getName(), 'tx_newspaper_page');
	}
	
	private $section = null;
	private $page = null;					///< the object
	private $section_uid = 1;
}
?>
