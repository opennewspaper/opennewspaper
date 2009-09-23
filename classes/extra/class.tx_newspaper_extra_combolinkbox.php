<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

/// tx_newspaper_Extra displaying a box of links of various type
/** 
 */
class tx_newspaper_Extra_ComboLinkBox extends tx_newspaper_Extra {
		
	public function __construct($uid = 0) {
		if ($uid) {
			parent::__construct($uid); 
		}
	}
	
	public function __toString() {
		try {
		return 'Extra: UID ' . $this->getExtraUid() . ', Combo link box Extra: UID ' . $this->getUid();
		} catch(Exception $e) {
			return "ComboLinkBox: Exception thrown!" . $e;
		}	
	}
	
	/// Assigns extras to be rendered to the smarty template and renders it.
	public function render($template_set = '') {
				
		$this->prepare_render($template_set);

		// ...
		
		return $this->smarty->fetch($this);
	}

	/// Displays the Tag Zone operating on.
	public function getDescription() {
		return $this->getTitle() . '(' . $this->getUid() . ')';
	}

	public static function getModuleName() {
		return 'np_combo_link_box';
	}
	
	public static function dependsOnArticle() { return true; }
	
	////////////////////////////////////////////////////////////////////////////
		
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_ComboLinkBox());

?>