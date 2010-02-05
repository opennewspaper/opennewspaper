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

if (true) {
    define('PATH_site', tx_newspaper_ResolveRealURL::base_path . '/');
} else {
    define('PATH_site', dirname(PATH_thisScript).'/');
}
echo PATH_site . '<br>';

if (@is_dir(PATH_site.'typo3/sysext/cms/tslib/')) {
    define('PATH_tslib', PATH_site.'typo3/sysext/cms/tslib/');
} elseif (@is_dir(PATH_site.'tslib/')) {
    define('PATH_tslib', PATH_site.'tslib/');
} else {

    // define path to tslib/ here:
    $configured_tslib_path = '';

    // example:
    // $configured_tslib_path = '/var/www/mysite/typo3/sysext/cms/tslib/';

    define('PATH_tslib', $configured_tslib_path);
}

if (PATH_tslib=='') {
    die('Cannot find tslib/. Please set path by defining $configured_tslib_path in '.basename(PATH_thisScript).'.');
}

//
// the following is copied and adapted from typo3/sysext/cms/tslib/index_ts.php
//

$TYPO3_MISC['microtime_start'] = microtime();
define('TYPO3_OS', stristr(PHP_OS,'win')&&!stristr(PHP_OS,'darwin')?'WIN':'');
define('TYPO3_MODE','FE');

if (!defined('PATH_t3lib'))         define('PATH_t3lib', PATH_site.'t3lib/');

define('TYPO3_mainDir', 'typo3/');      // This is the directory of the backend administration for the sites of this TYPO3 installation.
define('PATH_typo3', PATH_site.TYPO3_mainDir);
define('PATH_typo3conf', PATH_site.'typo3conf/');

if (!defined('PATH_tslib')) {
    if (@is_dir(PATH_site.TYPO3_mainDir.'sysext/cms/tslib/')) {
        define('PATH_tslib', PATH_site.TYPO3_mainDir.'sysext/cms/tslib/');
    } elseif (@is_dir(PATH_site.'tslib/')) {
        define('PATH_tslib', PATH_site.'tslib/');
    }
}

if (!@is_dir(PATH_typo3conf))   die('Cannot find configuration. This file is probably executed from the wrong location.');

echo PATH_thisScript . '<br>';
echo "PATH_tslib ". PATH_tslib . " PATH_t3lib " . PATH_t3lib . " PATH_typo3 " . PATH_typo3 . " PATH_typo3conf " . PATH_typo3conf;

require_once(PATH_t3lib.'class.t3lib_div.php');
require_once(PATH_t3lib.'class.t3lib_extmgm.php');
    
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
		
		// todo: make the path portable
        require_once(self::base_path . '/typo3conf/localconf.php');

        $link = mysql_connect($typo_db_host, $typo_db_username, $typo_db_password)
            or self::error('Could not connect: ' . mysql_error());
        mysql_select_db($typo_db) or self::error('Could not select database');

        $res = mysql_query('
	        SELECT field_id, value_id 
	        FROM ' . self::uniquealias_table .'
	        WHERE value_alias = \'' . $article_alias .'\'
	    ');
        if (!$res) self::error('article alias ' . $article_alias . ' not found');

        $row = mysql_fetch_array($res, MYSQL_ASSOC);
        if (!$row) self::error('article alias ' . $article_alias . ' not found');
        
		die('article alias: ' . $article_alias . ': ' . print_r($row, 1));
		
		// include newspaper extension.
		// check /index.php and /www/onlinetaz/typo3_src-4.2.6/typo3/sysext/cms/tslib for
		// files to include.
		
		// instantiate article and redirect ot $article->getLink()
	}
	
	private static function error($msg) {
		// todo handle errors.
		die($msg);
	}
	
	private $uri;
}

$resolver = new tx_newspaper_ResolveRealURL();

$resolver->resolve();

?>
