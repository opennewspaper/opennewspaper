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
 abstract class tx_newspaper_ArticleList {
 	function __construct($uid, tx_newspaper_Section $section = null) {
 		$this->attributes = tx_newspaper::selectOneRow(
			'*', self::getName(), "uid = $uid"
		);
		if ($section) {
			$this->section = $section;
		} else {
			$this->section = new tx_newspaper_Section($this->attributes['section_id']);
		}
 	}
 	
 	function getArticle($number) {
 		$articles = $this->getArticles(1, $number);
 		return $articles[0];
 	}
 	
 	abstract function getArticles($number, $start = 0);
 	
 	static function getName() {
		return strtolower(get_class());
	}
 	
 	protected $attributes = array();
 	protected $section = null;
 	
 	/// SQL table for persistence
 	static protected $table = 'tx_newspaper_articlelist';
 	/// SQL table for tx_newspaper_Section objects	
 	static protected $section_table = 'tx_newspaper_section';
 }
 
?>
