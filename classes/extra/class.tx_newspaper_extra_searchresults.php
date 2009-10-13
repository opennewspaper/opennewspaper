<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

/// tx_newspaper_Extra displaying the results of a search
/** This Extra displays articles returned by a search which is usually for the
 *  search terms delivered via \c $_GET.
 *  
 *  The search can be restricted or fine-tuned by giving a (or several)
 *  tx_newspaper_Section. tx_newspaper_Tag, or a preset search term.
 * 
 *  Attributes:
 * 	- \p sections (UIDs of tx_newspaper_Section)
 *  - \p search_term (string)
 *  - \p tags (UIDs of tx_newspaper_Tag)
 *  
 *  \todo Ensure there is a fulltext index on all required fields
 *  \todo a lot more
 */
class tx_newspaper_extra_SearchResults extends tx_newspaper_Extra {

	/// Article attributes counted as title (higher score when searching).
	private static $title_fields = array('title', 'kicker', 'title_list', 'kicker_list');
	
	///	How much higher matches on title fields are rated.
	const title_score_factor = 2.0;

	/// Above which score a match is considered as good enough
	const score_limit = 0.1;
	
	///	Table storing tx_newspaper_Article
	const article_table = 'tx_newspaper_article';
	const article_section_mm = 'tx_newspaper_article_sections_mm';
	const article_tag_mm = 'tx_newspaper_article_tags_mm';
	
	///	Article attributes also searched for the search term (in addition to \p $title_fields).
	private static $text_fields = array('teaser', 'teaser_list', 'text', 'author');
	
	/// Extra tables and their fields also searched for the search term.
	private static $extra_fields = array(
		'tx_newspaper_extra_textbox' => array('title', 'text'),
		'tx_newspaper_extra_image' => array('title', 'kicker', 'caption'),
		'tx_newspaper_extra_bio' => array('author_name', 'bio_text'),
	);

	/// Definition of umlauts which MySQL cannot match case-insensitively
	private static $umlauts = array (
		'ä' => 'Ä', 'ö' => 'Ö', 'ü' => 'Ü', 'Ä' => 'ä', 'Ö' => 'ö', 'Ü' => 'ü'
	);
		
	/// Section the search is restricted to, if any.
	private $section = '';
	
	///	Articles found may be no older than this day.
	private $start_day = 0;
	///	Articles found may be no older than this month.
	private $start_month = 0;
	///	Articles found may be no older than this year.
	private $start_year = 0;

	///	Articles found may be no younger than this day.
	private $end_day = 0;
	///	Articles found may be no younger than this month.
	private $end_month = 0;
	///	Articles found may be no younger than this year.
	private $end_year = 0;
	
	/// Articles older than this are excluded globally
	private $search_lifetime = 0;
	
	/// Words for which may not be searched.
	/** Either because they're too frequent or because they're legal trouble */
	private $excluded_words = '';	
	
	/** \todo Populate class members from $_GET */
	public function __construct($uid = 0) { 
		if ($uid) {
			parent::__construct($uid); 
		}
	}
	
	/** Display results of the search leading to the current page.
	 * 
	 *  Smarty template:
	 *  \include res/templates/tx_newspaper_extra_searchresults.tmpl
	 */
	public function render($template_set = '') {
		$this->prepare_render($template_set);
		
	    // add special hits to smarty array
	    $this->searchSpecialHits($search_term);
		
		
		return $this->smarty->fetch($this);
	}

	public static function getModuleName() {
		return 'np_searchresults'; 
	}

	public static function dependsOnArticle() { return false; }
	
	protected function searchArticles($search_term) {

		$table = self::article_table;
		$where = '1';
		$fields = 'MATCH (' . implode(', ', self::$title_fields).') ' .
					'AGAINST (\''.mysql_real_escape_string($search_term).'\') AS titlescore, '.
				  'MATCH (' . implode(', ', self::$text_fields).') ' .
					'AGAINST (\''.mysql_real_escape_string($search_term).'\') AS score,';

		if ($this->getSections()) {
			$table .= ' JOIN ' . self::article_section_mm .
					  '   ON ' . self::article_table . '.uid = ' . self::article_section_mm . '.uid_local';

			$where .= ' AND ' . self::article_section_mm . '.uid_foreign IN (' . 
							$this->getSections() . ')';
		}
		if ($this->getAttribute('tags')) {
			$table .= ' JOIN ' . self::article_tag_mm .
					  '   ON ' . self::article_table . '.uid = ' . self::article_tag_mm .'.uid_local';

			$where .= ' AND ' . self::article_tag_mm . '.uid_foreign IN (' . 
							$this->getAttribute('tags') .')';
			
		}
		
		$where .= $this->searchWhereClause($search_term);
		
		$row = tx_newspaper::selectRows('COUNT(*) AS number', $table, $where);

		$num_articles = intval($row['number']);
	    if (!$num_articles) {
	        $GLOBALS['smarty']->assign('num_results', 0);
	    } else {

			$page = $this->getResultPage();
			
	        $query = $GLOBALS['TYPO3_DB']->SELECTquery(
				$fields . ',' . self::article_table . '.uid',
				self::article_table,
				$where,
				'',
				($this->searchOrder == 'crdate'?
					'crdate':
					$this->titleWeight.'*titlescore + '.$this->textWeight.'*score').
					' DESC',
				$page*self::$resultListLength.', '.self::$resultListLength
			);

		    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
	    }
	}

	/// Gets sections the search is restricted to as comma-separated list
	/** \return UIDs of the sections as comma separated list usable in an SQL statement
	 */	
	protected function getSections() {
		if ($this->getAttribute('sections') || $this->section) {
			if ($this->getAttribute('sections') && $this->section) {
				return $this->getAttribute('sections') . ',' . $this->section;
			} else if ($this->section) return $this->section;
			else return $this->getAttribute('sections'); 
		}
		return '';
	}
	
	protected function searchExtras($search_term) {
		throw new tx_newspaper_NotYetImplementedException();
	}

	protected function getResultPage() {
		throw new tx_newspaper_NotYetImplementedException();
	}
	
	///	Assembles a SQL \c WHERE - clause to search for the supplied search term
	/** \param $term the search term to look for.
	 *  \param $field_list The fields in which to search.
	 * 
	 *  \return \c WHERE - clause as string.
	 */
	private function searchWhereClause($term, array $field_list) {

	    $where = '';

		// Assemble conditions for pulishing date
	    $tstamp = 0;
	    if (intval($this->search_lifetime)) {
	    	$tstamp = mktime()-intval($$this->search_lifetime)*24*60*60;
	    }
	    if ($this->start_day || $this->start_month || $this->start_year) {
	        $tstamp = max($tstamp,
	              		  mktime(0, 0, 0, $this->start_month, $this->start_day, $this->start_year));
	    }
	    $where .= " ((starttime > 0 AND starttime >= $tstamp) OR (starttime = 0 AND crdate >= $tstamp)) AND ";

	    if ($this->end_day || $this->end_month || $this->end_year) {
		      $tstamp = mktime(23, 59, 59, $this->end_month, $this->end_day, $this->end_year);
		      $where .= " ((endtime > 0 AND endtime < $tstamp) OR (endtime = 0 AND crdate < $tstamp)) AND ";
	    }

		//	Assemble conditions on search terms
	    foreach (explode(' ', $term) as $current_term) {
	        if (!$current_term) continue;
	        //	don't search for excluded words
	        if (!$this->isExcludedWord($current_term)) {
	            $where .= $this->umlautCaseInsensitiveMatch($current_term);
	        }
	    }

	    return $where;
    }

	///	Checks whether the word is excluded from search terms
	/** \param $term Word to check
	 *  \return \c true if \p $term is a word that shouldn't be searched for
	 */ 
	private function isExcludedWord($term) {
	    $this->excluded_words .= self::$globally_excluded_words;
        if ($this->excluded_words) {
	        foreach (explode(',', $this->excluded_words) as $excluded) {
	            if (strtoupper(trim($excluded)) == strtoupper(trim($term))) {
	                return true;
	            }
			}
		}
		return false;
	}
	
	///	Generates a \c MATCH - clause recognizing umlauts regardless of their case
	/** Because umlauts are not in the latin1-charset, MySQL does not know their
	 *  respective upper- and lowercase versions. Because we want a case-
	 *  insensitive search, we must assemble the matches agains upper or lower  
	 *  case separately and match against both.
	 * 
	 *  \param $search_term The term which is to be matched case-insensitively.
	 *  \param $field_list The fields in which to search.
	 * 
	 *  \return SQL clause matching \p $field_list against \p $search_term.
	 */
	private function umlautCaseInsensitiveMatch($search_term, array $field_list) {
		$ret = '(';
		foreach (array_keys(self::$umlauts) as $replacableChar) {
			if (strpos($search_term, $replacableChar) !== false) {

				$ret .= 'MATCH (' . implode(', ', $field_list) . ')' .
	           			' AGAINST (\'' . 
	           				mysql_real_escape_string(
	           					trim(
	           						str_replace(
	           							$replacableChar, 
	           							self::$umlauts[$replacableChar], 
	           							$search_term
	           						)
	           					)
	           				) . 
						'\') > ' . self::score_limit . ' OR ';
			}
		}
		$ret .= 'MATCH (' . implode(', ', $field_list) . ')' .
	           ' AGAINST (\'' . 
	           		mysql_real_escape_string(trim($search_term)) . 
				'\') > ' . self::score_limit;
		$ret .=')';

		return $ret;
	}
	
	
}
tx_newspaper_Extra::registerExtra(new tx_newspaper_extra_SearchResults());

?>