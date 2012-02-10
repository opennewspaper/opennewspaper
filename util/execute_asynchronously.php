<?php
/**
 * Author: Lene Preuss <lene.preuss@gmail.com>
 * Date:   2/9/12
 * Time:   6:22 PM
 */


$DIR = dirname(__FILE__);

function includeTypo3($script_basedir) {

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


// *******************************
// Define constants
// *******************************
define('TYPO3_OS', stristr(PHP_OS,'win')&&!stristr(PHP_OS,'darwin')?'WIN':'');
define('TYPO3_MODE','BE');
define(
    'PATH_thisScript',
    str_replace('//','/', str_replace('\\','/', (php_sapi_name()=='cgi'||php_sapi_name()=='isapi' ||php_sapi_name()=='cgi-fcgi')&&($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED'])? ($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED']):($_SERVER['ORIG_SCRIPT_FILENAME']?$_SERVER['ORIG_SCRIPT_FILENAME']:$_SERVER['SCRIPT_FILENAME']))));
define('TYPO3_mainDir', 'typo3/');		// This is the directory of the backend administration for the sites of this TYPO3 installation.


// *******************************
// Checking path
// *******************************
$temp_path = str_replace('\\','/',dirname(PATH_thisScript).'/');
$temp_modPath='';
	// If TYPO3_MOD_PATH is defined we must calculate the modPath since init.php must be included by a module
if (substr($temp_path,-strlen(TYPO3_mainDir))!=TYPO3_mainDir)	{
	if (defined('TYPO3_MOD_PATH'))	{
echo "<br />\ntemp path: " . substr($temp_path,-strlen(TYPO3_MOD_PATH)) . " ; MOD PATH: " . TYPO3_MOD_PATH . " <br />\n";
echo "end of temp path: " . substr($temp_path,-strlen(TYPO3_mainDir)) . " <br />\n";
		if (substr($temp_path,-strlen(TYPO3_MOD_PATH))==TYPO3_MOD_PATH)	{
			$temp_path=substr($temp_path,0,-strlen(TYPO3_MOD_PATH));
			$temp_modPath=TYPO3_MOD_PATH;
echo "branch 1; temp path $temp_path <br />\n";
		} elseif (substr(TYPO3_MOD_PATH,0,13)=='../typo3conf/' && (substr(TYPO3_MOD_PATH,3)==substr($temp_path,-strlen(substr(TYPO3_MOD_PATH,3))))) {
			$temp_path = substr($temp_path,0,-strlen(substr(TYPO3_MOD_PATH,3))).TYPO3_mainDir;
			$temp_modPath=TYPO3_MOD_PATH;
		}
		if (!@is_dir($temp_path))	{
			$temp_path='';
		}
	}
}

// OUTPUT error message and exit if there are problems with the path. Otherwise define constants and continue.
if (!$temp_path || substr($temp_path,-strlen(TYPO3_mainDir))!=TYPO3_mainDir)	{	// This must be the case in order to proceed

echo substr($temp_path,-strlen(TYPO3_mainDir)). " != ".TYPO3_mainDir . " <br />\n";

	if (TYPO3_OS=='WIN')	{
		$thisPath_base = basename(substr($temp_path,-strlen(TYPO3_mainDir)));
		$mainPath_base = basename(TYPO3_mainDir);
		if (!strcasecmp($thisPath, $mainPath))	{	// Seems like the requested URL is not case-specific. This may happen on Windows only. -case. Otherwise, redirect to the correct URL. TYPO3_mainDir must be lower-case!!
			$script_name = (php_sapi_name()=='cgi'||php_sapi_name()=='cgi-fcgi')&&($_SERVER['ORIG_PATH_INFO']?$_SERVER['ORIG_PATH_INFO']:$_SERVER['PATH_INFO']) ? ($_SERVER['ORIG_PATH_INFO']?$_SERVER['ORIG_PATH_INFO']:$_SERVER['PATH_INFO']) : ($_SERVER['ORIG_SCRIPT_NAME']?$_SERVER['ORIG_SCRIPT_NAME']:$_SERVER['SCRIPT_NAME']);	// Copied from t3lib_div::getIndpEnv()
			header('Location: '.str_replace($thisPath_base, $mainPath_base, $script_name));
			exit;
		}
	}

	echo 'Error in init.php: Path to TYPO3 main dir could not be resolved correctly. <br /><br />';

	echo '<font color="red"><strong>';
	if (strstr($temp_path,'typo3_src')) {
		echo 'It seems you are trying to run the TYPO3 source libraries DIRECTLY! You cannot do that.<br />
		Please read the installation documents for more information.';
	} else {
		$temp_path_parts = explode('/', $temp_path);
		$temp_path_parts = array_slice($temp_path_parts, count($temp_path_parts) - 3);
		$temp_path = '..../' . implode('/', $temp_path_parts);
		echo 'This happens if the last ' . strlen(TYPO3_mainDir) . ' characters of this path, ' . $temp_path . ' (end of $temp_path), is NOT "' . TYPO3_mainDir . '" for some reason.<br />
		You may have a strange server configuration.
		Or maybe you didn\'t set constant TYPO3_MOD_PATH in your module?';
	}
	echo '</strong></font>';

	echo '<br /><br />If you want to debug this issue, please edit typo3/init.php of your TYPO3 source and search for the die() call right after this line (search for this text to find)...';

// Remove this line if you want to debug this problem a little more...
#die();
	echo '<br /><br /><strong>If you expect any help from anybody on this issue, you should save this page as an html document and send it along with your request for help!</strong>';
	echo '<HR><pre>';
	print_r(array(
		'TYPO3_OS'=>TYPO3_OS,
		'PATH_thisScript'=>PATH_thisScript,
		'php_sapi_name()'=>php_sapi_name(),
		'TYPO3_MOD_PATH'=>TYPO3_MOD_PATH,
		'PATH_TRANSLATED'=>$_SERVER['PATH_TRANSLATED'],
		'SCRIPT_FILENAME'=>$_SERVER['SCRIPT_FILENAME']
	));
	echo '</pre><HR>';
	phpinfo();
	exit;
} else {
	define('PATH_typo3', $temp_path);			// Abs. path of the TYPO3 admin dir (PATH_site + TYPO3_mainDir).
	define('PATH_typo3_mod', $temp_modPath);	// Relative path (from the PATH_typo3) to a properly configured module
	define('PATH_site', substr(PATH_typo3,0,-strlen(TYPO3_mainDir)));	// Abs. path to directory with the frontend (one above the admin-dir)
	$temp_path_t3lib = @is_dir(PATH_site.'t3lib/') ? PATH_site.'t3lib/' : PATH_typo3.'t3lib/';
	define('PATH_t3lib', $temp_path_t3lib);			// Abs. path to t3lib/ (general TYPO3 library) within the TYPO3 admin dir
	define('PATH_typo3conf', PATH_site.'typo3conf/');	// Abs. TYPO3 configuration path (local, not part of source)
}

// *********************
// Unset variable(s) in global scope (fixes #13959)
// *********************
unset($error);

// *************************************************
// t3lib_div + extention management class included
// *************************************************
require_once(PATH_t3lib.'class.t3lib_div.php');		// The standard-library is included
require_once(PATH_t3lib.'class.t3lib_extmgm.php');	// Extension API Management library included

// ****************************************************
// Include configuration (localconf + ext_localconf)
// ****************************************************
require(PATH_t3lib.'config_default.php');
if (!defined ('TYPO3_db')) 	die ('The configuration file was not included.');

require_once(PATH_t3lib.'class.t3lib_db.php');		// The database library
$TYPO3_DB = t3lib_div::makeInstance('t3lib_DB');
$TYPO3_DB->debugOutput = $TYPO3_CONF_VARS['SYS']['sqlDebug'];

$CLIENT = t3lib_div::clientInfo();					// $CLIENT includes information about the browser/user-agent
$PARSETIME_START = t3lib_div::milliseconds();		// Is set to the system time in milliseconds. This could be used to output script parsetime in the end of the script




// *************************
// CLI dispatch processing
// *************************
if (defined('TYPO3_cliMode') && TYPO3_cliMode && basename(PATH_thisScript)=='cli_dispatch.phpsh')	{
		// First, take out the first argument (cli-key)
	$temp_cliScriptPath = array_shift($_SERVER['argv']);
	$temp_cliKey = array_shift($_SERVER['argv']);
	array_unshift($_SERVER['argv'],$temp_cliScriptPath);

		// If cli_key was found in configuration, then set up the cliInclude path and module name:
	if ($temp_cliKey)	{
		if (is_array($TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['cliKeys'][$temp_cliKey]))	{
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



// *********************
// Libraries included
// *********************
require_once(PATH_t3lib.'class.t3lib_userauth.php');
require_once(PATH_t3lib.'class.t3lib_userauthgroup.php');
require_once(PATH_t3lib.'class.t3lib_beuserauth.php');
require_once(PATH_t3lib.'class.t3lib_iconworks.php');
require_once(PATH_t3lib.'class.t3lib_befunc.php');
require_once(PATH_t3lib.'class.t3lib_cs.php');

// **********************
// Check Hardcoded lock on BE:
// **********************
if ($TYPO3_CONF_VARS['BE']['adminOnly'] < 0)	{
	header('Status: 404 Not Found');	// Send Not Found header - if the webserver can make use of it...
	header('Location: http://');	// Just point us away from here...
	exit;	// ... and exit good!
}

// **********************
// Check IP
// **********************
if (trim($TYPO3_CONF_VARS['BE']['IPmaskList']) && !(defined('TYPO3_cliMode') && TYPO3_cliMode))	{
	if (!t3lib_div::cmpIP(t3lib_div::getIndpEnv('REMOTE_ADDR'), $TYPO3_CONF_VARS['BE']['IPmaskList']))	{
		header('Status: 404 Not Found');	// Send Not Found header - if the webserver can make use of it...
		header('Location: http://');	// Just point us away from here...
		exit;	// ... and exit good!
	}
}


// **********************
// Check SSL (https)
// **********************
if (intval($TYPO3_CONF_VARS['BE']['lockSSL']) && !(defined('TYPO3_cliMode') && TYPO3_cliMode))	{
	if(intval($TYPO3_CONF_VARS['BE']['lockSSLPort'])) {
		$sslPortSuffix = ':'.intval($TYPO3_CONF_VARS['BE']['lockSSLPort']);
	} else {
		$sslPortSuffix = '';
	}
	if ($TYPO3_CONF_VARS['BE']['lockSSL'] == 3)	{
		$requestStr = substr(t3lib_div::getIndpEnv('TYPO3_REQUEST_SCRIPT'), strlen(t3lib_div::getIndpEnv('TYPO3_SITE_URL').TYPO3_mainDir));
		if($requestStr == 'index.php' && !t3lib_div::getIndpEnv('TYPO3_SSL'))	{
			list(,$url) = explode('://',t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'),2);
			list($server,$address) = explode('/',$url,2);
			header('Location: https://'.$server.$sslPortSuffix.'/'.$address);
			exit;
		}
	} elseif (!t3lib_div::getIndpEnv('TYPO3_SSL') )	{
		if ($TYPO3_CONF_VARS['BE']['lockSSL'] == 2)	{
			list(,$url) = explode('://',t3lib_div::getIndpEnv('TYPO3_SITE_URL').TYPO3_mainDir,2);
			list($server,$address) = explode('/',$url,2);
			header('Location: https://'.$server.$sslPortSuffix.'/'.$address);
		} else {
			header('Status: 404 Not Found');	// Send Not Found header - if the webserver can make use of it...
			header('Location: http://');	// Just point us away from here...
		}
		exit;	// ... and exit good!
	}
}


// *******************************
// Checking environment
// *******************************
if (isset($_POST['GLOBALS']) || isset($_GET['GLOBALS']))	die('You cannot set the GLOBALS-array from outside the script.');
if (!get_magic_quotes_gpc())	{
	t3lib_div::addSlashesOnArray($_GET);
	t3lib_div::addSlashesOnArray($_POST);
	$HTTP_GET_VARS = $_GET;
	$HTTP_POST_VARS = $_POST;
}


// ********************************************
// Check if the install script should be run:
// ********************************************
if (defined('TYPO3_enterInstallScript') && TYPO3_enterInstallScript)	{
	if(!t3lib_extMgm::isLoaded('install')) {
		die('Install Tool is not loaded as an extension.<br/>You must add the key "install" to the list of installed extensions in typo3conf/localconf.php, $TYPO3_CONF_VARS[\'EXT\'][\'extList\'].');
	}

	require_once(t3lib_extMgm::extPath('install').'mod/class.tx_install.php');
	$install_check = t3lib_div::makeInstance('tx_install');
	$install_check->allowUpdateLocalConf = 1;
	$install_check->init();
	exit;
}


// *************************
// Connect to the database
// *************************
if ($TYPO3_DB->sql_pconnect(TYPO3_db_host, TYPO3_db_username, TYPO3_db_password))	{
	if (!TYPO3_db)	{
		t3lib_BEfunc::typo3PrintError ('No database selected','Database Error');
		exit;
	} elseif (!$TYPO3_DB->sql_select_db(TYPO3_db))	{
		t3lib_BEfunc::typo3PrintError ('Cannot connect to the current database, "'.TYPO3_db.'"','Database Error');
		exit;
	}
} else {
	t3lib_BEfunc::typo3PrintError ('The current username, password or host was not accepted when the connection to the database was attempted to be established!','Database Error');
	exit;
}


// *******************************
// Checks for proper browser
// *******************************
if (!$CLIENT['BROWSER'] && !(defined('TYPO3_cliMode') && TYPO3_cliMode))	{
	t3lib_BEfunc::typo3PrintError ('Browser error','Your browser version looks incompatible with this TYPO3 version!',0);
	exit;
}


// ****************************************************
// Include tables customization (tables + ext_tables)
// ****************************************************
include (TYPO3_tables_script ? PATH_typo3conf.TYPO3_tables_script : PATH_t3lib.'stddb/tables.php');
	// Extension additions
if ($TYPO3_LOADED_EXT['_CACHEFILE'])	{
	include (PATH_typo3conf.$TYPO3_LOADED_EXT['_CACHEFILE'].'_ext_tables.php');
} else {
	include (PATH_t3lib.'stddb/load_ext_tables.php');
}
	// extScript
if (TYPO3_extTableDef_script)	{
	include (PATH_typo3conf.TYPO3_extTableDef_script);
}



// *******************************
// BackEnd User authentication
// *******************************
/*
	NOTICE:
	if constant TYPO3_PROCEED_IF_NO_USER is defined true (in the mainscript), this script will return even though a user did not log in!
*/
$BE_USER = t3lib_div::makeInstance('t3lib_beUserAuth');	// New backend user object
$BE_USER->warningEmail = $TYPO3_CONF_VARS['BE']['warning_email_addr'];
$BE_USER->lockIP = $TYPO3_CONF_VARS['BE']['lockIP'];
$BE_USER->auth_timeout_field = intval($TYPO3_CONF_VARS['BE']['sessionTimeout']);
$BE_USER->OS = TYPO3_OS;
$BE_USER->start();			// Object is initialized
$BE_USER->checkCLIuser();
$BE_USER->backendCheckLogin();	// Checking if there's a user logged in
$BE_USER->trackBeUser($TYPO3_CONF_VARS['BE']['trackBeUser']);	// Tracking backend user script hits

	// Setting the web- and filemount global vars:
$WEBMOUNTS = $BE_USER->returnWebmounts();		// ! WILL INCLUDE deleted mount pages as well!
$FILEMOUNTS = $BE_USER->returnFilemounts();


// ****************
// CLI processing
// ****************
if (defined('TYPO3_cliMode') && TYPO3_cliMode)	{
		// Status output:
	if (!strcmp($_SERVER['argv'][1],'status'))	{
		echo "Status of TYPO3 CLI script:\n\n";
		echo "Username [uid]: ".$BE_USER->user['username']." [".$BE_USER->user['uid']."]\n";
		echo "Database: ".TYPO3_db."\n";
		echo "PATH_site: ".PATH_site."\n";
		echo "\n";
		exit;
	}
}

}

if (true) {
    includeTypo3($DIR);
} else {
    define('PATH_typo3conf', $DIR . '/../../../');
}

require_once(dirname(__FILE__) . '/../classes/private/class.tx_newspaper_file.php');
require_once(dirname(__FILE__) . '/../tests/class.AsynchronousTask_testcase.php');

function getObjectFromObjectFile($filename) {
    $serialized_file = new tx_newspaper_File($filename);
    $serialized_object = $serialized_file->read();
    return unserialize($serialized_object);
}

$object = getObjectFromObjectFile($argv[1]);

echo "class: " . get_class($object) . ", method: " . $argv[2] . ", args: " . $argv[3];

