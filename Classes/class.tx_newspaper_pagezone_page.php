<?php
/**
 *  \file class.tx_newspaper_pagezone_page.php
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
 *  \date Jan 8, 2009
 */
 
require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_pagezone.php');

/// A section of a page for an online edition of a newspaper
/** Pages are divided into several independent sections, or zones, such as:
 *  - Left column, containing the main content area (article text, list of 
 *       articles)
 *  - Right column with additional info or ads
 *  - footer area  
 *  A PageZone contains a list of content elements.
 * 
 *  Class tx_newspaper_PageZone implements the tx_newspaper_Extra interface,
 *  because a PageZone can be placed like an Extra.
 * 
 *  Default smarty template:
 *  \include res/templates/tx_newspaper_pagezone_page.tmpl
 */
class tx_newspaper_PageZone_Page extends tx_newspaper_PageZone {

    public function __construct($uid = 0) {

        $timer = tx_newspaper_ExecutionTimer::create();

        parent::__construct($uid);
        if ($uid) {
            if (!self::$lazy_creation) {
                $this->readExtrasForPagezoneID($uid);
            }
            $this->readAttributes($this->getTable(), $uid);
            $this->pagezonetype = new tx_newspaper_PageZoneType($this->attributes['pagezonetype_id']);
            $this->pagezone_uid = $this->createPageZoneRecord();
        }

     }

        /// Convert object to string to make it visible in stack backtraces, devlog etc.
    public function __toString() {
        try {
            return $this->printableName();
        } catch (tx_newspaper_Exception $e) {
            return '... oops, exception thrown: ' . $e;
        }
    }

    public function printableName() {
        $ret = '';
        $page = $this->getParentPage();
        if ($page instanceof tx_newspaper_Page) {
            $section = $page->getParentSection();
            if ($section instanceof tx_newspaper_Section) {
                $ret .= $section->getAttribute('section_name') . '/';
            }
            $ret .= $page->getPageType()->getAttribute('type_name') . '/';
        }
        $ret .= $this->getPageZoneType()->getAttribute('type_name');

        return $ret;
    }


    /// \todo Will this work in the parent class too?
    public function __clone() {
         /*  ensure attributes are loaded from DB. readExtraItem() isn't  
          *  called here because maybe the content is already there and it would
          *  cause the DB operation to be done twice.
          */
        $this->getAttribute('uid');
        
        //  unset the UID so the object can be written to a new DB record.
         $this->attributes['uid'] = 0;
         $this->setUid(0);

         $this->setAttribute('crdate', time());
         $this->setAttribute('tstamp', time());
         
         /// \todo clone extras
         $old_extras = $this->getExtras();
         $this->extras = array();
         foreach ($old_extras as $old_extra) {
             $this->extras[] = clone $old_extra;
         }
     }

    /**
     *  Render the page zone, containing all extras
     *
     *  @param string $template_set the template set used to render this page (as passed down from
     *  tx_newspaper_Page::render() )
     *  @return string The rendered page as HTML (or XML, if you insist)
     */
    public function render($template_set = '') {

        /// Check whether to use a specific template set
        if ($this->getAttribute('template_set')) {
            $template_set = $this->getAttribute('template_set');
        }

        /// Configure Smarty rendering engine
        if ($template_set) {
            $this->smarty->setTemplateSet($template_set);
        }
        if ($this->getParentPage() && $this->getParentPage()->getPagetype()) {
            $this->smarty->setPageType($this->getParentPage());
        }
        if ($this->getPageZoneType()) {
            $this->smarty->setPageZoneType($this);
        }

        /// Pass global attributes to Smarty
        $this->smarty->assign('class', get_class($this));
        $this->smarty->assign('attributes', $this->attributes);
        $this->smarty->assign('normalized_name', $this->getPageZoneType()->getAttribute('normalized_name'));

        /// Pass the Extras on this page zone, already rendered, to Smarty
        $this->smarty->assign('extras', array_map(
            function(tx_newspaper_Extra $e) use($template_set) { return $e->render($template_set); },
            $this->getExtras()
        ));

        $this->smarty->assign('typoscript', tx_newspaper::getNewspaperTyposcript());

        $rendered = $this->smarty->fetch($this);

        return $rendered;
    }


    /** \todo ensure page zone type is stored correctly
     *  \todo store Extras placed on $this
     */
    public function store() {

        if ($this->getUid()) {
            $this->storeAgain();
        } else {
            $this->storeFirstTime();
        }

        /// Ensure the page zone has an entry in the abstract supertable...
        $pagezone_uid = $this->createPageZoneRecord($this->getUid(), $this->getTable());

        /// ... and is attached to the correct page
        $this->connectToPage($pagezone_uid);

        $this->storeExtras();

        return $this->getUid();

    }

    protected function connectToPage($pagezone_uid) {
        if ($this->getParentPage() instanceof tx_newspaper_Page) {
            tx_newspaper::updateRows(
                'tx_newspaper_pagezone',
                'uid = ' . $pagezone_uid,
                array('page_id' => $this->getParentPage()->getUid())
            );
        }
    }

    /// \todo store Extras placed on $this
    private function storeExtras() {
        if ($this->getExtras()) {
            foreach ($this->extras as $extra) {
                #                t3lib_div::devlog('extra on pagezone', 'newspaper', 0, $extra);
                #                $extra_uid = $extra->store();
                #                $extra_table = $extra->getTable();
                #$this->relateExtra2Article($extra);
            }
            #            throw new tx_newspaper_NotYetImplementedException('store Extras placed on $this');
        }
    }

    ///    Store a newly created page zone
    private function storeFirstTime() {
        if (!$this->pagezonetype instanceof tx_newspaper_PageZoneType) {
            throw new tx_newspaper_IllegalUsageException("Tried to store a page zone without a page zone type");
        }

        $this->attributes['pagezonetype_id'] = $this->pagezonetype->getUid();
        /** \todo If the PID is not set manually, $tce->process_datamap()
         *           fails silently.
         */
        tx_newspaper::setDefaultFields($this, array('crdate', 'tstamp', 'pid', 'cruser_id'));
        unset($this->attributes['query']);

        $this->setUid(
            tx_newspaper::insertRows(
                $this->getTable(), $this->attributes
            )
        );
    }

    private function storeAgain() {
        if (!$this->attributes) {
            $this->readAttributes($this->getTable(), $this->getUid());
        }

        tx_newspaper::setDefaultFields($this, array('tstamp'));
        unset($this->attributes['query']);
        tx_newspaper::updateRows(
            $this->getTable(), 'uid = ' . $this->getUid(), $this->attributes
        );
    }

    /// Default implementation for page zones which do not have paragraphs
    public function changeExtraParagraph(tx_newspaper_Extra $extra, $new_paragraph) {
        $extra->store();
    }


    /**
     *  Get a list of Page Zones to which the inheritance of \p $this can change.
     *
     *  The parent, from which the current Page Zone inherits its Extras, can be altered. This
     *  function lists the Zones it can be altered to:
     *  - The PageZone of the same type in the tx_newspaper_Section which is the parent of the
     *    current Section (this is the default)
     *  - Any PageZone of the same tx_newspaper_PageZoneType as \c $this which lies under a
     *    tx_newspaper_Page in the same tx_newspaper_Section as \c $this. (Expect for page zone $this)
     *
     *  @param bool $siblingsOnly Return sister pagezones only, ignore parent page zone
     *
     *  @return tx_newspaper_PageZone_Page[] List of Page Zones to which the inheritance of \p $this can change.
     *
     */
    public function getPossibleParents($siblingsOnly = false) {

        $zones = array();

        if (!$siblingsOnly) {
            $parent_zone = $this->getParentForPlacement(true);
            if ($parent_zone) $zones[] = $parent_zone;
        }

        if (self::isHorizontalInheritanceEnabled()) {
            $sister_pages = $this->getParentPage()->getParentSection()->getActivePages();
            foreach ($sister_pages as $page) {
                if ($sister_zone = $page->getPageZone($this->getPageZoneType())) {
                    if ($sister_zone->getParentPage()->getPageType() != $this->getParentPage()->getPageType()) {
                        $zones[] = $sister_zone;
                    }
                }
            }
        }

        return $zones;
    }

    /// Get the hierarchy of Page Zones from which the current Zone inherits the placement of its extras
    /** @param bool $including_myself If true, add $this to the list
     *  @param tx_newspaper_PageZone_Page[] $hierarchy List of already found parents (for recursive calling)
     *  @return tx_newspaper_PageZone_Page[] Inheritance hierarchy of Page Zones from which the current Page
     *              Zone inherits, ordered upwards
     */
    public function getInheritanceHierarchyUp($including_myself = true,
                                              $hierarchy = array()) {
        if ($including_myself) $hierarchy[] = $this;
        if ($this->getParentForPlacement()) {
            return $this->getParentForPlacement()->getInheritanceHierarchyUp(true, $hierarchy);
        } else return $hierarchy;
    }

    /// Set whether PageZones down the inheritance hierarchy inherit this Extra
    /** If the inheritance mode is changed to false, the Extra must be removed
     *  from all PageZones inheriting from $this (if it's  already present there).
     *  If it is set to true, it must be copied to all inheriting PageZones. Or,
     *  if it is already present there (because the inheritance status was
     *  toggled to false previously), the Extras must be reactivated and placed
     *  according to their origin_uid.
     *
     *  @param tx_newspaper_Extra $extra The Extra whose inheritance status is changed
     *  @param bool $inherits Whether to pass the Extra down the hierarchy
     *  @exception tx_newspaper_InconsistencyException If $extra is not present on the PageZone
     */
    public function setInherits(tx_newspaper_Extra $extra, $inherits = true) {

        //    Check if the Extra is really present. An exception is thrown if not.
        $this->indexOfExtra($extra);

        if ($inherits == $extra->getAttribute('is_inheritable')) return;

        $extra->setAttribute('is_inheritable', $inherits);
        $extra->store();

        foreach($this->getInheritanceHierarchyDown(false) as $inheriting_pagezone) {
            $copied_extra = $inheriting_pagezone->findExtraByOriginUID($extra->getOriginUid(), true);

            if ($copied_extra && $copied_extra->getExtraUid() != $extra->getExtraUid()) {
                $copied_extra->setAttribute('gui_hidden', !$inherits);
                $copied_extra->store();
            } else {
                ///    \todo What's going on here?
            }

        }
    }

    /// As the name says, copies Extras from another PageZone
    /** In particular, it copies the entry from the abstract Extra supertable,
     *  but not the data from the concrete Extra_* tables. I.e. it creates a
     *  new Extra which is a reference to a concrete Extra for each copyable
     *  Extra on the template PageZone.
     *  Also, it sets the origin_uid property on the copied Extras to reflect
     *  the origin of the Extra.
     *
     *  \param $parent_zone Page Zone from which the Extras are copied.
     */
    public function copyExtrasFrom(tx_newspaper_PageZone $parent_zone) {
        foreach ($parent_zone->getExtras() as $extra_to_copy) {
            if (!$extra_to_copy->getAttribute('is_inheritable')) continue;

            /// Clone $extra_to_copy
            /** Not nice: because we're working on the abstract superclass here, we
             *     can't clone the superclass entry because there's no object for it.
             */
            $new_extra = array();
            foreach (tx_newspaper::getAttributes('tx_newspaper_extra') as $attribute) {
                $new_extra[$attribute] = $extra_to_copy->getAttribute($attribute);
            }
            $new_extra['show_extra'] = 1;
            if ($extra_to_copy->getOriginUid()) {
               $new_extra['origin_uid'] = $extra_to_copy->getOriginUid();
            } else {
              $new_extra['origin_uid'] = $extra_to_copy->getAttribute('uid');
            }
            $extra_uid = tx_newspaper::insertRows('tx_newspaper_extra', $new_extra);
            if (false && $parent_zone->getParentPage()->getPageType()->getAttribute('type_name') == 'Liste') {
               tx_newspaper::devlog(
                'tx_newspaper_PageZone::copyExtrasFrom: $extra_to_copy',
                array(
                    'Extra' => $extra_to_copy,
                    'origin uid' => $extra_to_copy->getOriginUid(),
                    'uid' => $extra_to_copy->getAttribute('uid'),
                    'new uid' => $extra_uid
                )
              );
            }

            $this->addExtra(tx_newspaper_Extra_Factory::getInstance()->create($extra_uid));
        }
    }

    /**
     * Change parent Page Zone
     * Hide Extras placed on this Page Zone. Inherit Extras from new parent.
     * @param   int $newAbstractParentUid Abstract page zone uid OR 0 for same page zone type above in hierarchy
     *          OR <0 or NULL for no inheritance
     */
    public function changeParent($newAbstractParentUid) {

        $this->removeInheritedExtras();
        $this->hideOriginExtras();

        if (is_null($newAbstractParentUid)) $newAbstractParentUid = -1;
        $parent_zone = $this->getParentZone($newAbstractParentUid);

        if ($parent_zone) {
            $this->inheritExtrasFrom($parent_zone);
            $concrete_uid = $parent_zone->getUid();
        } else {
            $concrete_uid = -1;
        }

        $this->storeWithNewParent($concrete_uid);

        self::$debug_lots_of_crap = false;
    }

    /// returns true if pagezone is an article
    public function isArticle() {
        return $this->getPageZoneType()->getAttribute('is_article');
    }

    /// returns true if pagezone is a concrete article
    public function isConcreteArticle() { return false; }

    /**
     *  Return the section \p $extra was inserted in string format
     *  @todo Make the '---' and '< error >' messages class constants instead of hardcoding them.
     */
    public function getExtraOriginAsString(tx_newspaper_Extra $extra) {
        $original_pagezone = $this->getExtraOrigin($extra);
        if (!$original_pagezone) return '---';
        if ($original_pagezone->getUid() == $this->getUid()) return '---';
        /** @var tx_newspaper_Page $page */
        $page = $original_pagezone->getParentPage();
        $section = $page->getParentSection();
        if (!$section instanceof tx_newspaper_Section) return '< error >';
        if ($section->getUid() == $this->getParentPage()->getParentSection()->getUid()) {
            return $page->getPageType()->getAttribute('type_name');
        }
        return $section->getAttribute('section_name');
    }

    /**
     *  The Page Zone from which \c $this inherits the placement of its Extras.
     *
     *  The page zone depends on attribute \c 'inherit_mode' (defined in
     *  pagezone_page and article):
     *
     *  If negative, don't inherit at all; if positive,    inherit from the pagezone_page
     *  identified by the UID given (parameter misnomer ;-) ; if zero, find the
     *  page zone in the parent page or higher up in the hierarchy with the same
     *  page zone type as \c $this.
     *
     *  @param bool  $structure_only Ignore the value of \c 'inherit_mode', base
     *         the return value only on the structure of the tx_newspaper_Section
     *         tree.
     *
     *  @return tx_newspaper_PageZone_Page|null The object from which to copy the
     *      tx_newspaper_Extra s and their placements.
     *
     *  \todo What if inherit_mode points to a non-existent PageZone? Currently
     *         a DBException is thrown.
     *  \todo A recursive version of this function would be more elegant, I reckon.
     */
    public function getParentForPlacement($structure_only = false) {

        $timer = tx_newspaper_ExecutionTimer::create();

        self::setLazyCreation(true);

        if (!$structure_only) {
            $inherit_mode = intval($this->getAttribute('inherits_from'));
            if ($inherit_mode < 0) return null;
            if ($inherit_mode > 0 && tx_newspaper_PageZone_Page::isHorizontalInheritanceEnabled()) {
                return tx_newspaper_PageZone_Factory::getInstance()->create($inherit_mode);
            }
        }

        return $this->getParentPageZoneOfSameType();
    }


    static private $parents_cache = array();
    static public function invalidateParentsCache() {
        self::$parents_cache = array();
    }

    /**
     *  Step from parent to parent until a PageZone with matching type is found.
     *
     *  @return tx_newspaper_PageZone_Page|null
     */
    public function getParentPageZoneOfSameType() {

        $timer = tx_newspaper_ExecutionTimer::create();

        if (isset(self::$parents_cache[$this->getAbstractUid()])) return self::$parents_cache[$this->getAbstractUid()];

        $current_page = $this->getParentPage();

        while ($current_page) {
            $current_page = $current_page->getParentPageOfSameType();
            if (!$current_page instanceof tx_newspaper_Page) break;

            /** Look for PageZone of the same type. If no active PageZone is
             *  found, continue looking in the parent section.
             */
            $parent_pagezone = $current_page->getPageZone($this->getPageZoneType());
            if (!is_null($parent_pagezone)) {
                self::$parents_cache[$this->getAbstractUid()] = $parent_pagezone;
                return $parent_pagezone;
            }

        }

        return null;

    }


    /// Retrieve the array of Extras on the PageZone, sorted by position
    /** @param bool $hidden_too Also get Extras that are hidden because their
     *        inheritance mode has been set to false
     *  @return tx_newspaper_Extra[]
     */
    public function getExtras($hidden_too = false) {

        if (empty($this->extras) || $hidden_too || $this->reread_extras) {
            $this->reread_extras = false;
            $this->readExtrasForPagezoneID($this->getUid(), $hidden_too);
        }

        usort($this->extras, array(get_class($this), 'compareExtras'));

        return $this->extras;
    }

    /**
     * @param string|tx_newspaper_Extra $extra_class desired extra class or instance thereof
     * @return tx_newspaper_Extra[] All ${extra_class}es on this PageZone
     */
    public function getExtrasOf($extra_class) {

        if ($extra_class instanceof tx_newspaper_Extra) {
            $extra_class = tx_newspaper::getTable($extra_class);
        }

        $extras = array();

        if ($this->extras && false) { // use the cached array of extras
            foreach ($this->getExtras() as $extra) {
                if (tx_newspaper::getTable($extra) == strtolower($extra_class)) {
                    $extras[] = $extra;
                }
            }
        } else {
            $records = tx_newspaper::selectRows(
                'DISTINCT uid_foreign',
                $this->getExtra2PagezoneTable(),
                'uid_local = ' . $this->getUid(),
                '', '', '', false
            );
            if (empty($records)) return $extras;

            $uids = array(0);
            foreach ($records as $record) {
                $uids[] = $record['uid_foreign'];
            }

            $uids = tx_newspaper::selectRows(
                'uid', tx_newspaper_Extra_Factory::getExtraTable(),
                'uid IN (' . implode(', ', $uids) . ') AND extra_table = \'' . strtolower($extra_class) . '\''
            );

            foreach ($uids as $uid) {
                $extras[] = tx_newspaper_Extra_Factory::getInstance()->create($uid['uid']);
            }
        }

        return $extras;
    }

    /**
     *  Remove a given Extra from the PageZone
     *
     *  @param tx_newspaper_Extra $remove_extra Extra to be removed
     *  @param bool $recursive if true, remove \p $remove_extra on inheriting page zones
     *  @return bool false if $remove_extra was not found, true otherwise
     *  @todo DELETE WHERE origin_uid = ...
     */
    public function removeExtra(tx_newspaper_Extra $remove_extra, $recursive = true) {
        if ($recursive) $this->removeExtraOnInheritingPagezones($remove_extra);
        parent::removeExtra($remove_extra, $recursive);
    }

    private function removeExtraOnInheritingPagezones(tx_newspaper_Extra $remove_extra) {
        foreach($this->getInheritanceHierarchyDown(false) as $inheriting_pagezone) {
            $copied_extra = $inheriting_pagezone->findExtraByOriginUID($remove_extra->getOriginUid(), true);
            if ($copied_extra) $inheriting_pagezone->removeExtra($copied_extra, false);
        }
    }

    /**
     *  Move an Extra present on the PageZone after another Extra, defined by its origin UID
     *
     *  @param tx_newspaper_Extra $move_extra The Extra to be moved
     *  @param int $origin_uid The origin UID of the Extra after which $new_extra will be inserted. If
     *              \p $origin_uid == 0, insert at the beginning.
     *  @param bool $recursive if true, move \p $move_extra after Extra with origin UID \p $origin_uid
     *              on inheriting page zones
     *  @exception tx_newspaper_InconsistencyException If $move_extra is not present on the PageZone
     */
    public function moveExtraAfter(tx_newspaper_Extra $move_extra, $origin_uid = 0, $recursive = true) {

        parent::moveExtraAfter($move_extra, $origin_uid, $recursive);

        if ($recursive) $this->moveExtraOnInheritingPagezones($move_extra, $origin_uid);
    }

    public function insertExtraAfter(tx_newspaper_Extra $insert_extra,
                                     $origin_uid = 0, $recursive = true) {
        $insert_extra = parent::insertExtraAfter($insert_extra, $origin_uid, $recursive);
        if ($recursive /* && (boolean)$insert_extra->getAttribute('is_inheritable') */) {
            $this->insertExtraOnInheritingPagezones($insert_extra, $origin_uid);
        }
        return $insert_extra;
    }

    /// Get the hierarchy of Page Zones inheriting placement from $this
    /**
     * @param bool $including_myself If $including_myself true, add $this to the list
     * @param tx_newspaper_Pagezone_Page[] $hierarchy of already found parents (for recursive calling)
     * @return tx_newspaper_Pagezone_Page[] Inheritance hierarchy of pages inheriting from the current Page
     *             Zone, ordered downwards, depth-first
     */
    public function getInheritanceHierarchyDown($including_myself = true,
                                                $hierarchy = array()) {

        $timer = tx_newspaper_ExecutionTimer::create();

        self::setLazyCreation(true);

        if ($including_myself) $hierarchy[] = $this;

        $hierarchy = array_merge($hierarchy, $this->getExplicitlyInheritingPagezoneHierarchy());

        if (!$this->getParentPage()) return $hierarchy;

        return $this->addInheritingPagezonesDownTheHierarchy($hierarchy);
    }


    protected function getExtraAndPagezone(tx_newspaper_Extra $extra) {

        if (!self::$debug_lots_of_crap) return array();

        return array(
            tx_newspaper_DB::getInstance()->selectRows(
                'tx_newspaper_extra.uid, tx_newspaper_extra.crdate, tx_newspaper_extra.cruser_id, tx_newspaper_extra.position, tx_newspaper_extra.origin_uid, tx_newspaper_extra.show_extra,
                 tx_newspaper_extra.extra_table, tx_newspaper_extra.extra_uid,
                 tx_newspaper_pagezone_page.crdate AS pagezone_date, tx_newspaper_pagezone_page.cruser_id AS pagezone_user,
                 tx_newspaper_pagezonetype.type_name,
                 tx_newspaper_pagetype.type_name,
                 tx_newspaper_section.section_name',
                'tx_newspaper_extra
                 JOIN tx_newspaper_pagezone_page_extras_mm ON tx_newspaper_extra.uid                     =  tx_newspaper_pagezone_page_extras_mm.uid_foreign
                 JOIN tx_newspaper_pagezone_page           ON tx_newspaper_pagezone_page.uid             = tx_newspaper_pagezone_page_extras_mm.uid_local
                 JOIN tx_newspaper_pagezonetype            ON tx_newspaper_pagezone_page.pagezonetype_id = tx_newspaper_pagezonetype.uid
                 JOIN tx_newspaper_pagezone                ON tx_newspaper_pagezone.pagezone_uid         = tx_newspaper_pagezone_page.uid
                 JOIN tx_newspaper_page                    ON tx_newspaper_pagezone.page_id              = tx_newspaper_page.uid
                 JOIN tx_newspaper_pagetype                ON tx_newspaper_page.pagetype_id              = tx_newspaper_pagetype.uid
                 JOIN tx_newspaper_section                 ON tx_newspaper_page.section                  = tx_newspaper_section.uid',
                'tx_newspaper_extra.extra_table = "' . $extra->getAttribute('extra_table') . '"
                 AND tx_newspaper_extra.extra_uid = ' . $extra->getAttribute('extra_uid') . '
                 AND tx_newspaper_pagezone.pagezone_table = "tx_newspaper_pagezone_page"
                 AND tx_newspaper_pagezone_page.uid = ' . $this->getUid() . ' OR tx_newspaper_pagezone_page.uid = ' . $this->getParentForPlacement()->getUid()
            ),
            array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 1, 5)
        );
    }


    /** Deduce the $extra_after_which from the parent page(s):
     *  http://segfault.hal.taz.de/mediawiki/index.php/Vererbung_Bestueckung_Seitenbereiche_(DEV)
     *  (2.3.1.3 Beispiel - Aenderung Ebene 1, aber Referenzelement wird nicht vererbt)
     */
    protected function deduceInsertExtraFromParent($origin_uid) {
        $parent = $this->getParentForPlacement();
        if (!$parent instanceof tx_newspaper_PageZone) {
            throw new tx_newspaper_DoesntInheritException($this, $origin_uid);
        } else {
            return $parent->getInsertPosition($origin_uid);
        }
    }

    /// Reads page zones which have been explicitly set to inherit from \c $this.
    private function getExplicitlyInheritingPagezoneHierarchy() {

        $hierarchy = array();

        $table = tx_newspaper::getTable($this);
        $heirs = tx_newspaper::selectRows(
            'uid', $table, 'inherits_from = ' . $this->getUid()
        );

        foreach ($heirs as $heir) {
            if (intval($heir['uid']) == $this->getUid()) continue;
            $inheriting_pagezone = new tx_newspaper_PageZone_Page($heir['uid']);
            $hierarchy = $inheriting_pagezone->getInheritanceHierarchyDown(true, $hierarchy);
        }

        return $hierarchy;
    }

        /// look for page zones on pages in section down the section hierarchy
    private function addInheritingPagezonesDownTheHierarchy(array $hierarchy) {

        $timer = tx_newspaper_ExecutionTimer::create();

        $sub_pages = $this->getParentPage()->getSubPagesOfSameType();

        foreach ($sub_pages as $sub_page) {
            $this->addInheritingPagezoneOnPage($sub_page, $hierarchy);
        }

        return $hierarchy;
    }
    private function addInheritingPagezoneOnPage(tx_newspaper_Page $sub_page, array &$hierarchy) {

        $timer = tx_newspaper_ExecutionTimer::create();

        $inheriting_pagezone = $sub_page->getPageZone($this->getPageZoneType());
        if ($this->isInheritedBy($inheriting_pagezone)) {
            $hierarchy = $inheriting_pagezone->getInheritanceHierarchyDown(true, $hierarchy);
        }
    }

    private function isInheritedBy(tx_newspaper_PageZone $pagezone = null) {
        $timer = tx_newspaper_ExecutionTimer::create();
        if (!$pagezone instanceof tx_newspaper_PageZone) return false;
        $parent = $pagezone->getParentForPlacement(false);
        if (!$parent instanceof tx_newspaper_PageZone) return false;
        if ($parent->getUid() != $this->getUid()) return false;
        return true;
    }

    /// Pass down the insertion to PageZones inheriting from \c $this
    private function insertExtraOnInheritingPagezones(tx_newspaper_Extra $insert_extra, $origin_uid) {
        foreach ($this->getInheritanceHierarchyDown(false) as $inheriting_pagezone) {
            self::cloneExtraOnPagezone($insert_extra, $inheriting_pagezone, $origin_uid);
        }
    }

    private static function cloneExtraOnPagezone(tx_newspaper_Extra $insert_extra, tx_newspaper_PageZone $inheriting_pagezone, $origin_uid) {

        $copied_extra = clone $insert_extra;
        $copied_extra->setOriginUid($insert_extra->getOriginUid());

        $inheriting_pagezone->insertExtraAfter($copied_extra, $origin_uid, false);
    }

    private function moveExtraOnInheritingPagezones(tx_newspaper_Extra $move_extra, $origin_uid) {
        $timer = tx_newspaper_ExecutionTimer::create();
        foreach ($this->getInheritanceHierarchyDown(false) as $inheriting_pagezone) {
            $copied_extra = $inheriting_pagezone->findExtraByOriginUID($move_extra->getOriginUid());
            if ($copied_extra) {
                $inheriting_pagezone->moveExtraAfter($copied_extra, $origin_uid, false);
            }
        }
    }


    private function removeInheritedExtras() {
        $debug_extras = array();
        foreach ($this->getExtras() as $extra) {
            if (!$extra->isOriginExtra()) {
                $debug_extras[] = $extra;
                /// Delete Extra, also on sub-PageZones
                $this->removeExtra($extra, true);
            }
        }
    }

    private function hideOriginExtras() {
        $debug_extras = array();
        foreach ($this->getExtras() as $extra) {
            if ($extra->isOriginExtra()) {
                $debug_extras[] = $extra;
                /// Hide and move to end of page zone
                $extra->setAttribute('show_extra', 0);
                $extra->store();
            }
        }
    }

    private function getParentZone($new_parent_uid) {
        $parent_uid = intval($new_parent_uid);

        if ($parent_uid < 0) {
            return null;
        } else if ($parent_uid == 0) {
            return $this->getParentPageZoneOfSameType();
        } else {
            return tx_newspaper_PageZone_Factory::getInstance()->create($parent_uid);
        }

    }

    private function inheritExtrasFrom(tx_newspaper_PageZone $parent_zone) {
        foreach (array_reverse($parent_zone->getExtras()) as $extra_to_copy) {
            $this->inheritExtra($extra_to_copy);
        }
    }

    private function inheritExtra(tx_newspaper_Extra $extra) {
        $copied = $extra->duplicate(true);
        $copied->setOriginUid($extra->getOriginUid());
        return $this->insertExtraAfter($copied);
    }

    private function storeWithNewParent($concrete_uid) {
        $this->setAttribute('inherits_from', intval($concrete_uid));
        $this->setAttribute('tstamp', time());
        $this->store();
    }


    private function getExtraOrigin(tx_newspaper_Extra $extra) {
        if ($extra->isOriginExtra()) return $this;

        foreach ($this->getInheritanceHierarchyUp(false) as $origin_pagezone) {
            foreach ($origin_pagezone->getExtras() as $potential_origin_extra) {
                if ($potential_origin_extra->getExtraUid() == $extra->getOriginUid()) {
                    return $origin_pagezone;
                }
            }
        }
    }


    static function getModuleName() { return 'np_pagezone_page'; }

    public function getExtra2PagezoneTable() {
        return self::$extra_2_pagezone_table;
    }

    public static function updateDependencyTree(tx_newspaper_PageZone_Page $pagezone) {
        if (tx_newspaper_DependencyTree::useDependencyTree()) {
            $tree = tx_newspaper_DependencyTree::generateFromPagezone($pagezone);
            $tree->executeActionsOnPages('tx_newspaper_Extra');
        }
    }

    /**
     * Check if horizontal inheritance is switched on
     * @return int Value configured in TSConfig newspaper.horizontal_inheritance_enabled or 0 as default
     */
    public static function isHorizontalInheritanceEnabled() {
        $TSConfig = tx_newspaper::getTSConfig();
        return intval($TSConfig['newspaper.']['horizontal_inheritance_enabled']);
    }

    static protected $extra_2_pagezone_table = 'tx_newspaper_pagezone_page_extras_mm';
}
 
?>
