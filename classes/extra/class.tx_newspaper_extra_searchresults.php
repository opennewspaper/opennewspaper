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
 *  - \p sections (UIDs of tx_newspaper_Section)
 *  - \p search_term (string)
 *  - \p tags (UIDs of tx_newspaper_Tag)
 *  
 *  \todo search order (publishing date or relevance)
 *  \todo parameter: number of results
 *  \todo excluded words
 */
class tx_newspaper_extra_SearchResults extends tx_newspaper_Extra {

	////////////////////////////////////////////////////////////////////////////
	//
	//	Configuration parameters
	//
	////////////////////////////////////////////////////////////////////////////
	
	///	How much higher matches on title fields are rated.
	const title_score_weight = 2.0;

	///	How much higher (or lower) matches on Extra fields are rated.
	const extra_score_weight = 0.5;

	/// Above which score a match is considered as good enough
	const score_limit = 0.1;
	
	/// Number of results stored in memory
	const max_search_results = 1000;
	
	/// Whether to log search terms
	private $log_searches = true;
	
	/// Whether to log search results
	private $log_results = true;
	
	/// Path to log file
	private $log_file = '/www/onlinetaz/logs/search.log';
	
	/// GET parameter used to pass search term
	const search_GET_var = 'search';

	/// GET parameter used to page the search results
	const page_GET_var = 'search_page';
	
	/// Article attributes counted as title (higher score when searching).
	/** A \c FULLTEXT index must be configured for these fields.
	 *  \see ext_tables_addon.sql
	 */
	private static $title_fields = array('title', 'kicker', 'title_list', 'kicker_list');
	
	///	Article attributes also searched for the search term (in addition to \p $title_fields).
	/** A \c FULLTEXT index must be configured for these fields.
	 *  \see ext_tables_addon.sql
	 */
	private static $text_fields = array('teaser', 'teaser_list', 'text', 'author');
	
	/// Extra tables and their fields also searched for the search term.
	/** A \c FULLTEXT index must be configured for each of these tables/fields.
	 *  \see ext_tables_addon.sql
	 */
	private static $extra_fields = array(
		'tx_newspaper_extra_textbox' => array('title', 'text'),
		'tx_newspaper_extra_image' => array('title', 'kicker', 'caption'),
		'tx_newspaper_extra_bio' => array('author_name', 'bio_text'),
	);

	///	Table storing tx_newspaper_Article
	const article_table = 'tx_newspaper_article';
	
	///	Table storing M-M relations between tx_newspaper_Article and tx_newspaper_Section
	const article_section_mm = 'tx_newspaper_article_sections_mm';
	
	///	Table storing M-M relations between tx_newspaper_Article and tx_newspaper_Tag
	const article_tag_mm = 'tx_newspaper_article_tags_mm';
	
	/// Definition of umlauts which MySQL cannot match case-insensitively
	private static $umlauts = array (
		'ä' => 'Ä', 'ö' => 'Ö', 'ü' => 'Ü', 'Ä' => 'ä', 'Ö' => 'ö', 'Ü' => 'ü'
	);

	/// Words which are excluded from searches
	/** \todo this is just a by-pass until the mysql stopword problem is solved
	 */
	private static $globally_excluded_words = ",aber,als,am,an,auch,auf,aus,bei,
			das,dass,dem,den,der,des,die,doch,dpa,ein,eine,einem,einer,er,es,im,
			in,ist,man,mit,nach,nicht,oder,sein,sich,sie,sind,so,und,von,vor,wenn,
			wie,wird,zu,zum,zur";

	////////////////////////////////////////////////////////////////////////////
	//
	//	Internal variables. Editing is useless.
	//
	////////////////////////////////////////////////////////////////////////////
			
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

	///	Number of results returned by the search
	private $num_results = 0;
	
	/// Search term
	private $search = '';

	////////////////////////////////////////////////////////////////////////////	
	
	/** \todo Populate class members from $_GET or otherwise:
	 *    - section restriction
	 *    - logging behavior
	 *    - log file
	 *    - excluded words
	 */
	public function __construct($uid = 0) { 
		if ($uid) {
			parent::__construct($uid); 
		}
		
		$this->search = t3lib_div::_GP(self::search_GET_var);
		
		if (t3lib_div::_GP('start_day')) $this->start_day = t3lib_div::_GP('start_day');
		if (t3lib_div::_GP('start_month')) $this->start_month = t3lib_div::_GP('start_month');
		if (t3lib_div::_GP('start_year')) $this->start_year = t3lib_div::_GP('start_year');
		if (t3lib_div::_GP('end_day')) $this->end_day = t3lib_div::_GP('end_day');
		if (t3lib_div::_GP('end_month')) $this->end_month = t3lib_div::_GP('end_month');
		if (t3lib_div::_GP('end_year')) $this->end_year = t3lib_div::_GP('end_year');
	}
	
	/** Display results of the search leading to the current page.
	 * 
	 *  Smarty template:
	 *  \include res/templates/tx_newspaper_extra_searchresults.tmpl
	 */
	public function render($template_set = '') {

        tx_newspaper::startExecutionTimer();

		$this->prepare_render($template_set);
		
	    // add special hits to smarty array
	    $this->smarty->assign('special', $this->searchSpecialHits($this->search));
		
		// perform the search on all articles
		$this->smarty->assign('articles', $this->searchArticles($this->search));
		
        $rendered = $this->smarty->fetch($this);
        
        tx_newspaper::logExecutionTime();
        
        return $rendered;
	}

	public static function getModuleName() {
		return 'np_searchresults'; 
	}

	public static function dependsOnArticle() { return false; }

    protected function searchSpecialHits($search_term) {
        return array('<!-- Search for special keywords not yet implemented! -->');
    }

	///	Performs the search on all articles.
	/** Also searches the configured Extras (as in \c self::$extra_fields) for
	 *  the term. The search ist executed once for every configured Extra table
	 *  and the found results are then sorted in PHP. 
	 * 
	 *  That implies that all search results must be read from DB (at least
	 *  their UIDs), so that the sorting of results where the Extras differ can
	 *  be inserted into the list of results and sorted.
	 * 
	 *  The following attributes, member variables and GET variables are
	 *  referenced, directly or indirectly, in this function:
	 *  - evaluated in getSections():
	 *    - attribute 'sections'	(set in BE)
	 *    - $this->section		(GET parameter, read in constructor)
	 *  - attribute 'tags'		(set in BE)
	 *  - evaluated in searchWhereClause():
	 *    - $this->search_lifetime
	 *    - $this->start_day, $this->start_month, $this->start_year
	 *    - $this->end_day, $this->end_month, $this->end_year
	 *    - evaluated in isExcludedWord():
	 *      - $this->excluded_words
	 *      - self::$globally_excluded_words
	 *    - evaluated in umlautCaseInsensitiveMatch():
	 *      - self::$umlauts
	 *      - self::score_limit
	 *  - self::max_search_results
	 *  - getNumResultsPerPage()
	 *  - evaluated in compareArticles():
	 *    - \b Todo: check whether to sort by date or relevance
	 *    - evaluated in totalScore():
	 *      - self::title_score_factor
     *      - self::extra_score_factor
	 *  - evaluated in logSearch():
	 *    - self::$log_searches
	 *    - self::$logfile
	 *    - self::$log_results
	 * 
	 *  \param $search_term The word(s) for which the search is performed
	 *  \return Array of tx_newspaper_Article found
	 */	
	protected function searchArticles($search_term) {

		$table = self::article_table;
		$where = '1';
		$fields = self::article_table . '.uid, ' .
				  'MATCH (' . implode(', ', self::$title_fields).') ' .
					'AGAINST (\''.mysql_real_escape_string($search_term).'\') AS title_score, '.
				  'MATCH (' . implode(', ', self::$text_fields).') ' .
					'AGAINST (\''.mysql_real_escape_string($search_term).'\') AS text_score, ';

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

		$where .= ' AND ( ' . $this->searchWhereClause($search_term, self::$title_fields) . 
				  ' OR ' . $this->searchWhereClause($search_term, self::$text_fields) . ' )';

		$table .= ' JOIN ' . self::article_extra_mm .
				  '   ON ' . self::article_table . '.uid = ' . self::article_extra_mm . '.uid_local' .
				  ' JOIN ' . self::extra_table .
				  '   ON ' . self::extra_table . '.uid = ' . self::article_extra_mm . '.uid_foreign';		
		
		$articles = array();
		
		foreach (self::$extra_fields as $extra_table => $fields) {
			$current_table = $table .
				' JOIN ' . self::article_tag_mm .
				'   ON ' . self::extra_table . '.extra_uid = ' . $extra_table . '.uid' .
				'     AND ' . self::extra_table . '.extra_table = \'' . $extra_table . '\'';

			$current_fields = $fields .
				'MATCH (' . implode(', ', $fields).') ' .
				'AGAINST (\''.mysql_real_escape_string($search_term).'\') AS extra_score ';

			$current_where = $where .
				' AND ( ' . $this->searchWhereClause($search_term, $fields) . 
				' OR ' . $this->searchWhereClause($search_term, $fields) . ' )';
			
			$row = tx_newspaper::selectRows('COUNT(*) AS number', $current_table, $current_where);
			t3lib_div::devlog('SQL query', 'newspaper', 0, tx_newspaper::$query);
			
			$num_articles = intval($row['number']);
			if (!$num_articles) continue;
			
	        $results = tx_newspaper::selectRows(
				$current_fields,
				$current_table,
				$current_where,
				'',
				'',
				'0, ' . self::max_search_results
			);
			t3lib_div::devlog('SQL query', 'newspaper', 0, tx_newspaper::$query);
			
			foreach ($results as $result) {
				foreach ($articles as $article) {
					if (intval($article['uid']) == intval($result['uid'])) {
						$article['extra_score'] += $result['extra_score'];
						continue 2;		//	continue outer loop
					}
				}
				$articles[] = $result;
					
			}
		}
		
    	$this->num_results = sizeof($articles);
		$return = array();
    	 
	    if ($this->num_results > 0) {
			usort($articles, array(get_class($this), 'compareArticles'));

			foreach (array_slice($articles, $this->getResultPage(), $this->getNumResultsPerPage())
					 as $article) {
				$return[] = new tx_newspaper_Article($article['uid']);
			}
			
	    }
	    
		$this->logSearch($search_term, $return);

		return $return;
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
	
	///	Page of search results currently displayed
	/** \return Page of search results currently displayed
	 */
	protected function getResultPage() {
		return intval(t3lib_div::_GP(self::page_GET_var));
	}

	/// How many search results to display per page
	/** \return Number of search results per page
	 *  \todo More sophisticated solution ;-)
	 */
	protected function getNumResultsPerPage() {
		return 10;
	}
	
	///	Write the requested search term and the search results to a log file.
	/** The behavior of this function is controlled by self::$log_searches and
	 *  self::$log_results. If self::$log_results is \c false, the search
	 *  results are not logged. If self::$log_searches is \c false, nothing is
	 *  logged at all.
	 * 
	 *  \param $search Search term
	 *  \param $results Found articles
	 */
	protected function logSearch($search, array $results = array()) {

		if (!self::$log_searches) return;

		$log = fopen(self::$logfile, 'a');

	    fwrite($log, 'Search term: ' . $search . "\n");

		if (self::$log_results) {
		    fwrite($log, 'Results:' . "\n");
		    if ($results) {
		    	foreach ($results as $result) {
		    		if (!($result instanceof tx_newspaper_ArticleIface)) {
		    			fwrite($log, '    Not an Article: ' . $result . "\n");
		    		} else {
		    			fwrite(
		    				$log, 
							'    Article ' . $result->getUid() . ': ' . 
							$result->getAttribute('title') . "\n");
		    		}
		    	}
		    } else {
		    	fwrite($log, '    None!' . "\n");
		    }
		}

	    fclose($log);
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

	///	Checks whether the word is excluded from search terms.
	/** Excluded words are silently dropped from the search words.
	 * 
	 *  \param $term Word to check
	 *  \return \c true if \p $term is a word that shouldn't be searched for
	 * 
	 *  \todo check a SQL table for excluded words (do that in __construct())
	 */ 
	private function isExcludedWord($term) {
		$excluded = $this->excluded_words . self::$globally_excluded_words;
	    
        foreach (explode(',', $excluded) as $word) {
            if (strtoupper(trim($word)) == strtoupper(trim($term))) {
                return true;
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
	
	
 	/// Determine which tx_newspaper_Article comes first in search results.
 	/** Supplied as parameter to \c usort() in searchArticles().
 	 *  This function may be overridden or reimplemented to reflect changing 
 	 *  requirements for the sorting of articles. 
 	 * 
 	 *  \param $art1 first tx_newspaper_Article to compare in the form \code
 	 * 		array(
 	 * 			'uid' => tx_newspaper_Article UID
 	 * 			'title_score' => MATCH score on self::$title_fields 
 	 * 			'text_score' => MATCH score on self::$text_fields
 	 * 			'extra_score' => MATCH score on self::$extra_fields
 	 * 		) \endcode
 	 *  \param $art2 second tx_newspaper_Article to compare, same format as
 	 *  	\p $art1.
 	 *  \return < 0 if \p $art1 comes before \p $art2, > 0 if it comes after, 
 	 * 			== 0 if their position is the same. 
 	 * 
 	 *  \todo take into account the possibility to sort by publishing date.
 	 */
	private static function compareArticles(array $art1,
											array $art2) {
		return self::totalScore($art2)-self::totalScore($art1);
	}
	
	///	Calculates the cumulative score on SQL \c MATCH for title, text and extras
	/** \param $article Article to evaluate in the form \code
 	 * 		array(
 	 * 			'uid' => tx_newspaper_Article UID
 	 * 			'title_score' => MATCH score on self::$title_fields 
 	 * 			'text_score' => MATCH score on self::$text_fields
 	 * 			'extra_score' => MATCH score on self::$extra_fields
 	 * 		) \endcode
 	 * \return Total score adjusted for the relevance weights on title and
 	 * 		Extras
 	 */
	private static function totalScore(array $article) {
		return self::title_score_factor*$article['title_score'] +
				$article['text_score'] +
				self::extra_score_factor*$article['extra_score'];
	}
}
tx_newspaper_Extra::registerExtra(new tx_newspaper_extra_SearchResults());

?>