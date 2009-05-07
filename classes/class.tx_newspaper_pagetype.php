<?php
/**
 *  \file class.tx_newspaper_pagetype.php
 * 
 *  This file is part of the TYPO3 extension "newspaper".
 * 
 *  Copyright notice
 *
 *  (c) 2008 Helge Preuss, Oliver Schroeder, Samuel Talleux <helge.preuss@gmail.com, oliver@schroederbros.de, samuel@talleux.de>
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
 *  \author Helge Preuss <helge.preuss@gmail.com>
 *  \date Feb 6, 2009
 */
 
/// brief description
/** long description
 */
class tx_newspaper_PageType implements tx_newspaper_StoredObject {
 	
 	/// Construct a tx_newspaper_PageType given the $_GET array
	/**
	 *	Find out which page type we're on (Section, Article, RSS, Comments, whatever)
	 *  \note If we need to change the mapping of GET-parameters to page types, 
	 *  do it here!
	 *
	 *	If $_GET['art'] is set, it is the article page
	 *
	 *	Else if $_GET['page'] is set, it is the page corresponding to that type
	 *
	 *	Else it is the section overview page
	 */
 	function __construct($get = array()) {
 		if (is_int($get)) {
			$this->setUid($get); // just read the record (probably for backend)
			if (TYPO3_MODE == 'BE') {
				$this->condition = 'uid=' . $this->getUid();
			}
 		} else if ($get['art']) {
 			$this->condition = 'get_var = \'art\'';
 		} else {
 			if ($get['page']) { 
				$this->condition = 'get_var = \'page\' AND get_value = '.intval($get['page']);
 			} else {
 				$this->condition = 'NOT get_var';
 			}
 		}
  	}
 	
	/// Convert object to string to make it visible in stack backtraces, devlog etc.
	public function __toString() {
		return get_class($this) . '-object ' . "\n" .
			   'attributes: ' . print_r($this->attributes, 1) . "\n";
	}
 	
 	public function getCondition() { 
 		return $this->condition . tx_newspaper::enableFields(tx_newspaper::getTable($this)); 
 	}

 	public function getID() { return $this->getAttribute('uid'); }
 	
 	function getAttribute($attribute) {
		/// Read Attributes from persistent storage on first call
		if (!$this->attributes) {
			$this->attributes = tx_newspaper::selectOneRow(
				'*', tx_newspaper::getTable($this), $this->getCondition()
			);
			$this->setUid($this->attributes['uid']);
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
					'*', tx_newspaper::getTable($this), $this->condition
			);
		}
		
		$this->attributes[$attribute] = $value;
	}

	/// Write or overwrite Section data in DB, return UID of stored record
	public function store() {
		throw new tx_newspaper_NotYetImplementedException();
	}
	
	/** \todo Internationalization */
	public function getTitle() {
		return 'Page Type';
	}

/// \todo: still needed? see getAvailablePageTypes()
 	static public function getPageTypeList() {
 		throw new tx_newspaper_NotYetImplementedException();
 	}
 	
	static function getModuleName() { return 'np_pagetype'; }
 	
 	public function getTable() { return tx_newspaper::getTable($this); }
	function getUid() { return intval($this->uid); }
	function setUid($uid) { $this->uid = $uid; }


	/// get all available page types
	/// \return array all available page types objects
	public static function getAvailablePageTypes() {
		$sf = tx_newspaper_Sysfolder::getInstance();
		$pt = new tx_newspaper_PageType();
		$row = tx_newspaper::selectRows(
			'*', 
			$pt->getTable(),
			'deleted=0 AND pid=' . $sf->getPid($pt)
		);
#t3lib_div::devlog('gapt row', 'newspaper', 0, $row);
		$list = array();
		for ($i = 0; $i < sizeof($row); $i++) {
			$list[] = new tx_newspaper_PageType(intval($row[$i]['uid']));
		}
		return $list;
	}


 	private $uid = 0;
 	private $condition = '1';
 	private $attributes = array();
}
?>