<?php
/*
 * Created on Oct 27, 2008
 *
 * Author: helge
 */

require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_extra_factory.php');

/// testsuite for all extras belonging to the newspaper extension
class test_Extra_testcase extends tx_phpunit_testcase {

	function setUp() {
		$this->old_page = $GLOBALS['TSFE']->page;
		$GLOBALS['TSFE']->page['uid'] = $this->plugin_page;
		$GLOBALS['TSFE']->page['tx_newspaper_associated_section'] = $this->section_uid;
	}

	function tearDown() {
		$GLOBALS['TSFE']->page = $this->old_page;
		/// Make sure $_GET is clean
		unset($_GET['art']);
		unset($_GET['type']);		
	}

	public function test_getExtraTable() {
		$this->assertEquals(tx_newspaper_Extra_Factory::getInstance()->getExtraTable(), 'tx_newspaper_extra');	
	}

	public function test_nonexistentExtra() {
		$this->setExpectedException('tx_newspaper_DBException');
		tx_newspaper_Extra_Factory::getInstance()->create($this->bad_extra_uid);
	}

	public function test_createExtra() {
		foreach($this->extras_to_test as $extra_class) {
			$temp = new $extra_class(1);
			$this->assertTrue(is_object($temp));
			$this->assertTrue($temp instanceof tx_newspaper_Extra);
			$this->assertTrue($temp instanceof $extra_class);
			if ($this->attributes_to_test['title'][$extra_class])
				$this->assertEquals($temp->getTitle(), 
								    $this->attributes_to_test['title'][$extra_class]);
			if ($this->attributes_to_test['modulename'][$extra_class])
				$this->assertEquals($temp->getModuleName(),
									$this->attributes_to_test['modulename'][$extra_class]);
		}
	}
	
	public function test_getAttribute() {
		foreach($this->extras_to_test as $extra_class) {
			$temp = new $extra_class(1);
			$this->assertEquals($temp->getAttribute('uid'), 1);
		}

		$this->setExpectedException('tx_newspaper_WrongAttributeException');
		$temp->getAttribute('es gibt mich nicht, schmeiss ne exception!');
	}

	public function test_setAttribute() {
		$this->setExpectedException('tx_newspaper_NotYetImplementedException');
		foreach($this->extras_to_test as $extra_class) {
			$temp = new $extra_class(1);
			$temp->setAttribute('uid', 100);
			$this->assertEquals($temp->getAttribute('uid'), 100);
		}

		$this->setExpectedException('tx_newspaper_WrongAttributeException');
		$temp->setAttribute('es gibt mich nicht, schmeiss ne exception!', 1);
	}

	public function test_getExtraPid() {
		foreach($this->extras_to_test as $extra_class) {
			$temp = new $extra_class(1);
			$temp->getExtraPid();
		}
	}	

	public function test_isRegisteredExtra() {
		foreach($this->extras_to_test as $extra_class) {
			$temp = new $extra_class(1);
			$this->assertTrue(tx_newspaper_ExtraImpl::isRegisteredExtra($temp));
		}
	}	

	public function test_registerExtra() {
		foreach($this->extras_to_test as $extra_class) {
			$temp = new $extra_class(1);
			tx_newspaper_ExtraImpl::registerExtra($temp);
			$this->assertTrue(tx_newspaper_ExtraImpl::isRegisteredExtra($temp));
		}
	}	

	public function test_render() {
		foreach($this->extras_to_test as $extra_class) {
			$temp = new $extra_class(1);
			$temp->render();
			/// \todo test the output... how can i do that generically?
		}
	}	

	public function test_store() {
		foreach($this->extras_to_test as $extra_class) {
			$temp = new $extra_class(1);
			$temp->store();
			/// \todo check that record in DB equals data in memory
			/// \todo change an attribute, store and check
			/// \todo create an empty extra and write it. verify it's been written.
		}
	}	
	
	public function test_getTable() {
		foreach(array_merge($this->extras_to_test, 
							$this->extras_to_test_additionally) as $extra_class) {
			$temp = new $extra_class(1);
			$this->assertEquals(strtolower($extra_class), $temp->getTable());
		}
	}
	
	public function test_createExtraRecord() {
		/// test whether the function runs at all
		tx_newspaper_ExtraImpl::createExtraRecord(
			$this->extra_uid_to_create_superobject_for, 
			$this->extra_table_to_create_superobject_for
		);

		/// check if the Extra record is present
		$row = tx_newspaper::selectOneRow(
			'uid',
			$this->extras_table,
			'extra_table = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->extra_table_to_create_superobject_for, $this->extras_table) .
			' AND extra_uid = ' . intval($this->extra_uid_to_create_superobject_for)
		);
		$this->assertTrue($row['uid'] > 0);
		
		/// delete the record from the extra table and check it it really is created anew
		$GLOBALS['TYPO3_DB']->exec_DELETEquery($this->extras_table,
			'extra_table = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->extra_table_to_create_superobject_for, $this->extras_table) .
			' AND extra_uid = ' . intval($this->extra_uid_to_create_superobject_for));
		/// \todo if i were pedantic, i'd check wheter deletion has really succeeded...

		tx_newspaper_ExtraImpl::createExtraRecord(
			$this->extra_uid_to_create_superobject_for, 
			$this->extra_table_to_create_superobject_for
		);

		/// check if the Extra record is present
		$row = tx_newspaper::selectOneRow(
			'uid',
			$this->extras_table,
			'extra_table = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->extra_table_to_create_superobject_for, $this->extras_table) .
			' AND extra_uid = ' . intval($this->extra_uid_to_create_superobject_for)
		);
		$this->assertTrue($row['uid'] > 0);
		
		/// \todo check if all fields are consistent
	}
	
	/// Section which contains the objects to be tested
	private $section_uid = 1;	
	private $bad_extra_uid = 2000000000;	///< extra that does not exist
	/// Extra classes that should be subjected to all tests
	private $extras_to_test = array(
		'tx_newspaper_Extra_ArticleRenderer',
		'tx_newspaper_Extra_Image',
		'tx_newspaper_Extra_SectionList',
	);
	/// Extra classes that will have additional tests run on them
	private $extras_to_test_additionally = array(
//		'tx_newspaper_PageZone',
		'tx_newspaper_PageZone_Page',
		'tx_newspaper_PageZone_Article',
		// ...
	);
	/// Attributes to test in test_createExtra() and their expected values
	private $attributes_to_test = array(
		'title' => array(
			'tx_newspaper_Extra_ArticleRenderer' => 'ArticleRenderer',
			'tx_newspaper_Extra_Image' => 'Image',
			'tx_newspaper_Extra_SectionList' => 'SectionList',
		),
		'modulename' => array(
			'tx_newspaper_Extra_ArticleRenderer' => 'np_artrend',
			'tx_newspaper_Extra_Image' => 'np_image',
			'tx_newspaper_Extra_SectionList' => 'np_sect_ls',
		),
	);
	/// Table which stores the Extra superclass
	private $extras_table = 'tx_newspaper_extra';
	/// Extra which is used to test createExtraRecord()
	private $extra_table_to_create_superobject_for = 'tx_newspaper_article';
	/// UID of concrete record to test in createExtraRecord()
	private $extra_uid_to_create_superobject_for = 1;
	
	
}
?>
