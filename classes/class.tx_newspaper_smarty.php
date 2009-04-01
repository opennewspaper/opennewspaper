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
	
	const DEFAULT_TEMPLATE_DIRECTORY = 'ext/newspaper/res/templates';
	
	public function __construct() {

		$tmp = PATH_site . 'typo3temp/';
		file_exists($tmp . 'smarty_cache') || mkdir($tmp . 'smarty_cache', 0774, true);
		$this->cache_dir    = $tmp . 'smarty_cache';
		file_exists($tmp . 'smarty_compile') || mkdir($tmp . 'smarty_compile', 0774, true);
		$this->compile_dir  = $tmp . 'smarty_compile';
		file_exists($tmp . 'smarty_config') || mkdir($tmp . 'smarty_config', 0774, true);
		$this->config_dir   = $tmp . 'smarty_config';

		$this->templateSearchPath = array(PATH_typo3conf . self::DEFAULT_TEMPLATE_DIRECTORY); 

		$this->caching = false;
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
					array(PATH_typo3conf . self::DEFAULT_TEMPLATE_DIRECTORY)
				)
			);
	}
	
	/// Render a template, scanning several directories for it
	/** The directories under which to search smarty templates are set with
	 *  setTemplateSearchPath().
	 * 
	 *  \return The rendered template as HTML (or XML, whatever your template does) 
	 */
	public function fetch($template) {
		if (is_object($template)) {
			$template = strtolower(get_class($template)) . '.tmpl';
		}
		
		$TSConfig = t3lib_BEfunc::getPagesTSconfig($GLOBALS['TSFE']->page['uid']);
		$basepath = $TSConfig['newspaper.']['defaultTemplate'];
		if ($basepath[0] != '/') $basepath = PATH_site . '/' . $basepath;
		foreach ($this->templateSearchPath as $dir) {
			if ($dir[0] != '/') $dir = $basepath . '/' . $dir;
			if (file_exists($dir . '/' . $template)) {
				$this->template_dir = $dir;	
				break;
			}
		}

		return parent::fetch($template);
	}
	
	////////////////////////////////////////////////////////////////////////////
	
	private $templateSearchPath = array();

}

?>
