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
class tx_newspaper_Page implements tx_newspaper_InSysFolder {
	
	/// Construct a page from DB
	/** \param $parent The newspaper section the page is in
	 *  \param $condition SQL WHERE condition to further specify the page
	 */
	public function __construct($parent = null, tx_newspaper_PageType $type = null) {
		if ($parent instanceof tx_newspaper_Section) {
			$this->parentSection = $parent;
			$this->pagetype = $type;
		} else if (is_integer($parent)) {
			$this->setUid($parent);
			
		} else 
			throw new tx_newspaper_IllegalUsageException(
				'First argument to tx_newspaper_Page::__construct() must be' .
				' either a tx_newspaper_Section or an integer UID! In fac it is: ' .
				$parent);
				
		/// Configure Smarty rendering engine
		$this->smarty = new tx_newspaper_Smarty();
		if ($type != null) {
			$this->smarty->setTemplateSearchPath(
				array(
					'template_sets/' . strtolower($this->pagetype->getAttribute('type_name')),
					'template_sets'
				)
			);
		}
 	}
 	
 	public function __clone() {
 		/*  ensure attributes are loaded from DB. Don't call 
 		 *  readAttributesFromDB() here because maybe the content is already
 		 *  there and it would cause the DB operation to be done twice.
 		 */
		$this->getAttribute('uid');
		
		//  unset the UID so the object can be written to a new DB record.
 		$this->attributes['uid'] = 0;
 		$this->setUid(0);
 		
 		/// \todo clone page zones
 		$old_pagezones = $this->getPageZones();
 		$this->pageZones = array();
 		foreach ($old_pagezones as $old_pagezone) {
 			$this->pageZones[] = clone $old_pagezone;
 		}
 	}
 	
 	public function __toString() {
 		$ret = $this->getTable() . ':' . " \n" .
 			   'UID: ' . $this->getUid() . " \n" .
 			   'parentSection: ' . $this->parentSection . " \n" .
 			   'condition: ' . $this->condition . " \n" .
 			   'pageZones: ' . print_r($this->pageZones, 1) . " \n" .
 			   'attributes: ' . print_r($this->attributes, 1) . " \n" .
 			   'pagetype: ' . $this->pagetype->getAttribute('type_name') . " \n";
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
	
	/// Render the page, containing all associated page areas
	/** The correct template is found the following way.
	 *  - the template set for the page is set via TSConfig
	 *  - the name for the page is found via its page type
	 *  - if the template <tt>tx_newspaper_page.tmpl</tt> exists under directory
	 *    <tt><template_set>/<page_type></tt>, use it
	 *  - else, if <tt>tx_newspaper_page.tmpl</tt> exists under directory
	 *    <tt><template_set></tt>, use it
	 *  - else, use the default template under 
	 * 	  <tt>PATH_typo3conf . 'ext/newspaper/res/templates'</tt>
	 * 
	 *  \todo implement this template-finding logic by calling 
	 * 		  $this->smarty-setTemplateSearchPath()
	 * 
	 *  \return The rendered page as HTML (or whatever your template does) 
	 */
 	public function render($template = '') {
 		if (!$template) {
 			$template = $this;
 		}

 		$this->smarty->assign('section', $this->parentSection->getAttribute('section_name'));
 		$this->smarty->assign('page_type', $this->pagetype->getAttribute('type_name'));
 		
 		$rendered = array(); 
 		foreach ($this->getPageZones() as $zone) {
 			$rendered[$zone->getAttribute('name')] = $zone->render();
 		}
		$this->smarty->assign('page_zones', $rendered);
		
 		return $this->smarty->fetch($template);
 	}
 	
 	public function getParent() {
 		return $this->parentSection;
 	}
 	
 	public function getPageType() { return $this->pagetype; }
 	
	static function getModuleName() { return 'np_page'; }

 	public function getTable() { return tx_newspaper::getTable($this); }
	function getUid() { return intval($this->uid); }
	function setUid($uid) { $this->uid = $uid; }


	/// get active pages for given section
	/** \param $section section object
	 *  \return array uids of active pages for given section
	 *  \todo move to tx_newspaper_Section
	 */
	public static function getActivePages(tx_newspaper_Section $section, $include_hidden=true) {
		$where = ($include_hidden)? '' : ' AND hidden=0'; // should hidden pages be included?
		$sf = tx_newspaper_Sysfolder::getInstance();
#t3lib_div::devlog('gap', 'newspaper', 0);
		$p = new tx_newspaper_Page($section);
		$row = tx_newspaper::selectRows(
			'*',
			$p->getTable(),
			'pid=' . $sf->getPid($p) . ' AND section=' . $section->getAttribute('uid') . $where
		);
		return $row;
	}

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


 	private $uid = 0;
 	
 	private $smarty = null;							///< Smarty object for HTML rendering
 	private $parentSection = null;					///< Newspaper section this page is in
 	private $condition = null;						///< WHERE-condition used to find current page
 	private $pageZones = array();					///< Page zones on this page
 	private $attributes = array();					///< The member variables
 	private $pagetype = null;
 	
 	/// Default Smarty template for HTML rendering
 	static private $defaultTemplate = 'tx_newspaper_page.tmpl';
 	
}
 
?>
