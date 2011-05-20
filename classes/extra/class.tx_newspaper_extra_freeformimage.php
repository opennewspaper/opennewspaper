<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_image.php');

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
        $this->instantiateImage();

		$this->prepare_render($template_set);

        $rendered = $this->smarty->fetch($this);
        tx_newspaper::devlog("tx_newspaper_Extra_FreeFormImage::render()", $rendered);
        tx_newspaper::logExecutionTime();
        return 'tx_newspaper_Extra_FreeFormImage::render()' . $rendered;
	}

	public function getDescription() {
        $this->instantiateImage();
		return $this->image->getThumbnail();
	}

    public function getUploadFolder() {

        if (!file_exists(tx_newspaper_Image::getBasepath() . '/freeform')) {
            mkdir(tx_newspaper_Image::getBasepath() . '/freeform');
        }
        return tx_newspaper_Image::getBasepath() . '/freeform';
    }

	/// title for module
	public static function getModuleName() {
		return 'np_extra_freeformimage';
	}
	
	public static function dependsOnArticle() { return false; }

    private function instantiateImage() {
        if (is_null($this->image)) {
            $this->image = new tx_newspaper_Image($this->getAttribute('image_file'));
        }
    }

    protected $image = null;

	
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_FreeFormImage());

?>