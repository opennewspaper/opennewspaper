<?php
/*
 * Created on Oct 27, 2008
 *
 * Author: helge
 */

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra_factory.php');

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
		foreach($this->extras_to_test as $extra_class) {
			$temp = new $extra_class(1);
			$time = time();
			$temp->setAttribute('crdate', $time);
			$this->assertEquals($temp->getAttribute('crdate'), $time);
		}
	}

/* \todo Helge: still needed? getExtraPid() removed (->tx_newspaper_Sysfolder -> getPid(obj))
	public function test_getExtraPid() {
		foreach($this->extras_to_test as $extra_class) {
			$temp = new $extra_class(1);
			$temp->getExtraPid();
		}
	}	
*/
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
		/// set an article ID for article renderer extra
		$_GET['art'] = 1;
		foreach($this->extras_to_test as $extra_class) {
			$temp = new $extra_class(1);
			$temp->render();
			/// \todo test the output... how can i do that generically?
		}
		unset($_GET['art']);
	}	

	public function test_store() {
		foreach($this->extras_to_test as $extra_class) {
			$temp = new $extra_class(1);
			$uid = $temp->store();
			$this->assertEquals($uid, $temp->getUid());

			/// check that record in DB equals data in memory
			$data = tx_newspaper::selectOneRow(
				'*', $temp->getTable(), 'uid = ' . $temp->getUid());
			foreach ($data as $key => $value) {
				$this->assertEquals($temp->getAttribute($key), $value);
			}
		
			/// change an attribute, store and check
			$time = time();
			$temp->setAttribute('tstamp', $time);
			$uid = $temp->store();
			$this->assertEquals($uid, $temp->getUid());
			$data = tx_newspaper::selectOneRow(
				'*', $temp->getTable(), 'uid = ' . $temp->getUid());
			$this->assertEquals($data['tstamp'], $time);
		
			/// create an empty extra and write it. verify it's been written.
			$temp = new $extra_class();
			$temp->setAttribute('tstamp', $time);
			$uid = $temp->store();
			$data = tx_newspaper::selectOneRow('*', $temp->getTable(), 'uid = ' . $uid);
			$this->assertEquals($data['tstamp'], $time);
			
			/// delete extra
			$GLOBALS['TYPO3_DB']->exec_DELETEquery($temp->getTable(), 'uid = ' . $uid);
			
		}
	}	

	public function test_relateExtra2Article() {
		$article_uid = 1;
		$article = new tx_newspaper_ArticleImpl($article_uid);
		foreach($this->extras_to_test as $extra_class) {
			/// create a new extra, call relateExtra2Article() on a known article 
			$extra = new $extra_class();
			$crdate = time();
			$extra->setAttribute('crdate', $crdate);
			$extra_uid = $extra->store();
			$abstract_uid = tx_newspaper_ArticleImpl::relateExtra2Article($extra_class, $extra_uid, $article_uid);

			/// check that entry for Extra supertable has been written and is equal to new Extra
			$data = tx_newspaper::selectOneRow(
				'*', 
				tx_newspaper_Extra_Factory::getExtraTable(),
				'extra_table = \'' . strtolower($extra_class) . '\' AND extra_uid = ' . intval($extra_uid)
			);
			$this->assertTrue(is_array($data));
			$this->assertTrue(sizeof($data) > 0);
			$extra_supertable_uid = $data['uid'];
			$extra_reborn = tx_newspaper_Extra_Factory::create($extra_supertable_uid);
			$this->assertTrue(is_a($extra_reborn, $extra_class));
			$this->assertEquals($extra_reborn->getAttribute('crdate'), $crdate);
			
			/// \todo check that $abstract_uid correspnds to entry in extra supertable
			
			/// check that MM-relation to the article has been written
			$data = tx_newspaper::selectOneRow(
				'*', 
				tx_newspaper_Extra_Factory::getExtra2ArticleTable(),
				'uid_local = ' . $article_uid . ' AND uid_foreign = ' . intval($extra_supertable_uid)
			);
			$this->assertTrue(is_array($data));
			$this->assertTrue(sizeof($data) > 0);
			
			/// remove MM relation, superclass table entry and newly created extra
			$query = $GLOBALS['TYPO3_DB']->DELETEquery(
				tx_newspaper_Extra_Factory::getExtra2ArticleTable(),
				'uid_local = ' . $article_uid . ' AND uid_foreign = ' . intval($extra_uid)
			);
			t3lib_div::debug($query);
			$GLOBALS['TYPO3_DB']->exec_DELETEquery($query);
			$GLOBALS['TYPO3_DB']->exec_DELETEquery(
				tx_newspaper_Extra_Factory::getExtraTable(),
				'uid = ' . $extra_supertable_uid
			);
			$GLOBALS['TYPO3_DB']->exec_DELETEquery($extra->getTable(), 'uid = ' . $extra_uid);
			
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
