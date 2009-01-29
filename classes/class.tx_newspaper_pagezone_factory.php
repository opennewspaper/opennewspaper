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
 
require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_pagezone_page.php');
require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_pagezone_article.php');

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
		$row = tx_newspaper::selectOneRow(
			'pagezone_table, pagezone_uid', tx_newspaper_PageZone::getName(), "uid = $uid"
		);
        
        if (!$row['pagezone_table']) {
        	throw new tx_newspaper_DBException('No pagezone_table in result', 
											   $row);
        }
		
		if (!class_exists($row['pagezone_table'])) {
        	throw new tx_newspaper_WrongClassException($row['pagezone_table']);
		}

        if (!$row['pagezone_uid']) {
        	throw new tx_newspaper_DBException('No pagezone_uid in result', 
        									   $row);
        }
		
		return new $row['pagezone_table']($row['pagezone_uid']);
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
