<?php
/**
 *  \file class.hierarchy.php
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
 *  \date Apr 28, 2009
 */
 
/// \todo brief description
/** \todo long description
 */
class tx_newspaper_hierarchy {

	public function __construct() {
		$this->createSectionHierarchy();
		$this->createPages();
		$this->createPageZones();
	}
	
	public function __destruct() {
		$this->removePageZones();
		$this->removePages();
		$this->removeSectionHierarchy();
	}
	
	public function getPageZones() {
		if (!$this->pagezones) {
			foreach ($this->pagezone_uids as $uid) {
				$this->pagezones[] = tx_newspaper_PageZone_Factory::getInstance()->create($uid);
			}
		}
		return $this->pagezones;
	}
	
	private function createSectionHierarchy() {
		foreach ($this->section_data as $section) {
			$section['parent_section'] = $this->section_uids[sizeof($this->section_uids)-1];
			$this->section_uids[] = tx_newspaper::insertRows($this->section_table, $section);
		}
	}
	
	private function createPages() {
		foreach ($this->pagetype_data as $pagetype) {
			$this->pagetype_uids[] = tx_newspaper::insertRows($this->pagetype_table, $pagetype);
		}
		foreach ($this->section_uids as $section_uid) {
			foreach ($this->page_data as $i => $page) {
				$page['section'] = $section_uid;
				$page['pagetype_id'] = $this->pagetype_uids[$i];
				// $page['inherit_pagetype_id'] = ...?;
				$this->page_uids[] = tx_newspaper::insertRows($this->page_table, $page);
			}
		}
	}

	/** Create a number of page zones (one for each page zone type defined in
	 *  $this->pagezonetype_data) for every page created above.
	 *  The page zone types are created first.
	 */
	private function createPageZones() {
		foreach ($this->pagezonetype_data as $pagezonetype) {
			$this->pagezonetype_uids[] = tx_newspaper::insertRows($this->pagezonetype_table, $pagezonetype);
		}
		foreach ($this->page_uids as $page_uid) {
			foreach ($this->pagezonetype_uids as $pagezonetype_uid) {
				$this->pagezone_page_data['pagezonetype_id'] = $pagezonetype_uid;
				$concrete_uid = tx_newspaper::insertRows($this->pagezone_page_table, $this->pagezone_page_data);
				$this->pagezone_page_uids[] = $concrete_uid;
				//	the c'tor creates the parent record as well
				$temp_pagezone = new tx_newspaper_Pagezone_Page($concrete_uid);
				
				$abstract_uid = $temp_pagezone->getPageZoneUID();
				$this->pagezone_uids[] = $abstract_uid;
				//  connect the abstract record to the page
				tx_newspaper::updateRows($this->pagezone_table, "uid = $abstract_uid", array('page_id' => $page_uid));
			}
		}
	}
	
	private function removeSectionHierarchy() {
		foreach ($this->section_uids as $uid) {
			tx_newspaper::deleteRows($this->section_table, $uid);
		}
	}
	
	private function removePages() {
		foreach ($this->page_uids as $uid) {
			tx_newspaper::deleteRows($this->page_table, $uid);
		}
		foreach ($this->pagetype_uids as $uid) {
			tx_newspaper::deleteRows($this->pagetype_table, $uid);
		}
	}	

	private function removePageZones() {
		foreach ($this->pagezone_uids as $uid) {
			tx_newspaper::deleteRows($this->pagezone_table, $uid);
		}
		foreach ($this->pagezone_page_uids as $uid) {
			tx_newspaper::deleteRows($this->pagezone_page_table, $uid);
		}
		foreach ($this->pagezonetype_uids as $uid) {
			tx_newspaper::deleteRows($this->pagezonetype_table, $uid);
		}
	}	
	
	private $section_table = 'tx_newspaper_section';
	private $section_uids = array();
	/// A hierarchy of three sections is generated
	private $section_data = array(
		array(	
			'pid' => '2828',
			'tstamp' => '1234567890',
			'crdate' => '1234567890',
			'cruser_id' => '1',
			'sorting' => '1024',
			'deleted' => '0',
			'section_name' => 'Big Daddy Section',
			'parent_section' => '',
			'articlelist' => '',
			'template_set' => '',
			'pagetype_pagezone' => ''
		),
		array(	
			'pid' => '2828',
			'tstamp' => '1234567890',
			'crdate' => '1234567890',
			'cruser_id' => '1',
			'sorting' => '2048',
			'deleted' => '0',
			'section_name' => 'Son Section',
			'parent_section' => '',
			'articlelist' => '',
			'template_set' => '',
			'pagetype_pagezone' => ''
		),
		array(	
			'pid' => '2828',
			'tstamp' => '1234567890',
			'crdate' => '1234567890',
			'cruser_id' => '1',
			'sorting' => '4096',
			'deleted' => '0',
			'section_name' => 'Little Grandchild Section',
			'parent_section' => '',
			'articlelist' => '',
			'template_set' => '',
			'pagetype_pagezone' => ''
		),
	);
	
	private $pagetype_table = 'tx_newspaper_pagetype';
	private $pagetype_uids = array();
	/// Two pagetypes are created to associate with the pages
	private $pagetype_data = array(
		array(
			'pid' => '2827',
			'tstamp' => '1234567890',
			'crdate' => '1234567890',
			'cruser_id' => '1',
			'sorting' => '1024',
			'deleted' => '0',
			'type_name' => 'Seitentyp 1',
			'normalized_name' => 'seitentyp_1',
			'get_var' => '',
			'get_value' => '',
		),
		array(
			'pid' => '2827',
			'tstamp' => '1234567890',
			'crdate' => '1234567890',
			'cruser_id' => '1',
			'sorting' => '2048',
			'deleted' => '0',
			'type_name' => 'Seitentyp 2',
			'normalized_name' => 'seitentyp_2',
			'get_var' => 'blah',
			'get_value' => '1',
		),
	);
	
	private $page_table = 'tx_newspaper_page';
	private $page_uids = array();
	/// There are two pages below each section
	private $page_data = array(
		array(	
			'pid' => '2474',
			'tstamp' => '1234567890',
			'crdate' => '1234567890',
			'cruser_id' => '1',
			'deleted' => '0',
			'section' => '',
			'pagetype_id' => '',
			'inherit_pagetype_id' => '',
			'template_set' => '',
		),
		array(	
			'pid' => '2474',
			'tstamp' => '1234567890',
			'crdate' => '1234567890',
			'cruser_id' => '1',
			'deleted' => '0',
			'section' => '',
			'pagetype_id' => '',
			'inherit_pagetype_id' => '',
			'template_set' => '',
		),
	);

	private $pagezonetype_table = 'tx_newspaper_pagezonetype';
	private $pagezonetype_data = array( 
		array(
			'pid' => '2822',
			'tstamp' => '1234567890',
			'crdate' => '1234567890',
			'cruser_id' => '1',
			'sorting' => '1024',
			'deleted' => '0',
			'type_name' => 'Ein Seitenbereich - z.B. die Hauptspalte',
			'normalized_name' => 'seitenbereich1',
			'is_article' => '',
		),
		array(
			'pid' => '2822',
			'tstamp' => '1234567890',
			'crdate' => '1234567890',
			'cruser_id' => '1',
			'sorting' => '1024',
			'deleted' => '0',
			'type_name' => 'Noch ein Seitenbereich, sagen wir die rechte Spalte',
			'normalized_name' => 'seitenbereich2',
			'is_article' => '',
		),
	);
	
	private $pagezone_table = 'tx_newspaper_pagezone';
	private $pagezone_uids = array();
	private $pagezone_page_table = 'tx_newspaper_pagezone_page';
	private $pagezone_page_uids = array();
	private $pagezone_page_data = array(
		'pid'		=> '2476',
		'tstamp'	=> '1234567890',
		'crdate'	=> '1234567890', 		  	
		'cruser_id'	=> '1',
		'sorting'	=> '256',
		'deleted'	=> '0',
		'pagezonetype_id' => '',
		'pagezone_id' => 'X',
		'extras'	=> '',
		'template_set' => '',
		'inherits_from' => '0'
	);

	/// The Page Zones in the hierarchy as a flat array of objects
	private $pagezones = array();
}
?>
