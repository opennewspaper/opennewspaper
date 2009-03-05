<?php

class user_savehook_newspaper {

	/// save hook: new and update

	function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, $that) {
#t3lib_div::devlog('sh post status', 'newspaper', 0, $status);
#t3lib_div::devlog('sh post table', 'newspaper', 0, $table);
#t3lib_div::devlog('sh post id', 'newspaper', 0, $id);
#t3lib_div::devlog('sh post fields', 'newspaper', 0, $fieldArray);

		/// If a new section has been created, copy its placement
		if ($status == 'new' && $table == tx_newspaper_Section::getTable()) {
			return $this->newSection($id, $fieldArray);
		} 

		/// check if a page zone type with is_article flag set is allowed
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
#t3lib_div::devlog('pzt: is_article', 'newspaper', 0, array('pid' => $pzt->getTable(), 'where' => $where, 'row' => $row));
			if (count($row) > 0) {
/// \to do: add to log file - but which?
				die('Fatal error: Only one page zone type can have the "is article" flag set. You change was not saved.<br /><br /><a href="javascript:history.back();">Click here to retry</a>');
			}
		}


		/// check if a newspaper record is saved and make sure it's stored in the appropriate sysfolder
		if (class_exists($table)) { ///<newspaper specification: table name = class name
			$np_obj = new $table();
			if (in_array("tx_newspaper_InSysFolder", class_implements($np_obj))) { 
				/// tx_newspaper_InSysFolder is implemented, so record is to be stored in a special sysfolder
				$sf = tx_newspaper_Sysfolder::getInstance();
				$pid = $sf->getPid($np_obj);
				$fieldArray['pid'] = $pid; // map pid to appropriate sysfolder
#t3lib_div::devlog('sh post fields modified', 'newspaper', 0, $fieldArray);
			}
		}
	}
	
	
	/// save hook: delete
	
	function processCmdmap_preProcess($command, $table, $id, $value, $that) {
#t3lib_div::devlog('command', 'newspaper', 0, $command);
#t3lib_div::devlog('id', 'newspaper', 0, $id);
#t3lib_div::devlog('value', 'newspaper', 0, $value);

		if ($table == 'tx_newspaper_pagetype' || $table == 'tx_newspaper_pagezonetype') {
			// it is not allowed to delete these records (with T3 means)
/// \to do: fully implement deleting these record types
/// \to do: add to log file - but which?
			die('Fatal error: It is not allowed to delete this record, so it was NOT deleted. If you still want to delete this record please contact the Typo3 developers.<br /><br /><a href="javascript:history.back();">Go back</a>');
		}
		
	}
	
	/// Stuff to do when a new section is created
	/** - pages, page zones and extras are copied from the parent section
	 *  - an automatic article list is created and associated with the section
	 */
	private function newSection($id, &$fieldArray) {
		t3lib_div::debug('user_savehook_newspaper::newSection('.$id.')');
		t3lib_div::debug($fieldArray);
		
		if ($fieldArray['inheritance_mode'] != '') $this->copyPagesFromParent();
		$this->generateArticleList();
	}

	/// Copy active pages and their content from parent section
	/** - copies the active pages from the parent section
	 *  - copies the page zones on those pages
	 *  - copies the Extras on those page zones
	 */	
	private function copyPagesFromParent() {
		throw new tx_newspaper_NotYetImplementedException();
	}

	/// Generate an automatically filled article list and link it to the section
	private function generateArticleList() {
		throw new tx_newspaper_NotYetImplementedException();
	}
	
}	

?>