<?php
/**
 *  \file class.tx_newspaper_articlelist.php
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
 *  \date Jan 30, 2009
 */
 
 /// A list of tx_newspaper_Article s
 /**  
  *  abstract functions:
  *  - function getArticles($number, $start)
  *	 - public static function getModuleName()
  */
abstract class tx_newspaper_ArticleList implements tx_newspaper_StoredObject {
	public function __construct($uid = 0, tx_newspaper_Section $section = null) {
		if ($uid) {
			$this->setUid($uid);
	 		$this->attributes = tx_newspaper::selectOneRow(
				'*', $this->getTable(), "uid = $uid"
			);
		}
		if ($section) {
			$this->section = $section;
		} else {
			$this->section = new tx_newspaper_Section($this->attributes['section_id']);
		}
	}

	public function getArticle($index) {
		$articles = $this->getArticles(1, $index);
		return $articles[0];
	}

	abstract function getArticles($number, $start = 0);

	public function getTable() { return tx_newspaper::getTable($this); }
	public function getUid() { return intval($this->uid); }
	public function setUid($uid) { $this->uid = $uid; }

	static public function registerArticleList(tx_newspaper_ArticleList $newList) {
		self::$registered_articlelists[] = $newList;
	}

	static public function getRegisteredArticleLists() {
		return self::$registered_articlelists;
	}

	/// \todo i18n
	public function getTitle() {
		global $LANG;
		return $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.php:title_' .
						 tx_newspaper::getTable($this), false);
	}

	private $uid = 0;

	protected $attributes = array();
	protected $section = null;

	/// SQL table for persistence
	static protected $table = 'tx_newspaper_articlelist';
	/// SQL table for tx_newspaper_Section objects	
	static protected $section_table = 'tx_newspaper_section';
	/// Array for registered article lists (subclasses of tx_newspaper_ArticleList)
	static protected $registered_articlelists = array();
}


?>
