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
//require_once(t3lib_extMgm::extPath('newspaper', 'tx_newspaper_include.php'));
require_once(BASEPATH.'/typo3conf/ext/newspaper/tx_newspaper_include.php');

/// Plugin 'Display Ressorts/Articles' for the 'newspaper' extension. Aka TBO.
/** Plugin 'Display Ressorts/Articles' for the 'newspaper' extension.
 *  Also known as "The Big One", because it is the one plugin that does all
 *  rendering.
 * 
 *  \author	Helge Preuss, Oliver Schröder, Samuel Talleux <helge.preuss@gmail.com, oliver@schroederbros.de, samuel@talleux.de>
 */
class tx_newspaper_pi1 extends tslib_pibase {
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
		
		// dummy functionality
		$content .= '<h2>The Big One</h2>
		';

		/// Get the tx_newspaper_Section object associated with the current Typo3 page
		$section = $this->getSection();
		if (!($section instanceof tx_newspaper_Section))
			throw new tx_newspaper_WrongClassException();
			
		$content .= print_r($section, 1);
		
		/// Find out which page type we're on (Section, Article, RSS, Comments, whatever)
		/// If $_GET['art'] is set, it is the article page
		/// Else if $_GET['type'] is set, it is the page corresponding to that type
		/// Else it is the ressort page
		$page = $this->getPage($section);
	
		if (!($page instanceof tx_newspaper_Page))
			throw new tx_newspaper_WrongClassException();

		/// Call the render() method for that page, which renders all page areas
		$content .= $page->render();
	
		return $this->pi_wrapInBaseClass($content);
	}
	
	/// Get the tx_newspaper_Section object the plugin currently works on
	/** Currently, that means it returns the ressort record which lies on the
	 *  current Typo3 page. This implementation may change, but this function
	 *  is required to always return the correct tx_newspaper_Section.
	 * 
	 *  \return The tx_newspaper_Section object the plugin currently works on
	 */
	public function getSection() {
		$page = intval($GLOBALS['TSFE']->page);
		
		/// Retrieve the UID of the section associated with current page
		$query = $GLOBALS['TYPO3_DB']->SELECTquery(
			'tx_newspaper_associated_section',
			'pages',
			"uid = $page"
		);
		$res =  $GLOBALS['TYPO3_DB']->sql_query($query);
        if (!$res) {
        	/// \todo Throw an appropriate exception
        	throw new tx_newspaper_Exception();
        }

        $row =  $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        if (!$row) {
        	/// \todo Throw an appropriate exception
        	throw new tx_newspaper_Exception();
        }

		$section_uid = intval($row['tx_newspaper_associated_section']);
		if (!$section_uid) {
        	/// \todo Throw an appropriate exception
        	throw new tx_newspaper_Exception();			
		}
		
		return new tx_newspaper_Section($section_uid);
		
//		throw new tx_newspaper_NotYetImplementedException("tx_newspaper_pi1::getSection(): $section_uid");
	}
	
	/// Get the tx_newspaper_Page which is currently displayed
	/**
	 *  \return The tx_newspaper_Page which is currently displayed
	 */ 
	public function getPage(tx_newspaper_Section $ressort) {
		throw new tx_newspaper_NotYetImplementedException("tx_newspaper_pi1::getPage()");
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/pi1/class.tx_newspaper_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/pi1/class.tx_newspaper_pi1.php']);
}

?>