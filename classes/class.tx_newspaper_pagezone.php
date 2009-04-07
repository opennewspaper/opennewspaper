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
	
	/// Configure Smarty rendering engine
	public function __construct($uid = 0, tx_newspaper_Page $page = null, tx_newspaper_PageZoneType $type = null) {
		/// Configure Smarty rendering engine
		$this->smarty = new tx_newspaper_Smarty();
		if ($uid) {
			$this->setUid($uid);
			/** I'm not sure whether the following line should remain. It's a
			 *  safety net because currently it's not ensured that extras are 
			 *  created consistently.
			 */
			tx_newspaper_Extra::createExtraRecord($uid, $this->getTable());
		}
		if ($page) $this->parent_page = $page;
		if ($type) $this->pagezonetype = $type;
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
		if ($this->getParentPage()->getPagetype()) {
			$this->smarty->setPageType($this->getParentPage());
		}
		if ($this->getPageZoneType()) {
			$this->smarty->setPageZoneType($this);
		}

 		/// Pass global attributes to Smarty
 		$this->smarty->assign('class', get_class($this));
 		$this->smarty->assign('attributes', $this->attributes);
 		
		/** Pass the Extras on this page zone, already rendered, to Smarty
		 *  \todo correct order of extras
		 *  \todo blockweise zusammenfuehren von extras gleiches layout (nicht vor taz launch)
		 */
 		$temp_extras = array();
 		foreach ($this->extras as $extra) {
 			$temp_extras[] = $extra->render($template_set);
 		}
 		$this->smarty->assign('extras', $temp_extras);

 		return $this->smarty->fetch($this);
 	}
 	
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
	
	public  function getTable() {
		return tx_newspaper::getTable($this);
	}

	public function getPageZoneType() {
		if (!$this->pagezonetype) {
			$pagezonetype_id = $this->getUid()? $this->getAttribute('pagezonetype_id'): 0;
			$this->pagezonetype = new tx_newspaper_PageZoneType($pagezonetype_id);
		} 
		return $this->pagezonetype; 
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
				$page_attributes = tx_newspaper::selectOneRow(
					'section, pagetype_id', 'tx_newspaper_page', 
					'uid = ' . $this->parent_page_id 
				);
				if (intval($page_attributes['section']) &&
					intval($page_attributes['pagetype_id'])) {
					$this->parent_page = new tx_newspaper_page(
							new tx_newspaper_Section($page_attributes['section']),
							new tx_newspaper_PageType($page_attributes['pagetype_id'])
					);
				}
			} else {
				throw new tx_newspaper_InconsistencyException(
					'PageZone ' . $this->getUid() . ' appears to have no parent Page'
				);
			}
		}
		return $this->parent_page;
	}
	
	public function store() {
		
		if ($this->getUid()) {
			/// If the attributes are not yet in memory, now would be a good time to read them 
			if (!$this->attributes) {
				$this->readAttributes($this->getTable(), $this->getUid());
			}			
				
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
		tx_newspaper::updateRows(
			'tx_newspaper_pagezone', 
			'uid = ' . $pagezone_uid, 
			array('page_id' => $this->parent_page)
		);
		
		
		return $this->getUid();
		
	}
	
	/** \todo Internationalization */
	public function getTitle() {
		return 'PageZone';
	}
	
	static function getModuleName() { return 'np_pagezone'; }

	static function readExtraItem($uid, $table) {
		throw new tx_newspaper_NotYetImplementedException();
	}
 	
 	////////////////////////////////////////////////////////////////////////////
 	
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
	 *  \param $uid UID in the table of the concrete type 
	 */
 	protected function readExtras($uid) {
		$uids = tx_newspaper::selectRows(
			'uid_foreign', $this->getExtra2PagezoneTable(), "uid_local = $uid"
		);

		if ($uids) {
        	foreach ($uids as $uid) {
        		$this->extras[] = tx_newspaper_Extra_Factory::getInstance()->create($uid['uid_foreign']);
        	}
		}
 	}
 	
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
	public static function createPageZoneRecord($uid, $table) {
		/// Check if record is already present in page zone table
		$row = tx_newspaper::selectZeroOrOneRows(
			'uid', 'tx_newspaper_pagezone', 
			'pagezone_table = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($table, $table) .
			' AND pagezone_uid = ' . intval($uid)	
		);
		if ($row['uid']) return $row['uid'];
		
		/// read typo3 fields to copy into page zone table
		$row = tx_newspaper::selectOneRow(
			implode(', ', self::$fields_to_copy_into_pagezone_table),
			$table,
			'uid = ' . intval($uid)
		);
		
		/// write the uid and table into page zone table, with the values read above
		$row['pagezone_uid'] = $uid;
		$row['pagezone_table'] = $table;
		$row['tstamp'] = time();				///< tstamp is set to now

		return tx_newspaper::insertRows('tx_newspaper_pagezone', $row);		
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
 	abstract protected function getExtra2PagezoneTable();
 	
	function getUid() { return intval($this->uid); }
	function setUid($uid) { $this->uid = $uid; }

	protected function getExtras() { return $this->extras; }

	/// get active pages zone for given page
	/** \param page object
	 *  \return array active pages zone objects for given page
	 */
	public static function getActivePageZones(tx_newspaper_Page $p) {

		$pid_list = tx_newspaper_Sysfolder::getInstance()->getPidsForAbstractClass('tx_newspaper_PageZone');
		if (sizeof($pid_list) == 0) {
			throw new tx_newspaper_SysfolderNoPidsFoundException('tx_newspaper_PageZone');
		}
		
		$row = tx_newspaper::selectRows(
			'*',
			'tx_newspaper_pagezone',
			'pid IN (' . implode(',', $pid_list) . ') AND page_id=' . intval($p->getUid())
		);
#t3lib_div::devlog('gapz', 'newspaper', 0, $row);
		$list = array();
		for ($i = 0; $i < sizeof($row); $i++) {
			switch (strtolower($row['pagezone_table'])) {
				case 'tx_newspaper_article':
					$list[] = new tx_newspaper_Article($row['pagezone_uid']);
				break;
				case 'tx_newspaper_pagezone_page':
					$list[] = new tx_newspaper_PageZone_Page($row['pagezone_uid']);
				break;
				default:
					/// \todo: throw ...
			}
		}
		return $list;
	}


 	private $uid = 0;
 	
 	protected $smarty = null;
 	
 	protected $attributes = array();	///< array of attributes
 	protected $extras = array();		///< array of tx_newspaper_Extra s
 	protected $pagezonetype = null;
 	
 	protected $parent_page_id = 0;
 	protected $parent_page = null;
 	
# 	static protected $table = 'tx_newspaper_pagezone';	///< SQL table for persistence
 	 
 	/// Default Smarty template for HTML rendering
 	static protected $defaultTemplate = 'tx_newspaper_pagezone.tmpl';
 	
 	private static $fields_to_copy_into_pagezone_table = array(
		'pid', 'crdate', 'cruser_id', 'deleted', 'hidden', 
	);
 	
}
 
?>