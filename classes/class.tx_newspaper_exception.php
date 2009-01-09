<?php
/**
 *  \file class.tx_newspaper_exception.php
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

/// Base class for all exceptions thrown by this Typo3 extension
class tx_newspaper_Exception extends Exception { }

/// This Exception is thrown when opening a Source fails
/** I could have named it OpenSourceFailedException, but no way! Open Source rules! :-)) */
class tx_newspaper_SourceOpenFailedException extends tx_newspaper_Exception { }

/// This Exception is thrown when a Source is asked to create a class that is not an Article or Extra
class tx_newspaper_WrongClassException extends tx_newspaper_Exception { }

/// This Exception is thrown if a feature is not yet implemented
class tx_newspaper_NotYetImplementedException extends tx_newspaper_Exception { 
	public function __construct($message, $code = 0) {
        parent::__construct("Not yet implemented: $message", $code);
    }
}

/// This Exception is thrown if a feature is used in a wrong way
class tx_newspaper_IllegalUsageException extends tx_newspaper_Exception { 
	public function __construct($message, $code = 0) {
        parent::__construct("Illegal usage: $message", $code);
    }	
}
 
?>
