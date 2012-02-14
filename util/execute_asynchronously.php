<?php
/**
 * Author: Lene Preuss <lene.preuss@gmail.com>
 * Date:   2/9/12
 * Time:   6:22 PM
 */

function includeTypo3() {

    global $TYPO3_DB, $TYPO3_CONF_VARS, $MCONF, $TYPO3_LOADED_EXT, $BE_USER;

    /***************************************************************
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
    ***************************************************************/

    defineBasicConstants();

    $temp_path = checkPath();

    defineAdditionalConstants($temp_path);

    // Unset variable(s) in global scope (fixes #13959)
    unsetGlobalVariables();

    includeBasicClassDefinitions();

    includeConfiguration();

    $TYPO3_DB = initDB();

    cliDispatchProcessing($TYPO3_CONF_VARS, $MCONF);

    includeLibraries();

    checkEnvironment();

    connectToDB();

    cliProcessing($BE_USER);

    loadExtensions();

    includeTablesCustomization();

}

function loadExtensions() {

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

        if (isProhibitedExtension($_EXTKEY)) continue;

        includeExtensionConfigFile($ext, 'ext_localconf.php');
        includeExtensionConfigFile($ext, 'ext_tables.php');

    }

}

function includeExtensionConfigFile(array $ext, $file) {
    global $_EXTKEY, $TCA;
    if (isset($ext[$file])) {
        if (file_exists($ext[$file])) {
            require_once($ext[$file]);
        } else {
            echo "<strong>" . $ext[$file] . " missing!</strong><br />\n";
        }
    }
}

function isProhibitedExtension($ext) {
    if (in_array($ext, array('timtab', 'ch_rterecords', 'dmc_https', 'magpierss', 'smarty'))) return true;
    return false;
}

function includeTablesCustomization() {

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

function connectToDB() {
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

function checkEnvironment() {
    if (isset($_POST['GLOBALS']) || isset($_GET['GLOBALS'])) die('You cannot set the GLOBALS-array from outside the script.');
    if (!get_magic_quotes_gpc()) {
        t3lib_div::addSlashesOnArray($_GET);
        t3lib_div::addSlashesOnArray($_POST);
    }
}

function cliProcessing($BE_USER) {
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

function includeLibraries() {
#    require_once(PATH_t3lib . 'class.t3lib_userauth.php');
#    require_once(PATH_t3lib . 'class.t3lib_userauthgroup.php');
#    require_once(PATH_t3lib . 'class.t3lib_beuserauth.php');
    require_once(PATH_t3lib . 'class.t3lib_iconworks.php');
    require_once(PATH_t3lib . 'class.t3lib_befunc.php');
    require_once(PATH_t3lib . 'class.t3lib_cs.php');
}

function cliDispatchProcessing($TYPO3_CONF_VARS, $MCONF) {
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

function initDB() {
    require_once(PATH_t3lib . 'class.t3lib_db.php'); // The database library
    $TYPO3_DB = t3lib_div::makeInstance('t3lib_DB');
    return $TYPO3_DB;
}

function includeConfiguration() {
    require(PATH_t3lib . 'config_default.php');
    if (!defined('TYPO3_db')) die ('The configuration file was not included.');
}

function includeBasicClassDefinitions() {
    require_once(PATH_t3lib . 'class.t3lib_div.php'); // The standard-library is included
    require_once(PATH_t3lib . 'class.t3lib_extmgm.php'); // Extension API Management library included
}

function unsetGlobalVariables() {
    unset($error);
}

function defineAdditionalConstants($temp_path) {
    define('PATH_typo3', $temp_path); // Abs. path of the TYPO3 admin dir (PATH_site + TYPO3_mainDir).
    define('PATH_site', substr(PATH_typo3, 0, -strlen(TYPO3_mainDir))); // Abs. path to directory with the frontend (one above the admin-dir)
    $temp_path_t3lib = @is_dir(PATH_site . 't3lib/') ? PATH_site . 't3lib/' : PATH_typo3 . 't3lib/';
    define('PATH_t3lib', $temp_path_t3lib); // Abs. path to t3lib/ (general TYPO3 library) within the TYPO3 admin dir
    define('PATH_typo3conf', PATH_site . 'typo3conf/'); // Abs. TYPO3 configuration path (local, not part of source)
}

function checkPath() {
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

function defineBasicConstants() {
    define('TYPO3_OS', stristr(PHP_OS, 'win') && !stristr(PHP_OS, 'darwin') ? 'WIN' : '');
    define('TYPO3_MODE', 'BE');
    define(
    'PATH_thisScript',
    str_replace('//', '/', str_replace('\\', '/', (php_sapi_name() == 'cgi' || php_sapi_name() == 'isapi' || php_sapi_name() == 'cgi-fcgi') && ($_SERVER['ORIG_PATH_TRANSLATED'] ? $_SERVER['ORIG_PATH_TRANSLATED'] : $_SERVER['PATH_TRANSLATED']) ? ($_SERVER['ORIG_PATH_TRANSLATED'] ? $_SERVER['ORIG_PATH_TRANSLATED'] : $_SERVER['PATH_TRANSLATED']) : ($_SERVER['ORIG_SCRIPT_FILENAME'] ? $_SERVER['ORIG_SCRIPT_FILENAME'] : $_SERVER['SCRIPT_FILENAME']))));
    define('TYPO3_mainDir', 'typo3/'); // This is the directory of the backend administration for the sites of this TYPO3 installation.
}

////////////////////////////////////////////////////////////////////////////////

function getSerializedObjectFromFile($filename) {
    $serialized_file = new tx_newspaper_File($filename);
    $serialized_object = $serialized_file->read();
    return unserialize($serialized_object);
}


function executeMethod($object, $method, $args) {
    switch (sizeof($args)) {
        case 0:
            $object->$method();
            return;
        case 1:
            $object->$method($args[0]);
            return;
        case 2:
            $object->$method($args[0], $args[1]);
            return;
        case 3:
            $object->$method($args[0], $args[1], $args[3]);
            return;

        default:
            die("Calling methods with " . sizeof($args) . " not implemented, sorry");
    }
}


ini_set('memory_limit', '64M');

$DIR = dirname(__FILE__);

includeTypo3();
require_once($DIR . '/../classes/private/class.tx_newspaper_file.php');

$object = getSerializedObjectFromFile($argv[1]);
$method = $argv[2];
$args = getSerializedObjectFromFile($argv[3]);
$includes = getSerializedObjectFromFile($argv[4]);

if (is_array($includes)) {
    foreach ($includes as $include) {
        require_once($include);
    }
}

executeMethod($object, $method, $args);

unlink($argv[1]);
unlink($argv[3]);
unlink($argv[4]);