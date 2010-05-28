<?php

#t3lib_div::devlog('class.tx_newspaper.php loaded', 'newspaper', 0);


/// Utility class which provides static functions. A namespace, so to speak.
/** Because PHP has introduced namespaces only with PHP 5.3, and we started
 *  development for \c newspaper on 5.2, and also because 5.3 is not yet widely
 *  used, all utility functions for \c newspaper are moved into class tx_newspaper,
 *  which simulates a namespace.
 * 
 *  \todo Reorder according to functionality (e.g. DB operations, class logic 
 * 		etc.)
 */
class tx_newspaper  {

	// basic newspaper configuration
	
	// \todo: set to true if template set are fully functional
	const USE_TEMPLATE_SETS = false; // if set to false, no template set form fields are visible in the backend


	///	Whether to use Typo3's command- and datamap functions for DB operations
	/** If this constant is set to true, Typo3 command- or datamap functions are
	 *  used wherever appropriate. 
	 * 
	 *  These functions have side effects which are not yet fully explored and 
	 *  seem to make more trouble than they're worth. That's why they're turned
	 *  off currently.  
	 */
	const use_datamap = false;
	
	/// The \c GET parameter which determines the article UID
	const article_get_parameter = 'art';
	///	The \c GET parameter which determines which page type is displayed
	const pagetype_get_parameter = 'pagetype';
	
	/// Returns whether a specified record is available in the DB
	public static function isPresent($table, $where, $use_enable_fields = true) {
		if (!is_object($GLOBALS['TYPO3_DB'])) $GLOBALS['TYPO3_DB'] = t3lib_div::makeInstance('t3lib_DB');

		$query = $GLOBALS['TYPO3_DB']->SELECTquery(
			'uid', $table, 
			$where . ($use_enable_fields? self::enableFields($table): ''));
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		
		if (!$res) return false;
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        if (!$row['uid']) return false; 
        return true;
		
	}
	
	private static function writeFunctionAndArgumentsToLog($type) {
		$backtrace = debug_backtrace();
		$previous_function = $backtrace[1];
		$function_name = $previous_function['function'];
		$args = join("\n", $previous_function['args']);
		self::writeNewspaperLogEntry($type, "$function_name\n$args");
	}
	
	/// Execute a \c SELECT query, check the result, return zero or one record(s)
	/** enableFields() are taken into account.
	 * 
	 *  \param $fields Fields to \c SELECT
	 *  \param $table Table to \c SELECT \c FROM
	 *  \param $where \c WHERE - clause (defaults to selecting all records)
	 *  \param $groupBy Fields to \c GROUP \c BY
	 *  \param $orderBy Fields to \c ORDER \c BY
	 *  \param $limit Maximum number of records to \c SELECT
	 *  \return The result of the query as associative array
	 *  \throw tx_newspaper_NoResException if no result is found, probably due
	 * 		to a SQL syntax error
	 */
	public static function selectZeroOrOneRows($fields, $table, $where = '1', 
											   $groupBy = '', $orderBy = '', $limit = '') {

		self::writeFunctionAndArgumentsToLog('logDbSelect');

		if (!is_object($GLOBALS['TYPO3_DB'])) $GLOBALS['TYPO3_DB'] = t3lib_div::makeInstance('t3lib_DB');

		self::$query = $GLOBALS['TYPO3_DB']->SELECTquery(
			$fields, $table, $where . self::enableFields($table), 
			$groupBy, $orderBy, $limit);
		$res = $GLOBALS['TYPO3_DB']->sql_query(self::$query);
		
		if (!$res) {
        	throw new tx_newspaper_NoResException(self::$query);
        }
        
        self::writeNewspaperLogEntry('logDbSelect', 'selectZeroOrOneRows, success');
        
        return $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	}

	/// Execute a \c SELECT query, check the result, return \em exactly one record
	/** enableFields() are taken into account.
	 * 
	 *  \param $fields Fields to \c SELECT
	 *  \param $table Table to \c SELECT \c FROM
	 *  \param $where \c WHERE - clause (defaults to selecting all records)
	 *  \param $groupBy Fields to \c GROUP \c BY
	 *  \param $orderBy Fields to \c ORDER \c BY
	 *  \param $limit Maximum number of records to \c SELECT
	 *  \return The result of the query as associative array
	 *  \throw tx_newspaper_NoResException if no result is found, probably due
	 * 		to a SQL syntax error
	 *  \throw tx_newspaper_EmptyResultException if the SQL query returns no 
	 * 		result
	 */
	public static function selectOneRow($fields, $table, $where = '1',
										$groupBy = '', $orderBy = '', $limit = '') {

		self::writeFunctionAndArgumentsToLog('logDbSelect');
		
		if (!is_object($GLOBALS['TYPO3_DB'])) $GLOBALS['TYPO3_DB'] = t3lib_div::makeInstance('t3lib_DB');

		self::$query = $GLOBALS['TYPO3_DB']->SELECTquery(
			$fields, $table, $where . self::enableFields($table), 
			$groupBy, $orderBy, $limit);
		$res = $GLOBALS['TYPO3_DB']->sql_query(self::$query);
		
		if (!$res) {
        	throw new tx_newspaper_NoResException(self::$query);
        }
        
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        
		if (!$row) {
			throw new tx_newspaper_EmptyResultException(self::$query);
		}

		self::writeNewspaperLogEntry('logDbSelect', 'selectOneRow, success');

		return $row;		
	}

	/// Execute a \c SELECT query, check the result, return all records
	/** enableFields() are taken into account.
	 * 
	 *  \param $fields Fields to \c SELECT
	 *  \param $table Table to \c SELECT \c FROM
	 *  \param $where \c WHERE - clause (defaults to selecting all records)
	 *  \param $groupBy Fields to \c GROUP \c BY
	 *  \param $orderBy Fields to \c ORDER \c BY
	 *  \param $limit Maximum number of records to \c SELECT
	 *  \return The result of the query as 2-dimensional associative array
	 *  \throw tx_newspaper_NoResException if no result is found, probably due
	 * 		to a SQL syntax error
	 */
	public static function selectRows($fields, $table, $where = '1',
									  $groupBy = '', $orderBy = '', $limit = '') {

		self::writeFunctionAndArgumentsToLog('logDbSelect');
		
		if (!is_object($GLOBALS['TYPO3_DB'])) $GLOBALS['TYPO3_DB'] = t3lib_div::makeInstance('t3lib_DB');

		self::$query = $GLOBALS['TYPO3_DB']->SELECTquery(
			$fields, $table, 
			$where . self::enableFields($table), 
			$groupBy, $orderBy, $limit);

		$res = $GLOBALS['TYPO3_DB']->sql_query(self::$query);

		if ($res) {
	        $rows = array();
	        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
	        	$rows[] = $row;
	        }
	        
	        self::writeNewspaperLogEntry('logDbSelect', 'selectRows, #results: ' . sizeof($rows));
	        
			return $rows;
		} else throw new tx_newspaper_NoResException(self::$query);
	}

	/// Execute a \c SELECT query, check the result, return all records
	/** enableFields() are NOT taken into account, that's why this method is called selectRowsDIRECT
	 * 
	 *  \param $fields Fields to \c SELECT
	 *  \param $table Table to \c SELECT \c FROM
	 *  \param $where \c WHERE - clause (defaults to selecting all records)
	 *  \param $groupBy Fields to \c GROUP \c BY
	 *  \param $orderBy Fields to \c ORDER \c BY
	 *  \param $limit Maximum number of records to \c SELECT
	 *  \return The result of the query as 2-dimensional associative array
	 *  \throw tx_newspaper_NoResException if no result is found, probably due
	 * 		to a SQL syntax error
	 */
	public static function selectRowsDirect($fields, $table, $where = '1',
									  $groupBy = '', $orderBy = '', $limit = '') {

		self::writeFunctionAndArgumentsToLog('logDbSelect');
		
		if (!is_object($GLOBALS['TYPO3_DB'])) $GLOBALS['TYPO3_DB'] = t3lib_div::makeInstance('t3lib_DB');

		self::$query = $GLOBALS['TYPO3_DB']->SELECTquery(
			$fields, $table, 
			$where, 
			$groupBy, 
			$orderBy, 
			$limit
		);

		$res = $GLOBALS['TYPO3_DB']->sql_query(self::$query);
		if ($res) {
	        $rows = array();
	        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
	        	$rows[] = $row;
	        }
	        
	        self::writeNewspaperLogEntry('logDbSelect', 'selectRowsDirect, #results: ' . sizeof($rows));
	        
			return $rows;
		} else {
			throw new tx_newspaper_NoResException(self::$query);
		}
	}

	/// Execute a \c SELECT query on M-M related tables
	/** Copied and adapted from \c t3lib_db::exec_SELECT_mm_query() so that the 
	 *	SQL query is retained for debugging as \c tx_newspaper::$query.
	 *
	 *	\param $select Field list for \c SELECT
	 *  \param $local_table Tablename, local table
	 *  \param $mm_table Tablename, relation table
	 *  \param $foreign_table Tablename, foreign table
	 *  \param $whereClause Optional additional \c WHERE clauses put in the end
	 *  	   of the query. \b NOTICE: You must escape values in this argument
	 *  	   with \c $GLOBALS['TYPO3_DB']->fullQuoteStr() yourself! DO NOT PUT
	 *  	   IN \c GROUP \c BY, \c ORDER \c BY or \c LIMIT! You have to prepend
	 *  	   \c 'AND ' to this parameter yourself!
	 *  \param $groupBy Optional \c GROUP \c BY field(s), if none, supply blank string.
	 *  \param $orderBy Optional \c ORDER \c BY field(s), if none, supply blank string.
	 *  \param $limit Optional \c LIMIT value ([begin,]max), if none, supply blank string.
	 *  \return The result of the query as 2-dimensional associative array
	 *  \throw tx_newspaper_NoResException if no result is found, probably due
	 * 		to a SQL syntax error
	 */
	public static function selectMMQuery($select, $local_table, $mm_table, $foreign_table,
										 $whereClause='' ,$groupBy='', $orderBy='', $limit='')  {

		self::writeFunctionAndArgumentsToLog('logDbSelect');
		
		if($foreign_table == $local_table) {
			$foreign_table_as = $foreign_table . uniqid('_join');
		}

		$mmWhere = $local_table ? $local_table.'.uid='.$mm_table.'.uid_local' : '';
		$mmWhere .= ($local_table AND $foreign_table) ? ' AND ' : '';
		if ($foreign_table) {
			$mmWhere .= ($foreign_table_as ? $foreign_table_as : $foreign_table) .
						'.uid='.$mm_table.'.uid_foreign';
		}
		
		if ($local_table) $table = $local_table . ',';
		$table .= $mm_table;
		if ($foreign_table) {
			$table .= ',' . $foreign_table;
			if ($foreign_table_as) $table .= ' AS '.$foreign_table_as;
		}
		
		return tx_newspaper::selectRows(
			$select, $table, $mmWhere.' '.$whereClause,
			$groupBy, $orderBy, $limit
		);
	}

	/// Inserts a record into a SQL table
	/** If the class constant \c tx_newspaper::use_datamap is set, the data is 
	 *  written using \c process_datamap(), which fills in all needed fields and
	 *  calls the save hook. Otherwise, \c $GLOBALS['TYPO3_DB']->INSERTquery() is
	 *  called. 
	 * 
	 *  \param $table SQL table to insert into
	 *  \param $row Data as key=>value pairs
	 *  \return uid of inserted record
	 *  \throw tx_newspaper_NoResException if no result is found, probably due
	 * 		to a SQL syntax error
	 *  \throw tx_newspaper_DBException if an error occurs in process_datamap()
	 */
	public static function insertRows($table, array $row) {

		self::writeFunctionAndArgumentsToLog('logDbInsertUpdateDelete');

		global $TCA;
		t3lib_div::loadTCA($table);

		self::setTimestampIfPresent($table, $row);

		if (!is_object($GLOBALS['TYPO3_DB'])) $GLOBALS['TYPO3_DB'] = t3lib_div::makeInstance('t3lib_DB');

		if (isset($TCA[$table]) && self::use_datamap) {
		
			///	Assemble a datamap with a new UID
			$new_id = 'NEW'.uniqid('');
			$datamap = array(
				$table => array(
					$new_id => $row
				)
			);

			/// Process the datamap, inserting a new record into $table
			$tce = t3lib_div::makeInstance('t3lib_TCEmain');
			$tce->start($datamap, null);
			$tce->process_datamap();
			
			if (count($tce->errorLog)){
				/// Set tx_newspaper::$query so the user can see what was attempted
				self::$query = $GLOBALS['TYPO3_DB']->INSERTquery($table, $row);
				throw new tx_newspaper_DBException(print_r($tce->errorLog, 1));
			}
			
			return $tce->substNEWwithIDs[$new_id];
		} else {
			self::$query = $GLOBALS['TYPO3_DB']->INSERTquery($table, $row);
			$res = $GLOBALS['TYPO3_DB']->sql_query(self::$query);
	
			if (!$res) {
	        	throw new tx_newspaper_NoResException(self::$query);
	        }
	        
	        return $GLOBALS['TYPO3_DB']->sql_insert_id();        			
		}
	}

	/// Updates a record using the Typo3 API
	/** \param $table SQL table to update
	 *  \param $where SQL \c WHERE condition (typically 'uid = ...')
	 *  \param $row Data as key=>value pairs
	 *  \return number of affected rows
	 *  \throw tx_newspaper_NoResException if no result is found, probably due
	 * 		to a SQL syntax error
	 */
	public static function updateRows($table, $where, array $row) {

		self::writeFunctionAndArgumentsToLog('logDbInsertUpdateDelete');

		unset ($row['uid']);
		
		self::setTimestampIfPresent($table, $row);
		
		if (!is_object($GLOBALS['TYPO3_DB'])) $GLOBALS['TYPO3_DB'] = t3lib_div::makeInstance('t3lib_DB');
		self::$query = $GLOBALS['TYPO3_DB']->UPDATEquery($table, $where, $row);
		$res = $GLOBALS['TYPO3_DB']->sql_query(self::$query);

		if (!$res) {
        	throw new tx_newspaper_NoResException(self::$query);
        }
        
        return $GLOBALS['TYPO3_DB']->sql_affected_rows();
        
	}

	/// Deletes a record from a DB table
	/** If the class constant \c tx_newspaper::use_datamap is set, the operation
	 *  uses \c process_cmdmap(), which checks all needed fields and calls the
	 *  save hook. Otherwise, if \p $table is recorded in \c $TCA, its field
	 *  'deleted' is set to 1. If \p $table is not recorded in \c $TCA (which is
	 *  the case for MM tables), an SQL \c DELETE query is executed.
	 * 
	 *  \param $table SQL table to delete a record from
	 *  \param $uids_or_where Array of UIDs to delete, a single UID to delete
	 *  	(must be an integer), or a \c WHERE condition as string
	 *  \return number of affected rows
	 *  \throw tx_newspaper_NoResException if no result is found, probably due
	 * 		to a SQL syntax error
	 *  \throw tx_newspaper_DBException if an error occurs in process_datamap()
	 */
	public static function deleteRows($table, $uids_or_where) {
		
		if (!$uids_or_where) return;
		

		self::writeFunctionAndArgumentsToLog('logDbInsertUpdateDelete');

		if (!is_object($GLOBALS['TYPO3_DB'])) {
			$GLOBALS['TYPO3_DB'] = t3lib_div::makeInstance('t3lib_DB');
		}
		if (self::use_datamap && is_array($uids_or_where)) {
			$cmdmap = array();
			foreach ($uids_or_where as $uid) {
				$cmdmap[$table][$uid] = array('delete' => '');
			}
	
			$tce = t3lib_div::makeInstance('t3lib_TCEmain');
			$tce->start(null, $cmdmap);
			$tce->process_cmdmap();
			if (count($tce->errorLog)){
				throw new tx_newspaper_DBException(print_r($tce->errorLog, 1));
			}
		} else {
			global $TCA;
			t3lib_div::loadTCA($table);

			if (is_array($uids_or_where)) {
				if (count($uids_or_where) <= 0) return;
				$uids_or_where = 'uid IN ( 0, ' . implode(', ', $uids_or_where) . ')';
			} else if (is_int($uids_or_where)) {
				$uids_or_where = 'uid = ' . $uids_or_where;
			}
			
			if (isset($TCA[$table])) {
				self::$query = $GLOBALS['TYPO3_DB']->UPDATEquery($table, $uids_or_where, array('deleted' => 1));
			} else {		
				self::$query = $GLOBALS['TYPO3_DB']->DELETEquery($table, $uids_or_where);
			}
			
			$res = $GLOBALS['TYPO3_DB']->sql_query(self::$query);

			if (!$res) {
        		throw new tx_newspaper_NoResException(self::$query);
        	}
		}
		
        return $GLOBALS['TYPO3_DB']->sql_affected_rows();        
	}

	/// If \p $table has a \c tstamp field, set it to current time in \p $row
	public static function setTimestampIfPresent($table, array &$row) {
		if (!isset($row['tstamp']) && self::fieldExists($table, 'tstamp')) {
			$row['tstamp'] = time();
		}
	}
	
	/// Returns true if SQL table \p $table has a field called \p $field
	public static function fieldExists($table, $field) {
	    return in_array($field, self::getFields($table));
	}

	/// Returns the fields that are present in SQL table \p $table
	public static function getFields($table) {
		self::$query = "SHOW COLUMNS FROM $table";
		$res = $GLOBALS['TYPO3_DB']->sql_query(self::$query);

		if (!$res) throw new tx_newspaper_NoResException(self::$query);
	
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	    return $row;
	}
	
	/// Returns an array which has the fields of SQL table \p $table as keys
	public static function makeArrayFromFields($table) {
		$fields = self::getFields($table);
		t3lib_div::devlog("makeArrayFromFields($table)", 'newspaper', 0, $fields);
		$array = array();
		
		foreach ($fields as $field) $array[$field] = null;
		
		return $array;
	}
	
	/// \c WHERE clause to filter out unwanted records 
	/** Returns a part of a \c WHERE clause which will filter out records with
	 *  start/end times, deleted flag set, or hidden flag set (if hidden should
	 *  be included used); switch for BE/FE is included.
	 * 
	 *  \param $table name of db table to check
	 *  \param $show_hidden [0|1] specifies if hidden records are to be included 
	 * 		(ignored if in FE)
	 *  \return \c WHERE part of an SQL statement starting with \c AND; or an  
	 * 		empty string, if not applicable.
	 */
	static public function enableFields($table, $show_hidden = 1) {
		global $TCA;
		t3lib_div::loadTCA($table);
		if (!isset($TCA[$table])) return '';

		require_once(PATH_t3lib . '/class.t3lib_page.php');
	
		if (TYPO3_MODE == 'FE') {
			// use values defined in admPanel config (override given $show_hidden param)
			// see: enableFields() in t3lib_pageSelect
			$show_hidden = ($table=='pages')? $GLOBALS['TSFE']->showHiddenPage : $GLOBALS['TSFE']->showHiddenRecords;
			$p = t3lib_div::makeInstance('t3lib_pageSelect');
			return $p->enableFields($table, $show_hidden);
		}

		/// show everything but deleted records in backend, if deleted flag is existing for given table
		if (isset($GLOBALS['TCA'][$table]['ctrl']['delete']))
			return ' AND ' . $GLOBALS['TCA'][$table]['ctrl']['delete'] . '=0';
	
		return '';		
	}

	/// Gets sorting position for next element in a MM table 
	/** \param $table name of MM table
	 *  \param $uid_local
	 *  \return sorting position of element inserted as last element
	 */
	public static function getLastPosInMmTable($table, $uid_local) {
		$row = self::selectRows(
			'MAX(sorting) AS max_sorting',
			$table,
			'uid_local=' . intval($uid_local)
		);
		return intval($row[0]['max_sorting']);
	}


	/// Check if at least one record exists in given table
	/**  Enable fields for BE/FE are taken into account.
	 *  
	 *  \return \c true, if at least one record availabe in given table
	 */
	public static function atLeastOneRecord($table) {
		try {
			self::selectOneRow(
				'uid',
				$table,
				'1',
				'',
				'',
				1		
			);
			return true;
		} catch (tx_newspaper_EmptyResultException $e) {
			return false;
		}
	}

	/// write newspaper log entry
	/** \param $type type configured in extension manager (f.ex. logDBInsertUpdateDelete)
	 *  \param $message string to be added to log file
	 *  \return \c true if log entry was written \c false else
	 */		
	private static function writeNewspaperLogEntry($type, $message) {
		if (!$logfile = self::getNewspaperLogfile($type)) {
			return false;
		}

		/// build message
		$message = '
Time: ' . date('Y-m-d H:i:s') . ', Timestamp: ' . time() . ', be_user: ' .  $GLOBALS['BE_USER']->user['uid'] . '
' . $message . '
';

		if (!$fp = @fopen($logfile, 'a')) {
			return false;
		} 
		if (!@fwrite($fp, $message)) {
			return false;
		}
		!@fclose($fp);
		return true;
	}
	
	/// find out which newspaper log file should be used
	/** \param $type type configured in extension manager (f.ex. logDBInsertUpdateDelete)
	 *  \return \c file name of log file if log should be written and can be written, \c false else
	 */	
	private static function getNewspaperLogfile($type) {
		/// get em configuration
		$em_conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['newspaper']);
		
		if (!isset($em_conf[$type]) || !$em_conf[$type]) {
			return false; // unknown type or type not set to true
		}
	
		/// get log file name 
		switch($type) {
			case 'logDbInsertUpdateDelete':
			case 'logDbSelect':
				$logfile = $em_conf['logDbFile'];
			break;
			default:
				return false; // couldn't determine log file
		}

		/// check if log file exists
		if (!@file_exists($logfile)) {
			if (!@touch($logfile)) {
				return false;
			}
		}

		
		/// check if log file can be used
		if (!@is_writable($logfile)) {
			return false; /// \todo: throw error
		}
		
		return $logfile; // log should be written, log file is configured and writable
		
	}

	/// Check if given class name is an abstract class
	/** \param $class class name
	 *  \return \c true if abstract class, \c false else (or if no class at all)
	 */
	public static function isAbstractClass($class) {
		$abstract = false;
		if (class_exists($class)) {
			$tmp = new ReflectionClass($class);
			$abstract = $tmp->isAbstract();
		}
		return $abstract;
	}

	/// get absolute path to Typo3 installation
	/** \param $endsWithSlash determines if the returned path ends with a slash
	 *  \return absolute path to Typo3 installation
	 */ 
	public static function getAbsolutePath($endsWithSlash=true) {
		/// \todo replace by a version NOT using EM conf (check t3lib_div)
		$em_conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['newspaper']);
		if (!isset($em_conf['newspaperTypo3Path']) || !$em_conf['newspaperTypo3Path']) {
			throw new tx_newspaper_Exception('newspaperTypo3Path was not set in EM');
		}
		$path = trim($em_conf['newspaperTypo3Path']);
		
		if ($endsWithSlash) {
			// append "/", if missing
			if ($path == '') {
				$path = '/';
			}
			elseif (substr($path, strlen($path)-1) != '/') {
				$path .= '/';
			}
		} else {
			// remove last "/", if any
			if (substr($path, strlen($path)-1) == '/') {
				// cut off last '/'
				$path = substr($path, 0, strlen($path)-1);
			}
		}
		
		// first character in ABSOLUTE path must be a "/"
		if (substr($path, 0, 1) != '/') {
			$path = '/' . $path;
		}
		
		return $path;
	}

	/// prepends the given absolute path part if path to check is no absolute path
	/** \param $path2check path to check if it's an absolute path
	 *  \param $absolutePath this path is prepended to $path2check; no
	 * 		check, if this path is absolute
	 *  \return absolute path (either absolute string was prepended or path to
	 * 		check was absolute already); WIN: backslashes are converted to slashes
	 *  \todo: throw exception if created path does not exist???
	 */
	public static function createAbsolutePath($path2check, $absolutePath) {

		// windows uses the backslash character as path delimiter - make sure slashes are used only
		$path2check = str_replace('\\', '/', $path2check);
		$absolutePath = str_replace('\\', '/', $absolutePath);

		if ($absolutePath == '')
			return preg_replace('#/+#', '/', $path2check); // nothing to prepend, just return $path2check 
		
		if ($path2check == '') 
			return preg_replace('#/+#', '/', $absolutePath); // no path to check, just return the absolute path to prepend 

		$newpath = $path2check; 
		// prepend absolute path, if needed			
		if (TYPO3_OS == 'WIN') {
			if ($path2check[1] != ':') {
				// windows 
				$newpath = $absolutePath . '/' . $path2check;
			}
		} else {
			// linux etc.
			if ($path2check[0] != '/') 
				$newpath = $absolutePath . '/' . $path2check;
		}

		return preg_replace('#/+#', '/', $newpath); // remove multiple slashes
	}
	
	/// Get the tx_newspaper_Section object of the page currently displayed
	/** Currently, that means it returns the tx_newspaper_Section record which
	 *  lies on the current Typo3 page. This implementation may change, but this
	 *  function is required to always return the correct tx_newspaper_Section.
	 * 
	 *  \return The tx_newspaper_Section object the plugin currently works on
	 *  \throw tx_newspaper_IllegalUsageException if the current page is not 
	 * 		associated with a tx_newspaper_Section.
	 */
	public static function getSection() {
		$section_uid = intval($GLOBALS['TSFE']->page['tx_newspaper_associated_section']);

        if (!$section_uid) {
        	throw new tx_newspaper_IllegalUsageException('No section associated with current page');
        }
		
		return new tx_newspaper_Section($section_uid);
	}
	
	/// Return the name of the SQL table \p $class is persistently stored in
	/** \param $class either object or a class name to find the SQL table for
	 *  \return The lower-cased class name of \p $class (= name of associated
	 * 		db table; newspaper convention)
	 */
	public static function getTable($class) {
		if (is_object($class)) {
			return strtolower(get_class($class));
		}
		return strtolower($class);
	}

	/// Get all child classes (but child only, no grand children etc.)
	/** Basically used to get concrete classes which extend an abstract class
	 *  \param $class_name Name of class to look for child classes
	 *  \return List of child classes
	 */
	public static function getChildClasses($class_name) {
		if ($class_name == '') return array();
		$class_name = strtolower($class_name);
		$child_list = array();
		foreach(get_declared_classes() as $cl) {
			if (strtolower(get_parent_class($cl)) == $class_name && strtolower($cl) != $class_name) 
				$child_list[] = $cl;
		}
		return $child_list;	
	}

	/// Check if a given class implements a given interface
	/** \param $class PHP class to check if it implements \p $interface
	 *  \param $interface PHP interface to check if it is implemented by \p $class
	 *  \return true if given \p $class implentes given \p $interface
	 */
	public static function classImplementsInterface($class, $interface) {
		if (!class_exists($class))
			return false;
		$tmp_impl = class_implements($class);
		if (isset($tmp_impl[$interface])) {
			return true;
		}
		return false;
	}
	
	/// checks if a string starts with a specific text
	/** \param $haystack string to searched
	 *  \param $needle string to search for
	 *  \param $caseSensitive specifies if the search is case-sensitive (default=false)
	 */
	public static function startsWith($haystack, $needle, $caseSensitive=false) {
		if ($caseSensitive) {
    		return (strpos($haystack, $needle) === 0);
		}
		return (stripos($haystack, $needle) === 0);
	}
	

	/// Get a list of all the attributes/DB fields an object (or class) has
	/** \param $object An object of the desired class, or the class name as string
	 *  \return The list of attributes
	 */
	public static function getAttributes($object) {
		global $TCA;
		$object = self::getTable($object);
		t3lib_div::loadTCA($object);
		return array_keys($TCA[$object]['columns']);
	}
	
	/// The \c GET parameter which determines the article UID
	public static function GET_article() {
		return self::article_get_parameter;
	}

	///	The \c GET parameter which determines which page type is displayed
	public static function GET_pagetype() {
		return self::pagetype_get_parameter;
	}

	/// Create a HTML link with text and URL using the \c typolink() API function 
	/** \param  $text the text to be displayed
	 *  \param  $params target and optional \c GET parameters as parameter => value
	 *  \param  $conf optional TypoScript configuration array
	 *  \return array ['text'], ['href']		
	 */
    public static function typolink($text, array $params = array(), array $conf = array()) {
		//  a tslib_cObj object is needed to call the typolink_URL() function
		if (!self::$local_cObj) {
	        self::$local_cObj = t3lib_div::makeInstance("tslib_cObj");
    	    self::$local_cObj->setCurrentVal($GLOBALS["TSFE"]->id);
		}

		//  make sure $params is a one-dimensional array
		foreach ($params as $key => $param) {
			if (is_array($param)) {
				foreach ($param as $subkey => $value) {
					$params[$key.'['.$subkey.']'] = $value;
				}
				unset($params[$key]);
			}
		}

		//	set TypoScript config array
		if ($conf) $temp_conf = $conf;
		else $temp_conf = array();

		if ($params['id']) $temp_conf['parameter'] = $params['id'];
	    else $temp_conf['parameter.']['current'] = 1;
	    unset($params['id']);

	    $no_cache = false;
   		$sep = '&';
	    if (sizeof($params) > 0) {
	    	foreach ($params as $key => $value) {
				if ($key == 'no_cache' && $value != 0) $no_cache = true;
				if ($key != 'cHash') {
    	    		$temp_conf['additionalParams'] .= "$sep$key=$value";
    	    		$sep = '&';
				}
        	}
	    }
        if (!$no_cache) $temp_conf['useCacheHash'] = 1;

    	//	call typolink_URL() and return data
    	$data = array();
    	$data['text'] = $text;
    	$data['href'] = self::$local_cObj->typolink_URL($temp_conf);

		return $data;
	}

	/// Get a typolink-compatible URL
	/** \param  $params Target and optional \c GET parameters. See the TSRef for
	 * 		details, eg. http://typo3.org/documentation/document-library/references/doc_core_tsref/4.1.0/view/5/8/
	 *  \param  $conf Optional TypoScript configuration array, if present. See
	 * 		TSRef.
	 *  \return Generated URL										  
	 */
	public static function typolink_url(array $params = array(), array $conf = array()) {
		$link = self::typolink('', $params, $conf);
		return $link['href'];
	}
	
	/// Find out whether we are displaying a tx_newpaper_Article right now
	/** \return true, if on an Article tx_newspaper_Page
	 */
	public static function onArticlePage() {
		$pagetype = new tx_newspaper_PageType($_GET);
		$is_article_page = $pagetype->getAttribute('is_article_page');
		return $is_article_page;
	}
	
	public static function currentURL() {
		$hostname = $_SERVER['SERVER_NAME'];
		$baseURI = explode($hostname, t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'));
		$https = $_SERVER['HTTPS'];
		$url = 'http' . ($https? 's': '') . '://'.$hostname.$baseURI[1];
		return $url;
	}
	
	/// \return current protocol and host
	public static function currentProtocolHost() {
		return 'http' . ($_SERVER['HTTPS']? 's': '') . '://' . $_SERVER['SERVER_NAME'];
	}
	
	public static function registerSource($key, tx_newspaper_Source $new_source) {
		self::$registered_sources[$key] = $new_source;
	}

	public static function getRegisteredSources() {
		return self::$registered_sources;
	}

	public static function getRegisteredSource($key) {
		return self::$registered_sources[$key];
	}
	
	/// Value for field \c tag_type of table \c tx_newspaper_tag denoting dossier tags	
	public static function getControlTagType() {
		$TSConfig = t3lib_BEfunc::getPagesTSconfig($GLOBALS['TSFE']->page['uid']);
		$ctt = intval($TSConfig['newspaper.']['control_tag_type']);
		return $ctt? $ctt: 2;
	}

    public static function getContentTagType() {
        $TSConfig = t3lib_BEfunc::getPagesTSconfig($GLOBALS['TSFE']->page['uid']);
		$ctt = intval($TSConfig['newspaper.']['content_tag_type']);
		return $ctt? $ctt: 1;
    }
	
	public static function registerSaveHook($class) {
		self::$registered_savehooks[] = $class;
	}
	
	public static function getRegisteredSaveHooks() {
		return self::$registered_savehooks;
	}
	
	////////////////////////////////////////////////////////////////////////////
	
	/** SQL queries are stored as a static member variable, so they can be 
	 *  accessed for debugging from outside the function if a query does not  
	 *  return the desired result.
	 */ 
	public static $query = ''; 
	
	/// a \c tslib_cObj object used to generate typolinks
	private static $local_cObj = null;
	
	private static $registered_sources = array();
	
	private static $registered_savehooks = array();
	
}

?>