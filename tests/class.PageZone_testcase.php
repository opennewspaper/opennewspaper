<?php

/*
 * Created on Oct 27, 2008
 *
 * Author: helge
 */

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_pagezone.php');
require_once(PATH_typo3conf . 'ext/newspaper/tests/class.fixture.php');
require_once(PATH_typo3conf . 'ext/newspaper/tests/class.tx_newspaper_database_testcase.php');

/// testsuite for class tx_newspaper_pagezone
class test_PageZone_testcase extends tx_newspaper_database_testcase {

	function setUp() {
		parent::setUp();
		if (false) {
			$this->uid = tx_newspaper::insertRows($this->pagezone_page_table, $this->pagezone_page_data);
		} else {
			$query = $GLOBALS['TYPO3_DB']->INSERTquery($this->pagezone_page_table, $this->pagezone_page_data);
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
			if (!$res) die("$query failed!");
	        
		    $this->uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
		}

		$this->pagezone = new tx_newspaper_PageZone_Page($this->uid);
		$this->pagezone_uid = $this->pagezone->getAbstractUid();

//		$this->createExtras();

//		$this->source = new tx_newspaper_DBSource();
	}

	public function test_createPageZone() {
		$temp = tx_newspaper_PageZone_Factory::getInstance()->create($this->pagezone->getPageZoneUID());
		$this->assertTrue(is_object($temp));
		$this->assertTrue($temp instanceof tx_newspaper_PageZone);
	}
	
	public function test_Title() {
		$this->assertEquals('Page zone', $this->pagezone->getTitle());
	}
	
	public function test_modulename() {
		$this->assertEquals($this->pagezone->getModuleName(), 'np_pagezone_page');
	}

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
			$this->assertEquals('Page zone type', $pzt->getTitle());
			
			$pzt->setAttribute('uid', 0);
			$this->assertEquals($pzt->getAttribute('uid'), 0);
		}
	}
	
	/**	setAttribute is tested without calling getAttribute() first
	 *  WrongAttributeException is tested */
	public function test_PageZoneType_setAttribute() {
		$rows = tx_newspaper_PageZoneType::getAvailablePageZoneTypes();
		foreach ($rows as $pzt) {
			
			$pzt->setAttribute('uid', 0);
			$this->assertEquals($pzt->getAttribute('uid'), 0);

			$this->setExpectedException('tx_newspaper_WrongAttributeException');
			$pzt->getAttribute('Gibts nicht');
		}	
	}
	
	/// test NotYetImplementedException
	public function test_PageZoneType_store() {
		$rows = tx_newspaper_PageZoneType::getAvailablePageZoneTypes();
		foreach ($rows as $pzt) {
			$this->setExpectedException('tx_newspaper_NotYetImplementedException');
			$pzt->store();
		}
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

    public function test_getParentPageReturnsPage() {
        foreach ($this->fixture->getPageZones() as $pagezone) {
            $parent_page = $pagezone->getParentPage();
            $this->assertTrue($parent_page instanceof tx_newspaper_Page,
                              'getParentPage() is not a Page: ' .
                              print_r($parent_page, 1));
        }
    }

    public function test_getParentPageFoundInFixture() {
        foreach ($this->fixture->getPageZones() as $pagezone) {

            $parent_page = $pagezone->getParentPage();

            $found = false;
            foreach ($this->fixture->getPages() as $page) {
                if ($parent_page->getUid() == $page->getUid()) $found = true;
            }
            $this->assertTrue($found,
                              'Parent page of PageZone ' . $pagezone->getUid() .
                              ' (abstract PageZone '.$pagezone->getAbstractUid() . ')' .
                              ' not found in array of pages: ' .
                              print_r($this->fixture->getPages(), 1));
        }
    }

    public function test_setParentPage() {
        foreach ($this->fixture->getPages() as $page) {
            $this->pagezone->setParentPage($page);
            $this->assertEquals($this->pagezone->getParentPage()->getUid(),
                                $page->getUid(),
                                'getParentPage() [' . $this->pagezone->getParentPage()->getUid() . ']' .
                                ' != $page [' . $page->getUid() .']');
        }
    }

    ///	singularly created page zone has no parent
    public function test_getParentForPlacementWithoutParent() {
        $this->assertEquals($this->pagezone->getParentForPlacement(), null);
    }

    /** @todo create a pagezone in the fixture which explicitly does not inherit
     */
    public function test_getParentForPlacementWithoutInheritance() {
        foreach ($this->fixture->getPageZones() as $pagezone) {
            if ($pagezone->getAttribute('inherits_from') < 0) {
                $parent = $pagezone->getParentForPlacement();
                $this->assertEquals(
                    $parent, null,
                    'PageZone ' . $pagezone->getUid() .': ' .
                    'inheritance mode is set to no inheritance, but a parent (' .
                    print_r($parent, 1) . ') is returned. '
                );
            }
        }
        $this->skipTest("No pagezones without inheritance set up yet");
    }

    /** @todo create a pagezone in the fixture which inherits from a specified pagezone
     */
    public function test_getParentForPlacementExplicitPagezone() {
        foreach ($this->fixture->getPageZones() as $pagezone) {
            if ($pagezone->getAttribute('inherits_from') > 0) {
                $parent = $pagezone->getParentForPlacement();
                $this->assertTrue(
                    $parent instanceof tx_newspaper_PageZone,
                    'PageZone object expected, but ' .print_r($parent, 1) . ') is returned. '
                );
                $this->assertEquals(
                    $parent->getUid(), $pagezone->getAttribute('inherits_from'),
                    'PageZone ' . $pagezone->getUid() .': ' .
                    'explicitly inherits from PageZone ' . $pagezone->getAttribute('inherits_from') .
                    ' but PageZone ' . $parent->getUid() . ' is returned. '
                );
            }
        }
        $this->skipTest("No pagezones with manual inheritance set up yet");
    }

    public function test_getParentForPlacementInheritFromSameType() {
        foreach ($this->fixture->getPageZones() as $pagezone) {
            if ($pagezone->getAttribute('inherits_from') == 0) {
                $parent = $pagezone->getParentForPlacement();
                //	Normal inheritance mode: go up in the section tree
                if ($parent) {
                    $this->assertTrue($parent instanceof tx_newspaper_PageZone,
                                      'PageZone object expected, but ' .
                                      print_r($parent, 1) . ') is returned. ');
                    $this->assertTrue($pagezone->getUid() != $parent->getUid(),
                                     'Pagezone ' . $pagezone->getUid() . ' has itself as parent. ');
                    $this->assertTrue($parent->getParentPage()->getParentSection()->getUid() !=
                                      $pagezone->getParentPage()->getParentSection()->getUid(),
                                      'Pagezone ' . $pagezone->getUid() . ' has a parent in the same Section (' .
                                      $pagezone->getParentPage()->getParentSection()->getUid() .
                                      '), but should not. ');
                    if (1) {
                        t3lib_div::debug($pagezone->__toString() . ': parent is ' .
                                         $parent->__toString());
                    }
                } else {
                    if (0) {
                        t3lib_div::debug($pagezone->__toString() . ': no parent');
                    }
                }
            }
        }
        $this->skipTest("No pagezones with automatic inheritance set up yet");
    }


    public function test_getInheritanceHierarchyUp_SinglePagezone() {
        $hierarchy = $this->pagezone->getInheritanceHierarchyUp();
        $this->assertEquals(1, sizeof($hierarchy),
                            $this->pagezone->__toString() . ' has no parents, inheritance hierarchy must have exactly 1 element (including itself). ');
        $this->assertTrue($hierarchy[0] instanceof tx_newspaper_PageZone,
                          $hierarchy[0]->__toString() . ' is not a PageZone');
        $this->assertEquals($this->pagezone->getUid(), $hierarchy[0]->getUid(),
                            'First element in hierarchy does not equal original PageZone');

        $this->assertEquals(0, sizeof($this->pagezone->getInheritanceHierarchyUp(false)),
                            $this->pagezone->__toString() . ' has no parents, inheritance hierarchy must be empty (not including itself). ');
    }

    public function test_getInheritanceHierarchyUpWithSelf() {
        foreach ($this->fixture->getPageZones() as $pagezone) {
            $hierarchy = $pagezone->getInheritanceHierarchyUp();
            if ($pagezone->getParentForPlacement()) {
                $this->assertGreaterThan(1, sizeof($hierarchy),
                                  $pagezone->__toString() . ' has parents, inheritance hierarchy must be bigger than 1 element (including itself). ');
            } else {
                $this->assertEquals(1, sizeof($hierarchy),
                                  $pagezone->__toString() . ' has no parents, inheritance hierarchy must have exactly 1 element (including itself). ');
            }

            $this->checkAllElementsArePagezones($hierarchy);
        }
    }

    public function test_getInheritanceHierarchyUpWithoutSelf() {
        foreach ($this->fixture->getPageZones() as $pagezone) {
            /// Same thing, not including the current PageZone in the hierarchy
            $hierarchy = $pagezone->getInheritanceHierarchyUp(false);
            if ($pagezone->getParentForPlacement()) {
                $this->assertGreaterThan(0, sizeof($hierarchy),
                                  $pagezone->__toString() . ' has parents, inheritance hierarchy must have elements (not including itself). ');
                $this->checkAllElementsArePagezones($hierarchy);
            } else {
                $this->assertEquals(0, sizeof($hierarchy),
                                  $pagezone->__toString() . ' has no parents, inheritance hierarchy must be empty (not including itself). ');
            }
        }
    }


    private function checkAllElementsArePagezones($hierarchy) {
        foreach ($hierarchy as $element) {
            $this->assertTrue($element instanceof tx_newspaper_PageZone,
                              $element->__toString() . ' is not a PageZone');
        }
    }

    public function test_Attribute() {
        $this->pagezone->setAttribute('crdate', 1);
        $this->assertEquals($this->pagezone->getAttribute('crdate'), 1);
    }

	public function test_clone() {
		$cloned = clone $this->pagezone;
		$this->assertEquals($cloned->getAttribute('uid'), 0);
		$this->assertEquals($cloned->getUid(), 0);
		$this->assertEquals($cloned->getAttribute('crdate'), time());
		$this->assertEquals($cloned->getAttribute('tstamp'), time());
	}
	
	////////////////////////////////////////////////////////////////////////////
	//	still a lot of work to be done here
	////////////////////////////////////////////////////////////////////////////

	/// \todo finish test
	public function test_storeEqualAttributes() {
		$this->pagezone->store();
        $uid = $this->pagezone->getUid();
        $record = tx_newspaper::selectOneRow('*', 'tx_newspaper_pagezone_page', "uid = $uid");

        $this->checkAttributesAreEqualToRecord($record);

    }

    private function checkAttributesAreEqualToRecord(array $record) {
        foreach ($record as $attribute => $value) {
            $this->assertEquals(
                $this->pagezone->getAttribute($attribute), $value,
                "Attribute $attribute: stored as $value, in memory as " . $this->pagezone->getAttribute($attribute)
            );
        }
    }

    public function test_storeEqualAbstractAttributes() {
        $this->pagezone->store();

        $abstract_uid = $this->pagezone->getAbstractUid();
        $abstract_record = tx_newspaper::selectOneRow('*', 'tx_newspaper_pagezone', "uid = $abstract_uid");

        $this->checkAttributesAreEqualToRecord($abstract_record);
    }

    public function test_store() {
        $this->pagezone->store();

        /// \todo check that record in DB equals data in memory
        /// \todo change an attribute, store and check
        /// \todo create an empty pagezone and write it. verify it's been written.
        /// \see ArticleImpl_testcase
        $this->skipTest('PageZone->store() not yet implemented. Requirements not known yet.');
    }


	public function test_render() {
		$this->fail('test_render not yet implemented');
		t3lib_div::debug($this->pagezone->render());
	}
	
	public function test_getAbstractUid() {
		/** This test seems a bit redundant because it checks the return value
		 *  of getAbstractUid() against the return value of getAbstractUid().
		 */ 
		$this->assertEquals($this->pagezone->getAbstractUid(), $this->pagezone_uid);
		/// Now we get real.
		$pagezone = tx_newspaper_PageZone_Factory::getInstance()->create($this->pagezone->getAbstractUid());
		$this->assertEquals($this->pagezone->getUid(), $pagezone->getUid());
	}

	public function test_insertExtraAfter() {
		foreach ($this->fixture->getPageZones() as $pagezone) {
#		$pagezone = array_pop($this->fixture->getPageZones()); {
			$old_extras = $pagezone->getExtras();
			foreach ($old_extras as $extra_after_which) {
				t3lib_div::debug("inserting after $extra_after_which");
				$i = 0;
				foreach ($this->extra_abstract_uids as $uid) {
					$i++;
					$new_extra = tx_newspaper_Extra_Factory::getInstance()->create($uid);
					$new_extra->setAttribute('title', "Inserted ${i}th");
					$pagezone->insertExtraAfter($new_extra, $extra_after_which->getOriginUid());
				}
			}

			$this->assertEquals(
				sizeof($pagezone->getExtras()),
				sizeof($old_extras)*(sizeof($this->extra_abstract_uids)+1),
				'There should be ' . sizeof($this->extra_abstract_uids) . ' new Extras after each of the ' .
				sizeof($old_extras) . ' original Extras, so PageZone ' . $pagezone . ' should now have ' .
				sizeof($old_extras)*(sizeof($this->extra_abstract_uids)+1) . ' Extras. Actually the number is ' .
				sizeof($pagezone->getExtras()) . '. '
			);

			$row = tx_newspaper::selectOneRow(
				'COUNT(*) AS num',
				$pagezone->getExtra2PagezoneTable(),
				'uid_local = ' . $pagezone->getUid()
			);
			$this->assertEquals(
				intval($row['num']),
				sizeof($old_extras)*(sizeof($this->extra_abstract_uids)+1),
				'Entries in ' . $pagezone->getExtra2PagezoneTable() . ' not written correctly. ' .
				'There should be ' . sizeof($old_extras)*(sizeof($this->extra_abstract_uids)+1) .
				' where uid_local = ' . $pagezone->getUid() . ', but actually ' . $row['num'] . ' are there.'
			);

			$this->checkPageZoneOrder($pagezone);

			/// Make sure the Extras are inserted on inheriting PageZones.
			foreach ($pagezone->getInheritanceHierarchyDown(false) as $sub_pagezone) {
				t3lib_div::debug($sub_pagezone.'');
				$this->assertEquals(
					sizeof($sub_pagezone->getExtras()),
					sizeof($old_extras)*(sizeof($this->extra_abstract_uids)+1),
					'There should be ' . sizeof($this->extra_abstract_uids) . ' new Extras after each of the ' .
					sizeof($old_extras) . ' original Extras, so inheriting PageZone ' . $sub_pagezone . ' should now have ' .
					sizeof($old_extras)*(sizeof($this->extra_abstract_uids)+1) . ' Extras. Actually the number is ' .
					sizeof($sub_pagezone->getExtras()) . '. '
				);

				$row = tx_newspaper::selectOneRow(
					'COUNT(*) AS num',
					$sub_pagezone->getExtra2PagezoneTable(),
					'uid_local = ' . $sub_pagezone->getUid()
				);
				$this->assertEquals(
					intval($row['num']),
					sizeof($old_extras)*(sizeof($this->extra_abstract_uids)+1),
					'Entries in ' . $sub_pagezone->getExtra2PagezoneTable() . ' not written correctly. ' .
					'There should be ' . sizeof($old_extras)*(sizeof($this->extra_abstract_uids)+1) .
					' but only ' . $row['num'] . ' are there.'
				);

				$this->checkPageZoneOrder($sub_pagezone, 'Order of Extras in inherited PageZone ' . $sub_pagezone . ' wrong. ');
			}
		}
	}

	public function test_getExtraOrigin() {
		foreach ($this->fixture->getPageZones() as $pagezone) {
			$hierarchy_root = array_pop($pagezone->getInheritanceHierarchyUp());
        	$some_origin_extra = array_pop($hierarchy_root->getExtras());
			foreach($pagezone->getExtras() as $extra) {
#				t3lib_div::debug(
					$pagezone->getExtraOrigin($extra)
#				)
				;
#				t3lib_div::debug($pagezone->getExtraOriginAsString($extra));
				$extra->setAttribute('origin_uid', $some_origin_extra->getUid());
#				t3lib_div::debug($pagezone->getExtraOriginAsString($extra));
			}
		}

		$this->fail('test_getExtraOrigin() not yet implemented.');
	}

	public function test_removeExtra() {
		foreach ($this->fixture->getPageZones() as $pagezone) {
			$old_extras = $pagezone->getExtras();

			foreach ($old_extras as $extra_to_remove) {
				$this->assertTrue(
					$pagezone->removeExtra($extra_to_remove),
					'Extra ' . $extra_to_remove . ' apparently wasn\'t on PageZone ' .
					$pagezone . ' in the first place. '
				);
				$found = false;
				foreach ($pagezone->getExtras() as $extra_still_there) {
					if ($extra_to_remove->getExtraUid() == $extra_still_there->getExtraUid()) {
						$this->fail($extra_to_remove . ' still on PageZone ' . $pagezone);
					}
				}
				$row = tx_newspaper::selectOneRow(
					'COUNT(*) AS num', 
					$pagezone->getExtra2PagezoneTable(),
					'uid_local = ' . $pagezone->getUid() . 
					' AND uid_foreign = ' . $extra_to_remove->getExtraUid()
				);
				$this->assertEquals(intval($row['num']), 0,
					'Still ' . $row['num'] . ' records linking Extra '. $extra_to_remove .
					' to PageZone ' . $pagezone . ' in table ' . $pagezone->getExtra2PagezoneTable());
			}
		}
	}

	public function test_moveExtraAfter() {
		foreach ($this->fixture->getPageZones() as $pagezone) {
    		$extras = $pagezone->getExtras();
			if (sizeof($extras) < 2) {
				$this->fail("fixture error: not enough extras on page zone to run test");
			}
			$this->assertTrue($extras[0] instanceof tx_newspaper_Extra);
			$this->assertTrue($extras[1] instanceof tx_newspaper_Extra);
			$pagezone->moveExtraAfter($extras[0], $extras[1]->getOriginUid());
			$new_extras = $pagezone->getExtras();
			//	find $extra[0] and $extra[1] in $new_extras
			for ($i = 0; $i < sizeof($new_extras); $i++) {
				if ($new_extras[$i]->getAttribute('title') == $extras[0]->getAttribute('title')) {
					$after_index = $i;
				}
				if ($new_extras[$i]->getAttribute('title') == $extras[1]->getAttribute('title')) {
					$before_index = $i;
				}
			}
			$this->assertEquals($after_index, $before_index+1);
		}
		/// \todo instantiate pagezone from DB and check it still works
	}
	
	public function test_setInherits() {
		foreach ($this->fixture->getPageZones() as $pagezone) {
#			t3lib_div::debug($pagezone->getInheritanceHierarchyUp());
		}
		$this->fail('test_setInherits not yet implemented');
	}
	
	public function test_getInheritanceHierarchyDown() {
		foreach ($this->fixture->getPageZones() as $pagezone) {
			$hierarchy = $pagezone->getInheritanceHierarchyDown(false);

			foreach ($hierarchy as $sub_pagezone) {
				$this->assertTrue(
					$sub_pagezone instanceof tx_newspaper_PageZone,
					$sub_pagezone . ' is not a PageZone. '
				);
				$parent_pagezones = $sub_pagezone->getInheritanceHierarchyUp();
				$found = false;
				foreach ($parent_pagezones as $pagezone_to_check) {
					if ($pagezone->getUid() == $pagezone_to_check->getUid())
						$found = true;
				}
				$this->assertTrue($found,
					'PageZone ' . $pagezone . ' not found in parents of ' . $sub_pagezone .
					', which is listed as a descendant of ' . $pagezone);
			}
		}
	}

	
	public function test_copyExtrasFrom() {
		foreach ($this->fixture->getPageZones() as $pagezone) {
#			t3lib_div::debug($pagezone->getInheritanceHierarchyUp());
		}
		$this->fail('test_copyExtrasFrom not yet implemented');
	}

	////////////////////////////////////////////////////////////////////////////

	/** Make sure the order is correct. \n
	 *  Expected order: \n
	 *  'Unit Test - Image Title 1', 'Inserted 3th', Inserted 2th', 'Inserted 1th',
	 *  'Unit Test - Image Title 2', 'Inserted 3th', Inserted 2th', 'Inserted 1th',
	 *  'Unit Test - Image Title 3', 'Inserted 3th', Inserted 2th', 'Inserted 1th'
	 */
	private function checkPageZoneOrder(tx_newspaper_PageZone $pagezone, $message = '') {
		$extra = $pagezone->getExtras();
		$this->assertEquals('Unit Test - Image Title 1', $extra[0]->getAttribute('title'), $message);
		$this->assertEquals('Inserted 3th', $extra[1]->getAttribute('title'), $message);
		$this->assertEquals('Inserted 2th', $extra[2]->getAttribute('title'), $message);
		$this->assertEquals('Inserted 1th', $extra[3]->getAttribute('title'), $message);

		$this->assertEquals('Unit Test - Image Title 2', $extra[4]->getAttribute('title'), $message);
		$this->assertEquals('Inserted 3th', $extra[5]->getAttribute('title'), $message);
		$this->assertEquals('Inserted 2th', $extra[6]->getAttribute('title'), $message);
		$this->assertEquals('Inserted 1th', $extra[7]->getAttribute('title'), $message);

		$this->assertEquals('Unit Test - Image Title 3', $extra[8]->getAttribute('title'), $message);
		$this->assertEquals('Inserted 3th', $extra[9]->getAttribute('title'), $message);
		$this->assertEquals('Inserted 2th', $extra[10]->getAttribute('title'), $message);
		$this->assertEquals('Inserted 1th', $extra[11]->getAttribute('title'), $message);
	}

	private function createExtras() {
		foreach ($this->extra_data as $index => $extra) {
			$extra_uid = tx_newspaper::insertRows($this->concrete_extra_table, $extra);
	    	
	    	$abstract_uid = tx_newspaper_Extra::createExtraRecord($extra_uid, $this->concrete_extra_table);
			$this->extra_abstract_uids[] = $abstract_uid;
			
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
    /** @var tx_newspaper_PageZone */
	private $pagezone = null;
	private $source = null;
	private $uid = 0;
	private $pagezone_uid = 0;
	
	private $extra_table = 'tx_newspaper_extra';
	private $concrete_extra_table = 'tx_newspaper_extra_image';
	private $extra2pagezone_table = 'tx_newspaper_pagezone_page_extras_mm';
	private $pagezone_table = 'tx_newspaper_pagezone';
	private $pagezone_page_table = 'tx_newspaper_pagezone_page';

	
	private $pagezone_page_data = array(
		'pid'		=> '2476',
		'tstamp'	=> '1234567890',
		'crdate'	=> '1234567890', 		  	
		'cruser_id'	=> '1',
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
			'tstamp' => 1234567890,
			'crdate' => 1234567890,
			'cruser_id' => 1,
			'deleted' => 0,
			'hidden' => 0,
			'starttime' => 0,
			'endtime' => 0,
			'title' => "Image 1",
			'image' => "E3_033009T.jpg",	
			'caption' => "Caption for image 3",	
		),
		array(
			'pid' => 2573,
			'tstamp' => 1234567890,
			'crdate' => 1234567890,
			'cruser_id' => 1,
			'deleted' => 0,
			'hidden' => 0,
			'starttime' => 0,
			'endtime' => 0,
			'title' => "Image 2",	
			'image' => "120px-GentooFreeBSD-logo.svg_02.png",	
			'caption' => "Daemonic Gentoo",	
		),
		array(
			'pid' => 2573,
			'tstamp' => 1234567890,
			'crdate' => 1234567890,
			'cruser_id' => 1,
			'deleted' => 0,
			'hidden' => 0,
			'starttime' => 0,
			'endtime' => 0,
			'title' => "Image 3",	
			'image' => "lolcatsdotcomoh5o6d9hdjcawys6.jpg",	
			'caption' => "caption[5]",	
		),
	);
	
	private $extra_pos = array(
		1024, 2048, 4096
	);
	
	private $extra_abstract_uids = array();
}
?>
