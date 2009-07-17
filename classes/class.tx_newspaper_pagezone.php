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
 
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_pagezonetype.php');
 
/// A section of a page for an online edition of a newspaper
/** Pages are divided into several independent sections, or zones, such as:
 *  - Left column, containing the main content area (article text, list of 
 * 	  articles)
 *  - Right column with additional info or ads
 *  - footer area  
 *  A PageZone contains a list of content elements.
 * 
 *  Class tx_newspaper_PageZone implements the tx_newspaper_Extra interface,
 *  because a PageZone can be placed like an Extra.
 */
abstract class tx_newspaper_PageZone implements tx_newspaper_ExtraIface {
	
	const EXTRA_SPACING = 1024;
	
	/// Configure Smarty rendering engine
	public function __construct($uid = 0) {
		/// Configure Smarty rendering engine
		$this->smarty = new tx_newspaper_Smarty();
		if ($uid) {
			$this->setUid($uid);
			/** I'm not sure whether the following line should remain. It's a
			 *  safety net because currently it's not ensured that extras are 
			 *  created consistently.
			 */
			$this->extra_uid = tx_newspaper_Extra::createExtraRecord($uid, $this->getTable());
		}
	}
	
	/// Convert object to string to make it visible in stack backtraces, devlog etc.
	public function __toString() {
		try {
		return get_class($this) . ' ' . $this->getUid() . ' (' . "\n" .
				($this->getParentPage() instanceof tx_newspaper_Page? 
					$this->getParentPage()->getPageType()->getAttribute('type_name'). '/': 
					'') .
#				$this->getPageZoneType()->getAttribute('type_name') .
				') ';
		} catch (tx_newspaper_Exception $e) { return 'Duh, exception thrown: ' . $e; } 
			   										 
			   
	}
	
	////////////////////////////////////////////////////////////////////////////
	//
	//	interface tx_newspaper_StoredObject
	//
	////////////////////////////////////////////////////////////////////////////

 	
 	/// returns an actual member (-> Extra)
	function getAttribute($attribute) {
		/** For reasons explained in readExtras() the attributes are read in the
		 *  constructor, so we don't read the attributes here 
		 */
		if (!array_key_exists($attribute, $this->attributes)) {
        	throw new tx_newspaper_WrongAttributeException($attribute);
 		}
		
		return $this->attributes[$attribute];
	}

	/// sets a member (-> Extra)
	function setAttribute($attribute, $value) {
		/** For reasons explained in readExtras() the attributes are read in the
		 *  constructor, so we don't read the attributes here 
		 */
		$this->attributes[$attribute] = $value;
	}
	
	/** \todo ensure page zone type is stored correctly
	 *  \todo store Extras placed on $this
	 */
	public function store() {
		
		if ($this->getUid()) {
			/// If the attributes are not yet in memory, now would be a good time to read them 
			if (!$this->attributes) {
				$this->readAttributes($this->getTable(), $this->getUid());
			}			
			
			unset($this->attributes['query']);
			tx_newspaper::updateRows(
				$this->getTable(), 'uid = ' . $this->getUid(), $this->attributes
			);
		} else {
			///	Store a newly created page zone
			$this->attributes['pagezonetype_id'] = $this->pagezonetype->getUid();
			/** \todo If the PID is not set manually, $tce->process_datamap()
			 * 		  fails silently. 
			 */
			$this->attributes['pid'] = tx_newspaper_Sysfolder::getInstance()->getPid($this);

			$this->setUid(
				tx_newspaper::insertRows(
					$this->getTable(), $this->attributes
				)
			);
		}

		/// Ensure the page zone has an entry in the abstract supertable...
		$pagezone_uid = $this->createPageZoneRecord($this->getUid(), $this->getTable());
		
		/// ... and is attached to the correct page
		if ($this->getParentPage() instanceof tx_newspaper_Page) {
			tx_newspaper::updateRows(
				'tx_newspaper_pagezone', 
				'uid = ' . $pagezone_uid, 
				array('page_id' => $this->getParentPage()->getUid())
			);
		}
				
		/// \todo store Extras placed on $this
		if ($this->getExtras()) {
			throw new tx_newspaper_NotYetImplementedException('store Extras placed on $this');
		}
		
		return $this->getUid();
		
	}
	
	/** \todo Internationalization */
	public function getTitle() {
		return 'PageZone';
	}
	
	function getUid() { 
		return intval($this->uid); 
	}
	
	function setUid($uid) { 
		$this->uid = $uid; 
	}

	public  function getTable() {
		return tx_newspaper::getTable($this);
	}

	static function getModuleName() { 
		return 'np_pagezone'; 
	}

	////////////////////////////////////////////////////////////////////////////
	//
	//	interface tx_newspaper_ExtraIface
	//
	////////////////////////////////////////////////////////////////////////////


	/// A short description that makes an Extra uniquely identifiable in the BE
	/** This function should be overridden in every class that can be pooled, to
	 *  provide the BE user a way to find an Extra to create a new Extra from.
	 */
	public function getDescription() {
		//	default implementation
		return $this->getTitle() . ' ' . $this->getUid();
	}
	
	/// Deletes the concrete Extras and all references to it
	public function deleteIncludingReferences() {
		throw new tx_newspaper_NotYetImplementedException();
		/*
		/// Find abstract records linking to the concrete Extra
		$uids = tx_newspaper::selectRows(
			'uid', self::$table, 
			'extra_table = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->getTable(), $this->getTable()) .
			' AND extra_uid = ' . $this->getUid());

		foreach ($uids as $uid) {
			/// Delete entries in association tables linking to abstract record
			tx_newspaper::deleteRows(
				tx_newspaper_Article::getExtra2PagezoneTable(), 
				'uid_foreign = ' . intval($uid['uid'])
			);
			tx_newspaper::deleteRows(
				tx_newspaper_PageZone_Page::getExtra2PagezoneTable(), 
				'uid_foreign = ' . intval($uid['uid'])
			);
			
			/// Delete the abstract record
			tx_newspaper::deleteRows(self::$table, 'uid = ' . intval($uid['uid']));
		}
		
		/// delete the concrete record
		tx_newspaper::deleteRows($this->getTable(), 'uid = ' . $this->getUid());
		*/
	}
	
	/// Lists Extras which are in the pool of master copies for new Extras
	public function getPooledExtras() {
		throw new tx_newspaper_IllegalUsageException('PageZones cannot be pooled.');
	}
	

	/// Render the page zone, containing all extras
	/** \param $template_set the template set used to render this page (as 
	 *  		passed down from tx_newspaper_Page::render() )
	 *	\return The rendered page as HTML (or XML, if you insist) 
	 */
 	public function render($template_set = '') {
		/// Check whether to use a specific template set
		if ($this->getAttribute('template_set')) {
			$template_set = $this->getAttribute('template_set');
		}
		
		/// Configure Smarty rendering engine
		if ($template_set) {
			$this->smarty->setTemplateSet($template_set);
		}
		if ($this->getParentPage() && $this->getParentPage()->getPagetype()) {
			$this->smarty->setPageType($this->getParentPage());
		}
		if ($this->getPageZoneType()) {
			$this->smarty->setPageZoneType($this);
		}

 		/// Pass global attributes to Smarty
 		$this->smarty->assign('class', get_class($this));
 		$this->smarty->assign('attributes', $this->attributes);
 		
		/** Pass the Extras on this page zone, already rendered, to Smarty
		 *  \todo blockweise zusammenfuehren von extras gleiches layout (nicht vor taz launch)
		 */
 		$temp_extras = array();
 		foreach ($this->getExtras() as $extra) {
 			$temp_extras[] = $extra->render($template_set);
 		}
 		$this->smarty->assign('extras', $temp_extras);

 		return $this->smarty->fetch($this);
 	}
	
	static function readExtraItem($uid, $table) {
		throw new tx_newspaper_NotYetImplementedException();
	}
	
	public static function dependsOnArticle() { return false; }	
	
	////////////////////////////////////////////////////////////////////////////
	//
	//	class tx_newspaper_PageZone
	//
	////////////////////////////////////////////////////////////////////////////

	public function getPageZoneType() {
		if (!$this->pagezonetype) {
			$pagezonetype_id = $this->getUid()? $this->getAttribute('pagezonetype_id'): 0;
			$this->pagezonetype = new tx_newspaper_PageZoneType($pagezonetype_id);
		}
		return $this->pagezonetype; 
	}

	/// \return uid of "parent" abstract pagezone record for given pagezone
	public function getAbstractUid() {
		$row = tx_newspaper::selectOneRow(
			'uid',
			'tx_newspaper_pagezone',
			'deleted=0 AND pagezone_uid=' . $this->getUid() . ' AND pagezone_table="' .$this->getTable() . '"'
		);
#t3lib_div::devlog('gau', 'newspaper', 0, array($this->getUid(), $this->getTable(), intval($row['uid'])));	
		return intval($row['uid']);
	}

	public function getParentPage() {
		if (!$this->parent_page) {
			if (!$this->parent_page_id) {
				$pagezone_record = tx_newspaper::selectOneRow(
					'page_id', 'tx_newspaper_pagezone', 
					'pagezone_table = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->getTable(), 'tx_newspaper_pagezone') .
					' AND pagezone_uid = ' .$this->getUid()
				);
				$this->parent_page_id = intval($pagezone_record['page_id']);
			}

			if ($this->parent_page_id) {
				$this->parent_page = new tx_newspaper_page($this->parent_page_id);
			} else {
				// that's ok, articles don't have parent pages
				return null;
			}
		}
		return $this->parent_page;
	}
	
	/// Get the Page Zone from which the current object inherits the placement of its extras
	/** The page zone depends on attribute 'inherit_mode' (defined in pagezone_page
	 *  and article):
	 *  If negative, don't inherit at all; if positive,	inherit from the page 
	 *  identified by the UID given (parameter misnomer ;-) ; if zero, find the 
	 *  page zone in the parent page or higher up in the hierarchy with the same
	 *  page zone type as $this.
	 *   
	 *  \return The PageZone object from which to copy the Extras and their 
	 * 		placements.
	 *  \todo What if inherit_mode points to a non-existent PageZone? Currently
	 * 		a DBException is thrown.
	 *  \todo A recursive version of this function would be more elegant, I reckon
	 */
	public function getParentForPlacement() {
		$inherit_mode = intval($this->getAttribute('inherits_from'));
		
		if ($inherit_mode < 0) return null;
		if ($inherit_mode > 0) return new tx_newspaper_PageZone($inherit_mode);
		
		/// Step from parent to parent until a PageZone with matching type is found
		$current_page = $this->getParentPage();
		while ($current_page) {
			/// First get parent section of the current page...
			$parent_section = $current_page->getParentSection();
			if ($parent_section instanceof tx_newspaper_Section) {
				/// ... then get parent section of the current section
				$parent_section = $parent_section->getParentSection();
			} else {
				//	Root of section tree reached
				return null;
			}
			
			if (!$parent_section instanceof tx_newspaper_Section) {
				//	Root of section tree reached
				return null;
			}
			
			/// find page of same page type under parent section
			$new_page = null;
			foreach ($parent_section->getSubPages() as $page) {
				if ($page->getPageType()->getUid() == $current_page->getPageType()->getUid()) {
					$new_page = $page;
				}
			}

			$current_page = $new_page;
			if (!$new_page) {
				/// If page not active in parent section, look in the section further up
				continue;
			}
		
			/** Look for PageZone of the same type in the Page of the same page
			 *  type in the parent section (phew). If no active PageZone is
			 *  found, continue looking in the parent section.
			 */	
			foreach ($new_page->getActivePageZones() as $parent_pagezone) {
				if ($parent_pagezone->getPageZoneType() == $this->getPageZoneType())
					return $parent_pagezone;
			}
			
		}
		
		return null;
	}
	
	/// Get the hierarchy of Page Zones from which the current Zone inherits the placement of its extras
	/** \param $including_myself If true, add $this to the list
	 *  \param $hierarchy List of already found parents (for recursive calling) 
	 *  \return Inheritance hierarchy of Page Zones from which the current Page 
	 * 		 	Zone inherits, ordered upwards  
	 */
	public function getInheritanceHierarchyUp($including_myself = true, 
											  $hierarchy = array()) {
		if ($including_myself) $hierarchy[] = $this;
		if ($this->getParentForPlacement()) {
			return $this->getParentForPlacement()->getInheritanceHierarchyUp(true, $hierarchy);			
		} else return $hierarchy;
	}
	
	/// Add an extra after the Extra which is on the original page zone as $origin_uid
	/** \param $origin_uid 
	 *  \param $extra The new, fully instantiated Extra to insert
	 *  \param $recursive If set, pass down the insertion to all inheriting PageZones
	 *  \return DOCUMENT_ME
	 */ 
	public function insertExtraAfter(tx_newspaper_Extra $insert_extra,
									 $origin_uid = 0, $recursive = true) {

		$insert_extra->setAttribute('position', $this->getInsertPosition($origin_uid));
		$insert_extra->setAttribute('paragraph', $this->paragraph_for_insert);
		
		/// Write Extra to DB
		$insert_extra->store();
		
		$this->addExtra($insert_extra);
		
		if ($recursive) {
			/// Pass down the insertion to PageZones inheriting from $this
			foreach($this->getInheritanceHierarchyDown(false) as $inheriting_pagezone) {
				$copied_extra = clone $insert_extra;
				$copied_extra->setAttribute('origin_uid', $insert_extra->getOriginUid());
				
				$inheriting_pagezone->insertExtraAfter($copied_extra, $origin_uid, false);
			}
		}
		return $insert_extra;
	}
	
	///	Remove a given Extra from the PageZone
	/** \param $remove_extra Extra to be removed
	 *  \return false if $remove_extra was not found, true otherwise
	 *  \todo DELETE WHERE origin_uid = ...
	 */
	public function removeExtra(tx_newspaper_Extra $remove_extra, $recursive = true) {
	
		if ($recursive) {
			///	Remove Extra on inheriting PageZones first
			foreach($this->getInheritanceHierarchyDown(false) as $inheriting_pagezone) {
				$copied_extra = $inheriting_pagezone->findExtraByOriginUID($remove_extra->getOriginUid());
				if ($copied_extra) $inheriting_pagezone->removeExtra($copied_extra, false);
			}
		}
		
		$index = -1;
		try {
			$index = $this->indexOfExtra($remove_extra);
		} catch (tx_newspaper_InconsistencyException $e) {
			//	Extra not found, nothing to do
			return false;
		}
		unset($this->extras[$index]);
		
		tx_newspaper::deleteRows(
				$this->getExtra2PagezoneTable(),
				'uid_local = ' . $this->getUid() .
				' AND uid_foreign = ' . $remove_extra->getExtraUid()
			);
			
		/// Delete the abstract record
		tx_newspaper::deleteRows(
			tx_newspaper_Extra_Factory::getExtraTable(), 
			array($remove_extra->getExtraUid())
		);
		
		/** If abstract record was the last one linking to the concrete Extra,
		 *  \em and the concrete Extra is not pooled, delete the concrete Extra
		 *  too.
		 */
		try {
			if (!$remove_extra->getAttribute('pool')) {
				$count = tx_newspaper::selectOneRow(
					'COUNT(*) AS num', 
					tx_newspaper_Extra_Factory::getExtraTable(),
					'extra_table = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr(
						$remove_extra->getTable(), $remove_extra->getTable()) .
					' AND extra_uid = ' . $remove_extra->getUid()
				);
				if (!intval($count['num'])) {
					$remove_extra->deleteIncludingReferences();
				}
			}
		} catch (tx_newspaper_WrongAttributeException $e) { }
		
		return true;
	}
	
	/// Move an Extra present on the PageZone after another Extra, defined by its origin UID
	/** \param $move_extra The Extra to be moved
	 *  \param $origin_uid The origin UID of the Extra after which $new_extra
	 * 			will be inserted. If $origin_uid == 0, insert at the beginning.
	 *  \exception tx_newspaper_InconsistencyException If $move_extra is not
	 * 			present on the PageZone
	 */	
	public function moveExtraAfter(tx_newspaper_Extra $move_extra, $origin_uid = 0, $recursive = true) {

		///	Check that $move_extra is really on $this
		$this->indexOfExtra($move_extra);
		
		$move_extra->setAttribute('position', $this->getInsertPosition($origin_uid));

		/// Write Extra to DB
		$move_extra->store();
		
		if ($recursive) {

			///	Move Extra on inheriting PageZones
			foreach($this->getInheritanceHierarchyDown(false) as $inheriting_pagezone) {
				$copied_extra = $inheriting_pagezone->findExtraByOriginUID($move_extra->getOriginUid());
				if ($copied_extra) $inheriting_pagezone->moveExtraAfter($copied_extra, $origin_uid, false);
			}

		}
		
		/** ... and that's it. We don't need to update the association table
		 *  because we asserted that the Extra is already on the PageZone.
		 */
	}

	/// Set whether PageZones down the inheritance hierarchy inherit this Extra
	/** If the inheritance mode is changed to false, the Extra must be removed 
	 *  from all PageZones inheriting from $this (if it's  already present there).
	 *  If it is set to true, it must be copied to all inheriting PageZones. Or,
	 *  if it is already present there (because the inheritance status was
	 *  toggled to false previously), the Extras must be reactivated and placed 
	 *  according to their origin_uid. 
	 * 
	 *  \param $extra The Extra whose inheritance status is changed
	 *  \param $inherits Whether to pass the Extra down the hierarchy
	 *  \exception tx_newspaper_InconsistencyException If $extra is not present
	 * 			on the PageZone
	 */
	public function setInherits(tx_newspaper_Extra $extra, $inherits = true) {

		//	Check if the Extra is really present. An exception is thrown if not.
		$this->indexOfExtra($extra);

		if ($inherits == $extra->getAttribute('is_inheritable')) return;
		t3lib_div::devlog('setInherits()', 'newspaper', 0, intval($inherits));

		$extra->setAttribute('is_inheritable', $inherits);
		$extra->store();
	
		foreach($this->getInheritanceHierarchyDown(false) as $inheriting_pagezone) {
			$copied_extra = $inheriting_pagezone->findExtraByOriginUID($extra->getOriginUid());
			t3lib_div::devlog('$inheriting_pagezone', 'newspaper', 0, array(
					'abstract pagezone uid' => $inheriting_pagezone->getPageZoneUid(),
					'concrete pagezone uid' => $inheriting_pagezone->getUid(),
					'copied extra' => $copied_extra
				)
			);
			if ($copied_extra) {
				if ($inherits == false) {	
					/** Whenever the inheritance hierarchy is invalidated, 
					 *  inherited Extras are hidden and moved to the end. 
					 */
					$copied_extra->setAttribute('position', 
						$inheriting_pagezone->findLastPosition()+self::EXTRA_SPACING);
					$copied_extra->setAttribute('show_extra', 0);
				} else {
					/** Whenever the inheritance hierarchy is restored, 
					 *  inherited Extras are unhidden, but they remain at the
					 *  end of the page zone. 
					 */
					$copied_extra->setAttribute('show_extra', 1);
				}
				$copied_extra->store();
			}
			 
		}
	}
	
	/// Get the hierarchy of Page Zones inheriting placement from $this
	/** \param $including_myself If true, add $this to the list
	 *  \param $hierarchy List of already found parents (for recursive calling) 
	 *  \return Inheritance hierarchy of pages inheriting from the current Page 
	 * 			Zone, ordered downwards, depth-first
	 */
	public function getInheritanceHierarchyDown($including_myself = true, 
												$hierarchy = array()) {

		if ($including_myself) $hierarchy[] = $this;
		
		///  look for inheriting page zones only of the same type as $this
		$table = tx_newspaper::getTable($this);
		$heirs = tx_newspaper::selectRows(
			'uid', $table, 'inherits_from = ' . $this->getUid()
		);
		foreach ($heirs as $heir) {
			$inheriting_pagezone = new $table($heir['uid']);
			$hierarchy = $inheriting_pagezone->getInheritanceHierarchyDown(true, $hierarchy);
		}

		/// look for page zones on pages in section down the section hierarchy
		foreach ($this->getParentPage()->getParentSection()->getChildSections() as $sub_section) {
			$page = $sub_section->getSubPage($this->getParentPage()->getPageType());
			if ($page) {
				$inheriting_pagezone = $page->getPageZone($this->getPageZoneType());
				$hierarchy = $inheriting_pagezone->getInheritanceHierarchyDown(true, $hierarchy);
			}
		}
		return $hierarchy;
	}

	/// As the name says, copies Extras from another PageZone
	/** In particular, it copies the entry from the abstract Extra supertable,
	 *  but not the data from the concrete Extra_* tables. I.e. it creates a
	 *  new Extra which is a reference to a concrete Extra for each copyable
	 *  Extra on the template PageZone.
	 *  Also, it sets the origin_uid property on the copied Extras to reflect
	 *  the origin of the Extra.
	 */
	public function copyExtrasFrom(tx_newspaper_PageZone $parent_zone) {
		foreach ($parent_zone->getExtras() as $extra_to_copy) {
			if (!$extra_to_copy->getAttribute('inheritable')) continue;
			/// Clone $extra_to_copy
			/** Not nice: because we're working on the abstract superclass here, we
			 * 	can't clone the superclass entry because there's no object for it.
			 */
			$new_extra = array();
			foreach (tx_newspaper::getAttributes('tx_newspaper_extra') as $attribute) {
				$new_extra[$attribute] = $extra_to_copy->getAttribute($attribute); 
			} 
			$new_extra['show_extra'] = 1;
			if (!$extra_to_copy->getOriginUid()) {
				$new_extra['origin_uid'] = $extra_to_copy->getAttribute('uid');
			}
			$extra_uid = tx_newspaper::insertRows('tx_newspaper_extra', $new_extra);
			
			$this->addExtra(tx_newspaper_Extra_Factory::getInstance()->create($extra_uid));
		}
	}

	public function getExtraOrigin(tx_newspaper_Extra $extra) {
		if ($extra->isOriginExtra()) return $this;
		
		foreach ($this->getInheritanceHierarchyUp(false) as $origin_pagezone) {
			foreach ($origin_pagezone->getExtras() as $potential_origin_extra) {
				if ($potential_origin_extra->getExtraUid() == $extra->getOriginUid()) {
					return $origin_pagezone;
				}
			}
		}
	}

	public function getExtraOriginAsString(tx_newspaper_Extra $extra) {
		$original_pagezone = $this->getExtraOrigin($extra);
		if (!$original_pagezone) return '---';
		if ($original_pagezone->getUid() == $this->getUid()) return '---';
		$page = $original_pagezone->getParentPage();
		$section = $page->getParentSection();
		if (!$section instanceof tx_newspaper_Section) return '< error >';
		if ($section->getUid() == $this->getParentPage()->getParentSection()->getUid()) {
			return $page->getPageType()->getAttribute('type_name');
		}
		return $section->getAttribute('section_name');
	}
	
	////////////////////////////////////////////////////////////////////////////
	//
	//	internal functions (public only to enable unit testing)
	//
	////////////////////////////////////////////////////////////////////////////
 	
 	/// Create the record for a concrete PageZone in the table of abstract PageZones
	/** This is probably necessary because a concrete PageZone has been freshly
	 *  created.
	 * 
	 *  Does nothing if the concrete PageZone is already linked in the abstract table. 
	 * 
	 *  \param $uid UID of the PageZone in the table of concrete PageZone
	 *  \param $table Table of concrete PageZone
	 *  \return UID of abstract PageZone record
	 */ 
	public function createPageZoneRecord() {
		/// Check if record is already present in page zone table
		$row = tx_newspaper::selectZeroOrOneRows(
			'uid', 'tx_newspaper_pagezone', 
			'pagezone_table = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->getTable(), $this->getTable()) .
			' AND pagezone_uid = ' . $this->getUid()	
		);
		if ($row['uid']) return $row['uid'];
		
		/// read typo3 fields to copy into page zone table
		$row = tx_newspaper::selectOneRow(
			implode(', ', self::$fields_to_copy_into_pagezone_table),
			$this->getTable(),
			'uid = ' . $this->getUid()
		);
		
		/// write the uid and table into page zone table, with the values read above
		$row['pagezone_uid'] = $this->getUid();
		$row['pagezone_table'] = $this->getTable();
		$row['tstamp'] = time();				///< tstamp is set to now

		return tx_newspaper::insertRows('tx_newspaper_pagezone', $row);		
	}

	public function setPageZoneType(tx_newspaper_PageZoneType $type) {
		$this->pagezonetype = $type;
	}

	public function setParentPage(tx_newspaper_Page $parent) {
		$this->parent_page = $parent;
		$this->parent_page_id = $parent->getUid();
	}

	////////////////////////////////////////////////////////////////////////////
	//
	//	protected functions
	//
	////////////////////////////////////////////////////////////////////////////

	/// Find next free position after the Extra whose origin_uid attribute matches $origin_uid
	/** Side effect: finds the paragraph of the Extra matching \p $origin_uid
	 *  and stores it in \p $this->paragraph_for_insert
	 * 
	 *  \param $origin_uid The origin_uid of the Extra after which a free place
	 *  				   is wanted
	 *  \return A position value which is halway between the found Extra and the
	 * 			next Extra
	 */
	protected function getInsertPosition($origin_uid) {
		if ($origin_uid) {
			/** Find the Extra to insert after. If it is not deleted on this page,
			 *  it is the Extra whose attribute 'origin_uid' equals $origin_uid.
			 */ 
			$extra_after_which = null;
			foreach ($this->getExtras() as $extra) {
				if ($extra->getOriginUid() == $origin_uid) {
					$extra_after_which = $extra;
					break;
				}
			}
			if (!($extra_after_which instanceof tx_newspaper_Extra)) {
				/** Deduce the $extra_after_which from the parent page(s)
				 *  \see http://segfault.hal.taz.de/mediawiki/index.php/Vererbung_Bestueckung_Seitenbereiche_(DEV)#Beispiel_-_.C3.84nderung_Ebene_1.2C_aber_Referenzelement_wird_nicht_vererbt
				 */
				$parent = $this->getParentForPlacement();
				if (!$parent instanceof tx_newspaper_PageZone) {
					throw new tx_newspaper_IllegalUsageException(
						'Tried to insert an Extra with an origin uid which is neither in the current' .
						' PageZone nor in any of the parents.'
						);
				} else {
					return $parent->getInsertPosition($origin_uid);
				}	
			}
			$position = $extra_after_which->getAttribute('position');
			$this->paragraph_for_insert = intval($extra_after_which->getAttribute('paragraph'));
		} else {
			$position = 0;
		}
		/// Find Extra before which to insert the new Extra
		$position_before_which = 0;
		foreach ($this->getExtras() as $extra) {
			/// \todo If $this is an article, handle paragraphs
			if ($extra->getAttribute('position') > $position &&
					(!$position_before_which ||
					 $position_before_which > $extra->getAttribute('position')
					)
				) {
				$position_before_which = $extra->getAttribute('position');
				break;
			} 
		}
		if (!$position_before_which) $position_before_which = 2*($position? $position: self::EXTRA_SPACING);
		
		if ($position_before_which-$position < 2) {
			/// Increase 'position' attribute for all extras after $extra_after_which 
			foreach ($this->getExtras() as $extra_to_rearrange) {
				if ($extra_to_rearrange->getAttribute('position') <= $position) continue;
				$extra_to_rearrange->setAttribute('position', $extra_to_rearrange->getAttribute('position')+self::EXTRA_SPACING);
				$extra_to_rearrange->store();
			}
			$position_before_which += self::EXTRA_SPACING;
		}

		/// Place Extra to insert between $extra_after and $extra_before (or at end)
		return $position+($position_before_which-$position)/2;
	}

	/// Binary search for an Extra, assuming that $this->extras is ordered by position
	/** This method must be overridden in the Article class because in articles
	 *  Extras are ordered by paragraph first, position second
	 */
	protected function indexOfExtra(tx_newspaper_Extra $extra) {
        $high = sizeof($this->getExtras())-1;
        $low = 0;
       
        while ($high >= $low) {
            $index_to_check = floor(($high+$low)/2);
            $comparison = $this->getExtra($index_to_check)->getAttribute('position') -
            			  $extra->getAttribute('position');
            if ($comparison < 0) $low = $index_to_check+1;
            elseif ($comparison > 0) $high = $index_to_check-1;
            else return $index_to_check;
        }
		
		// Loop ended without a match
		throw new tx_newspaper_InconsistencyException('Extra ' . $extra->getUid() .
													  ' not found in array of Extras!');		
	}
	
	///	Given a origin_uid, find the Extra which has this value for origin_uid
	final protected function findExtraByOriginUID($origin_uid) {
		foreach ($this->getExtras() as $extra) {
			if ($extra->getAttribute('origin_uid') == $origin_uid) return $extra;
		}
		return null;
	}

	/// \return The position value of the last Extra on the PageZone
	protected function findLastPosition() {
		return $this->getExtra(sizeof($this->getExtras())-1)->getAttribute('position');
	}
	
	/// Read Extras from DB
	/** Objective: Read tx_newspaper_Extra array and attributes from the base  
	 *  class c'tor instead of every descendant to minimize code duplication.
	 * 
	 *  Problem: The descendant c'tor calls <tt>parent::__construct()</tt>. The
	 *  base c'tor knows only its own class, not the concrete class which is 
	 *  intantiated. Every function call in the base c'tor therefore calls 
	 *  functions in the base class. Late binding is impossible.
	 * 
	 *  Solution: Factor out the methods to read Extras and attributes in the 
	 *  base class, and call them in the descended c'tor like this:
	 *  \code
	 * 	parent::__construct();
	 *  $this->readExtras($uid);
	 *  $this->readAttributes($this->getTable(), $uid);
	 *  \endcode
	 * 
	 *  \todo factor out code to read MM table and create Extras
	 * 
	 *  \param $uid UID in the table of the abstract PageZone type 
	 */
 	protected function readExtras($uid) {
		$uids = tx_newspaper::selectRows(
			'uid_foreign', $this->getExtra2PagezoneTable(), "uid_local = $uid", '', '', '', false
		);

		if ($uids) {
        	foreach ($uids as $uid) {
        		try {
					//  assembling the query manually here cuz we want to ignore enable fields
					$query = $GLOBALS['TYPO3_DB']->SELECTquery(
						'deleted', 
						tx_newspaper_Extra_Factory::getExtraTable(),
						'uid = ' . $uid['uid_foreign']);
					$res = $GLOBALS['TYPO3_DB']->sql_query($query);
			
			        $deleted = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
					if (!$deleted['deleted']) {
	
	        			$extra = tx_newspaper_Extra_Factory::getInstance()->create($uid['uid_foreign']);
		        		$this->extras[] = $extra;
					} else {
						/// \todo remove association table entry
#						t3lib_div::debug('Extra ' . $uid['uid_foreign'] . ': deleted!');
					}
        		} catch (tx_newspaper_EmptyResultException $e) {
        			/// \todo remove association table entry
					t3lib_div::debug('Extra ' . $uid['uid_foreign'] . ': EmptyResult!');
        		}
        	}
		} 
		# else t3lib_div::debug("readExtras($uid): Empty result for " . tx_newspaper::$query);
 	}
 	
 	/// Ordering function to keep Extras in the order in which they appear on the PageZone
 	/** Supplied as parameter to usort() in getExtras().
 	 *  \param $extra1 first Extra to compare
 	 *  \param $extra2 second Extra to compare
 	 *  \return < 0 if $extra1 comes before $extra2, > 0 if it comes after, 
 	 * 			== 0 if their position is the same 
 	 */
 	static protected function compareExtras(tx_newspaper_ExtraIface $extra1, 
 									 		tx_newspaper_ExtraIface $extra2) {
 		return $extra1->getAttribute('position')-$extra2->getAttribute('position');			 	
	}
	
	/// Read Attributes from DB
	/** \see readExtras()
	 * 
	 *  \param $table Table which stores the concrete object 
	 *  \param $uid UID in the table of the concrete type 
	 */
 	protected function readAttributes($table, $uid) {
		/// Read Attributes from persistent storage
 		$this->attributes = tx_newspaper::selectOneRow('*', $table, 'uid = ' . $uid);
 		$this->attributes['query'] = tx_newspaper::$query;
 	}
 	
 	/// Returns the table which links Extras to this type of page zone
 	/** This function is needed, and non-static, because late static binding
 	 *  does not work too well with PHP (at least prior to 5.3, which introduced
 	 *  the static:: storage type - but this is not yet distributed widely 
 	 *  enough).
 	 *  \return self::$extra_2_pagezone_table
 	 */
 	abstract public function getExtra2PagezoneTable();
 	
 	/// Retrieve the array of Extras on the PageZone, sorted by position
	public function getExtras() {
		if (!$this->extras) {
			$this->readExtras($this->getPagezoneUid());
		}

		usort($this->extras, array(get_class($this), 'compareExtras')); 
		return $this->extras; 
	}

	///	Retrieve a single Extra, defined by its index in the sequence
	/** \param $index 
	 *  \return The \p $index -th Extra on the PageZone 
	 */
	public function getExtra($index) {
		$extras = $this->getExtras();
		return $extras[$index];
	}

	/// Add an Extra to the PageZone, both in RAM and persistently
	public function addExtra(tx_newspaper_Extra $extra) {
/*		$found = false;
		foreach ($this->getExtras() as $present_extra) {
			if ($extra->getExtraUid() == $present_extra->getExtraUid()) {
				$found = true;
				break;
			}
		}
		if (!$found) 
*/		$this->extras[] = $extra;

		try {
			tx_newspaper::selectOneRow(
				'uid_local',
				$this->getExtra2PagezoneTable(),
				'uid_local = ' . $this->getUid() . 
				' AND uid_foreign = ' . $extra->getExtraUid()
			);
#t3lib_div::debug(tx_newspaper::$query);

		} catch (tx_newspaper_EmptyResultException $e) {
			tx_newspaper::insertRows(
				$this->getExtra2PagezoneTable(),
				array(
					'uid_local' => $this->getUid(),
					'uid_foreign' => $extra->getExtraUid()
				)
			);
#t3lib_div::debug(tx_newspaper::$query);
		}
	}
	
	public function getPageZoneUid() { return $this->pagezone_uid; }
	
	public function getExtraUid() { return $this->extra_uid; }

	public function setExtraUid($uid) { 
		$this->extra_uid = intval($uid);
		if ($this->extra_attributes) 
			$this->extra_attributes['uid'] = intval($uid); 
	}

 	protected $uid = 0;				///< The UID of the record in the concrete table
 	protected $pagezone_uid = 0;	///< The UID of the record in the abstract PageZone table
 	protected $extra_uid = 0;		///< The UID of the record in the abstract Extra table
 	
 	protected $smarty = null;		///< Smarty object for rendering
 	
 	protected $attributes = array();	///< array of attributes
 	protected $extras = array();		///< array of tx_newspaper_Extra s
 	protected $pagezonetype = null;
 	
 	protected $parent_page_id = 0;	///< UID of the parent Page
 	protected $parent_page = null;	///< Parent Page object

 	/// Default Smarty template for HTML rendering
 	static protected $defaultTemplate = 'tx_newspaper_pagezone.tmpl';

	/// Temporary variable to store the paragraph of Extras after which a new Extra is inserted
	private $paragraph_for_insert = 0; 	

 	private static $fields_to_copy_into_pagezone_table = array(
		'pid', 'crdate', 'cruser_id', 'deleted', 
	);
 	
}
 
?>