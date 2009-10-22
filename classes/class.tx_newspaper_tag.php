<?php
/**
 *  \file class.tx_newspaper_tag.php
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
 *  \author Oliver Sch�rder <typo3@schroederbros.de>
 *  \date Mar 25, 2009
 */
 
/// Tag
/** \todo document me!
 */
class tx_newspaper_Tag implements tx_newspaper_StoredObject {
	
	public function __construct($uid = 0) {
		$uid = intval($uid);
		if ($uid > 0) {
			$this->setUid($uid);
		}
	}
	
	/// Convert object to string to make it visible in stack backtraces, devlog etc.
	public function __toString() {
		try {
			return $this->getAttribute('tag');
		} catch (tx_newspaper_Exception $e) { 
			return 'Unstored tx_newspaper_Tag object';
		}
	}
	
	public function getAttribute($attribute) {
		/// Read Attributes from persistent storage on first call
		if (!$this->attributes) {
			$this->attributes = tx_newspaper::selectOneRow(
				'*', 
				tx_newspaper::getTable($this),
				'uid=' . $this->getUid() . tx_newspaper::enableFields(tx_newspaper::getTable($this))
			);
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
					'*', tx_newspaper::getTable($this), 'uid = ' . $this->getUid()
			);
		}
		
		$this->attributes[$attribute] = $value;
	}

	/// Write or overwrite Section data in DB, return UID of stored record
	public function store() {
		throw new tx_newspaper_NotYetImplementedException();
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

	public function getUid() {
		return intval($this->uid);
	}
	
	public function setUid($uid) {
		$this->uid = $uid;
	}
	
	/// \return Name of the database table the object's data are stored in
	public function getTable() { return tx_newspaper::getTable($this); }
	
	static public function getModuleName() { return 'np_tag'; }
	
	/// Given a partial tag, return all possible completions for that tag
	/** \param $fragment A string to interpret as a part of a tag
	 *  \param $max Maximum number of hints returned
	 *  \param $start_only If \c true, returns only tags beginning with
	 *  	\p $fragment
	 *  \param $strong If \c true, fatten the requested portion of the tag that
	 * 		has been searched for.
	 *  \return Array of tags (as strings, not UIDs) that match \p $fragment
	 */
	static public function getCompletions($fragment, 
										  $max = 0, 
										  $start_only = false, 
										  $strong = false) {
		$results = tx_newspaper::selectRows(
			'tag', 'tx_newspaper_tag',
			'tag LIKE ' . ($start_only? '': '%') . $fragment . '%',
			'', '', ($max? ('0, ' . $max): '')
		);

		$return = array();

		if ($results) {
			foreach ($results as $row) {
				$return[] = str_replace($fragment, 
										($strong? '<strong>': '') . $fragment . ($strong? '</strong>': ''), 
										$row['tag']);
			}
		}
		
		return $return;
	}
	
	////////////////////////////////////////////////////////////////////////////
	
	private $uid = ''; ///< UID that identifies the tag in the DB
}
