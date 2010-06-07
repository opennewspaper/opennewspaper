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


/// Resolves a link to an old taz article and loads the article in the newspaper extension.
/** \todo long description
 */
class tx_newspaper_ResolveRealURL {

	/// SQL table containing the resolution parameters.
	const uniquealias_table = 'tx_newspaper_uniqalias';

	/// Typo3 page id used to display resolved articles.
	const article_typo3_page_id = 33;

	/// Typo3 page used to display resolved articles as seen by RealURL.
	const article_typo3_page_alias = '/start/';

	/// Keyword in page path used to mark the article alias.
	const post_key = '1';

	/// Typo3 installation base.
	const base_path = '/www/onlinetaz/branches/taz 2.0/helge';

	/// Prefixes which signify that this URI is an old path.
	/** Paths starting with these prefixes after the first slash are redirected
	 *  to the article display page in newspaper. The web server must redirect
	 *  these URIs to this script.
	 */
	static $prefixes = array('1', '4');
	
	public function __construct() {
		$this->uri = $_SERVER['REQUEST_URI'];
	}
	
	public function resolve() {
		
	    $article_alias = $this->getAlias();
	    
		require_once(tx_newspaper_ResolveRealURL::base_path . '/typo3conf/localconf.php');
		
		// Connecting, selecting database
        $link = mysql_connect($typo_db_host, $typo_db_username, $typo_db_password)
            or die('Could not connect: ' . mysql_error());
        mysql_select_db($typo_db) or die('Could not select database');
		
		$query = 'SELECT field_id, value_id FROM ' . self::uniquealias_table .
		    ' WHERE value_alias = \'' . $article_alias .'\'';

		$res = mysql_query($query);
        if (!$res) $this->error('Faulty SQL query: ' . $query);
				
		$row = mysql_fetch_array($res, MYSQL_ASSOC);
        if (!$row) $this->error('Article alias ' . $article_alias . ' not found');
        
        $this->article_id = intval($row['value_id']);
	}
	
	public function redirect() {
		if ($this->article_id) {
#			print($this->articleURL());
            header('Location: ' . $this->articleURL(),
                   true, 301);
		} else {
			$this->fail();
		}
	}
		
	/** Edit this for pretty URLs to redirect to
	 */
	private function articleURL() {
        if (false) {
            return self::article_typo3_page_alias . 
                self::post_key . '/' .
                $this->article_alias . '/';
        } else if (true) {
		    return self::article_typo3_page_alias . 
		        '?art=' . $this->article_id;
		} else {
		    return '/index.php?id=' . self::article_typo3_page_id . 
                '&' . 'art=' . $this->article_id;
		}
	}
	
    
	private function getAlias() {
        
        // uri will be of the form /[14]/.*/1/article-alias[...]
        $segments = explode('/', $this->uri);
        
        array_shift($segments);             // remove leading null string
        $first = array_shift($segments);    // get first path segment
        
        // should never happen if mod_rewrite and resolverealurl.php are configured in sync
        if (!in_array($first, self::$prefixes)) {
            // to do: show the original URI
            $this->error('Path \'' . $this->uri . '\' does not start with ' . implode(' or ', self::$prefixes));
        }
        
        $post_index = array_search(self::post_key, $segments);
        if ($post_index === false) {
            // URL does not lead to an article.
            // to do: handle this.
            self::error('Path segment \'' . self::post_key . '\' not found!');
        }
        
        $this->article_alias = $segments[$post_index+1];
        return $this->article_alias; 
    }
	
	
    private function error($msg) {
        $this->error_log[] = $msg;
	}
	
	
	private function fail() {
        foreach ($this->error_log as $error) {
            echo $error . '<br />';
        }
        exit;
	}
	    

	private $uri;
	private $article_id;
	private $article_alias;
	private $error_log = array();
}

$resolver = new tx_newspaper_ResolveRealURL();

$resolver->resolve();
$resolver->redirect();

?>
