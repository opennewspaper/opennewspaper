<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Helge Preuss, Oliver Schröder, Samuel Talleux <helge.preuss@gmail.com, oliver@schroederbros.de, samuel@talleux.de>
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
***************************************************************/

require_once(PATH_tslib.'class.tslib_pibase.php');

/// Plugin 'Display Ressorts/Articles' for the 'newspaper' extension. Aka TBO.
/** Plugin 'Display Ressorts/Articles' for the 'newspaper' extension.
 *  Also known as "The Big One", because it is the one plugin that does all
 *  rendering.
 * 
 *  \author	Helge Preuss, Oliver Schröder, Samuel Talleux <helge.preuss@gmail.com, oliver@schroederbros.de, samuel@talleux.de>
 */
class tx_newspaper_pi1 extends tslib_pibase {
	
	const db_exception_template = 'error_db_exception.tmpl';
	
	var $prefixId      = 'tx_newspaper_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_newspaper_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'newspaper';	// The extension key.
	var $pi_checkCHash = true;
	
	/// The main method of the PlugIn
	/** \param			$content: The PlugIn content
	 *  \param			$conf: The PlugIn configuration
	 *  \return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		try {

			$page = new tx_newspaper_PageType($_GET);
			$page->getAttribute('uid');

			/// Get the tx_newspaper_Section object associated with the current Typo3 page
			$section = $this->getSection();
			if (!($section instanceof tx_newspaper_Section))
				throw new tx_newspaper_WrongClassException();
			
			/// Get the page displayed on that section	
			$page = $this->getPage($section);
		
			if (!($page instanceof tx_newspaper_Page))
				throw new tx_newspaper_WrongClassException();

			/// Call the render() method for that page, which renders all page areas
			$content .= $page->render();

		} catch (tx_newspaper_DBException $e) {

			$smarty = new tx_newspaper_Smarty();
			$smarty->assign('_GET', $_GET);
			$smarty->assign('exception', $e);
			
			$content .= $smarty->fetch(self::db_exception_template); 

		}

		return $content;

	}
	
	/// Get the tx_newspaper_Section object the plugin currently works on
	public function getSection() { return tx_newspaper::getSection(); }
	
	/// Get the tx_newspaper_Page which is currently displayed
	public function getPage(tx_newspaper_Section $section) {
		return new tx_newspaper_Page($section, new tx_newspaper_PageType($_GET));
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/pi1/class.tx_newspaper_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/pi1/class.tx_newspaper_pi1.php']);
}

?>
