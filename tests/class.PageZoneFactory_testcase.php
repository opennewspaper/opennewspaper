<?php
/*
 * Created on Oct 17, 2013
 *
 * Author: Lene Preuss <lene.preuss@gmail.com>
 */

require_once(PATH_typo3conf . 'ext/newspaper/Classes/Controller/SectionModuleController.php');
require_once(PATH_typo3conf . 'ext/newspaper/Classes/private/class.tx_newspaper_pagezone_factory.php');
require_once(PATH_typo3conf . 'ext/newspaper/tests/class.tx_newspaper_database_testcase.php');

class test_PageZoneFactory_testcase extends tx_newspaper_database_testcase {

    public function test_createPageZone() {
        $uid = $this->getRandomPageZoneUid();
        $page_zone = tx_newspaper_PageZone_Factory::getInstance()->create($uid);
        $this->assertTrue(
            $page_zone instanceof tx_newspaper_PageZone,
            'page zone factory returned something that is not a page zone'
        );
        $this->assertTrue(
            $page_zone instanceof tx_newspaper_PageZone_Page,
            'page zone factory returned something that is not a page zone on a page'
        );
    }

    public function test_pageZoneIsUnique() {
        $uid = $this->getRandomPageZoneUid();
        $page_zone_1 = tx_newspaper_PageZone_Factory::getInstance()->create($uid);
        $page_zone_2 = tx_newspaper_PageZone_Factory::getInstance()->create($uid);
        $this->checkAttributesAreEqual($page_zone_1, $page_zone_2);

        $page_zone_1->setAttribute('inherits_from', $page_zone_1->getAttribute('inherits_from')+1);
        $this->assertTrue(
            $page_zone_1->getAttribute('inherits_from') == $page_zone_2->getAttribute('inherits_from'),
            "Changing attribute in one page zone does not affect another page zone"
        );

    }

    public function test_addExtraToPageZone() {
        $uid = $this->getRandomPageZoneUid();
        $page_zone_1 = tx_newspaper_PageZone_Factory::getInstance()->create($uid);
        $page_zone_2 = tx_newspaper_PageZone_Factory::getInstance()->create($uid);

        $extra = $this->fixture->createExtraToInherit('tx_newspaper_Extra_Textbox');
        $page_zone_1->insertExtraAfter($extra);
        $this->assertTrue($page_zone_1->doesContainExtra($extra), 'inserting extra failed');

        $this->assertTrue(
            $page_zone_2->doesContainExtra($extra),
            'inserting extra failed to update other page zone'
        );
    }

    private function getRandomPageZoneUid() {
        $pz = array_pop($this->fixture->getPageZones());
        return $pz->getAbstractUid();
    }

    private function checkAttributesAreEqual(tx_newspaper_Pagezone $pagezone_1, tx_newspaper_Pagezone $pagezone_2) {
        foreach (tx_newspaper::getAttributes($pagezone_1) as $attribute) {

            if ($attribute == 'uid') continue;

            $this->assertEquals(
                $pagezone_1->getAttribute($attribute), $pagezone_2->getAttribute($attribute),
                "Attribute $attribute: " . $pagezone_1->getAttribute($attribute) . " !=  " . $pagezone_1->getAttribute($attribute)
            );
        }
    }

}
?>
