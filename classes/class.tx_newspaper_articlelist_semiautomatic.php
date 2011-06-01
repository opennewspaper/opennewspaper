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

/// Operation which changes an article's place in a list
class tx_newspaper_Articlelist_Operation {
    
	/** \param $uid The article's UID.
	 *  \param $operation This can either be an integer, describing how much the
	 *         article should be sorted up or down, or the keywords 'top' or
	 *         'bottom', to sort the article to the top or bottom of the list. 
	 */
	public function __construct($uid, $operation) {
		self::checkUIDValid($uid);
    	$this->article_uid = $uid;
    	
    	self::checkOperationValid($operation);
    	$this->operation = $operation;
    }
    
    public function __toString() {
    	$ret = 'Sort article ' . $this->getUid() . ' ';
    	if ($this->shuffleValue()) {
    		$ret .= abs($this->shuffleValue());
    		if ($this->shuffleValue() > 0) {
    			$ret .= ' up';
    		} else {
    			$ret .= ' down';
    		}
    	} else {
    		$ret .= 'to ' . $this->operation;
    	}
    	return $ret;
    }
    
    public function getUid() { return $this->article_uid; }
    
    public function isToTop() { return self::isTopString($this->operation); }
    public function isToBottom() { return self::isBottomString($this->operation); }
    public function shuffleValue() { return intval($this->operation); }
    
    private static function checkUIDValid($uid) {
        if (!intval($uid)) {
            throw new tx_newspaper_IllegalUsageException('UID must be an integer value');
        }
    }
    
    private static function checkOperationValid($operation) {
        if (intval($operation)) {
            if (abs($operation) != 1) {
                throw new tx_newspaper_IllegalUsageException('Only movements of +/- 1 are supported.');
            }
            return;
        }
        if (self::isTopString($operation)) return;
        if (self::isBottomString($operation)) return;
                
        throw new tx_newspaper_IllegalUsageException(
            'Operation must be either an integer value or one of the strings "' .
            self::TOP_STRING . '" or "' . self::BOTTOM_STRING .'"'
        );
    }
    
    private static function isTopString($operation) {
    	return (stripos($operation, self::TOP_STRING) === 0);
    }
    
    private static function isBottomString($operation) {
    	return (stripos($operation, self::BOTTOM_STRING) === 0);
    }
    
    private $article_uid;
    private $operation;
    
    const TOP_STRING = 'top';
    const BOTTOM_STRING = 'bottom';
    
    const NUM_RAW_UIDS = 10;
}

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
	
	/// default number for articles (if not set (properly) in article list record).
	const default_num_articles = 20;
	
	/// how many articles are loaded for comparison of offsets
	const num_raw_uids = 100;
	
	/// SQL table storing the relations between list and articles.
	const mm_table = 'tx_newspaper_articlelist_semiautomatic_articles_mm';
	
	/// Whether articles from subsections should be recursively included in the list.
	const include_articles_from_subsections = false;
	
	/// Whether to write information about sorting operations to devlog.
	const debug_resort_operations = false;
	
	/// Construct a tx_newspaper_ArticleList_Semiautomatic.
	/** This constructor is used to set the default section filter
	 *  \param $uid UID of the record in the corresponding SQL table
	 *  \param $section tx_newspaper_Section to which this ArticleList is
	 * 		bound.
	 */
	public function __construct($uid = 0, tx_newspaper_Section $section = null) {
		tx_newspaper_ArticleList::__construct($uid, $section);
		if ($uid == 0 && $section) {
			// set default filter
			// currently sections are stored as comma separated list, so init with current secton uid is working (won't work with mm relations)
			$this->setAttribute('filter_sections', $section->getUid());
		}
	}
	
	
	/// Returns a number of tx_newspaper_Article s from the list
	/** \param $number Number of Articles to return
	 *  \param $start Index of first Article to return (starts with 0)
	 *  \return The \p $number Articles starting with \p $start
	 */
	public function getArticles($number, $start = 0) {
		$articles_sorted = $this->getSortedArticles($number, $start);

		$articles = array();
		foreach ($articles_sorted as $i => $article) {
			$articles[] = $article['article'];
		}

		return $articles;
	}

	/// Makes a persistent article list from an array of UIDs with their respective offsets.
	/** \param $uids \code array (
	 *     array($article_uid, $article_offset),
	 *     ...
	 * ) \endcode
	 */
	public function assembleFromUIDs(array $uids) {
        t3lib_div::devlog('assembleFromUIDs()', 'newspaper', 0, $uids);
		
		$this->clearList();

		foreach ($uids as $uid) {
						
            self::checkArticleOffsetValidity($uid);

			$offset = intval($uid[1]);
			if ($offset == 0) continue;
			
			tx_newspaper::insertRows(
				self::mm_table,
				array('uid_local' => intval($this->getUid()), 
					'uid_foreign' =>  $uid[0],
					'offset' => $offset)
			);

		}

		$this->callSaveHooks();
		
	}
	
	/// Given a list of articles and an operation on one of those, returns a new ordered list.
	/** This function, in contrast to \c assembleFromUIDs(), does not store the 
	 *  article list. It is used from an AJAX conroller to resort the articles in
	 *  the list in correct order.
	 * 
	 * \param $old_order Previous article list.
	 * \param $operation Sorts an article up, down, to top or bottom.
	 * \return The sorted article list after \p $operation has been applied.
	 */
	public function resort(array $old_order, tx_newspaper_Articlelist_Operation $operation) {
		
		try {
			$index = self::indexOfArticle($operation->getUid(), $old_order);
		} catch (tx_newspaper_Exception $e) {
			// article not in list; do nothing.
			return;
		}

		if ($operation->shuffleValue()) {
           	$this->resortArticle($operation->getUid(), $operation->shuffleValue(), $old_order);
        } else if ($operation->isToTop()) {
           	$this->sortArticleToTop($index, $old_order);
        } else if ($operation->isToBottom()) {
           	$this->dropArticle($index, $old_order);
        } else {
        	throw new tx_newspaper_IllegalUsageException('WTF is that: ' . $operation);
        }

        return $old_order;
	}
	
	/// Checks whether an array is a valid pair of article UID and offset.
	private static function checkArticleOffsetValidity($article_offset) {
        if (!is_array($article_offset) || sizeof($article_offset) < 2) {
            throw new tx_newspaper_InconsistencyException(
                'Semiautomatic article list needs UID array to have members
                  of the form: array(uid, offset), but no array was given: ' .
                 print_r($article_offset)
            );
        }
	}
	
	/// Finds the position of article with UID \p $uid in the array \p $old_order.
	private static function indexOfArticle($uid, array $old_order) {
	    for ($i = 0; $i < sizeof($old_order); $i++) {
            $article_offset = $old_order[$i];

            self::checkArticleOffsetValidity($article_offset);
            
            $current_uid = $article_offset[0];
	    	if ($current_uid == $uid) return $i;

			if ($article_offset['article'] instanceof tx_newspaper_Article) {
	            $current_uid = $article_offset['article']->getUid();
		    	if ($current_uid == $uid) return $i;
			}
	    }
	    throw new tx_newspaper_Exception('UID ' . $uid . ' not found in ' . print_r($old_order, 1));
	}
	
	/// Moves article at position \p $index in array \p $old_order \p $shuffle_value positions up or down.
	private function resortArticle($uid, $shuffle_value, array &$old_order) {
        if (abs($shuffle_value) != 1) {
			throw new tx_newspaper_IllegalUsageException('Only movements of +/- 1 are supported.');
		}

		$articles = $this->getArticlesAndOffsets(self::num_raw_uids);

		foreach ($old_order as $old_article) {
			$index = self::indexOfArticle($old_article[0], $articles);
			$articles[$index]['offset'] = $old_article[1];
		}

		$index = self::indexOfArticle($uid, $articles);

		$articles[$index]['offset'] += $shuffle_value;

		$temp_order = $this->sortArticles($articles);
		
		$old_order = array();
		foreach ($temp_order as $temp_article_offset) {
			$old_order[] = array(
				$temp_article_offset['article']->getUid(),
				$temp_article_offset['offset']
			);
		}
		
	}
	
	private static function swap(array &$old_order, $index, $new_index) {
        $temp = $old_order[$index];
        $old_order[$index] = $old_order[$new_index];            
        $old_order[$new_index] = $temp;
	}
	
	/// Makes sure an index does not point outside the array. 
	private static function generateValidIndex($prospective_index) {
		if ($prospective_index >= 0) return $prospective_index;
		return 0;
	}
	
	/// How far are article/offset combinations \p $entry1 and \p $entry2 really apart?
	private function distance(array $entry1, array $entry2) {
        
		$this->getRawUids();
		
		$uid1 = $entry1[0]; $uid2 = $entry2[0];
		$offset1 = $entry1[1]; $offset2 = $entry2[1];
		
        $raw_index1 = array_search($uid1, $this->raw_uids);
        $raw_index2 = array_search($uid2, $this->raw_uids);
        
        $actual_index1 = $raw_index1-$offset1;
        $actual_index2 = $raw_index2-$offset2;
        
        return $actual_index2 - $actual_index1;
	}
    
	/// Ensures that the list's raw UIDs are stored in \c $this->raw_uids.
    private function getRawUids() {
        if (!$this->raw_uids) {
          $this->raw_uids = $this->getRawArticleUIDs(self::num_raw_uids, 0);
        }
    }
	
	/// Moves article at position \p $index in array \p $old_order to the first position.
	/** \todo Consider sticky articles at the (former) top of the list. */
    private function sortArticleToTop($index, array &$old_order) {
    	$distance = $this->distance($old_order[0], $old_order[$index]);
        self::moveArticleTemporarily($index, $old_order, $distance);
    }
    
    /// Removes article at position \p $index in array \p $old_order from the list.
    private function dropArticle($index, array &$old_order) {
    	self::moveArticleTemporarily($index, $old_order, -self::offset_deleted);
        //moveArticleTemporarily adds article on top but should be at the bottom
        $entry = array_shift($old_order);
        $old_order[] = $entry;
    }
    
    /// Moves an article \p $offset positions away from its current position in \p $old_order.
    private static function moveArticleTemporarily($index, array &$old_order, $offset) {
        $entry = $old_order[$index];
        $entry[1] += $offset;
        unset($old_order[$index]);
        array_unshift($old_order, $entry);
    }
    
	/// Updates or insert a record with the corresponding offset. 
	private function updateOffset($uid_local, $uid_foreign, $offset) {
		if (tx_newspaper::updateRows(
			self::mm_table,
			'uid_local = ' . intval($uid_local) . 
				' AND uid_foreign = ' . $uid_foreign,
			array('offset' => $offset)
		)) {
			return; // record was successfully updated
		}

		// no record was updated, so write a new one
		tx_newspaper::insertRows(
			self::mm_table,
			array(
				'uid_local' => intval($uid_local), 
				'uid_foreign' => intval($uid_foreign),
				'offset' => $offset
			)
		);	

	}
	
	/// User function called from the BE to display the articles on the list.
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

		if (intval($PA['row']['uid']) == 0) {
			// probably a new record
			return tx_newspaper::getTranslation('message_tx_newspaper_articlelist_unsaved');
		}

		$current_artlist = new tx_newspaper_ArticleList_Semiautomatic(intval($PA['row']['uid']));

		$articles_sorted = $current_artlist->getSortedArticles($current_artlist->getNumArticles());
//t3lib_div::devlog('articles', 'newspaper', 0, array('articles_sorted' => $articles_sorted));

		if (true) {
	 	 	$smarty = new tx_newspaper_Smarty();
			$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/res/be/templates'));
			$smarty->assign('articles', $articles_sorted);
			$smarty->assign('message_empty', tx_newspaper::getTranslation('message_tx_newspaper_articlelist_empty'));
	
			return $smarty->fetch('tx_newspaper_articlelist_semiautomatic.tmpl');
		} else {
			return $this->renderPlacement(array());
		}
	}
	
	/// Renders the placement editors according to sections selected for article.
	/** In comparison to the displayed ones in the form
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
		$smarty->assign('isde', tx_newspaper_workflow::isDutyEditor());
		$smarty->assign('T3PATH', tx_newspaper::getAbsolutePath());
		return $smarty->fetch('mod7_placement_section.tpl');
	}
	
	/// Calculates a "minimal" (tree-)list of sections.
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
	
	/// Get article and offset lists for a set of sections.
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
	
	public function insertArticleAtPosition(tx_newspaper_ArticleIface $article, $pos = 0) {
		
		$articles = $this->getArticles($this->getNumArticles());

		//  If article already in list, move it to position $pos
		foreach ($articles as $i => $present_article) {
			if ($article->getUid() == $present_article->getUid()) {
				$old_offset = $this->getOffset($present_article);
				$new_offset = $old_offset+($i-$pos); 
				$this->updateOffset(
					$this->getUid(), 
					$present_article->getUid(), 
					$new_offset
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

			$old_offset = $this->getOffset($article);
			$new_offset = $old_offset-abs($this->getArticlePosition($article)+$offset);
			$this->updateOffset(
				$this->getUid(), 
				$article->getUid(), 
				$new_offset
			);
		}
	}
	
	/// Tells how much an Article is moved from its chronological list position.
	/** \param $article Article whose offset is required
	 *  \return The offset of \p $article relative to its original position in
	 *  	the list. If \p $article is not in the list, returns zero.
	 */
	public function getOffset(tx_newspaper_ArticleIface $article) {
		$offset = $this->getOffsets(array($article->getUid()));
		if (sizeof($offset) < 1) return 0;
		return intval($offset[$article->getUid()]);
	}

	/// A short description that makes an Article List identifiable in the BE.
	public function getDescription() {
		
		$ret = $this->getTitle();
		
		if ($this->getAttribute('filter_sections')) {
			$ret .= "<br />\n" . 
				tx_newspaper::getTranslation('label_articlelist_included_sections') . ':';
			$sep = '';
			foreach (explode(',', $this->getAttribute('filter_sections')) as $section_uid) {
				$section = new tx_newspaper_Section($section_uid);
				$ret .=  $sep . $section->getAttribute('section_name');
				$sep = ', ';
			}
		}
		
		if ($this->getAttribute('filter_tags_include')) {
			$ret .= "<br />\n" . 
				tx_newspaper::getTranslation('label_articlelist_included_tags') . ':';
			$sep = '';
			foreach (explode(',', $this->getAttribute('filter_tags_include')) as $tag_uid) {
				$ret .= new tx_newspaper_Tag($tag_uid);
				$sep = ', ';
			}
		}

		if ($this->getAttribute('filter_tags_exclude')) {
			$ret .= "<br />\n" . 
				tx_newspaper::getTranslation('label_articlelist_excluded_tags') . ':';
			$sep = '';
			foreach (explode(',', $this->getAttribute('filter_tags_exclude')) as $tag_uid) {
				$ret .= new tx_newspaper_Tag($tag_uid);
				$sep = ', ';
			}
		}

		if ($this->getAttribute('filter_articlelist_exclude')) {
			$ret .= "<br />\n" . 
				tx_newspaper::getTranslation('label_articlelist_excluded_articlelist') . ':';
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
		foreach ($this->getArticles($this->getNumArticles()) as $article) {
			$article_uids[] = $article->getUid();
		}
		if ($article_uids) {
			tx_newspaper::deleteRows(
				self::mm_table, 
				$article_uids, 
				'uid_foreign', // check uid_foreign = article uid
				'uid_local=' . $this->getUid() // process current article list only
			);
		}
	}
	
	/// Get number of articles set in this articles list
	/** \return number of articles as stored in the article list record (or a
	 *  	default number, if number isn't properly stored in the data base)
	 */
	private function getNumArticles() {
		$num_articles = intval($this->getAttribute('num_articles'));
		if ($num_articles <= 0) {
			// use default value if not set properly in article list record
			$num_articles = self::default_num_articles;
		}  
		return $num_articles;
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
		
		$articles = $this->getArticlesAndOffsets($number, $start);
        tx_newspaper::devlog("getSortedArticles($number, $start)", $articles);

		$articles_sorted = $this->sortArticles($articles);
		$articles = array();
		foreach ($articles_sorted as $i => $article) {
			$articles[] = array($article['article']->getUid(), $article['offset']);
		}
#		t3lib_div::devlog('getSortedArticles()', 'newspaper', 0, array('uids'=>$uids, 'offsets' => $offsets, 'articles' => $articles));

		return array_slice($articles_sorted, 0, $number);
	}

	private function getArticlesAndOffsets($number, $start = 0) {
		/*	Because articles may be moved off the bottom of the list, we need a
		 *  safety margin. Twice as many articles as required should be enough.
		 */
		$uids = $this->getRawArticleUIDs(2*$number, $start);

        $offsets = $this->getOffsets($this->select_method_strategy->getUids($uids));
		
		$articles = array();
		foreach ($uids as $uid) {
			$articles[] = array(
				'article' => $this->select_method_strategy->createArticle($uid),
				'offset' => $this->select_method_strategy->getOffset($offsets, $uid)
			);
		}
		return $articles;
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

		/// \todo: Implement \p filter_articlelist_exclude. This must be done separately from the SQL query.
		
		try {
			$results = tx_newspaper::selectRows(
				$this->select_method_strategy->selectFields(),
				$this->selectTable(),
				$this->selectWhere(),
				'',
				$this->selectOrderBy(),
				$this->selectLimit($start, $number)
			);
		} catch (tx_newspaper_DBException $e) {
			//  This guards against article lists which use GET varaiables,
			//	which are not set in the BE
			$results = array();	
		}

        return $this->select_method_strategy->rawArticleUIDs($results);
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

    private function selectTable() {
        $table = $this->getAttribute('filter_sql_table');
        if (!$table) $table = 'tx_newspaper_article';

        if ($this->getAttribute('filter_tags_include')) {
            $table .= ' JOIN tx_newspaper_article_tags_mm' .
                      '   ON tx_newspaper_article.uid = tx_newspaper_article_tags_mm.uid_local';
        }
        if ($this->getAttribute('filter_tags_exclude')) {

            $table .= ' JOIN tx_newspaper_article_tags_mm' .
                      '   ON tx_newspaper_article.uid = tx_newspaper_article_tags_mm.uid_local';
        }

        if ($this->getAttribute('filter_sections')) {
            $table .= ' JOIN tx_newspaper_article_sections_mm' .
                      '   ON tx_newspaper_article.uid = tx_newspaper_article_sections_mm.uid_local';
        }

        return $table;
    }


    private function selectWhere() {
        $where = $this->getAttribute('filter_sql_where');
        if (!$where) $where = '1';
        if (strpos($where, '$') !== false) $where = self::expandGETParameter($where);
        $where .= tx_newspaper::enableFields('tx_newspaper_article', (TYPO3_MODE == 'BE'));

        if ($this->getAttribute('filter_tags_include')) {
            $where .= ' AND tx_newspaper_article_tags_mm.uid_foreign IN (' .
                            $this->getAttribute('filter_tags_include') . ')';
        }

        if ($this->getAttribute('filter_tags_exclude')) {
            $where .= ' AND tx_newspaper_article_tags_mm.uid_foreign NOT IN (' .
                            $this->getAttribute('filter_tags_exclude') . ')';
        }

        if ($this->getAttribute('filter_sections')) {
            $sections = array();
            foreach (explode(',', $this->getAttribute('filter_sections')) as $section_uid) {
                $sections[] = $section_uid;
                if (self::include_articles_from_subsections) {
                    //	add subsections to search clause
                    $section = new tx_newspaper_Section($section_uid);
                    $child_sections = $section->getChildSections(true);
                    if ($child_sections) {
                        foreach ($child_sections as $child_section) {
                            $sections[] = $child_section->getUid();
                        }
                    }
                }
            }
            $where .= ' AND tx_newspaper_article_sections_mm.uid_foreign IN (' .
                            implode(', ', $sections) . ')';
        }

        return $where;
    }

    private function selectOrderBy() {
        if ($this->getAttribute('filter_sql_order_by')) {
            $order_by = $this->getAttribute('filter_sql_order_by');
        } else {
            $order_by = '
CASE
  WHEN tx_newspaper_article.publish_date = \'0\'
  THEN tx_newspaper_article.tstamp
  ELSE tx_newspaper_article.publish_date
END
DESC';
        }

        return $order_by;
    }

    private function selectLimit($start, $number) {
        return intval($start) . ', ' . intval($number);
    }

	/// Sort articles, taking their offsets into account
	/** 
	 *  \param $articles array(
	 * 	array(
	 * 			'article' => tx_newspaper_Article object
	 * 			'offset' => offset to move article up or down in array
	 * 		)
	 * ...)
	 *  \return $articles sorted, taking offsets into account
	 *  \attention repeatedly calling this function will garble the results!
	 */
	private static function sortArticles(array $articles) {

		$temp_articles = array();

		foreach ($articles as $i => $article) {
			if (array_key_exists('offset', $article)) $offset_key = 'offset';
			else $offset_key = 1;
			$scaled_offset = $article[$offset_key]*(1+self::EPSILON);
			$new_index = $i-$scaled_offset;
			$temp_articles["$new_index"] = $article;
		}

		ksort($temp_articles);
		
		$new_articles = array();
		foreach($temp_articles as $article) $new_articles[] = $article;

		return $new_articles;
	}

	const EPSILON = 0.0001;
	
	
	///	Replace a substring denoted as a variable with the corresponding GET parameter
	/** For example, all occurrences of \c $art are replaced with 
	 *  \c $_GET['art']. If \c $_GET['art'] is not set, the variable is
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
	
	/// The UIDs of articles in the list in the order in which they are originally stored.
	private $raw_uids = array();
	
}

tx_newspaper_ArticleList::registerArticleList(new tx_newspaper_ArticleList_Semiautomatic());

?>
