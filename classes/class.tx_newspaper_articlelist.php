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
 /** An Article List returns an array of Articles on request. Which Articles are
  *  returned is defined by the logic of the ArticleList.
  * 
  *  Typical examples include:
  *   - All Articles belonging to a specified tx_newspaper_Section
  *   - All Articles tagged with a specified tx_newspaper_Tag
  *   - All Articles written by a specified author
  *   - or any other condition for Articles that can be specified as an SQL
  * 	statement
  * 
  *  This is the abstract base class which every concrete Article List must
  *  extend.
  *  
  *  Abstract functions which the concrete class must implement:
  *  - function getArticles($number, $start)
  *	 - static function getModuleName()
  *  - function insertArticleAtPosition($pos)
  *
  *  Currently the important classes which implement tx_newspaper_ArticleList
  *  are tx_newspaper_ArticleList_Manual and 
  *  tx_newspaper_ArticleList_Semiautomatic.
  * 
  *  If you want to write a new type of Article List, you must register your
  *  class by calling 
  *  \code
  *  tx_newspaper_ArticleList::registerArticleList(new <your class>());
  *  \endcode
  *  after defining the class. See tx_newspaper_ArticleList_Manual for an
  *  example.
  */
abstract class tx_newspaper_ArticleList implements tx_newspaper_StoredObject {

/// \todo: rename field "notes", should be "title" (see #595)

	/// Construct a tx_newspaper_ArticleList
	/** \param $uid UID of the record in the corresponding SQL table
	 *  \param $section tx_newspaper_Section to which this ArticleList is
	 * 		bound.
	 */
	public function __construct($uid = 0, tx_newspaper_Section $section = null) {

		if (intval($uid)) {
			$this->setUid($uid);
	 		$this->attributes = tx_newspaper::selectOneRow(
				'*', $this->getTable(), "uid = $uid"
			);
		} else {
			// set some typo3 values for new record
			$this->setAttribute('crdate', time());
			$this->setAttribute('tstamp', time());
			$this->setAttribute('cruser_id', $GLOBALS['BE_USER']->user['uid']);
		}
		if ($section) {
			$this->section = $section;
		} elseif ($this->getUid()) {
			try {
			if ($this->getAttribute('section_id')) {
				$this->section = new tx_newspaper_Section($this->getAttribute('section_id'));
			}
			} catch (tx_newspaper_WrongAttributeException $e) {
/// \todo: remove if not needed: t3lib_div::devlog('wrong attribute exception', 'newspaper', 1, $e);
			}
		}
	}

	/// Convert object to string to make it visible in stack backtraces, devlog etc.
	public function __toString() {
		return get_class($this) . '-object ' . "\n" .
			   'attributes: ' . print_r($this->attributes, 1) . "\n" .
			   'abstract attributes: ' . print_r($this->abstract_attributes, 1);
	}

	////////////////////////////////////////////////////////////////////////////
	//
	//	interface tx_newspaper_StoredObject
	//
	////////////////////////////////////////////////////////////////////////////

	/// \see tx_newspaper_StoredObject
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

	/// \see tx_newspaper_StoredObject
	public function setAttribute($attribute, $value) {
		if (!$this->attributes && $this->getUid()) {
			$this->attributes = tx_newspaper::selectOneRow(
					'*', tx_newspaper::getTable($this), 'uid = ' . $this->getUid()
			);
		}
		
		$this->attributes[$attribute] = $value;
	}

	/// \see tx_newspaper_StoredObject
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

	public function getTitle() {
		global $LANG;
		return $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:title_' .
						 tx_newspaper::getTable($this), false);
	}

	public function getUid() { return intval($this->uid); }

	public function setUid($uid) { $this->uid = $uid; }

	public function getTable() { return tx_newspaper::getTable($this); }

	////////////////////////////////////////////////////////////////////////////
	//
	//	class tx_newspaper_ArticleList
	//
	////////////////////////////////////////////////////////////////////////////

	///	Generate the list from an array of unique identifiers
	/** \param $uids List of identifiers; currently these may be a list of UIDs
	 * 		or the UIDs may be paired with an offset value.
	 */
	abstract function assembleFromUIDs(array $uids);
	
	/// Inserts an Article at a specified position in the list
	/** \param $article Article to insert
	 *  \param $pos Position to insert \p $article at
	 *  \throw tx_newspaper_ArticleNotFoundException if \p $article could not be
	 * 		inserted for some reason
	 */
	abstract function insertArticleAtPosition(tx_newspaper_ArticleIface $article, $pos = 0);

	///	Removes an Article from the list
	/** \param $article Article to insert
	 */
	abstract function deleteArticle(tx_newspaper_ArticleIface $article);

	///	Moves an Article up or down in the list
	/** \param $article Article to move
	 * 	\param $offset Positions to relocate \p $article - negative for up,
	 * 		positive for down 
	 *  \throw tx_newspaper_ArticleNotFoundException if \p $article could not be
	 * 		found in the list
	 */
	abstract function moveArticle(tx_newspaper_ArticleIface $article, $offset);
		
	/// Returns a number of tx_newspaper_Article s from the list
	/** \param $number Number of Articles to return
	 *  \param $start Index of first Article to return (starts with 0)
	 *  \return The \p $number Articles starting with \p $start
	 */
	abstract function getArticles($number, $start = 0);

	/// Get a single tx_newspaper_Article at place \p $index in the List
	/** \param $index The place in the list at which the Article is wanted,
	 * 		starting with 0.
	 *  \return The Article at place \p $index.
	 */
	public function getArticle($index) {
		$articles = $this->getArticles(1, $index);
		return $articles[0];
	}

	/// Get the position of an Article in the List
	/** \param $article The Article to look for in \p $this
	 *  \return The position of an Article in the List
	 *  \throw tx_newspaper_ArticleNotFoundException if \p $article is not in
	 * 		\p $this.
	 */
	public function getArticlePosition(tx_newspaper_ArticleIface $article) {
		foreach ($this->getArticles($this->getAttribute('num_articles')) as $i => $present_article) {
			if ($article->getUid() == $present_article->getUid()) {
				return $i;
			}
		}
		throw new tx_newspaper_ArticleNotFoundException($article->getUid());
	}
	
	/// A short description that makes an Article List identifiable in the BE
	/** This function might be overridden in every implementation. Here it gives
	 *  a reasonable default: Name of the Article List and any notes.
	 */
	public function getDescription() {
		if ($this->isSectionList()) {
			$section = new tx_newspaper_Section($this->getAttribute('section_id'));
			$name = $section->getAttribute('section_name');
		}
		return $this->getTitle() . 
		($name? '[' . $name . ']': '') . (
			$this->getAttribute('notes')? "<br />\n" . $this->getAttribute('notes'): '' 
		);
	}

	///	Check if current Article List is associated with a tx_newspaper_Section
	/** \return true, if \p $this is associated with a valid Section
	 */
	public function isSectionList() {
		if (!intval($this->getAttribute('section_id'))) return false;
		try {
			$section = new tx_newspaper_Section($this->getAttribute('section_id'));
			return ($section->getAttribute('uid') == $this->getAttribute('section_id'));
		} catch (tx_newspaper_Exception $e) {
			return false;
		}
	} 

 	/// Create the record for a concrete ArticleList in the table of abstract ArticleList
	/** This is probably necessary because a concrete ArticleList has been freshly
	 *  created.
	 *  Does nothing if the concrete ArticleList is already linked in the abstract table. 
	 * 
	 *  \param $uid UID of the ArticleList in the table of concrete ArticleList
	 *  \param $table Table of concrete ArticleList
	 *  \return UID of abstract ArticleList record
	 *  \todo Does it need to be public?
	 */ 
	public function createArticleListRecord($uid, $table) {
		/// read typo3 fields to copy into article list super table
		$row = tx_newspaper::selectOneRow(
			implode(', ', self::$fields_to_copy_into_articlelist_table),
			$table,
			'uid = ' . intval($uid)
		);
	
		/// write the uid and table into articlelist table, with the values read above
		$row['list_uid'] = $uid;
		$row['list_table'] = $table;
		$row['tstamp'] = time();				///< tstamp is set to now
		
		// assign article list with section (if any)
		if ($this->section) {
			$row['section_id'] = $this->section->getUid();
		}

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

	/// Get UID of abtract article list
	/** \return UID of abstract ArticleList record, or 0 if no record was found
	 *  \todo Does it need to be public?
	 */
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
	
	/// Set UID of abtract article list
	/** \param $uid New UID of abstract ArticleList record
	 *  \todo Does it need to be public?
	 */
	public function setAbstractUid($uid) {
		$this->abstract_uid = $uid;
	}
		
	/// Register a type of Article List so the system knows about it
	/** The registered Article Lists are used so a BE user can select the type
	 *  of Article List they need.
	 * 
	 *  registerArticleList() must be called from the definition file of all
	 *  Article List classes which should be usable by the BE user.
	 * 
	 *  \param $newList A (default constructed) object of the Article List type
	 * 		to be registered.
	 */
	static public function registerArticleList(tx_newspaper_ArticleList $newList) {
		self::$registered_articlelists[] = $newList;
	}


	/// Get the list of registered Article List types
	/** \return The list of registered Article List types.
	 */
	static public function getRegisteredArticleLists() {
		return self::$registered_articlelists;
	}

	/// Checks if an object or a table name is a registered article list
	/** \param $data object or database table to be checked
	 *  \return true if $data represents a registered article list
	 */
	static public function isRegisteredArticleList($data) {
		// extract table name from given $data
		if (is_object($data)) {
			// $data contains an object
			if (!is_subclass_of($data, 'tx_newspaper_ArticleList')) {
				return false; // object does NOT extend tx_newspaper_ArticleList, so no definatley no registered article list
			}
			$table = $data->getTable();
		} else {
			// $data contains a database table name
			$table = $data;
		}
		$table = strtolower($table);
		
		// check if database table is known
		for ($i = 0; $i < sizeof(self::$registered_articlelists); $i++) {
			if (strtolower(self::$registered_articlelists[$i]->getTable()) == $table) {
				return true; // here you go
			}
		} 
		return false;
	}
	
	
	/// Save hook function, called from the global save hook in tx_newspaper_typo3hook
	/** Writes an abstract record for a concreate3 article list, if no abstract record is available
	 * \param $status Status of the current operation, 'new' or 'update
	 * \param $table The table currently processing data for
	 * \param $id The record uid currently processing data for, [integer] or [string] (like 'NEW...')
	 * \param $fieldArray The field array of a record
	 * \param $that t3lib_TCEmain object? 
	 */
	public static function processDatamap_afterDatabaseOperations($status, $table, $id, &$fieldArray, $that) {
//t3lib_div::devlog('datamap ado hook', 'newspaper', 0, array('status' => $status, 'table' => $table, 'id' => $id, 'fieldArray' => $fieldArray));
		
		if (self::isRegisteredArticleList($table)) {
			if ($status == 'new') {
				// new record, so get substituated uid
				$concrete_al_uid = intval($that->substNEWwithIDs[$id]);
			} else {
				// existing record with existing uid, so just use the given $id
				$concrete_al_uid = intval($id);
			}
			
			$concrete_al = new $table($concrete_al_uid);
			if ($concrete_al->getAbstractUid() == 0) {
				/// no abstract record found, so create a new one
				$concrete_al->createArticleListRecord($concrete_al_uid, $table);
			}	
		}	
	}
	
	
	

	///	Remove all articles from the list.
	/** This function should be an abstract function. But it also should be
	 *  protected or private, and PHP doesn't allow abstract functions to be
	 *  anything but public. Well, sucks to be PHP! So i have to declar the
	 *  function and make sure it is never called.
	 */
	protected function clearList() {
		throw new tx_newspaper_InconsistencyException(
			'clearList() should never be called. Override it in the child classes!'
		);
	}


	private $uid = 0;				///< UID of concrete record
	protected $abstract_uid = 0;	///< UID of abstract record

	///	Attributes of concrete record
	protected $attributes = array();
	///	Attributes of abstract record
	protected $abstract_attributes = array();
	/// tx_newspaper_Section this Article List is associated with, if any
	protected $section = null;

	/// SQL table for persistence for the abstract record
	static protected $table = 'tx_newspaper_articlelist';
	/// SQL table for tx_newspaper_Section objects	
	static protected $section_table = 'tx_newspaper_section';
	/// Array for registered article lists (subclasses of tx_newspaper_ArticleList)
	static protected $registered_articlelists = array();
	
	///	Fields which should be copied from the concrete to the abstract record
	private static $fields_to_copy_into_articlelist_table = array(
		'pid', 'crdate', 'cruser_id'
	);

}

?>