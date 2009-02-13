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
 
//require_once(t3lib_extMgm::extPath('smarty', 'Smarty.class.php'));

/// Smarty rendering engine with configurable template directory
/** Smarty suffers from the limitation that you can only have \em one folder to
 *  store templates in per instance. For that reason, you must instantiate a 
 *  separate Smarty object per template folder, and set the folder manually.
 *  Because that process is somewhat tedious, this class does it in the c'tor.  
 */
class tx_newspaper_Smarty extends Smarty {
	public function __construct($basepath) {

		/// Configure directories
		$tmp = "/tmp/" . substr(BASEPATH, 1);
		file_exists($tmp) || mkdir($tmp, 0774, true);
		
		$this->template_dir = BASEPATH . '/' . $basepath;
		$this->compile_dir  = $tmp;
		$this->config_dir   = $tmp;
		$this->cache_dir    = $tmp;		
 	}

}

?>
