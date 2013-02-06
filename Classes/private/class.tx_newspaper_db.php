<?php
/**
 * @author Lene Preuss <lene.preuss@gmail.com>
 */

require_once('class.tx_newspaper_tabledescription.php');

require_once('class.tx_newspaper_dbtransaction.php');

class tx_newspaper_DB {

    ///	Whether to use Typo3's command- and datamap functions for DB operations
    /** If this constant is set to true, Typo3 command- or datamap functions are
     *  used wherever appropriate.
     *
     *  These functions have side effects which are not yet fully explored and
     *  seem to make more trouble than they're worth. That's why they're turned
     *  off currently.
     */
    const use_datamap = false;

    const default_max_logged_queries = 1000;

    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new tx_newspaper_DB();
        }
        return self::$instance;
    }

    public static function getQuery() {
        $instance = self::getInstance();
        return $instance->query;
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
    public static function enableFields($tableString) {
        $enableFields = '';
        foreach(tx_newspaper_TableDescription::createDescriptions($tableString) as $tableDescription) {
            $enableFields .= $tableDescription->getEnableFields();
        }
        return $enableFields;
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

        /** @var $tce t3lib_TCEmain */
        $tce = t3lib_div::makeInstance('t3lib_TCEmain');
        $tce->start(null, $cmdmap);
        $tce->process_cmdmap();
        if (count($tce->errorLog)){
            throw new tx_newspaper_DBException(print_r($tce->errorLog, 1));
        }

        return $GLOBALS['TYPO3_DB']->sql_affected_rows();
    }

    /// Returns whether a specified record is available in the DB
    public function isPresent($table, $where, $use_enable_fields = true) {
        $res = $this->getResultSetForSelect(
            'uid', $table,
            $where . ($use_enable_fields? self::enableFields($table): ''),
            '', '', '1'
        );

        if (!$res) return false;
        $record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        if (!$record['uid']) return false;
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
    public function countRows($table, $where='1', $groupBy='') {

        $res = $this->getResultSetForSelectOrThrow(
            'COUNT(*) AS c', $table,
            $where . self::enableFields($table),
            $groupBy, '', ''
        );

        $record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        $count = intval($record['c']);

        self::writeNewspaperLogEntry('logDbSelect', 'selectRows, #results: ' . $count);

        return $count;
    }

    public function startLoggingQueries() {
        tx_newspaper_ExecutionTimer::start();
        $this->are_queries_logged = true;
    }

    public function getLoggedQueries() {

        $queries = $this->logged_queries;
        $this->logged_queries = array();
        $this->are_queries_logged = false;

        $timing = tx_newspaper_ExecutionTimer::getTimingInfo();
        $queries['Timing'] = "$timing";

        return $queries;
    }

    public function setNumLoggedQueries($num = self::default_max_logged_queries) {
        $this->max_logged_queries = $num;
    }

    public function executeQuery($query = '') {
        if ($query) $this->query = $query;
        self::logQuery();
        return $GLOBALS['TYPO3_DB']->sql_query($this->query);
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
    public function selectZeroOrOneRows($fields, $table, $where = '1',
                                        $groupBy = '', $orderBy = '', $limit = '') {

        $res = $this->getResultSetForSelectOrThrow(
            $fields, $table, $where . self::enableFields($table), $groupBy, $orderBy, $limit
        );

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
    public function selectOneRow($fields, $table, $where = '1',
                                 $groupBy = '', $orderBy = '', $limit = '') {

        $record = $this->selectZeroOrOneRows(
            $fields, $table, $where, $groupBy, $orderBy, $limit
        );

        if (!$record) throw new tx_newspaper_EmptyResultException($this->query);

        return $record;
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
    public function selectRows($fields, $table, $where = '1',
                               $groupBy = '', $orderBy = '', $limit = '') {
        return $this->selectRowsDirect(
            $fields, $table, $where . self::enableFields($table), $groupBy, $orderBy, $limit
        );
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
    public function selectRowsDirect($fields, $table, $where = '1',
                                     $groupBy = '', $orderBy = '', $limit = '') {

        $res = $this->getResultSetForSelectOrThrow(
            $fields, $table, $where, $groupBy, $orderBy, $limit
        );

        $records = self::getRecordsFromResultSet($res);

        self::writeNewspaperLogEntry('logDbSelect', 'selectRowsDirect, #results: ' . sizeof($records));

        return $records;
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
    public function selectMMQuery($select, $local_table, $mm_table, $foreign_table,
                                  $whereClause='' ,$groupBy='', $orderBy='', $limit='')  {

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

        return $this->selectRows(
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
    public function insertRows($table, array $record) {

        if (!$record) return 0;

        self::writeFunctionAndArgumentsToLog('logDbInsertUpdateDelete');

        global $TCA;
        t3lib_div::loadTCA($table);

        $this->setTimestampIfPresent($table, $record);

        self::ensureTypo3DBObject();

        if (isset($TCA[$table]) && self::use_datamap) {
            return $this->insertUsingDatamap($table, $record);
        }

        $this->query = $GLOBALS['TYPO3_DB']->INSERTquery($table, $record);
        $res = $this->executeQuery();

        if (!$res) throw new tx_newspaper_NoResException($this->query);

        return $GLOBALS['TYPO3_DB']->sql_insert_id();
    }

    /// Updates a record using the Typo3 API
    /** \param $table SQL table to update
     *  \param $where SQL \c WHERE condition (typically 'uid = ...')
     *  \param $row Data as key=>value pairs
     *  \return number of affected rows
     *  \throw tx_newspaper_NoResException if no result is found, probably due
     * 		to a SQL syntax error
     */
    public function updateRows($table, $where, array $row) {

        self::writeFunctionAndArgumentsToLog('logDbInsertUpdateDelete');

        unset ($row['uid']);

        $this->setTimestampIfPresent($table, $row);

        self::ensureTypo3DBObject();
        $this->query = $GLOBALS['TYPO3_DB']->UPDATEquery($table, $where, $row);
        $res = $this->executeQuery();

        if (!$res) throw new tx_newspaper_NoResException($this->query);

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
    public function deleteRows($table, $uids_or_where, $key='uid', $additional_where='') {

        if (!$uids_or_where) return 0;

        self::writeFunctionAndArgumentsToLog('logDbInsertUpdateDelete');

        self::ensureTypo3DBObject();

        if (self::use_datamap && is_array($uids_or_where)) {
            self::deleteUsingCmdMap($table, $uids_or_where);
        } else {
            global $TCA;
            t3lib_div::loadTCA($table);

            if (is_array($uids_or_where)) {
                if (count($uids_or_where) <= 0) return 0;
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
                $this->query = $GLOBALS['TYPO3_DB']->UPDATEquery($table, $where, array('deleted' => 1));
            } else {
                $this->query = $GLOBALS['TYPO3_DB']->DELETEquery($table, $where);
            }

            $res = $this->executeQuery();

            if (!$res) throw new tx_newspaper_NoResException($this->query);

        }

        return $GLOBALS['TYPO3_DB']->sql_affected_rows();
    }

    /// If \p $table has a \c tstamp field, set it to current time in \p $row
    public function setTimestampIfPresent($table, array &$row) {
      if (!isset($row['tstamp']) && $this->fieldExists($table, 'tstamp')) {
          $row['tstamp'] = time();
      }
    }

    /// Returns true if SQL table \p $table has a field called \p $field
    public function fieldExists($table, $field) {
        return in_array($field, $this->getFields($table));
    }

    /// Returns the fields that are present in SQL table \p $table
    public function getFields($table) {
        $this->query = "SHOW COLUMNS FROM $table";
        $res = $this->executeQuery();

        if (!$res) throw new tx_newspaper_NoResException($this->query);

        $fields = array();
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $fields[] = $row['Field'];
        }

      return $fields;
    }

    /// Returns an array which has the fields of SQL table \p $table as keys
    public function makeArrayFromFields($table) {
        $fields = $this->getFields($table);
        $array = array();

        foreach ($fields as $field) $array[$field] = null;

        return $array;
    }

    /// Gets sorting position for next element in a MM table
    /** \param $table name of MM table
     *  \param $uid_local
     *  \return sorting position of element inserted as last element
     */
    public function getLastPosInMmTable($table, $uid_local) {
        $row = $this->selectRows(
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
    public function atLeastOneRecord($table) {
        try {
            $this->selectOneRow('uid', $table, '1', '', '', '1');
            return true;
        } catch (tx_newspaper_EmptyResultException $e) {
            return false;
        }
    }

    public function assembleSQLQuery($fields, $table, $where = '1', $groupBy = '', $orderBy = '', $limit = '') {
        return $GLOBALS['TYPO3_DB']->SELECTquery($fields, $table, $where, $groupBy, $orderBy, $limit);
    }

    ////////////////////////////////////////////////////////////////////////////

    private function __construct() { }

    private function getResultSetForSelect($fields, $table, $where, $groupBy, $orderBy, $limit) {
        self::writeFunctionAndArgumentsToLog('logDbSelect');

        self::ensureTypo3DBObject();

        $this->query = $this->assembleSQLQuery($fields, $table, $where, $groupBy, $orderBy, $limit);
        return $this->executeQuery();
    }

    private function getResultSetForSelectOrThrow($fields, $table, $where, $groupBy, $orderBy, $limit) {
        $res = $this->getResultSetForSelect($fields, $table, $where, $groupBy, $orderBy, $limit);
        if (!$res) throw new tx_newspaper_NoResException($this->query);
        return $res;
    }

    private function logQuery() {
        if (!$this->are_queries_logged) return;

        if (sizeof($this->logged_queries) > $this->max_logged_queries) {
            $this->are_queries_logged = false;
            $this->logged_queries[] = 'Number of maximum logged queries exceeded; turning off logging.';
            $this->logged_queries[] = 'Call tx_newspaper::setMaxLoggedQueries() to increase the number of logged queries.';
        } else {
            $this->logged_queries[] = $this->query;
        }
    }

    private function insertUsingDatamap($table, $record) {
        $new_id = 'NEW' . uniqid('');
        $datamap = array(
            $table => array($new_id => $record)
        );

        /** @var $tce t3lib_TCEmain */
        $tce = t3lib_div::makeInstance('t3lib_TCEmain');
        $tce->start($datamap, null);
        $tce->process_datamap();

        if (count($tce->errorLog)) {
            // Set tx_newspaper::$query so the user can see what was attempted
            $this->query = $GLOBALS['TYPO3_DB']->INSERTquery($table, $record);
            throw new tx_newspaper_DBException(print_r($tce->errorLog, 1));
        }

        return $tce->substNEWwithIDs[$new_id];
    }

    private static function ensureTypo3DBObject() {
        if (!is_object($GLOBALS['TYPO3_DB'])) {
            $GLOBALS['TYPO3_DB'] = t3lib_div::makeInstance('t3lib_DB');
        }
    }

    private static function getRecordsFromResultSet($res) {
        $records = array();
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $records[] = $row;
        }
        return $records;
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
        @fclose($fp);
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

    /** @var tx_newspaper_DB */
    private static $instance = null;

    private $query = '';

    private $logged_queries = array();

    private $are_queries_logged = false;

    private $max_logged_queries = self::default_max_logged_queries;

}

