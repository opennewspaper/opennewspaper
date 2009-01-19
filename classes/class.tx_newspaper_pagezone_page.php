<?php
/**
 *  \file class.tx_newspaper_pagezone_page.php
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
 
/// A section of a page for an online edition of a newspaper
/** Pages are divided into several independent sections, or zones, such as:
 *  - Left column, containing the main content area (article text, list of 
 * 	  articles)
 *  - Right column with additional info or ads
 *  - footer area  
 *  A PageZone contains a list of content elements.
 * 
 *  Class tx_newspaper_PageZone implements the tx_newspaper_Extra interface,
 *  because a PageZone can be placed like an Extra.
 */
abstract class tx_newspaper_PageZone_Page extends tx_newspaper_PageZone {
		
	public function __construct() {
		parent::__construct($uid);
		
		/// Read Attributes from persistent storage
		$query = $GLOBALS['TYPO3_DB']->SELECTquery(
			'*', self::$table, "uid = $uid"
		);

		$res =  $GLOBALS['TYPO3_DB']->sql_query($query);
        if (!$res) {
        	/// \todo Throw an appropriate exception
        	throw new tx_newspaper_Exception();
        }

        $row =  $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        
		if (!$row) {
        	/// \todo Throw an appropriate exception
        	throw new tx_newspaper_Exception();
        }
 		
 		$this->attributes = $row;
	}
	
	/// Render the page zone, containing all extras
	/**
	 *  \todo default smarty template?
	 * 
	 *  \return The rendered page as HTML (or XML, if you insist) 
	 */
 	public function render($template) {
		/// \todo assign smarty variables
 		return $this->smarty->fetch($template);
 	}
 	
 	/// returns an actual member (-> Extra)
	function getAttribute($attribute) {
		return $this->attributes[$attribute];
	}

	/// sets a member (-> Extra)
	function setAttribute($attribute, $value) {
		$this->attributes[$attribute] = $value;
	}
	
	/// Defined to implement the tx_newspaper_Extra interface
	/** Of course it sucks that we implement an interface that, well, we don't
	 *  implement fully. 
	 *  \todo Think of a way out of this.
	 */
	function getSource() { 
		throw new tx_newspaper_IllegalUsageException(
			"tx_newspaper_PageZone::getSource(): " .
			"PageZone should never deal with Sources");
	}

	/// Defined to implement the tx_newspaper_Extra interface
	/** Of course it sucks that we implement an interface that, well, we don't
	 *  implement fully. 
	 *  \todo Think of a way out of this.
	 */
	function setSource(tx_newspaper_Source $source) {
		throw new tx_newspaper_IllegalUsageException(
			"tx_newspaper_PageZone::setSource(): " .
			"PageZone should never deal with Sources");
	 }
	
	/// Defined to implement the tx_newspaper_Extra interface
	/** Of course it sucks that we implement an interface that, well, we don't
	 *  implement fully. 
	 *  \todo Think of a way out of this.
	 */
	static function mapFieldToSourceField($fieldname, tx_newspaper_Source $source) {
		throw new tx_newspaper_IllegalUsageException(
			"tx_newspaper_PageZone::mapFieldToSourceField(): " .
			"PageZone should never deal with Sources");
	}
	
	/// Defined to implement the tx_newspaper_Extra interface
	/** Of course it sucks that we implement an interface that, well, we don't
	 *  implement fully. 
	 *  \todo Think of a way out of this.
	 */
	static function sourceTable(tx_newspaper_Source $source) {
		throw new tx_newspaper_IllegalUsageException(
			"tx_newspaper_PageZone::sourceTable(): " .
			"PageZone should never deal with Sources");
	}

	static function getName() {
		return self::$table;
	}

	/** \todo Internationalization */
	static function getTitle() {
		return 'PageZone';
	}
	
	
	static function getModuleName() {
		throw new tx_newspaper_NotYetImplementedException("tx_newspaper_PageZone::getModuleName()");
	}

	static function readExtraItem($uid, $table) {
		throw new tx_newspaper_NotYetImplementedException("tx_newspaper_PageZone::readExtraItem()");
	}
 	
 	private $smarty = null;
 	
 	private $attributes = array();	///< array of attributes
 	
 	static private $table = 'tx_newspaper_pagezone';///< SQL table for persistence
 	
}
 
?>
