<?php
/**
 *  \file tx_newspaper_include.php
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
 *  \date Jan 9, 2009
 */
//if (t3lib_extMgm::isLoaded('newspaper')) {
//	require_once(t3lib_extMgm::extPath('newspaper', 'classes/class.tx_newspaper_exception.php'));
//	require_once(t3lib_extMgm::extPath('newspaper', 'interfaces/interface.tx_newspaper_extra.php'));
//	require_once(t3lib_extMgm::extPath('newspaper', 'interfaces/interface.tx_newspaper_article.php'));
//	require_once(t3lib_extMgm::extPath('newspaper', 'interfaces/interface.tx_newspaper_source.php'));
//} else {
	require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_exception.php');
	require_once(BASEPATH.'/typo3conf/ext/newspaper/interfaces/interface.tx_newspaper_extra.php');
	require_once(BASEPATH.'/typo3conf/ext/newspaper/interfaces/interface.tx_newspaper_article.php');
	require_once(BASEPATH.'/typo3conf/ext/newspaper/interfaces/interface.tx_newspaper_source.php');
//}

?>
