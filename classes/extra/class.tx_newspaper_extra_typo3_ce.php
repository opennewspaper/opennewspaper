<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

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


		foreach (explode(',', $this->getAttribute('content_elements')) as $ce_uid) {
            $attributes = tx_newspaper::selectOneRow('*', 'tt_content', "uid = $ce_uid");
            $raw[] = $attributes;

			if (TYPO3_MODE == 'BE') {
                $rendered[] = self::printBasicCE($attributes);
            } else {
                $rendered[] = self::renderTypo3CE($ce_uid, $cObj);
            }
		}

        $this->smarty->assign('raw', $raw);
        $this->smarty->assign('rendered', $rendered);

        $ret = $this->smarty->fetch($this->getTemplate());

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
            'dontCheckPid' => 1
        );
        $rendered = $cObj->RECORDS($tt_content_conf);
        return $rendered;
    }

    /**	In BE (for e.g. unit tests), RECORDS() can't be used. Just show something.
     */
    private static function printBasicCE($attributes) {
        $rendered = '<h2>' . $attributes['header'] . "</h2>\n" .
                    '<img src="uploads/pics/' . $attributes['image'] . '" />' . "\n" .
                    $attributes['text'] . "\n";
        return $rendered;
    }

    private function getTemplate() {
        $template = $this->getAttribute('template');
        if ($template) {
            if (strpos($template, '.tmpl') === false) {
                $template .= '.tmpl';
            }
        } else {
            $template = $this;
        }
        return $template;
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