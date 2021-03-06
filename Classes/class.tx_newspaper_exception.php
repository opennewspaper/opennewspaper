<?php
/**
 *  \file class.tx_newspaper_exception.php
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
 *  \date Jan 9, 2009
 */


/// Base class for all exceptions thrown by this Typo3 extension
class tx_newspaper_Exception extends Exception { 

    /// Maximum depth of the stack backtrace written to the devlog
    const BACKTRACE_DEPTH = 10;

    /// Constructor which also writes a stack backtrace to the devlog
    /** 
     *  @param string $message Additional info about the Exception being thrown
     *  @param bool $stack_trace If the class is not set to be silent, write stack trace to devlog
     */
    public function __construct($message, $stack_trace = true) {
        if ($stack_trace && !self::isSilent() && self::canWriteDevlog()) {
            tx_newspaper::devlog('Exception thrown: ' . $message, self::getStrippedBacktrace(), 2);
        }
        parent::__construct($message);
    }

    public static function setSilent($state) { self::$silent = (bool)$state; }

    public static function isSilent() { return self::$silent; }


    private static function canWriteDevlog() {
        return $GLOBALS['TYPO3_DB'] instanceof t3lib_DB;
    }

    private static function getStrippedBacktrace() {
        $backtrace = debug_backtrace();
        for ($i = 0; $i < sizeof($backtrace); $i++) {
            unset($backtrace[$i]['object']);
        }
        return array_slice($backtrace, 1, self::BACKTRACE_DEPTH);
    }


    private static $silent = false;
}

/// Use an object of this class to temporarily stop exceptions from writing to the devlog.
/**
 *  Usage:
 *  \code
 *  $dont_spam_devlog = new tx_newspaper_ExceptionSilencer();
 *  throw new tx_newspapaper_Exception('Hey, whatever', true);
 *  \endcode
 */
class tx_newspaper_ExceptionSilencer {

    public function __construct() {
        $this->previous_state = tx_newspaper_Exception::isSilent();
        tx_newspaper_Exception::setSilent(true);
    }

    public function __destruct() {
        tx_newspaper_Exception::setSilent($this->previous_state);
    }

    private $previous_state = null;

}

/// This Exception is thrown when opening a tx_newspaper_Source fails
/** I could have named it OpenSourceFailedException, but no way! Open Source rules! :-)) */
class tx_newspaper_SourceOpenFailedException extends tx_newspaper_Exception { 
	public function __construct($message = '') {
        parent::__construct($message);
    }	
}

/// This Exception is thrown when a wrong class is requested
/** This can happen if a tx_newspaper_Source is asked to create a class that is
 *  not a tx_newspaper_Article or tx_newspaper_Extra.
 */
class tx_newspaper_WrongClassException extends tx_newspaper_Exception {
	public function __construct($message = '') {
        parent::__construct($message);
    }
}

/// This Exception is thrown if a feature is not yet implemented
class tx_newspaper_NotYetImplementedException extends tx_newspaper_Exception { 

	/// Tells [which feature in] which function in which class is missing 
	/** \param $message Optional message about which feature is not yet implemented
	 */ 
	public function __construct($message = '') {
		$backtrace = debug_backtrace();
        parent::__construct('Not yet implemented: ' . 
        	($message? $message . ' in ': '') .
        	$backtrace[1]['class'] . 
			'::' . $backtrace[1]['function'] . 
			'(' . sizeof($backtrace[1]['args']) . ' args)' .
			' [ called from ' . $backtrace[2]['class'] . 
			'::' . $backtrace[2]['function'] .'() ]');
    }
}

/// This Exception is thrown if a feature is used in a wrong way
class tx_newspaper_InconsistencyException extends tx_newspaper_Exception { 

	/** \param $message Message about what is wrong
	 */ 
	public function __construct($message, $stack_trace = true) {
        parent::__construct("Internal inconsistency discovered: $message", $stack_trace);
    }	
}

/// This Exception is thrown if a feature is used in a wrong way
class tx_newspaper_IllegalUsageException extends tx_newspaper_Exception { 

	/** @param string $message Message about what is wrong
	 */ 
	public function __construct($message, $stack_trace = false) {
        parent::__construct("Illegal usage: $message", $stack_trace);
    }	
}

/// This Exception is thrown if a non-existing attribute is accessed
class tx_newspaper_WrongAttributeException 
	extends tx_newspaper_IllegalUsageException { 
	
	/** \param $attribute Which attribute was wrongly accessed
	 *  \param $uid UID of the object which was misused
	 */ 
	public function __construct($attribute, array $attributes = array(), $uid = '') {
        parent::__construct("Attribute '$attribute' does not exist" .
        					($attributes? " in attribute list: " . print_r($attributes, 1): '') . 
        					($uid === ''? '': " - UID $uid"));
        $this->attributeName = $attribute;
    }
    
    public function getAttributeName() { return $this->attributeName; }
    
    private $attributeName;
}

/// This Exception is thrown if a database operation fails
class tx_newspaper_DBException extends tx_newspaper_Exception { 

    /// Displays information about the failed database operation
    /**
     *  @param string $message Error message returned by RDBMS
     *  @param array $row Data returned by last SQL query
     */
    public function __construct($message, $row = array()) {
        $last_error = mysql_error();
        $message = "SQL query: \n" . tx_newspaper_DB::getQuery() . " \nfailed".
            ($message? " with message: \n'$message' \n": "\n") .
            ($last_error? "Error: " . $last_error . "\n": "" ) .
            ($row? print_r($row, 1): '');
        parent::__construct($message);
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

/// This Exception is thrown if an object, requested by UID, doesn't exist
class tx_newspaper_ObjectNotFoundException extends tx_newspaper_Exception {

    /**
     *  @param string $class Class of the requested object
     *  @param int $uid UID of the requested object
     */
    public function __construct($class, $uid) {
        parent::__construct("Object of class $class with UID $uid does not exist");
        $this->class = $class;
        $this->uid = $uid;
    }

    public $class;
    public $uid;

}


/// This Exception is thrown if a path can't be found in the filesystem
class tx_newspaper_PathNotFoundException extends tx_newspaper_Exception {
	
	/** \param $message Additional info on why \p $path is needed
	 *  \param $path Path that is missing in the filesystem
	 */ 
	public function __construct($message, $path) {
        parent::__construct("$message\n$path");
    }	
}


/// This Exception is thrown if a module name does not match module name specification  
class tx_newspaper_SysfolderIllegalModulenameException 
	extends tx_newspaper_IllegalUsageException { 

	/** \param $name Module name which does not match the convention
	 */
	public function __construct($name) {
        parent::__construct("Illegal Module name $name. Allowed: np_* or newspaper; max. 255 characters.");
    }	
}

/// This Exception is thrown if it's not possible to get pids for a class implmenting the tx_newspaper_StoredObject interface
class tx_newspaper_SysfolderNoPidsFoundException
	extends tx_newspaper_InconsistencyException {

	/** \param $class Class whose associated SysFolder could not be determined
	 */
	public function __construct($class) {
        parent::__construct("Could not retrieve pids for class $class");
    }	
}

?>