<?php
/**
 *  \file class.tx_newspaper_extra_factory.php
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

/// \todo All extra definitions must be known to this class, even those which are not part of tx_newspaper 
require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/extra/class.tx_newspaper_extra_image.php');
require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/extra/class.tx_newspaper_extra_articlerenderer.php');

/// Factory class to create the correct kind of tx_newspaper_Extra from a UID
/** Problem: The tx_newspaper_Extra is stored in a table for the abstract
 *  parent type. At the time of creation, given only a UID, the concrete type
 *  of the Extra pointed to by that UID is not known yet.
 * 
 *  Solution: This factory class.
 * 
 *  This class is implemented as a Singleton.
 */
class tx_newspaper_Extra_Factory {
	
	/// Returns the only instance of the tx_newspaper_Extra_Factory Singleton
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new tx_newspaper_Extra_Factory();
		}
		return self::$instance;
	}
	
	public function create($uid) {
		/// Read actual type and UID of the Extra to instantiate from DB
        $row =  tx_newspaper::selectOneRow(
			'extra_table, extra_uid', self::$extra_table, "uid = $uid"
		);

        if (!$row['extra_table']) {
        	throw new tx_newspaper_DBException('No extra_table in result', 
											   $row);
        }
		
		if (!class_exists($row['extra_table'])) {
        	throw new tx_newspaper_WrongClassException($row['extra_table']);
		}

        if (!$row['extra_uid']) {
        	throw new tx_newspaper_DBException('No extra_uid in result', 
        									   $row);
        }
		
		return new $row['extra_table']($row['extra_uid']);
	}
	
	static function getExtraTable() { return self::$extra_table; } 
	
	/// Protected constructor, tx_newspaper_Extra_Factory cannot be created freely
	protected function __construct() { }
	
	/// Cloning tx_newspaper_Extra_Factory is prohibited by making __clone private
	private function __clone() {}
	
	// attributes
	
	/// The only instance of the tx_newspaper_Extra_Factory Singleton
	private static $instance = null;
	
	/// Extra table must be defined here because tx_newspaper_Extra is an interface
	private static $extra_table = 'tx_newspaper_extra';
 	
}
 
?>
