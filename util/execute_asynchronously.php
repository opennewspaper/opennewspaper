<?php

/// This file is the delegate script executed in the background by tx_newspaper_AsynchronousTask.
/**
 *  @author Lene Preuss <lene.preuss@gmail.com>
 *  @file execute_asynchronously.php
 *
 *  This script must be called with (up to) four command line arguments:
 *  \code
 *  php execute_asynchronously <object_file> <method_name> [<arguments_file> [<includes_file>]]
 *  \endcode
 *  - \c object_file contains the object a method is called on in serialized form
 *  - \c method_name is the method that is called on the object
 *  - \c arguments_file contains optional arguments passed to the method in
 *       serialized form
 *  - \c includes_file contains an optional array of included PHP files in
 *       serialized form
 *
 *  The given method on the given object is executed with the given arguments,
 *  after including the given PHP include files. Before doing any of that, the
 *  Typo3 framework is included and initialized.
 */

/**
 *  Loads the Typo3 framework. A refactored version of the script init.php from
 *  Typo3 4.2.
 *
 *  The framework is not loaded completely; the user authentication is skipped
 *  (because this script is standalone, there is no user logged in and authentication
 *  would fail). Also some extensions are not loaded, because including the
 *  extension files led to problems; rather than fixing those problems I excluded
 *  the extensions.
 *
 *  Copyright notice
 *
 *  (c) 1999-2008 Kasper Skaarhoj (kasperYYYY@typo3.com)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */
class LoadTypo3 {

    /**
     *  These extensions do not load properly when included with \c require_once()
     *  in \c loadExtensions(). This list may need to be completed.
     */
    private static $prohibited_extensions = array('timtab', 'ch_rterecords', 'dmc_https', 'magpierss', 'smarty');

    public static function includeTypo3() {

        global $TYPO3_DB, $TYPO3_CONF_VARS, $MCONF, $TYPO3_LOADED_EXT, $BE_USER;

        self::defineBasicConstants();

        $temp_path = self::checkPath();

        self::defineAdditionalConstants($temp_path);

        self::unsetGlobalVariables();

        self::includeBasicClassDefinitions();

        self::includeConfiguration();

        $TYPO3_DB = self::initDB();

        self::cliDispatchProcessing($TYPO3_CONF_VARS, $MCONF);

        self::includeLibraries();

        self::checkEnvironment();

        self::connectToDB();

        self::cliProcessing($BE_USER);

        self::loadExtensions();

        self::includeTablesCustomization();

    }

    ////////////////////////////////////////////////////////////////////////////

    private static function defineBasicConstants() {
        define('TYPO3_OS', stristr(PHP_OS, 'win') && !stristr(PHP_OS, 'darwin') ? 'WIN' : '');
        define('TYPO3_MODE', 'BE');
        define(
        'PATH_thisScript',
        str_replace('//', '/', str_replace('\\', '/', (php_sapi_name() == 'cgi' || php_sapi_name() == 'isapi' || php_sapi_name() == 'cgi-fcgi') && ($_SERVER['ORIG_PATH_TRANSLATED'] ? $_SERVER['ORIG_PATH_TRANSLATED'] : $_SERVER['PATH_TRANSLATED']) ? ($_SERVER['ORIG_PATH_TRANSLATED'] ? $_SERVER['ORIG_PATH_TRANSLATED'] : $_SERVER['PATH_TRANSLATED']) : ($_SERVER['ORIG_SCRIPT_FILENAME'] ? $_SERVER['ORIG_SCRIPT_FILENAME'] : $_SERVER['SCRIPT_FILENAME']))));
        define('TYPO3_mainDir', 'typo3/'); // This is the directory of the backend administration for the sites of this TYPO3 installation.
    }

    private static function checkPath() {
        $temp_path = str_replace('\\', '/', dirname(PATH_thisScript) . '/');

        $pos = strpos($temp_path, 'typo3conf');
        $temp_path = substr($temp_path, 0, $pos + 5) . '/';

        // OUTPUT error message and exit if there are problems with the path. Otherwise define constants and continue.
        if (!$temp_path || substr($temp_path, -strlen(TYPO3_mainDir)) != TYPO3_mainDir) { // This must be the case in order to proceed

            echo 'Error in init.php: Path to TYPO3 main dir could not be resolved correctly. <br /><br />';

            echo '<font color="red"><strong>';

            $temp_path_parts = explode('/', $temp_path);
            $temp_path_parts = array_slice($temp_path_parts, count($temp_path_parts) - 3);
            $temp_path = '..../' . implode('/', $temp_path_parts);
            echo 'This happens if the last ' . strlen(TYPO3_mainDir) . ' characters of this path, ' . $temp_path . ' (end of $temp_path), is NOT "' . TYPO3_mainDir . '" for some reason.<br />
                You may have a strange server configuration.
                Or maybe you didn\'t set constant TYPO3_MOD_PATH in your module?';

            echo '</strong></font>';

            exit;
        }
        return $temp_path;
    }

    private static function defineAdditionalConstants($temp_path) {
        define('PATH_typo3', $temp_path); // Abs. path of the TYPO3 admin dir (PATH_site + TYPO3_mainDir).
        define('PATH_site', substr(PATH_typo3, 0, -strlen(TYPO3_mainDir))); // Abs. path to directory with the frontend (one above the admin-dir)
        $temp_path_t3lib = @is_dir(PATH_site . 't3lib/') ? PATH_site . 't3lib/' : PATH_typo3 . 't3lib/';
        define('PATH_t3lib', $temp_path_t3lib); // Abs. path to t3lib/ (general TYPO3 library) within the TYPO3 admin dir
        define('PATH_typo3conf', PATH_site . 'typo3conf/'); // Abs. TYPO3 configuration path (local, not part of source)
    }

    /** Unset variable(s) in global scope (fixes #13959) */
    private static function unsetGlobalVariables() {
        global $error;
        unset($error);
    }

    private static function includeBasicClassDefinitions() {
        require_once(PATH_t3lib . 'class.t3lib_div.php'); // The standard-library is included
        require_once(PATH_t3lib . 'class.t3lib_extmgm.php'); // Extension API Management library included
    }

    private static function includeConfiguration() {
        require(PATH_t3lib . 'config_default.php');
        if (!defined('TYPO3_db')) die ('The configuration file was not included.');
    }

    private static function initDB() {
        require_once(PATH_t3lib . 'class.t3lib_db.php'); // The database library
        $TYPO3_DB = t3lib_div::makeInstance('t3lib_DB');
        return $TYPO3_DB;
    }

    private static function cliDispatchProcessing($TYPO3_CONF_VARS, $MCONF) {
        if (defined('TYPO3_cliMode') && TYPO3_cliMode && basename(PATH_thisScript) == 'cli_dispatch.phpsh') {
            // First, take out the first argument (cli-key)
            $temp_cliScriptPath = array_shift($_SERVER['argv']);
            $temp_cliKey = array_shift($_SERVER['argv']);
            array_unshift($_SERVER['argv'], $temp_cliScriptPath);

            // If cli_key was found in configuration, then set up the cliInclude path and module name:
            if ($temp_cliKey) {
                if (is_array($TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['cliKeys'][$temp_cliKey])) {
                    define('TYPO3_cliInclude', t3lib_div::getFileAbsFileName($TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['cliKeys'][$temp_cliKey][0]));
                    $MCONF['name'] = $TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['cliKeys'][$temp_cliKey][1];
                } else {
                    echo "The supplied 'cliKey' was not valid. Please use one of the available from this list:\n\n";
                    print_r(array_keys($TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['cliKeys']));
                    echo "\n";
                    exit;
                }
            } else {
                echo "Please supply a 'cliKey' as first argument. The following are available:\n\n";
                print_r($TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['cliKeys']);
                echo "\n";
                exit;
            }
        }
    }

    private static function includeLibraries() {
    #    require_once(PATH_t3lib . 'class.t3lib_userauth.php');
    #    require_once(PATH_t3lib . 'class.t3lib_userauthgroup.php');
    #    require_once(PATH_t3lib . 'class.t3lib_beuserauth.php');
        require_once(PATH_t3lib . 'class.t3lib_iconworks.php');
        require_once(PATH_t3lib . 'class.t3lib_befunc.php');
        require_once(PATH_t3lib . 'class.t3lib_cs.php');
    }

    private static function checkEnvironment() {
        if (isset($_POST['GLOBALS']) || isset($_GET['GLOBALS'])) die('You cannot set the GLOBALS-array from outside the script.');
        if (!get_magic_quotes_gpc()) {
            t3lib_div::addSlashesOnArray($_GET);
            t3lib_div::addSlashesOnArray($_POST);
        }
    }

    private static function connectToDB() {
        global $TYPO3_DB;

        if ($TYPO3_DB->sql_pconnect(TYPO3_db_host, TYPO3_db_username, TYPO3_db_password)) {
            if (!TYPO3_db) {
                t3lib_BEfunc::typo3PrintError('No database selected', 'Database Error');
                exit;
            } elseif (!$TYPO3_DB->sql_select_db(TYPO3_db)) {
                t3lib_BEfunc::typo3PrintError('Cannot connect to the current database, "' . TYPO3_db . '"', 'Database Error');
                exit;
            }
        } else {
            t3lib_BEfunc::typo3PrintError('The current username, password or host was not accepted when the connection to the database was attempted to be established!', 'Database Error');
            exit;
        }
    }

    private static function cliProcessing($BE_USER) {
        if (defined('TYPO3_cliMode') && TYPO3_cliMode) {
            // Status output:
            if (!strcmp($_SERVER['argv'][1], 'status')) {
                echo "Status of TYPO3 CLI script:\n\n";
                echo "Username [uid]: " . $BE_USER->user['username'] . " [" . $BE_USER->user['uid'] . "]\n";
                echo "Database: " . TYPO3_db . "\n";
                echo "PATH_site: " . PATH_site . "\n";
                echo "\n";
                exit;
            }
        }
    }

    private static function loadExtensions() {

        global $TYPO3_CONF_VARS, $TYPO3_LOADED_EXT, $_EXTKEY;

        require(PATH_typo3conf . '/localconf.php');

        if (strpos($TYPO3_CONF_VARS['EXT']['extList'], 'lang') === false) {
            if (empty($TYPO3_CONF_VARS['EXT']['extList'])) {
                $TYPO3_CONF_VARS['EXT']['extList'] = 'lang';
            } else {
                $TYPO3_CONF_VARS['EXT']['extList'] .= ',lang';
            }
        }

        $TYPO3_LOADED_EXT = t3lib_extMgm::typo3_loadExtensions();

        foreach ($TYPO3_LOADED_EXT as $_EXTKEY => $ext) {

            if (self::isProhibitedExtension($_EXTKEY)) continue;

            self::includeExtensionConfigFile($ext, 'ext_localconf.php');
            self::includeExtensionConfigFile($ext, 'ext_tables.php');

        }

    }

    private static function includeExtensionConfigFile(array $ext, $file) {
        global $_EXTKEY, $TCA;
        if (isset($ext[$file])) {
            if (file_exists($ext[$file])) {
                require_once($ext[$file]);
            } else {
                echo "<strong>" . $ext[$file] . " missing!</strong><br />\n";
            }
        }
    }

    private static function isProhibitedExtension($ext) {
        if (in_array($ext, self::$prohibited_extensions)) return true;
        return false;
    }

    private static function includeTablesCustomization() {

        global $TYPO3_LOADED_EXT;

        include (TYPO3_tables_script ? PATH_typo3conf . TYPO3_tables_script : PATH_t3lib . 'stddb/tables.php');
        // Extension additions
        if ($TYPO3_LOADED_EXT['_CACHEFILE']) {
            include (PATH_typo3conf . $TYPO3_LOADED_EXT['_CACHEFILE'] . '_ext_tables.php');
        } else {
            include (PATH_t3lib . 'stddb/load_ext_tables.php');
        }
        // extScript
        if (TYPO3_extTableDef_script) {
            include (PATH_typo3conf . TYPO3_extTableDef_script);
        }
    }

}

////////////////////////////////////////////////////////////////////////////////

/**
 *  This class encapsulates the execution of a method on an object within Typo3.
 *
 *  Basically this functionality could have been provided via global functions,
 *  but both the source code and the documentation look cleaner this way. Yay
 *  for folding!
 */
class MethodExecutor {

    /**
     *  Loads Typo3 framework, includes all necessary PHP files and stores the
     *  operands necessary to run the task.
     */
    public function __construct(array $argv) {
        self::includeEverythingNecessary($argv[4]);

        $this->object = self::getSerializedObjectFromFile($argv[1]);
        $this->method = $argv[2];
        $this->args = self::getSerializedArgsFromFile($argv[3]);

        $this->argv = $argv;
    }

    /**
     *  Cleans up after itself.
     */
    public function __destruct() {
        $this->deleteTemporaryFiles();
    }

    /**
     *  Runs the task.
     */
    public function executeMethod() {

        // make the body of the switch-statement more readable
        $object = $this->object;
        $method = $this->method;

        switch (sizeof($this->args)) {
            case 0:
                $object->$method();
                return;
            case 1:
                $object->$method($this->args[0]);
                return;
            case 2:
                $object->$method($this->args[0], $this->args[1]);
                return;
            case 3:
                $object->$method($this->args[0], $this->args[1], $this->args[3]);
                return;

            default:
                die("Calling methods with " . sizeof($this->args) . " not implemented, sorry");
        }
    }

    ////////////////////////////////////////////////////////////////////////////

    private function deleteTemporaryFiles() {
        unlink($this->argv[1]);
        unlink($this->argv[3]);
        unlink($this->argv[4]);
    }

    private static function getSerializedObjectFromFile($filename) {
        $serialized_file = new tx_newspaper_File($filename);
        $serialized_object = $serialized_file->read();
        return unserialize($serialized_object);
    }

    private static function getSerializedArgsFromFile($file) {
        $args = self::getSerializedObjectFromFile($file);
        if (!is_array($args)) {
            $args = array();
        }
        return $args;
    }

    private static function includeEverythingNecessary($serialized_includes_file) {

        LoadTypo3::includeTypo3();

        require_once(dirname(__FILE__) . '/../classes/private/class.tx_newspaper_file.php');

        $includes = self::getSerializedObjectFromFile($serialized_includes_file);

        if (is_array($includes)) {
            foreach ($includes as $include) {
                require_once($include);
            }
        }
    }

    private $object = null;
    private $method = '';
    private $args = array();
    private $argv = array();

}


ini_set('memory_limit', '64M');

$executor = new MethodExecutor($argv);

$executor->executeMethod();

