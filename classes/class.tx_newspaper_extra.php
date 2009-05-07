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

	/// Convert object to string to make it visible in stack backtraces, devlog etc.
	public function __toString() {
		return get_class($this) . '-object ' . "\n" .
			   'attributes: ' . print_r($this->attributes, 1) . "\n";
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

		if (!$this->extra_attributes) {
			$this->extra_attributes = $this->getExtraUid()? 
				tx_newspaper::selectOneRow('*', 'tx_newspaper_extra', 'uid = ' . $this->getExtraUid()): 
				array();
		}
		if (!$this->attributes) {
			$this->attributes = tx_newspaper::selectOneRow(
				'*', $this->getTable(), 'uid = ' . $this->getUid()
			);
		}

 		if (array_key_exists($attribute, $this->attributes)) {
	 		return $this->attributes[$attribute];
 		}
 		if (array_key_exists($attribute, $this->extra_attributes)) {
	 		return $this->extra_attributes[$attribute];
 		}

        throw new tx_newspaper_WrongAttributeException($attribute);
	}

	/** No tx_newspaper_WrongAttributeException here. We want to be able to set
	 *  attributes, even if they don't exist beforehand.
	 */
	public function setAttribute($attribute, $value) {
		if (!$this->extra_attributes) {
			$this->extra_attributes = $this->getExtraUid()? 
				tx_newspaper::selectOneRow(
					'*', 'tx_newspaper_extra', 'uid = ' . $this->getExtraUid()): 
				array();
		}
		if (!$this->attributes) {
			$this->attributes = $this->getUid()?
				tx_newspaper::selectOneRow(
					'*', $this->getTable(), 'uid = ' . $this->getUid()):
				array();
		}

		/** Because we separated the attributes for the concrete Extra and the
		 *  abstract superclass in two arrays, it is not trivial to decide where
		 *  the new attribute should be set - in particular if we created an
		 *  empty object and want to set a new attribute.
		 * 
		 *  The logic goes thus. If an attribute exists in either array, set it
		 *  there (if it exists in both, set it in both). Else assume it is
		 *  meant for the concrete Extra.
		 * 
		 *  This is consistent with the assumption made in store(), that the
		 *  concrete record must exist before abstract attributes can be written.
		 */
		if (array_key_exists($attribute, $this->extra_attributes)) {
			$this->extra_attributes[$attribute] = $value;
		} else {
			$this->attributes[$attribute] = $value;
		}
		if (array_key_exists($attribute, $this->attributes)) {
			$this->attributes[$attribute] = $value;
		}
		
	}

	/// A short description that makes an Extra uniquely identifiable in the BE
	/** This function should be overridden in every class that can be pooled, to
	 *  provide the BE user a way to find an Extra to create a new Extra from.
	 */
	public function getTitle() {
		//	default implementation
		return get_class($this) . ' ' . $this->getUid();
	}
	
	/// Deletes the concrete Extras and all references to it
	public function deleteIncludingReferences() {
		/// Find abstract records linking to the concrete Extra
		$uids = tx_newspaper::selectRows(
			'uid', self::$table, 
			'extra_table = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->getTable(), $this->getTable()) .
			' AND extra_uid = ' . $this->getUid());

		foreach ($uids as $uid) {
			/// Delete entries in association tables linking to abstract record
			tx_newspaper::deleteRows(
				tx_newspaper_Article::getExtra2PagezoneTable(), 
				'uid_foreign = ' . intval($uid['uid'])
			);
			tx_newspaper::deleteRows(
				tx_newspaper_PageZone_Page::getExtra2PagezoneTable(), 
				'uid_foreign = ' . intval($uid['uid'])
			);
			
			/// Delete the abstract record
			tx_newspaper::deleteRows(self::$table, 'uid = ' . intval($uid['uid']));
		}
		
		/// delete the concrete record
		tx_newspaper::deleteRows($this->getTable(), 'uid = ' . $this->getUid());
	}
	
	/// Lists Extras which are in the pool of master copies for new Extras
	public static function getPooledExtras() {
		try {
			$uids = tx_newspaper::selectRows(
				'uid', $this->getTable(), 'pool', '', 'crdate DESC'
			);
		} catch (tx_newspaper_DBException $e) {
			return array();
		}
		$pooled_extras = array();
		foreach ($uids as $uid) {
			$pooled_extras[] = tx_newspaper_Extra_Factory::getInstance()->create($uid['uid']);
		}
		return $pooled_extras;
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
	
	/// \return array with (registered) Extra objects
	static public function getRegisteredExtras() {
		return self::$registeredExtra;
	}
	
	
	/// name of module
	/** this is the default folder for data associated with newspaper etxension,
	 *  overwrite in conrete Extras
	 */
	public static function getModuleName() { return 'np_extra_default'; }

	public function getTable() {
		return tx_newspaper::getTable($this);
	}
	
	/// Write or overwrite Extra data in DB, return UID of stored record
	public function store() {
		if ($this->getUid()) {
			/// If the attributes are not yet in memory, read them now
			$this->getAttribute('uid');
			
			tx_newspaper::updateRows(
				$this->getTable(), 'uid = ' . $this->getUid(), $this->attributes
			);
			tx_newspaper::updateRows(
				'tx_newspaper_extra',
				'uid = ' . $this->getExtraUid(), 
				$this->extra_attributes
			);
		} else {
			
			if ($this->extra_attributes) {
				throw new tx_newspaper_InconsistencyException(
					'Attributes for abstract Extra have been set before a concrete Extra exists. ' .
					print_r($this->extra_attributes, 1)
				);
			}
			
			//	Make sure the Extra is stored in the correct SysFolder
			$this->setAttribute('pid', tx_newspaper_Sysfolder::getInstance()->getPid($this));
			//	Write data for concrete Extra
			$this->setUid(
				tx_newspaper::insertRows(
					$this->getTable(), $this->attributes
				)
			);
			if (!$this->getUid())
				t3lib_div::debug(tx_newspaper::$query);
			//	Write data for abstract Extra
			$this->setExtraUid(
				self::createExtraRecord($this->getUid(), $this->getTable())
			);
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
		if ($row['uid']) {
			return $row['uid'];		
		} else {
		
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
	}

	public function getUid() { return intval($this->uid); }
	public function setUid($uid) { $this->uid = $uid; }

	public function setExtraUid($uid) { $this->extra_uid = $uid; }
	/// This function is only public so unit tests can access it	
	public function getExtraUid() {
		if (!$this->extra_uid) {
			if (!$this->getUid()) {
				return 0;
#				t3lib_div::debug('tried to call getExtraUid() before there was a record written');
#				t3lib_div::debug(debug_backtrace());
			}
			$this->extra_uid = self::createExtraRecord($this->getUid(), $this->getTable());
		} 
		return intval($this->extra_uid); 
	}

	/// gets the origin uid of an extra 
	/// \return int the origin uid of an extra (if 0 return abstract extra uid)
	public function getOriginUid() {
		if ($this->getAttribute('origin_uid'))
			return intval($this->getAttribute('origin_uid'));
		else
			return intval($this->getExtraUid());
	}

	/// checks if this Extra was placed on this page zone 
	/// \return boolean true if this Extra was placed on this page zone
	public function isOriginExtra() {
		return (($this->getAttribute('origin_uid') == 0) || 
				($this->getAttribute('origin_uid') == $this->getExtraUid())); 
	}

	/// Finds the PageZone this Extra is placed upon
	/** I'm afraid this raises several problems, so this function should be used
	 *  with care.
	 *  - What if an Extra is placed on more than one PageZone?
	 *  - What if an Extra is not placed on any PageZone at all (perhaps because
	 *    it is a template from which other Extras are copied)
	 *  - We must manually select the Extras from all Extra to PageZone-MM-tables
	 *    there are. 
	 *    - These are currently limited to two (for Articles and normal
	 *      Page Zones), but there is no guarantee that that stays this way
	 *      (although it is highly likely). When that happens, this function
	 *      must be changed. Bad software design!
	 *    - The order in which these MM-tables are checked for the Extra is
	 *      pretty arbitrary.
	 *      
	 *	\return The PageZone this Extra is placed upon, or null
	 */
	protected function getPageZone() {
		/// Check if the Extra is associated with an article...
		foreach (array('tx_newspaper_article_extras_mm' => 'tx_newspaper_Article',
						/// ...or a page zone...
				 	   'tx_newspaper_pagezone_page_extras_mm' => 'tx_newspaper_PageZone_Page')
					   as $table => $type) {
			$row = tx_newspaper::selectZeroOrOneRows(
				'uid_local', $table, 'uid_foreign = ' . $this->getExtraUid(), '', '', '', false
			);
			if ($row['uid_local']) {
				return new $type(intval($row['uid_local']));
			}
		}
		return null;
	}
	
	private $uid = 0;

	private $attributes = array();				///< attributes of the concrete extra
	private $extra_attributes = array();		///< attributes of the abstract extra

	protected $smarty = null;

	private static $registeredExtra = array();	///< list of registered Extras

	protected $extra_uid = 0;	///< article's UID in the abstract Extra table

	/// Extra table must be defined here because tx_newspaper_Extra is an interface
	/** \todo this table is defined in tx_newspaper_Extra_Factory too. decide
	 *		  on one class to store it!
	 */
	private static $table = 'tx_newspaper_extra';

	private static $fields_to_copy_into_extra_table = array(
		'pid', 'crdate', 'cruser_id', 'deleted',  
	);

}
?>
