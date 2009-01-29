<?php

require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_extra_be.php');

#t3lib_div::devlog('class.tx_newspaper.php loaded', 'newspaper', 0);


// is this class still needed or can these two methods be moved to tx_newspaper_extra_be.php?
class tx_newspaper {

	/**
	 * add javascript (or other script parts) to extra form (basically containing an onunload script)
	 * \param $PA typo3 standard for userFunc
	 * \param $fobj typo3 standard for userFunc
	 * \return String html code to be placed in the html header <script ...></script>
	 */
	public function getCodeForBackend($PA, $fobj) {
#t3lib_div::devlog('tx_newspaper->getCodeForBackend', 'newspaper', 0);
		return tx_newspaper_ExtraBE::getJsForExtraField();
	}


	/**
	 * add Extra list to backend form
	 * \param $PA typo3 standard for userFunc
	 * \param $fobj typo3 standard for userFunc
	 * \return String html code to be placed in the html header <script ...></script>
	 */
	function renderList($PA, $fobj) {
#t3lib_div::devlog('tx_newspaper->renderList pa', 'newspaper', 0, $PA);

//TODO: can/should articles be hard-coded here?
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
	 */
	public static function selectRows($fields, $table, $where = '1',
										$groupBy = '', $orderBy = '', $limit = '') {
		self::$query = $GLOBALS['TYPO3_DB']->SELECTquery(
			$fields, $table, $where, $groupBy, $orderBy, $limit);
		$res = $GLOBALS['TYPO3_DB']->sql_query(self::$query);
		
		if ($res) {        
	        $rows = array();
	        while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
	        	$rows[] = $row;
	
			return $rows;
		}		
	}

	/** SQL queries are stored as a static member variable, so they can be 
	 *  accessed for debugging from outside the function if a query does not  
	 *  return the desired result.
	 */ 
	static $query = ''; 
}

?>
