<?php
/**
 *  \file resolverealurl.php
 * 
 *  This file is part of the TYPO3 extension "newspaper".
 * 
 *  Copyright notice
 *
 *  (c) 2008 Helge Preuss, Oliver Schroeder, Samuel Talleux <helge.preuss@gmail.com, oliver@schroederbros.de, samuel@talleux.de>
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
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 *  
 *  \author Helge Preuss <helge.preuss@gmail.com>
 *  \date Jan 18, 2010
 */


//
// the following is copied and adapted from index.php
//

error_reporting (E_ALL ^ E_NOTICE);                                     // stfu

define('PATH_thisScript',
    str_replace('//','/', 
        str_replace('\\','/', 
            (php_sapi_name()=='cgi' || php_sapi_name()=='isapi' || php_sapi_name()=='cgi-fcgi')&&
             ($_SERVER['ORIG_PATH_TRANSLATED']? 
              $_SERVER['ORIG_PATH_TRANSLATED']: 
                $_SERVER['PATH_TRANSLATED'])? 
                ($_SERVER['ORIG_PATH_TRANSLATED']? $_SERVER['ORIG_PATH_TRANSLATED']: $_SERVER['PATH_TRANSLATED']):
                  $_SERVER['SCRIPT_FILENAME']
        )
    )
);

define('PATH_site', tx_newspaper_ResolveRealURL::base_path . '/');

if (@is_dir(PATH_site.'typo3/sysext/cms/tslib/')) {
    define('PATH_tslib', PATH_site.'typo3/sysext/cms/tslib/');
} elseif (@is_dir(PATH_site.'tslib/')) {
    define('PATH_tslib', PATH_site.'tslib/');
}
if (PATH_tslib=='') {
    die('Cannot find tslib/. Please set path by defining $configured_tslib_path in '.basename(PATH_thisScript).'.');
}

//
// the following is copied and adapted from typo3/sysext/cms/tslib/index_ts.php
//

define('TYPO3_MODE','FE');

if (!defined('PATH_t3lib')) define('PATH_t3lib', PATH_site.'t3lib/');

define('PATH_typo3conf', PATH_site.'typo3conf/');
if (!@is_dir(PATH_typo3conf))   die('Cannot find configuration. This file is probably executed from the wrong location.');

require_once(PATH_t3lib.'class.t3lib_div.php');
require_once(PATH_t3lib.'class.t3lib_extmgm.php');

require_once(PATH_t3lib.'config_default.php');
// the name of the TYPO3 database is stored in this constant. Here the inclusion of the config-file is verified by checking if this var is set.
if (!defined ('TYPO3_db'))  die ('The configuration file was not included.');   

require_once(PATH_t3lib.'class.t3lib_db.php');
$TYPO3_DB = t3lib_div::makeInstance('t3lib_DB');
$TYPO3_DB->connectDB();

if (!t3lib_extMgm::isLoaded('newspaper')) die('newspaper not loaded.');

// needed for tslib_cObj::typolink()
require_once(PATH_tslib.'class.tslib_content.php');
require_once(PATH_tslib.'class.tslib_fe.php');

require_once(PATH_t3lib.'class.t3lib_timetrack.php');
$TT = new t3lib_timeTrack;  //  $TSFE needs this.
$TT->start();

$temp_TSFEclassName = t3lib_div::makeInstanceClassName('tslib_fe');
$TSFE = new $temp_TSFEclassName(
        $TYPO3_CONF_VARS,
        tx_newspaper_ResolveRealURL::article_typo3_page,
        t3lib_div::_GP('type'),
        t3lib_div::_GP('no_cache'),
        t3lib_div::_GP('cHash'),
        t3lib_div::_GP('jumpurl'),
        t3lib_div::_GP('MP'),
        t3lib_div::_GP('RDCT')
    );
    
$TSFE->connectToDB();
#    $TSFE->initFEuser();
$TSFE->workspacePreviewInit();
        $TSFE->checkAlternativeIdMethods();
    $TSFE->clear_preview();
 #   $TSFE->determineId();
    $TSFE->makeCacheHash();
    $TSFE->getCompressedTCarray();
    
$TSFE->initTemplate();
    $TSFE->getFromCache();

    
$TSFE->getConfigArray();

/// Resolves a link to an old taz article and loads the article in the newspaper extension.
/** \todo long description
 */
class tx_newspaper_ResolveRealURL {

	/// SQL table containing the resolution parameters.
	const uniquealias_table = 'tx_newspaper_uniqalias';
	/// Typo3 page used to display resolved articles.
	const article_typo3_page = 33;
	
	const post_key = '1';

	const base_path = '/www/onlinetaz/branches/taz 2.0/helge';
	
	static $prefixes = array('1', '4');
	
	public function __construct() {
		$this->uri = $_SERVER['REQUEST_URI'];
	}
	
	public function resolve() {
		// uri will be of the form /[14]/.*/1/article-alias[...]
		$segments = explode('/', $this->uri);
		
        array_shift($segments);             // remove leading null string
		$first = array_shift($segments);    // get first path segment
		
		// should never happen if mod_rewrite and resolverealurl.php are configured in sync
		if (!in_array($first, self::$prefixes)) {
			// to do: show the original URI
		    self::error('Path ' . $this->uri . ' does not start with ' . implode(' or ', self::$prefixes));
		}
		
		$post_index = array_search(self::post_key, $segments);
		if ($post_index === false) {
			// URL does not lead to an article.
			// to do: handle this.
			self::error(self::post_key . ' not found!');
		}
		
		$article_alias = $segments[$post_index+1];
	
		$query = $GLOBALS['TYPO3_DB']->SELECTquery(
		    'field_id, value_id', self::uniquealias_table,
		    'value_alias = \'' . $article_alias .'\''
		);
		echo $query . '<br>';
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        if (!$res) self::error('article alias ' . $article_alias . ' not found');

        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        if (!$row) self::error('article alias ' . $article_alias . ' not found');
        
        print('article alias: ' . $article_alias . ': ' . print_r($row, 1));

        /*
        $link = $this->fake_typolink(
            array(
                'id' => self::article_typo3_page,
                tx_newspaper::article_get_parameter => intval($row['value_id'])
        ));
        */
        $link = tx_newspaper::typolink_url(
            array(
                'id' => self::article_typo3_page,
                tx_newspaper::article_get_parameter => intval($row['value_id'])
        ));
        #        print_r($GLOBALS['TSFE']);
		die($link);		
	}
	
	private static function error($msg) {
		// todo handle errors.
		die($msg);
	}
	
    function getConfigArray()       {
        $setStatPageName = false;

        // If config is not set by the cache (which would be a major mistake somewhere) 
        // OR if INTincScripts-include-scripts have been registered, then we must parse the template in order to get it
        if (!is_array($this->config) || is_array($this->config['INTincScript']) || $this->forceTemplateParsing) {       
            $GLOBALS['TT']->push('Parse template','');

            // Force parsing, if set?:
            $this->tmpl->forceTemplateParsing = $this->forceTemplateParsing;

            // Start parsing the TS template. Might return cached version.
            $this->tmpl->start($this->rootLine);
            $GLOBALS['TT']->pull();

            if ($this->tmpl->loaded)    {
                $GLOBALS['TT']->push('Setting the config-array','');
                //      t3lib_div::print_array($this->tmpl->setup);
                $this->sPre = $this->tmpl->setup['types.'][$this->type];    // toplevel - objArrayName
                $this->pSetup = $this->tmpl->setup[$this->sPre.'.'];

                if (!is_array($this->pSetup))   {
                    if ($this->checkPageUnavailableHandler())       {
                        $this->pageUnavailableAndExit('The page is not configured! [type= '.$this->type.']['.$this->sPre.']');
                    } else {
                        $message = 'The page is not configured! [type= '.$this->type.']['.$this->sPre.']';
                        header('HTTP/1.0 503 Service Temporarily Unavailable');
                        t3lib_div::sysLog($message, 'cms', t3lib_div::SYSLOG_SEVERITY_ERROR);
                        $this->printError($message);
                        exit;
                    }
                } else {
                    $this->config['config'] = array();

                    // Filling the config-array, first with the main "config." part
                    if (is_array($this->tmpl->setup['config.'])) {
                        $this->config['config'] = $this->tmpl->setup['config.'];
                    }
                    // override it with the page/type-specific "config."
                    if (is_array($this->pSetup['config.'])) {
                        $this->config['config'] = t3lib_div::array_merge_recursive_overrule($this->config['config'], $this->pSetup['config.']);
                    }

                        // if .simulateStaticDocuments was not present, the default value will rule.
                    if (!isset($this->config['config']['simulateStaticDocuments'])) {
                        $this->config['config']['simulateStaticDocuments'] = trim($this->TYPO3_CONF_VARS['FE']['simulateStaticDocuments']);
                    }
                    if ($this->config['config']['simulateStaticDocuments']) {
                            // Set replacement char only if it is needed
                        $this->setSimulReplacementChar();
                    }

                    if ($this->config['config']['typolinkEnableLinksAcrossDomains']) {
                        $this->config['config']['typolinkCheckRootline'] = true;
                    }

                        // Set default values for removeDefaultJS, inlineStyle2TempFile and minifyJS so CSS and JS are externalized/minified if compatversion is higher than 4.0
                    if (t3lib_div::compat_version('4.0')) {
                        if (!isset($this->config['config']['removeDefaultJS'])) {
                            $this->config['config']['removeDefaultJS'] = 'external';
                        }
                        if (!isset($this->config['config']['inlineStyle2TempFile'])) {
                            $this->config['config']['inlineStyle2TempFile'] = 1;
                        }
                        if (!isset($this->config['config']['minifyJS'])) {
                            $this->config['config']['minifyJS'] = 1;
                        }
                    }

                            // Processing for the config_array:
                    $this->config['rootLine'] = $this->tmpl->rootLine;
                    $this->config['mainScript'] = trim($this->config['config']['mainScript']) ? trim($this->config['config']['mainScript']) : 'index.php';

                        // Initialize statistics handling: Check filename and permissions
                    $setStatPageName = $this->statistics_init();

                    $this->config['FEData'] = $this->tmpl->setup['FEData'];
                    $this->config['FEData.'] = $this->tmpl->setup['FEData.'];
	            }
                $GLOBALS['TT']->pull();
            } else {
                if ($this->checkPageUnavailableHandler())       {
                    $this->pageUnavailableAndExit('No template found!');
                } else {
                    $message = 'No template found!';
                    header('HTTP/1.0 503 Service Temporarily Unavailable');
                    t3lib_div::sysLog($message, 'cms', t3lib_div::SYSLOG_SEVERITY_ERROR);
                    $this->printError($message);
                    exit;
                }
            }
        }
    }
    
	private $uri;
}

$resolver = new tx_newspaper_ResolveRealURL();

$resolver->resolve();

?>
