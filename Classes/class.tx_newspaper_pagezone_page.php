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
