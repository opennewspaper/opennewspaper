<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

/// A tx_newspaper_Extra displaying articles that got the most comments recently
/** Depends on a patched version of \p sk_pagecomments.
 *
 *  Attributes:
 *  - \p hours (int)
 *  - \p num_favorites (int)
 *  - \p display_num (bool)
 *  - \p display_time (bool)
 */
class tx_newspaper_extra_MostCommented extends tx_newspaper_Extra {

	/// Boa Constructor ;-)
	/** Instantiates the associated Article List too. */
	public function __construct($uid = 0) {
		if (intval($uid)) {
			parent::__construct($uid);
		}
	}

	/** Assign the list of articles to a Smarty template. The template must
	 *  contain all the logic to display the articles.
	 *
	 *  \param $template_set Template set to use
	 *
	 *  Smarty template:
	 *  \include res/templates/tx_newspaper_extra_mostcommented.tmpl
	 */
	public function render($template_set = '') {
		t3lib_div::devlog('tx_newspaper_extra_MostCommented::render()', 'newspaper', 0,
			array(
				'uid' => $this->getUid(),
				'extra uid' => $this->getExtraUid(),
			)
		);

        tx_newspaper::startExecutionTimer();

		$this->prepare_render($template_set);

		$this->smarty->assign('articles', $this->getMostCommented());

        $rendered = $this->smarty->fetch($this->getSmartyTemplate());

        tx_newspaper::logExecutionTime();

        return $rendered;
	}

	public function getDescription() {
		return $this->getAttribute('short_description');
	}

	public static function getModuleName() {
		return 'np_mostcommented';
	}

	public static function dependsOnArticle() { return false; }

	////////////////////////////////////////////////////////////////////////////

	protected function getMostCommented() {

		$hits = array();

		while (sizeof($hits) < $this->getAttribute('num_favorites') &&
			$this->getAttribute('hours') <= self::FAVORITES_MAXAGE) {

			$hits = tx_newspaper::selectRows(
				    'COUNT(*) as num_comments,' . implode (',', self::$comment_fields),
				    self::comment_cache_table,
					(string)(time()-intval($this->getAttribute('hours'))*60*60).' <= crdate',
					'article',
					'num_comments DESC',
					'0, '.(string)(intval($this->getAttribute('num_favorites')))
				);

			if (sizeof($hits) < $this->getAttribute('num_favorites')) {
				$this->favoritesLifetime *= 2;
			}
		}
		t3lib_div::devlog('$hits', 'newspaper', 0, $hits);

		$article_data = array();
		foreach ($hits as $i => $row) {

			$link = $this->linkData($row);	///< \todo fix
			$row['link'] = $link['link'];
			$row['comments'] = $this->getLatestComments($row['article'], $row['num_comments']);
				/// \todo fix
				$row['commentlink'] = taz_base::typolink_url(
				    					array(
    										'id' => $this->getCommentPage($row['article']),
    										'art' => $row['article'],
    										'tx_skpagecomments_pi1[showComments]' => 1,
    										'tx_skpagecomments_pi1[showForm]' => 1,));
			$row['number'] = $i+1;

			$article_data[] = $row;
		}
		t3lib_div::devlog('$article_data', 'newspaper', 0, $article_data);

		return $article_data;
	}

	/** This function defines an interface that is only used in the derived
	 *  class tx_hptazfavorites_pi4.
	 *  Here it is empty.													  */
	protected function getLatestComments($uid, $total) { }

	/** This function defines an interface that enables derived classes to
	 *  select additional fields from the DB.
	 *  Here it is empty.													  */
	protected function additionalSelectFields() { return array(); }

	const FAVORITES_MAXAGE = 1200; // 50 days
	const comment_cache_table = 'tx_newspaper_comment_cache';
	protected static $comment_fields = array(
		'article', 'kicker', 'title', 'author', 'crdate'
	);

}

///	"Leserforum" from old onlinetaz. Maybe this is not needed, i copied it to have to code in one place, just in case.
class tx_newspaper_ReadersForum extends tx_newspaper_Extra_MostCommented {

	/** reimplemented from tx_hptazfavorites_pi2, called via late binding from
	 *  tx_hptazfavorites_pi2::displayMostCommented()
	 *
	 *	\param $uid uid of the corresponding article
	 *  \param $total Total number of comments for article \p $uid
	 */
	protected function getLatestComments($uid, $total) {

		$query = $GLOBALS['TYPO3_DB']->SELECTquery(
   	        'crdate, name, comment', 'tx_skpagecomments_comments',
       	    "pivar = 'art=".intval($uid)."' AND hidden = 0 AND deleted = 0",
            '', 'crdate DESC', '0,'.$this->numTeasers
        );
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);

    	if (!$res) return;

		$comments = array();
        while ($row =  $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
        	$row['id'] = md5($row['comment']);
        	try {
				$row['commentlink'] = taz_base::typolink_url(
				    					array(
	    									'id' => $this->getCommentPage(intval($uid)),
	    									'art' => intval($uid),
	    									'tx_skpagecomments_pi1[showComments]' => 1,
	    									'tx_skpagecomments_pi1[showForm]' => 1));
        	} catch (GesperrterArtikel $a) { }

	       	$comments[] = $row;
    	}
    	t3lib_div::devlog('comments', 'tx_hptazfavorites_pi4', 0, $comments);


    	$GLOBALS['smarty']->assign('comment_teasers', $comments);
    	$GLOBALS['smarty']->assign('comment_link',
    							   taz_base::typolink_url(
				    					array(
	    									'id' => $this->getCommentPage(intval($uid)),
	    									'art' => intval($uid),
	    									'tx_skpagecomments_pi1[showComments]' => 1,
	    									'tx_skpagecomments_pi1[showForm]' => 1)));
		$GLOBALS['smarty']->assign('shown_comments', $this->numTeasers);
		$GLOBALS['smarty']->assign('num_comments', $total);
		$query = $GLOBALS['TYPO3_DB']->SELECTquery(
   	        'COUNT(*) as total_num_comments', 'tx_skpagecomments_comments',
       	    "pivar = 'art=".intval($uid)."' AND hidden = 0 AND deleted = 0");
		t3lib_div::devlog('query for total_num_comments', 'tx_hptazfavorites_pi4', 0, $query);
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
    	if ($res) {
    		$row =  $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			t3lib_div::devlog('$row for total_num_comments', 'tx_hptazfavorites_pi4', 0, $row);
			$GLOBALS['smarty']->assign('total_num_comments', $row['total_num_comments']);
    	} else {
    		t3lib_div::devlog('failed query', 'tx_hptazfavorites_pi4', 0, $query);
    	}

        return $GLOBALS['smarty']->fetch('comments_ressort.tmpl');
	}

	/** reimplemented from tx_hptazfavorites_pi2, called via late binding from
	 *  tx_hptazfavorites_pi2::displayMostCommented()						  */
	protected function additionalSelectFields() {
		return array('article_title2, article_date');
	}

	const numTeasers = 3;

}

tx_newspaper_Extra::registerExtra(new tx_newspaper_extra_MostCommented());

?>