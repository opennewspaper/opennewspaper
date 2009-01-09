<?php
/**
 *  \file class.tx_newspaper_page.php
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
 *  \date Jan 8, 2009
 */
 
/// A page type for an online edition of a newspaper
/** Examples include:
 *  - List view of the most recent articles in a department
 *  - Article view, displays an article
 *  - Comments page, shows the comments to an article (or a department page)
 *  - RSS feed for list view or article page
 *  - Mobile versions of any of the above
 *  - Whatever else you can think of
 * 
 *  Currently just a dummy.
 */
class tx_newspaper_PageZone implements tx_newspaper_Extra {
		
	public function __construct() {
		$this->smarty = new Smarty();
		/// \todo smarty template dir
		/// \todo default smarty template?
	}
	
	/// Render the page zone, containing all extras
	/**
	 *  \todo default smarty template?
	 * 
	 *  \return The rendered page as HTML (or XML, if you insist) 
	 */
 	public function render($template) {
		/// \todo assign smarty variables
 		return $this->smarty->fetch($template);
 	}
 	
 	/// returns an actual member (-> Extra)
	function getAttribute($attribute) {
		return $this->attributes[$attribute];
	}

	/// sets a member (-> Extra)
	function setAttribute($attribute, $value) {
		$this->attributes[$attribute] = $value;
	}
	
	/// Defined to implement the tx_newspaper_Extra interface
	/** Of course it sucks that we implement an interface that, well, we don't
	 *  implement fully. 
	 *  \todo Think of a way out of this.
	 */
	function getSource() { 
		throw new tx_newspaper_IllegalUsageException(
			"tx_newspaper_PageZone::getSource(): " .
			"PageZone should never deal with Sources");
	}

	/// Defined to implement the tx_newspaper_Extra interface
	/** Of course it sucks that we implement an interface that, well, we don't
	 *  implement fully. 
	 *  \todo Think of a way out of this.
	 */
	function setSource(tx_newspaper_Source $source) {
		throw new tx_newspaper_IllegalUsageException(
			"tx_newspaper_PageZone::setSource(): " .
			"PageZone should never deal with Sources");
	 }
	
 	
 	private $smarty = null;
 	
 	private $attributes = array();	///< array of attributes
 	
}
 
?>
