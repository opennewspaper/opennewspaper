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

    private $index = 0;

}
