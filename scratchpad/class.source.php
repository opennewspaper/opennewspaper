<?php
/*
 * Created on Aug 12, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

interface Source {
	function getFields($lowLevelFields, $key);
	function getArray($verknuepfungstabelle, $key);
}

class DBSource implements Source {
	
	function getFields($lowLevelFields, $key) {
		$res = $_GLOBALS['TYPO3_DB']->exec_SELECTquery($lowLevelFields, $this->table, 
			'uid = '.$key);	/// ! DANGER HERE ! The key needn't be named 'uid'. maybe it's a property of the source?
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		return $row;
	}
	
	function getArray($verknuepfungstabelle, $key) {
		return mach_mir_ne_select_query_mit_m_zu_n_verknuepfung();
	}
	
	private $table;
	
}


?>
