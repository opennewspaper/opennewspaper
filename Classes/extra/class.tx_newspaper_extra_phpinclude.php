<?php

require_once(PATH_typo3conf . 'ext/newspaper/Classes/class.tx_newspaper_extra.php');

class tx_newspaper_IncludePathStack {

    public function __construct($additional_path) {
        $this->old_path = get_include_path();
        set_include_path($this->old_path . ':' . $additional_path);
    }

    public function __destruct() {
        set_include_path($this->old_path);
    }

    private $old_path = '';
}

/**
 *  This Extra is used to render PHP from a file.
 */
class tx_newspaper_Extra_PHPInclude extends tx_newspaper_Extra {

    public function __construct($uid = 0) { if ($uid) parent::__construct($uid); }

    public function __toString() {
        try {
            return 'Extra: UID ' . $this->getExtraUid() . ',PHP file: ' . $this->getAttribute('file');
        } catch(Exception $e) {
            return "PHP Include: Exception thrown!" . $e;
        }
    }

    /**
     *  Render PHP File. Currently only passes the name of the file to smarty.
     *  For actually evaluating the PHP and keeping its output use evaluatePHPFile().
     */
    public function render($template_set = '') {
        $this->prepare_render($template_set);
        $this->smarty->assign('file', $this->getAttribute('file'));
        return $this->smarty->fetch($this->getSmartyTemplate());
    }

    private function evaluatePHPFile() {
        if (!is_file($this->getAttribute('file'))) return '';

        $path_stack = new tx_newspaper_IncludePathStack(self::getBaseFolder());

        ob_start();
        include($this->getAttribute('file'));
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }


    public function getDescription() {
        return substr($this->getAttribute('short_description'), 0, tx_newspaper::description_length);
    }

    /// Title for module
    public static function getModuleName() { return 'np_extra_phpinclude'; }

    public static function dependsOnArticle() { return false; }

    /// Renders the menu for selecting a PHP file in the BE.
    /**
     * @param array $params Params array (call by reference!)
     * @param t3lib_TCEforms $pObj Parent object
     */
    public function addFileDropdownEntries(&$params, &$pObj) {

        if (!is_dir(self::getBaseFolder())) {
            $message = t3lib_div::makeInstance(
                't3lib_FlashMessage',
                tx_newspaper::getTranslation('flashMessage_extra_flexform_no_folder_configured'),
                'PHP Include', t3lib_FlashMessage::ERROR
            );
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

    private static function getPHPFiles() {
        return array_filter(
            self::getAllFiles(self::getBaseFolder(), self::isRecursive()),
            array('tx_newspaper_Extra_PHPInclude', 'isPHPFile')
        );
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
                $files = array_merge($files, self::getAllFiles($dir . $path . '/', $recursive));
            }
        }
        return $files;
    }

    private static function getTSconfig($key) {
        $tsconfig = tx_newspaper::getTSConfig();
        return $tsconfig['newspaper.']['extra_phpinclude.'][$key];
    }

    private static function getBaseFolder() {
        $base_folder = self::getTSconfig('basefolder');
        if (substr($base_folder, 0, 1) != '/') $base_folder = PATH_site . $base_folder;
        if (substr($base_folder, -1, 1) != '/') $base_folder .= '/';
        return $base_folder;
    }

    private static function allowedExtensions() {
        return array_map('trim', explode(',', self::getTSconfig('allowed_extensions')));
    }

    private static function isRecursive() {
        return intval(self::getTSconfig('recursive'));
    }

}

tx_newspaper_Extra::registerExtra(new tx_newspaper_Extra_PHPInclude());

?>