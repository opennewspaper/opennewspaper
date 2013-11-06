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

        // change parent of grandchild section to parent section, see if extras change and all

        // insert extra on child section that must be gone from the grandchild section after changing parent

        // check all origin uids on grandchild section, must be != extra uids


        $method = new ReflectionMethod($this->controller, 'changeParent');
        $method->setAccessible(true);
        $method->invoke($this->controller, new tx_newspaper_Section($this->fixture->getParentSectionUid()));

        $this->skipTest("Not yet implemented");
    }

    /** @var Tx_newspaper_Controller_SectionModuleController */
    private $controller;
}
?>
