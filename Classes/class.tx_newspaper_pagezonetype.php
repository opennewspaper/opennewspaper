<?php
/**
 *  \file class.tx_newspaper_pagezonetype.php
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
 *  \date Feb 6, 2009
 */

/// brief description
/** long description
 */
class tx_newspaper_PageZoneType implements tx_newspaper_StoredObject {

     /// Construct a tx_newspaper_PageZoneType given the UID of the SQL record
     function __construct($uid = 0) {
         if ($uid) {
             $this->setUid($uid);
         }
     }

    /// Convert object to string to make it visible in stack backtraces, devlog etc.
    public function __toString() {
        return get_class($this) . '-object ' . "\n" .
               'attributes: ' . print_r($this->attributes, 1) . "\n";
    }

    function getAttribute($attribute) {
        /// Read Attributes from persistent storage on first call
        if (!$this->attributes) {
            $this->attributes = tx_newspaper::selectOneRow(
                '*', tx_newspaper::getTable($this), 'uid = ' . $this->getUid()
            );
        }

         if (!array_key_exists($attribute, $this->attributes)) {
            throw new tx_newspaper_WrongAttributeException($attribute);
         }
         return $this->attributes[$attribute];
     }
      /** No tx_newspaper_WrongAttributeException here. We want to be able to set
      *  attributes, even if they don't exist beforehand.
      */
    public function setAttribute($attribute, $value) {
        if (!$this->attributes) {
            $this->attributes = tx_newspaper::selectOneRow(
                    '*', tx_newspaper::getTable($this), 'uid = ' . $this->getUid()
            );
        }

        $this->attributes[$attribute] = $value;
    }

    /// Write or overwrite Section data in DB, return UID of stored record
    public function store() {
        throw new tx_newspaper_NotYetImplementedException();
    }

    /// \return true if pagezone type can be accessed (FE/BE use enableFields)
    function isValid() {
        // check if pagezone type is valid
        try {
            $this->getAttribute('uid'); // getAttribute forces the object to be read from database
            return true;
        } catch (tx_newspaper_EmptyResultException $e) {
            return false;
        }
    }

    public function getTitle() {
        return tx_newspaper::getTranslation('title_' . $this->getTable());
    }

     function setUid($uid) { $this->uid = $uid; }
    function getUid() { return $this->uid; }

    static function getModuleName() { return 'np_pagezonetype'; }

     public function getTable() { return tx_newspaper::getTable($this); }


    /** 
     * Get all available page zone types
     * @param bool $includeDefaultArticle Are default articles included?
     * @return tx_newspaper_PageZoneType[] all available page zone types objects 
     */
    public static function getAvailablePageZoneTypes($includeDefaultArticle=true) {
        $sf = tx_newspaper_Sysfolder::getInstance();
        $pzt = new tx_newspaper_PageZoneType();
        $row = tx_newspaper::selectRows(
            '*',
            $pzt->getTable(),
            'deleted=0 AND pid=' . $sf->getPid($pzt)
        );
//t3lib_div::devlog('gapzt row', 'newspaper', 0, array('row' => $row));
        $list = array();
        for ($i = 0; $i < sizeof($row); $i++) {
            if (!$row[$i]['is_article'] || $includeDefaultArticle) {
                $list[] = new tx_newspaper_PageZoneType(intval($row[$i]['uid']));
            }
        }
        return $list;
    }


     private $uid = 0;
     private $attributes = array();
}
?>