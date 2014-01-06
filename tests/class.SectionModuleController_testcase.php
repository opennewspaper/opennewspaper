<?php
/*
 * Created on Oct 17, 2013
 *
 * Author: Lene Preuss <lene.preuss@gmail.com>
 */

require_once(PATH_typo3conf . 'ext/newspaper/Classes/Controller/SectionModuleController.php');
require_once(PATH_typo3conf . 'ext/newspaper/tests/class.tx_newspaper_database_testcase.php');

class InheritanceStructure {

    public function __construct(tx_newspaper_fixture $fixture) {
        $this->sections = $fixture->getAllSections();

        $this->parent_section = $this->getSectionByName('Daddy Section');
        $this->child_section = $this->getSectionByName('Son Section');
        $this->grandchild_section = $this->getSectionByName('Grandchild Section');

        $this->parent_zone = $fixture->getRandomPageZoneForPlacement($this->parent_section); #
        $this->child_zone =  $fixture->getRandomPageZoneForPlacement($this->child_section);
        $this->grandchild_zone =  $fixture->getRandomPageZoneForPlacement($this->grandchild_section);

    }

    public function __toString() {
        $ret = "";
        foreach ($this->sections as $section) {
            $ret .= $section->getSectionName() . "\n";
            foreach ($section->getActivePages() as $page) {
                $ret .= "    " . str_replace('Unit Test - ', '', $page->getPageType()->getAttribute('type_name')) . "\n";
                foreach ($page->getPageZones() as $zone) {
                    $ret .= "        " . str_replace('Unit Test - ', '', $zone->printableName()) . "\n";
                    if ($zone->getParentForPlacement() instanceof tx_newspaper_PageZone) {
                        $ret .= "            parent: " . str_replace('Unit Test - ', '', $zone->getParentForPlacement()->printableName()) . "\n";
                    }
                }
            }
        }
        return $ret;
    }

    public function &parentS() { return $this->parent_section; }
    public function &childS() { return $this->child_section; }
    public function &grandchildS() { return $this->grandchild_section; }

    public function &parentZ() { return $this->parent_zone; }
    public function &childZ() { return $this->child_zone; }
    public function &grandchildZ() { return $this->grandchild_zone; }

    private function &getSectionByName($name) {
        foreach($this->sections as $section) {
            if (strpos($section->getSectionName(), $name) !== false) return $section;
        }
        throw new tx_newspaper_InconsistencyException("Section $name not found");
    }

    /** @var tx_newspaper_Section[] */
    private $sections = array();
    /** @var tx_newspaper_Section */
    private $parent_section = null;
    /** @var tx_newspaper_Section */
    private $child_section = null;
    /** @var tx_newspaper_Section */
    private $grandchild_section = null;

    /** @var tx_newspaper_PageZone */
    private $parent_zone = null;
    /** @var tx_newspaper_PageZone */
    private $child_zone = null;
    /** @var tx_newspaper_PageZone */
    private $grandchild_zone = null;
}

class test_SectionModuleController_testcase extends tx_newspaper_database_testcase {


    function setUp() {
        parent::setUp();
        $this->controller = new Tx_newspaper_Controller_SectionModuleController();
    }
    
    function tearDown() {
    }

    public function test_buildInheritanceStructure() {
        $this->buildInheritanceStructure($this->fixture->createExtraToInherit('tx_newspaper_Extra_Generic'));
    }


    /**
     *  preparing the inheritance structure more than once leads to errors. instead of
     *  debugging those (i'm sooo fed up with debugging things, tbh) i put all tests in
     *  one function, which works.
     */
    public function test_changeParent() {

        list($inheriting_extra, /** @var InheritanceStructure */$s) = $this->prepareTestChangeParent();

        $this->assertEquals(
            $s->parentS()->getUid(), $s->grandchildS()->getParentSection()->getUid(),
            "grandchild's parent section: " . $s->grandchildS()->getParentSection()->getUid() . ", parent section: " . $s->parentS()->getUid()
        );

        $this->assertFalse(
            in_array($s->childZ(), $s->grandchildZ()->getInheritanceHierarchyUp(false)),
            "Child zone still in inheritance hierarchy: " . print_r(array_map(function(tx_newspaper_PageZone $z) { return $z->printableName() . $z->getAbstractUid(); }, $s->grandchildZ()->getInheritanceHierarchyUp(false)), 1)
        );
        $this->assertTrue(
            in_array($s->parentZ(), $s->grandchildZ()->getInheritanceHierarchyUp(false)),
            "Parent zone no longer in inheritance hierarchy"
        );
        $this->assertEquals(
            sizeof($s->grandchildZ()->getInheritanceHierarchyUp(false)), 1,
            "Too many zones in inheritance hierarchy"
        );

        $s->grandchildZ()->rereadExtras();

        $this->assertFalse(
            $s->grandchildZ()->doesContainExtra($inheriting_extra),
            "grandchild zone apparently still contains extra inherited from child zone: " . print_r(array_map(function(tx_newspaper_Extra $e) { return $e->getDescription(); }, $s->grandchildZ()->getExtras()), 1) . "<" . $inheriting_extra->getDescription() . ">"
        );

        $this->assertEquals(
            $s->parentZ()->getAbstractUid(), $s->grandchildZ()->getParentForPlacement()->getAbstractUid(),
            "grandchild's parent zone: " . $s->grandchildZ()->getParentForPlacement() . ", parent zone: " . $s->parentZ()
        );

        // check all origin uids on grandchild section, must be != extra uids
        foreach ($s->parentZ()->getExtras() as $inherited_extra) {
            $this->assertTrue(
                $s->grandchildZ()->doesContainExtra($inherited_extra),
                "inherited extra $inherited_extra not on inheriting zone"
            );
        }

    }


    /**
     * @return InheritanceStructure
     */
    private function buildInheritanceStructure(tx_newspaper_Extra $inheriting_extra) {
        $s = new InheritanceStructure($this->fixture);

        // insert extra on child section that must be gone from the grandchild section after changing parent
        $s->childZ()->insertExtraAfter($inheriting_extra);

        $s->grandchildZ()->rereadExtras();

        $this->checkInheritanceStructureIsInOrder($s, $inheriting_extra);

        return $s;
    }

    private static function debug(tx_newspaper_PageZone $zone, $text = '') {
        tx_newspaper_File::w(
            "$text: \n" .
            $zone->getAbstractUid() .'/'.str_replace('Unit Test - ', '', $zone->printableName()) . "\n" .
            implode(', ',
                array_map(
                    function (tx_newspaper_Extra $e) { return $e->getExtraUid() . "/" . $e->getUid(); },
                    $zone->getExtras()
                )
            ) .
            " In DB: ". implode(
                ', ',
                array_map(
                    function(array$a) { return array_pop($a); },
                    tx_newspaper_DB::getInstance()->selectRows('uid_foreign', $zone->getExtra2PagezoneTable(), 'uid_local = ' . $zone->getUid())
                )
            )
        );
    }

    /**
     * @param $parent_section
     */
    private function callChangeParent(tx_newspaper_Section &$section, tx_newspaper_Section $new_parent_section) {
        $property = new ReflectionProperty($this->controller, 'module_request');
        $property->setAccessible(true);
        $property->setValue($this->controller, array('parent_section' => $new_parent_section->getUid()));

        $method = new ReflectionMethod($this->controller, 'changeParent');
        $method->setAccessible(true);
        $method->invokeArgs($this->controller, array(&$section));

        $section->store();
    }

    /**
     *  checks that \p $inheriting_extra has been inserted on the child zone of \p $structure,
     *  is inherited in the grandchild zone, and not present on the parent zone.
     * @param $structure
     * @param $inheriting_extra
     */
    private function checkInheritanceStructureIsInOrder(InheritanceStructure &$structure, tx_newspaper_Extra $inheriting_extra) {

        $this->assertFalse(
            $structure->parentZ()->doesContainExtra($inheriting_extra),
            'parent section contains extra after inserting'
        );
        $this->assertTrue(
            $structure->childZ()->doesContainExtra($inheriting_extra),
            'child section does not contain extra after inserting'
        );
        $this->assertTrue(
            $structure->grandchildZ()->doesContainExtra($inheriting_extra),
            'grandchild section does not contain extra after inserting '
        );

        $inherited_extra = array_pop($structure->grandchildZ()->getExtrasOf('tx_newspaper_Extra_Generic'));
        $this->assertNotEquals(
            $inherited_extra->getExtraUid(), $inherited_extra->getOriginUid(),
            "inherited extra has own uid as origin uid"
        );
    }

    /** @var Tx_newspaper_Controller_SectionModuleController */
    private $controller;

    /**
     * @return array
     */
    private function prepareTestChangeParent() {
        $inheriting_extra = $this->fixture->createExtraToInherit('tx_newspaper_Extra_Generic');
        $s = $this->buildInheritanceStructure($inheriting_extra);

        // change parent of grandchild section to parent section, see if extras change and all
        $this->callChangeParent($s->grandchildS(), $s->parentS());

        // reinstantiate everything to make sure the changes have taken place
        $s = new InheritanceStructure($this->fixture);
        $inheriting_extra = tx_newspaper_Extra_Factory::getInstance()->create($inheriting_extra->getExtraUid());
        return array($inheriting_extra, $s);
    }

}
?>
