<?php
/**
 *  \file class.tx_newspaper_pagezone.php
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
			$ret = get_class($this) . ' ' . $this->getUid() . ' (' . "\n";
			
			$page = $this->getParentPage();
			if ($page instanceof tx_newspaper_Page) {
				$section = $page->getParentSection();
				if ($section instanceof tx_newspaper_Section) {
					$ret .= $section->getAttribute('section_name') . '/';
				}
				$ret .= $page->getPageType()->getAttribute('type_name'). '/';
			}
			$ret .= $this->getPageZoneType()->getAttribute('type_name') .
				') ';
			
			return $ret;
		} catch (tx_newspaper_Exception $e) { 
			return $ret . '... oops, exception thrown: ' . $e; 
		}
			   
	}
	

	////////////////////////////////////////////////////////////////////////////
	//
	//	interface tx_newspaper_StoredObject
	//
	////////////////////////////////////////////////////////////////////////////

 	
	
	function getAttribute($attribute) {
		/** For reasons explained in readExtras() the attributes are read in the
		 *  constructor, so we don't read the attributes here 
		 */
		if (array_key_exists($attribute, $this->attributes)) {
			return $this->attributes[$attribute];
		}
		if (!$this->pagezone_attributes) {
			$this->pagezone_attributes = tx_newspaper::selectOneRow(
				'*', 'tx_newspaper_pagezone', 
				'pagezone_table = \'' . $this->getTable() . '\' AND pagezone_uid = ' . $this->getUid()
			);
		}

 		if (array_key_exists($attribute, $this->pagezone_attributes)) {
			return $this->pagezone_attributes[$attribute];
 		}
		throw new tx_newspaper_WrongAttributeException($attribute);
	}

	
	function setAttribute($attribute, $value) {
		/** For reasons explained in readExtras() the attributes are read in the
		 *  constructor, so we don't read the attributes here 
		 */
		$this->attributes[$attribute] = $value;
	}
	
	
	public function store() {
		/** \todo ensure page zone type is stored correctly
		 *  \todo store Extras placed on $this
		 */
		
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
			unset($this->attributes['query']);

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
			foreach ($this->extras as $extra) {
				t3lib_div::devlog('extra on pagezone', 'newspaper', 0, $extra);
#				$extra_uid = $extra->store();
#				$extra_table = $extra->getTable();
#				$this->relateExtra2Article($extra);
			}
#			throw new tx_newspaper_NotYetImplementedException('store Extras placed on $this');
		}
		
		return $this->getUid();
		
	}
	
	
	public function getTitle() {
		return tx_newspaper::getTranslation('title_' . $this->getTable());
	
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
\todo: Oliver: I found this in my code, wrote it in September, so I have to have a deep look into this ...
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
 		
        tx_newspaper::startExecutionTimer();
 		
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
 		$this->smarty->assign('normalized_name', $this->getPageZoneType()->getAttribute('normalized_name'));
 		
		/** Pass the Extras on this page zone, already rendered, to Smarty
		 *  \todo blockweise zusammenfuehren von extras gleiches layout (nicht vor taz launch)
		 */
 		$temp_extras = array();
 		foreach ($this->getExtras() as $extra) {
 			$temp_extras[] = $extra->render($template_set);
 		}
 		$this->smarty->assign('extras', $temp_extras);

        $rendered = $this->smarty->fetch($this);
        
        tx_newspaper::logExecutionTime();
        
        return $rendered;
 	}
	

 	/// \todo: oliver: deprecated? probably yes
	static function readExtraItem($uid, $table) {
		throw new tx_newspaper_NotYetImplementedException();
	}
	
	
	public static function dependsOnArticle() { return false; }	
	
	////////////////////////////////////////////////////////////////////////////
	//
	//	class tx_newspaper_PageZone
	//
	////////////////////////////////////////////////////////////////////////////

	///	The tx_newspaper_PageZoneType of the current PageZone.
	/** \return The tx_newspaper_PageZoneType of \c $this.
	 */
	public function getPageZoneType() {
		if (!$this->pagezonetype) {
			$pagezonetype_id = $this->getUid()? $this->getAttribute('pagezonetype_id'): 0;
			$this->pagezonetype = new tx_newspaper_PageZoneType($pagezonetype_id);
		}
		return $this->pagezonetype; 
	}

	
	/// Get the UID of the abstract record for this PageZone.
	/** \return UID of the record containing the data for the abstract portion
	 *  	of the given pagezone - the one from the table
	 *  	\c tx_newspaper_pagezone.
	 */
	public function getAbstractUid() {
		$row = tx_newspaper::selectOneRow(
			'uid',
			'tx_newspaper_pagezone',
			'deleted=0 AND pagezone_uid=' . $this->getUid() . ' AND pagezone_table="' .$this->getTable() . '"'
		);
#t3lib_div::devlog('gau', 'newspaper', 0, array($this->getUid(), $this->getTable(), intval($row['uid'])));	
		return intval($row['uid']);
	}

	
	///	Get the tx_newspaper_Page on which the PageZone lies.
	/** \return The tx_newspaper_Page on which the PageZone lies.
	 */
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

	
	///	Get a list of Page Zones to which the inheritance of \p $this can change.
	/** The parent, from which the current Page Zone inherits its Extras, can be
	 *  altered. This function lists the Zones it can be altered to:
	 *  - The PageZone of the same type in the tx_newspaper_Section which is the
	 *    parent of the current Section (this is the default)
	 *  - Any PageZone of the same tx_newspaper_PageZoneType as \c $this which
	 *    lies under a tx_newspaper_Page in the same tx_newspaper_Section as
	 *    \c $this. (Expect for page zone $this)
	 *
	 * 	\param $sistersOnly Return sister pagezones only, ignore parent page zone
	 * 	  
	 *  \return List of Page Zones to which the inheritance of \p $this can
	 *  	change.
	 */
	public function getPossibleParents($sistersOnly=false) {

		$zones = array();

		if (!$sistersOnly) {
			$parent_zone = $this->getParentForPlacement(true);
			if ($parent_zone) $zones[] = $parent_zone;
		}
		
		$sister_pages = $this->getParentPage()->getParentSection()->getActivePages();
		foreach ($sister_pages as $page) {
			if ($sister_zone = $page->getPageZone($this->getPageZoneType())) {
				if ($sister_zone->getParentPage()->getPageType() != $this->getParentPage()->getPageType()) {
					$zones[] = $sister_zone;
				}
			}
		}
		
		return $zones;	
	}
	
	
	/// The Page Zone from which \c $this inherits the placement of its Extras
	/** The page zone depends on attribute \c 'inherit_mode' (defined in 
	 *  pagezone_page and article):
	 * 
	 *  If negative, don't inherit at all; if positive,	inherit from the page 
	 *  identified by the UID given (parameter misnomer ;-) ; if zero, find the 
	 *  page zone in the parent page or higher up in the hierarchy with the same
	 *  page zone type as \c $this.
	 *   
	 *  \param $structure_only Ignore the value of \c 'inherit_mode', base the
	 * 		return value only on the structure of the tx_newspaper_Section tree.
	 * 
	 *  \return The tx_newspaper_PageZone object from which to copy the
	 *  	tx_newspaper_Extra s and their placements.
	 * 
	 *  \todo What if inherit_mode points to a non-existent PageZone? Currently
	 * 		a DBException is thrown.
	 *  \todo A recursive version of this function would be more elegant, I reckon.
	 */
	public function getParentForPlacement($structure_only = false) {

		if (!$structure_only) { 
			$inherit_mode = intval($this->getAttribute('inherits_from'));
	
			if ($inherit_mode < 0) return null;
			if ($inherit_mode > 0) 
				return tx_newspaper_PageZone_Factory::getInstance()->create($inherit_mode);
		}

		return $this->getParentPageZoneOfSameType();
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
	/** \param $insert_extra The new, fully instantiated Extra to insert
	 *  \param $origin_uid UID of \p $insert_extra on the PageZone where it was
	 * 		originally added. 
	 *  \param $recursive If set, pass down the insertion to all inheriting
	 *  	PageZones.
	 *  \return \p $insert_extra
	 */ 
	public function insertExtraAfter(tx_newspaper_Extra $insert_extra,
									 $origin_uid = 0, $recursive = true) {
		
		/** \todo: it should be possible to set the paragraph BEFORE calling
		 *   	this function. otherwise a workaround is needed: insert extra to
		 * 		article and call changeExtraArticle() on the article afterwards
		 */
		$insert_extra->setAttribute('position', $this->getInsertPosition($origin_uid));
		$insert_extra->setAttribute('paragraph', $this->paragraph_for_insert);
		
		/** Write Extra to DB	*/
		$insert_extra->store();
		
		$this->addExtra($insert_extra);
		
		if ($recursive) {
			/// Pass down the insertion to PageZones inheriting from $this
			foreach($this->getInheritanceHierarchyDown(false) as $inheriting_pagezone) {
				$copied_extra = clone $insert_extra;
				$copied_extra->setAttribute('origin_uid', $insert_extra->getOriginUid());
				
				$inheriting_pagezone->insertExtraAfter($copied_extra, $origin_uid /* $insert_extra->getOriginUid() */, false);		
			}
		}
		return $insert_extra;
	}
	
	
	///	Remove a given Extra from the PageZone
	/** \param $remove_extra Extra to be removed
	 *  \param $recursive if true, remove \p $remove_extra on inheriting page zones
	 *  \return false if $remove_extra was not found, true otherwise
	 *  \todo DELETE WHERE origin_uid = ...
	 */
	public function removeExtra(tx_newspaper_Extra $remove_extra, $recursive = true) {

        tx_newspaper::devlog("removeExtra($remove_extra, $recursive)");
        
		if ($recursive) {
			///	Remove Extra on inheriting PageZones first
			foreach($this->getInheritanceHierarchyDown(false) as $inheriting_pagezone) {
                tx_newspaper::devlog("inheriting pagezone: $inheriting_pagezone");
				$copied_extra = $inheriting_pagezone->findExtraByOriginUID($remove_extra->getOriginUid(), true);
				if ($copied_extra) $inheriting_pagezone->removeExtra($copied_extra, false);
			}
		}
		tx_newspaper::devlog("finished removing on inheriting page zones");

		$index = -1;
		try {
			$index = $this->indexOfExtra($remove_extra);
		} catch (tx_newspaper_InconsistencyException $e) {
			//	Extra not found, nothing to do
			return false;
		}
        tx_newspaper::devlog("index: $index");
		unset($this->extras[$index]);
		
		tx_newspaper::deleteRows(
				$this->getExtra2PagezoneTable(),
				'uid_local = ' . $this->getUid() .
				' AND uid_foreign = ' . $remove_extra->getExtraUid()
			);
        tx_newspaper::devlog("deleted mm entry");

		/// Delete the abstract record
		tx_newspaper::deleteRows(
			tx_newspaper_Extra_Factory::getExtraTable(), 
			array($remove_extra->getExtraUid())
		);
        tx_newspaper::devlog("deleted abstract record");

		/** If abstract record was the last one linking to the concrete Extra,
		 *  \em and the concrete Extra is not pooled, delete the concrete Extra
		 *  too.
		 */
		try {
			if (!$remove_extra->getAttribute('pool')) {
                tx_newspaper::devlog("delete concrete record...");
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
                tx_newspaper::devlog("...done");

			}
		} catch (tx_newspaper_WrongAttributeException $e) { }
		
		return true;
	}
	
	
	/// Move an Extra present on the PageZone after another Extra, defined by its origin UID
	/** \param $move_extra The Extra to be moved
	 *  \param $origin_uid The origin UID of the Extra after which $new_extra
	 * 			will be inserted. If $origin_uid == 0, insert at the beginning.
	 *  \param $recursive if true, move \p $move_extra after Extra with origin
	 *  		UID \p $origin_uid on inheriting page zones
	 *  \exception tx_newspaper_InconsistencyException If $move_extra is not
	 * 			present on the PageZone
	 */	
	public function moveExtraAfter(tx_newspaper_Extra $move_extra, $origin_uid = 0, $recursive = true) {

        try {
            ///	Check that $move_extra is really on $this
            $this->indexOfExtra($move_extra);
        } catch (tx_newspaper_InconsistencyException $e) {
            throw new tx_newspaper_InconsistencyException($e->getMessage(), true);    
        }

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

		$extra->setAttribute('is_inheritable', $inherits);
		$extra->store();
		
		foreach($this->getInheritanceHierarchyDown(false) as $inheriting_pagezone) {
			$copied_extra = $inheriting_pagezone->findExtraByOriginUID($extra->getOriginUid(), true);
			
			if ($copied_extra) {
				if ($inherits == false) {	
					/** Whenever the inheritance hierarchy is invalidated, 
					 *  inherited Extras are hidden and moved to the end. 
					 */
					$copied_extra->setAttribute('gui_hidden', 1);
				} else {
					/** Whenever the inheritance hierarchy is restored, 
					 *  inherited Extras are unhidden, but they remain at the
					 *  end of the page zone. 
					 */
					$copied_extra->setAttribute('gui_hidden', 0);
				}
				$copied_extra->store();
				
			}
			else {
				///	\todo What's going on here?
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

        tx_newspaper::devlog("getInheritanceHierarchyDown($including_myself): recursion " . self::$recursion_level);
        self::$recursion_level++;
        if (self::$recursion_level > 20) return;
		if ($including_myself) $hierarchy[] = $this;
		
		$hierarchy = array_merge($hierarchy, $this->getExplicitlyInheritingPagezoneHierarchy());
        tx_newspaper::devlog("explicitly inheriting pagezone hierarchy added");

		if (!$this->getParentPage()) return $hierarchy;

		/// look for page zones on pages in section down the section hierarchy
		$sub_pages = $this->getParentPage()->getSubPagesOfSameType();
        tx_newspaper::devlog("sub pages", $sub_pages);

		foreach ($sub_pages as $sub_page) {
            tx_newspaper::devlog("sub page: $sub_page");
			$inheriting_pagezone = $sub_page->getPageZone($this->getPageZoneType());
            tx_newspaper::devlog("inheriting page zone: $inheriting_pagezone");

			if ($inheriting_pagezone instanceof tx_newspaper_PageZone) {
				$hierarchy = $inheriting_pagezone->getInheritanceHierarchyDown(true, $hierarchy);
			}
		}
		return $hierarchy;
	}
    private static $recursion_level = 0;

	/// Reads page zones which have been explicitly set to inherit from \c $this.
	private function getExplicitlyInheritingPagezoneHierarchy() {
		tx_newspaper::devlog("getExplicitlyInheritingPagezoneHierarchy()");
		$hierarchy = array();
		
		$table = tx_newspaper::getTable($this);
		$heirs = tx_newspaper::selectRows(
			'uid', $table, 'inherits_from = ' . $this->getUid()
		);
        tx_newspaper::devlog("heirs", $heirs);

		foreach ($heirs as $heir) {
            if (intval($heir['uid']) == $this->getUid()) continue;
			$inheriting_pagezone = new $table($heir['uid']);
			$hierarchy = $inheriting_pagezone->getInheritanceHierarchyDown(true, $hierarchy);
		}
        tx_newspaper::devlog("hierarchy", $hierarchy);

		return $hierarchy;
	}


	/// As the name says, copies Extras from another PageZone
	/** In particular, it copies the entry from the abstract Extra supertable,
	 *  but not the data from the concrete Extra_* tables. I.e. it creates a
	 *  new Extra which is a reference to a concrete Extra for each copyable
	 *  Extra on the template PageZone.
	 *  Also, it sets the origin_uid property on the copied Extras to reflect
	 *  the origin of the Extra.
	 * 
	 *  \param $parent_zone Page Zone from which the Extras are copied.
	 */
	public function copyExtrasFrom(tx_newspaper_PageZone $parent_zone) {
		foreach ($parent_zone->getExtras() as $extra_to_copy) {
			if (!$extra_to_copy->getAttribute('is_inheritable')) continue;
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


	/// Change parent Page Zone
	/** - Hide Extras placed on this Page Zone
	 *  - Copy Extras from new parent
	 *  \param $parent_uid UID of parent Page Zone, or if 0, inherit from next
	 *      page zone above, or if < 0, don't inherit at all
 	 */
	public function changeParent($new_parent_uid) {

        tx_newspaper::devlog("changeParent($new_parent_uid)");
        $this->removeInheritedExtras();
        $this->hideOriginExtras();
        tx_newspaper::devlog("extras removed");

        $parent_zone = $this->getParentZone($new_parent_uid);
        tx_newspaper::devlog("parent zone", $parent_zone);

		if ($parent_zone) $this->inheritExtrasFrom($parent_zone);
        tx_newspaper::devlog("extras copied");

        $this->storeWithNewParent($new_parent_uid);
	}

    private function removeInheritedExtras() {
        foreach ($this->getExtras() as $extra) {
            if (!$extra->isOriginExtra()) {
                tx_newspaper::devlog("remove extra", $extra);
                /// Delete Extra, also on sub-PageZones
                $this->removeExtra($extra, true);
            }
            tx_newspaper::devlog("...done");
        }

    }

    private function hideOriginExtras() {
        foreach ($this->getExtras() as $extra) {
            if ($extra->isOriginExtra()) {
                tx_newspaper::devlog("hide origin extra", $extra);
                /// Hide and move to end of page zone
                $extra->setAttribute('show_extra', 0);
                $extra->store();
            }
            tx_newspaper::devlog("...done");
        }
    }

    private function getParentZone($new_parent_uid) {
        $parent_uid = intval($new_parent_uid);

        if ($parent_uid < 0) {
            return null;
        } else if ($parent_uid == 0) {
            return $this->getParentPageZoneOfSameType();
        } else {
            return tx_newspaper_PageZone_Factory::getInstance()->create($parent_uid);
        }

    }

    private function inheritExtrasFrom(tx_newspaper_PageZone $parent_zone) {
        // temporary result of refactoring
        $this->copyExtrasFrom($parent_zone);
    }

    private function storeWithNewParent($new_parent_uid) {
        $this->setAttribute('inherits_from', intval($new_parent_uid));
        $this->setAttribute('tstamp', time());
        $this->store();
        tx_newspaper::devlog("stored");
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

	/// Return the section \p $extra was inserted in string format
	/** \todo Make the '---' and '< error >' messages class constants instead of
	 *  	hardcoding them. 
	 */
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
	

	/// returns true if pagezone is an article
	public function isArticle() {
		return $this instanceof tx_newspaper_Article;
	}


	/// returns true if pagezone is a pagezone_page
	public function isPagezonePage() {
		return $this instanceof tx_newspaper_PageZone_Page;
	}


	/// returns true if pagezone is a default article
	public function isDefaultArticle() {
		if ($this->isPagezonePage()) {
			return false;
		}
		return ($this->getAttribute('is_template') == 1);
	}


	/// returns true if pagezone is a concrete article
	public function isConcreteArticle() {
		if ($this->isPagezonePage()) {
			return false;
		}
		return ($this->getAttribute('is_template') == 0);
	}
		
	/// delete this concrete and the parent abstract pagezone
	public function delete() {
		// delete concrete pagezone delete abstract record first
		$this->setAttribute('deleted', 1); 
		$this->store();
		// delete abstract record then
		tx_newspaper::updateRows(
			'tx_newspaper_pagezone',
			'uid=' . $this->getAbstractUid(),
			array('deleted' => 1)
		);
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

		$uid = tx_newspaper::insertRows('tx_newspaper_pagezone', $row);
		
		return $uid;		
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
				/** Deduce the $extra_after_which from the parent page(s): 
				 *  http://segfault.hal.taz.de/mediawiki/index.php/Vererbung_Bestueckung_Seitenbereiche_(DEV)
				 *  (2.3.1.3 Beispiel - Aenderung Ebene 1, aber Referenzelement wird nicht vererbt)
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
			foreach ($this->getExtras() as $extra) {
				$extra->setAttribute('position', $extra->getAttribute('position')+self::EXTRA_SPACING);
				$extra->store();
			}
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
	

	///	Given a origin uid, find the Extra which has this value for \p origin_uid
	/** \param $origin_uid The origin uid of the extra to be found
	 *  \param $hidden_too Whether to search in GUI-hidden extras as well
	 */
	final protected function findExtraByOriginUID($origin_uid, $hidden_too = false) {
if(0)        t3lib_div::devlog('findExtraByOriginUID()', 'newspaper', 0, array(
	        'pagezone_uid' => $this->getUid(),
	        '$origin_uid' => $origin_uid, 
	        '$hidden_too' => intval($hidden_too),
            'getExtras()' => $this->getExtras($hidden_too))
        );
        
		foreach ($this->getExtras($hidden_too) as $extra) {
			if ($extra->getAttribute('origin_uid') == $origin_uid) return $extra;
		}
		return null;
	}


	/// \return The position value of the last Extra on the PageZone
	protected function findLastPosition() {
		return $this->getExtra(sizeof($this->getExtras())-1)->getAttribute('position');
	}


	/** Step from parent to parent until a PageZone with matching type is
	 *  found.
	 */
	protected function getParentPageZoneOfSameType() {
		$current_page = $this->getParentPage();
		while ($current_page) {
			
			$current_page = $current_page->getParentPageOfSameType();		
			if (!$current_page instanceof tx_newspaper_Page) continue;
			
			/** Look for PageZone of the same type. If no active PageZone is
			 *  found, continue looking in the parent section.
			 */	
			foreach ($current_page->getActivePageZones() as $parent_pagezone) {
				if ($parent_pagezone->getPageZoneType() == $this->getPageZoneType())
					return $parent_pagezone;
			}
			
		}
		
		return null;
		
	}
	

	/// Retrieve the array of Extras on the PageZone, sorted by position
    /** \param $hidden_too Also get Extras that are hidden because their
     *        inheritance mode has been set to false
     */
    public function getExtras($hidden_too = false) {
    	if (!$this->extras || $hidden_too) {
            $this->readExtras($this->getUid(), $hidden_too);
        }

        usort($this->extras, array(get_class($this), 'compareExtras')); 
        return $this->extras; 
    }

    public function getExtrasOf($extra_class) {

        if ($extra_class instanceof tx_newspaper_Extra) {
            $extra_class = tx_newspaper::getTable($extra_class);
        }

        $extras = array();

        if ($this->extras && false) { // use the cached array of extras
            foreach ($this->getExtras() as $extra) {
                if (tx_newspaper::getTable($extra) == strtolower($extra_class)) {
                    $extras[] = $extra;
                }
            }
        } else {
            $records = tx_newspaper::selectRows(
                'DISTINCT uid_foreign', 
                $this->getExtra2PagezoneTable(), 
                'uid_local = ' . $this->getUid(), 
                '', '', '', false
            );
            if (empty($records)) return $extras;
            
            $uids = array(0);
            foreach ($records as $record) {
                $uids[] = $record['uid_foreign'];
            }
            
            $uids = tx_newspaper::selectRows(
                'uid', tx_newspaper_Extra_Factory::getExtraTable(),
                'uid IN (' . implode(', ', $uids) . ') AND extra_table = \'' . strtolower($extra_class) . '\''
            );
            
            foreach ($uids as $uid) {
                $extras[] = tx_newspaper_Extra_Factory::getInstance()->create($uid['uid']);
            }
        }

        return $extras;
    }
    
	
	/// Read Extras from DB
	/** Objective: Read tx_newspaper_Extra array and attributes from the base  
	 *  class c'tor instead of every descendant to minimize code duplication.
	 * 
	 *  Problem: The descendant c'tor calls \c parent::__construct(). The
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
	 *  \param $hidden_too Also get Extras that are hidden because their
     *        inheritance mode has been set to false
	 */
 	protected function readExtras($uid, $hidden_too = false) {
 	
 		$uids = tx_newspaper::selectRows(
			'DISTINCT uid_foreign', 
			$this->getExtra2PagezoneTable(), 
			"uid_local = $uid", 
			'', '', '', false
		);
		if (empty($uids)) return;
		
        foreach ($uids as $uid) {
        	try {
				//  assembling the query manually here cuz we want to ignore enable fields
				$query = $GLOBALS['TYPO3_DB']->SELECTquery(
					'deleted, gui_hidden, show_extra', 
					tx_newspaper_Extra_Factory::getExtraTable(),
					'uid = ' . $uid['uid_foreign']);
				$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		
		        $deleted = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				if (!$deleted['deleted'] && 
				    (!$deleted['gui_hidden'] || $hidden_too) && 
					!(TYPO3_MODE != 'BE' && 
					!$deleted['show_extra'])) {
	
        			$extra = tx_newspaper_Extra_Factory::getInstance()->create($uid['uid_foreign']);
	        		$this->extras[] = $extra;
				} else {
					/// \todo remove association table entry, but only if really deleted
				}
       		} catch (tx_newspaper_EmptyResultException $e) {
       			/// \todo remove association table entry
				t3lib_div::debug('Extra ' . $uid['uid_foreign'] . ': EmptyResult! '. $e);
        	}
        }
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
		$this->extras[] = $extra;
		
		tx_newspaper::insertRows(
			$this->getExtra2PagezoneTable(),
			array(
				'uid_local' => $this->getUid(),
				'uid_foreign' => $extra->getExtraUid()
			)
		);
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
 	protected $pagezone_attributes = array(); ///< array of attributes for the parent part of the record
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
