<?php

require_once(BASEPATH.'/typo3conf/ext/newspaper/classes/class.tx_newspaper_extra_be.php');

#t3lib_div::devlog('class.tx_newspaper.php loaded', 'newspaper', 0);


/// \to do: is this class still needed or can these two methods be moved to tx_newspaper_extra_be.php?
class tx_newspaper implements tx_newspaper_InSysFolder {

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
	
	
	/// insysfodler interface
	/** \return String module name (for sysfolder)
	 */
	public static function getModuleName() {
		return 'newspaper'; // root
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
		/// \todo noresexception		
	}
	
	
	/// inserts a record using T3 API
	/** \param $table T3 table
	 *  \param $row fields and data 
	 *  \return uid of inserted record
	 */
/// \todo: rename to insertRow ???
	public static function insertRows($table, $row) {
		self::$query = $GLOBALS['TYPO3_DB']->INSERTquery($table, $row);
		$res = $GLOBALS['TYPO3_DB']->sql_query(self::$query);

		if (!$res) {
        	throw new tx_newspaper_NoResException(self::$query);
        }
        
        return $GLOBALS['TYPO3_DB']->sql_insert_id();
        
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
	
	/// Get the tx_newspaper_Page which is currently displayed
	/**
	 *	Find out which page type we're on (Section, Article, RSS, Comments, whatever)
	 *
	 *	If $_GET['art'] is set, it is the article page
	 *
	 *	Else if $_GET['type'] is set, it is the page corresponding to that type
	 *
	 *	Else it is the section overview page
	 *
	 *  \return The tx_newspaper_Page which is currently displayed
	 */ 
	public static function getPage(tx_newspaper_Section $section) {
		if (t3lib_div::_GP('art')) $cond = 'get_var = \'art\'';
		else if (t3lib_div::_GP('type')) 
			$cond = 'get_var = \'type\' AND get_value = '.intval(t3lib_div::_GP('type'));
		else $cond = 'NOT get_var';
		
		return new tx_newspaper_Page($section, $cond);
	}

	public static function getTable($object) {
		return strtolower(get_class($object));
	}	

	////////////////////////////////////////////////////////////////////////////
	
	/** SQL queries are stored as a static member variable, so they can be 
	 *  accessed for debugging from outside the function if a query does not  
	 *  return the desired result.
	 */ 
	static $query = ''; 
}

?>
