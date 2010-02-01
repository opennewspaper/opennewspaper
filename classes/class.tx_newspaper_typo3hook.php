<?php



/// newspaper configuration; added here because this file is included when accssing hooks even if the newspaper framework is NOT available

// replace element browser (EB) with article browser; array of fields in
//$GLOBALS['newspaper']['tx_newspaper_article']['replaceEBwithArticleBrowser'][name_of_db_table] = array(field_list) 
$GLOBALS['newspaper']['replaceEBwithArticleBrowser']['tx_newspaper_article'] = array('related'); // fields in articles
$GLOBALS['newspaper']['replaceEBwithArticleBrowser']['tx_newspaper_articlelist_manual'] = array('articles');
$GLOBALS['newspaper']['replaceEBwithArticleBrowser']['tx_newspaper_extra_combolinkbox'] = array('manually_selected_articles'); // \todo: replace with mod7 be (see #609)
/** \todo
 * set newspaper configuration using framework?
 * example: tx_newspaper::setConfReplaceElementBrowserWithArticleBrowser([table], [field]);
 */








require_once(PATH_t3lib . 'interfaces/interface.t3lib_localrecordlistgettablehook.php');


class tx_newspaper_Typo3Hook implements t3lib_localRecordListGetTableHook {

	/// List module hook - determines which records are hidden in list view
	public function getDBlistQuery($table, $pageId, &$additionalWhereClause, &$selectedFieldsList, &$parentObject) {
		
		// \todo: move to tx_np_article
		if (strtolower($table) == 'tx_newspaper_article') {
			// hide default articles in list module, only concrete article are visible in list module
			$additionalWhereClause .= ' AND is_template=0';
		}
		
	}






	/// TCEForm hooks
	function getSingleField_preProcess($table, $field, $row, $altName, $palette, $extra, $pal, $that) {
//t3lib_div::devlog('getSingleField_preProcess() hook', 'newspaper', 0, array('table' => $table, 'field' => $field, 'row' => '$row, 'altName' => $altName, 'palette' => $palette, 'extra' => $extra, 'pal' => $pal, '_REQUEST' => $_REQUEST));
		$this->checkCantUncheckIsArticlePageZoneType($table, $field, $row);
	}

	/// the checkbox is_article in pagezonetype can't be unchecked once it was set
	/** \param $table table name in hook
	 *  \param $field name of single field currently processed in hook 
	 *  \param $row data to be written
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


	function getSingleField_postProcess($table, $field, $row, &$out, $PA, $that) {
//if ($field == 'related') t3lib_div::devlog('getSingleField_postProcess() hook', 'newspaper', 0, array('table' => $table, 'field' => $field, 'row' => $row, 'out' => $out, 'PA' => $PA));

		// replace element browser (EB) with tx_newspaper article browser
		if ($this->replaceEbWithArticleBrowser($table, $field)) {
			// add table and field name to js function name
			// \todo better solution: make sure that setFormValueOpenBrowser[newspaper]() is added once only for ALL occurances ...
			$js = '<script type="text/javascript">
function setFormValueOpenBrowser_' . $table . '_' . $field . '(mode,params,form_table,form_field,form_uid) {
  var url = "' . tx_newspaper::getAbsolutePath() .  'typo3conf/ext/newspaper/mod2/index.php?mode="+mode+"&bparams="+params+"&form_table="+form_table+"&form_field="+form_field+"&form_uid="+form_uid;
  browserWin = window.open(url,"Typo3WinBrowser","height=350,width="+(mode=="db"?650:600)+",status=0,menubar=0,resizable=1,scrollbars=1");
  browserWin.focus();
}
</script>';
			// replace em with article browser
			$replace = $js . '<a href="#" onclick="setFormValueOpenBrowser_' . $table . '_' . $field . '(\'db\',\'data[' . $table . '][' . $row['uid'] . '][' . $field . ']|||tx_newspaper_article|\', \'' . $table . '\', \'' . $field . '\', ' . $row['uid'] . '); return false;" >';
			$out = preg_replace('/<a [^>]*setFormValueOpenBrowser[^>]*>/i', $replace, $out);
		}

	}

	/// replaces element browser with article browser
	private function replaceEbWithArticleBrowser($table, $field) {
//t3lib_div::devlog('replaceEbWithArticleBrowser()', 'newspaper', 0, array('GLOBALS[newspaper]' => $GLOBALS['newspaper'], 'table' => $table, $field => $field));
		//$GLOBALS['newspaper']['replaceEBwithArticleBrowser']['tx_newspaper_article'] = array(field1, ... fieldn);
		//$GLOBALS['newspaper']['replaceEBwithArticleBrowser'][another_table] = array(field1, ... fieldn);
		return 	array_key_exists('replaceEBwithArticleBrowser', $GLOBALS['newspaper']) &&
				array_key_exists(strtolower($table), $GLOBALS['newspaper']['replaceEBwithArticleBrowser']) && 
				in_array(strtolower($field), $GLOBALS['newspaper']['replaceEBwithArticleBrowser'][strtolower($table)]);
	}






	/// save hooks: new and update
	function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, $that) {
//t3lib_div::devlog('sh pre enter', 'newspaper', 0, array('incoming field array'=>$incomingFieldArray, 'table'=>$table, 'id'=>$id, 'request'=>$_REQUEST));

// \todo: move to writesLog check ???
		$this->checkIfWorkflowStatusChanged($incomingFieldArray, $table, $id);
	}

	private function checkIfWorkflowStatusChanged(&$incomingFieldArray, $table, $id) {
//t3lib_div::devlog('wf stat', 'newspaper', 0, array($incomingFieldArray, $table, $id, $_REQUEST));

		$request = $_REQUEST; // copy array, because values might be overwritten

		if (array_key_exists('hidden_status', $request) && $request['hidden_status'] != -1 && $request['hidden_status'] != $request['data'][$table][$id]['hidden']) {
			$incomingFieldArray['hidden'] = $request['hidden_status']; // if hide/publish button was used, overwrite value of field "hidden"
		}
		if (!array_key_exists('workflow_status', $request) || !array_key_exists('workflow_status_ORG', $request)) {
			return; // value not set, so can't decide if the status changed 
		}
		if ($request['workflow_status'] == $request['workflow_status_ORG']) {
			return; // status wasn't changed, so don't store value
		}
		$incomingFieldArray['workflow_status'] = $request['workflow_status']; // change workflow status
	}




	/** \todo some documentation would be nice ;-) */
	function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, $that) {
		global $LANG;
//t3lib_div::devlog('sh post enter', 'newspaper', 0, array('status' => $status, 'table' => $table, 'id' => $id, 'fieldArray' => $fieldArray, '_request' => $_REQUEST));

		/// add modifications user if tx_newspaper_Article is updated
		$this->addModificationUserIfArticle($status, $table, $id, $fieldArray);

		/// check if publish_date is to be added
		$this->addPublishDateIfNotSet($status, $table, $id, &$fieldArray);

		/// check if an article list was changed for a section
		$this->checkArticleListChangedInSection($fieldArray, $table, $id);

		/// check if a page zone type with is_article flag set is allowed
		$this->checkPageZoneWithIsArticleFlagAllowed($fieldArray, $table, $id); 
		
		/// check if the combination of get param name and value is unique
		$this->checkIfPageTypeGetVarGetValueIsUnique($fieldArray, $table, $id);

		/// handle uploads of tx_newspaper_Extra_Image
		$this->handleImageUploads($status, $table, $id, $fieldArray, $that);
		
		if (!tx_newspaper::isAbstractClass($table) && class_exists($table)) { ///<newspaper specification: table name = class name

			$np_obj = new $table();

			/// check if a newspaper record is saved and make sure it's stored in the appropriate sysfolder
			if (in_array("tx_newspaper_StoredObject", class_implements($np_obj))) {
/// \todo: move to function
				/// tx_newspaper_StoredObject is implemented, so record is to be stored in a special sysfolder
				$pid = tx_newspaper_Sysfolder::getInstance()->getPid($np_obj);
				$fieldArray['pid'] = $pid; // map pid to appropriate sysfolder
#t3lib_div::devlog('sh post fields modified', 'newspaper', 0, $fieldArray);
			}

			/// check if a newspaper record action should be logged
			if (in_array("tx_newspaper_WritesLog", class_implements($np_obj))) {
				
/// \todo: move to function - move to log class?

				/// IMPORTANT: checkIfWorkflowStatusChanged() has run, so $fieldArray has been modified already

				$request = $_REQUEST;

//debug($GLOBALS['BE_USER']);				
				$be_user = $GLOBALS['BE_USER']->user['uid']; /// i'm not sure if this object is always available, we'll see ...
				
				// check if the placement form should be opened after saving the record
				// \todo if that's possible ...
				$redirectToPlacementModule = false;
				if (isset($request['workflow_status']) && isset($request['workflow_status_ORG']) && $request['workflow_status'] == 2 && $request['workflow_status_ORG'] != 2) {
					$redirectToPlacementModule = true;
					$request['workflow_status'] = 1; /// active role is set to duty editor, but placement form is opened immediately. it that form is saved, workflow_status is set to 2
					$fieldArray['workflow_status'] = 1;						
				}
				
				
				/// check if auto log entry for hiding/publishing newspaper record should be written
				if (array_key_exists('hidden', $fieldArray)) {
					if ($table == 'tx_newspaper_article') {
						$action = $fieldArray['hidden']? $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:log_article_hidden', false) : $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:log_article_published', false);
					} else {
						$action = $fieldArray['hidden']? $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:log_record_hidden', false) : $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:log_record_published', false);
					}
					tx_newspaper::insertRows('tx_newspaper_log', array(
						'pid' => $fieldArray['pid'],
						'tstamp' => time(),
						'crdate' => time(), 
						'cruser_id' => $be_user, 
						'be_user' => $be_user, // same value as cruser_id, but this field is visible in backend
						'table_name' => $table, 
						'table_uid' => $id,
						'action' => $action,
						'comment' => ''
					));
				}
				
				/// check if auto log entry for change of workflow status should be written (article only)
				if ($table == 'tx_newspaper_article' & array_key_exists('workflow_status', $fieldArray) && array_key_exists('workflow_status', $request)) {
					tx_newspaper::insertRows('tx_newspaper_log', array(
						'pid' => $fieldArray['pid'],
						'tstamp' => time(),
						'crdate' => time(), 
						'cruser_id' => $be_user, 
						'be_user' => $be_user, // same value as cruser_id, but this field is visible in backend
						'table_name' => $table, 
						'table_uid' => $id,
						'action' => tx_newspaper_BE::getWorkflowStatusActionTitle(intval($fieldArray['workflow_status']), intval($request['workflow_status_ORG'])),
						'comment' => ''
					));
				}
				
				/// check if manual comment should be written (this log record should always be written LAST)
				if (isset($request['workflow_comment']) && $request['workflow_comment'] != '') {
					tx_newspaper::insertRows('tx_newspaper_log', array(
						'pid' => $fieldArray['pid'],
						'tstamp' => time(),
						'crdate' => time(), 
						'cruser_id' => $be_user, 
						'be_user' => $be_user, // same value as cruser_id, but this field is visible in backend
						'table_name' => $table, 
						'table_uid' => $id,
						'action' => $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:log_user_entry', false),
						'comment' => $request['workflow_comment']
					));
				}
/// \todo: if ($redirectToPlacementModule) { ...}
			}
		}
	}


	/// checks if the combination of get_var and get_value is unique for every page type
	private function checkIfPageTypeGetVarGetValueIsUnique($fieldArray, $table, $id) {
		if ($table == 'tx_newspaper_pagetype') {
			
			// get values for get_var and get_value and check if current record id is to be added to sql statement
			if (substr($id, 0, 3) == 'NEW') {
				// new record, so all fields are available in $fieldArray
				$param = $fieldArray['get_var'];
				$value = $fieldArray['get_value']; 
				$where['id'] = ''; // new record, so don't exclude this record from sql statement
			} else {
				$where['id'] = ' AND uid<>' . $id . ' '; // exclude current record from sql statement
				
				if (isset($fieldArray['get_var']) && isset($fieldArray['get_value'])) {
					// existing record and both fields needed are set in $fieldArray 
					$param = $fieldArray['get_var'];
					$value = $fieldArray['get_value']; 
				} else {
					// at least one value is missing in $fieldArray (for existing records), so read data from record
					$row = tx_newspaper::selectOneRow(
						'get_var, get_value',
						$table,
						'uid=' . $id
					);
					$param = (isset($fieldArray['get_var']))? $fieldArray['get_var'] : $row['get_var'];
					$value = (isset($fieldArray['get_value']))? $fieldArray['get_value'] : $row['get_value'];
				}
			}
			
			// check if the values for get_var and get_values that are to be saved are unique in the database
			$row = tx_newspaper::selectRows(
				'uid,type_name',
				$table,
				'deleted=0 ' . $where['id'] . ' AND get_var="' . $param . '" AND get_value="' . $value . '"'
			);
			
			if (sizeof($row) > 0) {
				die('Error: the combination of get variable and value must be unique. The used combination is already assigned to page type #' . 
					$row[0]['uid'] . ' (' . $row[0]['type_name'] . ').<br /><br /><a href="javascript:history.back();">Click here to retry</a>');
			}

		}
	}


	/// add modification user when updating tx_newspaper_article
	private function addModificationUserIfArticle($status, $table, $id, &$fieldArray) {
		if (strtolower($table) != 'tx_newspaper_article' || $status != 'update')
			return false;
		$fieldArray['modification_user'] = $GLOBALS['BE_USER']->user['uid'];
		return true; 
	}

	/// set publish_date when article changed from hidden=1 to hidden=0 and publish_date isn't set
	private function addPublishDateIfNotSet($status, $table, $id, &$fieldArray) {
//t3lib_div::devlog('addPublishDateIfNotSet()', 'newspaper', 0, array('status' => $status, 'table' => $table, 'id' => $id, 'fieldArray' => $fieldArray));
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
			$al = new $fieldArray['articlelist'](); // create new article list, the value in the backend dropdown is the name of the article list class
			if ($al instanceof tx_newspaper_articlelist) {

				// "delete" (= set deleted flag) previous concrete article list before writing the new one
				// concrete article list must be deleted first (otherwise data for concrete article list can't be obtained from abstract article list)
				$s = new tx_newspaper_Section(intval($id));
				$current_al = $s->getArticleList();
				tx_newspaper::updateRows(
					$current_al->getTable(),
					'uid=' . $current_al->getUid(),
					array('deleted' => 1)
				);

				// "delete" (= set deleted flag) all abstract article lists assigned to this section, before writing the new one
				// just deleting the current article list would do too, but this is easier (and deletes potential orphan article list for this section too)
				tx_newspaper::updateRows(
					'tx_newspaper_articlelist',
					'section_id=' . $id,
					array('deleted' => 1)
				);
				
				// store new article list
				$new_al = new $fieldArray['articlelist'](0, $s);
				$new_al->setAttribute('crdate', time());
				$new_al->setAttribute('cruser_id', $GLOBALS['BE_USER']->user['uid']);
				
				// check if current section is to be assigned to newly created semi automatic article list (default behavior)
/// \todo: move to class tx_newspaper_articlelist_semiautomatic?
				/// currently sections are stored as comma separated list, so init with current secton uid is working (won't work with mm relations)
				if (strtolower($fieldArray['articlelist']) == 'tx_newspaper_articlelist_semiautomatic') {
					$new_al->setAttribute('filter_sections', $s->getUid());
				}
				
				$new_al->store();
 				$fieldArray['articlelist'] = $new_al->getAbstractUid(); // store uid of abstract article list; will be stored in section
//t3lib_div::devlog('al 2 instanceof al: s, new_al, fieldArray', 'np', 0, array($s, $new_al, $fieldArray));
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
//t3lib_div::devlog('command pre enter', 'newspaper', 0, array('command' => $command, 'table' => $table, 'id' => $id, 'value' => $value));
//t3lib_div::debug($_SERVER); t3lib_div::debug($that); die();
		if ($command == 'delete') {
			$this->checkIfArticletypeCanBeDeleted($table, $id);
			$this->checkIfSectionCanBeDeleted($table, $id);
			$this->checkIfPageTypeCanBeDeleted($table, $id);
			$this->checkIfPageZoneTypeCanBeDeleted($table, $id);
			$this->processIfConcreteExtraIsDeleted($table, $id);
/// \todo: check if articles are assigned to section
/// \todo: check if pages are assigned to section
		}
	}

	/// if a concrete extra is deleted it is checked if 
	private function processIfConcreteExtraIsDeleted($table, $id) {
		if ($table != 'tx_newspaper_article' && tx_newspaper::classImplementsInterface($table, 'tx_newspaper_ExtraIface')) {
//t3lib_div::devlog('processIfConcreteExtraIsDeleted()', 'newspaper', 0, array('table' => $table, 'id' => $id));
			$e = new $table(intval($id));
//t3lib_div::devlog('processIfConcreteExtraIsDeleted() count ref', 'newspaper', 0, array($e->getReferenceCount()));
			if ($e->getReferenceCount() <= 1) {
				// just one (or none?) abstract extra for this concrete extra
				$e->deleteIncludingReferences(); 
			} else {
				// \todo: include list of reference, or even a link to delete all references?
				die('This extra can\'t be deleted because ' . $e->getReferenceCount() . ' references are existing! Please remove the extras one by one');
			}
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
		
		tx_newspaper_ArticleList::processDatamap_afterDatabaseOperations($status, $table, $id, &$fieldArray, $that);
		
	}

	private function addDefaultArticleListIfNewSection($status, $table, $id, $fieldArray, $that) {
		if ($status == 'new' && $table == 'tx_newspaper_section') {
			$section_uid = intval($that->substNEWwithIDs[$id]); // $id contains "NEWS...." id
			$s = new tx_newspaper_Section($section_uid);
			$al = new tx_newspaper_ArticleList_Semiautomatic(0, $s);
			$al->setAttribute('crdate', time());
			$al->setAttribute('cruser_id', $GLOBALS['BE_USER']->user['uid']);
			$al->setAttribute('filter_sections', $s->getUid()); // current section is default filter
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
		if (tx_newspaper::isAbstractClass($table)) {
			return; // abstract class, nothing to do
		}
	
		/// check if a new extra is stored
		// exclude new articles - articles are extras but shouldn't be treated like extras here!
		if ($status == 'new' && $table != 'tx_newspaper_article' && tx_newspaper::classImplementsInterface($table, 'tx_newspaper_ExtraIface')) {
//t3lib_div::devlog('writeRecordsIfNewExtraOnPageZone()', 'newspaper', 0, array($table, $id, $_REQUEST));		
			$pz_uid = intval(t3lib_div::_GP('new_extra_pz_uid'));
			$after_origin_uid = intval(t3lib_div::_GP('new_extra_after_origin_uid'));
			if (!$pz_uid) {
				t3lib_div::devlog('writeRecordsIfNewExtraOnPageZone(): Illegal value for pagezone uid: #', 'newspaper', 3, array($table, $id, $pz_uid));
				die('Fatal error: Illegal value for pagezone uid: #' . $pz_uid . '. Please contact developers');
			}

			// get uid of new concrete extra (that was just stored)
			$concrete_extra_uid = intval($that->substNEWwithIDs[$id]);
			
			// create abstract record for this concrete extra
			$abstract_uid = tx_newspaper_Extra::createExtraRecord($concrete_extra_uid, $table, true); // $force=true, there's no abstract record for this extra existing (for this is a totally new extra)

			// get pagezone (pagezone_page or article)
			$pz = tx_newspaper_PageZone_Factory::getInstance()->create(intval($pz_uid));

			// get extra ...
			$e = tx_newspaper_Extra_Factory::getInstance()->create($abstract_uid);
			// .... add set some default values
			$e->setAttribute('show_extra', 1);
			$e->setAttribute('is_inheritable', 1);

			// insert extra on pagezone
			$pz->insertExtraAfter($e, $after_origin_uid, true); // insert BEFORE setting the paragraph (so the paragraph can be inherited)

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