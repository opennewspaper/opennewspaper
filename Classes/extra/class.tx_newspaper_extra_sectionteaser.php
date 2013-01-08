<?php

require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_extra.php');

/// tx_newspaper_Extra that renders
/** This Extra is used to place HTML in an article
 */

class tx_newspaper_Extra_Sectionteaser extends tx_newspaper_Extra {

	const description_length = 50;

	/// Constructor
	public function __construct($uid = 0) {
		if ($uid) {
			parent::__construct($uid);
		}
	}

	public function __toString() {
		try {
			return 'Extra: UID ' . $this->getExtraUid() . ', Sectionteaser - Extra: UID ' . $this->getUid();
		} catch(Exception $e) {
			return "Sectionteaster: Exception thrown!" . $e;
		}
	}

	/** Renders a section teaser.
	/*  Smarty template:
	 *  \include res/templates/tx_newspaper_extra_sectionteaser.tmpl
	 */
	public function render($template_set = '') {

		$this->prepare_render($template_set);

        $this->smarty->assign("num_articles", $this->getAttribute('num_articles'));
        $this->smarty->assign("num_articles_w_image", $this->getAttribute('num_articles_w_image'));

        $this->assignSpecificContent($this->getAttribute('is_ctrltag'));

        $rendered = $this->smarty->fetch($this->getSmartyTemplate());

        return $rendered;
	}

    private function assignSpecificContent($is_ctrltag) {
        if ($is_ctrltag == "0") {
            $this->smarty->assign("section_or_ctrltag", "section");
            $this->assignSectionAndPage($this->getAttribute('section'));
        } elseif ($is_ctrltag == "1") {
            $this->smarty->assign("section_or_ctrltag", "ctrltag");
            $this->smarty->assign("cat", $this->getAttribute('ctrltag_cat'));
            $this->assignControlTag($this->getAttribute('ctrltag'));
        }
    }

    private function assignControlTag($ctrltag_id) {
        $this->smarty->assign("tag_id", intval($ctrltag_id));
    }

    private function assignSectionAndPage($section_id) {
        $this->smarty->assign("section_id", $section_id);
        try {
            $section = new tx_newspaper_Section($section_id);
            $this->smarty->assign("page_id", $section->getTypo3PageID());
        } catch(tx_newspaper_Exception $e) { }
    }

    /** Displays the title and the beginning of the text.
	 */
	public function getDescription() {
		if ($desc = $this->getAttribute('short_description')) {
		} elseif ($desc = $this->getAttribute('notes')) {
			$desc = preg_replace("/<(.*)?>/U", "", $desc);
		} else {
			return '';
		}
		return substr(
			$desc,
			0, self::description_length+2*strlen('<strong>')+1);
	}


	/// title for module
	public static function getModuleName() {
		return 'np_extra_sectionteaser';
	}

	public static function dependsOnArticle() { return false; }

}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_sectionteaser());

?>
