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
		$this->createExtras();
	}
	
	/** For whatever reason, __destruct is not automatically called when the
	 *  unit test is over. This function must be called explicitly to clean up.
	 */
	public function removeAllJunkManually() {
		$this->removeExtras();
		$this->removePageZones();
		$this->removePages();
		$this->removeSectionHierarchy();
		///	Make sure you got all records
		foreach ($this->delete_all_query as $query) {
			$res = $GLOBALS['TYPO3_DB']->sql_query($query);
			$res || die('Aaaargh!');
		}
	}
	
	public function getPageZones() {
		if (!$this->pagezones) {
			foreach ($this->pagezone_uids as $uid) {
				$this->pagezones[] = tx_newspaper_PageZone_Factory::getInstance()->create($uid);
			}
		}
		return $this->pagezones;
	}
	
	public function getPages() {
		if (!$this->pages) {
			foreach ($this->page_uids as $uid) {
				$this->pages[] = new tx_newspaper_Page(intval($uid));
			}
		}
		return $this->pages;
	}
	
	////////////////////////////////////////////////////////////////////////////
	
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
#				// $page['inherit_pagetype_id'] = ...?;
				$this->page_uids[] = tx_newspaper::insertRows($this->page_table, $page);
			}
		}
	}

	/** Create a number of page zones (one for each page zone type defined in
	 *  $this->pagezonetype_data) for every page created above.
	 *  The page zone types are created first.
	 * 
	 *  \todo Create page zones which explicitly inherit from another page zone
	 *  	under the same Page
	 *  \todo Create page zones which don't inherit from another page zone
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
				tx_newspaper::updateRows(
					$this->pagezone_table, 
					"uid = $abstract_uid", 
					array('page_id' => $page_uid)
				);
			}
		}
	}
	
	private function createExtras() {
		foreach ($this->pagezone_uids as $pagezone_uid) {

			$pagezone = tx_newspaper_PageZone_Factory::getInstance()->create($pagezone_uid);

			foreach($this->extra_data as $i => $extra) {
				$extra_uid = tx_newspaper::insertRows($this->concrete_extra_table, $extra);
				$this->extra_uids[] = $extra_uid;

				$extra_object = new $this->concrete_extra_table($extra_uid);
				tx_newspaper::updateRows(
					$this->extra_table, 
					'uid = ' . $extra_object->getExtraUid(), 
					array(
						'position' => $this->extra_pos[$i],
						'origin_uid' => $extra_object->getExtraUid(),
					)
				);
			
				tx_newspaper::insertRows(
					$pagezone->getExtra2PagezoneTable(),
					array(
						'uid_local' => $pagezone->getUid(),
						'uid_foreign' => $extra_object->getExtraUid()
					));
			}
		}
	}
	
	private function removeExtras() {
		$pagezone = tx_newspaper_PageZone_Factory::getInstance()->create($this->pagezone_uids[0]);

		foreach ($this->extra_uids as $uid) {
			$extra_object = new $this->concrete_extra_table($uid);
			tx_newspaper::deleteRows(
				$pagezone->getExtra2PagezoneTable(),
				'uid_foreign = ' . $extra_object->getExtraUid()
			);
			tx_newspaper::deleteRows(
				$this->extra_table, 
				'uid = ' . $extra_object->getExtraUid()
			);
			tx_newspaper::deleteRows($this->concrete_extra_table, $uid);
		}
	}
	
	private function removeSectionHierarchy() {
		tx_newspaper::deleteRows($this->section_table, $this->section_uids);
	}
	
	private function removePages() {
		tx_newspaper::deleteRows($this->page_table, $this->page_uids);
		tx_newspaper::deleteRows($this->pagetype_table, $this->pagetype_uids);
	}	

	private function removePageZones() {
		tx_newspaper::deleteRows($this->pagezone_table, $this->pagezone_uids);
		tx_newspaper::deleteRows($this->pagezone_page_table, $this->pagezone_page_uids);
		tx_newspaper::deleteRows($this->pagezonetype_table, $this->pagezonetype_uids);
	}	
	
	private $delete_all_query = array(
		"DELETE FROM `tx_newspaper_section` WHERE section_name LIKE 'Unit Test%'",
		"DELETE FROM `tx_newspaper_section` WHERE deleted",
		"DELETE FROM `tx_newspaper_pagetype` WHERE type_name LIKE 'Unit Test%'",
		"DELETE FROM `tx_newspaper_pagetype` WHERE deleted",
		"DELETE FROM `tx_newspaper_page` WHERE template_set LIKE 'Unit Test%'",
		"DELETE FROM `tx_newspaper_page` WHERE deleted",
		"DELETE FROM `tx_newspaper_pagezonetype` WHERE type_name LIKE 'Unit Test%'",
		"DELETE FROM `tx_newspaper_pagezonetype` WHERE deleted",
		"DELETE FROM `tx_newspaper_pagezone_page` WHERE pagezone_id LIKE 'Unit Test%'",
		"DELETE FROM `tx_newspaper_pagezone_page` WHERE deleted",
		"DELETE FROM `tx_newspaper_pagezone` WHERE deleted",
		"DELETE FROM `tx_newspaper_extra_image` WHERE title LIKE 'Unit Test%'",
		"DELETE FROM `tx_newspaper_extra_image` WHERE deleted",
		"DELETE FROM `tx_newspaper_extra` WHERE deleted"
	);
		
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
			'section_name' => 'Unit Test - Big Daddy Section',
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
			'type_name' => 'Unit Test - Seitentyp 1',
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
			'type_name' => 'Unit Test - Seitentyp 2',
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
			'template_set' => 'Unit Test',
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
			'template_set' => 'Unit Test',
		),		
	);

	/// The Pages in the hierarchy as a flat array of objects
	private $pages = array();

	private $pagezonetype_table = 'tx_newspaper_pagezonetype';
	private $pagezonetype_data = array( 
		array(
			'pid' => '2822',
			'tstamp' => '1234567890',
			'crdate' => '1234567890',
			'cruser_id' => '1',
			'sorting' => '1024',
			'deleted' => '0',
			'type_name' => 'Unit Test - Hauptspalte',
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
			'type_name' => 'Unit Test - Rechte Spalte',
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
		'deleted'	=> '0',
		'pagezonetype_id' => '',
		'pagezone_id' => 'Unit Test',
		'extras'	=> '',
		'template_set' => '',
		'inherits_from' => '0'
	);

	/// The Page Zones in the hierarchy as a flat array of objects
	private $pagezones = array();
	
	private $extra_table = 'tx_newspaper_extra';
	private $concrete_extra_table = 'tx_newspaper_extra_image';
	private $extra2pagezone_table = 'tx_newspaper_pagezone_page_extras_mm';	
	private $extra_data = array(
		array(
			'pid' => 2573,
			'tstamp' => 1234567890,
			'crdate' => 1234567890,
			'cruser_id' => 1,
			'deleted' => 0,
			'hidden' => 0,
			'starttime' => 0,
			'endtime' => 0,
			'title' => "Unit Test - Image Title 1",
			'image' => "E3_033009T.jpg",	
			'caption' => "Caption for image 3",	
		),
		array(
			'pid' => 2573,
			'tstamp' => 1234567890,
			'crdate' => 1234567890,
			'cruser_id' => 1,
			'deleted' => 0,
			'hidden' => 0,
			'starttime' => 0,
			'endtime' => 0,
			'title' => "Unit Test - Image Title 2",	
			'image' => "120px-GentooFreeBSD-logo.svg_02.png",	
			'caption' => "Daemonic Gentoo",	
		),
		array(
			'pid' => 2573,
			'tstamp' => 1234567890,
			'crdate' => 1234567890,
			'cruser_id' => 1,
			'deleted' => 0,
			'hidden' => 0,
			'starttime' => 0,
			'endtime' => 0,
			'title' => "Unit Test - Image Title 3",	
			'image' => "lolcatsdotcomoh5o6d9hdjcawys6.jpg",	
			'caption' => "caption[5]",	
		),
	);
	
	private $extra_pos = array(
		1024, 2048, 4096
	);
	
}
?>
