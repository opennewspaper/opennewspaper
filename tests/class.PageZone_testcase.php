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

		if (0) {
			$this->uid = tx_newspaper::insertRows($this->pagezone_page_table, $this->pagezone_page_data);
		} else {
			$query = $GLOBALS['TYPO3_DB']->INSERTquery($this->pagezone_page_table, $this->pagezone_page_data);
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
			if (!$res) die("$query failed!");
	        
		    $this->uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
		}

		$this->pagezone = new tx_newspaper_PageZone_Page($this->uid);

		$this->createExtras();

		$this->source = new tx_newspaper_DBSource();
	}
	
	function tearDown() {
		
		$this->removeExtras();
		
		//	delete pagezone_papge
		tx_newspaper::deleteRows($this->pagezone_page_table, 'uid = ' . $this->uid);

		//	delete page zone entry for pagezone_page
		tx_newspaper::deleteRows(
			$this->pagezone_table,
			'pagezone_table = \'' . $this->pagezone_page_table . '\' AND pagezone_uid = ' . $this->uid
		);
		
		//	delete extra entry for pagezone_page
		tx_newspaper::deleteRows(
			$this->extra_table, 
			'extra_table = \'' . $this->pagezone_page_table . '\' AND extra_uid = ' . $this->uid
		);
	}

	public function test_createPageZone() {
		$temp = tx_newspaper_PageZone_Factory::getInstance()->create($this->pagezone->getPageZoneUID());
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
		
	public function test_PageZoneType() {
		$rows = tx_newspaper_PageZoneType::getAvailablePageZoneTypes();
		foreach ($rows as $pzt) {
			foreach (tx_newspaper::getAttributes($pzt) as $attribute) {
//				$this->assertEquals($pzt->getAttribute($attribute), $value);
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
		foreach ($rows as $pzt) {
			
			$pzt->setAttribute('uid', 0);
			$this->assertEquals($pzt->getAttribute('uid'), 0);

			$this->setExpectedException('tx_newspaper_WrongAttributeException');
			$pzt->getAttribute('Gibts nicht');
		}	
	}
	
	/// test NotYetImplementedException
	public function test_PageZoneType_3() {
		$rows = tx_newspaper_PageZoneType::getAvailablePageZoneTypes();
		foreach ($rows as $pzt) {
			$this->setExpectedException('tx_newspaper_NotYetImplementedException');
			$pzt->store();
		}
	}
	
	
	////////////////////////////////////////////////////////////////////////////
	//	still a lot of work to be done here
	////////////////////////////////////////////////////////////////////////////
	
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
	
	public function test_store() {
		$this->pagezone->store();
		/// \todo check that record in DB equals data in memory
		/// \todo change an attribute, store and check
		/// \todo create an empty pagezone and write it. verify it's been written.
		/// \see ArticleImpl_testcase
		$this->fail('PageZone->store() not yet implemented. Requirements not known yet.');
	}	

	public function test_getUid() {
		$this->assertEquals($this->pagezone->getUid(), $this->uid);
	}

	public function test_setUid() {
		$this->pagezone->setUid(1);
		$this->assertEquals($this->pagezone->getUid(), 1);
	}
	
	public function test_getTable() {
		$this->assertEquals($this->pagezone->getTable(), 'tx_newspaper_pagezone_page');
	}
	
	public function test_getModuleName() {
		$this->assertEquals($this->pagezone->getModuleName(), 'np_pagezone_page');
	}
		
	public function test_render() {
		$this->fail('test_render not yet implemented');
	}
	
	public function test_getAbstractUid() {
		$this->fail('test_getAbstractUid not yet implemented');
	}

	public function test_getParentPage() {
		$this->fail('test_getParentPage not yet implemented');
	}

	public function test_setParentPage() {
		$this->fail('test_setParentPage not yet implemented');
	}
	
	public function test_getParentForPlacement() {
		$this->fail('test_getParentForPlacement not yet implemented');
	}
	
	public function test_getInheritanceHierarchyUp() {
		$this->fail('test_getInheritanceHierarchyUp not yet implemented');
	}

	public function test_getInheritanceHierarchyDown() {
		$this->fail('test_getInheritanceHierarchyDown not yet implemented');
	}
	
	public function test_insertInheritedExtraAfter() {
		$this->fail('test_insertInheritedExtraAfter not yet implemented');
	}
	
	public function test_copyExtrasFrom() {
		$this->fail('test_copyExtrasFrom not yet implemented');
	}
	
	public function test_insertExtraAfter() {
		$this->fail('test_insertExtraAfter not yet implemented');
	}
	
	public function test_removeExtra() {
		$this->fail('test_removeExtra not yet implemented');
	}

	public function test_moveExtraAfter() {
		$this->fail('test_moveExtraAfter not yet implemented');
	}
	
	public function test_setShow() {
		$this->fail('test_setShow not yet implemented');
	}

	public function test_setInherits() {
		$this->fail('test_setInherits not yet implemented');
	}
	
	////////////////////////////////////////////////////////////////////////////

	private function createExtras() {
		foreach ($this->extra_data as $index => $extra) {
			$extra_uid = tx_newspaper::insertRows($this->concrete_extra_table, $extra);
	    	
	    	$abstract_uid = tx_newspaper_Extra::createExtraRecord($extra_uid, $this->concrete_extra_table);

	    	///	link extra to article
			tx_newspaper::insertRows(
				$this->extra2pagezone_table,
				array(
					'uid_local' => $this->uid,
					'uid_foreign' => $abstract_uid
				));
	    	
	    	/// set position of extra
	    	$row = array('position' => $this->extra_pos[$index]);
			tx_newspaper::updateRows($this->extra_table, 'uid = ' . $abstract_uid, $row);
		}	
	}
	
	private function removeExtras() {
		$rows = tx_newspaper::selectRows('uid_foreign', $this->extra2pagezone_table, 'uid_local = ' . $this->uid);
		foreach ($rows as $row) {
			$abstract_uid = $row['uid_foreign'];
			$extra = tx_newspaper::selectOneRow('extra_uid, extra_table', $this->extra_table, 'uid = ' . $abstract_uid);
			$concrete_uid = $extra['extra_uid'];
			$this->assertEquals($extra['extra_table'], $this->concrete_extra_table);
			
			tx_newspaper::deleteRows($this->extra_table, array($abstract_uid));
			tx_newspaper::deleteRows($this->extra2pagezone_table, 
									 "uid_foreign = $abstract_uid AND uid_local = " . $this->uid);
			tx_newspaper::deleteRows($this->concrete_extra_table, array($concrete_uid));
		}
	}
	


	private $bad_uid = 2000000000;			///< pagezone that does not exist
	private $pagezone = null;				///< the object
	private $source = null;
	private $uid = 1;
	
	private $extra_table = 'tx_newspaper_extra';
	private $concrete_extra_table = 'tx_newspaper_extra_image';
	private $extra2pagezone_table = 'tx_newspaper_pagezone_page_extras_mm';
	private $pagezone_table = 'tx_newspaper_pagezone';
	private $pagezone_page_table = 'tx_newspaper_pagezone_page';

	private $pagezone_page_data = array(
		'pid'		=> '2476',
		'tstamp'	=> '1233326263',
		'crdate'	=> '1232376462', 		  	
		'cruser_id'	=> '1',
		'sorting'	=> '256',
		'deleted'	=> '0',
		'pagezonetype_id' => '2',
		'pagezone_id' => 'X',
		'extras'	=> '3',
		'template_set' => '',
		'inherits_from' => '0'
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
	
	private $extra_pos = array(
		1024, 2048, 4096
	);
}
?>
