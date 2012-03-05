<?php
/**
 *  \file class.tx_newspaper_articlelist_manual.php
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

/// A list of tx_newspaper_Article s ordered manually
/** This tx_newspaper_ArticleList contains Articles which have been manually 
 *  added to the list and manually placed.
 * 
 *  The selection of articles from which the list can be filled in the BE is 
 *  done by the attributes \c filter_section, \c filter_tags_include,
 *  \c filter_tags_exclude, \c filter_sql_table and \c filter_sql_where. The
 *  order in which the article selection is displayed can be determined by
 *  \c filter_sql_order_by.
 * 
 * \todo indexOfArticle(), isSectionList(), insertArticleAtPosition()
 */
class tx_newspaper_ArticleList_Manual extends tx_newspaper_ArticleList {

	/// SQL table storing the relations between list and articles
	const mm_table = 'tx_newspaper_articlelist_manual_articles_mm';
	const article_table = 'tx_newspaper_article';

	/// Returns a number of tx_newspaper_Article s from the list
	/** @param $number Number of Articles to return
	 *  @param $start Index of first Article to return (starts with 0)
	 *  @return tx_newspaper_Article[] The \p $number Articles starting with \p $start
	 */
	public function getArticles($number, $start = 0) {
/*
SELECT tx_newspaper_articlelist_manual_articles_mm.uid_foreign
FROM tx_newspaper_articlelist_manual_articles_mm
JOIN tx_newspaper_article ON tx_newspaper_articlelist_manual_articles_mm.uid_foreign = tx_newspaper_article.uid
WHERE
uid_local = ${articlelist_uid} AND hidden = 0
ORDER BY sorting ASC
LIMIT 0, 10
 */				

        $timer = tx_newspaper_ExecutionTimer::create("Manual ArticleList(" . $this->getUid() . ")::getArticles($number)");

		$results = tx_newspaper::selectRows(
				$this->select_method_strategy->fieldsToSelect(),
				self::mm_table . 
					' JOIN ' . self::article_table . 
					' ON ' . self::mm_table . '.uid_foreign = ' . self::article_table . '.uid',
				'uid_local = ' . intval($this->getUid()) . 
					tx_newspaper::enableFields(self::article_table),
				'',
				self::mm_table . '.sorting ASC',
				"$start, $number"
		);
		
		$articles = array();
		foreach ($results as $row) {
            $articles[] = $this->select_method_strategy->createArticle($row);
		}
		
		return $articles;
	}

	function assembleFromUIDs(array $uids) {

        $timer = tx_newspaper_ExecutionTimer::create();

		$this->clearList();
		for($i = 0; $i < sizeof($uids); $i++) {
//			t3lib_div::devlog('assembleFromUIDs()', 'newspaper', 0, array('uids' => $uids));
            if (!intval($uids[$i])) {
				throw new tx_newspaper_InconsistencyException(
					'Manual article list needs UID array to consist of integers,
					 but no int was given: ' . $uids[$i]
				);				
			}
			$this->insertArticleAtPosition(new tx_newspaper_Article($uids[$i]), $i);
		}

		$this->callSaveHooks();

	}
		
	public function insertArticleAtPosition(tx_newspaper_ArticleIface $article, $pos = 0) {
		$this->deleteArticle ($article);

        $ids = array();
		foreach ($this->getArticles($this->getAttribute('num_articles')) as $i => $present_article) {
			if ($i >= $pos) {
                $ids[] = $present_article->getUid();
            }
        }

		tx_newspaper::updateRows(
		    self::mm_table,
			'uid_local = ' . intval($this->getUid()) .
			' AND uid_foreign IN (' . join(', ', $ids) .')',
			array ('sorting' => 'sorting + 1')
		);

		tx_newspaper::insertRows(
			self::mm_table,
			array(
				'uid_local' =>  intval($this->getUid()),
				'uid_foreign' => $article->getUid(),
				'sorting' => $pos+1
			)
		);
	}

	public function deleteArticle(tx_newspaper_ArticleIface $article) {
		tx_newspaper::deleteRows(
			self::mm_table,
			'uid_local = ' . intval($this->getUid()) .
				' AND uid_foreign = ' . $article->getUid()
		);
	}

	public function moveArticle(tx_newspaper_ArticleIface $article, $offset) {
		$this->insertArticle(max(0, $this->getArticlePosition($article)+$offset));
	}

	static public function getModuleName() { return 'np_al_manual'; }
	
	///	Remove all articles from the list.
	protected function clearList() {
		tx_newspaper::deleteRows(self::mm_table,
								 'uid_local = ' . intval($this->getUid()));
	}

	static protected $table = 'tx_newspaper_articlelist_manual';	///< SQL table for persistence
}

tx_newspaper_ArticleList::registerArticleList(new tx_newspaper_ArticleList_Manual());

?>