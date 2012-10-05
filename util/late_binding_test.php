<?php
/**
 *  \file late_binding_test.php
 * 
 *  This file is part of the TYPO3 extension "newspaper".
 * 
 *  Copyright notice
 *
 *  (c) 2008 Lene Preuss, Oliver Schroeder, Samuel Talleux <lene.preuss@gmail.com, oliver@schroederbros.de, samuel@talleux.de>
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
 *  \author Lene Preuss <lene.preuss@gmail.com>
 *  \date Jan 21, 2009
 */
 
class base {
 	function __construct() { echo "base c'tor<br />\n"; }
 	
 	protected function callee() { $this->callViaLateBinding(); }
 	
 	protected function callViaLateBinding() {
 		throw new Exception("Don't call me in base class!");
 	}
}
 
class derived extends base {
 	function __construct() { 
		parent::__construct();
		$this->callee();
 	}
 	
 	protected function callViaLateBinding() { echo "late binding works ok<br />\n"; }
}
 
$x = new derived(); 
?>
