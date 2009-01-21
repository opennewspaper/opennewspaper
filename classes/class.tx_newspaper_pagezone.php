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
 
//require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_pagezone_factory.php');
 
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
		
	public function __construct($uid) {
		$this->smarty = new Smarty();

		/// Configure Smarty rendering engine
		$this->smarty = new Smarty();
		$tmp = "/tmp/" . substr(BASEPATH, 1);
		file_exists($tmp) || mkdir($tmp, 0774, true);
		
		$this->smarty->template_dir = BASEPATH.'/fileadmin/templates/tx_newspaper/smarty';
		$this->smarty->compile_dir  = $tmp;
		$this->smarty->config_dir   = $tmp;
		$this->smarty->cache_dir    = $tmp;
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
	
	/// Defined to implement the tx_newspaper_Extra interface
	/** Of course it sucks that we implement an interface that, well, we don't
	 *  implement fully. 
	 *  \todo Think of a way out of this.
	 */
	function getSource() { 
		throw new tx_newspaper_IllegalUsageException(
			"tx_newspaper_PageZone::getSource(): " .
			"PageZone should never deal with Sources");
	}

	/// Defined to implement the tx_newspaper_Extra interface
	/** Of course it sucks that we implement an interface that, well, we don't
	 *  implement fully. 
	 *  \todo Think of a way out of this.
	 */
	function setSource(tx_newspaper_Source $source) {
		throw new tx_newspaper_IllegalUsageException(
			"tx_newspaper_PageZone::setSource(): " .
			"PageZone should never deal with Sources");
	 }
	
	/// Defined to implement the tx_newspaper_Extra interface
	/** Of course it sucks that we implement an interface that, well, we don't
	 *  implement fully. 
	 *  \todo Think of a way out of this.
	 */
	static function mapFieldToSourceField($fieldname, tx_newspaper_Source $source) {
		throw new tx_newspaper_IllegalUsageException(
			"tx_newspaper_PageZone::mapFieldToSourceField(): " .
			"PageZone should never deal with Sources");
	}
	
	/// Defined to implement the tx_newspaper_Extra interface
	/** Of course it sucks that we implement an interface that, well, we don't
	 *  implement fully. 
	 *  \todo Think of a way out of this.
	 */
	static function sourceTable(tx_newspaper_Source $source) {
		throw new tx_newspaper_IllegalUsageException(
			"tx_newspaper_PageZone::sourceTable(): " .
			"PageZone should never deal with Sources");
	}

	static function getName() {
		return self::$table;
	}

	/** \todo Internationalization */
	static function getTitle() {
		return 'PageZone';
	}
	
	
	static function getModuleName() {
		throw new tx_newspaper_NotYetImplementedException("tx_newspaper_PageZone::getModuleName()");
	}

	static function readExtraItem($uid, $table) {
		throw new tx_newspaper_NotYetImplementedException("tx_newspaper_PageZone::readExtraItem()");
	}
 	
 	static protected function getExtra2PagezoneTable() {
 		if (!$this->extra_2_pagezone_table) {
 			throw new tx_newspaper_IllegalUsageException(
				'getExtra2PagezoneTable() can not be called on class ' .
				'tx_newspaper_PageZone, only on its  descendants');
 		}
 		return $this->extra_2_pagezone_table;
 	}
 	
 	protected $smarty = null;
 	
 	protected $attributes = array();	///< array of attributes
 	protected $extras = array();		///< array of tx_newspaper_Extra s
 	
 	static protected $table = 'tx_newspaper_pagezone';	///< SQL table for persistence
 	 
 	/// Default Smarty template for HTML rendering
 	static protected $defaultTemplate = 'tx_newspaper_pagezone.tmpl';
 	
}
 
?>