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
		
	public function __construct($uid = 0) {
		parent::__construct();
		if ($uid) {
			$this->readExtras($uid);
		    $this->readAttributes(self::$table, $uid);
		    $this->pagezonetype = new tx_newspaper_PageZoneType($this->attributes['pagezonetype_id']);
		}
#		t3lib_div::debug($this->attributes);
 	}
	
	/// \todo Return default paragraph of extra of given type in article
	public function getDefaultParagraph($extra_type) {
		throw new tx_newspaper_NotYetImplementedException();
	}
	
	protected function getExtra2PagezoneTable() {
		return self::$extra_2_pagezone_table;
	}

	static protected $extra_2_pagezone_table = 'tx_newspaper_pagezone_article_extras_mm';

 	static protected $table = 'tx_newspaper_pagezone_article';	///< SQL table for persistence
	
}
 
?>
