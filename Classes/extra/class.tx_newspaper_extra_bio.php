<?php

require_once(PATH_typo3conf . 'ext/newspaper/Classes/extra/class.tx_newspaper_extra_image.php');

/// A tx_newspaper_Extra that displays a biography for a contributor
/**
 *  A photo of an author (or any person, actually) is displayed along with some
 *  biographical text.
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
 *  The render() function is inherited from tx_newspaper_Extra_Image, but of course uses
 *  \c tx_newspaper_extra_bio.tmpl. Ah, the joys of inheritance...
 *
 *  @include res/templates/tx_newspaper_extra_bio.tmpl
 *
 *  @todo Import the box automatically from the pool when the Article is imported.
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

        $rendered = $this->smarty->fetch($this->getSmartyTemplate());

        return $rendered;
    }


    /// A description to identify the bio box in the BE. Shows the author's name and the start of the text.
	public function getDescription() {
        return tx_newspaper::formatDescription(
            $this->getAttribute('author_name'),
            $this->getAttribute('bio_text'),
            $this->getAttribute('short_description')
        );
	}

	public function getSearchFields() {
		return array('short_description', 'author_name', 'bio_text');
	}

    public static function getModuleName() { return 'np_bio'; }

    public static function dependsOnArticle() { return true; }

    /// Save hook function, called from the global save hook
    /** Resizes the uploaded image into all sizes specified in TSConfig. */
    public static function processDatamap_postProcessFieldArray(
        $status, $table, $id, &$fieldArray, $that
    ) {
        if ($table != 'tx_newspaper_extra_bio') return;

        $timer = tx_newspaper_ExecutionTimer::create();
        
        if ($fieldArray[self::image_file_field]) {
            $image = new tx_newspaper_Image($fieldArray[self::image_file_field]);
            if (class_exists('tx_AsynchronousTask')) {
                $task = new tx_AsynchronousTask($image, 'deployImages');
                $task->execute();
            } else {
                $image->deployImages();
            }
        }
    }


    public static function getBasepath() {
        return tx_newspaper_Image::getBasepath();
    }

    public function getSizes() {
        return $this->image->getSizes();
    }

    public function getWidths() {
        return $this->image->getWidths();
    }

    public function getHeights() {
        return $this->image->getHeights();
    }

    /** @var tx_newspaper_Image */
    private $image = null;

}

tx_newspaper_Extra::registerExtra(new tx_newspaper_extra_Bio());

tx_newspaper::registerSaveHook(new tx_newspaper_extra_Bio());

?>