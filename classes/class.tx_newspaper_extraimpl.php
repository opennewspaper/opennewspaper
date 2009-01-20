<?php
/**
 *  \file class.tx_newspaper_extraimpl.php
 *
 *  \author Oliver Schr�der <newspaper@schroederbros.de>
 *  \date Dec 12, 2008
 */

require_once(BASEPATH.'/typo3conf/ext/newspaper/tx_newspaper_include.php');

/// An Extra for the online newspaper, all Extras (except article) inherit from this class
/** \todo This is just a dummy class.
 */
class tx_newspaper_ExtraImpl implements tx_newspaper_Extra {

	private static $registeredExtra = array(); // list of registered Extras


	public function render($template = '') {
		throw new NotYetImplementedException("ExtraImpl::render()");
	}

	public function getAttribute($fieldname) {
		throw new NotYetImplementedException("ExtraImpl::getAttribute()");
	}

	public function setAttribute($fieldname, $value) {
		throw new NotYetImplementedException("ExtraImpl::setAttribute()");
	}

	function getSource() {
		throw new NotYetImplementedException("ExtraImpl::getSource()");
	}

	function setSource(tx_newspaper_Source $source) {
		throw new NotYetImplementedException("ExtraImpl::setSource()");
	}

	static function mapFieldToSourceField($fieldname, tx_newspaper_Source $source) {
		throw new NotYetImplementedException("ExtraImpl::mapFieldToSourceField()");
	}

	static function sourceTable(tx_newspaper_Source $source) {
		throw new NotYetImplementedException("ExtraImpl::sourceTable()");
	}


// \todo: return real pid (like it's done in dam: check if folder exists, if not create folder)
// Extra folder can be hidden (see dam)
// One folder per Extra
	static function getExtraPid() {
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
	static function registerExtra(tx_newspaper_Extra $extra) {
#t3lib_div::devlog('registerExtra', 'newspaper', 0);
		if (!self::isRegisteredExtra($extra)) {
			self::$registeredExtra[] = $extra; // add this Extra to list
			return true;
		}
		return false;
	}
	
	
// internal name
	static function getName() {
#throw new NotYetImplementedException("ExtraImpl::getName()");
	}

// title for be
	static function getTitle() {
#throw new NotYetImplementedException("ExtraImpl::getTitle()");
	}

// title for module
	static function getModuleName() {
		return 'newspaper'; // this is the default folder for data associated with newspaper etxension, overwrite in conrete Extras
	}


	/**
	 * read data of Extra
	 * \return Array row with Extra data for given uid and table
	 */
	public static function readExtraItem($uid, $table) {
#t3lib_div::devlog('Extra Image: readExtraItem - reached!', 'newspaper', 0);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			$table,
			'uid=' . $uid);
		if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			return $row;
		}
//TODO: throw Exception ... (how to? -> Helge)
t3lib_div::devlog('readExtraItem - referenced Extra can\'t be found in table', 'newspaper', 3, $uid);
		return false;
	}

}
?>
