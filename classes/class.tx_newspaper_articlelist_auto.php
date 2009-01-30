<?php
/**
 *  \file class.tx_newspaper_articlelist_auto.php
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

require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_articlelist.php');

/// A list of tx_newspaper_Article s
class tx_newspaper_ArticleList_Auto extends tx_newspaper_ArticleList {
 	
 	function getArticle($number) { 
 		throw new tx_newspaper_NotYetImplementedException();
 	}
 	
 	function getArticles($number, $start = 0) {
 		/// \todo honor $number and $start
		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
			'tx_newspaper_article.uid', 
			'tx_newspaper_article',
			'tx_newspaper_article_sections_mm',
			'tx_newspaper_section',
			' AND tx_newspaper_article_sections_mm.uid_foreign = ' . $this->section->getAttribute('uid'),
			'',
			'',
			''
		);
		tx_newspaper::$query = $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery;
		
		if (!$res) {
			throw new tx_newspaper_NoResException();
		}
		
		$articles = array();
	    while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
	    	$articles[] = new tx_newspaper_ArticleImpl($row['uid']);
	    } 
			
		return $articles;
 	}
 	
 	static protected $table = 'tx_newspaper_articlelist_auto';	///< SQL table for persistence
 }
 
?>
