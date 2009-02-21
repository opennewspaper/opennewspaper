<?php

class user_savehook_newspaper {

	function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, $that) {
t3lib_div::devlog('sh post status', 'newspaper', 0, $status);
t3lib_div::devlog('sh post table', 'newspaper', 0, $table);
t3lib_div::devlog('sh post id', 'newspaper', 0, $id);
t3lib_div::devlog('sh post fields', 'newspaper', 0, $fieldArray);

/// \todo: remove by-pass after article class and table have the same name
$class_bypass = ($table == 'tx_newspaper_article')? 'tx_newspaper_ArticleImpl' : $table;


		/// check if a page zone type with is_article fals set is allowed
		$pzt = new tx_newspaper_PageZoneType(); 
		if  ($table == $pzt->getTable() && 
			isset($fieldArray['is_article']) && 
			$fieldArray['is_article'] == 1 &&
			($status = 'new' || $status == 'update')
		) {
			/// make sure no other page zone type with is_article flag set exists
			$sf = tx_newspaper_Sysfolder::getInstance();
			$pid = $sf->getPid($pzt);
			$where = 'pid=' . $pid . ' AND deleted=0 AND is_article=1';
			if ($status != 'new') { /// no uid if new record (NEW49b018c614878)
				$where .= ' AND uid !=' . $id; 				
			}
			$row = tx_newspaper::selectRows(
				'uid, name',
				$pzt->getTable(),
				$where
			);
t3lib_div::devlog('pzt: is_article', 'newspaper', 0, array('pid' => $pzt->getTable(), 'where' => $where, 'row' => $row));
			if (count($row) > 0) {
				die('Fatal error: Only one page zone type can have the "is article" flag set. You change was not saved.<br /><br /><a href="javascript:history.back();">Click here to retry</a>');
			}
		}


		/// check if a newspaper record is saved and make sure it's stored in the appropriate sysfolder
		if (class_exists($class_bypass)) { ///<newspaper specification: table name = class name
			$np_obj = new $class_bypass();
			if (in_array("tx_newspaper_InSysFolder", class_implements($np_obj))) { 
				/// tx_newspaper_InSysFolder is implemented, so record is to be stored in a special sysfolder
				$sf = tx_newspaper_Sysfolder::getInstance();
				$pid = $sf->getPid($np_obj);
				$fieldArray['pid'] = $pid; // map pid to appropriate sysfolder
#t3lib_div::devlog('sh post fields modified', 'newspaper', 0, $fieldArray);
			}
		}
	}
	
}	

?>