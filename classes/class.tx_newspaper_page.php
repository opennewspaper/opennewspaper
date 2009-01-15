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
 *  - List view of the most recent articles in a section
 *  - Article view, displays an article
 *  - Comments page, shows the comments to an article (or a section page)
 *  - RSS feed for list view or article page
 *  - Mobile versions of any of the above
 *  - Whatever else you can think of
 * 
 *  Currently just a dummy.
 */
class tx_newspaper_Page {
	
	public function __construct(tx_newspaper_Section $parent) {
		$this->parentSection = $parent;
		$this->smarty = new Smarty();
		/// \todo smarty template dir
		/// \todo default smarty template?
	}
	
	/// Render the page, containing all associated page areas
	/**
	 *  \todo default smarty template?
	 * 
	 *  \return The rendered page as HTML (or XML, if you insist) 
	 */
 	public function render($template) {
 		foreach ($this->pageZones as $zone) {
 			/// \todo assign smarty variable
 		}
 		return $this->smarty->fetch($template);
 	}
 	
 	public function getParent() {
 		return $this->parentSection;
 	}
 	
 	private $smarty = null;
 	private $parentSection = null;
 	private $pageZones = array();
}
 
?>
