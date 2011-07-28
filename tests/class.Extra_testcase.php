<?php
/*
 * Created on Oct 27, 2008
 *
 * Author: helge
 */

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra_factory.php');
require_once(PATH_typo3conf . 'ext/newspaper/tests/class.tx_newspaper_database_testcase.php');

/// testsuite for all extras belonging to the newspaper extension
class test_Extra_testcase extends tx_newspaper_database_testcase {

	function setUp() {
		$this->old_page = $GLOBALS['TSFE']->page;
		$GLOBALS['TSFE']->page['uid'] = $this->plugin_page;
		$GLOBALS['TSFE']->page['tx_newspaper_associated_section'] = $this->section_uid;
        parent::setUp();
	}

	function tearDown() {
		parent::tearDown();
		$GLOBALS['TSFE']->page = $this->old_page;
		/// Make sure $_GET is clean
		unset($_GET['art']);
		unset($_GET['type']);
	}

	public function test_getExtraTable() {
		$this->assertEquals(tx_newspaper_Extra_Factory::getInstance()->getExtraTable(), 'tx_newspaper_extra');
	}

	public function test_nonexistentExtra() {
		$extra = tx_newspaper_Extra_Factory::getInstance()->create($this->bad_extra_uid);
        $this->assertTrue($extra instanceof ErrorExtra);
	}

    public function test_extraWithBrokenTable() {
        $extra = tx_newspaper_Extra_Factory::getInstance()->create(tx_newspaper_fixture::broken_extra_uid);
        $this->assertTrue($extra instanceof ErrorExtra);
    }

	public function test_createExtra() {
        $temp = array();
        foreach($this->extras_to_test as $extra_class) {
			$temp = new $extra_class();
			$this->assertTrue(is_object($temp));
			$this->assertTrue($temp instanceof tx_newspaper_Extra);
			$this->assertTrue($temp instanceof $extra_class);
			if ($this->attributes_to_test['modulename'][$extra_class])
				$this->assertEquals($temp->getModuleName(),
									$this->attributes_to_test['modulename'][$extra_class]);
		}
	}

	public function test_getAttributeUid() {
		foreach($this->fixture->getExtraUids() as $uid) {
			$temp = tx_newspaper_Extra_Factory::getInstance()->create($uid);
			$this->assertEquals($uid, $temp->getAttribute('uid'), 'uid passed in on object creation does not match with the attribute uid.');
		}
	}

	public function test_getAttribute() {
        $temp = array();
        foreach($this->fixture->getExtraUids() as $uid) {
            $temp[] = tx_newspaper_Extra_Factory::getInstance()->create($uid);
        }

        foreach($this->fixture->image_extra_data as $i => $value) {
            $this->assertEquals($this->fixture->image_extra_data[$i]['caption'], $temp[$i]->getAttribute('caption'));
        }

		$this->setExpectedException('tx_newspaper_WrongAttributeException');
		$temp[0]->getAttribute('es gibt mich nicht, schmeiss ne exception!');
	}

	public function test_setAttribute() {
		$temp = array();
        foreach($this->fixture->getExtraUids() as $uid) {
            $extra = tx_newspaper_Extra_Factory::getInstance()->create($uid);
            $time = time();
			$extra->setAttribute('crdate', $time);
			$this->assertEquals($extra->getAttribute('crdate'), $time);
        }
	}

	public function test_isRegisteredExtra() {
        $uid = $this->fixture->getExtraUid();
        $extra = tx_newspaper_Extra_Factory::getInstance()->create($uid);
        $this->assertTrue(tx_newspaper_Extra::isRegisteredExtra($extra));
	}

	public function test_registerExtra() {
		$uid = $this->fixture->getExtraUid();
        $extra = tx_newspaper_Extra_Factory::getInstance()->create($uid);
        tx_newspaper_Extra::registerExtra($extra);
        $this->assertTrue(tx_newspaper_Extra::isRegisteredExtra($extra));
	}

	public function test_render() {

        $this->skipTest('test not yet ready!'); return;

		/// set an article ID for article renderer extra
		$_GET['art'] = 1;
		foreach($this->extras_to_test as $extra_class) {
			$temp = new $extra_class(1);
			$temp->render();
			/// \todo test the output... how can i do that generically?
		}
		unset($_GET['art']);
	}

	public function test_storeReturnsEqualUids() {
        foreach($this->fixture->getExtraUids() as $uid) {
			$temp = tx_newspaper_Extra_Factory::getInstance()->create($uid);
			$uid_after_store = $temp->store();
			$this->assertEquals($uid_after_store, $temp->getUid(), "id after store ($uid_after_store) != original id ($uid)");
		}
	}

    const tested_attribute = 'crdate';
    public function test_storeDBEqualsMemory() {
        foreach($this->fixture->getExtraUids() as $uid) {
            $temp = tx_newspaper_Extra_Factory::getInstance()->create($uid);

            $temp->setAttribute(self::tested_attribute, time());
            $temp->store();

            $data = tx_newspaper::selectOneRow(
                '*', 'tx_newspaper_extra',
                "uid = $uid"
            );
            $this->assertEquals(
                $temp->getAttribute(self::tested_attribute), $data[self::tested_attribute],
                self::tested_attribute." has wrong value: " .  $data[self::tested_attribute] . " instead of " . $temp->getAttribute(self::tested_attribute)
            );
        }
    }

    /// create an empty extra and write it. verify it's been written.
    public function test_storeNewExtra() {
        $temp = new tx_newspaper_Extra_Image();
        $temp->setAttribute('title', 'teststring');
        $uid = $temp->store();
        $data = tx_newspaper::selectOneRow('*', $temp->getTable(), 'uid = ' . $uid);
        t3lib_div::debug(time());
        t3lib_div::debug($data);
        $this->assertEquals($data['title'], 'teststring');
        /// delete extra
        $GLOBALS['TYPO3_DB']->exec_DELETEquery($temp->getTable(), 'uid = ' . $uid);
    }

	public function test_relateExtra2Article() {
		$article_uid = 1;
		$article = new tx_newspaper_Article($article_uid);
		foreach($this->extras_to_test as $extra_class) {
			/// create a new extra, call relateExtra2Article() on a known article
			$extra = new $extra_class();
			$crdate = time();
			$extra->setAttribute('crdate', $crdate);
			$extra_uid = $extra->store();
			$abstract_uid = $article->relateExtra2Article($extra, $extra_uid, $article_uid);

			/// check that entry for Extra supertable has been written and is equal to new Extra
			$data = tx_newspaper::selectOneRow(
				'*',
				tx_newspaper_Extra_Factory::getExtraTable(),
				'extra_table = \'' . strtolower($extra_class) . '\' AND extra_uid = ' . intval($extra_uid)
			);
			$this->assertTrue(is_array($data));
			$this->assertTrue(sizeof($data) > 0);
			$extra_supertable_uid = $data['uid'];
			$extra_reborn = tx_newspaper_Extra_Factory::getInstance()->create($extra_supertable_uid);
			$this->assertTrue(is_a($extra_reborn, $extra_class));
			$this->assertEquals($extra_reborn->getAttribute('crdate'), $crdate);

			/// \todo check that $abstract_uid corresponds to entry in extra supertable

			/// check that MM-relation to the article has been written
			$data = tx_newspaper::selectOneRow(
				'*',
				tx_newspaper_Extra_Factory::getExtra2ArticleTable(),
				'uid_local = ' . $article_uid . ' AND uid_foreign = ' . intval($extra_supertable_uid)
			);
			$this->assertTrue(is_array($data));
			$this->assertTrue(sizeof($data) > 0);

			/// remove MM relation, superclass table entry and newly created extra
			tx_newspaper::deleteRows(
				tx_newspaper_Extra_Factory::getExtra2ArticleTable(),
				'uid_local = ' . $article_uid . ' AND uid_foreign = ' . intval($extra_supertable_uid),
				true
			);
			tx_newspaper::deleteRows(
				tx_newspaper_Extra_Factory::getExtraTable(),
				'uid = ' . $extra_supertable_uid,
				true
			);
			tx_newspaper::deleteRows($extra->getTable(), 'uid = ' . $extra_uid, true);

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
		tx_newspaper_Extra::createExtraRecord(
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

		tx_newspaper_Extra::createExtraRecord(
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

	public function test_getSearchFields() {
		$extra = new tx_newspaper_Extra_Image();
		$fields = $extra->getSearchFields();
		$this->assertContains('title', $fields);
		$this->assertContains('caption', $fields);
		$this->assertContains('kicker', $fields);

		$extra = new tx_newspaper_Extra_Bio();
		$fields = $extra->getSearchFields();
		$this->assertContains('author_name', $fields);
		$this->assertContains('bio_text', $fields);
	}

	public function test_getSearchResults() {
		$extra = new tx_newspaper_Extra_Image();
		$results = $extra->getSearchResults('Unit Test');

		$this->assertGreaterThanOrEqual(3, sizeof($results));
		foreach ($results as $result) {
			$this->assertTrue($result instanceof tx_newspaper_Extra_Image);
		}

		$extra = new tx_newspaper_extra_articlelist();
		$results = $extra->getSearchResults('Unit Test');

		$this->assertGreaterThanOrEqual(2, sizeof($results));
		foreach ($results as $result) {
			$this->assertTrue($result instanceof tx_newspaper_extra_articlelist);
			$this->assertThat(
				$result->getAttribute('short_description'),
				$this->stringContains('Unit Test', false)
			);
		}

	}

	public function test_duplicateReturnsSameClass() {
		foreach($this->extras_to_test as $extra_class) {

            $temp = self::generateExtraWithRandomCrdate($extra_class);
			$that = $temp->duplicate();

            $this->assertTrue(
                $that instanceof $extra_class,
                "Duplicated extra is not of class $extra_class, but " . get_class($that)
            );
		}
	}

    public function test_duplicateGeneratesSameAttributes() {
        foreach($this->extras_to_test as $extra_class) {

            $temp = self::generateExtraWithRandomCrdate($extra_class);
            $that = $temp->duplicate();

            foreach (tx_newspaper::getAttributes($temp) as $attribute) {
                $this->assertTrue(
                    in_array($attribute, tx_newspaper::getAttributes($that)),
                    "Attribute $attribute is not in attributes for duplicated extra"
                );
            }
        }
    }

    public function test_duplicateGeneratesEqualAttributes() {
        foreach($this->extras_to_test as $extra_class) {

            $temp = self::generateExtraWithRandomCrdate($extra_class);
            $that = $temp->duplicate();

            foreach (tx_newspaper::getAttributes($temp) as $attribute) {
                $this->assertEquals(
                    $temp->getAttribute($attribute), $that->getAttribute($attribute),
                    "Attribute $attribute original: " . $temp->getAttribute($attribute) . ', copied: ' . $that->getAttribute($attribute)
                );
            }
        }
    }

    private static function generateExtraWithRandomCrdate($extra_class) {
        $temp = new $extra_class(1);
        $time = time();
        $temp->setAttribute('crdate', $time);
        return $temp;
    }


    public function test_GetAttributeShortDescription() {
		foreach (tx_newspaper_Extra::getRegisteredExtras() as $registeredExtra) {
			$table = $registeredExtra->getTable();
			if (strpos($table, 'tx_newspaper_extra') !== false) {
				// check newspaper (core) extras (db tables missing for other extras)

				$registeredExtra->store(); // save to database so attributes can be read

				//re-read extra
				$extra = new $table($registeredExtra->getUid());

				try {
			        $extra->getAttribute('short_description');
				} catch (tx_newspaper_Exception $e) {
					$this->fail('Could not get mandatory attribute short_description for core extra ' . $extra->getTitle());
				}
			} else {
				// simply check tca for other extras
				t3lib_div::loadTCA($table);
				if (!isset($GLOBALS['TCA'][$table]['columns']['short_description'])) {
					$this->fail('Could not get mandatory attribute short_description for extension extra ' . $extra->getTitle());
				}
			}
		}
	}
	public function test_GetAttributeTemplate() {
		foreach (tx_newspaper_Extra::getRegisteredExtras() as $registeredExtra) {
			$table = $registeredExtra->getTable();
			if (strpos($table, 'tx_newspaper_extra') !== false) {
				// check newspaper (core) extras (db tables missing for other extras)

				$registeredExtra->store(); // save to database so attributes can be read

				//re-read extra
				$extra = new $table($registeredExtra->getUid());

				try {
			        $extra->getAttribute('template');
				} catch (tx_newspaper_Exception $e) {
					$this->fail('Could not get mandatory attribute template for extra ' . $extra->getTitle());
				}
			} else {
				// simply check tca for other extras
				t3lib_div::loadTCA($table);
				if (!isset($GLOBALS['TCA'][$table]['columns']['template'])) {
					$this->fail('Could not get mandatory attribute template for extension extra ' . $extra->getTitle());
				}
			}
		}
	}

	/// Section which contains the objects to be tested
	private $section_uid = 1;
	private $bad_extra_uid = 2000000000;	///< extra that does not exist
	/// Extra classes that should be subjected to all tests
	private $extras_to_test = array(
		'tx_newspaper_Extra_Image',
		'tx_newspaper_Extra_SectionList',
	);
	/// Extra classes that will have additional tests run on them
	private $extras_to_test_additionally = array(
		'tx_newspaper_PageZone_Page',
		'tx_newspaper_Article',
		// ...
	);
	/// Attributes to test in test_createExtra() and their expected values
	private $attributes_to_test = array(
		'title' => array(
			'tx_newspaper_Extra_Image' => 'Unit Test - Image Title 1',
		),
		'modulename' => array(
			'tx_newspaper_Extra_Image' => 'np_image',
			'tx_newspaper_Extra_SectionList' => 'np_sect_ls',
		),
	);

	/// Helper for common setUp Tasks
	private $testHelper = null;
	/// Table which stores the Extra superclass
	private $extras_table = 'tx_newspaper_extra';
	/// Extra which is used to test createExtraRecord()
	private $extra_table_to_create_superobject_for = 'tx_newspaper_article';
	/// UID of concrete record to test in createExtraRecord()
	private $extra_uid_to_create_superobject_for = 1;


}
?>
