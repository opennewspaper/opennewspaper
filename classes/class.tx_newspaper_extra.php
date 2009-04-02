<?php
/**
 *  \file class.tx_newspaper_extraimpl.php
 *
 *  \author Oliver Schroeder <newspaper@schroederbros.de>
 *  \date Dec 12, 2008
 */

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
abstract class tx_newspaper_Extra implements tx_newspaper_ExtraIface {

	public function __construct($uid) {
		$this->setUid($uid);
	}

	protected function prepare_render(&$template_set = '') {
		if (!$this->smarty) $this->smarty = new tx_newspaper_Smarty();
		
		/// Check whether to use a specific template set.
		if ($this->getAttribute('template_set')) {
			$template_set = $this->getAttribute('template_set');
		}
		
		/// Configure Smarty rendering engine
		if ($template_set) {
			$this->smarty->setTemplateSet($template_set);
		}
		if ($this->getPageZone() &&
			$this->getPageZone()->getParentPage() &&
			$this->getPageZone()->getParentPage()->getUID() && 
			$this->getPageZone()->getParentPage()->getPageType()) {
			$this->smarty->setPageType($this->getPageZone()->getParentPage());
			if ($this->getPageZone()->getPageZoneType()) {
				$this->smarty->setPageZoneType($this->getPageZone());
			}
		}
	}
	
	/// \todo remove before launch
	public function render($template_set = '') {
		$this->prepare_render($template_set);
		return '<p>' .
			   get_class($this) . '::render() not yet implemented - called ' .
			   get_class() . '::render(' . $template_set . ')' . "</p>\n" .
			   '<p>' . print_r($this->attributes, 1) . "</p>\n";
	}

	public function getAttribute($attribute) {

		if (!$this->attributes) {
			$this->attributes = $this->getExtraUid()? 
				tx_newspaper::selectOneRow('*', 'tx_newspaper_extra', 'uid = ' . $this->getExtraUid()): 
				array();
			$this->attributes += tx_newspaper::selectOneRow(
				'*', $this->getTable(), 'uid = ' . $this->getUid()
			);
		}

 		if (!array_key_exists($attribute, $this->attributes)) {
        	throw new tx_newspaper_WrongAttributeException($attribute);
 		}

 		return $this->attributes[$attribute];
	}

	/** No tx_newspaper_WrongAttributeException here. We want to be able to set
	 *  attributes, even if they don't exist beforehand.
	 */
	public function setAttribute($attribute, $value) {
		if (!$this->attributes) {
			$this->attributes = $this->readExtraItem($this->getUid(), $this->getTable());
		}
		
		$this->attributes[$attribute] = $value;
	}

	/// checks if an Extra is registered
	/** \return true if the Extra is registered (else false)
	 */
	public static function isRegisteredExtra(tx_newspaper_Extra $extra) {
		for ($i = 0; $i < sizeof(self::$registeredExtra); $i++) {
			if ($extra->getTable() == self::$registeredExtra[$i]->getTable())
				return true;
		}
		return false;
	}

	/// register an Extra
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
	
	/// name of module
	/** this is the default folder for data associated with newspaper etxension,
	 *  overwrite in conrete Extras
	 */
	public static function getModuleName() { return 'np_extra_default'; }

	public function getTable() {
		$class = tx_newspaper::getTable($this);
		if ($class == 'tx_newspaper_extraimpl') $class = self::$table;
		return $class;
	}
	
	/// Write or overwrite Extra data in DB, return UID of stored record
	public function store() {
		if ($this->getUid()) {
			/// If the attributes are not yet in memory, read them now
			if (!$this->attributes) { 
				$this->attributes = $this->readExtraItem($this->getUid(), $this->getTable());
			}
			
			tx_newspaper::updateRows(
				$this->getTable(), 'uid = ' . $this->getUid(), $this->attributes
			);
		} else {
			$this->setUid(
				tx_newspaper::insertRows(
					$this->getTable(), $this->attributes
				)
			);
			self::createExtraRecord($this->getUid(), $this->getTable());
		}
		return $this->getUid();
	}
	
	/// Read data of Extra
	/** \param $uid uid of record in given table
	 *  \param $table name of table (f.ex tx_newspaper_extra_image)
	 *  \return Array row with Extra data for given uid and table
	 */
	public static function readExtraItem($uid, $table) {
t3lib_div::devlog('ExtraImpl: readExtraItem - reached!', 'newspaper', 0, array($table, $uid));
		if (!$uid) return array();
		
		return tx_newspaper::selectOneRow('*', $table, 'uid = ' . intval($uid));
	}
	
	/// Create the record for a concrete Extra in the table of abstract Extras
	/** This is probably necessary because a concrete Extra has been freshly
	 *  created.
	 * 
	 *  Does nothing if the concrete Extra is already linked in the abstract table. 
	 * 
	 *  \param $uid UID of the Extra in the table of concrete Extras
	 *  \param $table Table of concrete Extras
	 *  \return UID of abstract Extra record
	 */ 
	public static function createExtraRecord($uid, $table) {
		/// Check if record is already present in extra table
		$row = tx_newspaper::selectZeroOrOneRows(
			'uid', self::$table, 
			'extra_table = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($table, $table) .
			' AND extra_uid = ' . intval($uid)	
		);
		if ($row['uid']) return $row['uid'];
		
		/// read typo3 fields to copy into extra table
		$row = tx_newspaper::selectOneRow(
			implode(', ', self::$fields_to_copy_into_extra_table),
			$table,
			'uid = ' . intval($uid)
		);
		
		/// write the uid and table into extra table, with the values read above
		$row['extra_uid'] = $uid;
		$row['extra_table'] = $table;
		$row['tstamp'] = time();				///< tstamp is set to now

		return tx_newspaper::insertRows(self::$table, $row);		
	}

	public function getUid() { return intval($this->uid); }
	public function setUid($uid) { $this->uid = $uid; }

	public function setExtraUid($uid) { $this->extra_uid = $uid; }
	protected function getExtraUid() { return intval($this->extra_uid); }

	protected function getPageZone() {
		return null;
	}
	
	private $uid = 0;

	private $attributes = array();				///< attributes of the extra

	protected $smarty = null;

	private static $registeredExtra = array();	///< list of registered Extras

	protected $extra_uid = 0;	///< article's UID in the abstract Extra table

	/// Extra table must be defined here because tx_newspaper_Extra is an interface
	/** \todo this table is defined in tx_newspaper_Extra_Factory too. decide
	 *		  on one class to store it!
	 */
	private static $table = 'tx_newspaper_extra';

	private static $fields_to_copy_into_extra_table = array(
		'pid', 'crdate', 'cruser_id', 'deleted', 'hidden', 
	);

}
?>
