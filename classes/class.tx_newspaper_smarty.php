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
require_once (PATH_t3lib.'class.t3lib_befunc.php');

/// Smarty rendering engine with configurable template directory
/** Smarty suffers from the limitation that you can only have \em one folder to
 *  store templates in per instance. For that reason, you must instantiate a 
 *  separate Smarty object per template folder, and set the folder manually.
 *  Because that process is somewhat tedious, this class does it automatically
 *  in the constructor.
 * 
 *  tx_newspaper_Smarty looks for templates which describe how objects are
 *  rendered in the following directories:
 *  -# if a parameter \p $template_set is set, look under the 
 *    \p template_sets/$template_set subdirectory under the base directory for
 *      templates, which is defined in the TSConfig parameter 
 *      \p newspaper.defaultTemplate.
 *      -# first look in the directory \p $pagezone_type under the directory
 *          \p $page_type under \p template_sets/$template_set, where
 *          \p $page_type is the normalized name (all lowercase, special
 *          characters and whitespace replaced with an underscore) of the 
 *          tx_newspaper_PageType of the tx_newspaper_Page currently displayed,
 *          and \p $pagezone_type is the normalized name of the 
 *          tx_newspaper_PageZoneType of the tx_newspaper_PageZone currently
 *          rendered.
 *      -# next look in the directory \p $page_type under
 *          \p template_sets/$template_set.
 *      -# then look under the template set root folder, 
 * 	        \p template_sets/$template_set.
 *  -# if no smarty template is found for the rendered object in the desired
 *      template set, look for the template under the default template set,
 *      in the same sequence:
 *      -# \p template_sets/default/$page_type/$pagezone_type
 *      -# \p template_sets/default/$page_type
 *      -# \p template_sets/default
 *  -# finally, if no template is found in any of the folders under the 
 *      template folder, look in the \p res/templates folder of the
 *      \em newspaper extension and in  the \p res/templates folder of all
 *      other installed extensions whose names start with \em newspaper.
 *  The name of the smarty template is the lowercased class name of the object,
 *  suffixed by \p ".tmpl".
 * 
 *  For example, say you want to render a tx_newspaper_Extra_Image on the
 *  \p Content page zone on the \p Article page. Your
 *  \p newspaper.defaultTemplate folder is \p "fileadmin/templates". The
 *  template set that is asked for is called, um, \p "whatever".
 *  In that case, the following files are checked for existence:
 *  - \p fileadmin/templates/template_sets/whatever/article/content/tx_newspaper_extra_image.tmpl 
 *  - \p fileadmin/templates/template_sets/whatever/article/tx_newspaper_extra_image.tmpl 
 *  - \p fileadmin/templates/template_sets/whatever/tx_newspaper_extra_image.tmpl 
 *  - \p fileadmin/templates/template_sets/default/article/content/tx_newspaper_extra_image.tmpl 
 *  - \p fileadmin/templates/template_sets/default/article/tx_newspaper_extra_image.tmpl 
 *  - \p fileadmin/templates/template_sets/default/tx_newspaper_extra_image.tmpl
 *  - \p typo3conf/ext/newspaper/res/templates/tx_newspaper_extra_image.tmpl
 *  - file \p res/templates/tx_newspaper_extra_image.tmpl in any other extension
 * 		folder, where the extension key starts with \em "newspaper", in random
 * 		order.
 */
class tx_newspaper_Smarty extends Smarty {
	
	const debug_search_path = true;
	
	const DEFAULT_TEMPLATE_DIRECTORY = 'ext/newspaper/res/templates';
	
	const pagename_for_all_pagezones = 'common';
	const default_template_set = 'default';
	
	public function __construct() {

		$tmp = PATH_site . 'typo3temp/';
		file_exists($tmp . 'smarty_cache') || mkdir($tmp . 'smarty_cache', 0774, true);
		$this->cache_dir    = $tmp . 'smarty_cache';
		file_exists($tmp . 'smarty_compile') || mkdir($tmp . 'smarty_compile', 0774, true);
		$this->compile_dir  = $tmp . 'smarty_compile';
		file_exists($tmp . 'smarty_config') || mkdir($tmp . 'smarty_config', 0774, true);
		$this->config_dir   = $tmp . 'smarty_config';

		$this->caching = false;

		$TSConfig = t3lib_BEfunc::getPagesTSconfig($GLOBALS['TSFE']->page['uid']);
		$this->basepath = $TSConfig['newspaper.']['defaultTemplate'];
		if ($this->basepath[0] != '/') $this->basepath = PATH_site . '/' . $this->basepath;
		$this->basepath = str_replace('//', '/', $this->basepath);
	}

	public function __toString() {
		$this->assembleSearchPath();
		return 'tx_newspaper_Smarty object: search path = ' . print_r($this->templateSearchPath, 1) .
			"\n" . 'assigned variables = ' . print_r($this->get_template_vars(), 1);
	}
	
	public function setTemplateSearchPath(array $path) {
		$this->templateSearchPath = $path;
	}
	
	/// Get the list of template sets present in the template directory
	/** \return Array of available template sets
	 */
	static public function getAvailableTemplateSets() {

		$template_sets = array();

		$root_page = tx_newspaper_Sysfolder::getInstance()->getPidRootfolder();
		$TSConfig = t3lib_BEfunc::getPagesTSconfig($root_page);
//t3lib_div::devlog('getAvailableTemplateSets', 'newspaper', 0, array($root_page, $TSConfig));
		$basepath = $TSConfig['newspaper.']['defaultTemplate'];
		
		$basepath = tx_newspaper::createAbsolutePath($basepath, PATH_site);
		if (!is_dir($basepath . '/template_sets/'))
			throw new tx_newspaper_PathNotFoundException('Templates could\'t be found. Is TSConfig newspaper.defaultTemplate set to the correct path?', $basepath . '/template_sets/');

		$basedir = dir($basepath . '/template_sets/');
		while (false !== ($template_set = $basedir->read())) {
			if (substr($template_set, 0, 1) != '.' && is_dir($basepath . '/template_sets/' . $template_set)) {
				$template_sets[] = $template_set;
			}
		}
		$basedir->close();

		sort($template_sets);
		
		return $template_sets;
	}
	
	/// Sets the template set we're working in
	public function setTemplateSet($template_set = tx_newspaper_Smarty::default_template_set) {
		self::debug_search_path && t3lib_div::devlog('setTemplateSet', 'np', 0, $template_set);
		
		$this->templateset = $template_set;
		$this->assign('template_set', $template_set);
	}
	
	/// Sets the page type we're working on
	public function setPageType(tx_newspaper_Page $page) {
		$page_type = $page->getPageType();
		self::debug_search_path && tx_newspaper::devlog('setPageType ' . $page_type->getAttribute('type_name'));
		$this->pagetype = $page_type;
	}
	
	/// Sets the page zone type we're working on
	public function setPageZoneType(tx_newspaper_PageZone $pagezone) {
		$pagezone_type = $pagezone->getPageZoneType();
		self::debug_search_path && tx_newspaper::devlog('setPageZoneType ' . $pagezone_type->getAttribute('type_name'));
		$this->pagezonetype = $pagezone_type;
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
		self::debug_search_path && t3lib_div::devlog("template search path", "np", 0, array ('template' => $template, 'search path' => $this->templateSearchPath));
	
		foreach ($this->templateSearchPath as $dir) {
			//	if not absolute path, prepend $this->basepath
			$dir = tx_newspaper::createAbsolutePath($dir, $this->basepath);
			
			//	if required template exists in current dir, use this dir
			if (file_exists($dir . '/' . $template)) {
				$compile_dir = str_replace($this->basepath, '', $dir);
				$this->compile_dir .= $compile_dir;
				file_exists($this->compile_dir) || mkdir($this->compile_dir, 0774, true);
				
				self::debug_search_path && t3lib_div::devlog("found", "np", 0, array ('basepath' => $this->basepath, 'template' => $dir . '/' . $template, 'compile dir' => $this->compile_dir));
				$this->template_dir = $dir;	
				
				break;
			}
		}
		
		if (TYPO3_MODE == 'FE') {
			//	log the template search path and the used template to admin panel
			$GLOBALS['TT']->setTSlogMessage('Smarty template search path: ' .
											print_r($this->templateSearchPath, 1));
			$GLOBALS['TT']->setTSlogMessage('Smarty template used: ' . $this->template_dir . '/' . $template);
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
		global $TYPO3_CONF_VARS;
		$temporary_searchpath = array();

		if ($this->templateset &&
			file_exists($this->basepath . '/template_sets/' . $this->templateset) &&
			is_dir($this->basepath . '/template_sets/' . $this->templateset)
		   ) {
			if ($this->pagetype) {
				$page_name = $this->pagetype->getAttribute('normalized_name')?
					$this->pagetype->getAttribute('normalized_name'):
					strtolower($this->pagetype->getAttribute('type_name'));
				$page_template_dir = $this->basepath . '/template_sets/' . $this->templateset . '/'. $page_name;
				if ($this->pagezonetype) {
					$pagezone_name = $this->pagezonetype->getAttribute('normalized_name')?
						$this->pagezonetype->getAttribute('normalized_name'):
						strtolower($this->pagezonetype->getAttribute('type_name'));
					$pagezone_template_dir = $page_template_dir . '/'. $pagezone_name;
					if (file_exists($pagezone_template_dir) && is_dir($pagezone_template_dir)) {
						$temporary_searchpath[] = $pagezone_template_dir;
					}
				}
				if (file_exists($page_template_dir) && is_dir($page_template_dir)) {
					$temporary_searchpath[] = $page_template_dir;
				}
			}
			$temporary_searchpath[] = 'template_sets/' . $this->templateset;
		}
		
		//	default template set
		if ($this->pagetype) {
			$page_name = $this->pagetype->getAttribute('normalized_name')?
				$this->pagetype->getAttribute('normalized_name'):
				strtolower($this->pagetype->getAttribute('type_name'));
		} else {
			$page_name = self::pagename_for_all_pagezones;
		}
		tx_newspaper::devlog('page name: ' . $page_name);
		
		$page_template_dir = $this->basepath . '/template_sets/' . self::default_template_set . '/'. $page_name;
		
		//	first look for the page zone specific templates
		if ($this->pagezonetype) {
			$pagezone_name = $this->pagezonetype->getAttribute('normalized_name')?
				$this->pagezonetype->getAttribute('normalized_name'):
				strtolower($this->pagezonetype->getAttribute('type_name'));
			$pagezone_template_dir = $page_template_dir . '/'. $pagezone_name;
			if (file_exists($pagezone_template_dir) && is_dir($pagezone_template_dir)) {
				$temporary_searchpath[] = $pagezone_template_dir;
			}
		}

		//	then for the page specific ones
		if (file_exists($page_template_dir) && is_dir($page_template_dir)) {
			$temporary_searchpath[] = 'template_sets/' . self::default_template_set . '/'. $page_name;
		}

		//	finally those common for all pages and page zones
		$temporary_searchpath[] = 'template_sets/' . self::default_template_set;
		
		//  and the default templates delivered with the newspaper extension
		$temporary_searchpath[] = PATH_typo3conf . self::DEFAULT_TEMPLATE_DIRECTORY;

		foreach (explode(',', $TYPO3_CONF_VARS['EXT']['extList']) as $ext) {
			if (substr($ext, 0, 9) == 'newspaper') {
				$temporary_searchpath[] = PATH_typo3conf . 'ext/' . $ext . '/res/templates';
			}
		}
		$this->templateSearchPath = array_unique(array_merge($this->templateSearchPath, $temporary_searchpath));
	}

	private $templateset = '';
	private $pagetype = null;
	private $pagezonetype = null;

	private $basepath = '';
	
	private $templateSearchPath = array();

}

?>
