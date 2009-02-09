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
 
require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_pagezonetype.php');
 
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
abstract class tx_newspaper_PageZone implements tx_newspaper_Extra {
	
	/// Configure Smarty rendering engine
	public function __construct($uid = 0) {
		$this->smarty = new Smarty();

		$this->smarty = new Smarty();
		$tmp = "/tmp/" . substr(BASEPATH, 1);
		file_exists($tmp) || mkdir($tmp, 0774, true);
		
		$this->smarty->template_dir = BASEPATH.'/fileadmin/templates/tx_newspaper/smarty';
		$this->smarty->compile_dir  = $tmp;
		$this->smarty->config_dir   = $tmp;
		$this->smarty->cache_dir    = $tmp;
		
		if ($uid) {
			/** I'm not sure whether the following line should remain. It's a
			 *  safety net because currently it's not ensured that extras are 
			 *  created consistently.
			 */
			tx_newspaper_ExtraImpl::createExtraRecord($uid, $this->getTable());
		}
	}
	
	/// Render the page zone, containing all extras
	/** \return The rendered page as HTML (or XML, if you insist) 
	 */
 	public function render($template = '') {
 		if (!$template) $template = self::$defaultTemplate;
 		
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
		return $this->attributes[$attribute];
	}

	/// sets a member (-> Extra)
	function setAttribute($attribute, $value) {
		$this->attributes[$attribute] = $value;
	}
	
	public  function getTable() {
		return tx_newspaper::getTable($this);
	}

	/** \todo Internationalization */
	static function getTitle() {
		return 'PageZone';
	}
	
	
	static function getModuleName() {
		throw new tx_newspaper_NotYetImplementedException();
	}

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
 	
 	protected $smarty = null;
 	
 	protected $attributes = array();	///< array of attributes
 	protected $extras = array();		///< array of tx_newspaper_Extra s
 	
 	static protected $table = 'tx_newspaper_pagezone';	///< SQL table for persistence
 	 
 	/// Default Smarty template for HTML rendering
 	static protected $defaultTemplate = 'tx_newspaper_pagezone.tmpl';
 	
}
 
?>