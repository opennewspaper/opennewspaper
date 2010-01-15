<?php
/**
 *  \file class.tx_newspaper_pagetype.php
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
 *  \date Feb 6, 2009
 */
 
/// Type of the page that is displayed.
/** Newspaper allows for (more or less) freely configurable types of pages for
 *  each tx_newspaper_Section. As always, the concept might best be explained by
 *  examples.
 * 
 *  - The List of articles belonging to a Section is shown on the Section
 *    Overview Page.
 *  - Articles belonging to a Section are displayed on the Article Page.
 *  - An Article may be displayed differently for printing on a Print Page.
 *  - There may be a RSS Page for RSS feeds.
 *  - etc. pp.
 * 
 *  Each of the Pages can be fully configured with Page Zones and Extras,
 *  independently from each other.
 * 
 *  Which Page Type is currently shown in the Frontend, is decided by the GET
 *  parameters the current (Typo3) page is called with. 
 * 
 *  \see tx_newspaper_Page.
 */
class tx_newspaper_PageType implements tx_newspaper_StoredObject {
 	
 	/// Construct a tx_newspaper_PageType given the $_GET array
	/**
	 *	Find out which page type we're on (Section, Article, RSS, Comments, whatever)
	 *  \note If we need to change the mapping of GET-parameters to page types, 
	 *  do it here!
	 *
	 *  - If $_GET['pagetype'] is set, it is the page corresponding to that type
	 *    - The parameter 'pagetype' is in fact configurable via 
	 *      tx_newspaper::GET_pagetype().
	 *  - If any other GET variable configured in the table tx_newspaper_pagetype
	 *    is set, the PageType corresponding to that is instantiated.
	 *  - If $_GET['art'] is set, it is the article page. $_GET['art'] is 
	 *    checked last, so it does not override any other page type which might
	 *    display a specific article.
	 *  - If no GET variable configured to define a page type is set, it is the
	 *    section overview page.
	 */
 	function __construct($get = array()) {
 		if (is_int($get)) {
			$this->setUid($get); // just read the record (probably for backend)
			if (TYPO3_MODE == 'BE') {
				$this->condition = 'uid=' . $this->getUid();
			}
 		} else if ($get[tx_newspaper::GET_pagetype()]) { 
				$this->condition = 'get_var = \'' . tx_newspaper::GET_pagetype() .
					'\' AND get_value = '.intval($get[tx_newspaper::GET_pagetype()]);
 		} else {
			t3lib_div::devlog('GET', 'gna', 0, $get);
 			//  try all page types other than the article page first 
 			$possible_types = tx_newspaper::selectRows(
 				'DISTINCT get_var', tx_newspaper::getTable($this),
 				'get_var != \'' . tx_newspaper::GET_pagetype() .'\' AND ' .
 				'get_var != \'' . tx_newspaper::GET_article() .'\' AND ' .
 				'get_var != \'\''
 			);
 			t3lib_div::devlog('gna', 'gna', 0, $possible_types);
 			foreach ($possible_types as $type) {
	 			t3lib_div::devlog('checking...', 'gna', 0, $type);
				$get_var = $type['get_var'];

 				// transform $get[skpc[sc]] to $get[skpc][sC]
				if (strpos($get_var, ']') !== false) { 				
					$parts = explode('[', $get_var);
					foreach($parts as $key => $part) $parts[$key] = rtrim($part, ']');
					
		 			t3lib_div::devlog('parts', 'gna', 0, $parts);
		 			$get_var = implode('][', $parts);
				}
				
 				if ($get[$get_var]) {
		 			t3lib_div::devlog('found', 'gna', 0, $type['get_var']);
 					$this->condition = 'get_var = \'' . $type['get_var'] .'\'';
 					return;
 				}
 			}
 			if ($get[tx_newspaper::GET_article()]) {
 				$this->condition = 'get_var = \'' . tx_newspaper::GET_article() .'\'';
			} else {
				$this->condition = 'NOT get_var';
 			}
 		}
  	}
 	
	/// Convert object to string to make it visible in stack backtraces, devlog etc.
	public function __toString() {
		return get_class($this) . '-object ' . "\n" .
			   'attributes: ' . print_r($this->attributes, 1) . "\n";
	}
 	
 	public function getCondition() { 
 		return $this->condition . tx_newspaper::enableFields(tx_newspaper::getTable($this)); 
 	}

 	public function getID() { return $this->getAttribute('uid'); }
 	
 	function getAttribute($attribute) {
		/// Read Attributes from persistent storage on first call
		if (!$this->attributes) {
			$this->attributes = tx_newspaper::selectOneRow(
				'*', tx_newspaper::getTable($this), $this->getCondition()
			);
			$this->setUid($this->attributes['uid']);
		}

 		if (!array_key_exists($attribute, $this->attributes)) {
        	throw new tx_newspaper_WrongAttributeException($attribute);
 		}
 		return $this->attributes[$attribute];
 	}
 	/** No tx_newspaper_WrongAttributeException here. We want to be able to set
	 *  attributes, even if they don't exist beforehand.
	 */
	public function setAttribute($attribute, $value) {
		if (!$this->attributes) {
			$this->attributes = tx_newspaper::selectOneRow(
					'*', tx_newspaper::getTable($this), $this->condition
			);
		}
		
		$this->attributes[$attribute] = $value;
	}

	/// Write or overwrite Section data in DB, return UID of stored record
	public function store() {
		throw new tx_newspaper_NotYetImplementedException();
	}
	
	/// \return true if page type can be accessed (FE/BE use enableFields)
	function isValid() {
		// check if page type is valid
		try {
			$this->getAttribute('uid'); // getAttribute forces the object to be read from database
			return true;
		} catch (tx_newspaper_EmptyResultException $e) {
			return false;
		}
	}
	
	public function getTitle() {
		global $LANG;
		if (!($LANG instanceof language)) {
			require_once(t3lib_extMgm::extPath('lang', 'lang.php'));
			$LANG = t3lib_div::makeInstance('language');
			$LANG->init('default');
		}
		return $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:title_' . $this->getTable(), false);	
	}

/// \todo: still needed? see getAvailablePageTypes()
 	static public function getPageTypeList() {
 		throw new tx_newspaper_NotYetImplementedException();
 	}
 	
	static function getModuleName() { return 'np_pagetype'; }
 	
 	public function getTable() { return tx_newspaper::getTable($this); }
	function getUid() { return intval($this->uid); }
	function setUid($uid) { $this->uid = $uid; }


	/// get all available page types
	/// \return array all available page types objects
	public static function getAvailablePageTypes() {
		$sf = tx_newspaper_Sysfolder::getInstance();
		$pt = new tx_newspaper_PageType();
		$row = tx_newspaper::selectRows(
			'*', 
			$pt->getTable(),
			'deleted=0 AND pid=' . $sf->getPid($pt)
		);
#t3lib_div::devlog('gapt row', 'newspaper', 0, $row);
		$list = array();
		for ($i = 0; $i < sizeof($row); $i++) {
			$list[] = new tx_newspaper_PageType(intval($row[$i]['uid']));
		}
		return $list;
	}


 	private $uid = 0;
 	private $condition = '1';
 	private $attributes = array();
}
?>