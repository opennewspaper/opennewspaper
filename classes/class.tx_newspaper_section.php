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
 class tx_newspaper_Section implements tx_newspaper_InSysFolder {
 	
 	/// Construct a tx_newspaper_Section given the UID of the SQL record
 	function __construct($uid = 0) {
 		if ($uid) {
 			$this->setUid($uid);
 		}
 	}
 	
 	function getAttribute($attribute) {
	 	/// TODO: \todo tx_np::getAttribute($attribute, array $attributes, '*', $this->getTable(), 'uid = ' . $this->getUid() )
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
 	
 	function getList() {
 		if (!$this->articlelist) { 
 			$list = tx_newspaper::selectOneRow(
				'uid', self::$list_table, 'section_id  = ' . $this->getUid()
			);
			$this->articlelist = tx_newspaper_ArticleList_Factory::create($list['uid'], $this);
 		}
 	
 		return $this->articlelist; 
 	}
 	
 	function getParentPage() {
 		throw new tx_newspaper_NotYetImplementedException();
 	}
 	
 	/// \todo do!
 	function getSubPages() {
 		throw new tx_newspaper_NotYetImplementedException();
 	}
 	
 	function getTable() {
		return tx_newspaper::getTable($this);
	}
	
	function setUid($uid) { $this->uid = $uid; }
	function getUid() { return $this->uid; }
	
	static function getModuleName() { return 'np_section'; }
	
	
	
	/// get all descendant sections
	/** \param $section_uid uid of section to get descendant sections for
	 *  \param $include_hidden if true, hidden sections are included
	 *  \return array uid of sections
	 */
	static function getDescendantSections($section_uid, $include_hidden=true) {
		
		$sections = array();
		
		$where = $include_hidden? '' : ' AND hidden=0'; // check if hidden sections are to be included
	
		// get pid for section records
 		$sf = tx_newspaper_Sysfolder::getInstance();
		$pid = $sf->getPid(new tx_newspaper_Section());
	
		$queue[] = intval($section_uid); // start with given section
		while (count($queue) > 0) {
			reset($queue); // process first entry in queue
			$row = tx_newspaper::selectRows(
				'uid',
				'tx_newspaper_section',
				'parent_section=' . current($queue) . ' AND pid=' . $pid . $where
			);
			for ($i = 0; $i < sizeof($row); $i++) {
				$sections[] = $row[$i]['uid']; // store this descendant section
				$queue[] = $row[$i]['uid']; // append this descendant section to queue
			}
			unset($queue[key($queue)]); // remove current entry from queue
		}
		return $sections;
	}
	/// get all descendant sections - including given section (which is not descending ...)
	/** \param $section_uid uid of section to get descendant sections for
	 *  \param $include_hidden if true, hidden sections are included
	 *  \return array uid of sections
	 */
	static function getSelfAndDescendantSections($section_uid, $include_hidden=true) {
		$sections = self::getDescendantSections($section_uid, $include_hidden); // get descendant section
		return array_merge(array(intval($section_uid)), $sections); // prepend start section
	}
	
	
	
	
 	private $attributes = array();					///< The member variables
	private $subPages = array();
	private $articlelist = null;
	private $uid = 0;
 	
 	/// table which stores the tx_newspaper_ArticleList associated with this section
 	static private $list_table = 'tx_newspaper_articlelist';
 }
 
?>
