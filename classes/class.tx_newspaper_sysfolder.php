<?php
/**
 *  \file class.tx_newspaper_sysfolder.php
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
 *  \author Oliver Schröder <typo3@schroederbros.de>
 *  \date Feb 9, 2009
 */


/// \to do: deleting and hiding can lead to data inconsistency - check in savehook if newspaper sysfolders are involved???

/// \to do: function to move lost records to the appropriate sysfolders
 
/// Get and create sysfolders for newspaper data
/**
 *  most ideas found in dam extension (http://typo3.org/extensions/repository/view/dam/current/)
 */

class tx_newspaper_Sysfolder {
 	
 	private static $instance = null; ///< use Singleton pattern
 	private $sysfolder = array(); 
 	
 	protected function __clone() {} ///< singleton pattern
 	
 	/// get instance (singeton pattern)
 	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new tx_newspaper_Sysfolder();
		}
		return self::$instance;
 	}
 	
 	/// constructor fills arary $this->sysfolder mapping module names to uid in table pages
 	protected function __construct() {
 		// read and store all tx_newspaper sysfolders
 		$row = tx_newspaper::selectRows('uid, module', 'pages', '(module="newspaper" OR module LIKE "np_%") AND deleted=0 AND doktype=254');
 		for ($i = 0; $i < sizeof($row); $i++) {
 			$this->sysfolder[$row['module']] = $row['uid'];
 		}
t3lib_div::debug($this->sysfolder); 		
 		// make sure sysfolder 'newspaper' exists
 		if (!isset($this->sysfolder['newspaper'])) {
 			$this->createSysfolder('newspaper');
 		}
t3lib_div::debug($this->sysfolder);
 		
 	}
 	
 	
 
  	/// creates a sysfolder (in Typo3 table pages)
 	/** \param $module_name name of module
 	 *  \return $pid pid of sysfolder
 	 */	
	private function createSysfolder($module_name) {
		
		$module_name = strtolower($module_name);
		
		$fields = array(); // data for sysfolder creation
		if ($module_name == 'newspaper') {
			/// sysfolder for module 'newspaper' is created on root level in Typo3
			$fields['pid'] = 0;
			$fields['sorting'] = 29999; // insert at the bottom of the page tree
		} else {
			/// all other sysfolders are created within the 'newspaper' sysfolder
			$fields['pid'] = self::getPid('newspaper'); 
		}
		$fields['module'] = $module_name;
		$fields['title'] = $module_name;
		$fields['doktype'] = 254;
		$fields['perms_user'] = 31;
		$fields['perms_group'] = 31;
		$fields['perms_everybody'] = 31;
		$fields['crdate'] = time();
		$fields['tstamp'] = time();
		
		$uid = tx_newspaper::insertRows('pages', $fields); // insert sysfolder and get uid of that sysfolder
		$this->sysfolder[$module_name] = $uid; // append this sysfolder
		
	}
 	
 	
 	/// gets the uid of the sysfolder to store data in
 	/** \param Extra $extra
 	 *  \return $pid of sysfolder (sysfolder is created if not existing)
 	 */
 	public function getPid(tx_newspaper_InSysFolder $obj) {
 		
 		$module_name = strtolower($obj->getModuleName());
		self::checkModuleName($module_name);

		// check if sysfolder exists (and create, if not)
 		if (!isset($this->sysfolder[$module_name])) {
 			$this->createSysfolder($module_name); // create and store uid in $this->sysfolder
 		}
 		
 		return $this->sysfolder[$module_name];
 		
 	} 


	/// checks if module name matches the specification
	/** Specification for module name:
	 *  max 10 charcters (Typo3 condition) for field module in table pages
	 *  'np_*' or 'newspaper'  
	 *  \param $name Module name to be checked
	 */
 	public static function checkModuleName($module_name) {
 		$module_name = strtolower($module_name);
 		
 		if (strlen($module_name) > 10) {
 			throw new tx_newspaper_SysfolderIllegalModulenameException($module_name);
 		}
 		
 		if ($module_name != 'newspaper' && substr($module_name, 0, 3) != 'np_') {
 			throw new tx_newspaper_SysfolderIllegalModulenameException($module_name);
 		}
 		
 	}

 	
}

?>