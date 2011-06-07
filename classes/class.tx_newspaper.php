<?php

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

    /// Whether to measure the execution times of functions
    const log_execution_times = true;

    /// GET-parameter describing the wanted control tag for a dossier
    const default_dossier_get_parameter = 'dossier';

    const default_max_logged_queries = 1000;

    ////////////////////////////////////////////////////////////////////////////
    //      DB functions
    ////////////////////////////////////////////////////////////////////////////

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

  /// Execute a \c SELECT query and return the amount of records in the result set
  /** enableFields() are taken into account.
   *  \param $table Table to \c SELECT \c FROM
   *  \param $where \c WHERE - clause (defaults to selecting all records)
   *  \param $groupBy Fields to \c GROUP \c BY
   *  \return number of records found
   *  \throw tx_newspaper_NoResException if no result is found, probably due
   * 		to a SQL syntax error
   */
  public static function countRows($table, $where='1', $groupBy='') {

    self::writeFunctionAndArgumentsToLog('logDbSelect');

    if (!is_object($GLOBALS['TYPO3_DB'])) {
      $GLOBALS['TYPO3_DB'] = t3lib_div::makeInstance('t3lib_DB');
    }

    self::$query = $GLOBALS['TYPO3_DB']->SELECTquery(
      'COUNT(*) AS c',
      $table,
      $where . self::enableFields($table),
      $groupBy
    );
    $res = $GLOBALS['TYPO3_DB']->sql_query(self::$query);

    $count = 0;
    if ($res) {
          if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $count = $row['c'];
          }

          self::writeNewspaperLogEntry('logDbSelect', 'selectRows, #results: ' . $count);

      return $count;
    } else {
      throw new tx_newspaper_NoResException(self::$query);
    }
  }

    public static function startLoggingQueries() {

        self::startExecutionTimer();

        self::$are_queries_logged = true;
    }

    public static function getLoggedQueries() {

        $queries = self::$logged_queries;
        self::$logged_queries = array();
        self::$are_queries_logged = false;

        $timing = self::getTimingInfo();
        $queries = array_merge($queries, $timing);

        return $queries;
    }

    public static function setNumLoggedQueries($num = self::default_max_logged_queries) {
        self::$max_logged_queries = $num;
    }

    private static function logQuery() {
        if (self::$are_queries_logged) {
            if (sizeof(self::$logged_queries) > self::$max_logged_queries) {
                self::$are_queries_logged = false;
                self::$logged_queries[] = 'Number of maximum logged queries exceeded; turning off logging.';
                self::$logged_queries[] = 'Call tx_newspaper::setMaxLoggedQueries() to increase the number of logged queries.';
            } else {
                self::$logged_queries[] = self::$query;
            }
        }
    }

    private static function executeQuery() {
        self::logQuery();
        return $GLOBALS['TYPO3_DB']->sql_query(self::$query);
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
        $res = self::executeQuery();

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
        $res = self::executeQuery();

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

        $res = self::executeQuery();

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

        $res = self::executeQuery();
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

    if (!$row) return;

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
            $res = self::executeQuery();

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
        $res = self::executeQuery();

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
   *  \param $key Name of key to be used for the query (default: 'uid')
   *  \param $additional_where be added to the generated where part (AND is added too)
   *  \return number of affected rows
   *  \throw tx_newspaper_NoResException if no result is found, probably due
   * 		to a SQL syntax error
   *  \throw tx_newspaper_DBException if an error occurs in process_datamap()
   */
  public static function deleteRows($table, $uids_or_where, $key='uid', $additional_where='') {

    if (!$uids_or_where) return;


    self::writeFunctionAndArgumentsToLog('logDbInsertUpdateDelete');

    if (!is_object($GLOBALS['TYPO3_DB'])) {
      $GLOBALS['TYPO3_DB'] = t3lib_div::makeInstance('t3lib_DB');
    }
    if (self::use_datamap && is_array($uids_or_where)) {
      self::deleteUsingCmdMap($table, $uids_or_where);
    } else {
      global $TCA;
      t3lib_div::loadTCA($table);

      if (is_array($uids_or_where)) {
        if (count($uids_or_where) <= 0) return;
        $uids_or_where = $key . ' IN ( 0, ' . implode(', ', $uids_or_where) . ')';
      } else if (is_int($uids_or_where)) {
        $uids_or_where = $key . '=' . $uids_or_where;
      }

      $where_parts[] = $uids_or_where;
      if ($additional_where) {
        $where_parts[] = $additional_where;
      }
      $where = implode(' AND ', $where_parts);

      if (isset($TCA[$table])) {
        self::$query = $GLOBALS['TYPO3_DB']->UPDATEquery($table, $where, array('deleted' => 1));
      } else {
        self::$query = $GLOBALS['TYPO3_DB']->DELETEquery($table, $where);
      }
//t3lib_div::devlog('deleteRows()', 'newspaper', 0, array('query' => self::$query));

            $res = self::executeQuery();

      if (!$res) {
            throw new tx_newspaper_NoResException(self::$query);
          }
    }

        return $GLOBALS['TYPO3_DB']->sql_affected_rows();
  }

  /// Deletes a record from a DB table using a Typo3 command map
  /**
   *  \param $table SQL table to delete a record from
   *  \param $uids Array of UIDs to delete
   *  \return number of affected rows
   *  \throw tx_newspaper_DBException if an error occurs in process_datamap()
   */

  public static function deleteUsingCmdMap($table, array $uids) {
    $cmdmap = array();
    foreach ($uids as $uid) {
      $cmdmap[$table][$uid] = array('delete' => 1);
    }

    $tce = t3lib_div::makeInstance('t3lib_TCEmain');
    $tce->start(null, $cmdmap);
    $tce->process_cmdmap();
    if (count($tce->errorLog)){
      throw new tx_newspaper_DBException(print_r($tce->errorLog, 1));
    }
//t3lib_div::devlog('deleteUsingCmdMap()', 'newspaper', 0, array('number rows' => $GLOBALS['TYPO3_DB']->sql_affected_rows()));
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
        $res = self::executeQuery();

    if (!$res) throw new tx_newspaper_NoResException(self::$query);

    $fields = array();
    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
      $fields[] = $row['Field'];
    }

      return $fields;
  }

    /// Returns an array which has the fields of SQL table \p $table as keys
    public static function makeArrayFromFields($table) {
        $fields = self::getFields($table);
        $array = array();

        foreach ($fields as $field) $array[$field] = null;

        return $array;
    }

	/// \c WHERE clause to filter out unwanted records
	/** Returns a part of a \c WHERE clause which will filter out records with
	 *  start/end times, deleted flag set, or hidden flag set (if hidden should
	 *  be included used); switch for BE/FE is included.
	 *
	 *  \param $tableString name of db table to check (can be a comma separated
	 *         list of tables too)
	 *  \param $show_hidden [0|1] specifies if hidden records are to be included
	 * 		(ignored if in FE)
	 *  \return \c WHERE part of an SQL statement starting with \c AND; or an
	 * 		empty string, if not applicable.
	 */
	static public function enableFields($tableString, $show_hidden = 1) {
	    require_once(PATH_t3lib . '/class.t3lib_page.php');

	    // might be a comma separated list of tables
		$allTables = explode(',', $tableString);

		$enableFields = '';
		foreach($allTables as $table) {
			if (strpos($table, ' ') != false) {
				// cut of alias (if any)
				$table = substr($table, 0, strpos($table, ' '));
			}
			$table = trim($table);

			t3lib_div::loadTCA($table); // make sure tca is available

			if (isset($GLOBALS['TCA'][$table])) {
				if (TYPO3_MODE == 'FE') {
					// use values defined in admPanel config (override given $show_hidden param)
					// see: enableFields() in t3lib_pageSelect
					$show_hidden = ($table=='pages')? $GLOBALS['TSFE']->showHiddenPage : $GLOBALS['TSFE']->showHiddenRecords;
					$p = t3lib_div::makeInstance('t3lib_pageSelect');
					$enableFields .= $p->enableFields($table, $show_hidden);
	    		} else {
					// Show everything but deleted records in backend, if deleted flag is existing for given table
    				if (isset($GLOBALS['TCA'][$table]['ctrl']['delete'])) {
      					$enableFields .= ' AND ' . $table . '.' . $GLOBALS['TCA'][$table]['ctrl']['delete'] . '=0';
    				}
	    		}
			}

		}

    	return $enableFields;
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

    public static function setDefaultFields(tx_newspaper_StoredObject &$object, array $fields) {
        foreach (self::getDefaultFieldValues($fields) as $attribute => $value) {
            $object->setAttribute($attribute, $value);
        }
        $object->setAttribute('pid', tx_newspaper_Sysfolder::getInstance()->getPid($object));
    }

    public static function getDefaultFieldValues(array $fields) {
        $values = array();
        foreach($fields as $field) {
          if (isset(self::$defaultFields[$field])) {
            $function = self::$defaultFields[$field];
            $values[$field] = self::$function();
          }
        }

        return $values;
    }

  private static $defaultFields = array(
      'tstamp' => 'getTimestamp',
      'crdate' => 'getTimestamp',
      'cruser_id' => 'getBeUserID',
      'modification_user' => 'getBeUserID',
  );

  private static function getTimestamp() { return time(); }
  private static function getBeUserID() { return tx_newspaper::getBeUserUid(); }

    ////////////////////////////////////////////////////////////////////////////
    //      Logging functions
    ////////////////////////////////////////////////////////////////////////////

    /// I've had enough of supplying all these useless parameters to devlog! Here's a wrapper function.
    public static function devlog($message, $data = array(), $extension = 'newspaper', $level = 0) {
        if (!is_array($data)) $data = array($data);
        t3lib_div::devlog($message, $extension, $level, $data);

    }

    /// Logs the current operation
    private static function writeFunctionAndArgumentsToLog($type) {
    $backtrace = debug_backtrace();
    $previous_function = $backtrace[1];
    $function_name = $previous_function['function'];
    $args = join("\n", $previous_function['args']);
    self::writeNewspaperLogEntry($type, "$function_name\n$args");
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

    public static function startExecutionTimer() {
        self::$execution_time_stack[] = microtime(true);

        self::$execution_start_time = microtime(true);
    }

    public static function logExecutionTime($message = '') {

        $timing_info = self::getTimingInfo();
        $timing_info['message'] = $message;

        if (self::log_execution_times) self::devlog('logExecutionTime', $timing_info);
    }

    private static function getTimingInfo() {
        $start_time = array_pop(self::$execution_time_stack);
        $execution_time = microtime(true)-$start_time;
        $execution_time_ms = 1000*$execution_time;

        return array(
            'execution time' => $execution_time_ms . ' ms',
            'object' => self::getTimedObject(),
        );
    }

    private static function getTimedObject() {
        $backtrace = array_slice(debug_backtrace(), 0, 5);
        foreach($backtrace as $function) {
            if ($function['class'] == 'tx_newspaper') continue;
            return $function['object'];
        }
    }

    ////////////////////////////////////////////////////////////////////////////

    /// \return Array with TSConfig set in newspaper root folder
    public static function getTSConfig() {
        $root_page = tx_newspaper_Sysfolder::getInstance()->getPidRootfolder();
        return t3lib_BEfunc::getPagesTSconfig($root_page);
    }

    private static $tsconfig = array();
    public static function getTSConfigVar($key) {
        if (empty(self::$tsconfig)) self::$tsconfig = self::getTSConfig();
        if (!is_array($key)) {
            return self::$tsconfig[$key];
        }
        throw new tx_newspaper_NotYetImplementedException('Multidimensional TSConfig');
    }

    public static function getDossierPageID() {

        $TSConfig = self::getTSConfig();

        $dossier_page = intval($TSConfig['newspaper.']['dossier_page_id']);
        if (!$dossier_page) {
            throw new tx_newspaper_IllegalUsageException(
                'No dossier page defined. Please set newspaper.dossier_page_id in TSConfig!'
            );
        }
        return $dossier_page;
    }

    public static function getDossierGETParameter() {

        $TSConfig = self::getTSConfig();
        $dossier_get_parameter = $TSConfig['newspaper.']['dossier_get_parameter'];
        if (!$dossier_get_parameter) $dossier_get_parameter = self::default_dossier_get_parameter;

        return $dossier_get_parameter;
    }


  /// get absolute path to Typo3 installation
  /** \param $endsWithSlash determines if the returned path ends with a slash
   *  \return absolute path to Typo3 installation
   */
  public static function getAbsolutePath($endsWithSlash=true) {

    $path = self::getBasePath();

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
      $path = rtrim($path, '/');
    }

    // first character in ABSOLUTE path must be a "/"
    if (substr($path, 0, 1) != '/') {
      $path = '/' . $path;
    }

    return $path;
  }

  public static function getBasePath() {

    if (self::isTazSpambusterHackNeeded()) return self::tazSpambusterHack();

    /// \todo replace by a version NOT using EM conf (check t3lib_div)
    $em_conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['newspaper']);

    if (!isset($em_conf['newspaperTypo3Path']) || !$em_conf['newspaperTypo3Path']) {
      throw new tx_newspaper_Exception('newspaperTypo3Path was not set in EM');
    }

    return trim($em_conf['newspaperTypo3Path']);
  }

  private static function isTazSpambusterHackNeeded() {
    return (stripos($_SERVER['HTTP_HOST'], 'spambuster.taz.de') !== false);
  }

  private static function tazSpambusterHack() {
    return '/onlinetaz/red';
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

  public static function getTranslation($key, $translation_file = 'locallang_newspaper.xml', $extension = 'newspaper') {
    global $LANG;

    if ($translation_file === false) { throw new tx_newspaper_IllegalUsageException('You forgot to remove the "false" parameter when refactoring!'); }
    if ($translation_file === true) { throw new tx_newspaper_IllegalUsageException('You forgot to remove the "true" parameter when refactoring!'); }

    if (!($LANG instanceof language)) {
      require_once(t3lib_extMgm::extPath('lang', 'lang.php'));
      $LANG = t3lib_div::makeInstance('language');
      $LANG->init('default');
    }

    return $LANG->sL("LLL:EXT:$extension/$translation_file:$key", false);
  }


    /// Symbol used as a replacement for whitespace characters by normalizeString().
    const space = '_';

    /// Normalizes a string to a basic subset of ASCII, for use in e.g. URLs.
    /** - Convert spaces to underscores
     *  - Convert non A-Z characters to ASCII equivalents
     *  - Convert some special things like the 'ae'-character
     *  - Strip off all other symbols
     *  Works with the character set defined as "forceCharset", if defined. Otherwise
     *  uses the charset used by \p $string.
     *
     *  Adapted from Extension RealURL: tx_realurl_advanced::encodeTitle()
     *
     * @param	$string		String to clean up
     * @return	string		Encoded \p $string, passed through rawurlencode() = ready to put in the URL.
     */
    public static function normalizeString($string) {

        $cs_converter = $GLOBALS['TSFE']->csConvObj;
        if (!$cs_converter instanceof t3lib_cs) return $string;

        // Fetch character set:
        $charset = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] ? $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] : $GLOBALS['TSFE']->defaultCharSet;

        // Convert to lowercase:
        $normalizedString = $cs_converter->conv_case($charset, $string, 'toLower');

        // Convert some special tokens to the space character:
        $normalizedString = preg_replace('/[ -+_]+/', self::space, $normalizedString); // convert spaces

        // Convert extended letters to ascii equivalents:
        $normalizedString = $cs_converter->specCharsToASCII($charset, $normalizedString);

        $normalizedString = preg_replace('[^a-zA-Z0-9\\' . self::space . ']', '', $normalizedString); // strip the rest
        $normalizedString = preg_replace('\\' . self::space . '+', self::space, $normalizedString); // Convert multiple 'spaces' to a single one
        $normalizedString = trim($normalizedString, self::space);

        // Return encoded URL:
        return rawurlencode($normalizedString);
    }


    /// Basic url encoding: encodes '?', '=' and '&' only
    /// \param $url URL to be encoded
    /// \return encoded URL
    public static function encodeUrlBasic($url) {
    $chars = array('?', '=', '&');
    $replaceWith = array('%3F', '%3D', '%26');
    return str_replace($chars, $replaceWith, $url);
    }


  /**
   * Gets field data of current be_user
   * \param $field Name of field to be read
   * \return $field data of logged in be_user, or false if data couldn't be fetched
   */
  public static function getBeUserData($field) {
    $field = htmlspecialchars($field);
    // check global object BE_USER first
    if (isset($GLOBALS['BE_USER']->user[$field])) {
      return $GLOBALS['BE_USER']->user[$field];
    }
    // check session then
    if ($_COOKIE['be_typo_user']) {
      if ($row = tx_newspaper::selectZeroOrOneRows(
        'ses_userid',
        'be_sessions',
        'sed_id=' . $_COOKIE['be_typo_user'] . ' AND ses_name="be_typo_user"'
      )) {
        $user = tx_newspaper::selectZeroOrOneRows(
          $field,
          'be_users',
          'uid=' . intval($_COOKIE['be_typo_user']) . ' AND deleted=0'
        );
        return $user[$field];
      }
    }
    return false;
  }
  /**
   * Gets the uid of current be_user
   * \return uid of logged in be_user, or 0 if uid couldn't be fetched
   */
  public static function getBeUserUid() {
    $uid = self::getBeUserData('uid');
    if ($uid != false) {
      return self::getBeUserData('uid');
    } else {
      return 0;
    }
  }


    ////////////////////////////////////////////////////////////////////////////

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

    ////////////////////////////////////////////////////////////////////////////

  /// Create a HTML link with text and URL using the \c typolink() API function
  /** \param  $text the text to be displayed
   *  \param  $params target and optional \c GET parameters as parameter => value
   *  \param  $conf optional TypoScript configuration array
   *  \return array ['text'], ['href']
   *
   *  \todo I don't think typolink() is called even once in \c tx_newspaper. Scrap
   *  	this function and put all functionality into typolink_url().
   */
    public static function typolink($text, array $params = array(), array $conf = array()) {

    if (TYPO3_MODE == 'BE') {
      self::buildTSFE();
      if (!is_object($GLOBALS['TSFE']) || !($GLOBALS['TSFE'] instanceof tslib_fe)) {
        throw new tx_newspaper_Exception('Tried to generate a typolink in the BE. Could not instantiate $GLOBALS[TSFE]. Have to give up, sorry.');
      }
    }

    self::makeLocalCObj();

    self::flattenParamsArray($params);

    $temp_conf = self::makeTSConfForTypolink($params, $conf);

      //	call typolink_URL() and return data
      $data = array(
      'text' => $text,
        'href' => self::$local_cObj->typolink_URL($temp_conf)
      );

    return $data;
  }

  /// populate \c $GLOBALS['TSFE'] even if we're in the BE
  /** Thanks to typo3.net user semidark. Function lifted from
   *  http://www.typo3.net/forum/list/list_post//39975/?tx_mmforum_pi1[page]=&tx_mmforum_pi1[sword]=typolink%20backend%20modules#pid149544
   */
  public static function buildTSFE() {

  	require_once(PATH_t3lib.'class.t3lib_timetrack.php');

    $page_id = 1;	/// \todo Ensure that this is a valid page ID

    /* Declare */
    $temp_TSFEclassName = t3lib_div::makeInstanceClassName('tslib_fe');

      /* Begin */
    if (!is_object($GLOBALS['TT'])) {
      $GLOBALS['TT'] = new t3lib_timeTrack;
      $GLOBALS['TT']->start();
    }

    if (!is_object($GLOBALS['TSFE'])) {
      //*** Builds TSFE object
      $GLOBALS['TSFE'] = new $temp_TSFEclassName($GLOBALS['TYPO3_CONF_VARS'],$page_id,0,0,0,0,0,0);

      //*** Builds sub objects
      $GLOBALS['TSFE']->tmpl = t3lib_div::makeInstance('t3lib_tsparser_ext');
      $GLOBALS['TSFE']->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');

      //*** init template
      $GLOBALS['TSFE']->tmpl->tt_track = 0;// Do not log time-performance information
      $GLOBALS['TSFE']->tmpl->init();

      $rootLine = $GLOBALS['TSFE']->sys_page->getRootLine($page_id);

      //*** This generates the constants/config + hierarchy info for the template.

      $GLOBALS['TSFE']->tmpl->runThroughTemplates($rootLine,$template_uid);
      $GLOBALS['TSFE']->tmpl->generateConfig();
      $GLOBALS['TSFE']->tmpl->loaded=1;

      //*** Get config array and other init from pagegen
      $GLOBALS['TSFE']->getConfigArray();
      $GLOBALS['TSFE']->linkVars = ''.$GLOBALS['TSFE']->config['config']['linkVars'];

      if ($GLOBALS['TSFE']->config['config']['simulateStaticDocuments_pEnc_onlyP'])
      {
        foreach (t3lib_div::trimExplode(',',$GLOBALS['TSFE']->config['config']['simulateStaticDocuments_pEnc_onlyP'],1) as $temp_p)
        {
          $GLOBALS['TSFE']->pEncAllowedParamNames[$temp_p]=1;
        }
      }
      //*** Builds a cObj
      $GLOBALS['TSFE']->newCObj();
    }
  }

  ///  A tslib_cObj object is needed to call the typolink_URL() function
  private static function makeLocalCObj() {
    if (!self::$local_cObj) {
          self::$local_cObj = t3lib_div::makeInstance("tslib_cObj");
          self::$local_cObj->setCurrentVal($GLOBALS["TSFE"]->id);
    }
  }

  /// Make sure \p $params is a one-dimensional array
  private static function flattenParamsArray(array &$params) {
    foreach ($params as $key => $param) {
      if (is_array($param)) {
        foreach ($param as $subkey => $value) {
          $params[$key.'['.$subkey.']'] = $value;
        }
        unset($params[$key]);
      }
    }
  }

  ///	set TypoScript config array - yeah I know, it's not TSConfig!
  private static function makeTSConfForTypolink(array $params, array $conf) {

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

    return $temp_conf;
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

    ////////////////////////////////////////////////////////////////////////////


  /// \return array [key]=value if $key is found in config file, emtpy array else
  public static function getNewspaperConfig($key) {

    if (!self::$newspaperConfig) {
      $newspaper_config_file = t3lib_extMgm::extPath('newspaper') . '/newspaper.conf';
          if (is_readable($newspaper_config_file)) {
              self::$newspaperConfig = parse_ini_file($newspaper_config_file);
          }
    }

    if (isset(self::$newspaperConfig[$key])) {
      return array($key => self::$newspaperConfig[$key]);
    }

    return array();

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
    if (!isset(self::$registered_sources[$key])) {
      throw new tx_newspaper_InconsistencyException(
          "Requested source '$key' not present in registered sources: " . print_r(self::$registered_sources, 1)
      );
    }
    return self::$registered_sources[$key];
  }

  public static function registerSaveHook($class) {
    self::$registered_savehooks[] = $class;
  }

  public static function getRegisteredSaveHooks() {
    return self::$registered_savehooks;
  }

  // Register $extKey so extKey's tca.php is loaded after newspaper/tca_php_addon.php is loaded
  public static function registerSubTca($extKey) {
    if (t3lib_extMgm::isLoaded($extKey)) {
      self::$registeredSubTca[] = $extKey;
    }
  }

  /// Load tca.php from all registered extensions (so modifications are visible in newspaper)
  public static function loadSubTca() {
    foreach(self::$registeredSubTca as $extKey) {
      $file = PATH_typo3conf . 'ext/' . $extKey . '/tca.php';
      if (is_readble($file)) {
        require_once $file;
      }
    }
  }

  ////////////////////////////////////////////////////////////////////////////

  /** SQL queries are stored as a static member variable, so they can be
   *  accessed for debugging from outside the function if a query does not
   *  return the desired result.
   */
  public static $query = '';

    private static $logged_queries = array();

    private static $are_queries_logged = false;

    private static $max_logged_queries = self::default_max_logged_queries;

  /// a \c tslib_cObj object used to generate typolinks
  private static $local_cObj = null;

  private static $registered_sources = array();

  private static $registered_savehooks = array();

  private static $registeredSubTca = array();

    private static $execution_start_time = 0;

    private static $newspaperConfig = null;

    private static $execution_time_stack = array();

}

?>
