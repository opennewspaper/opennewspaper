<?php
/**
 *  \file class.tx_newspaper_pagezone_factory.php
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
 *  \date Jan 8, 2009
 */
 
/// Factory class to create the correct kind of tx_newspaper_PageZone from a UID
/** Problem: The tx_newspaper_PageZone is stored in a table for the abstract
 *  parent type. At the time of creation, given only a UID, the concrete type
 *  of the PageZone pointed to by that UID is not known yet.
 * 
 *  Solution: This factory class.
 * 
 *  This class is implemented as a Singleton.
 */
class tx_newspaper_PageZone_Factory {
	
	/// Returns the only instance of the tx_newspaper_PageZone_Factory Singleton
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new tx_newspaper_PageZone_Factory();
		}
		return self::$instance;
	}
	
	public function create($uid) {
		/// Read actual type and UID of the PageZone to instantiate from DB
		$query = $GLOBALS['TYPO3_DB']->SELECTquery(
			'pagezone_table, pagezone_uid', tx_newspaper_PageZone::getName(), "uid = $uid"
		);

		$res =  $GLOBALS['TYPO3_DB']->sql_query($query);
        if (!$res) {
        	/// \todo Throw an appropriate exception
        	throw new tx_newspaper_Exception();
        }

        $row =  $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		
		throw new tx_newspaper_NotYetImplementedException('tx_newspaper_PageZone_Factory::create()' . print_r($row, 1));
	}
	
	/// Protected constructor, tx_newspaper_PageZone_Factory cannot be created freely
	protected function __construct() { }
	
	/// Cloning tx_newspaper_PageZone_Factory is prohibited by making __clone private
	private function __clone() {}
	
	// attributes
	
	/// The only instance of the tx_newspaper_PageZone_Factory Singleton
	private static $instance = null;
 	
}
 
?>
