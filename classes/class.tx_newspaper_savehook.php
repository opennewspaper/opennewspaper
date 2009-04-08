<?php

class tx_newspaper_SaveHook {


	/// tceform hooks (well, those aren't really save hooks ...)

	function getSingleField_preProcess($table, $field, $row, $altName, $palette, $extra, $pal, $that) {
#t3lib_div::devlog('th pre table', 'newspaper', 0, $table);
#t3lib_div::devlog('th pre field', 'newspaper', 0, $field);
#t3lib_div::devlog('th pre row', 'newspaper', 0, $row);
#t3lib_div::devlog('th pre altName', 'newspaper', 0, $altName);
#t3lib_div::devlog('th pre palette', 'newspaper', 0, $palette);
#t3lib_div::devlog('th pre extra', 'newspaper', 0, $extra);
#t3lib_div::devlog('th pre pal', 'newspaper', 0, $pal);
		/// make sure field "normalized_name" can't be modified if data is present already
		$this->checkNormalizedNameField($table, $field, $row, new tx_newspaper_Articletype());
		$this->checkNormalizedNameField($table, $field, $row, new tx_newspaper_PageType());
		$this->checkNormalizedNameField($table, $field, $row, new tx_newspaper_PageZoneType());
	}



	/// save hook: new and update

	function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, $that) {
#t3lib_div::devlog('sh post status', 'newspaper', 0, $status);
#t3lib_div::devlog('sh post table', 'newspaper', 0, $table);
#t3lib_div::devlog('sh post id', 'newspaper', 0, $id);
#t3lib_div::devlog('sh post fields', 'newspaper', 0, $fieldArray);

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
				'uid, type_name',
				$pzt->getTable(),
				$where
			);
#t3lib_div::devlog('pzt: is_article', 'newspaper', 0, array('pid' => $pzt->getTable(), 'where' => $where, 'row' => $row));
			if (count($row) > 0) {
/// \to do: add to log file - but which?
				die('Fatal error: Only one page zone type can have the "is article" flag set. You change was not saved.<br /><br /><a href="javascript:history.back();">Click here to retry</a>');
			}
		}



		$abstract = false;
		if (class_exists($table)) {
			$tmp = new ReflectionClass();
			$abstract = $tmp->isAbstract();
		}
		
		if (!$abstract && class_exists($table)) { ///<newspaper specification: table name = class name

			$np_obj = new $table();

			/// check if a newspaper record is saved and make sure it's stored in the appropriate sysfolder
			if (in_array("tx_newspaper_StoredObject", class_implements($np_obj))) { 
				/// tx_newspaper_StoredObject is implemented, so record is to be stored in a special sysfolder
				$sf = tx_newspaper_Sysfolder::getInstance();
				$pid = $sf->getPid($np_obj);
				$fieldArray['pid'] = $pid; // map pid to appropriate sysfolder
#t3lib_div::devlog('sh post fields modified', 'newspaper', 0, $fieldArray);
			}

			/// check if a newspaper record action should be logged
			if (in_array("tx_newspaper_WritesLog", class_implements($np_obj))) {
#t3lib_div::debug('log ...');
#t3lib_div::debug($status);
#t3lib_div::debug($table);
#t3lib_div::debug($id);
#t3lib_div::debug($fieldArray);
			}
			
		}
	}
	
	
	/// save hook: delete
	
	function processCmdmap_preProcess($command, $table, $id, $value, $that) {
#t3lib_div::devlog('command', 'newspaper', 0, $command);
#t3lib_div::devlog('id', 'newspaper', 0, $id);
#t3lib_div::devlog('value', 'newspaper', 0, $value);

		/// check if it is allowed to delete an article type
		if ($command == 'delete' && $table == 'tx_newspaper_articletype') {
			$list = tx_newspaper_Article::listArticlesWithArticletype(new tx_newspaper_ArticleType($id), 3);
			if (sizeof($list) > 0) {
				/// assigned articles found, so this article type can't be deleted
				$content = 'This article type can\'t be deleted, because at least one article is using this article type. Find examples below (list might be much longer)<br /><br />';
				for ($i = 0; $i < sizeof($list); $i++) {
					$content .= ($i+1) . '. ' . $list[$i]->getAttribute('kicker') . ': ' . $list[$i]->getAttribute('title') . ' (#'. $list[$i]->getAttribute('uid') . ')<br />';  
				}
				$content .= '<br /><br /><a href="javascript:history.back();">Go back</a>';
				die($content);
			}
		}


		/// check if it is allowed to delete an page type
		if ($command == 'delete' && $table == 'tx_newspaper_pagetype') {
			$list = tx_newspaper_Page::listPagesWithPageType(new tx_newspaper_PageType($id, 3));
			if (sizeof($list) > 0) {
				/// assigned articles found, so this article type can't be deleted
				$content = 'This page type can\'t be deleted, because at least one page is using this page type. Find examples below (list might be much longer)<br /><br />';
				for ($i = 0; $i < sizeof($list); $i++) {
					$content .= ($i+1) . '. Section <i>';
					$tmp_section = new tx_newspaper_Section(intval($list[$i]->getAttribute('section')));
					$content .= $tmp_section->getAttribute('section_name');
					$content .= '</i><br />';  
				}
				$content .= '<br /><br /><a href="javascript:history.back();">Go back</a>';
				die($content);
			}
		}
		
		
		/// check if it is allowed to delete an page zone type
		if ($command == 'delete' && $table == 'tx_newspaper_pagezonetype') {
			$list = tx_newspaper_Page::listPagesWithPageZoneType(new tx_newspaper_PageZoneType($id, 3));
			if (sizeof($list) > 0) {
				/// assigned articles found, so this article type can't be deleted
				$content = 'This page zone type can\'t be deleted, because at least one page is using this page zone type. Find examples below (list might be much longer)<br /><br />';
				for ($i = 0; $i < sizeof($list); $i++) {
					$content .= ($i+1) . '. Section <i>';
					$tmp_section = new tx_newspaper_Section(intval($list[$i]->getAttribute('section')));
					$content .= $tmp_section->getAttribute('section_name');
					$content .= '</i> on Page <i>';
					$tmp_pt = new tx_newspaper_PageType(intval($list[$i]->getAttribute('pagetype_id')));
					$content .= $tmp_pt->getAttribute('type_name'); 
					$content .= '</i><br />';  
				}
				$content .= '<br /><br /><a href="javascript:history.back();">Go back</a>';
				die($content);
			}
		}


/// \todo: check if articles are assigned to section
/// \todo: check if pages are assigned to section
		
		
		
	}
	
	function processDatamap_afterDatabaseOperations($status, $table, $id, &$fieldArray, $that) {
		/// If a new section has been created, copy its placement
		if ($status == 'new' && $table == 'tx_newspaper_section') {
#			return $this->newSection($id, $fieldArray);
		} 
	}

	/// Stuff to do when a new section is created
	/** - pages, page zones and extras are copied from the parent section
	 *  - an automatic article list is created and associated with the section
	 * 
	 *  \param $id The UID assigned to the new record by Typo3 - usually NEW....
	 *  \param $fieldarray The data which have already been written to the new record
	 */
	private function newSection($id, array $fieldArray) {
		
		$section = new tx_newspaper_Section($this->getSectionID($fieldArray));
		t3lib_div::debug($section);

		if ($fieldArray['inheritance_mode'] != 'dont_inherit') 
			$this->copyPagesFromParent($section);

		$this->generateArticleList($section);
	}

	/// Determine the UID of a newly written section
	/** Typo3 apparently provides no reliable way to determine the UID of the 
	 *  record which has just been written to the 
	 *  processDatamap_afterDatabaseOperations() hook. Therefore, the only way
	 *  to find the identity of the new record is to search for the written data
	 *  in the DB.
	 * 
	 *  \param $fieldarray The data written to DB
	 */
	private function getSectionID(array $fieldArray) {
		$where = 1;
		foreach ($fieldArray as $key => $value) {
			$where .= " AND $key = '$value'";
		}
		$row = tx_newspaper::selectOneRow('uid', 'tx_newspaper_section', $where);
		return intval($row['uid']);		
	}
	
	/// Copy active pages and their content from parent section
	/** - copies the active pages from the parent section
	 *  - copies the page zones on those pages
	 *  - copies the Extras on those page zones
	 */	
	private function copyPagesFromParent(tx_newspaper_Section $section) {
		$parent = $section->getParentSection();

		foreach ($parent->getSubPages() as $page) {
			/// clone page, set parent section to new section and store it
			$new_page = clone $page;
			$new_page->setAttribute('section', $section->getAttribute('uid'));
			$new_page->store();
		}
	}

	/// Generate an automatically filled article list and link it to the section
	private function generateArticleList(tx_newspaper_Section $section) {
		throw new tx_newspaper_NotYetImplementedException();
	}
	
	
	
	/// check if a normlized_name was already entered - if yes, display value as non-editable field
	/** \param string $table table name in hook
	 *  \param string $field name of single field currently processed in hook 
	 *  \param array $row data to be written
	 *  \return void 
	 */
	private function checkNormalizedNameField($table, $field, $row, tx_newspaper_StoredObject $obj) {
		if ($table == $obj->getTable() && $field == 'normalized_name' && $row['normalized_name'] != '') {
			t3lib_div::loadTCA($table); // Make sure to load full $TCA array for the table
			/// if field 'normalized_name' is filled, just display the value, but the value can't be edited
			unset($GLOBALS['TCA'][$table]['columns']['normalized_name']['config']['type']);
			$GLOBALS['TCA'][$table]['columns']['normalized_name']['config']['type'] = 'none';
		}
	}
	
	
	
}	

?>