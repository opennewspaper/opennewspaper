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
//	this is needed because the following error appears sometimes:
//	Fatal error: Class 't3lib_TSparser' not found in /var/lib/httpd/onlinetaz/typo3_src-4.2.2/t3lib/class.t3lib_befunc.php on line 1276
require_once (PATH_t3lib.'class.t3lib_tsparser.php');

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

		$TSConfig = t3lib_BEfunc::getPagesTSconfig($GLOBALS['TSFE']->page['uid']);
		$this->basepath = $TSConfig['newspaper.']['defaultTemplate'];
		if ($this->basepath[0] != '/') $this->basepath = PATH_site . '/' . $this->basepath;
	}

	public function setTemplateSearchPath(array $path) {
		t3lib_div::debug("setTemplateSearchPath()");
		t3lib_div::debug($path);
		$this->templateSearchPath = $path;
	}
	/// Sets the template set we're working in
	public function setTemplateSet($template_set = 'default') {
		$this->templateset = $template_set;
	}
	
	/// Sets the page type we're working on
	public function setPageType(tx_newspaper_Page $page) {
		$this->pagetype = $page->getPageType();
	}
	
	/// Sets the page zone type we're working on
	public function setPageZoneType(tx_newspaper_PageZone $pagezone) {
		$this->pagezonetype = $pagezone->getPageZoneType();
	}
		
	/// Render a template, scanning several directories for it
	/** The directories under which to search smarty templates are set with
	 *  setTemplateSearchPath().
	 * 
	 *	\param $template Either a smarty template or a Renderable object to be rendered
	 *  \return The rendered template as HTML (or XML, whatever your template does) 
	 */
	public function fetch($template) {
		if (is_object($template)) {
			$template = strtolower(get_class($template)) . '.tmpl';
		}
		
		$this->assembleSearchPath();
		
		foreach ($this->templateSearchPath as $dir) {
			//	if not absolute path, prepend $this->basepath
			if ($dir[0] != '/') $dir = $this->basepath . '/' . $dir;
			
			//	if required template exists in current dir, use this dir
			if (file_exists($dir . '/' . $template)) {
				$this->template_dir = $dir;	
				break;
			}
		}

		return parent::fetch($template);
	}
		
	////////////////////////////////////////////////////////////////////////////
	
	///	Sets the path in which Smarty looks for renderable templates
	/** The template search path consists of the following directories, each 
	 *	subject to the existence of the necessary parameters: template set, 
	 *	page zone type, page type.
	 *  \code
	 *  $basepath/<template set>/<page type name>/<page zone type name>
	 *  $basepath/<template set>/<page type name>
	 *  $basepath/<template set>
	 *  $basepath/default/<page type name>/<page zone type name>
	 *  $basepath/default/<page type name>
	 *  $basepath/default
	 *  PATH_typo3conf . self::DEFAULT_TEMPLATE_DIRECTORY
	 *  \endcode
	 */
	private function assembleSearchPath() {
		$temporary_searchpath = array();
		if ($this->templateset &&
			file_exists($this->basepath . 'template_sets/' . $this->templateset) &&
			is_dir($this->basepath . 'template_sets/' . $this->templateset)
		   ) {
			if ($this->pagetype) {
				$page_name = $this->pagetype->getAttribute('normalized_name')?
					$this->pagetype->getAttribute('normalized_name'):
					strtolower($this->pagetype->getAttribute('type_name'));
				if ($this->pagezonetype) {
					$pagezone_name = $this->pagezonetype->getAttribute('normalized_name')?
						$this->pagezonetype->getAttribute('normalized_name'):
						strtolower($this->pagezonetype->getAttribute('type_name'));
					if (file_exists($this->basepath . 'template_sets/' . $this->templateset . '/'. $page_name . '/'. $pagezone_name) &&
						is_dir($this->basepath . 'template_sets/' . $this->templateset . '/'. $page_name . '/'. $pagezone_name)
					   ) {
						$temporary_searchpath[] = 'template_sets/' . $this->templateset . '/'. $page_name . '/'. $pagezone_name;
					}
				}
				if (file_exists($this->basepath . 'template_sets/' . $this->templateset . '/'. $page_name) &&
					is_dir($this->basepath . 'template_sets/' . $this->templateset . '/'. $page_name)
				   ) {
					$temporary_searchpath[] = 'template_sets/' . $this->templateset . '/'. $page_name;
				}
			}
			$temporary_searchpath[] = 'template_sets/' . $this->templateset;
		}
		
		//	default template set
		if ($this->pagetype) {
			$page_name = $this->pagetype->getAttribute('normalized_name')?
				$this->pagetype->getAttribute('normalized_name'):
				strtolower($this->pagetype->getAttribute('type_name'));
			if ($this->pagezonetype) {
				$pagezone_name = $this->pagezonetype->getAttribute('normalized_name')?
					$this->pagezonetype->getAttribute('normalized_name'):
					strtolower($this->pagezonetype->getAttribute('type_name'));
				if (file_exists($this->basepath . 'template_sets/' . $this->templateset . '/'. $page_name . '/'. $pagezone_name) &&
					is_dir($this->basepath . 'template_sets/' . $this->templateset . '/'. $page_name . '/'. $pagezone_name)
				   ) {
					$temporary_searchpath[] = 'template_sets/default/'. $page_name . '/'. $pagezone_name;
				}
			}
			if (file_exists($this->basepath . 'template_sets/' . $this->templateset . '/'. $page_name) &&
				is_dir($this->basepath . 'template_sets/' . $this->templateset . '/'. $page_name)
			   ) {
				$temporary_searchpath[] = 'template_sets/default/'. $page_name;
			}
		}
		$temporary_searchpath[] = 'template_sets/default';
		
		//  default templates delivered with the newspaper extension
		$temporary_searchpath[] = PATH_typo3conf . self::DEFAULT_TEMPLATE_DIRECTORY;

		$this->templateSearchPath = array_unique(array_merge($this->templateSearchPath, $temporary_searchpath));
	}

	private $templateset = '';
	private $pagetype = null;
	private $pagezonetype = null;

	private $basepath = '';
	
	private $templateSearchPath = array();

}

?>
