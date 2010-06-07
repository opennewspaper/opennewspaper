<?php
/**
 *  \file class.tx_newspaper_articlelist_factory.php
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
 *  \date Jan 30, 2009
 */

/// \todo All articlelist definitions must be known to this class, even those which are not part of tx_newspaper 
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_articlelist_manual.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_articlelist_semiautomatic.php');

/// Factory class to create the correct kind of tx_newspaper_ArticleList from a UID
/** Problem: The tx_newspaper_ArticleList is stored in a table for the abstract
 *  parent type. At the time of creation, given only a UID, the concrete type
 *  of the ArticleList pointed to by that UID is not known yet.
 * 
 *  Solution: This factory class.
 * 
 *  This class is implemented as a Singleton.
 * 
 *  Usage: tx_newspaper_ArticleList_Factory::getInstance()->create($uid)
 */
class tx_newspaper_ArticleList_Factory {
	
	/// Returns the only instance of the tx_newspaper_ArticleList_Factory Singleton
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new tx_newspaper_ArticleList_Factory();
		}
		return self::$instance;
	}
	
	/// Create a tx_newspaper_ArticleList given a UID
	/** \param $uid UID of the abstract tx_newspaper_ArticleList record in DB
	 *  \param $section optional: tx_newspaper_Section the tx_newspaper_ArticleList
	 * 		belongs to
	 *  \return A concrete object of a class derived from tx_newspaper_ArticleList
	 */
	public function create($uid, tx_newspaper_Section $section = null) {
		/// Read actual type and UID of the ArticleList to instantiate from DB
        $row =  tx_newspaper::selectOneRow(
			'list_table, list_uid', self::$list_table, "uid = $uid"
		);

        if (!$row['list_table']) {
        	throw new tx_newspaper_DBException('No list_table in result', 
											   $row);
        }
		
		if (!class_exists($row['list_table'])) {
        	throw new tx_newspaper_WrongClassException($row['list_table']);
		}

        if (!$row['list_uid']) {
        	throw new tx_newspaper_DBException('No list_uid in result', 
        									   $row);
        }
		
		$articlelist = new $row['list_table']($row['list_uid'], $section);
		$articlelist->setAbstractUid($uid);
		
		return $articlelist;
	}
	
	/// Protected constructor, tx_newspaper_ArticleList_Factory cannot be created freely
	protected function __construct() { }
	
	/// Cloning tx_newspaper_ArticleList_Factory is prohibited by making __clone private
	private function __clone() {}
	
	/// The only instance of the tx_newspaper_ArticleList_Factory Singleton
	private static $instance = null;
	
	/// SQL table storing the abstract tx_newspaper_ArticleList records
	private static $list_table = 'tx_newspaper_articlelist';
}
 
?>
