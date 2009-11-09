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
		global $LANG;
		if (!($LANG instanceof language)) {
			require_once(t3lib_extMgm::extPath('lang', 'lang.php'));
			$LANG = t3lib_div::makeInstance('language');
			$LANG->init('default');
		}
		return $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:title_' . $this->getTable(), false);	
	}

	public function getArticleList() {
		if (!$this->articlelist) { 
			$list = tx_newspaper::selectOneRow(
				'uid', self::$list_table, 'section_id  = ' . $this->getUid()
			);
			$this->articlelist = tx_newspaper_ArticleList_Factory::create($list['uid'], $this);
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
 	
 	public function getSubPage(tx_newspaper_PageType $type) {
 		foreach ($this->getSubPages() as $page) {
 			if ($page->getPageType()->getUid() == $type->getUid())
 				return $page;
 		}
 		///	\todo or throw?
 		return null;
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
		try{
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
	 * 		Page marked as the Article Page
	 */ 
	public function getDefaultArticle() {
		foreach ($this->getSubPages() as $sub_page) {
			foreach ($sub_page->getActivePageZones() as $pagezone) {
				if ($pagezone->getPageZoneType()->getAttribute('is_article')) {
					return $pagezone;
				}
			}
		}

		/// \todo Print the names of the article page type and page zone type
		throw new tx_newspaper_IllegalUsageException('There must be one page under section "' .
			$this->getAttribute('section_name') . '" that has the page type which is marked ' .
			'as Article Page. Additionally, this page must have a page zone which is marked' .
			' as the Article Page Zone.'
		);
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
 	 *  \param $must_have_extras The list of Extras which an Article in this
 	 * 			Section must have by default, as specified in TSConfig. These
 	 * 			are supplied as class names.
 	 */
 	public function copyDefaultArticle(array $must_have_extras) {

 		$new_article = $this->getDefaultArticle();
 		if (!$new_article instanceof tx_newspaper_Article) {
 			throw new tx_newspaper_InconsistencyException('getDefaultArticle() did not return an Article!');
 		}

		//	zeroing the UID causes the article to be written to DB as a new object.
 		$new_article->setAttribute('uid', 0);
 		$new_article->setUid(0);
 		$new_article->store();
 		
 		$default_extras = $this->getDefaultArticle()->getExtras();
 		
 		$new_article->clearExtras();
 		
 		$new_article->setAttribute('is_template', 0);

 		$new_article->setAttribute('crdate', time());
 		$new_article->setAttribute('tstamp', time());

		///	Copy the must-have Extras from default placement
		/// \todo does not work for multiple extras of the same class
		foreach($default_extras as $i => $default_extra) {
			
			$key = array_search(tx_newspaper::getTable($default_extra), $must_have_extras);
			if ($key !== false) {
				$new_article->addExtra($default_extra->duplicate());
				unset($must_have_extras[$key]);
			}
		}
		
		/**	Add must-have Extras which are not in default placement:
		 *  empty, hidden, at first position before first paragraph
		 */
		foreach($must_have_extras as $key => $default_extra) {
			$new_extra = new $default_extra;
			
			//	I think this is needed before i can safely setAttribute(). Not sure. Anyway, BSTS.
			$new_extra->store();
			
	 		$new_extra->setAttribute('crdate', time());
	 		$new_extra->setAttribute('tstamp', time());
			
			$new_extra->setAttribute('show_extra', 0);
			$new_extra->setAttribute('paragraph', 0);
			$new_extra->setAttribute('position', 0);
			
			$new_extra->store();						//	Final store()
			
			/// Write association table entry article -> extra
			/// \todo $new_article->relateExtra2Article($new_extra)?
			tx_newspaper::insertRows(tx_newspaper_Article::getExtra2PagezoneTable(),
				array(
					'uid_local' => $new_article->getUid(),
					'uid_foreign' => $new_extra->getExtraUid(),
					));
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
	
	
	public function getArticles($limit = 10) {
		$limit = intval($limit);
		$row = tx_newspaper::selectRows(
			'uid_local',
			'tx_newspaper_article_sections_mm',
			'uid_foreign=' . $this->getUid()
		);
//t3lib_div::devlog('s getArticles row', 'newspaper', 0, $row);
		$list = array();
		for ($i = 0; $i < sizeof($row); $i++) {
			$a = new tx_newspaper_Article(intval($row[$i]['uid_local']));
			if ($a->getAttribute('deleted') == 0) {
				$list[] = $a;
				if (sizeof($list) == $limit) {
					return $list;
				}	
			} 
		}
		return $list;
	}
	
	///	Generate a URL which links to the "section overview" page of the Section
	public function getLink() {
		return tx_newspaper::typolink_url(
			array('id' => $this->getTypo3PageID())
		);
	}
	
	///	Get all tx_newspaper_Section records in the DB.
	/** \param $sort_by Field of the \c tx_newspaper_section SQL table to sort 
	 * 		results by.
	 *  \return tx_newspaper_Section objects in the DB.
	 */
	public static function getAllSections($sort_by = 'sorting') {
		$TSConfig = t3lib_BEfunc::getPagesTSconfig(0);
		$excluded = $TSConfig['newspaper.']['excluded_sections'];
		t3lib_div::devlog('excluded', 'newspaper', 0, $excluded);
		
		$excluded_sections = array();
		$additional_where = '';
		if ($excluded) {
			foreach (explode(',', $excluded) as $excluded_section_uid) {
				$excluded_section = new tx_newspaper_Section($excluded_section_uid);
				$excluded_sections = array_merge($excluded_sections, $excluded_section->getChildSections(true));
			}
			if ($excluded_sections) {
				$additional_where .= ' AND uid NOT IN (';
				$separator = '';
				foreach ($excluded_sections as $excluded_section) {
					$additional_where .= $excluded_section->getUid();
					$separator = ', ';
				}
				$additional_where .= ')';
			}
		}
		
		t3lib_div::devlog('$additional_where', 'newspaper', 0, $additional_where);
		
		$row = tx_newspaper::selectRows(
			'uid',
			'tx_newspaper_section',
			$additional_where,
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
	
	
	
 	private $attributes = array();					///< The member variables
	private $subPages = array();
	private $articlelist = null;
	private $uid = 0;
	private $abstract_articlelist_id = 0;
 	
 	/// table which stores the tx_newspaper_ArticleList associated with this section
 	static private $list_table = 'tx_newspaper_articlelist';
 }
 
?>
