<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');

/// tx_newspaper_Extra that renders 
/** This Extra is used to place ads in an article 
 */  
class tx_newspaper_Extra_FreeFormImage extends tx_newspaper_Extra {

	/// Constructor
	public function __construct($uid = 0) {
		if ($uid) {
			parent::__construct($uid); 
		}
	}
	
	public function __toString() {
		try {
			return 'Extra: UID ' . $this->getExtraUid() . ', Free Form Image Extra: UID ' . $this->getUid();
		} catch(Exception $e) {
			return "Free Form Image: Exception thrown!" . $e;
		}	
	}
	
	/// Render an image.
	/**  Smarty template:
	 *  \include res/templates/tx_newspaper_extra_freeformimage.tmpl
	 */	
	public function render($template_set = '') {
        
        tx_newspaper::startExecutionTimer();
        
		$this->prepare_render($template_set);
		
		$template = $this->getAttribute('template');
		if ($template) {
			if (strpos($template, '.tmpl') === false) {
				$template .= '.tmpl';
			}
		} else {
			$template = $this;
		}

        $rendered = $this->smarty->fetch($template);
        
        tx_newspaper::logExecutionTime();
        
        return $rendered;
	}

	public function getDescription() {
		return $this->getTitle();
	}

    public function getUploadFolder() {
        if (!file_exists(PATH_site . '/uploads/images/freeform')) {
            mkdir(PATH_site . '/uploads/images/freeform');
        }
        return PATH_site . '/uploads/images/freeform';
    }

	/// title for module
	public static function getModuleName() {
		return 'np_extra_freeformimage';
	}
	
	public static function dependsOnArticle() { return false; }
	
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_FreeFormImage());

?>