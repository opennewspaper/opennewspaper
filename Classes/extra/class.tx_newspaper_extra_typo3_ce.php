<?php

require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_extra.php');

/// tx_newspaper_Extra displaying one or several Typo3 Content Elements
/** Insert this Extra on Page Zones (or Articles, if you find a use for that) to
 *  display an existing Typo3 Content Element. Content Elements can be grouped
 *  to show more than one CE.
 *
 *  Attributes:
 *  - \p pool (bool)
 *  - \p content_elements (UIDs to table \p tt_content)
 */
class tx_newspaper_Extra_Typo3_CE extends tx_newspaper_Extra {

	public function __construct($uid = 0) {
		if ($uid) {
			parent::__construct($uid);
		}
	}

	public function __toString() {
		try {
		return 'Extra: UID ' . $this->getExtraUid() . ', Typo3 CE Extra: UID ' . $this->getUid() .
				' (Content Element(s): ' . $this->getAttribute('content_elements') . ')';
		} catch(Exception $e) {
			return "Typo3 CE: Exception thrown!" . $e;
		}
	}

	/** Use TypoScript to render the selected Content Elements
	 *
	 *  \param $template_set Ignored
	 *
	 *  \todo It's probably not wise to print the whole content of the DB records
	 *  	inside a HTML comment.
	 */
	public function render($template_set = '') {

        $this->prepare_render($template_set);

        /** @var tslib_cObj $cObj  */
		$cObj = t3lib_div::makeInstance('tslib_cObj');

		$rendered = array();
        $raw = array();

        $ce_ids = trim($this->getAttribute('content_elements'));
        if (!empty($ce_ids)) {
            foreach (explode(',', $this->getAttribute('content_elements')) as $ce_uid) {
                $attributes = tx_newspaper::selectOneRow('*', 'tt_content', "uid = $ce_uid");
                $raw[] = $attributes;

                if (TYPO3_MODE == 'BE') {
                    $rendered[] = self::printBasicCE($attributes);
                } else {
                    $ce = self::renderTypo3CE($ce_uid, $cObj);
                    $ce = self::stripAnchorBeforeCE($ce);
                    $rendered[] = $ce;
                }
            }
        }

        $this->smarty->assign('raw', $raw);
        $this->smarty->assign('rendered', $rendered);

        $ret = $this->smarty->fetch($this->getSmartyTemplate());

		return $ret;
	}

    /** Render the TypoScript equivalent of
     *  \code
     *  ce = RECORDS
     *  ce {
     *        tables = tt_content
     *        source = $ce_uid
     *        dontCheckPid = 1
     *    }
     *  \endcode
     *  I could render them all at once as a comma-separated list of
     *  UIDs instead of in a foreach-loop, but I don't trust that
     *  feature...
     *
     * \see http://www.nabble.com/rendering-a-modified-tt_content-record-inside-of-plugin-td22804683.html
     * \see http://www.typo3forum.net/forum/extension-modifizieren-neu-erstellen/17955-content-elemente-extension-rendern.html
     * \see http://www.typo3.net/forum/beitraege/thema/53494/
     */
    private static function renderTypo3CE($ce_uid, tslib_cObj $cObj) {
        $tt_content_conf = array(
            'tables' => 'tt_content',
            'source' => intval($ce_uid),
            'dontCheckPid' => 1,
        );
        $rendered = $cObj->RECORDS($tt_content_conf);
        tx_newspaper::devlog('renderTypo3CE()', array('rendered' => $rendered, 'processed' => self::processLinks($rendered)));
        return self::processLinks($rendered);
    }

    private static function stripAnchorBeforeCE($rendered) {
        $startpos = strpos($rendered, '<a id="');
        if ($startpos === false) return $rendered;
        $endpos = strpos($rendered, '</a>', $startpos+1);
        $head = substr($rendered, 0, $startpos);
        $tail = substr($rendered, $endpos);
        return $head . $tail;
    }
    
    /**	In BE (for e.g. unit tests), RECORDS() can't be used. Just show something.
     */
    private static function printBasicCE($attributes) {
        $rendered = '<h2>' . $attributes['header'] . "</h2>\n" .
                    '<img src="uploads/pics/' . $attributes['image'] . '" />' . "\n" .
                    $attributes['text'] . "\n";
        return $rendered;
    }

    const internal_regexp = '/\<link (\d+) .*?\>(.*?)\<\/link\>/i';
    const external_regexp = '/\<link (http:\/\/.+?) (.*?)\>(.*?)\<\/link\>/i';
    const simple_external_regexp = '/\<link (http:\/\/.+?)\>(.*?)\<\/link\>/i';
    /**
   	 *	return the text with all typo3-<link>-tags replaced with the appropriate <a> tags
   	 *  @param string $text typo3 pseudo-HTML text to process
   	 *  @return string valid HTML with straight <a>'s
   	 */
   	private static function processLinks($text) {
   		$textnew = preg_replace(self::internal_regexp,
   					            '<a href="'.self::makeLinkTarget(array('id' => '$1')).'">$2</a>',
   					            $text);
   		$textnew = preg_replace(self::external_regexp,
   								 '<a href="$1" target="$2">$3</a>',
   								 $textnew);
   		$textnew = preg_replace(self::simple_external_regexp,
   								 '<a href="$1">$2</a>',
   								 $textnew);
   		return $textnew;
   	}

    private static function makeLinkTarget(array $args) {
        return tx_newspaper::typolink_url($args);
    }

    /** If the CE has a header, display the header. Else if it has a titleText,
	 *  display that. Else just display its UID.
	 *  \todo This could probably be improved, especially for multiple CEs.
	 */
	public function getDescription() {
		try {
			$row = tx_newspaper::selectOneRow('*', 'tt_content', 'uid = ' . $this->getAttribute('content_elements'));
			if ($row['header']) return $row['header'];
			if ($row['titleText']) return $row['titleText'];
		} catch (tx_newspaper_DBException $e) { }

		return $this->getAttribute('short_description');
	}

    /// title for module
	public static function getModuleName() {
		return 'np_typo3_ce';
	}

	public static function dependsOnArticle() { return false; }

}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_Typo3_CE());

?>