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

	public function __construct() {
		$this->uri = $_SERVER['REQUEST_URI'];
	}
	
	public function resolve() {
		$segments = explode('/', $this->uri);
		$first = array_shift($segments);
		echo $first . "<br />\n";
		if (intval($first) != 1 && intval($first) != 4) {
		    die(print_r(array($first, $segments, 1)));
		}
		$post_index = array_search(self::post_key);
		if ($post_index === false) {
			die(self::post_key . ' not found!');
		}
		$article_alias = $segments[$post_index+1];
		die('article alias: ' . $article_alias);
	}
	
	private $uri;
}

$resolver = new tx_newspaper_ResolveRealURL();

$resolver->resolve();

?>
