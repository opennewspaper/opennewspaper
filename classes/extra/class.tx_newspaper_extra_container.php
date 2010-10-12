<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

/// tx_newspaper_Extra displaying other Extras inside it.
/** 
 *  Attributes:
 *  - \p extras (UIDs of tx_newspaper_Extra records)
 */
class tx_newspaper_Extra_Container extends tx_newspaper_Extra {

	public function __construct($uid = 0) {
		if ($uid) {
			parent::__construct($uid); 
		}
	}

	public function __toString() {
		return 'Container::__toString()';
	}
	
	/// Assigns extras to be rendered to the smarty template and renders it.
	/** If no Extras match, returns nothing.
	 * 
	 *  Smarty template:
	 *  \include res/templates/tx_newspaper_extra_container.tmpl
	 */
	public function render($template_set = '') {
        
        tx_newspaper::startExecutionTimer();
        
		$extras = $this->getExtras();
		if (!$extras) { 
			tx_newspaper::logExecutionTime();
			return;
		}
		
		$rendered_extras = array();
		foreach ($extras as $extra) {
			$rendered_extras[] = $extra->render($template_set);
		}
				
		$this->prepare_render($template_set);

		$this->smarty->assign('extras', $rendered_extras);
		
		$template = $this->getAttribute('template');
        if (!$template) $themplate = $this;
        
        $rendered = $this->smarty->fetch($template);
        
        tx_newspaper::logExecutionTime();
        
        return $rendered;
	}

	/// Displays the Tag Zone operating on.
	public function getDescription() {
		return $this->__toString();
	}

	public static function getModuleName() {
		return 'np_extra_container';
	}
	
	public static function dependsOnArticle() { return false; }
	
	////////////////////////////////////////////////////////////////////////////
	
	///	Returns the Extras displayed in this Extra
	/** 
	 */
	private function getExtras() {
		$extra = array();
		$factory = new tx_newspaper_ExtraFactory(); // temporary fix; won't work in reality
		foreach (explode(',', $this->getAttribute('extras')) as $uid) {
			$extra[] = $factory->create($uid);
		}

		return $extra;
	}
	
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_Container());

?>