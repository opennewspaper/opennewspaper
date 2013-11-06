<?php
/*
 * Created on Oct 17, 2013
 *
 * Author: Lene Preuss <lene.preuss@gmail.com>
 */

require_once(PATH_typo3conf . 'ext/newspaper/Classes/Controller/SectionModuleController.php');
require_once(PATH_typo3conf . 'ext/newspaper/tests/class.tx_newspaper_database_testcase.php');

class test_SectionModuleController_testcase extends tx_newspaper_database_testcase {

    function setUp() {
        parent::setUp();
        $this->controller = new Tx_newspaper_Controller_SectionModuleController();
    }
    
    function tearDown() {
    }

    public function test_changeParent() {

        $parent_section = $this->fixture->getParentSection();
        $sections = $this->fixture->getAllSections();
        $grandchild_section = array_pop($sections);
        $child_section = array_pop($sections);

        $parent_zone = $this->fixture->getRandomPageZoneForPlacement($parent_section);
        $child_zone =  $this->fixture->getRandomPageZoneForPlacement($child_section);
        $grandchild_zone =  $this->fixture->getRandomPageZoneForPlacement($grandchild_section);

        $inheriting_extra = $this->fixture->createExtraToInherit('tx_newspaper_Extra_Generic');

        // insert extra on child section that must be gone from the grandchild section after changing parent
        $child_zone->insertExtraAfter($inheriting_extra);
        $this->assertTrue(
            $child_zone->doesContainExtra($inheriting_extra),
            'child section does not contain extra after inserting'
        );
        $this->assertTrue(
            $grandchild_zone->doesContainExtra($inheriting_extra),
            'grandchild section does not contain extra after inserting'
        );
        $this->assertFalse(
            $parent_zone->doesContainExtra($inheriting_extra),
            'parent section contains extra after inserting'
        );
        $inherited_extra = array_pop($grandchild_zone->getExtrasOf('tx_newspaper_Extra_Generic'));
        $this->assertNotEquals(
            $inherited_extra->getExtraUid(), $inherited_extra->getOriginUid(),
            "inherited extra has own uid as origin uid"
        );

        // change parent of grandchild section to parent section, see if extras change and all
        $this->callChangeParent($grandchild_section, $parent_section);

        // check all origin uids on grandchild section, must be != extra uids



        $this->skipTest("Not yet implemented");
    }

    /**
     * @param $parent_section
     */
    private function callChangeParent(tx_newspaper_Section $section, tx_newspaper_Section $new_parent_section) {
        $property = new ReflectionProperty($this->controller, 'module_request');
        $property->setAccessible(true);
        $property->setValue($this->controller, array('parent_section' => $new_parent_section->getUid()));

        $method = new ReflectionMethod($this->controller, 'changeParent');
        $method->setAccessible(true);
        $method->invoke($this->controller, $section);
    }

    /** @var Tx_newspaper_Controller_SectionModuleController */
    private $controller;

}
?>
