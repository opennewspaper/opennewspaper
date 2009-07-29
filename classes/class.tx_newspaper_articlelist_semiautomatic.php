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
class tx_newspaper_ArticleList_Semiautomatic extends tx_newspaper_ArticleList {

	public function getArticles($number, $start = 0) {
		
		$uids = $this->getRawArticleUIDs($number, $start);
		
		$offsets = $this->getOffsets($uids);
		t3lib_div::devlog('$offsets', 'newspaper', 0, $offsets);			
		
		$articles = array();
		foreach ($uids as $uid) {
			$articles[] = new tx_newspaper_Article($uid);
		}
		
		return $articles;
	}
	
	/** \param $PA \code array(
	 *    altName => 
	 * 	  palette =>
	 * 	  extra => 
	 * 	  pal => 
	 * 	  fieldConf => array (
	 * 		exclude =>
	 * 		label => LLL:EXT:newspaper/locallang_db.xml:tx_newspaper_articlelist_semiautomatic.articles
	 * 		config => array(
	 * 		  type => user
	 * 		  internal_type => db
	 * 		  allowed => tx_newspaper_article
	 * 		  size => 10
	 * 		  minitems => 0
	 * 		  maxitems => 100
	 * 		  MM => tx_newspaper_articlelist_semiautomatic_articles_mm
	 * 		  userFunc => tx_newspaper_articlelist_semiautomatic->displayListedArticles
	 * 		  form_type => user
	 * 		)
	 * 	  )
	 * 	  fieldTSConfig => 
	 * 	  itemFormElName => 
	 *    itemFormElName_file => 
	 *    itemFormElValue => 
	 * 	  itemFormElID => 
	 * 	  onFocus => 
	 * 	  label => 
	 * 	  fieldChangeFunc => array(
	 * 		TBE_EDITOR_fieldChanged => 
	 * 		alert => 
	 * 	  )
	 * 	  table => tx_newspaper_articlelist_semiautomatic
	 * 	  field => articles
	 * 	  row => array(
	 * 		uid =>
	 * 		... (other attributes of tx_newspaper_articlelist_semiautomatic)
	 * 	  )
	 * 	  pObj =>
	 *  )\endcode
	 */
	public function displayListedArticles($PA, $fobj) {
		$current_artlist = new tx_newspaper_ArticleList_Semiautomatic($PA['row']['uid']);
		$uids = $current_artlist->getRawArticleUIDs(0, $current_artlist->getAttribute('num_articles'));
		$offsets = $current_artlist->getOffsets($uids);
		$articles = array();
		foreach ($uids as $uid) {
			$articles[] = array(
				'article' => new tx_newspaper_Article($uid),
				'offset' => intval($offsets[$uid])
			);
		}
		
		$articles_sorted = $current_artlist->sortArticles($articles);
		
		$view_articles = array();
		return t3lib_div::view_array($articles).'->'.t3lib_div::view_array($articles_sorted);
	}
	
	static public function getModuleName() { return 'np_al_semiauto'; }

	////////////////////////////////////////////////////////////////////////////
		
	private function getRawArticleUIDs($number, $start = 0) {
		$results = tx_newspaper::selectRows(
			'uid', 'tx_newspaper_article', $this->getAttribute('sql_condition')
		);
		$uids = array();
		foreach ($results as $result) {
			if (intval($result['uid'])) $uids[] = intval($result['uid']);
		}
		t3lib_div::devlog('$uids', 'newspaper', 0, $uids);	
		return $uids;
	}
	
	private function getOffsets($uids) {
		$results = tx_newspaper::selectRows(
			'uid_foreign, offset',
			'tx_newspaper_articlelist_semiautomatic_articles_mm',
			'uid_local = ' . intval($this->getUid()) . 
			' AND uid_foreign IN (' . implode(',', $uids) . ')'
		);
		
		$offsets = array();
		foreach	($results as $result) {
			if (intval($result['uid_foreign']) && intval($result['offset']))
				$offsets[intval($result['uid_foreign'])] = intval($result['offset']);
		}
		t3lib_div::devlog('$offsets', 'newspaper', 0, $offsets);
		return $offsets;
	}
	
	/** \param $articles array(
	 * 		array(
	 * 			'article' => tx_newspaper_Article object
	 * 			'offset' => offset to move article up or down in array
	 * 		)
	 * ...)
	 *  \return $articles sorted, taking offsets into account
	 *  \attention repeatedly calling this function will garble the results!
	 */
	private function sortArticles($articles) {
		$new_articles = array();
		foreach ($articles as $i => $article) {
			$article->getAttribute('uid');
			$new_articles[$i-$article['offset']] = $article;
		}
		ksort($new_articles);
		return $new_articles;
	}
	
	static protected $table = 'tx_newspaper_articlelist_semiautomatic';	///< SQL table for persistence
}

tx_newspaper_ArticleList::registerArticleList(new tx_newspaper_ArticleList_Semiautomatic());

?>