<?php
/**
 *  \file class.tx_newspaper_pagezonetype.php
 * 
 *  This file is part of the TYPO3 extension "newspaper".
 * 
 *  Copyright notice
 *
 *  (c) 2008 Helge Preuss, Oliver Schroeder, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
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
 *  \author Oliver Schörder <typo3@schroederbros.de>
 *  \date Mar 25, 2009
 */
 
/// \todo: description
class tx_newspaper_ArticleType implements tx_newspaper_StoredObject {
	
	public function __construct($uid = 0) {
		$uid = intval($uid);
		if ($uid > 0) {
			$this->setUid($uid);
		}
	}
	
	function getAttribute($attribute) {
		/// Read Attributes from persistent storage on first call
		if (!$this->attributes) {
			$this->attributes = tx_newspaper::selectOneRow(
					'*', tx_newspaper::getTable($this), 'uid = ' . $this->getUid() . ' AND deleted=0'
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

	public function getTitle() {
		global $LANG;
		return $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.php:title_' .
						 tx_newspaper::getTable($this), false);
	}

	public function getUid() {
		return intval($this->uid);
	}
	
	public function setUid($uid) {
		$this->uid = $uid;
	}
	
	/// \return Name of the database table the object's data are stored in
	public function getTable() { return tx_newspaper::getTable($this); }
	
	static function getModuleName() { return 'np_articletype'; }
	
	
	
	private $uid = ''; ///< UID that identifies the article type
}
