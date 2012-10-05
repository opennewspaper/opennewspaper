<?php
/**
 *  \file class.tx_newspaper_articlelist.php
 *
 *  This file is part of the TYPO3 extension "newspaper".
 *
 *  Copyright notice
 *
 *  (c) 2008 Lene Preuss, Oliver Schroeder, Samuel Talleux <lene.preuss@gmail.com, oliver@schroederbros.de, samuel@talleux.de>
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
 *  \author Lene Preuss <lene.preuss@gmail.com>
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
  *  \todo: rename field "notes", should be "title" (see #595)
  */
abstract class tx_newspaper_ArticleList implements tx_newspaper_StoredObject {

	/// Construct a tx_newspaper_ArticleList
	/** \param $uid UID of the record in the corresponding SQL table
	 *  \param $section tx_newspaper_Section to which this ArticleList is
	 * 		bound.
	 */
	public function __construct($uid = 0, tx_newspaper_Section $section = null) {

        $this->useOptimizedGetArticles(false);

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
			} catch (tx_newspaper_WrongAttributeException $e) { }
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
        	$attribute, array('attributes' => $this->attributes, 'abstract_attributes' => $this->abstract_attributes));
	}

	/// \see tx_newspaper_StoredObject
	public function setAttribute($attribute, $value) {

        $this->readAttributes();
        $this->readAbstractAttributes();

        if (!$this->attributes && $this->getUid()) {
			$this->attributes = tx_newspaper::selectOneRow(
					'*', tx_newspaper::getTable($this), 'uid = ' . $this->getUid()
			);
		}

		if (array_key_exists($attribute, $this->abstract_attributes)) {
			$this->abstract_attributes[$attribute] = $value;
		} else {
			$this->attributes[$attribute] = $value;
		}

	}

    private function readAbstractAttributes() {
        if (!empty($this->abstract_attributes)) return;

        if (!$this->getAbstractUid()) {
            $this->setAbstractUid(
                $this->createAbstractRecord($this->getUid(), $this->getTable())
            );
        }
        if ($this->getAbstractUid()) {
            $this->abstract_attributes = tx_newspaper::selectOneRow(
                        '*', 'tx_newspaper_articlelist', 'uid = ' . $this->getAbstractUid());
        }
    }

    private function readAttributes() {
        if (!empty($this->attributes)) return;

        if ($this->getUid()) {
            $this->attributes = tx_newspaper::selectOneRow(
                        '*', $this->getTable(), 'uid = ' . $this->getUid());
        }
    }

    /// \see tx_newspaper_StoredObject
	public function store() {

		if ($this->getUid()) {

			// read attributes initially
			if (!$this->attributes) {
				$this->readAttributes($this->getTable(), $this->getUid());
			}

			tx_newspaper::updateRows(
				'tx_newspaper_articlelist', 'uid = ' . $this->getAbstractUid(), $this->abstract_attributes
			);
			tx_newspaper::updateRows(
				$this->getTable(), 'uid = ' . $this->getUid(), $this->attributes
			);
		} else {
			/// \todo If the PID is not set manually, $tce->process_datamap() fails silently.
            tx_newspaper::setDefaultFields($this, array('crdate', 'tstamp', 'pid', 'cruser_id'));

			$this->setUid(
				tx_newspaper::insertRows(
					$this->getTable(), $this->attributes
				)
			);
		}

		$articlelist_uid = $this->createAbstractRecord($this->getUid(), $this->getTable());

		return $this->getUid();
	}


	/**
	 * Add $articles to an EMPTY manual article list
	 * @param $articles Array with tx_newspaper_article's
	 * @return true if articles were added, else false
	 */
	public function addArticlesToEmptyManualArticlelist(array $articles) {
		if ($this->getTable() == 'tx_newspaper_articlelist_manual' && !$this->getArticles(1) && $articles) {
			// Add articles (from a semiautomatic articlelist) to this manual article list
			$this->assembleFromUIDs(tx_newspaper::getUidArray($articles));
			return true;
		}
		return false;
	}


	public function getTitle() {
		return tx_newspaper::getTranslation('title_' . tx_newspaper::getTable($this));
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
	/** @param $number Number of Articles to return
	 *  @param $start Index of first Article to return (starts with 0)
	 *  @return tx_newspaper_Article[] The \p $number Articles starting with \p $start
	 */
	abstract function getArticles($number, $start = 0);

    /**
     * @param $do boolean Whether getArticles() uses a faster method to create articles
     */
    public function useOptimizedGetArticles($do) {
        $this->select_method_strategy = SelectMethodStrategy::create($do);
    }

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

    public function getSection() {

        if (!$this->isSectionList()) {
            throw new tx_newspaper_IllegalUsageException('Article list ' . $this->getUid() . ' is not a section list');
        }

        $section_id = intval($this->getAttribute('section_id'));
        if (!$section_id) {
            throw new tx_newspaper_IllegalUsageException('Article list ' . $this->getUid() . ' does not have an associated section');
        }

        return new tx_newspaper_Section($section_id);
    }

	/// Tests whether \p $article is in the article list.
	/** \param $article The article tested for.
	 *  \param $max_index How many articles are checked.
	 */
	public function doesContainArticle(tx_newspaper_Article $article, $max_index) {
		$articles_to_check = $this->getArticles($max_index);
		foreach ($articles_to_check as $checked_article) {
			if ($article->getUid() == $checked_article->getUid()) return true;
		}
		return false;
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
	public function createAbstractRecord($uid, $table) {
        $uid = intval($uid);
        if (!$uid) return 0;

        if ($al_uid = self::readAbstractRecordUid($uid, $table)) return $al_uid;

        $al_uid = tx_newspaper::insertRows(
            'tx_newspaper_articlelist',
            $this->prepareAbstractRecord($uid, $table)
        );

        $this->addArticleListToSection($al_uid);

        return $al_uid;
	}

    /// Get UID of abtract article list
	/** \return UID of abstract ArticleList record, or 0 if no record was found
	 *  \todo Does it need to be public?
	 */
	public function getAbstractUid() {
		if ($this->abstract_uid)
			return $this->abstract_uid;

		if (!is_object($GLOBALS['TYPO3_DB'])) return false;

		// add section to query if this article list is assigned to a section
		$where = '';
		if ($this->section instanceof tx_newspaper_Section)
			$where = ' AND section_id = ' . intval($this->section->getUid());

		$row = tx_newspaper::selectZeroOrOneRows(
			'uid', 'tx_newspaper_articlelist',
			'list_table = \'' . $this->getTable() . '\'' .
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

	static public function registerSaveHook($class) {
		self::$registered_savehooks[] = $class;
	}

	static public function getRegisteredSaveHooks() {
		return self::$registered_savehooks;
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
        $timer = tx_newspaper_ExecutionTimer::create();
		if (self::isRegisteredArticleList($table)) {
			self::writeAbstractRecordIfNeeded($status, $table, $id, $that);
		}
	}

    private function prepareAbstractRecord($uid, $table) {
        $row = self::getFieldsToCopyToAbstractTable($uid, $table);
        $row = $this->fillFieldsForAbstractTable($uid, $row, $table);
        return $row;
    }

    private function addArticleListToSection($al_uid) {
        if ($this->section) {
            tx_newspaper::updateRows(
                $this->section->getTable(),
                'uid=' . $this->section->getUid(),
                array('articlelist' => $al_uid)
            );
        }
    }

    private function fillFieldsForAbstractTable($uid, $row, $table) {
        $row['list_uid'] = $uid;
        $row['list_table'] = $table;
        $row['tstamp'] = time(); ///< tstamp is set to now

        if ($this->section) {
            $row['section_id'] = $this->section->getUid();
        }

        return $row;
    }

    private static function readAbstractRecordUid($uid, $table) {
        $abstract_record = tx_newspaper::selectZeroOrOneRows(
            'uid', 'tx_newspaper_articlelist', "list_uid = $uid AND list_table = '$table'"
        );

        return (intval($abstract_record['uid']));
    }

    private static function getFieldsToCopyToAbstractTable($uid, $table) {
        return tx_newspaper::selectOneRow(
			implode(', ', self::$fields_to_copy_to_abstract_table),
			$table,
			'uid = ' . intval($uid)
		);
    }


	/// checks if an abstract record needs to be written after creating a concrete article list in the list module
	/** \param $status Status of the current operation, 'new' or 'update
	 *  \param $table The table currently processing data for
	 *  \param $id The record uid currently processing data for, [integer] or [string] (like 'NEW...')
	 */
	private static function writeAbstractRecordIfNeeded($status, $table, $id, $that) {
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
			$concrete_al->createAbstractRecord($concrete_al_uid, $table);
		}
	}

    private static function updateDependencyTree(tx_newspaper_ArticleList $list) {

        $timer = tx_newspaper_ExecutionTimer::create();

        if (tx_newspaper_DependencyTree::useDependencyTree()) {
            $tree = tx_newspaper_DependencyTree::generateFromArticlelist($list);
            $tree_proxy = new tx_newspaper_DependencyTreeProxy($tree);
            $tree_proxy->executeActionsOnPages('tx_newspaper_Article');
            $tree_proxy->executeActionsOnPages('tx_newspaper_Extra');
        }
    }

	/// TCEforms hook
	public static function getMainFields_preProcess($table, $row, $that) {
		// check concrete article lists first ...
		if (self::isRegisteredArticleList($table)) {
			self::blockAccessIfSectionArticleList($table, $row);
		}

		// ... abstract article lists then
		if (strtolower($table) == self::$table) {
			if (!intval($row['uid'])) {
				// no integer uid for abstract article list, so a NEW123something uid,, so a new records
				die('You can\'t create a new abstract articlelist.');
			}
			if ($row['section_id']) {
				$section_uid = 0;
				if (intval($row['section_id'])) {
					$section_uid = intval($row['section_id']);
				} else {
					// so value is formed like: tx_newspager_section_[uid]|[section_name]
					// \todo: is there a typo3 api method to extract the uid?
					if (substr(strtolower($row['section_id']), 0, 21) == 'tx_newspaper_section_') {
						$part = explode('|', substr($row['section_id'], 21));
						if (isset($part[0]) && intval($part[0])) {
							$section_uid = intval($part[0]);
						}
					}
				}
				if ($section_uid) {
					die(self::blockAccessIfSectionArticleListMessage(new tx_newspaper_section($section_uid)));
				}
			}
		}
	}

	/// blocks access (in the list module) to article list associated with a section; must be edited in the section articl list module
	private static function blockAccessIfSectionArticleList($table, $row) {
		if (!intval($row['uid'])) {
			return; // new record, can' be associated to a section
		}
		$concrete_al = new $table($row['uid']);
		if ($concrete_al->getAttribute('section_id')) {
			// a section is associated with this article list ...
			die(self::blockAccessIfSectionArticleListMessage(new tx_newspaper_section($concrete_al->getAttribute('section_id'))));
		}
	}

	/// \return Error message (Section article lists can only be edited in article list module)
	private static function blockAccessIfSectionArticleListMessage(tx_newspaper_section $s) {
		return 'This article list is associated to section "' . htmlspecialchars($s->getAttribute('section_name')) . '". This article list can only be edited in the section article list module.';
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

	/// Call all registered save hooks
	protected function callSaveHooks() {
        $timer = tx_newspaper_ExecutionTimer::create();

		foreach (self::$registered_savehooks as $hook_class) {
			if (!is_object($hook_class)) $hook_class = new $hook_class();
			if (method_exists($hook_class, 'articleListSaveHook')) {
				$hook_class->articleListSaveHook($this);
			}
		}
        self::updateDependencyTree($this);
	}

	/// \return $TCA for this article list (abstract and concrete)
	public function getTcaFields() {
		t3lib_div::loadTCA(tx_newspaper_articlelist::$table); // load tca for abstract articlelist
		t3lib_div::loadTCA($this->getTable()); // load tca for current concrete articlelist

		$tsc = t3lib_BEfunc::getPagesTSconfig(tx_newspaper_Sysfolder::getInstance()->getPidRootfolder());

		// read TCA for abstract article list and this concrete article list
		$tca = array(
			'abstract' => $GLOBALS['TCA']['tx_newspaper_articlelist']['columns'],
			'concrete' => tx_newspaper_UtilMod::disableTsconfigFieldsInTca($GLOBALS['TCA'][$this->getTable()]['columns'], $tsc['TCEFORM.'][$this->getTable() . '.'])
		);
		return $tca;
	}

	/// \return HTML code containing the form for (abstract and concrete) article list editing
	public function getAndProcessTceformBasedBackend() {
		$fields = $this->getTcaFields();
//t3lib_div::devlog('getAndProcessTceformBasedBackend()', 'newspaper', 0, array('fields' => $fields, '_request' => $_REQUEST)); //, 'TBE_STYLES[stylesheet]' => $GLOBALS['TBE_STYLES']));

		// check if data needs to be stored in article list configuration
		$this->processDataStorage();

		// check if configuration form was closed and redirect to article list form
		// make sure that processDataStorage() has run (otherwise save&close can't save the data)
		$this->checkIfFormWasClosed();

//t3lib_div::devlog('t3 consts', 'newspaper', 0, array('PATH_typo3_mod' => PATH_typo3_mod, 'TYPO3_MOD_PATH' => TYPO3_MOD_PATH, 'TBE_MODULES' => $GLOBALS['TBE_MODULES'], 'TBE_STYLES' => $GLOBALS['TBE_STYLES'], 'T3_VAR' => $GLOBALS['T3_VAR']));
		$content = '';

		// tceforms configuration
        /** @var $form t3lib_TCEforms */
		$form = t3lib_div::makeInstance('t3lib_TCEforms');
		$form->initDefaultBEmode();
		$form->backPath = '';
		$form->formName = 'editform';
		$form->doSaveFieldName = 'doSave';

//		$content .= $form->printNeededJSFunctions_top(); // \todo: check if this method can replace the manual addition of css and js below
		//http://lists.typo3.org/pipermail/typo3-german/2009-December/064304.html
		//thanks to dimitry dupelov
		// \todo: Check T3 4.2.x only? Does this work in T3 4.3.x?
		$content .= '
<!-- <link rel="stylesheet" type="text/css" href="stylesheet.css" /></link> -->
<link rel="stylesheet" type="text/css" href="' . $GLOBALS['TBE_STYLES']['styleSheetFile_post'] . '" /></link>
<style type="text/css">
body {
	overflow: auto !important;
}
.class-main4 {
	margin: 0 0 0 10px;
}
.nbsp {
	display: block;
	margin: 0 0 10px 0;
}
</style>
<script type="text/javascript" src="' . tx_newspaper::getAbsolutePath() . 'typo3/contrib/prototype/prototype.js"></script>
<script type="text/javascript" src="' . tx_newspaper::getAbsolutePath() . 'typo3/js/iecompatibility.js"></script>
<script type="text/javascript" src="' . tx_newspaper::getAbsolutePath() . 'typo3/js/clickmenu.js"></script>
<script type="text/javascript" src="' . tx_newspaper::getAbsolutePath() . 'typo3/md5.js"></script>
<script type="text/javascript" src="' . tx_newspaper::getAbsolutePath() . 't3lib/jsfunc.evalfield.js"></script>
';

		// next 4 lines copied from typo3/alt_doc.php
		// open form
		$this->R_URL_parts = parse_url(t3lib_div::getIndpEnv('REQUEST_URI'));
		$this->R_URL_getvars = t3lib_div::_GET();
		$this->R_URI = $this->R_URL_parts['path'] . '?' . t3lib_div::implodeArrayForUrl('', $this->R_URL_getvars);
		$content .= '<form action="' . htmlspecialchars($this->R_URI) . '" method="post" enctype="' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['form_enctype'] . '" name="editform" onsubmit="document.editform._scrollPosition.value=(document.documentElement.scrollTop || document.body.scrollTop); return TBE_EDITOR.checkSubmit(1);">';

		// add abtract article list uid
		$content .= '<input type="hidden" name="abstr_al_uid" value="' . $this->getAbstractUid() . '" />';


		// Idea for tceforms rendering, see: http://www.typo3.net/forum/list/list_post//85598/?page=1#pid339011

		// Render abstract article list backend first ...
		$row = tx_newspaper::selectOneRow(
			'*',
			'tx_newspaper_articlelist',
			'uid=' . $this->getAbstractUid()
		);
        foreach($row as $key => $value) {
            // Strip slashes. Otherwise TCEforms will render the backslahes in field values.
            $row[$key] = stripslashes($value);
        }
		foreach($fields['abstract'] as $tcaField => $tcaFieldConfig) {
			if ($tcaField != 'list_table' && $tcaField != 'list_uid') {
				$content .= $form->getSingleField('tx_newspaper_articlelist', $tcaField, $row);
			}
		}

		// ... render concrete article list backend then
		$row = tx_newspaper::selectOneRow(
			'*',
			$this->getAttribute('list_table'),
			'uid=' . $this->getUid()
		);
        foreach($row as $key => $value) {
            // Strip slashes. Otherwise TCEforms will render the backslahes in field values.
            $row[$key] = stripslashes($value);
        }
		foreach($fields['concrete'] as $tcaField => $tcaFieldConfig) {
			if ($tcaField != 'articles') { // \todo: add field articles too to allow complete form editing (but will this work with the mod7 standalone article list backend??)
				$content .= $form->getSingleField($this->getAttribute('list_table'), $tcaField, $row);
			}
		}


		// add store buttons (based on /typo3/alt_doc.php)
		$buttons['save'] = '<input type="image" class="c-inputButton" name="_savedok"' . t3lib_iconWorks::skinImg($form->backPath, 'gfx/savedok.gif','') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:rm.saveDoc', 1) . '" />';
		$buttons['save_close'] = '<input onclick="document.editform.closeDoc.value=1;" type="image" class="c-inputButton" name="_saveandclosedok"' . t3lib_iconWorks::skinImg($form->backPath, 'gfx/saveandclosedok.gif', '').' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:rm.saveCloseDoc', 1) . '" />';
		$buttons['close'] = '<a href="#" onclick="document.editform.closeDoc.value=1; document.editform.submit(); return false;">' . '<img' . t3lib_iconWorks::skinImg($form->backPath, 'gfx/closedok.gif', 'width="21" height="16"').' class="c-inputButton" title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:rm.closeDoc', 1) . '" alt="" /></a>';
		//?delete?
		// \todo: move to top (in non-scrolling div)
		$content .= '<br /><br ><br />';
		foreach($buttons as $button) {
			$content .= $button;
		}

		// add typo3 js files
		$content .= $form->printNeededJSFunctions();

		// add some hidden fields (needed for typo3 js backend handling)
		$content = tx_newspaper_UtilMod::compileForm($content);

		$content .= '</form>'; // close form

//t3lib_div::devlog('getAndProcessTceformBasedBackend()', 'newspaper', 0, array('content' => $content, 'button' => $buttons));
		return $content;
	}

	/// check if article list configuration needs to be stored and stores the data if yes
	private function processDataStorage() {
//t3lib_div::devlog('processDataStorage()', 'newspaper', 0, array('_request' => $_REQUEST));

		/// loadTCA for abstract und concrete table has run already, so TCA is accessible

		if (
			(isset($_REQUEST['_saveandclosedok_x']) ||
			isset($_REQUEST['_saveandclosedok_y']) ||
			isset($_REQUEST['_savedok_x']) ||
			isset($_REQUEST['_savedok_y'])) &&
			isset($_REQUEST['abstr_al_uid']) && intval($_REQUEST['abstr_al_uid'])
		) {
			$al = tx_newspaper_ArticleList_Factory::getInstance()->create(intval($_REQUEST['abstr_al_uid']));

			// prepare abstract attributes ...
			foreach($_REQUEST['data']['tx_newspaper_articlelist'][$al->getAbstractUid()] as $field => $value) {
				$al->setAttribute($field, $value);
			}
			// ... and prepare concrete attributes
			foreach($_REQUEST['data'][$al->getTable()][$al->getUid()] as $field => $value) {
				if ($GLOBALS['TCA'][$this->getTable()]['columns'][$field]['config']['type'] == 'group' &&
					$GLOBALS['TCA'][$this->getTable()]['columns'][$field]['config']['internal_type'] == 'db'
				) {
					// check if type group/db is processed and remove table name so only th uid remains
					// THIS DOES ONLY WORK IF ONLY ONE TABLE IS ALLOWED FOR THIS FIELD!!!
					// based on t3lib_loaddbgroup::readList()
					$val = strrev($value);
					$parts = explode('_',$val,2);
					$value = strrev($parts[0]); // overwrite $value
				}

				$al->setAttribute($field, $value);
			}
			$al->store();
		}
	}

	// checks if an article list configuration form was closed and redirect to article list form if yes
	private function checkIfFormWasClosed() {
		if (isset($_REQUEST['closeDoc']) && $_REQUEST['closeDoc'] == 1) {
			header('Location: ' . $_SERVER['PHP_SELF'] . '?fullrecord=0&M=' . htmlspecialchars($_REQUEST['M']) .'&id=' . intval($_REQUEST['id']));
		}
	}




	private $uid = 0;				///< UID of concrete record
	protected $abstract_uid = 0;	///< UID of abstract record

	///	Attributes of concrete record
	protected $attributes = array();
	///	Attributes of abstract record
	protected $abstract_attributes = array();
	/// tx_newspaper_Section this Article List is associated with, if any
	protected $section = null;

    /** @var SelectMethodStrategy */
    protected $select_method_strategy = null;

	/// SQL table for persistence for the abstract record
	static protected $table = 'tx_newspaper_articlelist';
	/// SQL table for tx_newspaper_Section objects
	static protected $section_table = 'tx_newspaper_section';
	/// Array for registered article lists (subclasses of tx_newspaper_ArticleList)
	static protected $registered_articlelists = array();

	/// Array for registered save hooks (called after assembleFromUIDs())
	private static $registered_savehooks = array();

	///	Fields which should be copied from the concrete to the abstract record
	private static $fields_to_copy_to_abstract_table = array(
		'pid', 'crdate', 'cruser_id'
	);

}

abstract class SelectMethodStrategy {

    public static function create($get_articles_uses_array) {
        if ($get_articles_uses_array) return new SelectMethodAllAtOnceStrategy();
        else return new SelectMethodSingleStrategy();
    }

    abstract public function createArticle($row);

    // methods for manual article list
    abstract public function fieldsToSelect();

    // methods for semiautomatic article list
    abstract public function getUids(array $uids);
    abstract public function getOffset(array $offsets, $uid);
    abstract public function rawArticleUIDs(array $select_results);
    abstract public function selectFields();
}

class SelectMethodAllAtOnceStrategy extends SelectMethodStrategy {
    public function createArticle($row){
        return tx_newspaper_Article::createFromArray($row);
    }

    public function fieldsToSelect() {
        return tx_newspaper_ArticleList_Manual::article_table . '.*';
    }

    public function getUids(array $uids) {
        $actual_uids = array();
        foreach ($uids as $article_data) {
            $actual_uids[] = $article_data['uid'];
        }
        return $actual_uids;
    }

    public function getOffset(array $offsets, $uid) {
        return intval($offsets[$uid['uid']]);
    }

    public function rawArticleUIDs(array $select_results) {
        return $select_results;
    }

    public function selectFields() {
        return 'tx_newspaper_article.*';
    }

}

class SelectMethodSingleStrategy extends SelectMethodStrategy {

    public function createArticle($row) {
        if (is_array($row)) return new tx_newspaper_Article($row['uid_foreign']);
        else return new tx_newspaper_Article(intval($row));
    }

    public function fieldsToSelect() {
        return tx_newspaper_ArticleList_Manual::mm_table . '.uid_foreign';
    }

    public function selectFields() {
        return 'DISTINCT tx_newspaper_article.uid';
    }


    public function getUids(array $uids) {
        return $uids;
    }


    public function getOffset(array $offsets, $uid) {
        return intval($offsets[$uid]);
    }

    public function rawArticleUIDs(array $select_results) {
        $uids = array();
        foreach ($select_results as $result) {
            if (intval($result['uid'])) $uids[] = intval($result['uid']);
        }

        return $uids;
    }


}

?>