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
 
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_pagezone.php');

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
 * 
 *  Default smarty template:
 *  \include res/templates/tx_newspaper_pagezone_page.tmpl
 */
class tx_newspaper_PageZone_Page extends tx_newspaper_PageZone {
		
	public function __construct($uid = 0) {

        $timer = tx_newspaper_ExecutionTimer::create();

		parent::__construct($uid);
		if ($uid) {
			$this->readExtrasForPagezoneID($uid);
		    $this->readAttributes($this->getTable(), $uid);
		    $this->pagezonetype = new tx_newspaper_PageZoneType($this->attributes['pagezonetype_id']);
		    $this->pagezone_uid = $this->createPageZoneRecord();
		}

 	}

	/// \todo Will this work in the parent class too?
	public function __clone() {
 		/*  ensure attributes are loaded from DB. readExtraItem() isn't  
 		 *  called here because maybe the content is already there and it would
 		 *  cause the DB operation to be done twice.
 		 */
		$this->getAttribute('uid');
		
		//  unset the UID so the object can be written to a new DB record.
 		$this->attributes['uid'] = 0;
 		$this->setUid(0);

 		$this->setAttribute('crdate', time());
 		$this->setAttribute('tstamp', time());
 		
 		/// \todo clone extras
 		$old_extras = $this->getExtras();
 		$this->extras = array();
 		foreach ($old_extras as $old_extra) {
 			$this->extras[] = clone $old_extra;
 		}
 	}

	static function getModuleName() { return 'np_pagezone_page'; }

	public function getExtra2PagezoneTable() {
		return self::$extra_2_pagezone_table;
	}

    public static function updateDependencyTree(tx_newspaper_PageZone_Page $pagezone) {
        if (tx_newspaper_DependencyTree::useDependencyTree()) {
            $tree = tx_newspaper_DependencyTree::generateFromPagezone($pagezone);
            $tree->executeActionsOnPages('tx_newspaper_Extra');
        }
    }

	static protected $extra_2_pagezone_table = 'tx_newspaper_pagezone_page_extras_mm';
}
 
?>
