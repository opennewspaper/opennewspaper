<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

class tx_newspaper_Extra_Textbox extends tx_newspaper_Extra {

	const description_length = 50; 

	public function __construct($uid = 0) {
		if ($uid) {
			parent::__construct($uid); 
		}
	}
	
	public function __toString() {
		try {
		return 'Extra: UID ' . $this->getExtraUid() . ', Textbox Extra: UID ' . $this->getUid() .
				' (Title: ' . $this->getAttribute('title') . ')';
		} catch(Exception $e) {
			return "Textbox: Exception thrown!" . $e;
		}	
	}
	
	/** Just a quick hack to see anything
	 */
	public function render($template_set = '') {


		$this->prepare_render($template_set);

		$this->smarty->assign('title', $this->getAttribute('title'));
		$this->smarty->assign('text', $this->getAttribute('text'));
		
		return $this->smarty->fetch($this);
	}

	/// \todo getLLL
	public function getTitle() {
		return 'Text Box';
	}

	public function getDescription() {
		return substr(
			'<strong>' . $this->getAttribute('title') . '</strong> ' . $this->getAttribute('text'), 
			0, self::description_length+2*strlen('<strong>')+1);
	}

	/// title for module
	public static function getModuleName() {
		return 'np_textbox';
	}
	
	public static function dependsOnArticle() { return true; }
	
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_Textbox());

?>