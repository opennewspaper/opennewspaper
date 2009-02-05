<?php
/**
 *  \file class.tx_newspaper_extraimpl.php
 *
 *  \author Oliver Schr�der <newspaper@schroederbros.de>
 *  \date Dec 12, 2008
 */

require_once(BASEPATH.'/typo3conf/ext/newspaper/tx_newspaper_include.php');

/// An Extra for the online newspaper
/** This is an abstract class which implements most of the methods defined in
 *  interface tx_newspaper_Extra, except those that must be overridden in a
 *  concrete Extra anyway. All Extras (except tx_newspaper_Article) inherit from
 *  this class.
 * 
 *  abstract functions:
 *	- static function getTitle()
 *
 *  generic functions which should be overridden:
 *  - __construct()
 *  - render()
 */ 
abstract class tx_newspaper_ExtraImpl implements tx_newspaper_Extra {

	public function __construct($uid) {
		
//		$this->attributes = $this->readExtraItem($uid, $this->getName());
	}

	/// \todo remove before launch
	public function render($template = '') {
		return '<p>' .
			   get_class($this) . '::render() not yet implemented - called ' .
			   get_class() . '::render(' . $template . ')' . "</p>\n" .
			   '<p>' . print_r($this->attributes, 1) . "</p>\n";
	}

	public function getAttribute($attribute) {
			t3lib_div::debug($this->attributes);
		t3lib_div::debug(array_slice(debug_backtrace(), 0, 7));
 		if (!array_key_exists($attribute, $this->attributes)) {
        	throw new tx_newspaper_WrongAttributeException($attribute);
 		}
 		return $this->attributes[$attribute];
	}

	public function setAttribute($attribute, $value) {
		throw new tx_newspaper_NotYetImplementedException();
	}

	/** Extra folder can be hidden (see dam)
	 *  One folder per Extra
	 * 
	 *  \todo: return real pid (like it's done in dam: check if folder exists, if not create folder)
	 *  \todo oli: what is this function for? does it need to be public?
	 */
	public static function getExtraPid() {
#t3lib_div::devlog('getExtraPid()', 'newspaper', 0, 2526);
		return 2526; // TODO: HARD-CODED!!!
	}

	/**
	 * checks if an Extra is registered
	 * \return true if the Extra is registered (else false)
	 */
	public static function isRegisteredExtra(tx_newspaper_Extra $extra) {
		for ($i = 0; $i < sizeof(self::$registeredExtra); $i++) {
			if ($extra->getName() == self::$registeredExtra[$i]->getName())
				return true;
		}
		return false;
	}

	/**
	 * every Extra has to register to be used
	 * \param tx_newspaper_Extra $extra concrete Extra object
	 * \return true if this Extra was registered or false if Extra was registered already
	 */
	public static function registerExtra(tx_newspaper_Extra $extra) {
#t3lib_div::devlog('registerExtra', 'newspaper', 0);
		if (!self::isRegisteredExtra($extra)) {
			self::$registeredExtra[] = $extra; // add this Extra to list
			return true;
		}
		return false;
	}
	
	/// title for module
	/** this is the default folder for data associated with newspaper etxension,
	 *   overwrite in conrete Extras
	 */
	static function getModuleName() { return 'newspaper'; }

	static function getName() {
		$class = strtolower(get_class());
		if ($class == 'tx_newspaper_extraimpl') $class = self::$table;
		return $class;
	}
	
	/// Read data of Extra
	/** \return Array row with Extra data for given uid and table
	 */
	public static function readExtraItem($uid, $table) {
t3lib_div::devlog('Extra Image: readExtraItem - reached!', 'newspaper', 0);
		
		return tx_newspaper::selectOneRow('*', $table, 'uid = ' . intval($uid));
	}
	
	/// Create the record for a concrete Extra in the table of abstract Extras
	/** This is probably necessary because a concrete Extra has been freshly
	 *  created.
	 * 
	 *  Does nothing if the concrete Extra is already linked in the abstract table. 
	 * 
	 *  \param $uid UID of the Extra in the table of concrete Extras
	 *  \param $table Table of concrete Extras. If empty, 
	 */ 
	public static function createExtraRecord($uid, $table) {
		/// Check if record is already present in extra table
		$row = tx_newspaper::selectZeroOrOneRows(
			'uid', self::getName(), 
			'extra_table = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($table, $table) .
			' AND extra_uid = ' . intval($uid)	
		);
		if ($row['uid']) return;
		
		/// read typo3 fields to copy into extra table
		$row = tx_newspaper::selectOneRow(
			implode(', ', self::$fields_to_copy_into_extra_table),
			$table,
			'uid = ' . intval($uid)
		);
		
		/// write the uid and table into extra table, with the values read above
		$row['extra_uid'] = $uid;
		$row['extra_table'] = $table;

		/** use the PID all Extras share. If Extras are created under more than
		 *  one page, we have a problem and can't continue.
		 */
		$rows = tx_newspaper::selectRows(
			'DISTINCT pid', self::getName(), 'pid != 0'
		);
		if (sizeof($rows) != 1) {
		 	throw new tx_newspaper_InconsistencyException(
		 		'Abstract Extras were created on more than one page:<br />' . "\n" .
		 		print_r($rows, 1)
		 	);
		}
		$row['pid'] = $rows[0]['pid'];

		tx_newspaper::insertRows(self::getName(), $row);		
	}
	
	private $attributes = array();				///< attributes of the extra
	
	private static $registeredExtra = array();	///< list of registered Extras
	
	/// Extra table must be defined here because tx_newspaper_Extra is an interface
	/** \todo this table is defined in tx_newspaper_Extra_Factory too. decide
	 * 		  on one class to store it!
	 */
	private static $table = 'tx_newspaper_extra';
	
	private static $fields_to_copy_into_extra_table = array(
		'tstamp', 'crdate', 'cruser_id', 'deleted', 'hidden', 
		'starttime', 'endtime', 'fe_group'
	);
	
}
?>
