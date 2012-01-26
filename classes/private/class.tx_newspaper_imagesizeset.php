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

    protected static function fillArrayForFormat(array &$prefilled, $function) {
        $TSconfig = self::getTSconfig();
        $i = sizeof($prefilled);
        foreach ($TSconfig['newspaper.']['image.']['format.'] as $format) {
            $prefilled[$i] = self::$function($format, $i);
            $i++;
        }
    }

    private static function getLabelFromTSconfigForFormat(array $format_tsconfig, $i) {
        return array($format_tsconfig['label'], $i);
    }

    private static function getSizesFromTSconfigForFormat(array $format_tsconfig) {
        unset($format_tsconfig['label']);
        return $format_tsconfig;
    }

}

class tx_newspaper_ImageSizeSet extends tx_newspaper_TSconfigControlled {

    public function __construct($width_set_index = 0) {
        $this->index = intval($width_set_index);
    }

    public static function getDataForFormatDropdown() {
        return self::readFormats();
    }

    public function getLabel() {
        return self::getWidthSetLabel($this->index);
    }

    public function getSizes() {
        return self::getAllSizes($this->index);
    }

    public function getWidths() {
        return self::getAllWidths($this->index);
    }

    public function getHeights() {
        return self::getAllHeights($this->index);
    }

    /// Get the array of possible image sizes registered in TSConfig
   	public static function getAllSizes($width_set = 0) {
   		self::readTSConfig();
   		return self::$sizes[$width_set];
   	}

       public static function getAllWidths($width_set = 0) {
           self::fillWidthOrHeightArray(self::$widths, 0);
           return self::$widths[$width_set];
       }

       public static function getAllHeights($width_set = 0) {
           self::fillWidthOrHeightArray(self::$heights, 1);
           return self::$heights[$width_set];
       }

    ////////////////////////////////////////////////////////////////////////////

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

    private static function fillWidthOrHeightArray(array &$what, $index) {
        if (empty($what)) {
            foreach (self::getAllSizes() as $key => $size) {
                $width_and_height = explode('x', $size);
                if (isset($width_and_height[$index])) {
                    $what[$key] = $width_and_height[$index];
                }
            }
        }
    }

    private static function setSizes() {
        self::$sizes[0] = self::getDefaultSizesArray();
        self::fillArrayForFormat(self::$sizes, 'getSizesFromTSconfigForFormat');
        for ($i = 1; $i < sizeof(self::$sizes); $i++) {
            self::$sizes[$i][tx_newspaper_Image::thumbnail_name] = tx_newspaper_Image::thumbnail_size;
        }
    }

    private static function getDefaultSizesArray() {
        $TSconfig = self::getTSconfig();
        $sizes = $TSconfig['newspaper.']['image.']['size.'];
        if (!isset($sizes[tx_newspaper_Image::thumbnail_name])) {
            $sizes[tx_newspaper_Image::thumbnail_name] = tx_newspaper_Image::thumbnail_size;
        }
        return $sizes;
    }

    ///	Read base path and predefined sizes for images
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
