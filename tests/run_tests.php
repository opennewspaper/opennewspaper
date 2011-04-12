<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lene
 * Date: 4/12/11
 * Time: 12:43 PM
 * To change this template use File | Settings | File Templates.
 */

if (!defined('PATH_typo3conf')) {
    define('PATH_typo3conf', run_tests::getPathTypo3Conf());
}
include run_tests::getNewspaperExtensionDir() . 'tx_newspaper_include.php';
include "class.Page_testcase.php";

class run_tests {

    static public function getPathTypo3Conf() {
        $working_directory = getcwd();
        $path_segments = explode('/', $working_directory);
        $path = '/';
        foreach($path_segments as $dir) {
            $path .= $dir . '/';
            if ($dir == 'typo3conf') return $path;
        }
    }

    static public function getNewspaperExtensionDir() {
        return self::getPathTypo3Conf() . 'ext/newspaper/';
    }
}


?>