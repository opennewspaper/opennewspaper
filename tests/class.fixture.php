<?php
/**
 *  \file class.hierarchy.php
 *
 *  This file is part of the TYPO3 extension "newspaper".
 *
 *  Copyright notice
 *
 *  (c) 2008 Lene Preuss, Oliver Schroeder, Samuel Talleux <lene.preuss@gmail.com, oliver@schroederbros.de, samuel@talleux.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 *
 *  \author Lene Preuss <lene.preuss@gmail.com>
 *  \date Apr 28, 2009
 */

/// @todo brief description
/** @todo long description
 */
class tx_newspaper_fixture {

    public function __construct() {
        $this->createSectionHierarchy();
        $this->createTypo3Pages();
        $this->createPages();
        $this->createPageZones();
        $this->createArticleList();
        $this->createExtras();
        $this->createArticles();
    }

    /** For whatever reason, __destruct is not automatically called when the
     *  unit test is over. This function must be called explicitly to clean up.
     */
    public function removeAllJunkManually() {
        $this->removeArticles();
        $this->removeArticleList();
        $this->removeExtras();
        $this->removePageZones();
        $this->removePages();
        $this->removeSectionHierarchy();
        ///    Make sure you got all records
        foreach ($this->delete_all_query as $query) {
            $res = $GLOBALS['TYPO3_DB']->sql_query($query);
            $res || die('Aaaargh!');
        }

    }

    /**
     * @param $class
     * @return tx_newspaper_Extra
     */
    public function createExtraToInherit($class) {
        /** @var tx_newspaper_Extra $extra */
        $extra = new $class();
        $extra->store();
#        $this->assertGreaterThan(0, intval($extra->getUid()),      "uid is " . $extra->getUid());
#        $this->assertGreaterThan(0, intval($extra->getExtraUid()), "extra uid is " . $extra->getExtraUid());

        return tx_newspaper_Extra_Factory::getInstance()->create($extra->getExtraUid());
    }


    /** @return tx_newspaper_PageZoneType[] */
    public function getPageZoneTypes() {
        $uids = tx_newspaper::selectRows('uid', $this->pagezonetype_table);
        $pagezonetypes = array();
        foreach ($uids as $uid) {
            $pagezonetypes[] = new tx_newspaper_PageZoneType($uid['uid']);
        }
        return $pagezonetypes;
    }

    /** @return tx_newspaper_PageZone[] */
    public function getPageZones() {
        if (!$this->pagezones) {
            foreach ($this->pagezone_uids as $uid) {
                $this->pagezones[] = tx_newspaper_PageZone_Factory::getInstance()->create($uid);
            }
        }
        return $this->pagezones;
    }

    /**
     * @param $parent_section
     * @return tx_newspaper_PageZone
     */
    public function getRandomPageZoneForPlacement(tx_newspaper_Section $parent_section) {
        $pagetype = array_pop($this->getPageTypes());
        $page = $parent_section->getSubPage($pagetype);
        return $page->getPageZone($this->getRandomPageZoneTypeForInheritance());
    }

    /**
     * @return tx_newspaper_PageZoneType
     */
    private function getRandomPageZoneTypeForInheritance() {
        return array_shift($this->getPageZoneTypes());
    }

    public function getPageZoneWithoutInheritance() {
        return tx_newspaper_PageZone_Factory::getInstance()->create($this->pagezone_without_inheritance_uid);
    }

    public function getPageZonePageUid() {
        return $this->pagezone_page_uids[0];
    }

    /** @return tx_newspaper_Page[] */
    public function getPages() {
        if (!$this->pages) {
            foreach ($this->page_uids as $uid) {
                $this->pages[] = new tx_newspaper_Page(intval($uid));
            }
        }
        return $this->pages;
    }

    /** @return tx_newspaper_PageType[] */
    public function getPageTypes() {
        return array_map(
            function($uid) { return new tx_newspaper_PageType(intval($uid['uid'])); },
            tx_newspaper::selectRows('uid', $this->pagetype_table)
        );
    }

    public function getParentSection() {
        return new tx_newspaper_Section($this->getParentSectionUid());
    }

    public function getAllSections() {
        return $this->getParentSection()->getChildSections(true);
    }

    public function getParentSectionUid() {
        return $this->section_uids[0];
    }

    public function getParentSectionPid() {
        return $this->section_data[0]['pid'];
    }

    public function getParentSectionName() {
        return $this->section_data[0]['section_name'];
    }

    public function getArticlelistUid() {
        return $this->articlelist_id;
    }

    public function getAbstractArticlelistUid() {
        return $this->abstract_articlelist_id;
    }

    public function getArticleUid(){
        return $this->article_uid;
    }

    public function getFirstExtraUid() {
        return $this->extra_uids[0];
    }

    public function getExtraUids() {
        return $this->extra_uids;
    }

    /** @return tx_newspaper_Extra[] */
    public function getExtras() {
        return array_map(
            function($uid) { return tx_newspaper_Extra_Factory::getInstance()->create($uid); },
            $this->getExtraUids()
        );
    }

    /** @return tx_newspaper_Extra[] */
    public function getExtrasOf($class) {
        return array_filter(
            $this->getExtras(),
            function($extra) use($class) { return $extra instanceof $class; }
        );
    }

    /** @return tx_newspaper_Extra */
    public function getFirstExtraOf($class) {
        return array_shift($this->getExtrasOf($class));
    }

    public function getPageUid() {
        return $this->page_uids[0];
    }

    public function getFirstUidOf($table) {
        $uids = tx_newspaper_DB::getInstance()->selectRows('uid', $table, '1', '', '', '1');
        return intval($uids[0]['uid']);
    }

    ////////////////////////////////////////////////////////////////////////////

    private function createTypo3Pages() {
        // Create sysfolder (uid 2574) for storing records and other newspaper related pages (like dossier page)
        foreach($this->typo3_newspaper_pages_data as $page) {
            tx_newspaper::insertRows($this->typo3_pages_table, $page);
        }
    }

    private function createSectionHierarchy() {
        foreach ($this->section_data as $i => $section) {
            $section['parent_section'] = $this->section_uids[sizeof($this->section_uids)-1];
            $uid = tx_newspaper::insertRows($this->section_table, $section);
            $this->section_uids[] = $uid;
            $this->typo3_pages_data[$i]['tx_newspaper_associated_section'] = $uid;
            $uid = tx_newspaper::insertRows($this->typo3_pages_table, $this->typo3_pages_data[$i]);
        }
    }

    private function createArticleList() {
            $this->articlelist_id = tx_newspaper::insertRows($this->articlelistauto_table, $this->articlelistauto_data);

            $this->articlelist_data['section_id'] = $this->getParentSectionUid();
            $this->articlelist_data['list_uid'] = $this->articlelist_id;
            $this->articlelist_data['list_table'] = $this->articlelistauto_table;
            $this->abstract_articlelist_id = tx_newspaper::insertRows($this->articlelist_table, $this->articlelist_data);
    }

    private function removeArticleList() {
        $this->delete($this->articlelistauto_table, $this->articlelist_id);
        $this->delete($this->articlelist_table, 'list_uid = '.$this->articlelist_id);
    }

    private function createArticles() {
        $this->article_uid = tx_newspaper::insertRows($this->article_table, $this->article_data);
        $this->article2section_uid = tx_newspaper::insertRows(
            'tx_newspaper_article_sections_mm',
            array(
                'uid_local' => $this->article_uid,
                'uid_foreign' => $this->getParentSectionUid()
            ));

        $this->related_article_uid = tx_newspaper::insertRows($this->article_table, $this->related_article_data);
        $parent_section = new tx_newspaper_Section($this->getParentSectionUid());
        $child_sections = $parent_section->getChildSections(true);
        foreach ($child_sections as $child_section) {
            tx_newspaper::insertRows(
                'tx_newspaper_article_sections_mm',
                array(
                    'uid_local' => $this->related_article_uid,
                    'uid_foreign' => $child_section->getUid()
                ));
        }

        tx_newspaper::insertRows(
                'tx_newspaper_article_related_mm',
                array(
                    'uid_local' => $this->article_uid,
                    'uid_foreign' => $this->related_article_uid,
                )
        );

        $this->createControlTag();
    }

    private function removeArticles() {
        $this->delete($this->article_table, $this->article_uid);
        $this->delete('tx_newspaper_article_sections_mm', 'uid_local ='.$this->article_uid.' and uid_foreign = '.$this->getParentSectionUid());
    }

    private function createControlTag() {
        $this->control_tag_id = tx_newspaper::insertRows(
            'tx_newspaper_tag', $this->control_tag_data
        );
        tx_newspaper::insertRows(
                'tx_newspaper_article_tags_mm',
                array(
                    'uid_local' => $this->article_uid,
                    'uid_foreign' => $this->control_tag_id
                )
        );

    }

    private function createPages() {
        foreach ($this->pagetype_data as $pagetype) {
            $this->pagetype_uids[] = tx_newspaper::insertRows($this->pagetype_table, $pagetype);
        }
        $sf = tx_newspaper_Sysfolder::getInstance();
        $pid = $sf->getPid(new tx_newspaper_Page());
        foreach ($this->section_uids as $section_uid) {
            foreach ($this->page_data as $i => $page) {
                $page['pid'] = $pid;
                $page['section'] = $section_uid;
                $page['pagetype_id'] = $this->pagetype_uids[$i];
#                // $page['inherit_pagetype_id'] = ...?;
                $this->page_uids[] = tx_newspaper::insertRows($this->page_table, $page);
            }
        }
    }

    /** Create a number of page zones (one for each page zone type defined in
     *  $this->pagezonetype_data) for every page created above.
     *  The page zone types are created first.
     *
     *  @todo Create page zones which explicitly inherit from another page zone
     *      under the same Page
     *  @todo Create page zones which don't inherit from another page zone
     */
    private function createPageZones() {
        foreach ($this->pagezonetype_data as $pagezonetype) {
            $this->pagezonetype_uids[] = tx_newspaper::insertRows($this->pagezonetype_table, $pagezonetype);
        }
        foreach ($this->page_uids as $page_uid) {
            foreach ($this->pagezonetype_uids as $pagezonetype_uid) {
                $this->createPageZone($pagezonetype_uid, $page_uid);
            }
        }
        $this->createPageZoneWithoutInheritance();
    }

    /**
     * @param $pagezonetype_uid
     * @param $page_uid
     */
    private function createPageZone($pagezonetype_uid, $page_uid) {
        $this->pagezone_page_data['pagezonetype_id'] = $pagezonetype_uid;
        $concrete_uid = tx_newspaper::insertRows($this->pagezone_page_table, $this->pagezone_page_data);
        $this->pagezone_page_uids[] = $concrete_uid;
        //    the c'tor creates the parent record as well
        $temp_pagezone = new tx_newspaper_Pagezone_Page($concrete_uid);
        $abstract_uid = $temp_pagezone->getPageZoneUID();
        $this->pagezone_uids[] = $abstract_uid;
        //  connect the abstract record to the page
        tx_newspaper::updateRows(
            $this->pagezone_table,
            "uid = $abstract_uid",
            array('page_id' => $page_uid)
        );
    }

    private function createPageZoneWithoutInheritance() {
        $this->createPageZone(array_pop($this->getPageZoneTypes())->getUid(), 0);
        $this->pagezone_without_inheritance_uid = $this->pagezone_uids[sizeof($this->pagezone_uids)-1];
        $pagezone = tx_newspaper_PageZone_Factory::getInstance()->create($this->pagezone_without_inheritance_uid);
        $pagezone->setAttribute('inherits_from', -1);
        $pagezone->store();
    }

    private function createExtras() {
//        $pagezone_uid = $this->pagezone_uids[0];
        foreach ($this->pagezone_uids as $pagezone_uid)
        {
            $pagezone = tx_newspaper_PageZone_Factory::getInstance()->create($pagezone_uid);

            $this->createImageExtras($pagezone);
            $this->createArticlelistExtras($pagezone);
        }
        $this->createSectionlistExtras();
        $this->createBrokenExtra();
    }

    private function createImageExtras(tx_newspaper_Pagezone $pagezone) {
        foreach($this->image_extra_data as $i => $extra) {
            $this->createExtraFromData($this->image_extra_table, $extra, $this->extra_pos[$i], $pagezone);
        }
    }
    private function createArticlelistExtras(tx_newspaper_Pagezone $pagezone) {
        foreach($this->articlelist_extra_data as $i => $extra) {
            $extra['articlelist'] = $this->getAbstractArticlelistUid();
            $this->createExtraFromData($this->articlelist_extra_table, $extra, $this->extra_pos[$i], $pagezone);
        }
    }

    private function createSectionlistExtras() {
        $pagezonetype_for_sectionlist = new tx_newspaper_PageZoneType($this->pagezonetype_uids[0]);
        foreach ($this->section_uids as $section_uid) {
            $section = new tx_newspaper_Section($section_uid);
            $pages = $section->getActivePages();
            foreach ($pages as $page) {
                if ($page->getPageType()->getAttribute('is_article_page')) continue;
                $pagezone = $page->getPageZone($pagezonetype_for_sectionlist);
                $this->createExtraFromData($this->sectionlist_extra_table, $this->sectionlist_extra_data, 0, $pagezone);
            }
        }
    }

    private function createExtraFromData($table, array $concrete_extra_data, $position, tx_newspaper_Pagezone $pagezone) {
        $extra_uid = tx_newspaper::insertRows($table, $concrete_extra_data);
        $extra_object = new $table($extra_uid);

        tx_newspaper::updateRows(
            $this->extra_table,
            'uid = ' . $extra_object->getExtraUid(),
            array(
                'position' => $position,
                'origin_uid' => $extra_object->getExtraUid(),
            )
        );

        tx_newspaper::insertRows(
            $pagezone->getExtra2PagezoneTable(),
            array(
                'uid_local' => $pagezone->getUid(),
                'uid_foreign' => $extra_object->getExtraUid()
            ));

        $this->extra_uids[] = $extra_object->getExtraUid();

    }

    private function createBrokenExtra() {

        tx_newspaper::insertRows(
            $this->extra_table,
            array(
                 'uid' => self::broken_extra_uid,
                'extra_table' => self::broken_extra_table
            )
        );
    }

    private function removeExtras() {
        $pagezone = tx_newspaper_PageZone_Factory::getInstance()->create($this->pagezone_uids[0]);

        foreach ($this->extra_uids as $uid) {
            $abstract_uids = tx_newspaper::selectRows(
                'uid', $this->extra_table,
                'extra_table = \'' . $this->concrete_extra_table . '\' AND extra_uid = ' . $uid);
            foreach ($abstract_uids as $abstract_uid) {
                $this->delete(
                    $pagezone->getExtra2PagezoneTable(),
                    'uid_foreign = ' . $abstract_uid['uid']
                );
                $this->delete(
                    $this->extra_table,
                    'uid = ' . $abstract_uid['uid']
                );
            }
            $this->delete($this->concrete_extra_table, $uid);
        }
    }

    private function removeSectionHierarchy() {
        $this->delete($this->section_table, $this->section_uids);
    }

    private function removePages() {
        $this->delete($this->page_table, $this->page_uids);
        $this->delete($this->pagetype_table, $this->pagetype_uids);
    }

    private function removePageZones() {
        $this->delete($this->pagezone_table, $this->pagezone_uids);
        $this->delete($this->pagezone_page_table, $this->pagezone_page_uids);
        $this->delete($this->pagezonetype_table, $this->pagezonetype_uids);
    }

    const broken_extra_uid = 1000000;
    const broken_extra_table = 'this_is_not_an_existing_table';

    private $delete_all_query = array(
        "DELETE FROM `tx_newspaper_section` WHERE section_name LIKE 'Unit Test%'",
        "DELETE FROM `tx_newspaper_section` WHERE deleted",
        "DELETE FROM `tx_newspaper_pagetype` WHERE type_name LIKE 'Unit Test%'",
        "DELETE FROM `tx_newspaper_pagetype` WHERE deleted",
        "DELETE FROM `tx_newspaper_page` WHERE template_set LIKE 'Unit Test%'",
        "DELETE FROM `tx_newspaper_page` WHERE deleted",
        "DELETE FROM `tx_newspaper_pagezonetype` WHERE type_name LIKE 'Unit Test%'",
        "DELETE FROM `tx_newspaper_pagezonetype` WHERE deleted",
        "DELETE FROM `tx_newspaper_pagezone_page` WHERE pagezone_id LIKE 'Unit Test%'",
        "DELETE FROM `tx_newspaper_pagezone_page` WHERE deleted",
        "DELETE FROM `tx_newspaper_pagezone` WHERE deleted",
        "DELETE FROM `tx_newspaper_extra_image` WHERE title LIKE 'Unit Test%'",
        "DELETE FROM `tx_newspaper_extra_image` WHERE deleted",
        "DELETE FROM `tx_newspaper_extra_articlelist` WHERE description LIKE 'Unit Test%'",
        "DELETE FROM `tx_newspaper_extra_articlelist` WHERE deleted",
        "DELETE FROM `tx_newspaper_extra` WHERE deleted",
        "DELETE FROM `tx_newspaper_tag` WHERE tag = 'control tag'",
    );

    private function delete($table, $uids_or_where) {
        if(is_array($uids_or_where)) {
            $uids_or_where = 'uid IN (' . implode(', ', $uids_or_where) . ')';
        } else if(is_int($uids_or_where)) {
            $uids_or_where = 'uid = '.$uids_or_where;
        }
        $GLOBALS['TYPO3_DB']->exec_DELETEquery($table, $uids_or_where);
    }

    private $section_table = 'tx_newspaper_section';
    private $section_uids = array();
    /// A hierarchy of three sections is generated
    private $section_data = array(
        array(
            'pid' => '2828',
            'tstamp' => '1234567890',
            'crdate' => '1234567890',
            'cruser_id' => '1',
            'sorting' => '1024',
            'deleted' => '0',
            'section_name' => 'Unit Test - Big Daddy Section',
            'parent_section' => '',
            'articlelist' => '',
            'template_set' => '',
            'pagetype_pagezone' => ''
        ),
        array(
            'pid' => '2828',
            'tstamp' => '1234567890',
            'crdate' => '1234567890',
            'cruser_id' => '1',
            'sorting' => '2048',
            'deleted' => '0',
            'section_name' => 'Son Section',
            'parent_section' => '',
            'articlelist' => '',
            'template_set' => '',
            'pagetype_pagezone' => ''
        ),
        array(
            'pid' => '2828',
            'tstamp' => '1234567890',
            'crdate' => '1234567890',
            'cruser_id' => '1',
            'sorting' => '4096',
            'deleted' => '0',
            'section_name' => 'Little Grandchild Section',
            'parent_section' => '',
            'articlelist' => '',
            'template_set' => '',
            'pagetype_pagezone' => ''
        ),
    );

    private $typo3_pages_table = 'pages';
    private $typo3_pages_data = array(
        array(
            'pid' => '2828',
            'tstamp' => '1234567890',
            'crdate' => '1234567890',
            'cruser_id' => '1',
            'sorting' => '1024',
            'deleted' => '0',
            'title' => 'Unit Test - Big Daddy Section Page',
        ),
        array(
            'pid' => '2828',
            'tstamp' => '1234567890',
            'crdate' => '1234567890',
            'cruser_id' => '1',
            'sorting' => '2048',
            'deleted' => '0',
            'title' => 'Son Section Page',
        ),
        array(
            'pid' => '2828',
            'tstamp' => '1234567890',
            'crdate' => '1234567890',
            'cruser_id' => '1',
            'sorting' => '4096',
            'deleted' => '0',
            'title' => 'Little Grandchild Section Page',
        ),
    );
    private $typo3_newspaper_pages_data = array(
        array(
            'uid' => 2574,              // Sysfolder for Newspaper records
            'pid' => '2828',
            'tstamp' => '1234567890',
            'crdate' => '1234567890',
            'cruser_id' => '1',
            'sorting' => '999',
            'deleted' => '0',
            'title' => 'Newspaper',
            'doktype' => '254',
        ),
    );

    private $articlelist_table = 'tx_newspaper_articlelist';
    private $articlelist_id = null;
    private $abstract_articlelist_id = null;
    private $articlelistauto_table = 'tx_newspaper_articlelist_semiautomatic';
    private $articlelist_data = array(
        'pid' => '2827',
        'tstamp' => '1234567890',
        'crdate' => '1234567890',
        'cruser_id' => '1',
        'sorting' => '1024',
        'deleted' => '0',
        'hidden' => '0',
        'starttime' => '0',
        'endtime' => '0',
    );

    private $articlelistauto_data = array (
        'pid' => '2827',
        'tstamp' => '1234567890',
        'crdate' => '1234567890',
        'cruser_id' => '1',
        );

    private $pagetype_table = 'tx_newspaper_pagetype';
    private $pagetype_uids = array();
    /// Two pagetypes are created to associate with the pages
    private $pagetype_data = array(
        array(
            'pid' => '2827',
            'tstamp' => '1234567890',
            'crdate' => '1234567890',
            'cruser_id' => '1',
            'sorting' => '1024',
            'deleted' => '0',
            'type_name' => 'Unit Test - Seitentyp 1',
            'normalized_name' => 'seitentyp_1',
            'get_var' => '',
            'get_value' => '',
        ),
        array(
            'pid' => '2827',
            'tstamp' => '1234567890',
            'crdate' => '1234567890',
            'cruser_id' => '1',
            'sorting' => '2048',
            'deleted' => '0',
            'type_name' => 'Unit Test - Seitentyp 2',
            'normalized_name' => 'seitentyp_2',
            'get_var' => 'blah',
            'get_value' => '1',
        ),
        array(
            'pid' => '2827',
            'tstamp' => '1234567890',
            'crdate' => '1234567890',
            'cruser_id' => '1',
            'sorting' => '2048',
            'deleted' => '0',
            'type_name' => 'Unit Test - Artikelseite',
            'normalized_name' => 'artikelseite',
            'get_var' => 'art',
            'is_article_page' => 1,
        ),
    );

    private $page_table = 'tx_newspaper_page';
    private $page_uids = array();
    /// There are two pages below each section
    private $page_data = array(
        array(
            'pid' => '2474',
            'tstamp' => '1234567890',
            'crdate' => '1234567890',
            'cruser_id' => '1',
            'deleted' => '0',
            'section' => '',
            'pagetype_id' => '',
            'inherit_pagetype_id' => '',
            'template_set' => 'Unit Test',
        ),
        array(
            'pid' => '2474',
            'tstamp' => '1234567890',
            'crdate' => '1234567890',
            'cruser_id' => '1',
            'deleted' => '0',
            'section' => '',
            'pagetype_id' => '',
            'inherit_pagetype_id' => '',
            'template_set' => 'Unit Test',
        ),
        array(
            'pid' => '2474',
            'tstamp' => '1234567890',
            'crdate' => '1234567890',
            'cruser_id' => '1',
            'deleted' => '0',
            'section' => '',
            'pagetype_id' => '',
            'inherit_pagetype_id' => '',
            'template_set' => 'Unit Test',
        ),
    );

    /// The Pages in the hierarchy as a flat array of objects
    private $pages = array();

    private $pagezonetype_table = 'tx_newspaper_pagezonetype';
    private $pagezonetype_data = array(
        array(
            'pid' => '2822',
            'tstamp' => '1234567890',
            'crdate' => '1234567890',
            'cruser_id' => '1',
            'sorting' => '1024',
            'deleted' => '0',
            'type_name' => 'Unit Test - Hauptspalte',
            'normalized_name' => 'seitenbereich1',
            'is_article' => '',
        ),
        array(
            'pid' => '2822',
            'tstamp' => '1234567890',
            'crdate' => '1234567890',
            'cruser_id' => '1',
            'sorting' => '1024',
            'deleted' => '0',
            'type_name' => 'Unit Test - Rechte Spalte',
            'normalized_name' => 'seitenbereich2',
            'is_article' => '',
        ),
        array(
            'pid' => '2822',
            'tstamp' => '1234567890',
            'crdate' => '1234567890',
            'cruser_id' => '1',
            'sorting' => '1024',
            'deleted' => '0',
            'type_name' => 'Unit Test - Artikel',
            'normalized_name' => 'artikel',
            'is_article' => '1',
        ),
    );
    private $pagezonetype_uids = array();

    private $pagezone_table = 'tx_newspaper_pagezone';
    private $pagezone_uids = array();
    private $pagezone_page_table = 'tx_newspaper_pagezone_page';
    private $pagezone_page_uids = array();
    private $pagezone_page_data = array(
        'pid'        => '2476',
        'tstamp'    => '1234567890',
        'crdate'    => '1234567890',
        'cruser_id'    => '1',
        'deleted'    => '0',
        'pagezonetype_id' => '',
        'pagezone_id' => 'Unit Test',
        'extras'    => '',
        'template_set' => '',
        'inherits_from' => '0'
    );

    private $pagezone_without_inheritance_uid;

    /// The Page Zones in the hierarchy as a flat array of objects
    private $pagezones = array();

    private $extra_uids = array();
    private $extra_table = 'tx_newspaper_extra';
    private $image_extra_table = 'tx_newspaper_extra_image';
    private $extra2pagezone_table = 'tx_newspaper_pagezone_page_extras_mm';
    public $image_extra_data = array(
        array(
            'pid' => 2573,
            'tstamp' => 1234567890,
            'crdate' => 1234567890,
            'cruser_id' => 1,
            'deleted' => 0,
            'hidden' => 0,
            'starttime' => 0,
            'endtime' => 0,
            'title' => "Unit Test - Image Title 1",
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
            'title' => "Unit Test - Image Title 2",
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
            'title' => "Unit Test - Image Title 3",
            'image_file' => "lolcatsdotcomoh5o6d9hdjcawys6.jpg",
            'caption' => "caption[5]",
        ),
    );

    private $articlelist_extra_table = 'tx_newspaper_extra_articlelist';
    private $articlelist_extra_data = array(
        array(
            'pid' => 2573,
            'tstamp' => 1234567890,
            'crdate' => 1234567890,
            'cruser_id' => 1,
            'deleted' => 0,
            'starttime' => 0,
            'endtime' => 0,
            'short_description' => "Unit Test - Article list extra 1",
            'articlelist' => 0,
            'first_article' => 1,
            'num_articles' => 10,
        ),
        array(
            'pid' => 2573,
            'tstamp' => 1234567890,
            'crdate' => 1234567890,
            'cruser_id' => 1,
            'deleted' => 0,
            'starttime' => 0,
            'endtime' => 0,
            'short_description' => "Unit Test - Article list extra 2",
            'articlelist' => 0,
            'first_article' => 1,
            'num_articles' => 10,
        ),
    );

    private $sectionlist_extra_table = 'tx_newspaper_extra_sectionlist';
    private $sectionlist_extra_data = array(
        'pid' => 2573,
        'tstamp' => 1234567890,
        'crdate' => 1234567890,
        'cruser_id' => 1,
        'deleted' => 0,
        'starttime' => 0,
        'endtime' => 0,
        'first_article' => 1,
        'num_articles' => 10,
    );

    private $article_table = 'tx_newspaper_article';
    private $article2section_table = 'tx_newspaper_article_sections_mm';
    private $article_uid = null;
    public $article_data = array(
        'pid' => 2574,
        'tstamp' => 1234806796,
        'crdate' => 1232647355,
        'cruser_id' => 1,
        'deleted' => 0,
        'hidden' => 0,
        'starttime' => 0,
        'endtime' => 0,
        'title' => "Nummer eins!",
        'extras' => 0,
        'teaser' => "Hey, ein neuer Artikel ist im Lande!",
        'bodytext' => "<p>Und was fuer einer! Er besteht zu 100% aus Blindtext! Nicht ein einziges sinnvolles Wort. Das soll mir mal einer nachmachen.</p>\r\n<p>  Hier kommt noch etwas mehr Testtext, so dass die erste Zeile nicht so alleine da steht. Und noch mehr Text und noch mehr und noch mehr und... (ad infinitum), denn wir wollen ja einen realistischen Artikel simulieren und da steht ja meistens auch ziemlich viel Text. In manchen Artikeln stehen sogar noch mehr als zwei Absaetze, und diese auch noch prallvoll mit Text, deshalb muss in diesen Blindtext auch ne ganze Menge Text und da kann ich ja nicht schon jetzt, nach nur zwei Absaetzen, aufhoeren Text zu schreiben.</p>\r\n<p>Also darum noch ein dritter Absatz mit noch mehr Text. Ich frage mich, wie oft das Wort \"Text\" schon in diesem Text aufgetaucht ist? Oh, nach dem letzten Satz kann man gleich noch zwei zum Text-Zaehler hinzuzaehlen. Upps, das hab ich gleich noch mal \"Text\" geschrieben.</p>\r\n<p></p>",
        'author' => "Test Text",
        'sections' => 1,
        'source_id' => 1,
        'source_object' => "",
        'name' => "",
        'is_template' => 0,
        'pagezonetype_id' => 1,
        'workflow_status' => 0,
        'articletype_id' => 0,
        'inherits_from' => 0,
    );

    private $related_article_uid = null;
    private $related_article_data = array(
        'pid' => 2574,
        'tstamp' => 1234806796,
        'crdate' => 1232647355,
        'cruser_id' => 1,
        'deleted' => 0,
        'hidden' => 0,
        'starttime' => 0,
        'endtime' => 0,
        'title' => "Nummer zwei!",
        'extras' => 0,
        'teaser' => "Dieser Artikel ist verknuepft mit Artikel eins!",
        'bodytext' => "<p>Juhu!</p>",
        'author' => "Test Text",
        'sections' => 2,
        'source_id' => 1,
        'source_object' => "",
        'name' => "",
        'is_template' => 0,
        'pagezonetype_id' => 1,
        'workflow_status' => 0,
        'articletype_id' => 0,
        'inherits_from' => 0,
    );

    private $control_tag_data = array(
        'tstamp' => 1234806796,
        'crdate' => 1232647355,
        'cruser_id' => 1,
        'deleted' => 0,
        'tag' => 'control tag',
        'tag_type' => 2,
        'ctrltag_cat' => 1
    );
    private $control_tag_id = 0;


    private $extra_pos = array(
        1024, 2048, 4096
    );


}
?>
