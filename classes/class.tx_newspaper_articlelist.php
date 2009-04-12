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
		if ($uid) {
			$this->setUid($uid);
	 		$this->attributes = tx_newspaper::selectOneRow(
				'*', $this->getTable(), "uid = $uid"
			);
		}
		if ($section) {
			$this->section = $section;
		} else {
			$this->section = new tx_newspaper_Section($this->attributes['section_id']);
		}
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
		if (!$this->attributes) {
			$this->attributes = tx_newspaper::selectOneRow(
					'*', tx_newspaper::getTable($this), 'uid = ' . $this->getUid()
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
	public static function createArticleListRecord($uid, $table) {
		/// Check if record is already present in articlelist table
		$row = tx_newspaper::selectZeroOrOneRows(
			'uid', 'tx_newspaper_articlelist', 
			'list_table = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($table, $table) .
			' AND list_uid = ' . intval($uid)
		);
		if (sizeof($row) > 0) {
			if ($row['deleted'] == 0 && $row['uid']) return $row['uid']; // active record found
			if ($row['deleted'] == 1 && $row['uid']) {
				/// reactivate old record
				// check if referenced record is still available
				$row2 = tx_newspaper::selectZeroOrOneRows(
					'uid', $row['list_table'], 
					'uid = ' . intval($row['list_uid'])
				);
				if (sizeof($row2) > 0) {
					/// undelete (= re-activate) record
					tx_newspaper::updateRows(
						'tx_newspaper_articlelist',
						'uid=' . $row['uid'],
						array('deleted' => 0)
					);
					return $row['uid']; // old record was undeleted
				}
			}
		}
		
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

		return tx_newspaper::insertRows('tx_newspaper_articlelist', $row);		
	}


	public function getTitle() {
		global $LANG;
		return $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:title_' .
						 tx_newspaper::getTable($this), false);
	}

	private $uid = 0;

	protected $attributes = array();
	protected $section = null;

	/// SQL table for persistence
	static protected $table = 'tx_newspaper_articlelist';
	/// SQL table for tx_newspaper_Section objects	
	static protected $section_table = 'tx_newspaper_section';
	/// Array for registered article lists (subclasses of tx_newspaper_ArticleList)
	static protected $registered_articlelists = array();
}


?>
