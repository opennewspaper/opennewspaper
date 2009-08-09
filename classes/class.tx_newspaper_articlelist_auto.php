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

/// A list of tx_newspaper_Article s which are filled automatically
/** This tx_newspaper_ArticleList returns the Articles belonging to its
 *  associated tx_newspaper_Section. 
 * 
 *  This is basically a dummy class serving as a proof of concept. It will 
 *  probably never be used in production. For that reason, it will not be 
 *  documented further.
 */
class tx_newspaper_ArticleList_Auto extends tx_newspaper_ArticleList {

	public function getArticles($number, $start = 0) {
		
		if (!$this->section instanceof tx_newspaper_Section) {
			throw new tx_newspaper_InconsistencyException('articlelist_auto needs a section object: \'' . $this->section . '\'');
		}
		$articles = array();
		$results = tx_newspaper::selectMMQuery(
				'tx_newspaper_article.uid',
				'tx_newspaper_article',
				'tx_newspaper_article_sections_mm',
				'tx_newspaper_section',
				' AND tx_newspaper_article_sections_mm.uid_foreign = ' . intval($this->section->getAttribute('uid')) .
				' AND NOT tx_newspaper_article.is_template',
				'',
				'',
				"$start, $number"
		);

		foreach ($results as $row) {
			$articles[] = new tx_newspaper_Article($row['uid']);
		}
		return $articles;
	}
	
	public function setSection(tx_newspaper_Section $section) {
		$this->section = $section;
	}
	
	static public function getModuleName() { return 'np_al_auto'; }
	
	static protected $table = 'tx_newspaper_articlelist_auto';	///< SQL table for persistence
}

tx_newspaper_ArticleList::registerArticleList(new tx_newspaper_ArticleList_Auto());

?>