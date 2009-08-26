<?php

class tx_newspaper_SaveHook {






/// tceform hooks (well, those aren't really save hooks ...)

	function getSingleField_preProcess($table, $field, $row, $altName, $palette, $extra, $pal, $that) {
//t3lib_div::devlog('sh pre table', 'newspaper', 0, array($table, $field, $row, $altName, $palette, $extra, $pal, $_REQUEST));
		$this->checkCantUncheckIsArticlePageZoneType($table, $field, $row);
	}

	/// the checkbox is_article in pagezonetype can't be unchecked later!
	/** \param string $table table name in hook
	 *  \param string $field name of single field currently processed in hook 
	 *  \param array $row data to be written
	 *  \return void 
	 */
	private function checkCantUncheckIsArticlePageZoneType($table, $field, $row) {
		if ($table == 'tx_newspaper_pagezonetype' && $field == 'is_article' && $row['is_article'] == 1) {
			t3lib_div::loadTCA($table); // Make sure full $TCA array for table 'tx_newspaper_pagezonetype' is loaded
			// once the checkbox is checked, it can't be undone!
			unset($GLOBALS['TCA'][$table]['columns']['is_article']['config']['type']);
			$GLOBALS['TCA'][$table]['columns']['is_article']['config']['type'] = 'none';
		}
	}










/// save hook: new and update

	function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, $that) {
//t3lib_div::devlog('sh pre enter', 'newspaper', 0, array($incomingFieldArray, $table, $id, $_REQUEST));

// \todo: move to writesLog check ???
		$this->checkIfWorkflowStatusChanged($incomingFieldArray, $table, $id, $_REQUEST);
	}

	private function checkIfWorkflowStatusChanged(&$incomingFieldArray, $table, $id, $request) {
//t3lib_div::devlog('wf stat', 'newspaper', 0, array($incomingFieldArray, $table, $id, $request));
		if (isset($request['hidden_status']) && $request['hidden_status'] != $request['data'][$table][$id]['hidden']) {
			$incomingFieldArray['hidden'] = $request['hidden_status'];
		}
		if (!isset($request['workflow_status']) || !isset($request['workflow_status_ORG']))
			return; // value not set, so can't decide if the status changed 
		if ($request['workflow_status'] == $request['workflow_status_ORG'])
			return; // status wasn't changed, so don't store value
		$incomingFieldArray['workflow_status'] = $request['workflow_status']; // change workflow status
	}




	/** \todo some documentation would be nice ;-) */
	function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, $that) {
		global $LANG;
//t3lib_div::devlog('sh post enter', 'newspaper', 0, array($status, $table, $id, $fieldArray));

		/// add modifications user and time if  tx_newspaper_Article is updated
		$this->addModificationUserDataIfArticle($status, $table, $id, $fieldArray);

		/// check if publish_date is to be added
		$this->addPublishDateIfNotSet($status, $table, $id, &$fieldArray);

		/// check if an article list was changed for a section
		$this->checkArticleListChangedInSection($fieldArray, $table, $id);

		/// check if a page zone type with is_article flag set is allowed
		$this->checkPageZoneWithIsArticleFlagAllowed($fieldArray, $table, $id); 

		/// handle uploads of tx_newspaper_Extra_Image
		$this->handleImageUploads($status, $table, $id, $fieldArray, $that);
		
		if (!tx_newspaper::isAbstractClass($table) && class_exists($table)) { ///<newspaper specification: table name = class name

			$np_obj = new $table();

			/// check if a newspaper record is saved and make sure it's stored in the appropriate sysfolder
			if (in_array("tx_newspaper_StoredObject", class_implements($np_obj))) { 
/// \todo: move to function
				/// tx_newspaper_StoredObject is implemented, so record is to be stored in a special sysfolder
				$sf = tx_newspaper_Sysfolder::getInstance();
				$pid = $sf->getPid($np_obj);
				$fieldArray['pid'] = $pid; // map pid to appropriate sysfolder
#t3lib_div::devlog('sh post fields modified', 'newspaper', 0, $fieldArray);
			}

			/// check if a newspaper record action should be logged
			if (in_array("tx_newspaper_WritesLog", class_implements($np_obj))) {
				
/// \todo: move to function - move to log class?

				/// IMPORTANT: checkIfWorkflowStatusChanged() has run, so $fieldArray has been modified already
				
				/// check if auto log entry for hiding/publishing newspaper record should be written
				if (isset($fieldArray['hidden'])) {
					if ($table == 'tx_newspaper_article') {
						$action = $fieldArray['hidden']? $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:log_article_hidden', false) : $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:log_article_published', false);
					} else {
						$action = $fieldArray['hidden']? $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:log_record_hidden', false) : $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:log_record_published', false);
					}
					tx_newspaper::insertRows('tx_newspaper_log', array(
						'pid' => $fieldArray['pid'],
						'tstamp' => time(),
						'crdate' => time(), /// \todo: remove this field from table - as log entries aren't updated the time equals tstamp
						'cruser_id' => -1, // \todo: $GLOBALS['BE_USER']->user['uid'] not available, other way to store be_user needed
						'table_name' => $table, 
						'table_uid' => $id,
						'action' => $action,
						'comment' => ''
					));
				}
				
				/// check if auto log entry for change of workflow status should be written (article only)
				if ($table == 'tx_newspaper_article' & isset($fieldArray['workflow_status']) && isset($_REQUEST['workflow_status_ORG'])) {
					tx_newspaper::insertRows('tx_newspaper_log', array(
						'pid' => $fieldArray['pid'],
						'tstamp' => time(),
						'crdate' => time(), /// \todo: remove this field from table - as log entries aren't updated the time equals tstamp
						'cruser_id' => -1, // \todo: $GLOBALS['BE_USER']->user['uid'] not available, other way to store be_user needed
						'table_name' => $table, 
						'table_uid' => $id,
						'action' => tx_newspaper_BE::getWorkflowStatusActionTitle(intval($fieldArray['workflow_status']), intval($_REQUEST['workflow_status_ORG'])),
						'comment' => ''
					));
				}
				
				/// check if manual comment should be written (this log record should always be written last)
				if (isset($_REQUEST['workflow_comment'])) {
					tx_newspaper::insertRows('tx_newspaper_log', array(
						'pid' => $fieldArray['pid'],
						'tstamp' => time(),
						'crdate' => time(), /// \todo: remove this field from table - as log entries aren't updated the time equals tstamp
						'cruser_id' => -1, // \todo: $GLOBALS['BE_USER']->user['uid'] not available, other way to store be_user needed
						'table_name' => $table, 
						'table_uid' => $id,
						'action' => $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:log_user_entry', false),
						'comment' => $_REQUEST['workflow_comment']
					));
				}

			}
		}
	}

	/// add modification user and modification time when updating tx_newspaper_article
	private function addModificationUserDataIfArticle($status, $table, $id, &$fieldArray) {
		if (strtolower($table) != 'tx_newspaper_article' || $status != 'update')
			return false;
		$fieldArray['modification_user'] = $GLOBALS['BE_USER']->user['uid'];
		return true; 
	}

	/// set publish_date when article changed from hidden=1 to hidden=0 and publish_date isn't set
	private function addPublishDateIfNotSet($status, $table, $id, &$fieldArray) {
/// \todo: timestart - alle kombinationen abfangen!!!
		if (strtolower($table) == 'tx_newspaper_article' && $fieldArray['hidden'] == 0 && !$fieldArray['publish_date']) {
//debug($fieldArray);
			$fieldArray['publish_date'] = time(); // change publish_date
			return true;
		}
		return false; // publish_date remained unchanged
	}

	private function checkArticleListChangedInSection(array &$fieldArray, $table, $id) {
		if ($table != 'tx_newspaper_section') return; // no section processed, nothing to do
		if (!isset($fieldArray['articlelist'])) return; // articlelist wasn't changed, nothing to do
//t3lib_div::devlog('al1 fiealdArray[al]', 'np', 0, array($fieldArray, $table, $id));
		if (!tx_newspaper::isAbstractClass($fieldArray['articlelist']) && class_exists($fieldArray['articlelist'])) {
			$al = new $fieldArray['articlelist'](); // create new article list
			if ($al instanceof tx_newspaper_articlelist) {
//t3lib_div::devlog('al 2 instanceof al', 'np', 0);

				// "delete" (= set deleted flag) all abstract article lists assigned to this section, before writing the new one
				tx_newspaper::updateRows(
					'tx_newspaper_articlelist',
					'section_id=' . $id,
					array('deleted' => 1)
				);

				// articlelist was changed to another valid articlelist type, so store new abstract article list
				$new_al = new $fieldArray['articlelist'](0, new tx_newspaper_Section(intval($id)));
				$new_al->setAttribute('crdate', time());
				$new_al->setAttribute('cruser_id', $GLOBALS['BE_USER']->user['uid']);
				$new_al->store();
 				$fieldArray['articlelist'] = $new_al->getAbstractUid(); // store uid of abstract article list; will be stored in section

			}
		}
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

	private function handleImageUploads($status, $table, $id, &$fieldArray, $that) {
		tx_newspaper_Extra_Image::processDatamap_postProcessFieldArray($status, $table, $id, $fieldArray, $that);
	}




/// save hook: delete
	
	function processCmdmap_preProcess($command, $table, $id, $value, $that) {
//t3lib_div::devlog('command pre enter', 'newspaper', 0, array($command, $id, $value));

		if ($command == 'delete') {
			$this->checkIfArticletypeCanBeDeleted($table, $id);
			$this->checkIfSectionCanBeDeleted($table, $id);
			$this->checkIfPageTypeCanBeDeleted($table, $id);
			$this->checkIfPageZoneTypeCanBeDeleted($table, $id);
/// \todo: check if articles are assigned to section
/// \todo: check if pages are assigned to section
		}
	}

	private function checkIfSectionCanBeDeleted($table, $id) {
		if ($table == 'tx_newspaper_section') {
			$id = intval($id);
			
			// look for child sections
			$s = new tx_newspaper_section($id);
			$children = $s->getChildSections();
			if (sizeof($children) > 0) {
				$content = 'This section can\'t be deleted, because of existing child sections:<br />';
				for ($i = 0; $i < sizeof($children); $i++) {
					$content .= '- ' . $children[$i]->getAttribute('section_name') . '<br /';
				}	
				$content .= '<br /><br /><a href="javascript:history.back();">Go back</a>';
				die($content);
			}
			
			// look for pages assigned to this section
			$pages = $s->getActivePages();
			if (sizeof($pages) > 0) {
				$content = 'This section can\'t be deleted, because assigned pages are existing:<br />';
				for ($i = 0; $i < sizeof($pages); $i++) {
					$content .= '- ' . $pages[$i]->getPageType()->getAttribute('type_name') . '<br />';
				}	
				$content .= '<br /><br /><a href="javascript:history.back();">Go back</a>';
				die($content);
			}			 
			
			// look for articles assigned to section
			$articles = $s->getArticles(5);
			if (sizeof($articles) > 0) {
				$content = 'This section can\'t be deleted, because assigned articles are existing:<br />';
				for ($i = 0; $i < sizeof($articles); $i++) {
					$content .= '- ' . $articles[$i]->getDescription() . '<br />';
				}	
				$content .= '<br /><br /><a href="javascript:history.back();">Go back</a>';
				die($content);
			}				
			
		}
	}

	private function  checkIfArticletypeCanBeDeleted($table, $id) {
		if ($table == 'tx_newspaper_articletype') {
			$id = intval($id);
			$list = tx_newspaper_Article::listArticlesWithArticletype(new tx_newspaper_ArticleType($id), 5);
			if (sizeof($list) > 0) {
				/// assigned articles found, so this article type can't be deleted
				$content = 'This article type can\'t be deleted, because at least one article is using this article type. Find examples below (list might be much longer)<br /><br />';
				for ($i = 0; $i < sizeof($list); $i++) {
/// \todo: try catch for getAttribute calls?
					$content .= ($i+1) . '. ' . $list[$i]->getAttribute('kicker') . ': ' . $list[$i]->getAttribute('title') . ' (#'. $list[$i]->getAttribute('uid') . ')<br />';  
				}
				$content .= '<br /><br /><a href="javascript:history.back();">Go back</a>';
				die($content);
			}
		}
	}

	private function checkIfPageTypeCanBeDeleted($table, $id) {
		if ($table == 'tx_newspaper_pagetype') {
			$id = intval($id);
			$list = tx_newspaper_Page::listPagesWithPageType(new tx_newspaper_PageType($id, 5));
			if (sizeof($list) > 0) {
				/// pages using this page type are assigned to sections, so this page type can't be deleted
				$content = 'This page type can\'t be deleted, because at least one section is using this page type. Find examples below (list might be much longer)<br /><br />';
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
	}

	private function checkIfPageZoneTypeCanBeDeleted($table, $id) {
		if ($table == 'tx_newspaper_pagezonetype') {
			$list = tx_newspaper_Page::listPagesWithPageZoneType(new tx_newspaper_PageZoneType($id), 5); // try to get 5 pages with the pagezone type assigned  
			if (sizeof($list) > 0) {
				/// pagezones using this pagezone type are assigned to pages, so this pagezone type can't be deleted
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
	}




	function processDatamap_afterDatabaseOperations($status, $table, $id, &$fieldArray, $that) {
//t3lib_div::devlog('adbo after enter', 'newspaper', 0, array($status, $table, $id, $fieldArray)); // , $_REQUEST
//t3lib_div::devlog('adbo after new ids', 'newspaper', 0, $that->substNEWwithIDs);		
				
		/// If a new section has been created ...
		$this->addDefaultArticleListIfNewSection($status, $table, $id, $fieldArray, $that);
		$this->copyPlacementIfNewSection($status, $table, $id, $fieldArray, $that);
		
		/// if a new extra was placed on a pagezone, write abstract record
		$this->writeRecordsIfNewExtraOnPageZone($status, $table, $id, $fieldArray, $that);
	}

	private function addDefaultArticleListIfNewSection($status, $table, $id, $fieldArray, $that) {
		if ($status == 'new' && $table == 'tx_newspaper_section') {
			$section_uid = intval($that->substNEWwithIDs[$id]); // $id contains "NEWS...." id
			/// \todo make default article list configurable
			$al = new tx_newspaper_ArticleList_Auto(0, new tx_newspaper_Section($section_uid));
			$al->setAttribute('crdate', time());
			$al->setAttribute('cruser_id', $GLOBALS['BE_USER']->user['uid']);
			$al->store();
		} 
	}
	private function copyPlacementIfNewSection($status, $table, $id, $fieldArray, $that) {
		if ($status == 'new' && $table == 'tx_newspaper_section') {
			$section_uid = intval($that->substNEWwithIDs[$id]); // $id contains "NEWS...." id
/// \todo: $this->newSection($id, $fieldArray); /// copy placement ...
		} 
	}

	/// writes tx_newspaper_extra and tx_newspaper_pagezone_page_extras_mm records
/// \todo: explain in detail what's happening here!
	private function writeRecordsIfNewExtraOnPageZone($status, $table, $id, $fieldArray, $that) {
		if (tx_newspaper::isAbstractClass($table))
			return; // abstract class, nothing to do
	
		// exclude new articles - articles are extras but shouldn't be treated like extras here!
		if ($status == 'new' && $table != 'tx_newspaper_article' && tx_newspaper::classImplementsInterface($table, 'tx_newspaper_ExtraIface')) {
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
				// set paragraph
				$pz->changeExtraParagraph($e, intval(t3lib_div::_GP('paragraph'))); // changeExtraParagraph() stores the extras, so no need to store after call this function call
			} else {
				$e->store(); // call store() only if changeExtraParagraph() wasn't called (see above)
			}

		}
	}

	
	
	/// check if a UNIQUE normlized_name was already entered - if yes, display value as non-editable field
	/** \param string $table table name in hook
	 *  \param string $field name of single field currently processed in hook 
	 *  \param array $row data to be written
	 *  \param tx_newspaper_StoredObject object type to check
	 *  \return void 
	 */
/*
\todo: still needed?
	private function checkNormalizedNameUniqueField($table, $field, $row, tx_newspaper_StoredObject $obj) {
		if ($table == $obj->getTable() && $field == 'normalized_name' && $row['normalized_name'] != '') {
			t3lib_div::loadTCA($table); // Make sure to load full $TCA array for the table
			/// if field 'normalized_name' is filled, just display the value, but the value can't be edited
			unset($GLOBALS['TCA'][$table]['columns']['normalized_name']['config']['type']);
			$GLOBALS['TCA'][$table]['columns']['normalized_name']['config']['type'] = 'none';
		}
	}
*/
	
	
	
}	

?>