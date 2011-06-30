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


/// A page of a certain type for an online edition of a newspaper
/** Under every tx_newspaper_Section there lie pages of different types.
 *  Whenever a certain type is required, the corresponding tx_newspaper_Page is
 *  displayed.
 *
 *  Examples for page types include:
 *  - List view of the most recent articles in a section
 *  - Article view, displays an article
 *  - Comments page, shows the comments to an article (or a section page)
 *  - RSS feed for list view or article page
 *  - Mobile versions of any of the above
 *  - Whatever else you can think of
 *
 *  tx_newspaper_Page implements the objects that are instantiated once for
 *  every needed page type/section combination.
 *
 *  \see tx_newspaper_Section, tx_newspaper_PageType
 */
class tx_newspaper_Page
		implements tx_newspaper_StoredObject, tx_newspaper_Renderable {

	/// Construct a tx_newspaper_Page, either read from DB or create a new one
	/** If the tx_newspaper_Page already exists and is stored in DB, just pass
	 *  the UID of the record containing the data to the constructor.
	 *
	 *  To create a new tx_newspaper_Page, give the constructor the
	 *  tx_newspaper_Section the tx_newspaper_Page lies under, and the
	 *  tx_newspaper_PageType of the tx_newspaper_Page to be.
	 *
	 *  \param $parent Either the tx_newspaper_Section the tx_newspaper_Page is
	 * 		in, or the UID of the page in the DB
	 *  \param $type tx_newspaper_PageType of which a tx_newspaper_Page is created
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

 	///	Create a deep copy of an object
 	public function __clone() {
 		/*  ensure attributes are loaded from DB. readAttributesFromDB() isn't
 		 *  called here because maybe the content is already there and it would
 		 *  cause the DB operation to be done twice.
 		 */
		$this->getAttribute('uid');

		/// Unset the UID so the object can be written to a new DB record.
 		$this->attributes['uid'] = 0;
 		$this->setUid(0);

 		$this->setAttribute('crdate', time());
 		$this->setAttribute('tstamp', time());

 		/// Clone tx_newspaper_PageZone s contained on tx_newspaper_Page.
 		$old_pagezones = $this->getPageZones();
		$this->pageZones = array();
		if (is_array($old_pagezones) && sizeof($old_pagezones) > 0) {
			foreach ($old_pagezones as $old_pagezone) {
				$this->pageZones[] = clone $old_pagezone;
			}
		}
 	}

	/// Convert object to string to make it visible in stack backtraces, devlog etc.
 	public function __toString() {
 		try {
	 		$this->getAttribute('uid');
	 		$ret = $this->getTable() . ':' . " \n" .
	 			   'UID: ' . $this->getUid() . " \n" .
					($this->parentSection? ('parentSection: ' . $this->parentSection->getUid() . " \n"): '') .
	 			   'condition: ' . $this->condition . " \n" .
	 			   'pageZones: ';
	 		foreach ($this->pageZones as $page_zone) {
	 		    $ret .= $page_zone . "\n";
	 		}
	 		$ret .= (($this->getPageType() && $this->getPageType() instanceof tx_newspaper_PageType)?
	 			('pagetype: ' . $this->getPageType()->getUid() . ' (' . $this->getPageType()->getAttribute('type_name') . ") \n"):
				'');
 		} catch (Exception $e) {
 			$ret = 'Page ' . $this->getUid() . ': __toString() threw an exception: ' . $e;
 		}
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

	public function store() {

		if ($this->getUid()) {
			/// If the attributes are not yet in memory, read them now
			if (!$this->attributes) $this->readAttributesFromDB();

            tx_newspaper::setDefaultFields($this, array('tstamp'));

			tx_newspaper::updateRows(
				$this->getTable(), 'uid = ' . $this->getUid(), $this->attributes
			);
		} else {
			$this->attributes['section'] = $this->parentSection->getUid();
			$this->attributes['pagetype_id'] = $this->pagetype->getUid();
			/** \todo If the PID is not set manually, $tce->process_datamap()
			 * 		  fails silently.
			 */
            tx_newspaper::setDefaultFields($this, array('crdate', 'tstamp', 'pid', 'cruser_id'));

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

	///	Check if the tx_newspaper_Page belongs to a tx_newspaper_Section
	/** \param $s tx_newspaper_Section to check against
	 *  \return \c true if current page can be accessed and is assigned to \p $s
	 * 		(FE/BE use enableFields)
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

	/// Adds pagezone $pz to pageZones attribute array
	private function appendPagezone(tx_newspaper_pagezone $pz) {
		if ($this->pageZones === true) {
			$this->pageZones = array();
		}
		$this->pageZones[] = $pz;
	}

	public function getTitle() {
		return tx_newspaper::getTranslation('title_' . $this->getTable());
	}

	///	Get all  tx_newspaper_PageZone s on the current tx_newspaper_Page
	/** \return array of tx_newspaper_PageZone s on the current
	 * 		tx_newspaper_Page, or \c true if there is none.
	 *
	 *  \attention \c true is used as a sentinel value to denote that there are
	 * 		no tx_newspaper_PageZone s under this tx_newspaper_Page, so that SQL
	 * 		queries are not performed unnecessarily. The return value must not
	 * 		be tested if it evaluates to \c true, rather use \c is_array() to
	 * 		check it!
	 *
	 *  \todo Use another member variable instead of misappropriating
	 *  	\c $this->pageZones. Might have to rewrite functions which call
	 * 		getPageZones() though.
	 */
	function getPageZones() {
 		/// Cache tx_newspaper_PageZone list for current page at first call.
		if (!$this->pagezones_are_already_read) {
			$uids = tx_newspaper::selectRows(
	 			'uid', 'tx_newspaper_pagezone',
				'page_id = '.$this->getAttribute('uid')
			);
            $this->pagezones_are_already_read = true;

            if ($uids) {
	        	foreach ($uids as $uid) {
	        		$this->pageZones[] =
	        			tx_newspaper_PageZone_Factory::getInstance()->create($uid['uid']);
	        	}
			}
		}

		return $this->pageZones;
	}

	///	Get the tx_newspaper_PageZone of the desired tx_newspaper_PageZoneType
	/** There can be only one tx_newspaper_PageZone of any
	 *  tx_newspaper_PageZoneType on every  tx_newspaper_Page. If it is found on
	 *  \c $this, it is returned. Otherwise, \c null is returned.
	 *
	 *  \param $type Wanted tx_newspaper_PageZoneType.
	 *
	 *  \return tx_newspaper_PageZone of type \p $type, or \c null.
	 */
	function getPageZone(tx_newspaper_PageZoneType $type) {
		if (!is_array($this->getPageZones())) return null;
 		foreach ($this->getPageZones() as $pagezone) {
 			if ($pagezone->getPageZoneType()->getUid() == $type->getUid())
 				return $pagezone;
 		}
 		return null;
	}

	/// Render the page, containing all associated tx_newspaper_PageZone s
	/** The correct template is found the following way.
	 *  - The template set for the page is set via TSConfig.
	 *  - The name for the page is found via its tx_newspaper_PageType.
	 *  - If the template \c tx_newspaper_page.tmpl exists under directory
	 *    \c $template_set/$page_type, use it
	 *  - Else, if \c tx_newspaper_page.tmpl exists under directory
	 *    \c $template_set, use it
	 *  - Else, use the default template under
	 * 	  <tt>PATH_typo3conf . 'ext/newspaper/res/templates'</tt>, ie. the one
	 *    delivered with \c tx_newspaper.
	 *
	 *  Default smarty template:
	 *  \include res/templates/tx_newspaper_page.tmpl
	 *
	 *  \todo implement this template-finding logic by calling
	 * 		  \c $this->smarty->setTemplateSearchPath()
	 *
	 *  \param $template_set the template set used to render this page (as
	 *  		passed down from tx_newspaper_pi1 - should be always empty.
	 *
	 *  \return The rendered page as HTML (or whatever your template does)
	 */
 	public function render($template_set = '') {

		/// Check the parent Section and own attributes whether to use a specific template set
 		if ($this->getParentSection()->getTemplateSet()) {
			$template_set = $this->getParentSection()->getTemplateSet();
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
        $rendered = $this->smarty->fetch($this);

        return $rendered;
 	}

 	///	Lists all tx_newspaper_Page s with the given tx_newspaper_PageType
 	/** \param $pt tx_newspaper_PageType of which tx_newspaper_Page s are wanted
 	 *  \param $limit Maximum number of tx_newspaper_Page s returned
 	 *
 	 *  \todo move to tx_newspaper_PageType
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

 	///	Lists all tx_newspaper_Page s containing a tx_newspaper_PageZone with the given tx_newspaper_PageZoneType
 	/** \param $pzt tx_newspaper_PageZoneType required on tx_newspaper_Page s
 	 *  \param $limit Maximum number of tx_newspaper_Page s returned
 	 *
 	 *  \todo move to tx_newspaper_PageZoneType
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

	/// Get tx_newspaper_Page object for given tx_newspaper_PageZone_Page object
	/** \param $pzp tx_newspaper_PageZone_Page object
	 *  \return \p $pzp's parent tx_newspaper_Page object
	 *  \todo Move to tx_newspaper_PageZone_Page
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

	/// Delete a newspaper page and all subsequent abstract and concrete pagezones
	public function delete() {
		$this->deletePagezones();
		$this->setAttribute('deleted', 1); // delete page then
		$this->store();
	}

	/// Delete all subsequent abstract and concrete pagezones
	public function deletePagezones() {
		foreach ($this->getActivePagezones() as $pz) {
			$pz->delete(); // delete all activated pagezones
		}
	}



	/// Get active tx_newspaper_PageZone s for this tx_newspaper_Page
	/** \param $includeDefaultArticle Are default articles included?
	 *  @return tx_newspaper_PageZone[] array of active page zones for given
	 *  	tx_newspaper_Page.
	 */
	public function getActivePageZones($includeDefaultArticle=true) {

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
			if ($includeDefaultArticle || $row[$i]['pagezone_table'] == 'tx_newspaper_pagezone_page') {
				$list[] = new $row[$i]['pagezone_table'](intval($row[$i]['pagezone_uid']));
			}
		}
#t3lib_div::debug($list);
		return $list;
	}

	///	The tx_newspaper_Section under which this tx_newspaper_Page lies
    /**
     * @return tx_newspaper_Section The section under which this page lies
	 */
 	public function getParentSection() {
 		if (!$this->parentSection) {
 			$this->parentSection = new tx_newspaper_Section(intval($this->getAttribute('section')));
 		}
 		return $this->parentSection;
 	}

    public function getTypo3PageID() {
        return $this->getParentSection()->getTypo3PageID();
    }

 	/// Find page of same page type under parent section.	*/
 	public function getParentPageOfSameType() {

	 	/** First get parent section of the current page...	*/
	 	$parent_section = $this->getParentSection();
	 	if (!$parent_section instanceof tx_newspaper_Section) {
	 		throw new tx_newspaper_InconsistencyException(
	 			'Oops, found a Page without a parent Section: Page ID = ' . $this->getUid()
	 		);
	 	}

	 	while ($parent_section) {
		 	/** ... then get parent section of the current section.	*/
		 	$parent_section = $parent_section->getParentSection();
		 	if (!$parent_section instanceof tx_newspaper_Section) {
			 	//	Root of section tree reached
			 	return null;
		 	}

		 	foreach ($parent_section->getSubPages() as $page) {
			 	if ($page->getPageType()->getUid() == $this->getPageType()->getUid()) {
				 	return $page;
			 	}
		 	}
	 	}

 	}

	public function getSubPagesOfSameType() {

		$sub_pages = array();
		$page_type = $this->getPageType();

		foreach ($this->getParentSection()->getChildSections() as $sub_section) {
			$page = $sub_section->getSubPage($page_type);
			if ($page instanceof tx_newspaper_Page) {
				$sub_pages[] = $page;
			} else {
				// find page further down the section hierarchy
				$sub_pages = array_merge($sub_pages, $sub_section->getSubPagesRecursively($page_type));
			}
		}

		return $sub_pages;
	}

	/// Activate a pagezone for this page
	/// \return true if pagezone was activated, false if pagezone has been active already
	public function activatePagezone(tx_newspaper_PagezoneType $type) {
		if ($this->getPageZone($type)) {
			return false; // pagezone has been activated already
		}

		// pagezone table associated to current pagezone type
		$pzConcreteTable = ($type->getAttribute('is_article'))? 'tx_newspaper_article' : 'tx_newspaper_pagezone_page';

		// check if a pagezone can be re-activated
		$row = tx_newspaper::selectRowsDirect(
			'pz.uid pz_uid, pz_concrete.uid pz_concrete_uid',
			'tx_newspaper_pagezone pz, ' . $pzConcreteTable . ' pz_concrete',
			'pz.page_id=' . $this->getUid() . ' AND pz.pagezone_uid=pz_concrete.uid AND pz_concrete.pagezonetype_id=' .
				$type->getUid() . ' AND pz.deleted=1',
			'',
			'pz.uid DESC',
			'1'
		);

		if ($row) {

			// restore abstract record
			tx_newspaper::updateRows(
				'tx_newspaper_pagezone',
				'uid=' . $row[0]['pz_uid'],
				array('deleted' => 0, 'tstamp' => time())
			);

			// restore concrete record
			tx_newspaper::updateRows(
				$pzConcreteTable,
				'uid=' . $row[0]['pz_concrete_uid'],
				array('deleted' => 0, 'tstamp' => time())
			);

			$pz = tx_newspaper_PageZone_Factory::getInstance()->create($row[0]['pz_uid']);

			// inherit extras (to reflect all changes since this pagezone was deleted)
			$pz->changeParent($pz->getParentForPlacement());

		} else {
			$pz = tx_newspaper_PageZone_Factory::getInstance()->createNew(
				$this,
				$type
			);
tx_newspaper::devlog("createNew done ");

			$pz->setAttribute('crdate', time());
			$pz->setAttribute('tstamp', time());
			$pz->setAttribute('cruser_id', $GLOBALS['BE_USER']->user['uid']);
			$pz->store();
tx_newspaper::devlog("pz stored ");
		}

		$this->appendPagezone($pz); // add pagezone to pageZones array, so it's available right away

		return true;

	}


 	/// tx_newspaper_PageType of the current tx_newspaper_Page
 	/** \return tx_newspaper_PageType of the current tx_newspaper_Page
 	 */
 	public function getPageType() {
 		if (!$this->pagetype) {
 			$this->pagetype = new tx_newspaper_PageType(intval($this->getAttribute('pagetype_id')));
 		}
 		return $this->pagetype;
 	}

	static function getModuleName() { return 'np_page'; }

 	public function getTable() { return tx_newspaper::getTable($this); }
	function getUid() { return intval($this->uid); }
	function setUid($uid) { $this->uid = $uid; }

	/// Read the record for this object from DB
	/** Because a page can be constructed both with a UID and a combination of
	 *  parent section and page type to uniquely define it, reading the record
	 *  is a bit more complicated than for other objects. Thus, it is factored
	 *  out here.
	 */
	protected function readAttributesFromDB() {

		if ($this->getUid()) {
			$this->attributes = tx_newspaper::selectOneRow(
				'*', $this->getTable(), 'uid = ' . $this->getUid()
			);
		} else {

            $parent = $this->getParentSection();
            if (! $parent instanceof tx_newspaper_Section ) return;

            $type = $this->pagetype;
            if (!$type instanceof tx_newspaper_PageType) return;

			$this->attributes = tx_newspaper::selectOneRow(
                '*', $this->getTable(),
				'section = ' . $parent->getAttribute('uid') .
				' AND pagetype_id = ' . $type->getID()
			);
			$this->setUid($this->attributes['uid']);
		}
	}


	/// UID of record in the DB
 	private $uid = 0;

 	/// tx_newspaper_Smarty object for HTML rendering
 	private $smarty = null;
 	/// tx_newspaper_Section this page is in
 	private $parentSection = null;
 	/// tx_newspaper_PageType of the current page
 	private $pagetype = null;
 	/// WHERE-condition used to find current page
 	private $condition = null;
 	/// tx_newspaper_PageZone s on this page
 	private $pageZones = array();
    /// marker that is set to true once the page zones are read, to avoid repetitions if there are none
    private $pagezones_are_already_read = false;
 	/// The member variables
 	private $attributes = array();

 	/// Default tx_newspaper_Smarty template for HTML rendering
 	/** \todo get rid of this variable */
 	static private $defaultTemplate = 'tx_newspaper_page.tmpl';

}

?>
