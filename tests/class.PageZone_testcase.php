<?php

/*
 * Created on Oct 27, 2008
 *
 * Author: lene
 */

// TODO relative paths so that the IDE can resolve dependencies
require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_pagezone.php');
require_once('class.fixture.php');
require_once('class.InheritanceStructure.php');
require_once('class.tx_newspaper_database_testcase.php');

/// testsuite for class tx_newspaper_pagezone
class test_PageZone_testcase extends tx_newspaper_database_testcase {


    function setUp() {

        $timer = tx_newspaper_ExecutionTimer::create();

        /** @var t3lib_DB */
        global $TYPO3_DB;
        parent::setUp();
        $pagezonetypes = $this->fixture->getPageZoneTypes();
        $this->pagezone_page_data['pagezonetype_id'] = $pagezonetypes[0]->getUid();
        if (false) {
            $this->uid = tx_newspaper::insertRows($this->pagezone_page_table, $this->pagezone_page_data);
        } else {
            $query = $TYPO3_DB->INSERTquery($this->pagezone_page_table, $this->pagezone_page_data);
            $res = $TYPO3_DB->sql_query($query);
            if (!$res) die("$query failed!");
            
            $this->uid = $TYPO3_DB->sql_insert_id();
        }

        $this->pagezone = new tx_newspaper_PageZone_Page($this->uid);
        $this->pagezone_uid = $this->pagezone->getAbstractUid();

        $this->createExtras();
    }

    public function test_insertExtraAfter() {
        $s = new InheritanceStructure($this->fixture);
        $after = $s->parentZ()->getExtrasOf('tx_newspaper_extra_ArticleList')[0];
        $this->assertTrue(
            $after instanceof tx_newspaper_extra_ArticleList,
            "Finding first article list on page zone failed"
        );
        $insert = $this->fixture->createExtraToInherit('tx_newspaper_Extra_Textbox');
        $this->assertFalse(
            $s->parentZ()->doesContainExtra($insert),
            "WTF??? page zone contains extra just created."
        );

        $s->parentZ()->insertExtraAfter($insert, $after->getOriginUid());

        $this->assertTrue(
            $s->parentZ()->doesContainExtra($insert),
            "Inserting extra failed"
        );
        $this->assertTrue(
            $this->extraComesBefore($after, $insert, $s->parentZ()),
            "inserted extra before specified place instead of after"
        );
    }

    public function test_insertExtraAfterOnChildren() {
        $this->makePageZoneHierarchy();
        $first = $this->level1->getExtrasOf('tx_newspaper_extra_Generic')[0];
        $this->assertTrue(
            $first instanceof tx_newspaper_extra_Generic,
            "Finding first generic extra on page zone failed" . self::printExtrasWithPosition($this->level1)
        );
        $insert = $this->fixture->createExtraToInherit('tx_newspaper_Extra_Textbox');
        $this->assertFalse(
            $this->level1->doesContainExtra($insert),
            "WTF??? page zone contains extra just created."
        );

        $this->level1->insertExtraAfter($insert, $first->getOriginUid());

        $this->level2->rereadExtras();
        $this->level3_1->rereadExtras();

        $this->assertTrue(
            $this->level2->doesContainExtra($insert),
            "Inserting extra on child zone failed " . self::printExtrasWithPosition($this->level1) . self::printExtrasWithPosition($this->level2) . self::printExtrasWithPosition($this->level3_1) . self::printExtrasWithPosition($this->level3_2)
        );
        $this->assertTrue(
            $this->extraComesBefore($first, $insert, $this->level2),
            "inserted extra on child zone before specified place instead of after " . self::printExtrasWithPosition($this->level2) .
                " expected " . self::printExtrasWithPosition($this->level1)
        );
        $this->assertTrue(
            $this->level3_1->doesContainExtra($insert),
            "Inserting extra on grandchild zone failed " . self::printExtrasWithPosition($this->level3_1)
        );
        $this->assertTrue(
            $this->extraComesBefore($first, $insert, $this->level3_1),
            "inserted extra on grandchild zone before specified place instead of after " . self::printExtrasWithPosition($this->level3_1) .
                " expected " . self::printExtrasWithPosition($this->level1)
        );
    }

    private static function printExtrasWithPosition(tx_newspaper_PageZone $pz) {
        return print_r(self::getExtrasWithPosition($pz), 1);
    }

    private static function getExtrasWithPosition(tx_newspaper_PageZone $pz) {
        return array_map(
            'test_PageZone_testcase::extraWithPosition',
            $pz->getExtras()
        );
    }

    private static function extraWithPosition (tx_newspaper_Extra $e) {
        return $e->getTable() . " (" . $e->getUid() . ")@" . $e->getAttribute('position');
    }

    public function test_removeExtra() {
        $s = new InheritanceStructure($this->fixture);
        $e = $s->parentZ()->getExtrasOf('tx_newspaper_extra_ArticleList')[0];
        $this->assertTrue($e instanceof tx_newspaper_extra_ArticleList);
        $s->parentZ()->removeExtra($e);
        $this->assertFalse($s->parentZ()->doesContainExtra($e));
        $this->assertFalse($s->childZ()->doesContainExtra($e));
        $this->assertFalse($s->grandchildZ()->doesContainExtra($e));
    }

    public function test_moveExtraAfter() {
        $s = new InheritanceStructure($this->fixture);
        $extras = $s->parentZ()->getExtras();
        $first = $extras[0];
        $second = $extras[1];
        $this->assertTrue(
            $this->extraComesBefore($first, $second, $s->parentZ()),
            "setup wrong: " . print_r(
                array_map(
                    function(tx_newspaper_Extra $e) { return $e->getDescription() . "@" . $e->getAttribute('position'); },
                    $s->parentZ()->getExtras()
                ), 1
            )
        );

        $s->parentZ()->moveExtraAfter($first, $second->getOriginUid(), true);

        $s->parentZ()->rereadExtras();
        $s->childZ()->rereadExtras();
        $s->grandchildZ()->rereadExtras();

        $this->assertTrue(
            $this->extraComesBefore($second, $first, $s->parentZ()),
            "move failed on parent: " .  print_r(
                array_map(
                    function(tx_newspaper_Extra $e) { return $e->getDescription() . "@" . $e->getAttribute('position'); },
                    $s->parentZ()->getExtras()
                ), 1
            )
        );
    }

    public function test_moveExtraAfterOnChildren() {
        $this->makePageZoneHierarchy(array($this->fixture->createExtraToInherit('tx_newspaper_Extra_Generic'), $this->fixture->createExtraToInherit('tx_newspaper_Extra_Ad')));
        $first = $this->level1->getExtrasOf('tx_newspaper_extra_Generic')[0];
        $this->assertTrue(
            $first instanceof tx_newspaper_extra_Generic,
            "Finding first extra on page zone failed" . self::printExtrasWithPosition($this->level1)
        );
        $second = $this->level1->getExtrasOf('tx_newspaper_extra_Ad')[0];
        $this->assertTrue(
            $second instanceof tx_newspaper_extra_Ad,
            "Finding second extra on page zone failed" . self::printExtrasWithPosition($this->level1)
        );

        $this->level1->moveExtraAfter($first, $second->getOriginUid(), true);

        $this->level1->rereadExtras();
        $this->level2->rereadExtras();
        $this->level3_1->rereadExtras();

        $this->assertTrue(
            $this->extraComesBefore($second, $first, $this->level2),
            "move failed on child: " . self::printExtrasWithPosition($this->level2) . " expected " . self::printExtrasWithPosition($this->level1)
        );
        $this->assertTrue(
            $this->extraComesBefore($second, $first, $this->level3_1),
            "move failed on grandchild: " . self::printExtrasWithPosition($this->level3_1) . " expected " . self::printExtrasWithPosition($this->level1)
        );

    }

    public function test_copyExtrasFrom() {

        $timer = tx_newspaper_ExecutionTimer::create();

        $s = new InheritanceStructure($this->fixture);
        $from = $s->parentS()->getActivePages()[0]->getPageZone($this->getAPageZoneType());
        $to = $this->createPagezoneForInheriting();

        $to->copyExtrasFrom($from);
        $to->rereadExtras();

        foreach ($from->getExtras() as $extra) {
            $this->assertTrue(
                $to->doesContainExtra($extra),
                "Pagezone " . $to->printableName() . " does not contain " . $extra->getDescription()
            );
        }
    }

    public function test_changeParentSetsUpInheritanceHierarchy() {
        $this->makePageZoneHierarchy();

        foreach($this->level1->getInheritanceHierarchyDown() as $page_zone) {
            $extras = $page_zone->getExtras();
            $this->assertEquals(
                1, sizeof($extras),
                "page zone $page_zone has " . sizeof($extras) . " extras instead of one"
            );
            $this->assertEquals(
                $this->inherited_extra->getUid(), $extras[0]->getUid(),
                "page zone $page_zone, extra " . $extras[0] . " is not the same concrete extra as inherited: " . $this->inherited_extra
            );
        }
    }

    public function test_changeParentDoesCreateOwnExtraRecord() {
        $this->makePageZoneHierarchy();

        foreach($this->level1->getInheritanceHierarchyDown(false) as $page_zone) {
            $extras = $page_zone->getExtras();
            $this->assertEquals(
                $this->inherited_extra->getUid(), $extras[0]->getUid(),
                "page zone $page_zone, extra " . $extras[0] . " is not the same concrete extra as inherited: " . $this->inherited_extra
            );
            $this->assertNotEquals(
                $this->inherited_extra->getExtraUid(), $extras[0]->getExtraUid(),
                "page zone $page_zone, extra " . $extras[0] . " has same extra uid as inherited"
            );
        }
    }

    public function test_changeParentCreatesCorrectOriginUid() {
        $this->makePageZoneHierarchy();

        foreach($this->level1->getInheritanceHierarchyDown(false) as $page_zone) {
            $extras = $page_zone->getExtras();
            $this->assertEquals(
                $this->inherited_extra->getExtraUid(), $extras[0]->getOriginUid(),
                "page zone $page_zone, extra " . $extras[0] . " does not have original extra as origin UID: " . $this->inherited_extra->getExtraUid() . " != " . $extras[0]->getOriginUid()
            );
        }
    }

    public function test_changeParentWorksForInheritingPagezones() {

        if ($this->change_parent_collection_running) return;

        if (!tx_newspaper_Pagezone_Page::isHorizontalInheritanceEnabled()) {
            /*
             * If horizontal inheritance is not enabled, only changing the parent to
             * the pagezone one up in the hierarchy and to none will work, i.e. switching
             * inheritance on and off.
             */
            $this->skipTest('Horizontal Inheritance must be enabled for this test'); return;
        }

        $zone = $this->createPagezoneForInheriting();
        $extra = $this->fixture->createExtraToInherit('tx_newspaper_Extra_Textbox');
        $zone->addExtra($extra);
        $this->assertEquals(1, sizeof($zone->getExtras()));

        $parent_section = new tx_newspaper_Section($this->fixture->getParentSectionUid());
        $parent_zone = $this->fixture->getRandomPageZoneForPlacement($parent_section);

        $zone->changeParent($parent_zone->getAbstractUid());

        echo "parent zone: ";
        var_dump($parent_zone);

        echo "record:";
        var_dump(tx_newspaper_DB::getInstance()->selectOneRow('*', 'tx_newspaper_pagezone_page', 'uid = ' . $zone->getUid()));

        echo "parent for placement:";
        var_dump($zone->getParentForPlacement());

        $this->assertEquals(
            $this->level1->getAbstractUid(), $zone->getParentForPlacement()->getAbstractUid(),
            "Parent page zone should be " . $this->level1->getAbstractUid() . ", is " . $zone->getParentForPlacement()->getAbstractUid()
        );
        $this->assertEquals(
            2, sizeof($zone->getExtras()),
            "There should be 1 original and 1 inherited extra; actual total is " . sizeof($zone->getExtras())
        );
        $this->assertTrue(
            $zone->doesContainExtra($extra),
            "Extra originally on page zone, $extra, gone after changeParent()"
        );
        $this->assertTrue(
            $zone->doesContainExtra($this->inherited_extra),
            "inherited extra, " . $this->inherited_extra . ", not there after changeParent(): " . print_r($zone->getExtras(), 1)
        );
    }

    public function test_changeParentWithoutParentAndPresentExtras() {
        // set grandchild section to have no parent
        $parent_section = new tx_newspaper_Section($this->fixture->getParentSectionUid());
        $grandchild = array_pop($parent_section->getChildSections(true));
        $pageZone = $this->fixture->getRandomPageZoneForPlacement($grandchild);

        $pageZone->changeParent(-1);

        $this->assertTrue(
            is_null($pageZone->getParentForPlacement()),
            "Parent is " . $pageZone->getParentForPlacement()
        );

        $inherited_extras = array_filter(
            $pageZone->getExtras(),
            function(tx_newspaper_Extra $e) { return $e->getOriginUid() != $e->getExtraUid(); }
        );

        $this->assertEquals(
            0, sizeof($inherited_extras),
            sizeof($inherited_extras) . " extras left: " . print_r($inherited_extras, 1)
        );
    }

    public function test_changeParentWithoutParentWithoutExtras() {

        // set grandchild section to have no parent
        $grandchild = array_pop($this->fixture->getAllSections());
        $pagezone = $this->fixture->getRandomPageZoneForPlacement($grandchild);

        // remove extras on this page zone
        tx_newspaper_DB::getInstance()->deleteRows(
            'tx_newspaper_pagezone_page_extras_mm', 'uid_local = ' . $pagezone->getUid()
        );
        $pagezone->rereadExtras();

        $this->assertEquals(0, sizeof($pagezone->getExtras()), sizeof($pagezone->getExtras()) . " Extras left!");

        $pagezone->changeParent(-1);

        $this->assertTrue(
            is_null($pagezone->getParentForPlacement()),
            "Parent is " . $pagezone->getParentForPlacement()
        );

       $inherited_extras = array_filter(
            $pagezone->getExtras(),
            function(tx_newspaper_Extra $e) { return $e->getOriginUid() != $e->getExtraUid(); }
        );

        $this->assertEquals(
            0, sizeof($inherited_extras),
            sizeof($inherited_extras) . " extras left: " . print_r($inherited_extras, 1)
        );
    }

    public function test_changeParentSwitchInheritanceOffAndOn() {

        /**
         *  previous tests have messed both with objects and the DB so much, that the following two
         *  lines are necessary. don't ask me how the changes can persist even across tests, making
         *  the second line necessary.
         */
        if ($this->change_parent_collection_running) return;
        tx_newspaper_PageZone_Factory::clear();

        // set up a page zone with both extras on it and inherited extras
        $parent_zone = $this->fixture->getRandomPageZoneForPlacement($this->fixture->getParentSection());

        $inherit_extra = $this->fixture->createExtraToInherit('tx_newspaper_Extra_Generic');
        $parent_zone->insertExtraAfter($inherit_extra);

        $this->assertTrue(
            $parent_zone->doesContainExtra($inherit_extra),
            "Extra not present on parent zone after insert"
        );

        $grandchild = array_pop($this->fixture->getAllSections());
        $zone = $this->fixture->getRandomPageZoneForPlacement($grandchild);
        $zone->rereadExtras();

        $original_extras = $zone->getExtras();
        $inherited = array_pop($zone->getExtrasOf('tx_newspaper_Extra_Generic'));
        $this->assertTrue(is_object($inherited));
        $original_origin_uid = $inherited->getOriginUid();

        $this->assertTrue(
            $zone->doesContainExtra($inherit_extra),
            "Extra not present on inheriting zone after insert"
        );

        // switch inheritance off
        $zone->changeParent(-1);

        // test that inherited extras are gone
        $this->assertFalse(
            $zone->doesContainExtra($inherit_extra),
            "Extra still present after turning off inheritance"
        );

        // switch inheritance on
        $zone->changeParent(0);

        // test that inherited extra is there,
        $this->assertTrue(
            $zone->doesContainExtra($inherit_extra),
            "Extra still present after turning off inheritance"
        );
        // ...with the same origin uid as before
        $actually_inserted_extra = array_pop($zone->getExtrasOf('tx_newspaper_Extra_Generic'));
        $this->assertEquals(
            $original_origin_uid, $actually_inserted_extra->getOriginUid(),
            "origin uid has changed"
        );

        foreach($original_extras as $extra) {
            $this->assertTrue(
                $zone->doesContainExtra($extra),
                "Extra $extra originally on page zone, not any more"
            );
        }
    }

    private $change_parent_collection_running = false;
    public function test_changeParent() {
        $this->change_parent_collection_running = true;
        echo "<p>test_changeParentSetsUpInheritanceHierarchy()</p>";
        $this->test_changeParentSetsUpInheritanceHierarchy();
        echo "<p>test_changeParentDoesCreateOwnExtraRecord()</p>";
        $this->test_changeParentDoesCreateOwnExtraRecord();
        echo "<p>test_changeParentCreatesCorrectOriginUid()</p>";
        $this->test_changeParentCreatesCorrectOriginUid();
        echo "<p>test_changeParentWorksForInheritingPagezones()</p>";
        $this->test_changeParentWorksForInheritingPagezones();
        echo "<p>test_changeParentWithoutParentAndPresentExtras()</p>";
        $this->test_changeParentWithoutParentAndPresentExtras();
        echo "<p>test_changeParentWithoutParentWithoutExtras()</p>";
        $this->test_changeParentWithoutParentWithoutExtras();
        echo "<p>test_changeParentSwitchInheritanceOffAndOn()</p>";
        $this->test_changeParentSwitchInheritanceOffAndOn();
    }

    public function test_doesContainExtra() {
        $zone = $this->createPagezoneForInheriting();
        $extra = $this->fixture->createExtraToInherit('tx_newspaper_Extra_Textbox');
        $zone->addExtra($extra);

        $this->assertTrue($zone->doesContainExtra($extra), "Extra $extra not found by doesContainExtra()");

        $extra = $this->fixture->createExtraToInherit('tx_newspaper_Extra_Bio');
        $this->assertFalse($zone->doesContainExtra($extra), "Extra $extra wrongly found by doesContainExtra()");

        // okay, theoretically you could test inheriting page zones as well... but we'll leave that as an exercise for the reader
    }

    public function test_createPageZone() {
        $temp = tx_newspaper_PageZone_Factory::getInstance()->create($this->pagezone->getPageZoneUID());
        $this->assertTrue(is_object($temp));
        $this->assertTrue($temp instanceof tx_newspaper_PageZone);
    }
    
    public function test_Title() {
        echo $this->pagezone->getTitle();
        $this->assertTrue('Seitenbereich' == $this->pagezone->getTitle());
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
//                $this->assertEquals($pzt->getAttribute($attribute), $value);
            }
            $this->assertEquals($pzt->getTable(), 'tx_newspaper_pagezonetype');
            $this->assertEquals($pzt->getModuleName(), 'np_pagezonetype');
            $this->assertEquals('Page zone type', $pzt->getTitle());
            
            $pzt->setAttribute('uid', 0);
            $this->assertEquals($pzt->getAttribute('uid'), 0);
        }
    }
    
    /**    setAttribute is tested without calling getAttribute() first
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

            if ($pagezone->isArticle()) continue;

            $parent_page = $pagezone->getParentPage();
            $this->assertTrue(
                $parent_page instanceof tx_newspaper_Page,
                'getParentPage() for page zone ' . $pagezone->printableName() . ' is not a Page: ' . print_r($parent_page, 1)
            );
        }
    }

    public function test_getParentPageFoundInFixture() {
        foreach ($this->fixture->getPageZones() as $pagezone) {

            if ($pagezone->isArticle()) continue;

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

    ///    singularly created page zone has no parent
    public function test_getParentForPlacementWithoutParent() {
        $this->assertEquals($this->pagezone->getParentForPlacement(), null);
    }

    public function test_getParentForPlacementWithoutInheritance() {

        $pagezone = $this->fixture->getPageZoneWithoutInheritance();
        $parent = $pagezone->getParentForPlacement();
        $this->assertEquals(
            $parent, null,
            'PageZone ' . $pagezone->getUid() .': ' .
            'inheritance mode is set to no inheritance, but a parent (' .
            print_r($parent, 1) . ') is returned. '
        );
    }

    /**
     *  @todo create a pagezone in the fixture which inherits from a specified pagezone
     */
/*
    public function test_getParentForPlacementExplicitPagezone() {
        $this->skipTest("No pagezones with manual inheritance set up yet"); return;
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
    }
*/
    public function test_getParentForPlacementInheritFromSameType() {
        $tested = $untested = 0;
        foreach ($this->fixture->getPageZones() as $pagezone) {
            if ($pagezone->getAttribute('inherits_from') == 0) {
                $tested++;
                $parent = $pagezone->getParentForPlacement();
                //    Normal inheritance mode: go up in the section tree
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
                } else {
                    $untested++;
                    if (0) {
                        t3lib_div::debug($pagezone->__toString() . ': no parent');
                    }
                }
            }
        }

        if ($tested == 0) $this->fail("No pagezones with automatic inheritance set up yet, $untested untested");
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


    private function checkAllElementsArePagezones(/** @var tx_newspaper_Pagezone[] $hierarchy */$hierarchy) {
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
    
    public function test_storeEqualAttributes() {
        $this->pagezone->store();
        $record = tx_newspaper::selectOneRow('*', 'tx_newspaper_pagezone_page', "uid = " . $this->pagezone->getUid());

        $this->checkAttributesAreEqualToRecord($this->pagezone, $record);

    }

    private function checkAttributesAreEqualToRecord(tx_newspaper_Pagezone $pagezone, array $record) {
        foreach ($record as $attribute => $value) {

            if ($attribute == 'uid') continue;

            $this->assertEquals(
                $pagezone->getAttribute($attribute), $value,
                "Attribute $attribute: stored as $value, in memory as " . $this->pagezone->getAttribute($attribute) . print_r($record, 1)
            );
        }
    }

    public function test_storeEqualAbstractAttributes() {
        $this->pagezone->store();

        $abstract_record = tx_newspaper::selectOneRow('*', 'tx_newspaper_pagezone', "uid = " . $this->pagezone->getAbstractUid());

        unset($abstract_record['pid']);
        $this->checkAttributesAreEqualToRecord($this->pagezone, $abstract_record);
    }

    public function test_storeChangedAttribute() {

        $this->pagezone->store();
        $record = tx_newspaper::selectOneRow('crdate', 'tx_newspaper_pagezone_page', "uid = " . $this->pagezone->getUid());
        $this->assertEquals(
            $record['crdate'], 1234567890,
            'preset crdate should be 1234567890, is ' . $record['crdate']
        );

        $time = time();
        $this->pagezone->setAttribute('crdate', $time);
        $this->pagezone->store();
        $record = tx_newspaper::selectOneRow('crdate', 'tx_newspaper_pagezone_page', "uid = " . $this->pagezone->getUid());
        $this->assertEquals(
            $record['crdate'], $time,
            "changed crdate should be $time, is " . $record['crdate']
        );

    }

    public function test_storeNewPagezone() {

        $new_pagezone = new tx_newspaper_PageZone_Page();
        $types = $this->fixture->getPageZoneTypes();
        $this->assertFalse(empty($types));
        $new_pagezone->setPageZoneType($types[0]);
        $new_pagezone->store();

        $abstract_uid = $new_pagezone->getAbstractUid();
        $this->assertTrue($abstract_uid > 0);

        $abstract_record = tx_newspaper::selectOneRow('*', 'tx_newspaper_pagezone', "uid = " . $abstract_uid);
        $this->checkAttributesAreEqualToRecord($new_pagezone, $abstract_record);

        $uid = $new_pagezone->getUid();
        $this->assertTrue($uid > 0);
    }
/*
    public function test_render() {
        $rendered = $this->pagezone->render();
        $template = file_get_contents(PATH_typo3conf . 'ext/newspaper/res/templates/tx_newspaper_pagezone_page.tmpl');
        $this->assertFalse($template === false);

        $this->doTestContains($rendered, 'uid: ' . $this->pagezone->getUid());
        $this->doTestContains($rendered, 'crdate: ' . $this->pagezone->getAttribute('crdate'));
    }
*/
    public function test_getAbstractUid() {
        /** This test seems a bit redundant because it checks the return value
         *  of getAbstractUid() against the return value of getAbstractUid().
         */ 
        $this->assertEquals($this->pagezone->getAbstractUid(), $this->pagezone_uid);
        /// Now we get real.
        $pagezone = tx_newspaper_PageZone_Factory::getInstance()->create($this->pagezone->getAbstractUid());
        $this->assertEquals($this->pagezone->getUid(), $pagezone->getUid());
    }

    ////////////////////////////////////////////////////////////////////////////
    //    still a lot of work to be done here
    ////////////////////////////////////////////////////////////////////////////
/*
    public function test_insertExtraAfterInsertsCorrectNumber() {
        $this->skipTest("test_insertExtraAfterInsertsCorrectNumber not yet finished!"); return;
        foreach ($this->fixture->getPageZones() as $pagezone) {
            $old_extras = $this->insertNewExtras($pagezone);
            $this->checkNumberInsertedExtrasCorrect($pagezone, $old_extras);
        }
    }

    private function insertNewExtras(tx_newspaper_PageZone $pagezone) {
        $old_extras = $pagezone->getExtrasOf('tx_newspaper_Extra_Image');

        foreach ($old_extras as $extra_after_which) {
            for ($i = 1; $i <= 3; $i++) {
                $new_extra = new tx_newspaper_Extra_Image();
                $new_extra->setAttribute('title', "Inserted ${i}th");
                $new_extra->store();
                $pagezone->insertExtraAfter($new_extra, $extra_after_which->getExtraUid(), false);
            }
        }
        return $old_extras;
    }

    private function checkNumberInsertedExtrasCorrect(tx_newspaper_PageZone $pagezone, array $old_extras) {
        $this->assertEquals(
            sizeof($pagezone->getExtrasOf('tx_newspaper_Extra_Image')),
            sizeof($old_extras) * (sizeof($this->extra_abstract_uids) + 1),
            'There should be ' . sizeof($this->extra_abstract_uids) . ' new Extras after each of the ' .
            sizeof($old_extras) . ' original Extras, so PageZone ' . $pagezone . ' should now have ' .
            sizeof($old_extras) * (sizeof($this->extra_abstract_uids) + 1) . ' Extras. Actually the number is ' .
            sizeof($pagezone->getExtrasOf('tx_newspaper_Extra_Image')) . '. '
        );

        $row = tx_newspaper::selectOneRow(
            'COUNT(*) AS num',
            $pagezone->getExtra2PagezoneTable(),
            'uid_local = ' . $pagezone->getUid()
        );
        $this->assertEquals(
            intval($row['num']),
            sizeof($old_extras) * (sizeof($this->extra_abstract_uids) + 1 + (sizeof($pagezone->getExtras())) - sizeof($pagezone->getExtrasOf('tx_newspaper_Extra_Image'))),
            'Entries in ' . $pagezone->getExtra2PagezoneTable() . ' not written correctly. ' .
            'There should be ' . sizeof($old_extras) * (sizeof($this->extra_abstract_uids) + 1) .
            ' where uid_local = ' . $pagezone->getUid() . ', but actually ' . $row['num'] . ' are there.'
        );
    }

    public function test_insertExtraAfterCheckOrder() {
        $this->skipTest("test_insertExtraAfterCheckOrder not yet finished!"); return;
        foreach ($this->fixture->getPageZones() as $pagezone) {
            $this->insertNewExtras($pagezone);
            $this->checkPageZoneOrder($pagezone);
        }
    }

    public function test_insertExtraAfterInsertsOnInheritingPagezones() {
        $this->skipTest("test_insertExtraAfterInsertsOnInheritingPagezones not yet finished!"); return;
        foreach ($this->fixture->getPageZones() as $pagezone) {

            $old_extras = $this->insertNewExtras($pagezone);

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
            }
        }
    }

    public function test_insertExtraAfterInsertsCorrectlyOnInheritingPagezones() {
        $this->skipTest("test_insertExtraAfterInsertsCorrectlyOnInheritingPagezones not yet finished!"); return;
        foreach ($this->fixture->getPageZones() as $pagezone) {
            $old_extras = $this->insertNewExtras($pagezone);
            foreach ($pagezone->getInheritanceHierarchyDown(false) as $sub_pagezone) {
                $this->checkPageZoneOrder($sub_pagezone, 'Order of Extras in inherited PageZone ' . $sub_pagezone . ' wrong. ');
            }
        }
    }

    public function test_getExtraOrigin() {
        $this->skipTest("test_getExtraOrigin not yet finished!"); return;
        foreach ($this->fixture->getPageZones() as $pagezone) {
            $hierarchy_root = array_pop($pagezone->getInheritanceHierarchyUp());
            $some_origin_extra = array_pop($hierarchy_root->getExtras());
            foreach($pagezone->getExtras() as $extra) {
#                t3lib_div::debug(
                    $pagezone->getExtraOrigin($extra)
#                )
                ;
#                t3lib_div::debug($pagezone->getExtraOriginAsString($extra));
                $extra->setAttribute('origin_uid', $some_origin_extra->getUid());
#                t3lib_div::debug($pagezone->getExtraOriginAsString($extra));
            }
        }

        $this->fail('test_getExtraOrigin() not yet implemented.');
    }

    public function test_removeExtra() {
        $this->skipTest("test_removeExtra not yet finished!"); return;
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
        $this->skipTest("test_moveExtraAfter not yet finished!"); return;
        foreach ($this->fixture->getPageZones() as $pagezone) {
            $extras = $pagezone->getExtras();
            if (sizeof($extras) < 2) {
                $this->fail("fixture error: not enough extras on page zone to run test");
            }
            $this->assertTrue($extras[0] instanceof tx_newspaper_Extra);
            $this->assertTrue($extras[1] instanceof tx_newspaper_Extra);
            $pagezone->moveExtraAfter($extras[0], $extras[1]->getOriginUid());
            $new_extras = $pagezone->getExtras();
            //    find $extra[0] and $extra[1] in $new_extras
            for ($i = 0; $i < sizeof($new_extras); $i++) {
                t3lib_div::debug($new_extras[$i]);
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
        $this->skipTest("test_setInherits not yet finished!"); return;
        foreach ($this->fixture->getPageZones() as $pagezone) {
#            t3lib_div::debug($pagezone->getInheritanceHierarchyUp());
        }
    }
    
    public function test_getInheritanceHierarchyDown() {
        $this->skipTest("test_getInheritanceHierarchyDown not yet finished!"); return;
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
        $this->skipTest("test_copyExtrasFrom not yet finished!"); return;
        foreach ($this->fixture->getPageZones() as $pagezone) {
#            t3lib_div::debug($pagezone->getInheritanceHierarchyUp());
        }
    }
*/


    public function runAllTests() {
        $reflection_class = new ReflectionClass($this);
        /** @var ReflectionMethod[] $valid_methods */
        $valid_methods = array_filter(
            $reflection_class->getMethods(ReflectionMethod::IS_PUBLIC),
            function(ReflectionMethod $m) { return ($m->getName() != 'test_runAllTests' && substr($m->getName(), 0, 5) == 'test_'); }
        );
        foreach($valid_methods as $method) {
            echo $method->getName() . "<br />\n";
            $method->invoke($this);
        }
    }

    ////////////////////////////////////////////////////////////////////////////

    /**
     * @return tx_newspaper_PageZoneType
     */
    private function getPagezoneForInheritance() {
        return array_shift($this->fixture->getPageZoneTypes());
    }

    /** Make sure the order is correct. \n
     *  Expected order: \n
     *  'Unit Test - Image Title 1', 'Inserted 3th', Inserted 2th', 'Inserted 1th',
     *  'Unit Test - Image Title 2', 'Inserted 3th', Inserted 2th', 'Inserted 1th',
     *  'Unit Test - Image Title 3', 'Inserted 3th', Inserted 2th', 'Inserted 1th'
     */
    private function checkPageZoneOrder(tx_newspaper_PageZone $pagezone, $message = '') {

        $extra = $pagezone->getExtrasOf('tx_newspaper_Extra_Image');
        t3lib_div::debug($extra);

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
            
            ///    link extra to article
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

    private function makePageZoneHierarchy(array $inherited_extras = array()) {
        if (empty($inherited_extras)) {
            $this->inherited_extra = $this->fixture->createExtraToInherit('tx_newspaper_Extra_Generic');
            $inherited_extras = array($this->inherited_extra);
        }

        $this->level1 = $this->createPagezoneForInheriting();

        foreach ($inherited_extras as $extra) {
            $this->level1->addExtra($extra);
        }


        $this->level2 = $this->createPagezoneForInheriting();
        $this->level2->changeParent($this->level1->getAbstractUid());

        $this->level3_1 = $this->createPagezoneForInheriting();
        $this->level3_2 = $this->createPagezoneForInheriting();
        $this->level3_1->changeParent($this->level2->getAbstractUid());
        $this->level3_2->changeParent($this->level2->getAbstractUid());
    }
    /** @var  tx_newspaper_Extra */
    private $inherited_extra;
    /** @var tx_newspaper_Pagezone_Page */
    private $level1;
    /** @var tx_newspaper_Pagezone_Page */
    private $level2;
    /** @var tx_newspaper_Pagezone_Page */
    private $level3_1;
    /** @var tx_newspaper_Pagezone_Page */
    private $level3_2;

    /**
     * @param $pagezonetype tx_newspaper_PageZoneType
     * @return tx_newspaper_PageZone_Page
     */
    private function createPagezoneForInheriting() {
        $pagezonetype = $this->getAPageZoneType();
        $this->assertTrue($pagezonetype instanceof tx_newspaper_PageZoneType, 'no page zone type found');

        $zone = new tx_newspaper_PageZone_Page();
        $zone->setPageZoneType($pagezonetype);
        $zone->store();

        $this->assertGreaterThan(0, intval($zone->getUid()), "uid is " . $zone->getUid());
        $this->assertGreaterThan(0, intval($zone->getAbstractUid()), "uid is " . $zone->getAbstractUid());

        return $zone;
    }

    /**
     * @return tx_newspaper_PageZoneType
     */
    private function getAPageZoneType() {
        $pagezonetypes = $this->fixture->getPageZoneTypes();
        return $pagezonetypes[0];
    }

    private function extraComesBefore(tx_newspaper_Extra $first, tx_newspaper_Extra $second, tx_newspaper_PageZone $zone) {
        if (!($zone->doesContainExtra($first) && $zone->doesContainExtra($second))) return false;
        return $first->getAttribute('position') < $second->getAttribute('position');
    }

    private function printPageZones(array $zones, $prefix = "") {
        if ($prefix) echo "$prefix:<br />\n";
        array_walk($zones,
            function (tx_newspaper_PageZone $p) {
                echo "$p<br />\n";
                array_walk($p->getExtras(), function (tx_newspaper_Extra $e) {
                    echo "&nbsp;$e<br />\n";
                });
            }
        );
    }
    
    private $bad_uid = 2000000000;            ///< pagezone that does not exist
    /** @var tx_newspaper_PageZone_Page */
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
        'pid'        => '2476',
        'tstamp'    => '1234567890',
        'crdate'    => '1234567890',               
        'cruser_id'    => '1',
        'deleted'    => '0',
        'pagezonetype_id' => '2',
        'pagezone_id' => 'X',
        'extras'    => '3',
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
            'image_file' => "E3_033009T.jpg",
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
            'image_file' => "120px-GentooFreeBSD-logo.svg_02.png",
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
            'image_file' => "lolcatsdotcomoh5o6d9hdjcawys6.jpg",
            'caption' => "caption[5]",    
        ),
    );
    
    private $extra_pos = array(
        1024, 2048, 4096
    );
    
    private $extra_abstract_uids = array();

}
?>
