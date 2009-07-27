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

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_articlelist.php');

/// A list of tx_newspaper_Article s
class tx_newspaper_ArticleList_Manual extends tx_newspaper_ArticleList {

	public function getArticles($number, $start = 0) {		
		$results = tx_newspaper::selectMMQuery(
				'tx_newspaper_article.uid',
				'tx_newspaper_articlelist_manual',
				'tx_newspaper_articlelist_manual_articles_mm',
				'tx_newspaper_article',
				' AND tx_newspaper_articlelist_manual_articles_mm.uid_local = ' . intval($this->getUid()),
				'',
				'tx_newspaper_articlelist_manual_articles_mm.sorting DESC',
				"$start, $number"
		);
		t3lib_div::devlog('MM query', 'newspaper', 0, tx_newspaper::$query);
		t3lib_div::devlog('MM query result', 'newspaper', 0, $results);
		
		$articles = array();
		// new tx_newspaper_Article(9), new tx_newspaper_Article(10), new tx_newspaper_Article(11)
		foreach ($results as $row) {
			$articles[] = new tx_newspaper_Article($row['uid']);
		}
		
		return $articles;
	}
	
	static public function getModuleName() { return 'np_al_manual'; }
	
	static protected $table = 'tx_newspaper_articlelist_manual';	///< SQL table for persistence
}

tx_newspaper_ArticleList::registerArticleList(new tx_newspaper_ArticleList_Manual());

?>