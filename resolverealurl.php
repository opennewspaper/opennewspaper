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
 
/// \todo brief description
/** \todo long description
 */
class tx_newspaper_ResolveRealURL {

	/// SQL table containing the resolution parameters.
	const uniquealias_table = 'tx_newspaper_uniqalias';
	/// Typo3 page used to display resolved articles.
	const article_typo3_page = 33;
	
	const post_key = '1';

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
		    die('Path ' . $this->uri . ' does not start with ' . implode(' or ', self::$prefixes));
		}
		
		$post_index = array_search(self::post_key, $segments);
		if ($post_index === false) {
			// URL does not lead to an article.
			// to do: handle this.
			die(self::post_key . ' not found!');
		}
		
		$article_alias = $segments[$post_index+1];
		
		// todo: make the path portable
        require_once('../localconf.php');

        $link = mysql_connect($typo_db_host, $typo_db_username, $typo_db_password)
            or die('Could not connect: ' . mysql_error());
        mysql_select_db($typo_db) or die('Could not select database');

        $res = mysql_query('
	        SELECT field_id, value_id 
	        FROM ' . self::uniquealias_table .'
	        WHERE value_alias = \'' . $article_alias .'\'
	    ');
        if (!$res) die('article alias ' . $article_alias . ' not found');

        while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) print_r ($row);
		// 
		die('article alias: ' . $article_alias);
	}
	
	private $uri;
}

$resolver = new tx_newspaper_ResolveRealURL();

$resolver->resolve();

?>
