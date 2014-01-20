<?php
/**
 * @author Lene Preuss <lene.preuss@gmail.com>
 */

require_once('class.fixture.php');


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
