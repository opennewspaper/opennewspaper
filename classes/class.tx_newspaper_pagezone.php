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
	public function __construct($uid = 0) {
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
	}
	
	/// Render the page zone, containing all extras
	/** \return The rendered page as HTML (or XML, if you insist) 
	 */
 	public function render($template = '') {
 		if (!$template) $template = $this;

		$this->smarty->setTemplateSearchPath(
			array(
				'template_sets/' . strtolower($this->getParentPage()->getPagetype()->getAttribute('type_name')) . '/'. 
								   strtolower($this->getPageZoneType()->getAttribute('name')),
				'template_sets/' . strtolower($this->getPageZoneType()->getAttribute('name')),
				'template_sets'
			)
		);
 		
 		$this->smarty->assign('class', get_class($this));
 		$this->smarty->assign('attributes', $this->attributes);
 		
 		/// render extras
 		/// \todo correct order of extras
 		$temp_extras = array();
 		foreach ($this->extras as $extra) {
 			$temp_extras[] = $extra->render();
 		}
 		$this->smarty->assign('extras', $temp_extras);

 		return $this->smarty->fetch($template);
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
			$this->pagezonetype = new tx_newspaper_PageZoneType($this->getAttribute('pagezonetype_id'));
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
				$this->parent_page_id = $pagezone_record['page_id'];
			}
			$page_attributes = tx_newspaper::selectOneRow(
				'section, pagetype_id', 'tx_newspaper_page', 
				'uid = ' . $this->parent_page_id 
			);
			$this->parent_page = new tx_newspaper_page(new tx_newspaper_Section($page_attributes['section']),
							 						   new tx_newspaper_PageType($page_attributes['pagetype_id']));
		}
		return $this->parent_page;
	}
	
	public function store() {
		/// \todo see extraImpl::store()
	}
	
	/** \todo Internationalization */
	static function getTitle() {
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
		/// Check if record is already present in extra table
		$row = tx_newspaper::selectZeroOrOneRows(
			'uid', 'tx_newspaper_pagezone', 
			'pagezone_table = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($table, $table) .
			' AND pagezone_uid = ' . intval($uid)	
		);
		if ($row['uid']) return $row['uid'];
		
		/// read typo3 fields to copy into extra table
		$row = tx_newspaper::selectOneRow(
			implode(', ', self::$fields_to_copy_into_pagezone_table),
			$table,
			'uid = ' . intval($uid)
		);
		
		/// write the uid and table into extra table, with the values read above
		$row['pagezone_uid'] = $uid;
		$row['pagezone_table'] = $table;
		$row['tstamp'] = time();				///< tstamp is set to now


		/** use the PID all PageZones share. If PageZones are created under more
		 *  than one page, we have a problem and can't continue.
		 */
		$rows = tx_newspaper::selectRows(
			'DISTINCT pid', 'tx_newspaper_pagezone', 'pid != 0'
		);
		if (sizeof($rows) != 1) {
		 	throw new tx_newspaper_InconsistencyException(
		 		'Abstract PageZones were created on more than one page:<br />' . "\n" .
		 		print_r($rows, 1)
		 	);
		}
		$row['pid'] = $rows[0]['pid'];

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


	/// get active pages zone for given page
	/** \param $page_uid uid of page
	 *  \return array uids of active pages zone for given page
	 */
	public static function getActivePageZones($page_uid, $include_hidden=true) {
		$where = ($include_hidden)? '' : ' AND hidden=0'; // should hidden pages be included?
	
		$pid_list = tx_newspaper_Sysfolder::getInstance()->getPidsForAbstractClass('tx_newspaper_PageZone');
		if (sizeof($pid_list) == 0) {
			throw new tx_newspaper_SysfolderNoPidsFoundException('tx_newspaper_PageZone');
		}
		
		$row = tx_newspaper::selectRows(
			'*',
			'tx_newspaper_pagezone',
			'pid IN (' . implode(',', $pid_list) . ') AND page_id=' . intval($page_uid) . $where
		);
		return $row;
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
		'tstamp', 'crdate', 'cruser_id', 'deleted', 'hidden', 
	);
 	
}
 
?>