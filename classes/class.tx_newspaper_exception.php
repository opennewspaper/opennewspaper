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
class tx_newspaper_Exception extends Exception { 
	public function __construct($message) {
		t3lib_div::devlog('Exception thrown: ' . $message, 
						  'newspaper', 
						  3, 
						  array_slice(debug_backtrace(), 0, self::BACKTRACE_DEPTH));
        parent::__construct($message);
    }
    
    const BACKTRACE_DEPTH = 5;
}

/// This Exception is thrown when opening a Source fails
/** I could have named it OpenSourceFailedException, but no way! Open Source rules! :-)) */
class tx_newspaper_SourceOpenFailedException extends tx_newspaper_Exception { 
	public function __construct($message = '') {
        parent::__construct($message);
    }	
}

/// This Exception is thrown when a Source is asked to create a class that is not an Article or Extra
class tx_newspaper_WrongClassException extends tx_newspaper_Exception {
	public function __construct($message = '') {
        parent::__construct($message);
    }
}

/// This Exception is thrown if a feature is not yet implemented
class tx_newspaper_NotYetImplementedException extends tx_newspaper_Exception { 
	public function __construct() {
		$backtrace = debug_backtrace();
        parent::__construct('Not yet implemented: ' . $backtrace[1]['class'] . 
			'::' . $backtrace[1]['function'] . 
			'(' . sizeof($backtrace[1]['args']) . ' args)' .
			' [ called from ' . $backtrace[2]['class'] . 
			'::' . $backtrace[2]['function'] .'() ]');
    }
}

/// This Exception is thrown if a feature is used in a wrong way
class tx_newspaper_InconsistencyException extends tx_newspaper_Exception { 
	public function __construct($message) {
        parent::__construct("Internal inconsistency discovered: $message");
    }	
}

/// This Exception is thrown if a feature is used in a wrong way
class tx_newspaper_IllegalUsageException extends tx_newspaper_Exception { 
	public function __construct($message) {
        parent::__construct("Illegal usage: $message");
    }	
}

/// This Exception is thrown if a non-existing attribute is accessed
class tx_newspaper_WrongAttributeException 
	extends tx_newspaper_IllegalUsageException { 
	public function __construct($attribute, $uid = '') {
        parent::__construct("Attribute '$attribute' does not exist" .
        					($uid === ''? '': " - UID $uid"));
    }	
}

/// This Exception is thrown if a database operation fails
class tx_newspaper_DBException extends tx_newspaper_Exception { 
	public function __construct($message, $row = array()) {
        parent::__construct("SQL query: \n" . tx_newspaper::$query . 
							" \nfailed with message: \n$message " .
        					($row? print_r($row, 1): ''));
    }
}
 
/// This Exception is thrown if a database operation does not return a result set
class tx_newspaper_NoResException extends tx_newspaper_DBException { 
	public function __construct() {
        tx_newspaper_DBException::__construct('No result set found');
    }
}

/// This Exception is thrown if a database operation returns an empty result set
class tx_newspaper_EmptyResultException extends tx_newspaper_DBException { 
	public function __construct() {
        tx_newspaper_DBException::__construct('Result empty');
    }
}

class tx_newspaper_ArticleNotFoundException extends tx_newspaper_Exception { 
	public function __construct($uid) {
        parent::__construct("Article $uid does not exist");
    }
}




/// This Exception is thrown if a module name does not match module name specification  
class tx_newspaper_SysfolderIllegalModulenameException 
	extends tx_newspaper_IllegalUsageException { 
	public function __construct($name) {
        parent::__construct("Illegal Module name $name. Allowed: np_* or newspaper; max. 255 characters.");
    }	
}

/// This Exception is thrown if it's not possible to get pids for a class implmenting the tx_newspaper_InSysfolder interface
class tx_newspaper_SysfolderNoPidsFoundException
	extends tx_newspaper_InconsistencyException {
	public function __construct($class) {
        parent::__construct("Could not retrieve pids for class $class");
    }	
}

?>
