<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Oliver Schröder <typo3@schroederbros.de>
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


/**
 * Plugin 'Text with Extra' for the 'extra' extension.
 *
 * @author	Oliver Schröder <typo3@schroederbros.de>
 * @package	TYPO3
 * @subpackage	tx_extra
 */
class tx_extra_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_extra_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_extra_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'extra';	// The extension key.
	var $pi_checkCHash = true;
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		return 'Hello World!<HR>
			Here is the TypoScript passed to the method:'.
					t3lib_div::view_array($conf);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extra/pi1/class.tx_extra_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extra/pi1/class.tx_extra_pi1.php']);
}

?>