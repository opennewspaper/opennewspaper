<?php
/**
 *  \file class.tx_newspaper_section.php
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
 
/// A section of an online edition of a newspaper
/** Currently just a dummy
 * 
 */
 class tx_newspaper_Section {
 	
 	/// Construct a tx_newspaper_Section given the UID of the SQL record
 	function __construct($section_uid) {
		$query = $GLOBALS['TYPO3_DB']->SELECTquery(
			'*', self::$table, "uid = $section_uid"
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
 		
 		/// \todo the rest
 	}
 	
 	static private $table = 'tx_newspaper_section';
 }
 
?>
