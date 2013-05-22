<?php

require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_extra.php');

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

        $this->prepare_render($template_set);
        $this->image->prepare_render($this->smarty);

        $this->smarty->assign('title', $this->getAttribute('title'));
        $this->smarty->assign('bodytext', tx_newspaper::convertRteField($this->getAttribute('bodytext')));

        $rendered = $this->smarty->fetch($this->getSmartyTemplate());

        return $rendered;
    }

    /** Displays the title and the beginning of the text.
     */
    public function getDescription() {
        return tx_newspaper::formatDescription($this->getAttribute('title'), $this->getAttribute('bodytext'), $this->getAttribute('short_description'));
    }

    /// title for module
    public static function getModuleName() {
        return 'np_textbox';
    }

    public static function dependsOnArticle() { return true; }

    /// Save hook function, called from the global save hook
    /** Resizes the uploaded image into all sizes specified in TSConfig.
     */
    public static function processDatamap_postProcessFieldArray(
        $status, $table, $id, &$fieldArray, $that
    ) {
        if ($table != 'tx_newspaper_extra_textbox') return;

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

}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_Textbox());

tx_newspaper::registerSaveHook(new tx_newspaper_Extra_Textbox());

?>