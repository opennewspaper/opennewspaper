<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lene
 * Date: 6/30/11
 * Time: 12:04 PM
 * To change this template use File | Settings | File Templates.
 */

class tx_newspaper_Date {

    public function __construct($year = 0, $month = 1, $day = 1) {
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
    }

    public function getTimestamp() {
        if (!$this->year) return 0;
        return mktime(0, 0, 0, $this->month, $this->day, $this->year);
    }

    private $year;
    private $month;
    private $day;
}

class tx_newspaper_Search {

	////////////////////////////////////////////////////////////////////////////
	//
	//	Configuration parameters
	//
	////////////////////////////////////////////////////////////////////////////

    /// dirty solution for speeding up the search while sacrificing flexibility
    /** order by date only. do not search extras.
     *  for launch. make this better ASAP.
     */
    const enable_quick_hack = true;

	///	Table storing tx_newspaper_Article
	const article_table = 'tx_newspaper_article';

	///	Table storing M-M relations between tx_newspaper_Article and tx_newspaper_Section
	const article_section_mm = 'tx_newspaper_article_sections_mm';

	///	Table storing M-M relations between tx_newspaper_Article and tx_newspaper_Tag
	const article_tag_mm = 'tx_newspaper_article_tags_mm';

	///	Table storing M-M relations between tx_newspaper_Article and tx_newspaper_Extra
    const article_extra_mm = 'tx_newspaper_article_extras_mm';
    const extra_table = 'tx_newspaper_extra';

	/// Above which score a match is considered as good enough
	const score_limit = 0.1;
	///	How much higher matches on title fields are rated.
	const title_score_weight = 2.0;
	///	How much higher (or lower) matches on Extra fields are rated.
	const extra_score_weight = 0.5;


	/// Article attributes counted as title (higher score when searching).
	/** A \c FULLTEXT index must be configured for these fields.
	 *  \see ext_tables_addon.sql
	 */
	private static $title_fields = array('title', 'kicker', 'title_list', 'kicker_list');

	///	Article attributes also searched for the search term (in addition to \p $title_fields).
	/** A \c FULLTEXT index must be configured for these fields.
	 *  \see ext_tables_addon.sql
	 */
	private static $text_fields = array('teaser', 'teaser_list', 'bodytext', 'author');

	/// Extra tables and their fields also searched for the search term.
	/** A \c FULLTEXT index must be configured for each of these tables/fields.
	 *  \see ext_tables_addon.sql
	 */
	private static $extra_fields = array(
#		'tx_newspaper_extra_textbox' => array('title', 'bodytext'),
#		'tx_newspaper_extra_image' => array('title', 'kicker', 'caption'),
#		'tx_newspaper_extra_bio' => array('author_name', 'bio_text'),
	);

	/// Whether to log search terms
	private static $log_searches = false;

	/// Whether to log search results
	private static $log_results = true;

	/// Path to log file
	private static $log_file = '/www/onlinetaz/logs/search.log';

	/// Words which are excluded from searches
	/** \todo this is just a by-pass until the mysql stopword problem is solved
	 */
	const globally_excluded_words = ",aber,als,am,an,auch,auf,aus,bei,
			das,dass,dem,den,der,des,die,doch,dpa,ein,eine,einem,einer,er,es,im,
			in,ist,man,mit,nach,nicht,oder,sein,sich,sie,sind,so,und,von,vor,wenn,
			wie,wird,zu,zum,zur";


	/// Definition of umlauts which MySQL cannot \c MATCH case-insensitively
	private static $umlauts = array (
		'ä' => 'Ä', 'ö' => 'Ö', 'ü' => 'Ü', 'Ä' => 'ä', 'Ö' => 'ö', 'Ü' => 'ü'
	);

    private static $sort_methods = array('compareArticlesByScore', 'compareArticlesByDate');

    public function __construct($section, tx_newspaper_Date $start_date, tx_newspaper_Date $end_date) {
        $this->section = $section;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
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
    public function searchArticles($search_term) {

        $table = self::article_table;
        $where = '1';
        $fields = self::article_table . '.uid, ' .
                  self::article_table . '.publish_date, ' .
                  'MATCH (' . implode(', ', self::$title_fields).') ' .
                    'AGAINST (\''.mysql_real_escape_string($search_term).'\') AS title_score, '.
                  'MATCH (' . implode(', ', self::$text_fields).') ' .
                    'AGAINST (\''.mysql_real_escape_string($search_term).'\') AS text_score ';

        if ($this->getSections()) {
            $table .= ' JOIN ' . self::article_section_mm .
                      '   ON ' . self::article_table . '.uid = ' . self::article_section_mm . '.uid_local';

            $where .= ' AND ' . self::article_section_mm . '.uid_foreign IN (' .
                            $this->getSections() . ')';
        }

        if ($this->tags) {
            $table .= ' JOIN ' . self::article_tag_mm .
                      '   ON ' . self::article_table . '.uid = ' . self::article_tag_mm .'.uid_local';

            $where .= ' AND ' . self::article_tag_mm . '.uid_foreign IN (' .
                            $this->tags .')';
        }

        $where .= ' AND ( ' . $this->searchWhereClause($search_term, self::$title_fields) .
                  ' OR ' . $this->searchWhereClause($search_term, self::$text_fields) . ' )';

        $table .= ' JOIN ' . self::article_extra_mm .
                  '   ON ' . self::article_table . '.uid = ' . self::article_extra_mm . '.uid_local' .
                  ' JOIN ' . self::extra_table .
                  '   ON ' . self::extra_table . '.uid = ' . self::article_extra_mm . '.uid_foreign';

        $articles = self::getSearchResultsForClass($fields, $table, $where);

        if (self::enable_quick_hack) {
        foreach (self::$extra_fields as $extra_table => $fields) {
            $current_table = $table .
                ' JOIN ' . self::article_tag_mm .
                '   ON ' . self::extra_table . '.extra_uid = ' . $extra_table . '.uid' .
                '     AND ' . self::extra_table . '.extra_table = \'' . $extra_table . '\'';

            $current_fields = $fields . ', ' .
                'MATCH (' . implode(', ', $fields).') ' .
                'AGAINST (\''.mysql_real_escape_string($search_term).'\') AS extra_score ';

            $current_where = $where .
                ' AND ( ' . $this->searchWhereClause($search_term, $fields) .
                ' OR ' . $this->searchWhereClause($search_term, $fields) . ' )';

            $row = tx_newspaper::selectRows('COUNT(*) AS number', $current_table, $current_where);

            $num_articles = intval($row['number']);
            if (!$num_articles) continue;

            $articles = array_merge($articles,
                                    self::getSearchResultsForClass($current_fields, $current_table, $current_where));
        }
        }
        $return = $this->generateArticleObjectsFromSearchResults($articles);

        $this->logSearch($search_term, $return);

        return $return;
    }

    public function setSortMethod($method_name) {
        if (self::isSortMethod($method_name)) {
            self::$sort_method = $method_name;
        }
    }
    
    public static function getSortMethods() {
        return self::$sort_methods;
    }
    
    ////////////////////////////////////////////////////////////////////////////

    private function generateArticleObjectsFromSearchResults($articles) {

        $this->num_results = sizeof($articles);
        $return = array();

        if ($this->num_results > 0) {
            usort($articles, array(get_class($this), 'compareArticles'));

            foreach ($articles as $article) {
                $return[] = new tx_newspaper_Article($article['uid'], true);
            }
        }

        return $return;
    }

    ///	Assembles a SQL \c WHERE - clause to search for the supplied search term
	/** \param $term the search term to look for.
	 *  \param $field_list The fields in which to search.
	 *
	 *  \return \c WHERE - clause as string.
	 */
	private function searchWhereClause($term, array $field_list) {

        $where = $this->getTimeClauseForSearch();

		//	Assemble conditions on search terms
	    foreach (explode(' ', $term) as $current_term) {
	        if (!$current_term) continue;
	        //	don't search for excluded words
	        if (!$this->isExcludedWord($current_term)) {
	            $where .= $this->umlautCaseInsensitiveMatch($current_term, $field_list) . ' OR ';
	        }
	    }

	    return $where . '0';
    }

    private static function getSearchResultsForClass($current_fields, $current_table, $current_where) {
        $results = tx_newspaper::selectRows(
            "DISTINCT $current_fields",
            $current_table,
            $current_where,
            '',
            'publish_date DESC'
        );

        $articles = array();
        foreach ($results as $result) {
            if (self::enable_quick_hack) {
            foreach ($articles as $article) {
                if (intval($article['uid']) == intval($result['uid'])) {
                    $article['extra_score'] += $result['extra_score'];
                    continue 2; //	continue outer loop
                }
            }
            }
            $articles[] = $result;

        }

        return $articles;
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

		$log = fopen(self::$log_file, 'a');

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

    private function getTimeClauseForSearch() {
        $where = '';

        $tstamp = $this->getStartTimeForSearch();
        if ($tstamp) {
            $where .= " ( (" . self::article_table . ".starttime > 0 AND " . self::article_table . ".starttime >= $tstamp) OR (" . self::article_table . ".starttime = 0 AND " . self::article_table . ".crdate >= $tstamp) ) AND ";
        }

        $tstamp = $this->getEndTimeForSearch();
        if ($tstamp) {
            $where .= " ( (" . self::article_table . ".endtime > 0 AND " . self::article_table . ".endtime < $tstamp) OR (" . self::article_table . ".endtime = 0 AND " . self::article_table . ".crdate < $tstamp) ) AND ";
        }

        return $where;
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


    ///	Checks whether the word is excluded from search terms.
	/** Excluded words are silently dropped from the search words.
	 *
	 *  \param $term Word to check
	 *  \return \c true if \p $term is a word that shouldn't be searched for
	 *
	 *  \todo check a SQL table for excluded words (do that in __construct())
	 */
	private function isExcludedWord($term) {
		$excluded = self::globally_excluded_words;

        foreach (explode(',', $excluded) as $word) {
            if (strtoupper(trim($word)) == strtoupper(trim($term))) {
                return true;
            }
		}

		return false;
	}

    /// Gets sections the search is restricted to as comma-separated list
	/** \return UIDs of the sections as comma separated list usable in an SQL statement
	 */
	private function getSections() {
		return $this->section;
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

        $method = self::$sort_method;
        return self::$method($art1, $art2);
	}

    private static function compareArticlesByScore(array $art1, array $art2) {
        return self::totalScore($art2)-self::totalScore($art1);
    }

    private static function compareArticlesByDate(array $art1, array $art2) {
        return $art2['publish_date']-$art1['publish_date'];
    }

    private static function isSortMethod($method_name) {
        if (empty($method_name)) return false;
        $refl = new ReflectionMethod('tx_newspaper_Search', $method_name);
        if (!$refl->isStatic()) return false;
        if ($refl->getNumberOfParameters() != 2) return false;
        return true;
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
		return self::title_score_weight*$article['title_score'] +
				$article['text_score'] +
				self::extra_score_weight*$article['extra_score'];
	}


    /// Assemble conditions for pulishing date
    private function getStartTimeForSearch() {
        $tstamp = 0;
        if (intval($this->search_lifetime)) {
            $tstamp = mktime() - intval($$this->search_lifetime) * 24 * 60 * 60;
        }
        return max($tstamp, $this->start_date->getTimestamp());
    }

    private function getEndTimeForSearch() {
        return $this->end_date->getTimestamp();
    }

	/// Articles older than this are excluded globally
	private $search_lifetime = 0;

    private $start_date = null;
    private $end_date = null;
    private $section = 0;
    private $tags = array();

    private static $sort_method = 'compareArticlesByScore';

	///	Number of results returned by the search
	private $num_results = 0;


}
