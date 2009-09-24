<?php
/**
 *  \file class.tx_newspaper_page.php
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
 
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_pagetype.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_pagezone_factory.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_smarty.php');


/// A page type for an online edition of a newspaper
/** Examples include:
 *  - List view of the most recent articles in a section
 *  - Article view, displays an article
 *  - Comments page, shows the comments to an article (or a section page)
 *  - RSS feed for list view or article page
 *  - Mobile versions of any of the above
 *  - Whatever else you can think of
 */
class tx_newspaper_Page
		implements tx_newspaper_StoredObject, tx_newspaper_Renderable {
	
	/// Construct a page from DB
	/** \param $parent Either the newspaper section the page is in, or the UID
	 * 		of the page in the DB
	 *  \param $type page type of which a page is created
	 */
	public function __construct($parent = null, tx_newspaper_PageType $type = null) {
		if ($parent instanceof tx_newspaper_Section) {
			$this->parentSection = $parent;
			$this->pagetype = $type;
		} else if (is_integer($parent)) {
			$this->setUid($parent);
		} else if ($parent != null || TYPO3_MODE == 'FE') {
			throw new tx_newspaper_IllegalUsageException(
				'First argument to tx_newspaper_Page::__construct() must be' .
				' either a tx_newspaper_Section or an integer UID! In fact it is: ' .
				$parent);
		}
				
		$this->smarty = new tx_newspaper_Smarty();
 	}
 	
 	public function __clone() {
 		/*  ensure attributes are loaded from DB. readAttributesFromDB() isn't  
 		 *  called here because maybe the content is already there and it would
 		 *  cause the DB operation to be done twice.
 		 */
		$this->getAttribute('uid');
		
		///  unset the UID so the object can be written to a new DB record.
 		$this->attributes['uid'] = 0;
 		$this->setUid(0);
 		
 		$this->setAttribute('crdate', time());
 		$this->setAttribute('tstamp', time());
 		
 		/// clone page zones contained on page
 		$old_pagezones = $this->getPageZones();
		$this->pageZones = array();
		if (is_array($old_pagezones) && sizeof($old_pagezones) > 0) {
			foreach ($old_pagezones as $old_pagezone) {
				$this->pageZones[] = clone $old_pagezone;
			}
		}
 	}
 	
 	public function __toString() {
 		$this->getAttribute('uid');
 		$ret = $this->getTable() . ':' . " \n" .
 			   'UID: ' . $this->getUid() . " \n" .
				($this->parentSection? ('parentSection: ' . $this->parentSection->getUid() . " \n"): '') .
 			   'condition: ' . $this->condition . " \n" .
 			   'pageZones: ' . print_r($this->pageZones, 1) . " \n" .
 			   'attributes: ' . print_r($this->attributes, 1) . " \n" .
 			   (($this->getPageType() && $this->getPageType() instanceof tx_newspaper_PageType)? 
 			   		('pagetype: ' . $this->getPageType()->getUid() . ' (' . $this->getPageType()->getAttribute('type_name') . ") \n"): 
					'');
		return $ret;
 	}
 	
 	function getAttribute($attribute) {
		/// Read Attributes from persistent storage on first call
		if (!$this->attributes) {
			$this->readAttributesFromDB();
		}

 		if (!array_key_exists($attribute, $this->attributes)) {
        	throw new tx_newspaper_WrongAttributeException($attribute);
 		}
 		return $this->attributes[$attribute];
 	}

	public function setAttribute($attribute, $value) {
		/// Read Attributes from persistent storage on first call
		if (!$this->attributes) {
			$this->readAttributesFromDB();
		}
		$this->attributes[$attribute] = $value;
	}
	
	/// insert page data (if uid == 0) or update if uid > 0
	public function store() {

		if ($this->getUid()) {
			/// If the attributes are not yet in memory, read them now
			if (!$this->attributes) $this->readAttributesFromDB();
			
			tx_newspaper::updateRows(
				$this->getTable(), 'uid = ' . $this->getUid(), $this->attributes
			);
		} else {
			$this->attributes['section'] = $this->parentSection->getUid();
			$this->attributes['pagetype_id'] = $this->pagetype->getUid();
			/** \todo If the PID is not set manually, $tce->process_datamap()
			 * 		  fails silently. 
			 */
			$this->attributes['pid'] = tx_newspaper_Sysfolder::getInstance()->getPid($this);

			$this->setUid(
				tx_newspaper::insertRows(
					$this->getTable(), $this->attributes
				)
			);
			
			//	empty attributes array so it can be read in full at next access
			$this->attributes = array(); 
		}
		
		/// store all page zones and set the page_id of their respective pagezone superclass entry
		//	disabled because pagezones now must be enabled manually
		/// \todo delete, i guess
		if (false && $this->pageZones) foreach ($this->pageZones as $pagezone) {
			$pagezone_uid = $pagezone->store();
			$pagezone_superclass_uid = $pagezone->createPageZoneRecord();
			tx_newspaper::updateRows(
				'tx_newspaper_pagezone', "uid = $pagezone_superclass_uid", 
				array('page_id' => $this->getUid())
			);
		}
		
		return $this->getUid();		
	}

	/** \param $s section
	 *  \return true if page can be accessed and if assigned to given section(FE/BE use enableFields)
	 */
	function isValid(tx_newspaper_Section $s) {
		// check if page is valid
		try {
			$tmp = $this->getAttribute('uid'); // getAttribute forces the object to be read from database
		} catch (tx_newspaper_EmptyResultException $e) {
			return false;
		}
		
		// check if page is assigned to section
		$assigned_page = $s->getSubPages();
		for ($i = 0; $i < sizeof($assigned_page); $i++) {
			if ($assigned_page[$i]->getUid() == $this->getUid())
				return true;
		}
			
		return false;
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

	function getPageZones() {
 		/// Get tx_newspaper_PageZone list for current page at first call
		if (!$this->pageZones) {
			$uids = tx_newspaper::selectRows(
	 			'uid', 'tx_newspaper_pagezone', 
				'page_id = '.$this->getAttribute('uid')
			);
	
			if ($uids) {
	        	foreach ($uids as $uid) {
	        		$this->pageZones[] = 
	        			tx_newspaper_PageZone_Factory::getInstance()->create($uid['uid']);
	        	}
			} else {
				/*  no page zones under this page - pagezones attribute is set 
				 *  to true so it is not read again.
				 */
				$this->pageZones = true; 
			}
		}
		
		return $this->pageZones;
	}
	
	function getPageZone(tx_newspaper_PageZoneType $type) {
 		foreach ($this->getPageZones() as $pagezone) {
 			if ($pagezone->getPageZoneType()->getUid() == $type->getUid())
 				return $pagezone;
 		}
 		///	\todo or throw?
 		return null;
	}
	
	/// Render the page, containing all associated page areas
	/** The correct template is found the following way.
	 *  - the template set for the page is set via TSConfig
	 *  - the name for the page is found via its page type
	 *  - if the template \c tx_newspaper_page.tmpl exists under directory
	 *    \c template_set/page_type, use it
	 *  - else, if \c tx_newspaper_page.tmpl exists under directory
	 *    \c template_set, use it
	 *  - else, use the default template under 
	 * 	  <tt>PATH_typo3conf . 'ext/newspaper/res/templates'</tt>
	 * 
	 *  Default smarty template:
	 *  \include res/templates/tx_newspaper_page.tmpl
	 * 
	 *  \todo implement this template-finding logic by calling 
	 * 		  $this->smarty-setTemplateSearchPath()
	 * 
	 *  \param $template_set the template set used to render this page (as 
	 *  		passed down from The Big One - should be always empty.
	 * 
	 *  \return The rendered page as HTML (or whatever your template does) 
	 */
 	public function render($template_set = '') {
		/// Check the parent Section and own attributes whether to use a specific template set
 		if ($this->getParentSection()->getAttribute('template_set')) {
			$template_set = $this->getParentSection()->getAttribute('template_set');
		}
		if ($this->getAttribute('template_set')) {
			$template_set = $this->getAttribute('template_set');
		}
		
		/// Configure Smarty rendering engine
		if ($template_set) $this->smarty->setTemplateSet($template_set);
		if ($this->getPagetype()) $this->smarty->setPageType($this);

		/// Pass global attributes to Smarty
 		$this->smarty->assign('section', $this->parentSection->getAttribute('section_name'));
 		$this->smarty->assign('page_type', $this->pagetype->getAttribute('type_name'));
 		
		/// Pass the page zones on this page, already rendered, to Smarty
 		$rendered = array();
 		$zones = $this->getPageZones(); 
 		if (is_array($zones)) foreach ($zones as $zone) {
 			$rendered[$zone->getPageZoneType()->getAttribute('type_name')] = $zone->render($template_set);
 		}
		$this->smarty->assign('page_zones', $rendered);
		
		/// Return the rendered page
 		return $this->smarty->fetch($this);
 	}

 	///	Lists all Pages with the given PageType
 	/** \todo move to tx_newspaper_PageType
 	 */
 	static public function listPagesWithPageType(tx_newspaper_PageType $pt, $limit=10) {

		$limit = intval($limit);
		$limit_part = ($limit > 0)? '0,' . $limit : ''; 
		
		$row = tx_newspaper::selectRows(
			'uid',
			'tx_newspaper_page',
			'deleted=0 AND pagetype_id=' . $pt->getUid(),
			'',
			'tstamp DESC',
			$limit_part
		);

		$list = array();
		for ($i = 0; $i < sizeof($row); $i++) {
			$list[] = new tx_newspaper_Page(intval($row[$i]['uid']));
		}
		return $list;
	}
 	
 	///	Lists all Pages which contain a PageZone with the given PageZoneType
 	/** \todo move to tx_newspaper_PageZoneType
 	 */
 	static public function listPagesWithPageZoneType(tx_newspaper_PageZoneType $pzt, $limit=10) {
#t3lib_div::devlog('lPZWPZT', 'newspaper', 0, $pzt->getUid());
		$limit = intval($limit);
		$limit_part = ($limit > 0)? '0,' . $limit : ''; 
		
		$list = array();
		if ($pzt->getAttribute('is_article') == 0) {
			/// so it's a non-article page zone type
#t3lib_div::devlog('lPZWPZT pz', 'newspaper', 0);
			$row = tx_newspaper::selectRows(
				'uid',
				'tx_newspaper_pagezone_page',
				'deleted=0 AND pagezonetype_id=' . $pzt->getUid(),
				'',
				'tstamp DESC',
				$limit_part
			);
			for ($i = 0; $i < sizeof($row); $i++) {
				$list[] = tx_newspaper_Page::getPageOfPageZonePage(new tx_newspaper_PageZone_Page(intval($row[$i]['uid'])));
			}
		} else {
			/// so it's an article page zone type
t3lib_div::devlog('lPZWPZT art', 'newspaper', 0);
//			$row = tx_newspaper::selectRows(
//				'uid',
//				'tx_newspaper_article',
//				'deleted=0 AND pagezonetype_id=' . $pzt->getUid(),
//				'',
//				'tstamp DESC',
//				$limit_part
//			);
//			for ($i = 0; $i < sizeof($row); $i++) {
//				$list[] = new tx_newspaper_Article(intval($row[$i]['uid']));
//			}
		}
		return $list;
	}

	/// get page zone object for given page zone page object
	/** \param $pzp page zone page object
	 *  \return parent page zone object
	 */
	public static function getPageOfPageZonePage(tx_newspaper_PageZone_Page $pzp) {
#t3lib_div::devlog('get pzp', 'newspaper', 0, $pzp->getAttribute('uid'));
		$row = tx_newspaper::selectOneRow(
			'page_id',
			'tx_newspaper_pagezone',
			'pagezone_table="tx_newspaper_pagezone_page" AND pagezone_uid=' . $pzp->getAttribute('uid')
		);
#t3lib_div::devlog('get pz row', 'newspaper', 0, $row);
		return new tx_newspaper_Page(intval($row['page_id']));		
	}

	/// get active pages zone for this page
	/** \return array of active pages zone objects for given page
	 */
	public function getActivePageZones() {

		$pid_list = tx_newspaper_Sysfolder::getInstance()->getPidsForAbstractClass('tx_newspaper_PageZone');
#t3lib_div::devlog('gapz pidlist', 'newspaper', 0, $pid_list);
		if (sizeof($pid_list) == 0) {
			throw new tx_newspaper_SysfolderNoPidsFoundException('tx_newspaper_PageZone');
		}
	
		$row = tx_newspaper::selectRows(
			'*',
			'tx_newspaper_pagezone',
			'deleted=0 AND pid IN (' . implode(',', $pid_list) . ') AND page_id=' . intval($this->getUid())
		);

#t3lib_div::devlog('gapz', 'newspaper', 0, $row);
		$list = array();
		for ($i = 0; $i < sizeof($row); $i++) {
			$list[] = new $row[$i]['pagezone_table'](intval($row[$i]['pagezone_uid']));
		}
#t3lib_div::debug($list);
		return $list;
	}

	/** \return The tx_newspaper_Section under which this page lies
	 */
 	public function getParentSection() { 
 		if (!$this->parentSection) {
 			$this->parentSection = new tx_newspaper_Section(intval($this->getAttribute('section')));
 		}
 		return $this->parentSection; 
 	}
 	
 	/** \return tx_newspaper_PageType of the current tx_newspaper_Page
 	 */
 	public function getPageType() {
# 		if (!$this->pagetype) {
 			$this->pagetype = new tx_newspaper_PageType(intval($this->getAttribute('pagetype_id')));
# 		} 
 		return $this->pagetype; 
 	}
 	
	static function getModuleName() { return 'np_page'; }

 	public function getTable() { return tx_newspaper::getTable($this); }
	function getUid() { return intval($this->uid); }
	function setUid($uid) { $this->uid = $uid; }

	/// Read the record for this object from DB
	/** Because a page can be constructed both with a UID and a combination of
	 *  parent section and page type to uniquely define it, reading the record
	 *  is a bit more complicated than for other objects. Thus, I have factored
	 *  it out here.
	 */
	protected function readAttributesFromDB() {
		if ($this->getUid()) {
			$this->attributes = tx_newspaper::selectOneRow(
				'*', $this->getTable(), 'uid = ' . $this->getUid()
			);
		} else {
			$this->attributes = tx_newspaper::selectOneRow('*', $this->getTable(),
				'section = ' . $this->parentSection->getAttribute('uid') . 
				' AND pagetype_id = ' . $this->pagetype->getID()
			);
			$this->setUid($this->attributes['uid']);
		}
	}


 	private $uid = 0;								///< UID of record in the DB
 	
 	private $smarty = null;							///< Smarty object for HTML rendering
 	private $parentSection = null;					///< Newspaper section this page is in
 	private $condition = null;						///< WHERE-condition used to find current page
 	private $pageZones = array();					///< Page zones on this page
 	private $attributes = array();					///< The member variables
 	private $pagetype = null;						///< tx_newspaper_PageType of the current page
 	
 	/// Default Smarty template for HTML rendering
 	/** \todo get rid of this variable */
 	static private $defaultTemplate = 'tx_newspaper_page.tmpl';
 	
}
 
?>
