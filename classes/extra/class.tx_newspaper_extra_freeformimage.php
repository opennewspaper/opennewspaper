<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_image.php');

/// tx_newspaper_Extra that renders an image without resizing it
class tx_newspaper_Extra_FreeFormImage extends tx_newspaper_Extra {

    const upload_folder_name = 'freeform';

    /// The field which carries the image file
    const image_file_field = 'image_file';

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

	/// Render the image.
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
		return $this->getAttribute('short_description') . $this->image->getThumbnail();
	}

    public function getUploadFolder() {

        $upload_folder_path = tx_newspaper_Image::getBasepath() . '/' . self::upload_folder_name;
        if (!file_exists($upload_folder_path)) {
            mkdir($upload_folder_path, 0775, true);
        }

        return $upload_folder_path;
    }

	/// title for module
	public static function getModuleName() {
		return 'np_extra_freeformimage';
	}

	public static function dependsOnArticle() { return false; }

    /// Save hook function, called from the global save hook
    /** Copies the uploaded image to the production server, if that is enabled.
     */
    public static function processDatamap_postProcessFieldArray(
        $status, $table, $id, &$fieldArray, $that
    ) {
        if ($table != 'tx_newspaper_extra_freeformimage') return;

        if ($fieldArray[self::image_file_field]) {
            $image = new tx_newspaper_Image($fieldArray[self::image_file_field]);
            $image->rsyncSingleImageFile(self::upload_folder_name);
        }
    }

    private $image = null;

}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_FreeFormImage());
