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
 *  concrete Extra anyway. All Extras (except tx_newspaper_Article and 
 *  tx_newspaper_Pagezone) inherit from this class.
 * 
 *  abstract functions:
 *	- static function getTitle()
 *
 *  generic functions which should be overridden:
 *  - __construct()
 *  - render()
 *  - getModuleName()
 */ 
abstract class tx_newspaper_ExtraImpl implements tx_newspaper_Extra {

	public function __construct($uid) {
		$this->setUid($uid);
	}

	/// \todo remove before launch
	public function render($template = '') {
		return '<p>' .
			   get_class($this) . '::render() not yet implemented - called ' .
			   get_class() . '::render(' . $template . ')' . "</p>\n" .
			   '<p>' . print_r($this->attributes, 1) . "</p>\n";
	}

	public function getAttribute($attribute) {

		if (!$this->attributes) 
			$this->attributes = $this->readExtraItem($this->getUid(), $this->getTable());

 		if (!array_key_exists($attribute, $this->attributes)) {
        	throw new tx_newspaper_WrongAttributeException($attribute);
 		}

 		return $this->attributes[$attribute];
	}

	/** No tx_newspaper_WrongAttributeException here. We want to be able to set
	 *  attributes, even if they don't exist beforehand.
	 */
	public function setAttribute($attribute, $value) {
		$this->attributes[$attribute] = $value;
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
			if ($extra->getTable() == self::$registeredExtra[$i]->getTable())
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
	public static function getModuleName() { return 'newspaper'; }

	public function getTable() {
		$class = tx_newspaper::getTable($this);
		if ($class == 'tx_newspaper_extraimpl') $class = self::$table;
		return $class;
	}
	
	/// Write or overwrite Extra data in DB, return UID of stored record
	public function store() {
		if ($this->getUid()) {
			tx_newspaper::updateRows(
				$this->getTable(), 'uid = ' . $this->getUid(), array($this->attributes)
			);
		} else {
			$this->setUid(
				tx_newspaper::insertRows(
					$this->getTable(), $this->attributes
				)
			);
		}
		return $this->getUid();		
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
			'uid', self::$table, 
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
			'DISTINCT pid', self::$table, 'pid != 0'
		);
		if (sizeof($rows) != 1) {
		 	throw new tx_newspaper_InconsistencyException(
		 		'Abstract Extras were created on more than one page:<br />' . "\n" .
		 		print_r($rows, 1)
		 	);
		}
		$row['pid'] = $rows[0]['pid'];

		tx_newspaper::insertRows(self::$table, $row);		
	}

	function getUid() { return intval($this->uid); }
	function setUid($uid) { $this->uid = $uid; }
 	
	private $uid = 0;
	
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
