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
            $this->image = new tx_newspaper_Image($this->getAttribute('image_file'));
		} else {
            $this->image = new tx_newspaper_NullImage();
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

        $this->image->copyTo($this->getUploadFolder());
        
		$this->prepare_render($template_set);
        $this->smarty->assign('basepath', $this->getUploadFolder());

        $rendered = $this->smarty->fetch($this);

        return $rendered;
	}

	public function getDescription() {
		return $this->image->getThumbnail();
	}

    public function getUploadFolder() {

        $upload_folder_path = tx_newspaper_Image::getBasepath() . '/' . self::upload_folder_name;
        if (!file_exists($upload_folder_path)) {
            mkdir($upload_folder_path);
        }
        
        return $upload_folder_path;
    }

	/// title for module
	public static function getModuleName() {
		return 'np_extra_freeformimage';
	}
	
	public static function dependsOnArticle() { return false; }

    private $image = null;

    const upload_folder_name = 'freeform';
	
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_FreeFormImage());

?>