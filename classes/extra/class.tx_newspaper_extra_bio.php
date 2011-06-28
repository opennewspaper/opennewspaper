<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/extra/class.tx_newspaper_extra_image.php');

/// A tx_newspaper_Extra that displays a bio for a contributor
/** A photo of an author is displayed along with some biographical text.
 *
 *  Attributes:
 *  - \p template_set (string)
 *  - \p pool (bool)
 *  - \p author_name (string)
 *  - \p author_id (string)
 *  - \p image_file (string)
 *  - \p photo_source (string)
 *  - \p bio_text (string)
 *
 *	The render() function is inherited from tx_newspaper_Extra_Image, but of
 *  course uses tx_newspaper_extra_bio.tmpl. Ah, the joys of inheritance...
 *  \include res/templates/tx_newspaper_extra_bio.tmpl
 *
 *  \todo Import the box automatically from the pool when the Article is
 * 		imported.
 */
class tx_newspaper_extra_Bio extends tx_newspaper_Extra {

	const description_length = 50;
    /// The field which carries the image file
    const image_file_field = 'image_file';

	public function __construct($uid = 0) {
        if ($uid) {
            parent::__construct($uid);
            $this->image = new tx_newspaper_Image($this->getAttribute(self::image_file_field));
        } else {
            $this->image = new tx_newspaper_NullImage();
        }
	}

    public function render($template_set = '') {

        $this->prepare_render($template_set);

        $this->image->prepare_render($this->smarty);

        $rendered = $this->smarty->fetch($this);

        return $rendered;
    }


	/// A description to identify the bio box in the BE
	/** Shows the author's name and the start of the text.
	 */
	public function getDescription() {
		return substr(
			($this->getAttribute('short_description')? $this->getAttribute('short_description') . '<br />' : '') .
			'<strong>' . $this->getAttribute('author_name') . '</strong> ' .
				$this->getAttribute('bio_text'),
			0, self::description_length+2*strlen('<strong>')+1) .
			(strlen($this->getAttribute('author_name') . ' ' . $this->getAttribute('bio_text')) > self::description_length?
				'...': '');
	}

	public function getSearchFields() {
		return array('short_description', 'author_name', 'bio_text');
	}

	public static function getModuleName() {
		return 'np_bio';
	}

	public static function dependsOnArticle() { return true; }

    /// Save hook function, called from the global save hook
    /** Resizes the uploaded image into all sizes specified in TSConfig.
     */
    public static function processDatamap_postProcessFieldArray(
        $status, $table, $id, &$fieldArray, $that
    ) {
        if ($table != 'tx_newspaper_extra_bio') return;
        tx_newspaper::devlog("bio save hook ($status, $table, $id)" , $fieldArray);
#$extra = new tx_newspaper_extra_Bio($id);
#tx_newspaper::devlog('bio save hook: extra '.$extra->getExtraUid().' origin uid '.$extra->getOriginUid());
        if ($fieldArray[self::image_file_field]) {
            $image = new tx_newspaper_Image($fieldArray[self::image_file_field]);
            $image->resizeImages();
        }
    }


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


    private $image = null;

}

tx_newspaper_Extra::registerExtra(new tx_newspaper_extra_Bio());

tx_newspaper::registerSaveHook(new tx_newspaper_extra_Bio());

?>