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
 *  \author Oliver Schr�der <typo3@schroederbros.de>
 *  \date Feb 9, 2009
 */


/// \todo: deleting and hiding can lead to data inconsistency - check in savehook if newspaper sysfolders are involved???
/// \todo: function to move lost records to the appropriate sysfolders
 
/// Get and create sysfolders for newspaper data
/**
 *  most ideas found in dam extension (http://typo3.org/extensions/repository/view/dam/current/)
 */

class tx_newspaper_Sysfolder {
 	
 	private static $instance = null; ///< use Singleton pattern
 	private $sysfolder = array(); ///< sysfolder are read only once
 	
 	private static $rootfolder_modulename = 'newspaper'; /// module name for root sysfolder
 	private static $rootfolder_sysfolder_name = 'Newspaper'; /// Sysfolder name for root sysfolder
 	
 	protected function __clone() {} // singleton pattern
 	
 	/**
      * get instance (singleton pattern)
      * @return tx_newspaper_Sysfolder
      */
 	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new tx_newspaper_Sysfolder();
		}
		return self::$instance;
 	}
 	
 	/// constructor fills array $this->sysfolder mapping module names to uid in table pages
 	protected function __construct() {
 		/// read and store all tx_newspaper sysfolders
 		$row = tx_newspaper::selectRows('uid, tx_newspaper_module', 'pages', '(tx_newspaper_module="newspaper" OR tx_newspaper_module LIKE "np_%") AND module="newspaper" AND deleted=0 AND doktype=254');
 		for ($i = 0; $i < sizeof($row); $i++) {
 			$this->sysfolder[$row[$i]['tx_newspaper_module']] = $row[$i]['uid'];
 		}
 		
 		/// make sure root sysfolder exists
 		if (!isset($this->sysfolder[self::getRootSysfolderModuleName()])) {
 			$this->createSysfolder(self::getRootSysfolderModuleName(), self::$rootfolder_sysfolder_name);
 		}
 		
 	}
 	
  	/// creates a sysfolder (in Typo3 table pages)
 	/** \param $module_name name of module
 	/** \param $sysfolder_name Title of sys folder
 	 */	
	private function createSysfolder($module_name, $sysfolder_name) {
		
		$module_name = strtolower($module_name);
		
		$fields = array(); // data for sysfolder creation

		if ($module_name == self::getRootSysfolderModuleName()) {
			/// newspaper root sysfolder for module is created on root level in Typo3
			$fields['pid'] = 0;
			$fields['sorting'] = 29999; // insert at the bottom of the page tree
		} else {
			/// all other sysfolders are created within the newspaper root sysfolder
			$fields['pid'] = self::getPidRootfolder(); 
			$fields['sorting'] = self::getNewSysfolderSorting($module_name);
		}
		$fields['module'] = 'newspaper'; // for plugin-list in pages
		$fields['tx_newspaper_module'] = $module_name;
		$fields['title'] = $sysfolder_name;
		$fields['doktype'] = 254;
		$fields['perms_user'] = 31;
		$fields['perms_group'] = 31;
		$fields['perms_everybody'] = 31;
		$fields['crdate'] = time();
		$fields['tstamp'] = time();
		$fields['hidden'] = 0;
		$uid = tx_newspaper::insertRows('pages', $fields); // insert sysfolder and get uid of that sysfolder
		$this->sysfolder[$module_name] = $uid; // append this sysfolder in local storage array
		// \todo: check: t3lib_BEfunc::getSetUpdateSignal('updatePageTree');
	}
	
	/// returns a sorting weight fopr Typo3 page creation in order to get a resonable sorting for newspaper sysfolders
	private function getNewSysfolderSorting($module_name) {
		switch(strtolower($module_name)) {
			case 'np_article':
				return 1;
			break;
			case 'np_section':
				return 2;
			break;
			case 'np_al_manual':
				return 11;
			break;
			case 'np_al_semiauto':
				return 12;
			break;
			case 'np_articletype':
				return 21;
			break;
			case 'np_pagetype':
				return 22;
			break;
			case 'np_pagezonetype':
				return 22;
			break;
			case 'np_page':
				return 29001;
			break;
			case 'np_pagezone_page':
				return 29002;
			break;
		}
		return 20000; // default: lower bottom of sysfolder, but not the very bottom; basically to sort extras above the bottom sysfolders
	}

 	/// gets pids of classes extending the given class
 	/** \param $class_name class name to get pid (if available) of child classes
 	 *  \return array list of pids (for storing records in Typo3 database)
 	 */ 
 	public function getPidsForAbstractClass($class_name) {
 		if ($class_name == '') return array();
 		$child_class = tx_newspaper::getChildClasses($class_name);
		$pid_list = array(); ///< list of all pids associated with class $class
		for ($i = 0; $i < sizeof($child_class); $i++) {
			$tmp_impl = class_implements($child_class[$i]);
			if (isset($tmp_impl['tx_newspaper_StoredObject'])) {
				/// store pid for this (concrete) child class
				// this works because that class implments the tx_newspaper_StoredObject interface
				$pid_list[] = tx_newspaper_Sysfolder::getInstance()->getPid(new $child_class[$i]());
			}
		}
		return $pid_list;
 	}

 	/// gets the uid of the sysfolder to store data in
 	/** \param $obj object implemeting the tx_newspaper_StoredObject interface
 	 *  \return $pid of sysfolder (sysfolder is created if not existing)
 	 */
 	public function getPid(tx_newspaper_StoredObject $obj) {
		return $this->getPidFromArray($obj->getModuleName(), $obj->getTitle());
 	}
 	 
 	/// as no object for the root sysfolder exists, the pid for this folder is handled separately
 	/// \return pid of root sysfolder
 	public function getPidRootfolder() {
		return $this->getPidFromArray(self::getRootSysfolderModuleName(), self::$rootfolder_sysfolder_name);
 	}
 	
 	/// read pid from local array or create sysfolder and return that uid
 	/// \return pid of sysfolder for given module name
	private function getPidFromArray($module_name, $sysfolder_name) {
		
		if (!$sysfolder_name) {
			$sysfolder_name = $module_name; // use module name as fallback if no title available
		}
		
		$module_name = strtolower($module_name);
		self::checkModuleName($module_name);

		// check if sysfolder exists (and create, if not)
 		if (!isset($this->sysfolder[$module_name])) {
 			$this->createSysfolder($module_name, $sysfolder_name); // create and store uid in $this->sysfolder
 		}
 		
 		return $this->sysfolder[$module_name];
	}

	/// checks if module name matches the specification
	/** Specification for module name:
	 *  min 4 chars, max 255 chars for field tx_newspaper_module in table pages
	 *  'np_*' or self::getRootSysfolderModuleName()  
	 *  \param $module_name Module name to be checked
	 */
 	public static function checkModuleName($module_name) {
 		$module_name = strtolower($module_name);
 		
 		if ((strlen($module_name) < 4) || (strlen($module_name) > 255)) {
 			throw new tx_newspaper_SysfolderIllegalModulenameException($module_name);
 		}

 		if ($module_name != self::getRootSysfolderModuleName() && substr($module_name, 0, 3) != 'np_') {
 			throw new tx_newspaper_SysfolderIllegalModulenameException($module_name);
 		}
 		
 	}

	/// gets the name of the root sysfolder
	public static function getRootSysfolderModuleName() {
		return self::$rootfolder_modulename;
	}
 	
 	/// create all sysfolder newspaper might need
 	public static function createAll() {
		foreach(get_declared_classes() as $class) {
			if (!tx_newspaper::isAbstractClass($class) && tx_newspaper::classImplementsInterface($class, 'tx_newspaper_StoredObject')) {
				$dummy = self::getInstance()->getPid(new $class); // this creates the sysfolder if it does not exist
			}
		}	 		
 	}
}

?>