<?php
/**
 *  \file class.tx_newspaper_smarty.php
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
 *  \date Feb 13, 2009
 */
 
if (file_exists(PATH_typo3conf . 'ext/smarty/Smarty/libs/Smarty.class.php')) {
	// new smarty extension
	require_once(PATH_typo3conf . 'ext/smarty/Smarty/libs/Smarty.class.php');	
} else {
	// old smarty extension
	require_once(PATH_typo3conf . 'ext/smarty/Smarty.class.php');
}


/// Smarty rendering engine with configurable template directory
/** Smarty suffers from the limitation that you can only have \em one folder to
 *  store templates in per instance. For that reason, you must instantiate a 
 *  separate Smarty object per template folder, and set the folder manually.
 *  Because that process is somewhat tedious, this class does it in the c'tor.  
 */
class tx_newspaper_Smarty extends Smarty {
	public function __construct($basepath) {

		/// Configure directories (one path per t3 installation)
		if (TYPO3_OS == 'WIN') {
			/// windows
			$temp_dir_win = str_replace('\\', '/', sys_get_temp_dir()); // temp dir, split paths with / character
			$installation = substr(PATH_typo3conf, 0, strrpos(PATH_typo3conf, '/', -2)); 
			$tmp = $temp_dir_win . str_replace(':', '', $installation); // remove ':' (like in 'c:/example')
		} else {
			/// other os
			$installation = substr(PATH_typo3conf, 0, strrpos(PATH_typo3conf, '/', -2));
			$tmp = "/tmp/" . substr($installation, 1);
		}
		
		file_exists($tmp) || mkdir($tmp, 0774, true);
		
		$this->template_dir = $installation . '/' . $basepath;
		$this->compile_dir  = $tmp;
		$this->config_dir   = $tmp;
		$this->cache_dir    = $tmp;		
 	}

	/// Sets the directories in which smarty looks for templates, in correct order
	/** The default path, <tt>PATH_typo3conf.'ext/newspaper/res/templates'</tt>, 
	 * 	must always be set.
	 * 
	 * \param $path
	 */
	public function setTemplateSearchPath(array $path) { 
		$this->templateSearchPath = 
			array_unique(
				array_merge(
					$path, 
					array(PATH_typo3conf . 'ext/newspaper/res/templates')
				)
			); 
	}
	
	public function fetch($template) {
		if (is_object($template)) {
			$template = strtolower(get_class($template)) . '.tmpl';
		}
		
		foreach ($this->templateSearchPath as $dir) {
			if (file_exists(/* ... */$template)) {
				$this->template_dir = /* $installation . '/' . $basepath . */ $dir;	
			}
		}
		
		return "debugging information: " . 
				print_r($GLOBALS['TSFE']->page['uid'], 1) .
				print_r(t3lib_BEfunc::getPagesTSconfig($GLOBALS['TSFE']->page['uid']), 1) .
				"debugging information end " .
				parent::fetch($template);
	}
	
	////////////////////////////////////////////////////////////////////////////
	
	private $templateSearchPath = array();

}

?>
