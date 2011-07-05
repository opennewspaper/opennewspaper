<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_search.php');

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

	/// GET parameter used to pass search term
	const search_GET_var = 'search';

	/// GET parameter used to page the search results
	const page_GET_var = 'search_page';

    const default_results_per_page = 10;

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

        $this->search_object = new tx_newspaper_Search(
            intval(t3lib_div::_GP('section')),
            new tx_newspaper_Date(
                intval(t3lib_div::_GP('start_year')),
                intval(t3lib_div::_GP('start_month')),
                intval(t3lib_div::_GP('start_day'))
            ),
            new tx_newspaper_Date(
                intval(t3lib_div::_GP('end_year')),
                intval(t3lib_div::_GP('end_month')),
                intval(t3lib_div::_GP('end_day')+1)
            )
        );

		$this->search = t3lib_div::_GP(self::search_GET_var);

	}

	/** Display results of the search leading to the current page.
	 *
	 *  Smarty template:
	 *  \include res/templates/tx_newspaper_extra_searchresults.tmpl
	 */
	public function render($template_set = '') {

        tx_newspaper::startExecutionTimer();

		$this->prepare_render($template_set);

		// perform the search on all articles
		$this->smarty->assign('articles', $this->searchArticles($this->search));

        $rendered = $this->smarty->fetch($this);

        tx_newspaper::logExecutionTime();

        return $rendered;
	}

	public function getDescription() {
		return $this->getAttribute('short_description');
	}

	public static function getModuleName() {
		return 'np_searchresults';
	}

	public static function dependsOnArticle() { return false; }

    private function searchArticles($search_term) {

        if (!$search_term) return array();

        $this->search_object->setOrderMethod('compareArticlesByDate');
        $articles = $this->search_object->searchArticles($search_term);

        return array_slice(
            $articles,
            self::getFirstArticleIndex(),
            self::getNumResultsPerPage()
        );
    }

    private static function getFirstArticleIndex() {
        return self::getResultPage()*self::getNumResultsPerPage();
    }

	///	Page of search results currently displayed
	/** \return Page of search results currently displayed
	 */
	private static function getResultPage() {
		return intval(t3lib_div::_GP(self::page_GET_var));
	}

    private static function getNumResultsPerPage() {
        $tsconf_results_per_page = intval(tx_newspaper::getTSConfigVar('search_results_per_page'));
        if ($tsconf_results_per_page) return $tsconf_results_per_page;
        return self::default_results_per_page;
    }

	////////////////////////////////////////////////////////////////////////////
	//
	//	Internal variables. Editing is useless.
	//
	////////////////////////////////////////////////////////////////////////////

	/// Search term
	private $search = '';

    private $search_object = null;

}
tx_newspaper_Extra::registerExtra(new tx_newspaper_extra_SearchResults());

?>