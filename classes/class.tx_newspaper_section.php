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
class tx_newspaper_Section implements tx_newspaper_StoredObject {

	/// Construct a tx_newspaper_Section given the UID of the SQL record
	public function __construct($uid = 0) {
		if ($uid) {
			$this->setUid($uid);
		}
	}

	/// Convert object to string to make it visible in stack backtraces, devlog etc.
	public function __toString() {
		try {
			$this->getAttribute('uid');
		} catch (tx_newspaper_Exception $e) { }
		return get_class($this) . '-object ' . $this->getUid() . " \n" .
			   'attributes: ' . print_r($this->attributes, 1) . "\n";
	}

	public function getAttribute($attribute) {
		if (!$this->attributes) {
			$this->attributes = tx_newspaper::selectOneRow(
				'*', $this->getTable(), 'uid = ' . $this->getUid()
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
				'*', $this->getTable(), 'uid = ' . $this->getUid()
			);
		}

		$this->attributes[$attribute] = $value;
	}

	/// Write or overwrite Section data in DB, return UID of stored record
	public function store() {
		throw new tx_newspaper_NotYetImplementedException();
	}


	/// \return true if section can be accessed (FE/BE use enableFields)
	public function isValid() {
		// check if section is valid
		try {
			$tmp = $this->getAttribute('uid'); // getAttribute forces the object to be read from database
			return true;
		} catch (tx_newspaper_EmptyResultException $e) {
			return false;
		}
	}

	public function getTitle() {
		return tx_newspaper::getTranslation('title_' . $this->getTable());
	}


	/// assigns a default article list to this section
	public function assignDefaultArticleList() {
// \todo make configurable which article list type is default
// \todo: how to add a default note like "Article list for section 'dummy'"?
		$al = new tx_newspaper_ArticleList_Semiautomatic(0, $this);
		$this->replaceArticleList($al); // assign new article list to this section
	}


	/// replaces the current (if any) article list with ths given new article list
	/** the method first removes the old article list (if any) and then assign the new article list.
	 *  As some attribute are changed (crdate f.ex) the new article gets stored in this method.
	 * \param $new_al article list object of the new article list
	 * \return uid of abstract article list
	 */
	public function replaceArticleList(tx_newspaper_articlelist $new_al) {

		try {
			$current_al = $this->getArticleList(); // get current article list

			// "delete" (= set deleted flag) previous concrete article list before writing the new one
			// concrete article list must be deleted first (otherwise data for concrete article list can't be obtained from abstract article list)
			tx_newspaper::updateRows(
				$current_al->getTable(),
				'uid=' . $current_al->getUid(),
				array('deleted' => 1)
			);
			$this->articlelist = null; // well, this list has just been deleted on the database
		} catch (tx_newspaper_EmptyResultException $e) {
			// no article list assigned so far, either new section or the article list was deleted for some reason
		}

		// "delete" (= set deleted flag) all abstract article lists assigned to this section, before writing the new one
		// just deleting the current article list would do, but this deletes potential orphan article lists for this section too
		tx_newspaper::updateRows(
			'tx_newspaper_articlelist',
			'section_id=' . $this->getUid(),
			array('deleted' => 1)
		);


		/// try to re-activate an old deleted article list for the new article list type

		// read newest abstract article list of new article list's type (record is deleted; was before or was set deleted by the updareRows() abobe)
		$al_abstract = tx_newspaper::selectRowsDirect(
			'*',
			'tx_newspaper_articlelist',
			'list_table="' .  $new_al->getTable() . '" AND section_id=' . $this->getUid(),
			'',
			'crdate DESC, tstamp DESC',
			'1'
		);
		if (sizeof($al_abstract) > 0) {
			// try to re-activate deleted article list
			// check if concrete article list is still available
			$al_concrete = tx_newspaper::selectRowsDirect(
				'*',
				$new_al->getTable(),
				'uid=' . $al_abstract[0]['list_uid']
			);
			if (sizeof($al_concrete) > 0) {
				tx_newspaper::updateRows( // undelete concrete article list
					$new_al->getTable(),
					'uid=' . $al_abstract[0]['list_uid'],
					array('deleted' => 0)
				);
				tx_newspaper::updateRows( // undelete abstract article list
					'tx_newspaper_articlelist',
					'uid=' . $al_abstract[0]['uid'],
					array('deleted' => 0)
				);
				return $al_abstract[0]['uid']; // uid of abstract article list
			}
		}

		// no article list found to re-activate, so create a new one
		$new_al->store(); // store new article list

		// set title for this articlelist
		$title = tx_newspaper::getTranslation('title_section_articlelist');
		$title = str_replace('###SECTION###', $this->getAttribute('section_name'), $title);
		$title = str_replace('###ARTICLELIST_TYPE###', $new_al->getTitle(), $title);
		tx_newspaper::updateRows(
			'tx_newspaper_articlelist',
			'uid=' . $new_al->getAbstractUid(),
			array(
				'notes' => $title
			)
		);

		return $new_al->getAbstractUid();

	}



	public function getArticleList() {
		if (!$this->articlelist) {
			$list = tx_newspaper::selectOneRow(
				'uid', self::$list_table, 'section_id  = ' . $this->getUid()
			);
			$this->articlelist = tx_newspaper_ArticleList_Factory::getInstance()->create($list['uid'], $this);
		}

		return $this->articlelist;
	}

	public function getParentSection() {
		if ($this->getAttribute('parent_section')) {
			return new tx_newspaper_Section($this->getAttribute('parent_section'));
		} else return null;
	}

	/// Get all Sections which have $this as ancestor
	/** \param $recursive If true, return all inheriting sections down to the
	 * 		leaves. Otherwise, return the direct children of this Section.
	 *  \return Array of Section objects under \p $this. parental relations are
	 * 		\em not preserved.
	 */
	public function getChildSections($recursive = false) {

		$row = tx_newspaper::selectRows(
				'uid', $this->getTable(),
				'parent_section = ' . $this->getUid()
			);

		$sections = array();
		if ($row) foreach ($row as $section_uid) {
			$child =  new tx_newspaper_Section($section_uid['uid']);
			$sections[] = $child;
			if ($recursive) {
				$sections = array_merge($sections,
										$child->getChildSections($recursive));
			}
		}

		return $sections;
	}

	/// \return uid of parent abstract record for concrete article list associated with section
	public function getAbstractArticleListUid() {
		return $this->getAttribute('articlelist');
	}


	/// Activate a page for this section
	/// \return true if page was activated, false if page has been active already
	public function activatePage(tx_newspaper_PageType $type) {
		if ($this->getSubPage($type)) {
			return false; // page has been activated already
		}

		$p = new tx_newspaper_Page($this, $type);
		$p->store();
		$p->setAttribute('crdate', time());
		$p->setAttribute('tstamp', time());
		$p->setAttribute('cruser_id', $GLOBALS['BE_USER']->user['uid']);
		$p->store();

		return true;
	}

	/// Returns all pages attached to the current section
	public function getSubPages() {
        if (!$this->subPages) {
            $row = tx_newspaper::selectRows(
                'uid', 'tx_newspaper_page',
                'section = ' . $this->getAttribute('uid')
            );
     		foreach ($row as $record) {
 			    $this->subPages[] = new tx_newspaper_Page((int)$record['uid']);
 		    }
        }
 		return $this->subPages;
 	}

 	/// Finds the page under the current section that has the required page type.
 	public function getSubPage(tx_newspaper_PageType $type) {
 		foreach ($this->getSubPages() as $page) {
 			if ($page->getPageType()->getUid() == $type->getUid())
 				return $page;
 		}
 		return null;
 	}

 	/// Finds a pages of specified page type under the current section or its children.
 	/** If this section has the specified page type activated, that page is returned.
 	 *  Else all activated pages in the child sections are returned. Each section
 	 *  is followed down the section tree until a desired page is found.
 	 */
 	public function getSubPagesRecursively(tx_newspaper_PageType $type) {
 		$sub_page = $this->getSubPage($type);
 		if ($sub_page instanceof tx_newspaper_Page) return array($sub_page);

 		$sub_pages = array();
 		foreach ($this->getChildSections() as $sub_section) {
 			$sub_pages = array_merge($sub_pages, $sub_section->getSubPagesRecursively($type));
 		}
 		return $sub_pages;
 	}

 	/// gets an array of sections up the rootline
	/// \return array tx_newspaper_Section objects, up the rootline
	public function getSectionPath($path = array()) {
		$path[] = $this;
		if ($this->getParentSection()) {
			return $this->getParentSection()->getSectionPath($path);
		}
		return $path;
	}

	/// \return The UID of the associated Typo3 page
	public function getTypo3PageID() {
		try {
			$row = tx_newspaper::selectOneRow('uid', 'pages',
											  'tx_newspaper_associated_section = ' . $this->getUid());
		} catch (tx_newspaper_DBException $e) {
			throw new tx_newspaper_IllegalUsageException(
				'Section number ' . $this->getUid() . ', "' . $this->getAttribute('section_name') .
				'", appears to have no Typo3 page associated with it. Please create a page and ' .
				' choose Section ' . $this->getUid() . ' in the "Extended" tab of the' .
				' "Page Properties" of that Typo3 page.'
			);
		}
		return intval($row['uid']);
	}

	/** \return the Article PageZone (PageZoneType has is_article set) on the
	 * 		Page marked as the Article Page, or \c null.
	 */
	public function getDefaultArticle() {
		foreach ($this->getSubPages() as $sub_page) {
			foreach ($sub_page->getActivePageZones() as $pagezone) {
				if ($pagezone->getPageZoneType()->getAttribute('is_article')) {
					return $pagezone;
				}
			}
		}
		return null; // no default article found
	}


 	/** Create a new article based on TSConfig settings for musthave extras
 	 *  \param $at article type object
 	 */
 	public function createNewArticle(tx_newspaper_articletype $at) {
 		$new_article = new tx_newspaper_article();

 		$new_article->setAttribute('crdate', time());
 		$new_article->setAttribute('tstamp', time());

 		$new_article->store(); // store article before adding the section (otherwise no uid available)
 		$new_article->addSection($this);

 		// \todo: check if extra is placed on $this->getDefaultArticle(), if default article are to be used at all
 		// if yes: copy matching extras from default article if any, else create empty extra (as implemented below)

		///	Create extras configured in TSConfig
		$must_have_extras = $at->getTSConfigSettings('musthave'); // read configured must have extras
		foreach($must_have_extras as $key => $default_extra) {
			// $default_extra contains a class name or
			// a class name extended with ":" and a default paragraph for the extra
			// TSConfig example: newspaper.articletype.[type].musthave = tx_newspaper_extra_image:-2
			list($extra_class, $paragraph) = explode(':', $default_extra);
			$paragraph = intval($paragraph);
//t3lib_div::devlog('createNewArticle', 'newspaper', 0, array('key' => $key, 'default_extra' => $default_extra, 'extra_class' => $extra_class, 'paragraph' => $paragraph));

			if (tx_newspaper::classImplementsInterface($extra_class, 'tx_newspaper_ExtraIface')) {
				$new_extra = new $extra_class();

				//	I think this is needed before I can safely setAttribute(). Not sure. Anyway, BSTS.
				$new_extra->store();

		 		$new_extra->setAttribute('crdate', time());
		 		$new_extra->setAttribute('tstamp', time());

                //todo: switch via tsconfig
				$new_extra->setAttribute('show_extra', 1);
				$new_extra->setAttribute('paragraph', $paragraph);
				$new_extra->setAttribute('position', 0);

				$new_extra->store();						//	Final store()

				/// Write association table entry article -> extra
				/// \todo $new_article->relateExtra2Article($new_extra)?
				tx_newspaper::insertRows(tx_newspaper_Article::getExtra2PagezoneTable(),
					array(
						'uid_local' => $new_article->getUid(),
						'uid_foreign' => $new_extra->getExtraUid(),
				));
			} else {
				t3lib_div::devlog('Unknown Extra configured in TSConfig', 'newspaper', 3, array('tsconfig' => $extra_class, 'section' => $this, 'articletype' => $at));
			}
		}

 		return $new_article;
 	}



 	/** Create a new article from the article with the default placement as
 	 *  specified in the Article PageZone of the Article Page of the Section.
 	 *
 	 *  Extras which are mandatory for an Article in this Section (specified in
 	 *  TSConfig for the Typo3 page in which the current Section lies) are
 	 *  created. If the Extra is placed in the default placement Article, it is
 	 *  copied. Else, a new Extra of the specified class is created hidden with
 	 *  paragraph and position set to (0,0).
 	 *
 	 *  \param $at article type object
 	 */
/* // note: code not working properly: default extra handling dev stopped ...
 	public function copyDefaultArticle(tx_newspaper_articletype $at) {

 		if (!$new_article = $this->getDefaultArticle()) {
			// no default article found, so no article to copy, just return a new empty article
			return new tx_newspaper_article();
 		}
 		if (!$new_article instanceof tx_newspaper_Article) {
 			throw new tx_newspaper_InconsistencyException('getDefaultArticle() did not return an Article!');
 		}

		$must_have_extras = $at->getTSConfigSettings('musthave'); // read configured must have extras
t3lib_div::devlog('at tsc musthave', 'newspaper', 0, array('musthave' => $must_have_extras));

		//	zeroing the UID causes the article to be written to DB as a new object.
 		$new_article->setAttribute('uid', 0);
 		$new_article->setUid(0);
 		$new_article->store();

 		$new_article->clearExtras();

 		$new_article->setAttribute('is_template', 0);

 		$new_article->setAttribute('crdate', time());
 		$new_article->setAttribute('tstamp', time());


		$default_extras = $this->getDefaultArticle()->getExtras();
		foreach($default_extras as $default_extra) {
			$key = array_search(tx_newspaper::getTable($default_extra), $must_have_extras);
			if ($key !== false) {
				$new_article->addExtra($default_extra->duplicate());
				unset($must_have_extras[$key]);
			}
		}

		///	Create extras configured in TSConfig
		/// Extras get either copied from default article (if available) or are created empty
		foreach($must_have_extras as $key => $default_extra) {

			// $default_extra contains a class name or
			// a class name extended with ":" and a default paragraph for the extra
			// TSConfig example: newspaper.articletype.[type].musthave = tx_newspaper_extra_image:-2
			list($extra_class, $paragraph) = explode(':', $default_extra);
			$paragraph = intval($paragraph);
t3lib_div::devlog('copyDefaultArticle', 'newspaper', 0, array('key' => $key, 'default_extra' => $default_extra, 'extra_class' => $extra_class, 'paragraph' => $paragraph));

			if (tx_newspaper::classImplementsInterface($extra_class, 'tx_newspaper_ExtraIface')) {
				$new_extra = new $extra_class();

				//	I think this is needed before I can safely setAttribute(). Not sure. Anyway, BSTS.
				$new_extra->store();

		 		$new_extra->setAttribute('crdate', time());
		 		$new_extra->setAttribute('tstamp', time());

				$new_extra->setAttribute('show_extra', 0);
				$new_extra->setAttribute('paragraph', $paragraph);
				$new_extra->setAttribute('position', 0);

				$new_extra->store();						//	Final store()

				/// Write association table entry article -> extra
				/// \todo $new_article->relateExtra2Article($new_extra)?
				tx_newspaper::insertRows(tx_newspaper_Article::getExtra2PagezoneTable(),
					array(
						'uid_local' => $new_article->getUid(),
						'uid_foreign' => $new_extra->getExtraUid(),
				));
			} else {
				t3lib_div::devlog('Unknown Extra configured in TSConfig', 'newspaper', 3, array('tsconfig' => $extra_class, 'section' => $this, 'articletype' => $at));
			}
		}

		false && t3lib_div::devlog('extras', 'newspaper', 0,
			array('default extras' => $default_extras,
				'must have extras' => $must_have_extras,
				'extras' => $new_article->getExtras()));

 		// set main section
 		$new_article->addSection($this);
 		$new_article->store();

 		return $new_article;
 	}
 */

 	public function getTable() {
		return tx_newspaper::getTable($this);
	}

	public function setUid($uid) { $this->uid = intval($uid); }
	public function getUid() { return $this->uid; }

	/// get active pages for current Section
	/// \return array uids of active pages objects for given section
	public function getActivePages() {
		$sf = tx_newspaper_Sysfolder::getInstance();
//t3lib_div::devlog('gap', 'newspaper', 0);
		$p = new tx_newspaper_Page($this);
		$row = tx_newspaper::selectRows(
			'uid',
			$p->getTable(),
			'pid=' . $sf->getPid($p) . ' AND section=' . $this->getUid()
		);
//t3lib_div::devlog('gap row', 'newspaper', 0, $row);
		$list = array();
		for ($i = 0; $i < sizeof($row); $i++) {
			$list[] = new tx_newspaper_Page(intval($row[$i]['uid']));
		}
		return $list;
	}


	/// Get array with article objects assigned to this section (limited by $limit)
	public function getArticles($limit=10) {
		$limit = intval($limit);
		$row = tx_newspaper::selectRows(
			'mm.uid_local',
			'tx_newspaper_article_sections_mm mm, tx_newspaper_article a',
			'mm.uid_foreign=' . $this->getUid() . ' AND mm.uid_foreign=a.uid AND a.deleted=0',
			'',
			'',
			$limit
		);
//t3lib_div::devlog('s getArticles row', 'newspaper', 0, array('query' => tx_newspaper::$query, 'row' => $row));
		$list = array();
		for ($i = 0; $i < sizeof($row); $i++) {
			$list[] = new tx_newspaper_Article(intval($row[$i]['uid_local']));
		}
		return $list;
	}

	///	Generate a URL which links to the "section overview" page of the Section
	public function getLink() {
		return tx_newspaper::typolink_url(
			array('id' => $this->getTypo3PageID())
		);
	}

	public function getTemplateSet() {
		if ($this->getAttribute('template_set')) $this->getAttribute('template_set');
		if (!$this->getParentSection()) return '';
		return $this->getParentSection()->getTemplateSet();
	}

	///	Get all tx_newspaper_Section records in the DB.
	/** \param $articlesAllowedOnly if set to true only section with the
	 *       articles_allowed flag set are returned
	 *  \param $sort_by Field of the \c tx_newspaper_section SQL table to sort
	 * 		results by.
	 *  \return tx_newspaper_Section objects in the DB.
	 */
	public static function getAllSections($articlesAllowedOnly=true, $sort_by = 'sorting') {
		$TSConfig = t3lib_BEfunc::getPagesTSconfig(0);
		$excluded = $TSConfig['newspaper.']['excluded_sections'];

		$excluded_sections = array();
		$additional_where = '';
		if ($excluded) {
			foreach (explode(',', $excluded) as $excluded_section_uid) {
				$excluded_section = new tx_newspaper_Section($excluded_section_uid);
				$excluded_sections = array_merge(
					$excluded_sections,
					array($excluded_section),
					$excluded_section->getChildSections(true));
			}
			if ($excluded_sections) {
				$additional_where .= ' AND uid NOT IN (';
				$separator = '';
				foreach ($excluded_sections as $excluded_section) {
					$additional_where .= $separator . $excluded_section->getUid();
					$separator = ', ';
				}
				$additional_where .= ')';
			}
		}

		if ($articlesAllowedOnly) {
			$additional_where .= ' AND articles_allowed=1';
		}

		// add sysfolder id
		$additional_where .= ' AND pid=' . tx_newspaper_Sysfolder::getInstance()->getPid(new tx_newspaper_section());

		$row = tx_newspaper::selectRows(
			'uid',
			'tx_newspaper_section',
			'1' . $additional_where,
			'',
			$sort_by
		);

		$s = array();
		for ($i = 0; $i < sizeof($row); $i++) {
			$s[] = new tx_newspaper_Section(intval($row[$i]['uid']));
		}
		return $s;
	}


	static public function getModuleName() { return 'np_section'; }


	/// Get root section(s)
	/// \return root section(s)
	public static function getRootSections() {
		$root = array();
		foreach(tx_newspaper_section::getAllSections(false) as $section) { // check all sections ...
			if (!$section->getParentSection()) {
				$root[] = $section; // no parent, so root section
			}
		}
		return $root;
	}

	static public function getSectionForTypo3Page($typo_page_id) {

        $row = tx_newspaper::selectZeroOrOneRows(
            'tx_newspaper_associated_section', 'pages',
            'uid = ' . $typo_page_id
        );
        $section_uid = intval($row['tx_newspaper_associated_section']);

        if (!$section_uid) return null;

        return new tx_newspaper_Section($section_uid);

    }

	/// Typo3  hooks

	/// \todo: documentation
	public static function processDatamap_afterDatabaseOperations($status, $table, $id, &$fieldArray, $that) {
		if ($status == 'new' && $table == 'tx_newspaper_section') {
			// a new section is stored, so assign a default article list
			$section_uid = intval($that->substNEWwithIDs[$id]); // $id contains "NEWS...." id
			$s = new tx_newspaper_Section($section_uid);
			$s->assignDefaultArticleList();
		}
	}

	/// \todo: documentation
	public static function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, $that) {
		if ($table != 'tx_newspaper_section') {
			return; // no section processed, nothing to do
		}

		// check if the article list was changed
		if (!isset($fieldArray['articlelist'])) {
			return; // article list wasn't changed, nothing to do
		}

//t3lib_div::devlog('al1 fiealdArray[al]', 'newspaper', 0, array('fieldArray' => $fieldArray, 'table' => $table, 'id' => $id));
		if (tx_newspaper::isAbstractClass($fieldArray['articlelist']) || !class_exists($fieldArray['articlelist'])) {
			return; // well, ... can't create an object for an abstract or non-existing class
		}

		// note: the value in the backend dropdown is the name of the article list class ($fieldArray['articlelist'])

		if (new $fieldArray['articlelist']() instanceof tx_newspaper_articlelist) {
//t3lib_div::devlog('sh post in section', 'newspaper', 0, array('fieldArray' => $fieldArray, 'table' => $table, 'id' => $id));
			// new article list class is a valid article list class, so change article list for this section now
			$s = new tx_newspaper_Section(intval($id)); // create section object
			$new_al = new $fieldArray['articlelist'](0, $s);
			if ($abstract_uid = $s->replaceArticleList($new_al)) {
				$fieldArray['articlelist'] = $abstract_uid; // store uid of abtracte article list in section, if replacing was successful
			}
		}
	}


 	private $attributes = array();					///< The member variables
	private $subPages = array();
	private $articlelist = null;
	private $uid = 0;
	private $abstract_articlelist_id = 0;

 	/// table which stores the tx_newspaper_ArticleList associated with this section
 	static private $list_table = 'tx_newspaper_articlelist';
 }

tx_newspaper::registerSaveHook(new tx_newspaper_Section());

?>