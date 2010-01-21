<?php
/**
 *  \file class.tx_newspaper_articlelist_semiautomatic.php
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

/// A list of tx_newspaper_Article s dynamically filled and optionally reordered by the user.
/** The Articles contained in the list are automatically determined by the
 *  filter attributes. If the user doesn't interact, these Articles are
 *  displayed fully automatic.
 * 
 *  The user can move Articles up and down in the list, though. By moving
 *  Articles up by a large amount, past the top Article, an Article can be made
 *  "sticky". By moving it down a large amount an Article can be made to
 *  disappear from the list. 
 * 
 *  The selection of articles from which the list id filled dynamically is 
 *  done by the attributes \c filter_section , \c filter_tags_include ,
 *  \c filter_tags_exclude , \c filter_sql_table  and
 *  \c filter_sql_where . The order in which the article selection is
 *  displayed can be determined by \c filter_sql_order_by .
 *
 *  The manual reordering is stored with the MM relation table,
 *  \c tx_newspaper_articlelist_semiautomatic_articles_mm .
 *  Articles which have not been manually reordered don't have an entry in the 
 *  MM table. For moved articles the value \c offset  is stored in the MM
 *  table. Every article recorded in the MM table is moved \c offset 
 *  places up (or down, if \c offset  is negative) in the list. 
 *  \c offset  can be greater than the length of the list, making articles
 *  sticky, or moving them off the end of the list.
 * 
 *  \todo indexOfArticle(), isSectionList(), insertArticleAtPosition()
 *  \todo I'm not certain if the number of articles in the list is correct when
 * 		articles have been dropped from the list.
 *  \todo There is no BE which allows reordering articles - currently it's all
 * 		done with PHPMyAdmin. This would require either some AJAX magic or a
 * 		function in the save hook (tx_newspaper_Typo3Hook), or both.
 */
class tx_newspaper_ArticleList_Semiautomatic extends tx_newspaper_ArticleList {

	///	Offset behind top position with which an article counts as deleted.
	const offset_deleted = 1000;
	
	/// SQL table storing the relations between list and articles
	const mm_table = 'tx_newspaper_articlelist_semiautomatic_articles_mm';
	
	/// Returns a number of tx_newspaper_Article s from the list
	/** \param $number Number of Articles to return
	 *  \param $start Index of first Article to return (starts with 0)
	 *  \return The \p $number Articles starting with \p $start
	 */
	public function getArticles($number, $start = 0) {
		
		$articles_sorted = $this->getSortedArticles($number, $start);
		
		$articles = array();
		foreach ($articles_sorted as $i => $article) {
#			t3lib_div::devlog('article '.$i, 'newspaper', 0, $article);
			$articles[] = $article['article'];
		}
		
		return $articles;
	}

	function assembleFromUIDs(array $uids) {
		$this->clearList();
		for($i = 0; $i < sizeof($uids); $i++) {
			if (!is_array($uids[$i]) || sizeof($uids[$i]) < 2) {
				throw new tx_newspaper_InconsistencyException(
					'Semiautomatic article list needs UID array to have members
					 of the form: array(uid, offset), but no array was given: ' .
					 print_r($uids[$i])
				);
			}
			$article = new tx_newspaper_Article($uids[$i][0]);
			$this->insertArticleAtPosition($article, $i);
			if ($this->getOffset($article) != $uids[$i][1]) {
				
				tx_newspaper::updateRows(
					self::mm_table,
					'uid_local = ' . intval($this->getUid()) . 
						' AND uid_foreign = ' . $article->getUid(),
					array('offset' => $uids[$i][1])
				);
			}
		}
	}
	
	/// User function called from the BE to display the articles on the list
	/** Also, to sort articles on the list up and down.
	 * 
	 *  This function is called by TCEForms with a default-constructed article
	 *  list object. Therefore it must create its own article list, the UID of
	 *  which it reads from \p $PA.
	 * 
	 *  \param $PA Array: \code array(
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
	 * 
	 *  \param $fobj reference to the parent object (instance of t3lib_TCEforms)
	 * 
	 *  \return List of articles currently on the list, with controls to 
	 *  	rearrange the articles
	 */
	public function displayListedArticles($PA, $fobj) {
		global $LANG;

		if (intval($PA['row']['uid'] == 0)) {
			// probably a new record
			return $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_tx_newspaper_articlelist_unsaved', false);
		}

		$current_artlist = new tx_newspaper_ArticleList_Semiautomatic(intval($PA['row']['uid']));

		$articles_sorted = $current_artlist->getSortedArticles($current_artlist->getAttribute('num_articles'));
//		t3lib_div::devlog('articles', 'newspaper', 0, array('articles_sorted' => $articles_sorted));

		if (true) {
	 	 	$smarty = new tx_newspaper_Smarty();
			$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/res/be/templates'));
			$smarty->assign('articles', $articles_sorted);
			$smarty->assign('message_empty', $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_tx_newspaper_articlelist_unsaved', false));
	
			return $smarty->fetch('tx_newspaper_articlelist_semiautomatic.tmpl');
		} else {
			return $this->renderPlacement(array());
		}
	}
	
	/// render the placement editors according to sections selected for article
	/** in comparison the the displayed ones in the form
		\param $input \c t3lib_div::GParrayMerged('tx_newspaper_mod7')
		\return ?
	*/
	function renderPlacement ($input) {
		$selection = $input['sections_selected'];
		
		$tree = array(array(array(array('articlelist'=> $this))));
		/*
		// calculate which / how many placers to show
		$tree = $this->calculatePlacementTreeFromSelection($selection);
		t3lib_div::devlog('tree 1', 'newspaper', 0, $tree);
		// grab the data for all the places we need to display
		$tree = $this->fillPlacementWithData($tree, $input['placearticleuid']);
		t3lib_div::devlog('tree 2', 'newspaper', 0, $tree);
		*/		
		// get ll labels 
		$localLang = t3lib_div::readLLfile('typo3conf/ext/newspaper/mod7/locallang.xml', $GLOBALS['LANG']->lang);
		$localLang = $localLang[$GLOBALS['LANG']->lang];	
										
		// render
		$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod7/res/'));					
		$smarty->assign('tree', $tree);
		$smarty->assign('lang', $localLang);
		$smarty->assign('iscod', $this->userIsChiefOfDuty());
		return $smarty->fetch('mod7_placement.tpl');
	}
	
				/// calculate a "minimal" (tree-)list of sections
				/**
				 * 
				 */
				function calculatePlacementTreeFromSelection ($selection) {
					$result = array();
										
					for ($i = 0; $i < count($selection); ++$i) {
						$selection[$i] = explode('|', $selection[$i]);
						$ressort = array();
						for ($j = 0; $j < count($selection[$i]); ++$j) {
							$ressort[]['uid'] = $selection[$i][$j];
							if(!isset($result[$j]) || !in_array($ressort, $result[$j])) {
								$result[$j][] = $ressort;
							}
						}
					}
					
					return $result;
				}

				/// get article and offset lists for a set of sections
				/**
				 * 
				 */
				function fillPlacementWithData ($tree, $articleId) {
					for ($i = 0; $i < count($tree); ++$i) {
						for ($j = 0; $j < count($tree[$i]); ++$j) {
							for ($k = 0; $k < count($tree[$i][$j]); ++$k) {
								// get data (for title display) for each section
								$tree[$i][$j][$k]['section'] = new tx_newspaper_section($tree[$i][$j][$k]['uid']);
								// add article list and list type for last element only to tree structure
								if (($k+1) == count($tree[$i][$j])) {
									$tree[$i][$j][$k]['listtype'] = get_class($tree[$i][$j][$k]['section']->getArticleList());
									$tree[$i][$j][$k]['articlelist'] = $this->getArticleListBySectionId ($tree[$i][$j][$k]['uid'], $articleId);
								}
							}
						}
					}
					return $tree;
				}
	
				function userIsChiefOfDuty () {
					// @todo: use later
					// return tx_newspaper::isChief();
					return true;
				}
	
	
	public function insertArticleAtPosition(tx_newspaper_ArticleIface $article, $pos = 0) {
		
		$articles = $this->getArticles($this->getAttribute('num_articles'));
		
		//  If article already in list, move it to position $pos
		foreach ($articles as $i => $present_article) {
			if ($article->getUid() == $present_article->getUid()) {
				tx_newspaper::updateRows(
					self::mm_table,
					'uid_local = ' . intval($this->getUid()) .
						' AND uid_foreign = ' . $present_article->getUid(),
					array (
						'offset' => 'offset' . ($pos < $i? (' + ' . $i-$pos): (' - ' . $pos - $i))
					)
				);
				return;
			}
		}
		
		throw new tx_newspaper_ArticleNotFoundException($article->getUid());
	}
	
	public function deleteArticle(tx_newspaper_ArticleIface $article) {
		$this->insertArticleAtPosition($article, self::offset_deleted);
	}

	public function moveArticle(tx_newspaper_ArticleIface $article, $offset) {
		$this->insertArticle(max(0, $this->getArticlePosition($article)+$offset));
		if ($this->getArticlePosition($article)+$offset < 0) {
			tx_newspaper::updateRows(
				self::mm_table,
				'uid_local = ' . intval($this->getUid()) .
					' AND uid_foreign = ' . $present_article->getUid(),
				array (
					'offset' => 'offset - ' . abs($this->getArticlePosition($article)+$offset)
				)
			);
		}
	}
	
	/// Tells how much an Article is moved from its chronological list position
	/** \param $article Article whose offset is required
	 *  \return The offset of \p $article relative to its original position in
	 *  	the list. If \p $article is not in the list, returns zero.
	 */
	public function getOffset(tx_newspaper_ArticleIface $article) {
		$offset = $this->getOffsets(array($article->getUid()));
		if (sizeof($offset) < 1) return 0;
		return intval($offset[$article->getUid()]);
	}

	/// A short description that makes an Article List identifiable in the BE
	public function getDescription() {
		
		global $LANG;
		
		$ret = $this->getTitle();
		
		if ($this->getAttribute('filter_sections')) {
			$ret .= "<br />\n" . 
				$LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_articlelist_included_sections', false) . ':';
			$sep = '';
			foreach (explode(',', $this->getAttribute('filter_sections')) as $section_uid) {
				$section = new tx_newspaper_Section($section_uid);
				$ret .=  $sep . $section->getAttribute('section_name');
				$sep = ', ';
			}
		}
		
		if ($this->getAttribute('filter_tags_include')) {
			$ret .= "<br />\n" . 
				$LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_articlelist_included_tags', false) . ':';
			$sep = '';
			foreach (explode(',', $this->getAttribute('filter_tags_include')) as $tag_uid) {
				$ret .= new tx_newspaper_Tag($tag_uid);
				$sep = ', ';
			}
		}

		if ($this->getAttribute('filter_tags_exclude')) {
			$ret .= "<br />\n" . 
				$LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_articlelist_excluded_tags', false) . ':';
			$sep = '';
			foreach (explode(',', $this->getAttribute('filter_tags_exclude')) as $tag_uid) {
				$ret .= new tx_newspaper_Tag($tag_uid);
				$sep = ', ';
			}
		}

		if ($this->getAttribute('filter_articlelist_exclude')) {
			$ret .= "<br />\n" . 
				$LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_articlelist_excluded_articlelist', false) . ':';
			$articlelist = tx_newspaper_ArticleList_Factory::getInstance()->create($this->getAttribute('filter_articlelist_exclude'));
			$ret .= $articlelist->getDescription();
		}

		if($this->getAttribute('notes')) { 
			$ret .= "<br />\n" . $this->getAttribute('notes');
		}
		
		return $ret;
	}
	
	static public function getModuleName() { return 'np_al_semiauto'; }

	////////////////////////////////////////////////////////////////////////////

	///	Remove all articles from the list.
	/** Deletes all articles that are presently on the list. Older articles are
	 *  untouched.
	 */
	protected function clearList() {		
		$article_uids = array();
		foreach ($this->getArticles($this->getAttribute('num_articles')) as $article) {
			$article_uids[] = $article->getUid();
		}
		if ($article_uids) tx_newspaper::deleteRows(self::mm_table, $article_uids);
	}
	
	/// Get the articles sorted by their offsets, including offset values
	/** \param $number Number of articles to return
	 *  \param $start Index of first article sought
	 *  \return \code array(
	 * 	  array(
	 * 		'article' => tx_newspaper_Article object
	 * 		'offset' => original offset
	 * 	  ),
	 * 	  ...
	 * )
	 *  \endcode
	 */
	private function getSortedArticles($number, $start = 0) {
		
		/*	Because articles may be moved off the bottom of the list, we need a
		 *  safety margin. Twice as many articles as required should be enough.
		 */
		$uids = $this->getRawArticleUIDs(2*$number, $start);
		
		$offsets = $this->getOffsets($uids);
		
		$articles = array();
		foreach ($uids as $uid) {
			$articles[] = array(
				'article' => new tx_newspaper_Article($uid),
				'offset' => intval($offsets[$uid])
			);
		}

		$articles_sorted = $this->sortArticles($articles);
#t3lib_div::devlog('sorted articles', 'newspaper', 0, $articles_sorted);

		return array_slice($articles_sorted, 0, $number);
	}

	/// Get the UIDs of articles found by the conditions defining the list
	/** \param $number Number of articles to return
	 *  \param $start Index of first article sought
	 *  \return array of UIDs in the order in which they should appear
	 * 
	 *  \todo Factor out the code to create the SQL statement so it can be used
	 *  	as filter for tx_newspaper_ArticleList_Manual. Possibly factor out
	 * 		the code to subtract another ArticleList too.
	 *  \todo Figure out how to preview hidden articles
	 */
	public function getRawArticleUIDs($number, $start = 0) {

		$table = $this->getAttribute('filter_sql_table');
		if (!$table) $table = 'tx_newspaper_article';
		
		$where = $this->getAttribute('filter_sql_where');
		if (!$where) $where = '1';
		t3lib_div::devlog('','',0,$where);
		if (strpos($where, '$') !== false) $where = self::expandGETParameter($where);
		$where .= tx_newspaper::enableFields('tx_newspaper_article', (TYPO3_MODE == 'BE'));
		
		if ($this->getAttribute('filter_sections')) {
			$sections = array();
			foreach (explode(',', $this->getAttribute('filter_sections')) as $section_uid) {
				$sections[] = $section_uid;
				//	add subsections to search clause
				$section = new tx_newspaper_Section($section_uid);
				$child_sections = $section->getChildSections(true);
				if ($child_sections) {
					foreach ($child_sections as $child_section) {
						$sections[] = $child_section->getUid();
					}
				}
			}
			$table .= ' JOIN tx_newspaper_article_sections_mm' .
					  '   ON tx_newspaper_article.uid = tx_newspaper_article_sections_mm.uid_local';

			$where .= ' AND tx_newspaper_article_sections_mm.uid_foreign IN (' . 
							implode(', ', $sections) . ')';
		}
		
		if ($this->getAttribute('filter_tags_include')) {

			$table .= ' JOIN tx_newspaper_article_tags_mm' .
					  '   ON tx_newspaper_article.uid = tx_newspaper_article_tags_mm.uid_local';

			$where .= ' AND tx_newspaper_article_tags_mm.uid_foreign IN (' . 
							$this->getAttribute('filter_tags_include') . ')';
		}
		
		if ($this->getAttribute('filter_tags_exclude')) {

			$table .= ' JOIN tx_newspaper_article_tags_mm' .
					  '   ON tx_newspaper_article.uid = tx_newspaper_article_tags_mm.uid_local';

			$where .= ' AND tx_newspaper_article_tags_mm.uid_foreign NOT IN (' . 
							$this->getAttribute('filter_tags_exclude') . ')';
		}

		/// \todo: Implement \p filter_articlelist_exclude. This must be done separately from the SQL query.
		
		try {
			$results = tx_newspaper::selectRows(
				'DISTINCT tx_newspaper_article.uid', 
				$table,
				$where,
				'',
				$this->getAttribute('filter_sql_order_by'),
				intval($start) . ', ' . intval($number)
			);
		} catch (tx_newspaper_DBException $e) {
			//  This guards agains article lists which use GET varaiables, 
			//	which are not set in the BE
			$results = array();	
		}
#t3lib_div::devlog('tx_newspaper::$query', 'newspaper', 0, array(tx_newspaper::$query, $results));

		$uids = array();
		foreach ($results as $result) {
			if (intval($result['uid'])) $uids[] = intval($result['uid']);
		}

		return $uids;
	}
	
	/// Get all offsets for the supplied UIDs
	/** \param $uids The UIDs for which to look up the offsets
	 *  \return \code array(
	 *    $uid => $offset,
	 * 	  ...
	 *  ) \endcode
	 */
	public function getOffsets(array $uids) {
		
		if (!$uids) return array();
		
		$results = tx_newspaper::selectRows(
			'uid_foreign, offset',
			self::mm_table,
			'uid_local = ' . intval($this->getUid()) . 
			' AND uid_foreign IN (' . implode(',', $uids) . ')'
		);
		
		$offsets = array();
		foreach ($uids as $uid) {
			foreach	($results as $result) {
				if (intval($result['uid_foreign']) == intval($uid)) {
					$offsets[intval($uid)] = $result['offset'];
					break;
				} 
			}
			if (!isset($offsets[intval($uid)])) $offsets[intval($uid)] = 0;
 		}

		return $offsets;
	}
	
	/// Sort articles, taking their offsets into account
	/** \param $articles array(
	 * 		array(
	 * 			'article' => tx_newspaper_Article object
	 * 			'offset' => offset to move article up or down in array
	 * 		)
	 * ...)
	 *  \return $articles sorted, taking offsets into account
	 *  \attention repeatedly calling this function will garble the results!
	 */
	private function sortArticles(array $articles) {
		$new_articles = array();
		foreach ($articles as $i => $article) {
			$article['article']->getAttribute('uid');
			$new_index = $i-$article['offset'];
			if (isset($new_articles[$new_index])) {
				/*  if the new index is already populated, we need to shift 
				 *  every article at and after that index one place down,  
				 *  starting with the last.
				 */
				$keys = array_keys($new_articles);
				rsort($keys);
				foreach($keys as $old_index) {
					if ($old_index < $new_index) break;
					$new_articles[$old_index+1] = $new_articles[$old_index];
					unset($new_articles[$old_index]);
				}
			}
			$new_articles[$new_index] = $article;
		}
		ksort($new_articles);
		return $new_articles;
	}
	
	///	Replace a substring denoted as a variable with the corresponding GET parameter
	/** For example, all occurrences of \c $art are replaced with 
	 *  \c $)GET['art']. If \c $)GET['art'] is not set, the variable is
	 *  unchanged.
	 * 
	 *  \param $string The string to be expanded.
	 *  \return The expanded string.
	 */
	private static function expandGETParameter($string) {
		$matches = array();
		
		if (!preg_match_all('/\$(.*)\w/', $string, $matches)) return $string;
		
		//	full matches are in $matches[0], partial ones in $matches[1] and so on
		foreach ($matches[0] as $match) {
			$var = substr($match, 1);	//  lose the '$'
			if ($_GET[$var]) $string = str_replace($match, $_GET[$var], $string); 
		}

		return $string;
	}
	
	static protected $table = 'tx_newspaper_articlelist_semiautomatic';	///< SQL table for persistence
	
}

tx_newspaper_ArticleList::registerArticleList(new tx_newspaper_ArticleList_Semiautomatic());

?>