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
		$this->getAttribute('uid');
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
		return $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:title_' .
				tx_newspaper::getTable($this), false);
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

	/// Get all Sections which have $this as direct parent
	/** \return Array of Section objects
	 */
	public function getChildSections() {

		$row = tx_newspaper::selectRows(
				'uid', $this->getTable(),
				'parent_section = ' . $this->getUid()
			);
		
		$sections = array();
		if ($row) foreach ($row as $section_uid) {
			$sections[] = new tx_newspaper_Section($section_uid['uid']);
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
	
	/** \return the Article PageZone (PageZoneType has is_article set) on the 
	 * 		Page marked as the Article Page
	 */ 
	protected function getDefaultArticle() {
		foreach ($this->getSubPages() as $sub_page) {
			if ($sub_page->getPageType()->getAttribute('is_article_page')) {
				foreach ($sub_page->getActivePageZones() as $pagezone) {
					if ($pagezone->getPageZoneType()->getAttribute('is_article')) {
						return $pagezone;
					}
				}
			}
		}
		
		/// \todo Print the names of the article page type and page zone type
		throw new tx_newspaper_IllegalUsageException('There must be one page under section ' .
			$this->getAttribute('section_name') . ' that has the page type which is marked ' .
			'as Article Page. Additionally, this page must have a page zone which is marked' .
			' as the Article Page Zone'
		);
	}
 	
 	public function getTable() {
		return tx_newspaper::getTable($this);
	}
	
	public function setUid($uid) { $this->uid = $uid; }
	public function getUid() { return $this->uid; }
	
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
