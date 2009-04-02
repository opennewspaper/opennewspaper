<?php
/**
 *  \file class.tx_newspaper_pagezonetype.php
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
class tx_newspaper_PageZoneType implements tx_newspaper_StoredObject {
 	
 	/// Construct a tx_newspaper_PageZoneType given the UID of the SQL record
 	function __construct($uid = 0) {
 		if ($uid) {
 			$this->setUid($uid);
 		}
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
	
	/** \todo Internationalization */
	public function getTitle() {
		return 'Page Zone Type';
	}

 	function setUid($uid) { $this->uid = $uid; }
	function getUid() { return $this->uid; }

	static function getModuleName() { return 'np_pagezonetype'; }

 	public function getTable() { return tx_newspaper::getTable($this); }


	/// get all available page zone types
	/// \return array all available page zone types
	public static function getAvailablePageZoneTypes($include_hidden=true) {
		$where = ($include_hidden)? '' : ' AND hidden=0'; // should hidden pages be included?
		$sf = tx_newspaper_Sysfolder::getInstance();
		$pzt = new tx_newspaper_PageZoneType();
		$row = tx_newspaper::selectRows(
			'*',
			$pzt->getTable(),
			'pid=' . $sf->getPid($pzt) . $where
		);
		return $row;
	}


 	private $uid = 0;
 	private $attributes = array();
}
?>
