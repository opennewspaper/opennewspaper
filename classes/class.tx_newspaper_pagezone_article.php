<?php
/**
 *  \file class.tx_newspaper_pagezone_article.php
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
 
/// An article represented as PageZone to allow placement of extras
class tx_newspaper_PageZone_Article extends tx_newspaper_PageZone {
		
	public function __construct($uid) {
		parent::__construct($uid);
		
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
        	throw new tx_newspaper_Exception("couldn't find UID $uid in table " . self::$table);
        }
 		
 		$this->attributes = $row;
 		
 		/// Read Extras from DB
		$query = $GLOBALS['TYPO3_DB']->SELECTquery(
			'uid_foreign', $this->getExtra2PagezoneTable(), "uid_local = $uid"
		);

		$res =  $GLOBALS['TYPO3_DB']->sql_query($query);

		if ($res) {
			/// Populate the tx_newspaper_Extra array 
        	while($row =  $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
        		$this->extras[] = tx_newspaper_Extra_Factory::getInstance()->create($row['uid']);
        	}
		}
 		
	}
	
	protected function getExtra2PagezoneTable() {
		return self::$extra_2_pagezone_table;
	}
	
	
	static protected $extra_2_pagezone_table = 'tx_newspaper_pagezone_article_extras_mm';

 	static protected $table = 'tx_newspaper_pagezone_article';	///< SQL table for persistence
	
}
 
?>
