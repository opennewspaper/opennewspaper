<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Lene Preuss, Oliver Schröder, Samuel Talleux <lene.preuss@gmail.com, oliver@schroederbros.de, samuel@talleux.de>
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
 *  \author	Lene Preuss, Oliver Schröder, Samuel Talleux <lene.preuss@gmail.com, oliver@schroederbros.de, samuel@talleux.de>
 */
class tx_newspaper_pi1 extends tslib_pibase {

    const default_exception_template = 'error_exception.tmpl';
    const db_exception_template = 'error_db_exception.tmpl';
    const notfound_exception_template = 'error_404.tmpl';

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

        } catch (tx_newspaper_ObjectNotFoundException $e) {
            $content .= $this->showError(" 404 Not Found", $e, self::getNotFoundExceptionTemplate());
        } catch (tx_newspaper_DBException $e) {
            $content .= $this->showError(" 500 Internal Server Error", $e, self::getDBExceptionTemplate());
		} catch (tx_newspaper_Exception $e) {
            $content .= $this->showError(" 500 Internal Server Error", $e, self::getDefaultExceptionTemplate());
        }

		return $content;

	}
	
	/// Get the tx_newspaper_Section object the plugin currently works on
	public function getSection() { return tx_newspaper::getSection(); }
	
	/// Get the tx_newspaper_Page which is currently displayed
	public function getPage(tx_newspaper_Section $section) {
		return new tx_newspaper_Page($section, new tx_newspaper_PageType($_GET));
	}

    private function showError($status, tx_newspaper_Exception $e, $error_template) {
        header($_SERVER["SERVER_PROTOCOL"]. $status);

        $smarty = new tx_newspaper_Smarty();
        $smarty->assign('_GET', $_GET);
        $smarty->assign('exception', $e);

        return $smarty->fetch($error_template);
    }

    private static function getDefaultExceptionTemplate() {
        return self::getTSConfigOrDefault('default_exception_template', self::default_exception_template);
    }

    private static function getDBExceptionTemplate() {
        return self::getTSConfigOrDefault('db_exception_template', self::db_exception_template);
    }

    private static function getNotFoundExceptionTemplate() {
        return self::getTSConfigOrDefault('notfound_exception_template', self::notfound_exception_template);
    }

    private static function getTSConfigOrDefault($ts_config_var, $default) {
        if (tx_newspaper::getTSConfigVar($ts_config_var)) {
            return tx_newspaper::getTSConfigVar($ts_config_var);
        }
        return $default;
    }

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/pi1/class.tx_newspaper_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/pi1/class.tx_newspaper_pi1.php']);
}

?>