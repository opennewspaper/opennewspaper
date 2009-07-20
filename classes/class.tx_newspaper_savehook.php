<?php

class tx_newspaper_SaveHook {


	/// tceform hooks (well, those aren't really save hooks ...)

	function getSingleField_preProcess($table, $field, $row, $altName, $palette, $extra, $pal, $that) {
//t3lib_div::devlog('sh pre table', 'newspaper', 0, array($table, $field, $row, $altName, $palette, $extra, $pal, $_REQUEST));
		$this->checkCantUncheckIsArticlePageZoneType($table, $field, $row);
	}



	/// save hook: new and update

	function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, $that) {
//t3lib_div::devlog('sh pre enter', 'newspaper', 0, array($incomingFieldArray, $table, $id, $_REQUEST));
		$this->checkIfWorkflowStatusChanged($incomingFieldArray, $table, $id, $_REQUEST);
		$this->checkIfWorkflowCommentIsToBeStored($incomingFieldArray, $table, $id, $_REQUEST);
	}

	private function checkIfWorkflowStatusChanged($incomingFieldArray, $table, $id, $request) {
		if (!isset($request['workflow_status']) || !isset($request['workflow_status_ORG']) || !isset($request['workflow_comment']))
			return;
		if (isset($request['workflow_comment'])) {
			$action = tx_newspaper_BE::getWorkflowActionTitle($request['workflow_status'], $request['workflow_status_ORG']);
t3lib_div::devlog('action', 'newspaper', 0, $action);
		}
	}

	private function checkIfWorkflowCommentIsToBeStored(&$incomingFieldArray, $table, $id, $request) {
		if ($table != 'tx_newspaper_article')
			return;
		if (!isset($request['workflow_status']) || !isset($request['workflow_status_ORG']))
			return;
		if ($request['workflow_status'] == $request['workflow_status_ORG'])
			return; // status wasn't changed, so don't store value
		$incomingFieldArray['workflow_status'] = $request['workflow_status'];
/// \todo: log entry schreiben
/// uid, pid, tstamp, crdate (raus), cruser_id, table_name, table_uid, be_user (raus), action, comment
	}


	private function checkPageZoneWithIsArticleFlagAllowed($fieldArray, $table, $id) {
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
				die('Fatal error: Only one page zone type can have the "is article" flag set. You change was not saved.<br /><br /><a href="javascript:history.back();">Click here to retry</a>');
			}
		}
	}
		


	function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, $that) {
//t3lib_div::devlog('sh post enter', 'newspaper', 0, array($status, $table, $id, $fieldArray));

		/// add modifications user and time if  tx_newspaper_Article is updated
		$this->addModificationUserDataIfArticle($status, $table, $id, $fieldArray);

		/// check if publish_date is to be added
		$this->addPublishDateIfNotSet($status, $table, $id, &$fieldArray);

		/// check if an article list was changed for a section
		$this->checkArticleListChangedInSection($fieldArray, $table, $id);

		/// check if a page zone type with is_article flag set is allowed
		$this->checkPageZoneWithIsArticleFlagAllowed($fieldArray, $table, $id); 

		if (!tx_newspaper::isAbstractClass($table) && class_exists($table)) { ///<newspaper specification: table name = class name

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
t3lib_div::devlog('writes log', 'newspaper', 0, array($status, $table, $id, $fieldArray, $_REQUEST));
			}
			
		}
	}
	
	
	/// save hook: delete
	
	function processCmdmap_preProcess($command, $table, $id, $value, $that) {
//t3lib_div::devlog('command pre enter', 'newspaper', 0, array($command, $id, $value));

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
//t3lib_div::devlog('adbo after enter', 'newspaper', 0, array($status, $table, $id, $fieldArray)); // , $_REQUEST
//t3lib_div::devlog('adbo after new ids', 'newspaper', 0, $that->substNEWwithIDs);		
				
		/// If a new section has been created, create default article list
		$this->addDefaultArticleListIfNewSection($status, $table, $id, $fieldArray, $that);
		
		$this->writeRecordsIfNewExtraOnPageZone($status, $table, $id, $fieldArray, $that);
	}



	/// add modification user and modification time when updating tx_newspaper_article
	private function addModificationUserDataIfArticle($status, $table, $id, &$fieldArray) {
		if (strtolower($table) != 'tx_newspaper_article' || $status != 'update')
			return false;
			
		$fieldArray['modification_user'] = $GLOBALS['BE_USER']->user['uid'];
		$fieldArray['modification_time'] = time();
		return true; 

	}


	/// set publish_date when article changed from hidden=1 to hidden=0 and publish_date isn't set
	private function addPublishDateIfNotSet($status, $table, $id, &$fieldArray) {
/// \todo: timestart - alle kombinationen abfangen!!!
		if (strtolower($table) == 'tx_newspaper_article' && $fieldArray['hidden'] == 0 && !$fieldArray['publish_date']) {
debug($fieldArray);
			$fieldArray['publish_date'] = time(); // change publish_date
			return true;
		}
		return false; // publish_date remained unchanged
	}













	/// writes tx_newspaper_extra and tx_newspaper_pagezone_page_extras_mm records
/// \todo: explain in detail what's happening here!
	private function writeRecordsIfNewExtraOnPageZone($status, $table, $id, $fieldArray, $that) {
		if (tx_newspaper::isAbstractClass($table))
			return; // abstract class, nothing to do
	
		if ($status == 'new' and tx_newspaper::classImplementsInterface($table, 'tx_newspaper_ExtraIface')) {
//t3lib_div::devlog('save hook reached', 'newspaper', 0);			
			$pz_uid = intval(t3lib_div::_GP('new_extra_pz_uid'));
			$after_origin_uid = intval(t3lib_div::_GP('new_extra_after_origin_uid'));
			if (!$pz_uid) {
				die('Fatal error: Illegal value for pagezone uid: #' . $pz_uid . '. Please contact developers');
			}

			// get uid of new concrete extra (that was just stored)
			$concrete_extra_uid = intval($that->substNEWwithIDs[$id]);
			
			// create abstract record
			$abstract_uid = tx_newspaper_Extra::createExtraRecord($concrete_extra_uid, $table);

			// create pagezone (pagezone_page or article)
			$pz = tx_newspaper_PageZone_Factory::getInstance()->create(intval($pz_uid));

			$e = tx_newspaper_Extra_Factory::getInstance()->create($abstract_uid);
			$e->setAttribute('show_extra', 1);
			$e->setAttribute('is_inheritable', 1);


			$pz->insertExtraAfter($e, $after_origin_uid); // insert BEFORE setting the paragraph (so the paragraph can be inherited)
			
			if (isset($_REQUEST['paragraph']) && ($pz instanceof tx_newspaper_Article)) {
				// set paragraph la
				$pz->changeExtraParagraph($e, intval(t3lib_div::_GP('paragraph'))); // changeExtraParagraph() stores the extras, so no need to store after call this function call
			} else {
				$e->store(); // call store() only if changeExtraParagraph() wasn't called
			}

		}
	}


	private function addDefaultArticleListIfNewSection($status, $table, $id, $fieldArray, $that) {
		if ($status == 'new' && $table == 'tx_newspaper_section') {
			$section_uid = intval($that->substNEWwithIDs[$id]); // $id contains "NEWS...." id
			$al = new tx_newspaper_ArticleList_Auto(0, new tx_newspaper_Section($section_uid));
			$al->store();
/// \todo: $this->newSection($id, $fieldArray); /// copy placement ...
		} 
	}


	private function checkArticleListChangedInSection(array &$fieldArray, $table, $id) {
		if ($table != 'tx_newspaper_section') return; // no section processed, nothing to do
		if (!isset($fieldArray['articlelist'])) return; // articlelist wasn't changed, nothing to do

		if (!tx_newspaper::isAbstractClass($fieldArray['articlelist']) && class_exists($fieldArray['articlelist'])) {
			$al = new $fieldArray['articlelist']();
			if ($al instanceof tx_newspaper_articlelist) {
				// so articlelist was changed to another valid articlelist type
				$new_al = new $fieldArray['articlelist'](0, new tx_newspaper_Section(intval($id)));
				$new_al->store();
				// delete all other article lists assigned to this section
				tx_newspaper::updateRows(
					'tx_newspaper_articlelist',
					'section_id=' . $id . ' AND list_table<>"' . $new_al->getTable() . '"',
					array('deleted' => 1)
				);

 				$fieldArray['articlelist'] = $new_al->getAbstractUid(); // store uid of abstract article list
			}
		}
	}








	
	
	
	/// the checkbox is_article in pagezonetype can't be unchecked later!
	/** \param string $table table name in hook
	 *  \param string $field name of single field currently processed in hook 
	 *  \param array $row data to be written

	 *  \return void 
	 */
	private function checkCantUncheckIsArticlePageZoneType($table, $field, $row) {
		$obj = new tx_newspaper_PageZoneType();
		if ($table == $obj->getTable() && $field == 'is_article' && $row['is_article'] == 1) {
			t3lib_div::loadTCA($table); // Make sure to load full $TCA array for the table
			/// if field 'normalized_name' is filled, just display the value, but the value can't be edited
			unset($GLOBALS['TCA'][$table]['columns']['is_article']['config']['type']);
			$GLOBALS['TCA'][$table]['columns']['is_article']['config']['type'] = 'none';
		}
	}
	
	
	
	/// check if a UNIQUE normlized_name was already entered - if yes, display value as non-editable field
	/** \param string $table table name in hook
	 *  \param string $field name of single field currently processed in hook 
	 *  \param array $row data to be written
	 *  \param tx_newspaper_StoredObject object type to check
	 *  \return void 
	 */
	private function checkNormalizedNameUniqueField($table, $field, $row, tx_newspaper_StoredObject $obj) {
		if ($table == $obj->getTable() && $field == 'normalized_name' && $row['normalized_name'] != '') {
			t3lib_div::loadTCA($table); // Make sure to load full $TCA array for the table
			/// if field 'normalized_name' is filled, just display the value, but the value can't be edited
			unset($GLOBALS['TCA'][$table]['columns']['normalized_name']['config']['type']);
			$GLOBALS['TCA'][$table]['columns']['normalized_name']['config']['type'] = 'none';
		}
	}
	
	
	
}	

?>