<?php

require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_extra.php');

/**
 *  This Extra is used to render PHP from a file
 */

class tx_newspaper_Extra_PHPInclude extends tx_newspaper_Extra {

    const description_length = 50;

    public function __construct($uid = 0) { if ($uid) parent::__construct($uid); }

    public function __toString() {
        try {
            return 'Extra: UID ' . $this->getExtraUid() . ',PHP file: ' . $this->getAttribute('file');
        } catch(Exception $e) {
            return "PHP Include: Exception thrown!" . $e;
        }
    }

    /**
     *  Render PHP File.
     */
    public function render($template_set = '') {

        if (!is_file($this->getAttribute('file'))) return '';

        ob_start();
        include($this->getAttribute('file'));
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }


    public function getDescription() {
        return substr(
            $this->getAttribute('short_description'),
            0, self::description_length + 2*strlen('<strong>') + 1
        );
    }

    /// Title for module
    public static function getModuleName() { return 'np_extra_phpinclude'; }

    public static function dependsOnArticle() { return false; }

    /**
     * @param $params Params array (call by reference!)
     * @param $pObj t3lib_TCEforms Parent object
     * @return void
     */
    public function addFileDropdownEntries(&$params, &$pObj) {

        if (!is_dir(self::getBaseFolder())) {
            $message = t3lib_div::makeInstance('t3lib_FlashMessage', tx_newspaper::getTranslation('flashMessage_extra_flexform_no_folder_configured'), 'Flexform', t3lib_FlashMessage::ERROR);
            t3lib_FlashMessageQueue::addMessage($message);
            return;
        }

        // Fill $params array for TCEForms
        foreach(self::getPHPFiles() as $file) {
            $params['items'][] = array('0' => self::getFileName($file), '1' => $file);
        }

    }

    private static function getFileName($file) {
        return substr($file, strlen(self::getBaseFolder()));
    }

    private static function endsWith($string, $suffix) {
        return substr_compare($string, $suffix, -strlen($suffix), strlen($suffix)) === 0;
    }

    private static function isPHPFile($file) {
        if (!is_file($file)) return false;
        foreach (self::allowedExtensions() as $ext) {
            if (self::endsWith($file, $ext)) return true;
        }
        return false;
    }

    private static function getAllFiles($dir, $recursive) {
        $files = array();
        foreach (scandir($dir) as $path) {
            if (is_file($dir . $path)) {
                array_push($files, $dir . $path);
            }
            if ($recursive && $path != '.' && $path != '..' && is_dir($dir . $path)) {
                $files += self::getAllFiles($dir . $path . '/', $recursive);
            }
        }
        return $files;
    }

    private static function getPHPFiles() {
        return array_filter(self::getAllFiles(self::getBaseFolder(), self::isRecursive()), array('tx_newspaper_Extra_PHPInclude', 'isPHPFile'));
    }

    private static function getBaseFolder() {
        return PATH_site . '/fileadmin/php/php_verlagsformulare_neu/';
    }

    private static function allowedExtensions() {
        return array('.php', '.inc', '.incl');
    }

    private static function isRecursive() {
        return true;
    }
}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_PHPInclude());

?>