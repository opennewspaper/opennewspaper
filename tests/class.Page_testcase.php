<?php
/*
 * Created on Oct 27, 2008
 *
 * Author: helge
 */

require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_page.php');
require_once(PATH_typo3conf . 'ext/newspaper/Classes/private/class.tx_newspaper_pagezone_factory.php');
require_once(PATH_typo3conf . 'ext/newspaper/tests/class.tx_newspaper_database_testcase.php');
/// testsuite for class tx_newspaper_page
class test_Page_testcase extends tx_newspaper_database_testcase {

	function setUp() {
		$this->old_page_id = $GLOBALS['TSFE']->page;
		$GLOBALS['TSFE']->page['uid'] = $this->plugin_page;
		$GLOBALS['TSFE']->page['tx_newspaper_associated_section'] = $this->section_uid;
		parent::setUp();
        $this->section_uid = $this->fixture->getParentSectionUid();
		$this->section = new tx_newspaper_Section($this->section_uid);
        $this->section_name = $this->fixture->getParentSectionName();
		$this->page = new tx_newspaper_Page($this->section, new tx_newspaper_PageType(1));
	}

	function tearDown() {
		$GLOBALS['TSFE']->page = $this->old_page_id;
		/// Make sure $_GET is clean
		unset($_GET['art']);
		unset($_GET['type']);
		parent::tearDown();

	}

	public function test_createPage() {
		$temp = new tx_newspaper_Page($this->section, new tx_newspaper_PageType(array()));		
		$uid = $temp->store();
		$this->assertTrue(is_object($temp), 'page-object is no object');
		$this->assertTrue($temp instanceof tx_newspaper_Page, 'created object is not of type '.get_class(tx_newspaper_Page));
		$this->assertEquals($temp->getUid(), $uid, 'method getUid does not return uid');
		$this->assertEquals($temp->getAttribute('uid'), $uid, 'method getAttribute does not return uid');		
		tx_newspaper::deleteRows($this->page->getTable(), $uid);
		
		$this->setExpectedException('tx_newspaper_IllegalUsageException');
		$temp = new tx_newspaper_Page('I\'m a string!');
	}
	
	public function testRender() {
        $this->skipTest('Rendering does not work yet'); return;
        $this->doTestContains($this->page->render(), $this->section_name);
		$this->assertRegExp('/.*Ressortseite.*/', $this->page->render(),
						    'Plugin output: '.$this->page->render());
	}

    public function test_sectionPage() {
        $pagetype = new tx_newspaper_PageType();
        $pagetype_uid = tx_newspaper::insertRows($pagetype->getTable(), array('get_var' => 'page', 'get_value' => 100));
        $pagetype = new tx_newspaper_PageType($pagetype_uid);

        $this->page = new tx_newspaper_Page($this->section,
            $pagetype);
        $this->page->store();
        $this->doTestContains($this->page->render(), $this->section_name);
    }

	public function test_articlePage() {

        $this->skipTest('Rendering does not work yet'); return;

		$this->page = new tx_newspaper_Page($this->section, new tx_newspaper_PageType(array('art' => 1)));
		/// set an article ID for article renderer extra
		$_GET['art'] = 1;
        $this->doTestContains($this->page->render(), $this->section_name);
		$this->assertRegExp('/.*Artikelseite.*/', $this->page->render('', null),
						    'Plugin output: '.preg_replace('/"data:image\/png;base64,.*?"/', '"data:image/png;base64,..."', $this->page->render('', null)));

	}

    public function testEmptyPageZones() {
		/// This test page is guaranteed to have no page zones
		$this->page = new tx_newspaper_Page($this->section, 
											new tx_newspaper_PageType(array('page' => 666)));
		$this->page->store();											
		$this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $this->page->getPageZones());
		$this->assertTrue(sizeof($this->page->getPageZones()) == 0);
	}

    public function test_getPageZonesReturnsArray() {
        $this->assertTrue(
            is_array($this->page->getPageZones()),
            "Expected pagezones"
        );
    }

    public function test_getPageZonesReturnsAsManyPagezonesAsInFixture() {
        $this->assertTrue(
            sizeof($this->page->getPageZones()) == sizeof($this->fixture->getPageZoneTypes()),
            "Expected " . sizeof($this->fixture->getPageZoneTypes()) . " pagezones, got " . sizeof($this->page->getPageZones())
        );
    }

    public function test_getPageZonesReturnsAllPagezonesInFixture() {
        $pagezones = $this->page->getPageZones();
        $expected_pagezone_types = $this->fixture->getPageZoneTypes();

        foreach ($expected_pagezone_types as $expected_type) {
            $this->assertTrue(
                self::pagezoneOfSameTypeIsPresentInArray($expected_type, $pagezones),
                "pagezone type $expected_type not in array " . self::arrayToString($pagezones)
            );
        }
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

        $this->skipTest('test not yet ready'); return;
	}
	
	public function test_toString() {
		$this->page->getAttribute('uid');
		$string = strval($this->page);
		$this->doTestContains($string, 'UID: 1');
	}
	
	////////////////////////////////////////////////////////////////////////////


    /**
     * @static
     * @param tx_newspaper_PageZoneType $pagezonetype
     * @param tx_newspaper_Pagezone[] $pagezones_to_check
     * @return bool
     */
    private static function pagezoneOfSameTypeIsPresentInArray(tx_newspaper_PageZoneType $pagezonetype, array $pagezones_to_check) {
        foreach ($pagezones_to_check as $to_check) {
            if ($pagezonetype->getUid() == $to_check->getPageZoneType()->getUid()) {
                return true;
            }
        }
        return false;
    }

    private static function arrayToString(array $array) {
        $ret = '('; $separator = '';
        foreach ($array as $element) {
            $ret .= $element . $separator;
            $separator = ', ';
        }
        return $ret . ')';
    }

    /** @var tx_newspaper_Section */
	private $section = null;
    /** @var tx_newspaper_Page */
	private $page = null;
	private $old_page_id = null;
	private $section_uid = null;
    private $section_name = null;
}
?>
