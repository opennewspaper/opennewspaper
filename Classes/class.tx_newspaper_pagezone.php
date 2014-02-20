<?php
/**
 *  \file class.tx_newspaper_pagezone.php
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

require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_pagezonetype.php');

class tx_newspaper_DoesntInheritException extends tx_newspaper_IllegalUsageException {

    public function __construct(tx_newspaper_PageZone $pagezone, $origin_uid) {
        parent::__construct(
            'Tried to insert an Extra with an origin uid ' . $origin_uid .
            ' which is neither in PageZone ' . $pagezone->getUid() . ' nor in any of the parents.'
        );
    }
}

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
 */
abstract class tx_newspaper_PageZone implements tx_newspaper_ExtraIface {

    const EXTRA_SPACING = 1024;

    protected static $lazy_creation = false;
    static public function setLazyCreation($lazy) {
        self::$lazy_creation = $lazy;
    }

    /// Configure Smarty rendering engine
    public function __construct($uid = 0) {
        /// Configure Smarty rendering engine
        $this->smarty = new tx_newspaper_Smarty();
        if ($uid) {
            $this->setUid($uid);
            /** I'm not sure whether the following line should remain. It's a
             *  safety net because currently it's not ensured that extras are
             *  created consistently.
             */
            $this->extra_uid = tx_newspaper_Extra::createExtraRecord($uid, $this->getTable());
        }
    }


    /// Convert object to string to make it visible in stack backtraces, devlog etc.
    public function __toString() {
        return get_class($this) . ' ' . $this->getUid() . " (" . $this->getAbstractUid() . ")";
    }


    ////////////////////////////////////////////////////////////////////////////
    //
    //    interface tx_newspaper_StoredObject
    //
    ////////////////////////////////////////////////////////////////////////////


    function getAttribute($attribute) {
        /** For reasons explained in readExtras() the attributes are read in the
         *  constructor, so we don't read the attributes here
         */
        if (array_key_exists($attribute, $this->attributes)) {
            return $this->attributes[$attribute];
        }
        if (!$this->pagezone_attributes) {
            $this->pagezone_attributes = tx_newspaper::selectOneRow(
                '*', 'tx_newspaper_pagezone',
                'pagezone_table = \'' . $this->getTable() . '\' AND pagezone_uid = ' . $this->getUid()
            );
        }

         if (array_key_exists($attribute, $this->pagezone_attributes)) {
            return $this->pagezone_attributes[$attribute];
         }
        throw new tx_newspaper_WrongAttributeException($attribute);
    }


    function setAttribute($attribute, $value) {
        /** For reasons explained in readExtras() the attributes are read in the
         *  constructor, so we don't read the attributes here
         */
        $this->attributes[$attribute] = $value;
    }


    public function getTitle() {
        return tx_newspaper::getTranslation('title_' . $this->getTable());
    }

    function getUid() {
        return intval($this->uid);
    }

    function setUid($uid) {
        $this->uid = $uid;
    }

    public  function getTable() {
        return tx_newspaper::getTable($this);
    }

    static function getModuleName() {
        return 'np_pagezone';
    }


    ////////////////////////////////////////////////////////////////////////////
    //
    //    interface tx_newspaper_ExtraIface
    //
    ////////////////////////////////////////////////////////////////////////////


    /// A short description that makes an Extra uniquely identifiable in the BE
    /** This function should be overridden in every class that can be pooled, to
     *  provide the BE user a way to find an Extra to create a new Extra from.
     */
    public function getDescription() {
        //    default implementation
        return $this->getTitle() . ' ' . $this->getUid();
    }


    /// Deletes the concrete Extras and all references to it
    public function deleteIncludingReferences() {
        throw new tx_newspaper_NotYetImplementedException();
        /*
\todo: Oliver: I found this in my code, wrote it in September, so I have to have a deep look into this ...
        /// Find abstract records linking to the concrete Extra
        $uids = tx_newspaper::selectRows(
            'uid', self::$table,
            'extra_table = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->getTable(), $this->getTable()) .
            ' AND extra_uid = ' . $this->getUid());

        foreach ($uids as $uid) {
            /// Delete entries in association tables linking to abstract record
            tx_newspaper::deleteRows(
                tx_newspaper_Article::getExtra2PagezoneTable(),
                'uid_foreign = ' . intval($uid['uid'])
            );
            tx_newspaper::deleteRows(
                tx_newspaper_PageZone_Page::getExtra2PagezoneTable(),
                'uid_foreign = ' . intval($uid['uid'])
            );

            /// Delete the abstract record
            tx_newspaper::deleteRows(self::$table, 'uid = ' . intval($uid['uid']));
        }

        /// delete the concrete record
        tx_newspaper::deleteRows($this->getTable(), 'uid = ' . $this->getUid());
        */
    }


    /// Lists Extras which are in the pool of master copies for new Extras
    public function getPooledExtras() {
        throw new tx_newspaper_IllegalUsageException('PageZones cannot be pooled.');
    }


    /// Render the page zone, containing all extras
    /** \param $template_set the template set used to render this page (as
     *          passed down from tx_newspaper_Page::render() )
     *    \return The rendered page as HTML (or XML, if you insist)
     */
#    abstract public function render($template_set = '');


     /// \todo: oliver: deprecated? probably yes - but used in tx_newspaper_Article
    static function readExtraItem($uid, $table) {
        throw new tx_newspaper_NotYetImplementedException();
    }


    public static function dependsOnArticle() { return false; }

    ////////////////////////////////////////////////////////////////////////////
    //
    //    class tx_newspaper_PageZone
    //
    ////////////////////////////////////////////////////////////////////////////

    ///    The tx_newspaper_PageZoneType of the current PageZone.
    /** @return tx_newspaper_PageZoneType The type of \c $this.
     */
    public function getPageZoneType() {
        if (!$this->pagezonetype) {
            $pagezonetype_id = $this->getUid()? $this->getAttribute('pagezonetype_id'): 0;
            $this->pagezonetype = new tx_newspaper_PageZoneType($pagezonetype_id);
            $this->pagezonetype->getAttribute('type_name'); // read in the attributes
        }
        return $this->pagezonetype;
    }

    abstract public function changeExtraParagraph(tx_newspaper_Extra $extra, $new_paragraph);

    /// Get the UID of the abstract record for this PageZone.
    /** \return UID of the record containing the data for the abstract portion
     *      of the given pagezone - the one from the table
     *      \c tx_newspaper_pagezone.
     */
    public function getAbstractUid() {
        $row = tx_newspaper::selectOneRow(
            'uid',
            'tx_newspaper_pagezone',
            'deleted=0 AND pagezone_uid=' . $this->getUid() . ' AND pagezone_table="' .$this->getTable() . '"'
        );
        return intval($row['uid']);
    }


    /**
     *  @return tx_newspaper_Page on which the PageZone lies.
     */
    public function getParentPage() {

        if (!$this->parent_page) {
            if (!$this->parent_page_id) {
                $pagezone_record = tx_newspaper::selectOneRow(
                    'page_id', 'tx_newspaper_pagezone',
                    'pagezone_table = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->getTable(), 'tx_newspaper_pagezone') .
                    ' AND pagezone_uid = ' .$this->getUid()
                );
                $this->parent_page_id = intval($pagezone_record['page_id']);
            }

            if ($this->parent_page_id) {
                $this->parent_page = new tx_newspaper_Page($this->parent_page_id);
            } else {
                // that's ok, articles don't have parent pages
                return null;
            }
        }
        return $this->parent_page;
    }

    /**
     *  dummy function, soon to be removed (as soon as all refernences to this function from inside this class are cleared up)
     *  moved to pagezone_page.
     */
    public function getParentForPlacement($structure_only = false) {
        return null;
    }


    /**
     *  Add an extra after the Extra which is on the original page zone as \p $origin_uid
     *  @param tx_newspaper_Extra $insert_extra The new, fully instantiated Extra to insert
     *  @param int $origin_uid UID of \p $insert_extra on the PageZone where it was originally added.
     *  @param bool $recursive  If set, pass down the insertion to all inheriting PageZones.
     *  @return tx_newspaper_Extra \p $insert_extra
     */
    public function insertExtraAfter(tx_newspaper_Extra $insert_extra,
                                     $origin_uid = 0, $recursive = true) {

        /** \todo: it should be possible to set the paragraph BEFORE calling this function. otherwise a workaround
         *         is needed: insert extra to article and call changeExtraArticle() on the article afterwards
         */
        $insert_extra->setAttribute('position', $this->getInsertPosition($origin_uid));
        $insert_extra->setAttribute('paragraph', $this->paragraph_for_insert);

        $insert_extra->setAttribute('is_inheritable', 1);
        $insert_extra->setAttribute('show_extra', 1);

        if (self::$debug_lots_of_crap) tx_newspaper::devlog('insertExtraAfter() before store', $this->getExtraAndPagezone($insert_extra));

        /** Write Extra to DB    */
        $insert_extra->store();

        if (self::$debug_lots_of_crap) tx_newspaper::devlog('insertExtraAfter() after store', $this->getExtraAndPagezone($insert_extra));

        $this->addExtra($insert_extra);

        if (self::$debug_lots_of_crap) tx_newspaper::devlog('insertExtraAfter() after addExtra', $this->getExtraAndPagezone($insert_extra));

        $this->reread_extras = true;

        return $insert_extra;
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

        if (!$this->removeExtraFromArray($remove_extra)) return false;

        $this->removeExtraFromMMTable($remove_extra);

        self::removeAbstractExtraRecord($remove_extra);

        self::removeConcreteExtraIfLastInstance($remove_extra);

        return true;
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

        $timer = tx_newspaper_ExecutionTimer::create(
            "tx_newspaper_PageZone(" . $this->getUid() . ")::moveExtraAfter(" . $move_extra->getUid() . ", $origin_uid, " . intval($recursive).")"
        );

        $this->checkExtraIsOnThis($move_extra);

        $this->changePositionOfExtra($move_extra, $origin_uid);
    }

    private function changePositionOfExtra(tx_newspaper_Extra $move_extra, $origin_uid) {
        $move_extra->setAttribute('position', $this->getInsertPosition($origin_uid));
        $move_extra->store();
    }

    ///    Check that \p $move_extra is really on $this
    private function checkExtraIsOnThis(tx_newspaper_Extra $move_extra) {
        try {
            $this->indexOfExtra($move_extra);
        } catch (tx_newspaper_InconsistencyException $e) {
            throw new tx_newspaper_InconsistencyException($e->getMessage(), true);
        }
    }


    /** @return tx_newspaper_Extra */
    private function copyExtra(tx_newspaper_Extra $extra) {

        $new_extra = clone $extra;

        if (!$new_extra->getAttribute('origin_uid')) {
            $new_extra->setAttribute('origin_uid', $extra->getExtraUid());
        }

        $new_extra->store();

        return $new_extra;
    }

    static protected $debug_lots_of_crap = false;
    abstract protected function getExtraAndPagezone(tx_newspaper_Extra $extra);



    /**
     *  This should actually be only defined in tx_newspaper_PageZone_Page, but because
     *  tx_newspaper_BE::populateExtraData() calls this on generic page zones it must be defined here.
     * @param tx_newspaper_Extra $extra
     * @return mixed
     */
    abstract public function getExtraOriginAsString(tx_newspaper_Extra $extra);

    /// returns true if pagezone is an article
    abstract public function isArticle();

    /// returns true if pagezone is a concrete article
    abstract public function isConcreteArticle();

    /// delete this concrete and the parent abstract pagezone
    public function delete() {
        // delete concrete pagezone delete abstract record first
        $this->setAttribute('deleted', 1);
        $this->store();
        // delete abstract record then
        tx_newspaper::updateRows(
            'tx_newspaper_pagezone',
            'uid=' . $this->getAbstractUid(),
            array('deleted' => 1)
        );
    }


    ////////////////////////////////////////////////////////////////////////////
    //
    //    internal functions (public only to enable unit testing)
    //
    ////////////////////////////////////////////////////////////////////////////

     /// Create the record for a concrete PageZone in the table of abstract PageZones
    /** This is probably necessary because a concrete PageZone has been freshly
     *  created.
     *
     *  Does nothing if the concrete PageZone is already linked in the abstract table.
     *
     *  \return UID of abstract PageZone record
     */
    public function createPageZoneRecord() {
        /// Check if record is already present in page zone table
        $row = tx_newspaper::selectZeroOrOneRows(
            'uid', 'tx_newspaper_pagezone',
            'pagezone_table = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->getTable(), $this->getTable()) .
            ' AND pagezone_uid = ' . $this->getUid()
        );
        if ($row['uid']) return $row['uid'];

        /// read typo3 fields to copy into page zone table
        $row = tx_newspaper::selectOneRow(
            implode(', ', self::$fields_to_copy_into_pagezone_table),
            $this->getTable(),
            'uid = ' . $this->getUid()
        );

        /// write the uid and table into page zone table, with the values read above
        $row['pagezone_uid'] = $this->getUid();
        $row['pagezone_table'] = $this->getTable();
        $row['tstamp'] = time();                ///< tstamp is set to now

        $uid = tx_newspaper::insertRows('tx_newspaper_pagezone', $row);

        return $uid;
    }


    public function setPageZoneType(tx_newspaper_PageZoneType $type) {
        $this->pagezonetype = $type;
    }


    public function setParentPage(tx_newspaper_Page $parent) {
        $this->parent_page = $parent;
        $this->parent_page_id = $parent->getUid();
    }

    public function doesContainExtra(tx_newspaper_Extra $extra, $exact_extra = false) {
        foreach($this->getExtras() as $tested_extra) {
            if ($tested_extra->getExtraUid() == $extra->getExtraUid()) {
                return true;
            }
            if (!$exact_extra &&
                $tested_extra->getAttribute('extra_uid') == $extra->getAttribute('extra_uid') &&
                $tested_extra->getAttribute('extra_table') == $extra->getAttribute('extra_table')) {
                return true;
            }
        }
        return false;
    }

    ////////////////////////////////////////////////////////////////////////////
    //
    //    protected functions
    //
    ////////////////////////////////////////////////////////////////////////////

    private function removeExtraFromArray(tx_newspaper_Extra $remove_extra) {
        $index = -1;
        try {
            $index = $this->indexOfExtra($remove_extra);
        } catch (tx_newspaper_InconsistencyException $e) {
            //    Extra not found, nothing to do
            return false;
        }
        unset($this->extras[$index]);

        return true;
    }

    private function removeExtraFromMMTable(tx_newspaper_Extra $remove_extra) {
        tx_newspaper::deleteRows(
                $this->getExtra2PagezoneTable(),
                'uid_local = ' . $this->getUid() .
                ' AND uid_foreign = ' . $remove_extra->getExtraUid()
            );
    }

    private static function removeAbstractExtraRecord(tx_newspaper_Extra $remove_extra) {
        tx_newspaper::deleteRows(
            tx_newspaper_Extra_Factory::getExtraTable(),
            array($remove_extra->getExtraUid())
        );
    }

    /** If abstract record was the last one linking to the concrete Extra,
     *  \em and the concrete Extra is not pooled, delete the concrete Extra
     *  too.
     */
    private static function removeConcreteExtraIfLastInstance(tx_newspaper_Extra $remove_extra) {
        try {
            if (!$remove_extra->getAttribute('pool')) {
                if (!self::getNumberOfReferencesToConcreteExtra($remove_extra)) {
                    $remove_extra->deleteIncludingReferences();
                }
            }
        } catch (tx_newspaper_WrongAttributeException $e) { }
    }

    private static function getNumberOfReferencesToConcreteExtra(tx_newspaper_Extra $extra) {
        $count = tx_newspaper::selectOneRow(
            'COUNT(*) AS num',
            tx_newspaper_Extra_Factory::getExtraTable(),
            'extra_table = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(
                $extra->getTable(), $extra->getTable()) .
            ' AND extra_uid = ' . $extra->getUid()
        );
        return intval($count['num']);
    }


    /// Find next free position after the Extra whose origin uid attribute matches \p $origin_uid
    /** Side effect: finds the paragraph of the Extra matching \p $origin_uid
     *  and stores it in \p $this->paragraph_for_insert
     *
     *  @param int $origin_uid The origin_uid of the Extra after which a free place is wanted
     *
     *  @return int A position value which is halfway between the found Extra and the
     *             next Extra
     */
    protected function getInsertPosition($origin_uid) {

        if ($origin_uid) {
            /** Find the Extra to insert after. If it is not deleted on this page,
             *  it is the Extra whose attribute 'origin_uid' equals $origin_uid.
             */
            $extra_after_which = $this->findExtraByOriginUID($origin_uid);
            if (!($extra_after_which instanceof tx_newspaper_Extra)) {
                return $this->deduceInsertExtraFromParent($origin_uid);
            }
            $this->paragraph_for_insert = intval($extra_after_which->getAttribute('paragraph'));
            $position = $extra_after_which->getAttribute('position');
        } else {
            $this->shiftPositionOfAllExtras();
            $position = 0;
        }

        $position_before_which = $this->findExtraToInsertBefore($position);
        $position_before_which = $this->maintainSpacingBetweenExtras($position_before_which, $position);

        /// Place Extra to insert between $extra_after and $extra_before (or at end)
        return $position+($position_before_which-$position)/2;
    }

    /** Deduce the $extra_after_which from the parent page(s):
     *  http://segfault.hal.taz.de/mediawiki/index.php/Vererbung_Bestueckung_Seitenbereiche_(DEV)
     *  (2.3.1.3 Beispiel - Aenderung Ebene 1, aber Referenzelement wird nicht vererbt)
     */
    abstract protected function deduceInsertExtraFromParent($origin_uid);

    private function shiftPositionOfAllExtras() {
        foreach ($this->getExtras() as $extra) {
            $extra->setAttribute('position', $extra->getAttribute('position') + self::EXTRA_SPACING);
            $extra->store();
        }
    }

    private function maintainSpacingBetweenExtras($position_before_which, $position) {
        if (!$position_before_which) $position_before_which = 2 * ($position ? $position : self::EXTRA_SPACING);

        if ($position_before_which - $position < 2) {
            $position_before_which = $this->shiftPositionOfFollowingExtras($position, $position_before_which);
            return $position_before_which;
        }
        return $position_before_which;
    }

    /// Find Extra before which to insert the new Extra
    private function findExtraToInsertBefore($position) {
        $position_before_which = 0;
        foreach ($this->getExtras() as $extra) {
            /// \todo If $this is an article, handle paragraphs
            if ($extra->getAttribute('position') > $position &&
                (!$position_before_which ||
                 $position_before_which > $extra->getAttribute('position')
                )
            ) {
                $position_before_which = $extra->getAttribute('position');
                break;
            }
        }
        return $position_before_which;
    }

    /// Increase 'position' attribute for all extras after $extra_after_which
    private function shiftPositionOfFollowingExtras($position, $position_before_which) {
        foreach ($this->getExtras() as $extra_to_rearrange) {
            if ($extra_to_rearrange->getAttribute('position') <= $position) continue;
            $extra_to_rearrange->setAttribute('position', $extra_to_rearrange->getAttribute('position') + self::EXTRA_SPACING);
            $extra_to_rearrange->store();
        }
        $position_before_which += self::EXTRA_SPACING;
        return $position_before_which;
    }


    /// Binary search for an Extra, assuming that $this->extras is ordered by position
    /** This method must be overridden in the Article class because in articles
     *  Extras are ordered by paragraph first, position second
     */
    protected function indexOfExtra(tx_newspaper_Extra $extra) {
        return $this->binarySearchForExtra($extra, 0, sizeof($this->getExtras())-1);
    }

    private function binarySearchForExtra(tx_newspaper_Extra $extra, $low_index, $high_index) {
        while ($high_index >= $low_index) {
            $index_to_check = floor(($high_index + $low_index) / 2);
            $comparison = $this->getExtra($index_to_check)->getAttribute('position') -
                          $extra->getAttribute('position');
            if ($comparison < 0) $low_index = $index_to_check + 1;
            elseif ($comparison > 0) $high_index = $index_to_check - 1;
            else return $index_to_check;
        }

        // Loop ended without a match
        throw new tx_newspaper_InconsistencyException('Extra ' . $extra->getUid() .
                                                      ' not found in array of Extras!');
    }


    ///    Given a origin uid, find the Extra which has this value for \p origin_uid
    /** @param int $origin_uid The origin uid of the extra to be found
     *  @param boolean $hidden_too Whether to search in GUI-hidden extras as well
     *  @return tx_newspaper_Extra
     */
    final protected function findExtraByOriginUID($origin_uid, $hidden_too = false) {

        foreach ($this->getExtras($hidden_too) as $extra) {
            if (intval($extra->getOriginUid()) == intval($origin_uid)) {
                return $extra;
            }
        }

        return null;
    }


    /// \return The position value of the last Extra on the PageZone
    protected function findLastPosition() {
        return $this->getExtra(sizeof($this->getExtras())-1)->getAttribute('position');
    }

    /// Retrieve the array of Extras on the PageZone, sorted by position
    /** @param bool $hidden_too Also get Extras that are hidden because their
     *        inheritance mode has been set to false
     *  @return tx_newspaper_Extra[]
     */
#    abstract public function getExtras($hidden_too = false);

    /**
     * @param string|tx_newspaper_Extra $extra_class desired extra class or instance thereof
     * @return tx_newspaper_Extra[] All ${extra_class}es on this PageZone
     */
    abstract public function getExtrasOf($extra_class);

    /// Read Extras from DB
    /** Objective: Read tx_newspaper_Extra array and attributes from the base
     *  class c'tor instead of every descendant to minimize code duplication.
     *
     *  Problem: The descendant c'tor calls \c parent::__construct(). The
     *  base c'tor knows only its own class, not the concrete class which is
     *  intantiated. Every function call in the base c'tor therefore calls
     *  functions in the base class. Late binding is impossible.
     *
     *  Solution: Factor out the methods to read Extras and attributes in the
     *  base class, and call them in the descended c'tor like this:
     *  \code
     *     parent::__construct();
     *  $this->readExtras($uid);
     *  $this->readAttributes($this->getTable(), $uid);
     *  \endcode
     *
     *  \todo factor out code to read MM table and create Extras
     *
     *  \param $uid UID in the table of the abstract PageZone type
     *  \param $hidden_too Also get Extras that are hidden because their
     *        inheritance mode has been set to false
     */
    protected function readExtrasForPagezoneID($uid, $hidden_too = false) {

        $this->extras = array();

        $uids = $this->getExtraUidsForPagezoneID($uid);
        if (empty($uids)) return;

        foreach ($uids as $uid) {
            try {
                $deleted = self::getAbstractExtraWithoutEnableFields($uid);

                if (self::extraIsDisplayedOnPagezone($deleted, $hidden_too)) {
                    $this->extras[] = tx_newspaper_Extra_Factory::getInstance()->create($uid['uid_foreign']);
                } else {
                    /// \todo remove association table entry, but only if really deleted
                }
            } catch (tx_newspaper_EmptyResultException $e) {
                /// \todo remove association table entry
                t3lib_div::debug('Extra ' . $uid['uid_foreign'] . ': EmptyResult! '. $e);
            }
        }
    }

    private function getExtraUidsForPagezoneID($uid) {
        $uids = tx_newspaper::selectRows(
            'DISTINCT uid_foreign',
            $this->getExtra2PagezoneTable(),
            "uid_local = $uid",
            '', '', '', false
        );
        return $uids;
    }

    /// assembling the query manually here cuz we want to ignore enable fields
    private static function getAbstractExtraWithoutEnableFields($uid) {
        $query = $GLOBALS['TYPO3_DB']->SELECTquery(
            'deleted, gui_hidden, show_extra',
            tx_newspaper_Extra_Factory::getExtraTable(),
            'uid = ' . $uid['uid_foreign']);
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);

        $deleted = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        return $deleted;
    }

    private static function extraIsDisplayedOnPagezone(array $extra_data, $hidden_too) {
        return !$extra_data['deleted'] &&
               (!$extra_data['gui_hidden'] || $hidden_too) &&
               !(TYPO3_MODE != 'BE' && !$extra_data['show_extra']);
    }


    /// Ordering function to keep Extras in the order in which they appear on the PageZone
    /** Supplied as parameter to usort() in getExtras().
     *  \param $extra1 first Extra to compare
     *  \param $extra2 second Extra to compare
     *  \return < 0 if $extra1 comes before $extra2, > 0 if it comes after,
     *             == 0 if their position is the same
     */
    static protected function compareExtras(tx_newspaper_ExtraIface $extra1,
                                              tx_newspaper_ExtraIface $extra2) {
         return $extra1->getAttribute('position')-$extra2->getAttribute('position');
    }


    /// Read Attributes from DB
    /** \see readExtras()
     *
     *  \param $table Table which stores the concrete object
     *  \param $uid UID in the table of the concrete type
     */
    protected function readAttributes($table, $uid) {
        /// Read Attributes from persistent storage
         $this->attributes = tx_newspaper::selectOneRow('*', $table, 'uid = ' . $uid);
         $this->attributes['query'] = tx_newspaper_DB::getQuery();
     }


     /**
      *  Returns the table which links Extras to this type of page zone
      *
      *  This function is needed, and non-static, because late static binding does not work too well
      *  with PHP (at least prior to 5.3, which introduced the static:: storage type - but this is
      *  not yet distributed widely enough).
      *  @return string self::$extra_2_pagezone_table
      */
    abstract public function getExtra2PagezoneTable();

    ///    Retrieve a single Extra, defined by its index in the sequence
    /** \param $index
     *  \return The \p $index -th Extra on the PageZone
     */
    public function getExtra($index) {
        $extras = $this->getExtras();
        return $extras[$index];
    }


    public function rereadExtras() {
        $this->readExtrasForPagezoneID($this->getUid(), false);
    }
    protected $reread_extras = false;

    /// Add an Extra to the PageZone, both in RAM and persistently
    public function addExtra(tx_newspaper_Extra $extra) {
        $this->extras[] = $extra;

        if (self::$debug_lots_of_crap)
            tx_newspaper::devlog('addExtra() before insertRows', $this->getExtraAndPagezone($extra));

        tx_newspaper::insertRows(
            $this->getExtra2PagezoneTable(),
            array(
                'uid_local' => $this->getUid(),
                'uid_foreign' => $extra->getExtraUid()
            )
        );
        $this->reread_extras = true;

        if (self::$debug_lots_of_crap)
            tx_newspaper::devlog('addExtra() after insertRows', $this->getExtraAndPagezone($extra));
    }


    public function getPageZoneUid() { return $this->pagezone_uid; }


    public function getExtraUid() { return $this->extra_uid; }


    public function setExtraUid($uid) {
        $this->extra_uid = intval($uid);
        if ($this->extra_attributes)
            $this->extra_attributes['uid'] = intval($uid);
    }


    public static function updateDependencyTree(tx_newspaper_PageZone $pagezone) {
        if (!tx_newspaper_DependencyTree::useDependencyTree()) return;

        /* \todo yeah, instanceof is the devil, but i don't know how to avoid it
         *  without refactoring updateDependencyTree() to be non-static, and i'm
         *  not sure of the consequences.
         */
        if ($pagezone instanceof tx_newspaper_Article) {
            tx_newspaper_Article::updateDependencyTree($pagezone);
        } else {
            tx_newspaper_PageZone_Page::updateDependencyTree($pagezone);
        }
    }

    protected $uid = 0;                ///< The UID of the record in the concrete table
    protected $pagezone_uid = 0;    ///< The UID of the record in the abstract PageZone table
    protected $extra_uid = 0;        ///< The UID of the record in the abstract Extra table

    protected $smarty = null;        ///< Smarty object for rendering

    protected $attributes = array();    ///< array of attributes
    protected $pagezone_attributes = array(); ///< array of attributes for the parent part of the record
    /** @var tx_newspaper_Extra[] */
    protected $extras = array();        ///< array of tx_newspaper_Extra s
    /** @var tx_newspaper_PageZoneType  */
    protected $pagezonetype = null;

    protected $parent_page_id = 0;    ///< UID of the parent Page
    protected $parent_page = null;    ///< Parent Page object

    /// Default Smarty template for HTML rendering
    static protected $defaultTemplate = 'tx_newspaper_pagezone.tmpl';

    /// Temporary variable to store the paragraph of Extras after which a new Extra is inserted
    private $paragraph_for_insert = 0;

    private static $fields_to_copy_into_pagezone_table = array(
        'pid', 'crdate', 'cruser_id', 'deleted',
    );


}

?>