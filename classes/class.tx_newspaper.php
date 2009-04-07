<?php

require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_extra_be.php');

#t3lib_div::devlog('class.tx_newspaper.php loaded', 'newspaper', 0);


/// Utility class which provides static functions. A namespace, so to speak.
class tx_newspaper  {

	/// add javascript (or other script parts) to extra form (basically containing an onunload script)
	/** \param $PA typo3 standard for userFunc
	  * \param $fobj typo3 standard for userFunc
	  * \return String html code to be placed in the html header <script ...></script>
	  */
	public function getCodeForBackend($PA, $fobj) {
#t3lib_div::devlog('tx_newspaper->getCodeForBackend', 'newspaper', 0);
		return tx_newspaper_ExtraBE::getJsForExtraField();
	}


	/// add Extra list to backend form
	/** \param $PA typo3 standard for userFunc
	  * \param $fobj typo3 standard for userFunc
	  * \return String html code: list of assiciated Extras
	  */
	function renderList($PA, $fobj) {
t3lib_div::devlog('tx_newspaper->renderList pa', 'newspaper', 0, $PA);

/// \to do: can/should articles be hard-coded here? Or: throw exception if table is not tx_newspaper_article
		// get table and uid of current record
		$current_record['table'] = $PA['table'];
		$current_record['uid'] = $PA['row']['uid'];

		return tx_newspaper_ExtraBE::renderList($current_record['table'], $current_record['uid']);

	}

	
	
	

	/// Execute a SELECT query, check the result, return zero or one record(s)
	/** \param $fields Fields to SELECT
	 *  \param $table Table to SELECT FROM
	 *  \param $where WHERE-clause (defaults to selecting all records)
	 *  \param $groupBy Fields to GROUP BY
	 *  \param $orderBy Fields to ORDER BY
	 *  \param $limit Maximum number of records to SELECT
	 *  \return The result of the query as associative array
	 */
	public static function selectZeroOrOneRows($fields, $table, $where = '1', 
											   $groupBy = '', $orderBy = '', $limit = '') {
		self::$query = $GLOBALS['TYPO3_DB']->SELECTquery(
			$fields, $table, $where, $groupBy, $orderBy, $limit);
		$res = $GLOBALS['TYPO3_DB']->sql_query(self::$query);
		
		if (!$res) {
        	throw new tx_newspaper_NoResException(self::$query);
        }
        
        return $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
	}

	/// Execute a SELECT query, check the result, return \em exactly one record
	/** \param $fields Fields to SELECT
	 *  \param $table Table to SELECT FROM
	 *  \param $where WHERE-clause (defaults to selecting all records)
	 *  \param $groupBy Fields to GROUP BY
	 *  \param $orderBy Fields to ORDER BY
	 *  \param $limit Maximum number of records to SELECT
	 *  \return The result of the query as associative array
	 */
	public static function selectOneRow($fields, $table, $where = '1',
										$groupBy = '', $orderBy = '', $limit = '') {
		self::$query = $GLOBALS['TYPO3_DB']->SELECTquery(
			$fields, $table, $where, $groupBy, $orderBy, $limit);
		$res = $GLOBALS['TYPO3_DB']->sql_query(self::$query);
		
		if (!$res) {
        	throw new tx_newspaper_NoResException(self::$query);
        }
        
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
        
		if (!$row) {
			throw new tx_newspaper_EmptyResultException(self::$query);
		}

		return $row;		
	}

	/// Execute a SELECT query, check the result, return all records
	/** \param $fields Fields to SELECT
	 *  \param $table Table to SELECT FROM
	 *  \param $where WHERE-clause (defaults to selecting all records)
	 *  \param $groupBy Fields to GROUP BY
	 *  \param $orderBy Fields to ORDER BY
	 *  \param $limit Maximum number of records to SELECT
	 *  \return The result of the query as 2-dimensional associative array
	 */
	public static function selectRows($fields, $table, $where = '1',
									  $groupBy = '', $orderBy = '', $limit = '') {
		self::$query = $GLOBALS['TYPO3_DB']->SELECTquery(
			$fields, $table, $where, $groupBy, $orderBy, $limit);

		$res = $GLOBALS['TYPO3_DB']->sql_query(self::$query);

		if ($res) {
	        $rows = array();
	        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
	        	$rows[] = $row;
			return $rows;
		} else throw new tx_newspaper_NoResException(self::$query);
	}
	
	/// Execute a SELECT query on M-M related tables
	/** Copied and adapted from t3lib_db::exec_SELECT_mm_query so that the 
	 *	SQL query is retained for debugging.
	 *	\param $select Field list for SELECT
	 *  \param $local_table Tablename, local table
	 *  \param $mm_table Tablename, relation table
	 *  \param $foreign_table Tablename, foreign table
	 *  \param $whereClause Optional additional WHERE clauses put in the end of
	 *  	   the query. NOTICE: You must escape values in this argument with
	 *  	   $this->fullQuoteStr() yourself! DO NOT PUT IN GROUP BY, ORDER BY
	 *  	   or LIMIT! You have to prepend 'AND ' to this parameter yourself!
	 *  \param $groupBy Optional GROUP BY field(s), if none, supply blank string.
	 *  \param $orderBy Optional ORDER BY field(s), if none, supply blank string.
	 *  \param $limit Optional LIMIT value ([begin,]max), if none, supply blank string.
	 *  \return The result of the query as 2-dimensional associative array
	 */
	public static function selectMMQuery($select, $local_table, $mm_table, $foreign_table,
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
		
		return tx_newspaper::selectRows(
			$select, $table, $mmWhere.' '.$whereClause,
			$groupBy, $orderBy, $limit
		);
	}

	/// inserts a record using T3 API
	/** \param $table SQL table to insert into
	 *  \param $row Data as key=>value pairs
	 *  \return uid of inserted record
	 */
	public static function insertRows($table, array $row) {
		if (true) {
			$new_id = 'NEW'.uniqid('');
			$datamap = array(
				$table => array(
					$new_id => $row
				)
			);
			$tce = t3lib_div::makeInstance('t3lib_TCEmain');
			$tce->start($datamap, null);
			$tce->process_datamap();
			if (count($tce->errorLog)){
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

	/// updates a record using T3 API
	/** \param $table SQL table to update
	 *  \param $where SQL WHERE condition (typically 'uid = ...')
	 *  \param $row Data as key=>value pairs
	 */
	public static function updateRows($table, $where, array $row) {
		self::$query = $GLOBALS['TYPO3_DB']->UPDATEquery($table, $where, $row);
		$res = $GLOBALS['TYPO3_DB']->sql_query(self::$query);

		if (!$res) {
        	throw new tx_newspaper_NoResException(self::$query);
        }
        
        return $GLOBALS['TYPO3_DB']->sql_insert_id();
        
	}

	/// deletes a record using T3 API
	/** \param $table SQL table to delete a record from
	 *  \param $where SQL WHERE condition (typically 'uid = ...')
	 *  \param $really If set, really delete records; else set deleted = 1
	 */
	public static function deleteRows($table, $where, $really = false) {
		if ($really) {
			self::$query = $GLOBALS['TYPO3_DB']->DELETEquery($table, $where);
		} else {
			self::$query = $GLOBALS['TYPO3_DB']->UPDATEquery($table, $where, array('deleted' => 1));
		}
		$res = $GLOBALS['TYPO3_DB']->sql_query(self::$query);

		if (!$res) {
        	throw new tx_newspaper_NoResException(self::$query);
        }
        
        return $GLOBALS['TYPO3_DB']->sql_affected_rows();
        
	}


	/// Returns a part of a WHERE clause which will filter out records with start/end times, deleted flag set, or hidden flag set (if hidden should be included used); switch for BE/FE is included 
	/** \param String $table name of db table to check
	 *  \param int [0|1] specifies if hidden records are to be included (ignored if in FE)
	 *  \return WHERE part of an SQL statement starting with AND
	 */
	static public function enableFields($table, $show_hidden = 1) {
		require_once(PATH_t3lib . '/class.t3lib_page.php');
	
		if (TYPO3_MODE == 'FE') {
			// use values defined in admPanel config (override given $show_hidden param)
			$show_hidden = ($table=='pages')? $GLOBALS['TSFE']->showHiddenPage : $GLOBALS['TSFE']->showHiddenRecords;
		} else if ($show_hidden != 0) {
			$show_hidden = 1; // make sure show_hidden param is correct
		}
	
		$p = t3lib_div::makeInstance('t3lib_pageSelect');
	
		return $p->enableFields($table, $show_hidden);
	}



	/// Get the tx_newspaper_Section object of the page currently displayed
	/** Currently, that means it returns the ressort record which lies on the
	 *  current Typo3 page. This implementation may change, but this function
	 *  is required to always return the correct tx_newspaper_Section.
	 * 
	 *  \return The tx_newspaper_Section object the plugin currently works on
	 */
	public static function getSection() {
		$section_uid = intval($GLOBALS['TSFE']->page['tx_newspaper_associated_section']);

        if (!$section_uid) {
        	throw new tx_newspaper_IllegalUsageException('No section associated with current page');
        }
		
		return new tx_newspaper_Section($section_uid);
	}
	
	
	/// Return the name of the SQL table \p $class resides in
	/** \param $class either object or a class name to find the SQL table for
	 *  \return The lower-cased class name of \p $class (= name of associated db table; newspaper convention)
	 */
	public static function getTable($class) {
		if (is_object($class)) {
			return strtolower(get_class($class));
		}
		return strtolower($class);
	}

	/// get all child classes (but child only, no grand children etc.)
	/** basically used to get concrete classes extending an abstract class
	 *  \param $class_name name of class to look for child classes
	 *  \return array list of child classes
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


	

	////////////////////////////////////////////////////////////////////////////
	
	/** SQL queries are stored as a static member variable, so they can be 
	 *  accessed for debugging from outside the function if a query does not  
	 *  return the desired result.
	 */ 
	static $query = ''; 
}

?>
