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
 
require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_pagetype.php');
require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_pagezone_factory.php');

/// A page type for an online edition of a newspaper
/** Examples include:
 *  - List view of the most recent articles in a section
 *  - Article view, displays an article
 *  - Comments page, shows the comments to an article (or a section page)
 *  - RSS feed for list view or article page
 *  - Mobile versions of any of the above
 *  - Whatever else you can think of
 * 
 *  Currently just a dummy.
 */
class tx_newspaper_Page {
	
	/// Construct a page from DB
	/** \param $parent The newspaper section the page is in
	 *  \param $condition SQL WHERE condition to further specify the page
	 */
	public function __construct(tx_newspaper_Section $parent, tx_newspaper_PageType $type) {

		$this->parentSection = $parent;
		$this->pagetype = $type;

		/// Configure Smarty rendering engine
		$this->smarty = new Smarty();
		$tmp = "/tmp/" . substr(BASEPATH, 1);
		file_exists($tmp) || mkdir($tmp, 0774, true);
		
		$this->smarty->template_dir = BASEPATH.'/fileadmin/templates/tx_newspaper/smarty';
		$this->smarty->compile_dir  = $tmp;
		$this->smarty->config_dir   = $tmp;
		$this->smarty->cache_dir    = $tmp;		
 	}
 	
 	function getAttribute($attribute) {
		/// Read Attributes from persistent storage on first call
		if (!$this->attributes) {
			$this->attributes = tx_newspaper::selectOneRow('*', $this->getTable(),
				'section = ' . $this->parentSection->getAttribute('uid') . 
				' AND pagetype_id = ' . $this->pagetype->getID()
			);
			t3lib_div::debug(tx_newspaper::$query);
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
	/**
	 *  \todo default smarty template?
	 * 
	 *  \return The rendered page as HTML (or XML, if you insist) 
	 */
 	public function render($template = '') {
 		if (!$template) $template = self::$defaultTemplate;
 		
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
 	
 	public function getTable() {
 		return tx_newspaper::getTable($this);
 	}
 	
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
