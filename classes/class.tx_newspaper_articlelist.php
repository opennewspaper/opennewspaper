<?php
/**
 *  \file class.tx_newspaper_articlelist.php
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
 
 /// A list of tx_newspaper_Article s
 /**  
  *  abstract functions:
  *  - function getArticles($number, $start)
  *	 - public static function getModuleName()
  */
abstract class tx_newspaper_ArticleList implements tx_newspaper_StoredObject {
	public function __construct($uid = 0, tx_newspaper_Section $section = null) {
		t3lib_div::devlog('tx_newspaper_ArticleList::__construct()', 'newspaper', 0, 
			array(
				'uid' => $uid, 
				'section' => $section
			)
		);
		if (intval($uid)) {
			$this->setUid($uid);
	 		$this->attributes = tx_newspaper::selectOneRow(
				'*', $this->getTable(), "uid = $uid"
			);
			t3lib_div::devlog('getTable()', 'newspaper', 0, $this->getTable());
			t3lib_div::devlog('attributes', 'newspaper', 0, $this->attributes);
			
		}
		if ($section) {
			$this->section = $section;
		} elseif ($this->getUid()) {
			try {
			if ($this->getAttribute('section_id')) {
				$this->section = new tx_newspaper_Section($this->getAttribute('section_id'));
			}
			} catch (tx_newspaper_WrongAttributeException $e) {
				t3lib_div::devlog('wrong attribute exception', 'newspaper', 1, $e);
			}
		}
	}

	/// Convert object to string to make it visible in stack backtraces, devlog etc.
	public function __toString() {
		return get_class($this) . '-object ' . "\n" .
			   'attributes: ' . print_r($this->attributes, 1) . "\n" .
			   'abstract attributes: ' . print_r($this->abstract_attributes, 1);
	}

	public function getArticle($index) {
		$articles = $this->getArticles(1, $index);
		return $articles[0];
	}

	abstract function getArticles($number, $start = 0);

	public function getTable() { return tx_newspaper::getTable($this); }
	public function getUid() { return intval($this->uid); }
	public function setUid($uid) { $this->uid = $uid; }

	static public function registerArticleList(tx_newspaper_ArticleList $newList) {
		self::$registered_articlelists[] = $newList;
	}

	static public function getRegisteredArticleLists() {
		return self::$registered_articlelists;
	}

	function getAttribute($attribute) {
		/// Read Attributes from persistent storage on first call
		if (!$this->abstract_attributes) {
			$this->abstract_attributes = $this->getAbstractUid()? 
				tx_newspaper::selectOneRow('*', self::$table, 'uid = ' . $this->getAbstractUid()): 
				array();
		}

		if (!$this->attributes) {
			$this->attributes = tx_newspaper::selectOneRow(
					'*', tx_newspaper::getTable($this), 'uid = ' . $this->getUid()
			);
		}


 		if (array_key_exists($attribute, $this->abstract_attributes)) {
	 		return $this->abstract_attributes[$attribute];
 		}
 		if (array_key_exists($attribute, $this->attributes)) {
	 		return $this->attributes[$attribute];
 		}

        throw new tx_newspaper_WrongAttributeException(
        	$attribute . 
        	' [ ' . print_r($this->attributes, 1) . ',' .
        	print_r($this->abstract_attributes, 1) . ' ]');
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


	/// Write or overwrite article list in DB, return UID of stored record 
	public function store() {

		if ($this->getUid()) {
			
			// read attributes initially
			if (!$this->attributes) {
				$this->readAttributes($this->getTable(), $this->getUid());
			}			
				
			tx_newspaper::updateRows(
				$this->getTable(), 'uid = ' . $this->getUid(), $this->attributes
			);
		} else {
			///	Store a newly created article list
			/// \todo If the PID is not set manually, $tce->process_datamap() fails silently. 
			$this->attributes['pid'] = tx_newspaper_Sysfolder::getInstance()->getPid($this);

			$this->setUid(
				tx_newspaper::insertRows(
					$this->getTable(), $this->attributes
				)
			);
		}

		/// Ensure the article list has an entry in the abstract super table...
		$articlelist_uid = $this->createArticleListRecord($this->getUid(), $this->getTable());
		return $this->getUid();
	}


 	/// Create the record for a concrete ArticleList in the table of abstract ArticleList
	/** This is probably necessary because a concrete ArticleList has been freshly
	 *  created.
	 *  Does nothing if the concrete ArticleList is already linked in the abstract table. 
	 * 
	 *  \param $uid UID of the ArticleList in the table of concrete ArticleList
	 *  \param $table Table of concrete ArticleList
	 *  \return UID of abstract ArticleList record
	 */ 
	public function createArticleListRecord($uid, $table) {
		/// read typo3 fields to copy into article list super table
		$row = tx_newspaper::selectOneRow(
			implode(', ', self::$fields_to_copy_into_articlelist_table),
			$table,
			'uid = ' . intval($uid)
		);
	
		/// write the uid and table into page zone table, with the values read above
		$row['list_uid'] = $uid;
		$row['list_table'] = $table;
		$row['tstamp'] = time();				///< tstamp is set to now
		
		// assign article list with section (if any)
		if ($this->section)
			$row['section_id'] = $this->section->getUid();

		$al_uid = tx_newspaper::insertRows('tx_newspaper_articlelist', $row);
		
		/// add article to section (if any)
		if ($this->section) {
			tx_newspaper::updateRows(
				$this->section->getTable(), 
				'uid=' . $this->section->getUid(),
				array('articlelist' => $al_uid)	
			);
		}

		return $al_uid; 		
	}


	/// get uid of abtract article list
	/// \return UID of abstract ArticleList record, or 0 if no record was found
	public function getAbstractUid() {
		if ($this->abstract_uid)
			return $this->abstract_uid;
			
		// add section to query if this article list is assigned to a section
		$where = '';
		if ($this->section instanceof tx_newspaper_Section) 
			$where = ' AND section_id = ' . intval($this->section->getUid());

		$row = tx_newspaper::selectZeroOrOneRows(
			'uid', 'tx_newspaper_articlelist', 
			'list_table = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->getTable(), $this->getTable()) . 
			' AND list_uid=' . $this->getUid() . $where .
			tx_newspaper::enableFields('tx_newspaper_articlelist')
		);
		if ($row) 
			$this->abstract_uid = $row['uid'];
		return $this->abstract_uid;
	}
	
	public function setAbstractUid($uid) {
		$this->abstract_uid = $uid;
	}
	
	public function getTitle() {
		global $LANG;
		return $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:title_' .
						 tx_newspaper::getTable($this), false);
	}

	private $uid = 0;
	protected $abstract_uid = 0;

	protected $attributes = array();
	protected $abstract_attributes = array();
	protected $section = null;

	/// SQL table for persistence
	static protected $table = 'tx_newspaper_articlelist';
	/// SQL table for tx_newspaper_Section objects	
	static protected $section_table = 'tx_newspaper_section';
	/// Array for registered article lists (subclasses of tx_newspaper_ArticleList)
	static protected $registered_articlelists = array();
	
	private static $fields_to_copy_into_articlelist_table = array(
		'pid', 'crdate', 'cruser_id'
	);

}


?>
