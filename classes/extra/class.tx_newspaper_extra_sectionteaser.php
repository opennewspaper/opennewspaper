<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

/// tx_newspaper_Extra that renders
/** This Extra is used to place HTML in an article
 */

class tx_newspaper_Extra_sectionteaser extends tx_newspaper_Extra {

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

		## t3 developer log error handling
		#  tx_newspaper::devlog("render", $rendered);

		$this->prepare_render($template_set);

		$num_articles=$this->getAttribute('num_articles');
		$this->smarty->assign("num_articles",$num_articles);
		$num_articles_w_image=$this->getAttribute('num_articles_w_image');
		$this->smarty->assign("num_articles_w_image",$num_articles_w_image);

		$is_ctrltag = $this->getAttribute('is_ctrltag');
		if ($is_ctrltag=="0") {
			$this->smarty->assign("section_or_ctrltag","section");
			$section_id = $this->getAttribute('section');
			if (!empty($section_id)) {
				$section=new tx_newspaper_section($section_id);
				$this->smarty->assign("id",$section_id);
				$this->smarty->assign("articles",$section->getArticleList()->getArticles($num_articles));
			}
		}  elseif ($is_ctrltag=="1") {
			$this->smarty->assign("section_or_ctrltag","ctrltag");
			$ctrltag_cat = $this->getAttribute('ctrltag_cat');
			$this->smarty->assign("cat",$ctrltag_cat);
			$ctrltag_id = $this->getAttribute('ctrltag');
			if (!empty($ctrltag_id)) {
				$ctrltag = new tx_newspaper_tag($ctrltag_id);
				$this->smarty->assign("id",$ctrltag_id);
				$this->smarty->assign("articles",$ctrltag->getArticles($num_articles));
			}
		}

        $rendered = $this->smarty->fetch($this->getSmartyTemplate());

        return $rendered;
	}

	/** Displays the title and the beginning of the text.
	 */
	public function getDescription() {
		if ($desc = $this->getAttribute('short_description')) {
		} elseif ($desc = $this->getAttribute('notes')) {
			$desc = preg_replace("/<(.*)?>/U", "", $desc);
		} else {
			return;
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
