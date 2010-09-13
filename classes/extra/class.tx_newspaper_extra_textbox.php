<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

/// tx_newspaper_Extra displaying a text.
/** Insert this Extra in Articles or Page Zones which have a box containing some
 *  text. 
 * 
 *  Attributes:
 *  - \p pool
 *  - \p title
 *  - \p text
 *  - \p image
 */
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
	
	/** Assigns stuff to the smarty template and renders it.
	 * 
	 *  Smarty template:
	 *  \include res/templates/tx_newspaper_extra_textbox.tmpl
	 * 
	 *  \todo Just assign the attributes array, not specific attributes
	 */
	public function render($template_set = '') {

        tx_newspaper::startExecutionTimer();

		$this->prepare_render($template_set);

		$this->smarty->assign('title', $this->getAttribute('title'));
		$this->smarty->assign('text', $this->getAttribute('text'));
		if ($this->getAttribute('image')) {
			$image = new tx_newspaper_Image(intval($this->getAttribute('image')));
			$smarty->assign('image', $image);
			$smarty->assign('rendered_image', $image->render());
		}
		
        $rendered = $this->smarty->fetch($this);
        
        tx_newspaper::logExecutionTime();
        
        return $rendered;
	}

	/** Displays the title and the beginning of the text.
	 */
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