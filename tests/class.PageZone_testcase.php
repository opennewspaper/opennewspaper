<?php
/*
 * Created on Oct 27, 2008
 *
 * Author: helge
 */

require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_pagezone.php');

/// testsuite for class tx_newspaper_pagezone
class test_PageZone_testcase extends tx_phpunit_testcase {

	function setUp() {
		$this->pagezone = tx_newspaper_PageZone_Factory::getInstance()->create($this->uid);
		$this->source = new tx_newspaper_DBSource();
	}

	public function test_createPageZone() {
		$temp = tx_newspaper_PageZone_Factory::getInstance()->create($this->uid);
		$this->assertTrue(is_object($temp));
		$this->assertTrue($temp instanceof tx_newspaper_PageZone);
	}
	
	public function test_Attribute() {
		$this->pagezone->setAttribute('', '');
		$this->assertEquals($this->pagezone->getAttribute(''), '');
		/// \todo test with existing and nonexisting attributes
	}
	
	public function test_Title() {
		$this->assertEquals($this->pagezone->getTitle(), 'PageZone');
	}
	
	public function test_modulename() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$this->pagezone->getModuleName();
	}

	public function test_readExtraItem() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$this->pagezone->readExtraItem(1, $this->pagezone->getName());
	}

	public function test_nonexistentZone() {
		$this->setExpectedException('tx_newspaper_DBException');
		tx_newspaper_PageZone_Factory::getInstance()->create($this->bad_uid);
	}
	
	private $bad_uid = 2000000000;			///< pagezone that does not exist
	private $pagezone = null;				///< the object
	private $source = null;
	private $uid = 1;
}
?>
