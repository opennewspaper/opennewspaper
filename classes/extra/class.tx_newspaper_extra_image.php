<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_sysfolder.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_image.php');

/// tx_newspaper_Extra that displays an image
/** This Extra displays an image, along with several parameters such as a title,
 *  caption or alt text.
 *
 *  The size of the image can be adjusted from the Smarty template, but it must
 *  be one of a list of sizes predefined in the TSConfig of the storage page of
 *  the Image Extras.
 *
 *  Image sizes can be defined like this (example):
 * 	\code
 *  newspaper.image.size.article = 500x300
 *  newspaper.image.size.teaser = 250x150
 *  \endcode
 *  Images are automatically resized to all defined sizes immediately on upload.
 *
 *  The sizes are supplied to the Smarty template as variable \p $sizes. An
 *  example for displaying the image in the Smarty template:
 *  \code
 *  	<img src="{$basepath}/{$sizes.article}/{$attributes.filename}" \>
 *  \endcode
 *
 *  The following member functions are especially useful in a Smarty template:
 *  - getBasepath()
 *  - getSizes()
 *  - getWidths()
 *  - getHeights()
 *  - getAttribute('filename')
 *  - getAttribute('alttext')
 *  - getAttribute('caption')
 *
 *  Attributes:
 *  - \p pool (bool)
 *  - \p title (string)
 *  - \p image_file (string)
 *  - \p caption (string)
 *  - \p normalized_filename (string)
 *  - \p kicker (string)
 *  - \p credit (string)
 *  - \p source (string)
 *  - \p type (int index to select tag)
 *  - \p alttext (string)
 *  - \p tags (UIDs of tx_newspaper_Tag)
 */
class tx_newspaper_Extra_Image extends tx_newspaper_Extra {

    /// The field which carries the image file
    const image_file_field = 'image_file';

    /// Create a tx_newspaper_Extra_Image
    public function __construct($uid = 0) {
        if ($uid) {
            parent::__construct($uid);
            $this->image = new tx_newspaper_Image($this->getAttribute(self::image_file_field));
        } else {
            $this->image = new tx_newspaper_NullImage();
        }
    }

    public function __toString() {
        $ret = '';
        try{
            $ret .= 'Extra: UID ' . $this->getExtraUid() . ', Image: UID ' . $this->getUid();
            $ret .= ' (Title: ' . $this->getAttribute('title') . ')';
        } catch (Exception $e) {  }
        return $ret;
    }

    /** Assigns image attributes and TSConfig parameters to smarty template,
     *  then renders it.
     *
     *  Smarty template:
     *  \include res/templates/tx_newspaper_extra_image.tmpl
     */
    public function render($template_set = '') {

        $this->prepare_render($template_set);

        $this->image->prepare_render($this->smarty);

        $this->smarty->assign('type', $this->getImageType());

        $rendered = $this->smarty->fetch($this);

        return $rendered;
    }

    /// A short description that makes an Extra uniquely identifiable in the BE
	/** Displays title and UID of the image, as well as a thumbnail of it.
	 */
	public function getDescription() {
		return $this->getAttribute('title') . $this->image->getThumbnail();
	}

    public function getImageType() {
        if (!in_array('image_type', tx_newspaper::getAttributes($this))) return false;

        $type_index = $this->getAttribute('image_type');
        $type_language_key = "tx_newspaper_extra_image.image_type.I.$type_index";
        $type_string = tx_newspaper::getTranslation($type_language_key, 'locallang_db.xml');
        return $type_string;
    }

    public function getSearchFields() {
        return array('title', 'caption', 'kicker');
    }

    /// Title for module to determine name of SysFolder this extra is stored under
	public static function getModuleName() {
		return 'np_image';
	}

	public static function dependsOnArticle() { return false; }


    public static function getBasepath() {
        return tx_newspaper_Image::getBasepath();
    }
    
    public static function getSizes() {
        return tx_newspaper_Image::getSizes();
    }

    public static function getWidths() {
        return tx_newspaper_Image::getWidths();
    }

    public static function getHeights() {
        return tx_newspaper_Image::getHeights();
    }

	/// Save hook function, called from the global save hook
	/** Resizes the uploaded image into all sizes specified in TSConfig.
	 *
	 *  \todo Appends the UID of the Extra_Image to the image file name.
	 *  \todo Normalizes field \p normalized_filename.
	 *
	 *  Damn the Typo3 documentation, I was unable to find authoritative docs
	 *  for processDatamap_postProcessFieldArray(). Here's what i could deduce.
	 *  \param $table The table of the record that is to be stored
	 *  \param $id The UID of the record that is to be stored
	 *  \param $fieldArray The values to be stored, as a reference so they can be changed
	 */
	public static function processDatamap_postProcessFieldArray(
		$status, $table, $id, &$fieldArray, $that
	) {

		if ($table != 'tx_newspaper_extra_image') return;
		if (!isset($fieldArray[self::image_file_field])) return;
        
        $image = new tx_newspaper_Image($fieldArray[self::image_file_field]);
        $image->resizeImages();
        $image->rsyncAllImageFiles();
	}

    ////////////////////////////////////////////////////////////////////////////

    private $image = null;
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_extra_Image());

tx_newspaper::registerSaveHook(new tx_newspaper_extra_Image());

//  Popups displaying enlarged images are handled here
if ($_GET['bild_fuer_artikel']) {
	//...
}
?>