<?php
/**
 *  \file class.tx_newspaper_pagezonetype.php
 * 
 *  This file is part of the TYPO3 extension "newspaper".
 * 
 *  Copyright notice
 *
 *  (c) 2008 Helge Preuss, Oliver Schroeder, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
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
 *  \author Oliver Schörder <typo3@schroederbros.de>
 *  \date Mar 25, 2009
 */
 
/// \todo: description
class tx_newspaper_ArticleType implements tx_newspaper_StoredObject {
	
	/// \todo: not needed
	public function getUid() {}
	
	/// \todo: not needed
	public function setUid($uid) {}
	
	/// \return Name of the database table the object's data are stored in
	public function getTable() { return tx_newspaper::getTable($this); }
	
	static function getModuleName() { return 'np_articletype'; }
}