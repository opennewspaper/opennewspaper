<?php

require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_extra.php');

/// tx_newspaper_Extra that renders
/** This Extra is used to place HTML in an article
 */

class tx_newspaper_Extra_HTML extends tx_newspaper_Extra {

	const description_length = 50;

	/// Constructor
	public function __construct($uid = 0) {
		if ($uid) {
			parent::__construct($uid);
		}
	}

	public function __toString() {
		try {
			return 'Extra: UID ' . $this->getExtraUid() . ', HTML Extra: UID ' . $this->getUid();
		} catch(Exception $e) {
			return "HTML: Exception thrown!" . $e;
		}
	}

	/** Render a piece of HTML.
	/*  Smarty template:
	 *  \include res/templates/tx_newspaper_extra_html.tmpl
	 */
	public function render($template_set = '') {

		## t3 developer log error handling
		#  tx_newspaper::devlog("render", $rendered);

		$this->prepare_render($template_set);

		$this->smarty->assign('html', $this->getAttribute('html'));

        $rendered = $this->smarty->fetch($this->getSmartyTemplate());

        return $rendered;
	}

	/** Displays the title and the beginning of the text.
	 */
	public function getDescription() {
		if ($desc = $this->getAttribute('short_description')) {
		} elseif ($desc = $this->getAttribute('notes')) {
		} else {
            $desc = strip_tags($this->getAttribute('html'));
		}
		return substr(
			$desc,
			0, self::description_length + 2*strlen('<strong>') + 1
        );
	}

		/// title for module
	public static function getModuleName() {
		return 'np_extra_html';
	}

	public static function dependsOnArticle() { return false; }

}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_HTML());

?>
