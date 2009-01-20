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
 
require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_pagezone.php');

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
class tx_newspaper_PageZone_Page extends tx_newspaper_PageZone {
		
	public function __construct($uid) {
		parent::__construct();
		
		/// Read Attributes from persistent storage
		$query = $GLOBALS['TYPO3_DB']->SELECTquery(
			'*', self::$table, "uid = $uid"
		);

		$res =  $GLOBALS['TYPO3_DB']->sql_query($query);
        if (!$res) {
        	/// \todo Throw an appropriate exception
        	throw new tx_newspaper_Exception("couldn't find UID $uid in table " . self::$table);
        }

        $row =  $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        
		if (!$row) {
        	/// \todo Throw an appropriate exception
        	throw new tx_newspaper_Exception();
        }
 		
 		$this->attributes = $row;
 		
 		/// Read Extras from DB
		$query = $GLOBALS['TYPO3_DB']->SELECTquery(
			'*', tx_newspaper_Extra_Factory::getExtraTable(), "uid = $uid"
		);

		$res =  $GLOBALS['TYPO3_DB']->sql_query($query);

		if ($res) {
			/// Populate the tx_newspaper_Extra array 
        	while($row =  $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
        		$this->extras[] = tx_newspaper_Extra_Factory::getInstance()->create($row['uid']);
        	}
		}
	}

 	static protected $table = 'tx_newspaper_pagezone_page';	///< SQL table for persistence
	
}
 
?>
