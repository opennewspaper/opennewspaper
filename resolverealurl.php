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


#error_reporting (E_ALL ^ E_NOTICE);                                     // stfu


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
	

		require_once(tx_newspaper_ResolveRealURL::base_path . '/typo3conf/localconf.php');
		
		// Connecting, selecting database
        $link = mysql_connect($typo_db_host, $typo_db_username, $typo_db_password)
            or die('Could not connect: ' . mysql_error());
        mysql_select_db($typo_db) or die('Could not select database');
		
		$query = 'SELECT field_id, value_id FROM ' . self::uniquealias_table .
		    ' WHERE value_alias = \'' . $article_alias .'\'';
		echo $query . '<br>';

		$res = mysql_query($query);
        if (!$res) self::error('article alias ' . $article_alias . ' not found');
				
		$row = mysql_fetch_array($res, MYSQL_ASSOC);
        if (!$row) self::error('article alias ' . $article_alias . ' not found');
        
        print('article alias: ' . $article_alias . ': ' . print_r($row, 1));

		$_GET['id'] = self::article_typo3_page;
		$_GET['art'] = $row['value_id'];
		include(PATH_site . 'index.php');	
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
