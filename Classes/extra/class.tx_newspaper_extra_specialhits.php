<?php

require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_extra.php');


/** This Extra is used to display special hits depending on a search query in GET parameters (see Extra search results:
 * tx_newspaper_extra_SearchResults::search_GET_var)
 * The special hits are defined in tx_newspaper_specialhit records (must be stored in the "Extra Special hits"
 * sysfolder; other records are ignored).
 * This extra is usually placed above an Extra "Search results" in order to render specials hits ("Did you mean ...")
 * Example: Special hit "subscription" can be matched with a page about RSS feeds.
 */
class tx_newspaper_Extra_SpecialHits extends tx_newspaper_Extra {

	///	SQL table special hits are stored in
	const special_hits_table = 'tx_newspaper_specialhit';

    private $search; // Stores search query
    private $seachArray; // Search query in an array


	/// Constructor
	public function __construct($uid = 0) {
		if ($uid) {
			parent::__construct($uid);
		}

        $this->search = t3lib_div::_GP(tx_newspaper_extra_SearchResults::search_GET_var);

        if (!tx_newspaper::isUTF8($this->search)) {
            // convert query string to UTF-8
            $this->search = utf8_encode($this->search);
        }

	}

	public function __toString() {
		try {
			return 'Extra: UID ' . $this->getExtraUid() . ', Extra Special hits: UID ' . $this->getUid();
		} catch(Exception $e) {
			return "Extra Special hits: Exception thrown!" . $e;
		}
	}

	/** Render special hits results.
	 *  Smarty template:
	 *  \include res/templates/tx_newspaper_extra_specialhits.tmpl
	 */
	public function render($template_set = '') {

		$this->prepare_render($template_set);

        $this->smarty->assign('special_hits', $this->searchSpecialHits());
        $this->smarty->assign('search_query', $this->search);

        return $this->smarty->fetch($this->getSmartyTemplate());
	}


    /**
     * Search special hits
     * @return Array with items array('title', 'teaser', 'url')
     */
    private function searchSpecialHits() {

        if (!$this->search) {
            return array(); // no query, no special hits
        }

        // Split search query into single words (and convert to lowercase)
        $this->searchArray = tx_newspaper::toLowerCaseArray(explode(' ', preg_replace("/\s\s+/", ' ', trim($this->search))));
        if (sizeof($this->searchArray) == 0) {
            return array(); // nothing to do ...
        }

        // Execute search
        return $this->executeSpecialHitSearch();
  }

    /**
     * Search special hit keywords for terms set in $searchTerm
     * @param array $searchTerms
     * @return array with items array('title', 'teaser', 'url')
     */
    private function executeSpecialHitSearch() {
        // Check all special hits if the search query matches
        $specialHits = array();
        foreach ($this->readSpecialWords() as $specialWord) {
            if ($this->matchesSearchQuery($specialWord)) {
                $specialHits[] = array(
                    'title' => $specialWord['title'],
                    'teaser' => $specialWord['teaser'],
                    'url' => $specialWord['url']
                );
            }
        }
//t3lib_div::devlog('executeSpecialHitSearch()', 'np', 0, array('terms' => $this->searchArray, 'this->search' => $this->search, 'special Hits' => $specialHits, 'specialWords' => $this->readSpecialWords()));
//t3lib_div::debug(array('terms' => $this->searchArray, 'this->search' => $this->search, 'special Hits' => $specialHits, 'specialWords' => $this->readSpecialWords()));
        return $specialHits;
    }

    /**
     * Check if given $specialWord matched the query string
     * @param array $specialWord Array with a list of words triggering a special hit
     * @return bool True if a hit was found, else false
     */
    private function matchesSearchQuery(array $specialWord) {
        foreach($specialWord['words'] as $word) {
            if (in_array($word, $this->searchArray)) {
                return true; // Hit found
            }
        }
        return false; // No hit found
    }


    /**
     * Read all special words stored in special hits sysfolder. The keywords are converted to lowercase!
     * This array is needed because a special hit can be linked to various space separated keywords, so accessing the
     * database with "LIKE" would return false positives ("dummySHOULDNTBEFOUND" would be found searching for "dummy")
     * @return array('words' => array('word 1', ..., 'word n'), 'title' => '...', 'teaser' => '...', 'url' => '...')
     */
    private function readSpecialWords() {

		// get pid
        $pid = tx_newspaper_Sysfolder::getInstance()->getPid(new tx_newspaper_Extra_SpecialHits());

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'words,title,teaser,url,sorting',
			self::special_hits_table,
			'pid=' . $pid . tx_newspaper::enableFields(self::special_hits_table),
            '',
            'sorting'
		);

        $specialWords = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if ($row['url'] != '') {
				$specialWords[] = array(
				    'words' => tx_newspaper::toLowerCaseArray(explode(' ', preg_replace('/\s\s+/', ' ', trim($row['words'])))),
        			'title' => $row['title'],
        			'teaser' => $row['teaser'],
        			'url' => $row['url']
        		);
			}
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
        return $specialWords;
  }



	/// title for module
	public static function getModuleName() {
		return 'np_extra_specialhits';
	}

	public static function dependsOnArticle() { return false; }

}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_SpecialHits());

?>