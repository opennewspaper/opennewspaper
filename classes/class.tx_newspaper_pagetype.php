<?php
/**
 *  \file class.tx_newspaper_pagetype.php
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
 *  \date Feb 6, 2009
 */
 
/// brief description
/** long description
 */
class tx_newspaper_PageType {
 	
 	/// Construct a tx_newspaper_PageType given the $_GET array
	/**
	 *	Find out which page type we're on (Section, Article, RSS, Comments, whatever)
	 *
	 *	If $_GET['art'] is set, it is the article page
	 *
	 *	Else if $_GET['type'] is set, it is the page corresponding to that type
	 *
	 *	Else it is the section overview page
	 */ 
 	
 	function __construct($get) {
 		if ($get['art']) $this->condition = 'get_var = \'art\'';
		else if ($get['page']) 
			$this->condition = 'get_var = \'page\' AND get_value = '.intval($get['page']);
		else $this->condition = 'NOT get_var';
 		
		$this->attributes = tx_newspaper::selectOneRow(
			'*', tx_newspaper::getTable($this), $this->condition
		);
 	}
 	
 	public function getCondition() { return $this->condition; }
 	public function getID() { return $this->attributes['uid']; }
 	 	
 	private $condition = null;
 	private $attributes = array();
}
?>
