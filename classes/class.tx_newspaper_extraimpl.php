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
 *  \todo Currently implements tx_newspaper_WithSource as well. I am not sure if
 *  this makes sense.
 */ 
abstract class tx_newspaper_ExtraImpl 
	implements tx_newspaper_Extra, tx_newspaper_WithSource {

	public function __construct($uid) {
		$this->attributes = $this->readExtraItem($uid, $this->getName());
	}

	public function render($template = '') {
		return '<p>' .
			   get_class($this) . '::render() not yet implemented - called ' .
			   get_class() . '::render(' . $template . ')' . "</p>\n" .
			   '<p>' . print_r($this->attributes, 1) . "</p>\n";
	}

	public function getAttribute($attribute) {
 		if (!array_key_exists($attribute, $this->attributes)) {
        	throw new tx_newspaper_WrongAttributeException($attribute);
 		}
 		return $this->attributes[$attribute];
	}

	public function setAttribute($attribute, $value) {
		throw new tx_newspaper_NotYetImplementedException();
	}

	public function getSource() {
		throw new tx_newspaper_NotYetImplementedException();
	}

	public function setSource(tx_newspaper_Source $source) {
		throw new tx_newspaper_NotYetImplementedException();
	}

	public static function mapFieldToSourceField($fieldname, tx_newspaper_Source $source) {
		throw new tx_newspaper_NotYetImplementedException();
	}

	public static function sourceTable(tx_newspaper_Source $source) {
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
//	static function getModuleName() { return 'newspaper'; }


	/// Read data of Extra
	/** \return Array row with Extra data for given uid and table
	 */
	public static function readExtraItem($uid, $table) {
t3lib_div::devlog('Extra Image: readExtraItem - reached!', 'newspaper', 0);
		
		return tx_newspaper::selectOneRow('*', $table, "uid = $uid");
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
		/// \todo check if record is already present in extra table
		
		/// \todo read typo3 fields to copy into extra table
		
		/// \todo write the uid and table into extra table, with the values read above
		throw new tx_newspaper_NotYetImplementedException();
	}
	
	private $attributes = array();				///< attributes of the extra
	
	private static $registeredExtra = array();	///< list of registered Extras
}
?>
