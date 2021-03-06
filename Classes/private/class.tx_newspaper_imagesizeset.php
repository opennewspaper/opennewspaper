<?php
/**
 * Author: Lene Preuss <lene.preuss@gmail.com>
 * Date:   1/26/12
 * Time:   3:45 PM
 */

class tx_newspaper_TSconfigControlled {

    protected static function getTSconfig() {
        $sysfolder = tx_newspaper_Sysfolder::getInstance()->getPidRootfolder();
        return t3lib_BEfunc::getPagesTSconfig($sysfolder);
    }

}

class tx_newspaper_ImageSizeSet extends tx_newspaper_TSconfigControlled {

    public function __construct($width_set_index = 0) {
        $this->index = intval($width_set_index);
    }

    public function __toString() {
        return "sizes: " . print_r(self::$widths, 1) .
                ", widths: " . print_r(self::$widths, 1) .
                ", heights: " . print_r(self::$heights, 1);
    }

    public static function getDataForFormatDropdown() {
        return self::readFormats();
    }

    public function getName() {
        $names = array_keys(self::getFormatConfig());
        return rtrim($names[$this->index-1], '.');
    }

    public function getLabel() {
        return self::getWidthSetLabel($this->index);
    }

    public function getSizes() {
        return self::readAndGetSizes($this->index);
    }

    public function getWidths() {
        return $this->readAndGetWidths();
    }

    public function getHeights() {
        return $this->readAndGetHeights();
    }

    ////////////////////////////////////////////////////////////////////////////

    private static function fillArrayForFormat(array &$prefilled, $function) {
        $i = sizeof($prefilled);
        foreach (self::getFormatConfig() as $key => $format) {
            if (is_array($format)) {
                $prefilled[$i] = self::$function($format, $i);
                $i++;
            } else {
                tx_newspaper::showFlashMessage(
                    sprintf(tx_newspaper::getTranslation('flashMessage_tsconfig_not_an_array'), "newspaper.image.format.$key", print_r($format, 1)),
                    tx_newspaper::getTranslation('flashMessage_title_illegal_usage')
                );
            }
        }
    }

    private static function getFormatConfig() {
        $TSconfig = self::getTSconfig();
        if (is_array($TSconfig['newspaper.']['image.']['format.'])) {
            return $TSconfig['newspaper.']['image.']['format.'];
        }
        return array();
    }

    private static function getLabelFromTSconfigForFormat(array $format_tsconfig, $i) {
        return array($format_tsconfig['label'], $i);
    }

    private static function getSizesFromTSconfigForFormat(array $format_tsconfig) {
        unset($format_tsconfig['label']);
        return $format_tsconfig;
    }

    private static function getWidthSetLabel($width_set) {
        $formats = self::readFormats();
        foreach ($formats as $format) {
            if ($format[1] == $width_set) return $format[0];
        }
        throw new tx_newspaper_IllegalUsageException("Width set label for set $width_set not found");
    }

    private static function readFormats() {
        $return = array (
            array("Default", 0)
        );

        self::fillFormatDropdownArray($return);

        return $return;
    }

    private static function fillFormatDropdownArray(array &$return) {
        self::fillArrayForFormat($return, 'getLabelFromTSconfigForFormat');
    }

    private function fillWidthOrHeightArray(array &$what, $index) {

        if (!empty($what)) return;

        foreach ($this->getSizes() as $key => $size) {
            $width_and_height = explode('x', $size);
            if (isset($width_and_height[$index])) {
                $what[$key] = $width_and_height[$index];
            }
        }
    }

    private static function setSizes() {
        self::$sizes[0] = self::getDefaultSizesArray();
        self::fillArrayForFormat(self::$sizes, 'getSizesFromTSconfigForFormat');
        tx_newspaper_ImageThumbnail::addThumbnailSizes(self::$sizes);
    }

    private static function getDefaultSizesArray() {
        $TSconfig = self::getTSconfig();
        return $TSconfig['newspaper.']['image.']['size.'];
    }

    private static function readAndGetSizes($index) {
        self::readTSConfig();
        return self::$sizes[$index];
    }

    private function readAndGetWidths() {
        $this->fillWidthOrHeightArray(self::$widths, 0);
        return self::$widths;
    }

    private function readAndGetHeights() {
        $this->fillWidthOrHeightArray(self::$heights, 1);
        return self::$heights;
    }

    ///    Read base path and predefined sizes for images
    /** The following parameters must be read from the TSConfig for the storage
     *  SysFolder for Image Extras:
     *  \code
     *  newspaper.image.basepath
     *  newspaper.image.size....
     *  \endcode
     *
     *  \return The whole TSConfig for the storage SysFolder for Image Extras
     */
    private static function readTSConfig() {

        $TSConfig = self::getTSconfig();

        if (!self::$sizes) self::setSizes();

        return $TSConfig;
    }

    private $index = 0;

    /// The list of image sizes, predefined in TSConfig
    private static $sizes = array();
    /// The list of image widths, predefined as sizes in TSConfig
    private static $widths = array();
    /// The list of image heights, predefined as sizes in TSConfig
    private static $heights = array();

}
