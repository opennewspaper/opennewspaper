<?php
/*
 * Created on Oct 27, 2008
 *
 * Author: helge
 */

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_pagezone.php');

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
		$this->assertEquals($this->pagezone->getModuleName(), 'np_pagezone_page');
	}
	/*
	public function test_readExtraItem() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		$this->pagezone->readExtraItem(1, $this->pagezone->getTable());
	}
	*/
	public function test_nonexistentZone() {
		$this->setExpectedException('tx_newspaper_DBException');
		tx_newspaper_PageZone_Factory::getInstance()->create($this->bad_uid);
	}
	/*
	public function test_store() {
		$this->pagezone->store();
		/// \todo check that record in DB equals data in memory
		/// \todo change an attribute, store and check
		/// \todo create an empty pagezone and write it. verify it's been written.
		/// \see ArticleImpl_testcase
		$this->fail('PageZone->store() not yet implemented. Requirements not known yet.');
	}	
	*/
	
	public function test_PageZoneType() {
		$rows = tx_newspaper_PageZoneType::getAvailablePageZoneTypes();
		foreach ($rows as $row) {
			$pzt = new tx_newspaper_PageZoneType($row['uid']);
			foreach ($row as $attribute => $value) {
				$this->assertEquals($pzt->getAttribute($attribute), $value);
			}
			$this->assertEquals($pzt->getTable(), 'tx_newspaper_pagezonetype');
			$this->assertEquals($pzt->getModuleName(), 'np_pagezonetype');
			$this->assertEquals($pzt->getTitle(), 'Page Zone Type');
			
			$pzt->setAttribute('uid', 0);
			$this->assertEquals($pzt->getAttribute('uid'), 0);
		}
	}
	
	/**	setAttribute is tested without calling getAttribute() first
	 *  WrongAttributeException is tested */
	public function test_PageZoneType_2() {
		$rows = tx_newspaper_PageZoneType::getAvailablePageZoneTypes();
		foreach ($rows as $row) {
			$pzt = new tx_newspaper_PageZoneType($row['uid']);
			
			$pzt->setAttribute('uid', 0);
			$this->assertEquals($pzt->getAttribute('uid'), 0);

			$this->setExpectedException('tx_newspaper_WrongAttributeException');
			$pzt->getAttribute('Gibts nicht');
		}	
	}
	
	/// test NotYetImplementedException
	public function test_PageZoneType_3() {
		$rows = tx_newspaper_PageZoneType::getAvailablePageZoneTypes();
		foreach ($rows as $row) {
			$pzt = new tx_newspaper_PageZoneType($row['uid']);
			$this->setExpectedException('tx_newspaper_NotYetImplementedException');
			$pzt->store();
		}
	}
	
	/// \todo finish test
	public function test_clone() {
		$cloned = clone $this->pagezone;
		$this->assertEquals($cloned->getAttribute('uid'), 0);
		$this->assertEquals($cloned->getUid(), 0);
		$this->assertEquals($cloned->getAttribute('crdate'), time());
		$this->assertEquals($cloned->getAttribute('tstamp'), time());
		
		// ...
		t3lib_div::debug("finish me!");
	}
	
	public function test_getActivePageZones() {
		t3lib_div::debug(tx_newspaper_PageZone::getActivePageZones(1)));
		t3lib_div::debug("finish me!");
	}
	
	private $bad_uid = 2000000000;			///< pagezone that does not exist
	private $pagezone = null;				///< the object
	private $source = null;
	private $uid = 1;
}
?>
