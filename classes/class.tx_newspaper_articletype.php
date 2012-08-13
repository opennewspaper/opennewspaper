<?php
/**
 *  \file class.tx_newspaper_articletype.php
 * 
 *  This file is part of the TYPO3 extension "newspaper".
 * 
 *  Copyright notice
 *
 *  (c) 2008 Helge Preuss, Oliver Schroeder, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
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
 *  \author Oliver Sch�rder <typo3@schroederbros.de>
 *  \date Mar 25, 2009
 */
 
/// Type of tx_newspaper_Article
/** Articles can have several types, which determine the way the Articles are
 *  displayed and also which tx_newspaper_Extras should be present in an Article
 *  by default. 
 * 
 *  This is best explained by an Example.
 *  - Standard Articles should always have an Image with them.
 *  - Columns don't necessarily have an Image (but they may), but they need a
 * 	  list of links.
 *  - Editorials should be displayed in a different design.
 * 
 *  Article Types must be defined previous to using them. This is done via the 
 *  Typo3 list module. Once an Article Type is defined, its properties can be
 *  set in TSConfig.
 */
class tx_newspaper_ArticleType implements tx_newspaper_StoredObject {
	
	public function __construct($uid = 0) {
		$uid = intval($uid);
		if ($uid > 0) {
			$this->setUid($uid);
		}
	}
	
	/// Convert object to string to make it visible in stack backtraces, devlog etc.
	public function __toString() {
		return get_class($this) . '-object ' . "\n" .
			   'attributes: ' . print_r($this->attributes, 1) . "\n";
	}
	
	public function getAttribute($attribute) {
		/// Read Attributes from persistent storage on first call
		if (!$this->attributes) {
			$this->attributes = tx_newspaper::selectOneRow(
				'*', 
				tx_newspaper::getTable($this), 
				'uid=' . $this->getUid() . tx_newspaper::enableFields(tx_newspaper::getTable($this))
			);
		}

		if (!array_key_exists($attribute, $this->attributes)) {
			throw new tx_newspaper_WrongAttributeException($attribute);
		}
		return $this->attributes[$attribute];
	}

	/** No tx_newspaper_WrongAttributeException here. We want to be able to set
	  *  attributes, even if they don't exist beforehand.
	  */
	public function setAttribute($attribute, $value) {
		if (!$this->attributes) {
			$this->attributes = tx_newspaper::selectOneRow(
					'*', tx_newspaper::getTable($this), 'uid = ' . $this->getUid()
			);
		}
		
		$this->attributes[$attribute] = $value;
	}

	/// Write or overwrite Section data in DB, return UID of stored record
	public function store() {
		throw new tx_newspaper_NotYetImplementedException();
	}

	public function getTitle() {
		return tx_newspaper::getTranslation('title_' . $this->getTable());	
	}

	public function getUid() {
		return intval($this->uid);
	}
	
	public function setUid($uid) {
		$this->uid = $uid;
	}

    public function getTypeName() { return $this->getAttribute('title'); }

	/// Get TSConfig setting for this article type
	/** \param $type article type configured in TSConfig, currently
	 *      available: musthave, shouldhave
	 *  \return array containing configuration: [class name][:paragraph][,[classname][:paragraph]][...,[classname][:paragraph]]
	 */
	public function getTSConfigSettings($type) {
		/// check tsconfig in article sysfolder
		$sysfolder = tx_newspaper_Sysfolder::getInstance()->getPid(new tx_newspaper_article()); 
		$tsc = t3lib_BEfunc::getPagesTSconfig($sysfolder);

		if (!isset($tsc['newspaper.']['articletype.'][$this->getAttribute('normalized_name') . '.'][$type])) {
			return array(); // no settings found
		}

		$setting = t3lib_div::trimExplode(',', $tsc['newspaper.']['articletype.'][$this->getAttribute('normalized_name') . '.'][$type]);

		return $setting;
	}
	
	
	/// \return Name of the database table the object's data are stored in
	public function getTable() { return tx_newspaper::getTable($this); }
	
	static public function getModuleName() { return 'np_articletype'; }
	
	/// \return Sorted list of all available article types
	static public function getArticleTypes() {
		$pid = tx_newspaper_Sysfolder::getInstance()->getPid(new tx_newspaper_ArticleType());
		$row = tx_newspaper::selectRows(
			'uid',
			'tx_newspaper_articletype',
			'pid=' . $pid,
			'',
			'sorting'
		);
		$at = array();
		for ($i = 0; $i < sizeof($row); $i++) {
			$at[] = new tx_newspaper_ArticleType(intval($row[$i]['uid']));
		}
		return $at;
	}	
	
	////////////////////////////////////////////////////////////////////////////
	
	private $uid = ''; ///< UID that identifies the article type
}
