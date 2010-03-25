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
		
		// pass down to newspaper hooks
		tx_newspaper_article::getDBlistQuery($table, $pageId, $additionalWhereClause, $selectedFieldsList, $parentObject);
		
	}






	/// TCEForm hooks
	function getSingleField_preProcess($table, $field, $row, $altName, $palette, $extra, $pal, $that) {
//t3lib_div::devlog('getSingleField_preProcess() hook', 'newspaper', 0, array('table' => $table, 'field' => $field, 'row' => '$row, 'altName' => $altName, 'palette' => $palette, 'extra' => $extra, 'pal' => $pal, '_REQUEST' => $_REQUEST));
		$this->checkCantUncheckIsArticlePageZoneType($table, $field, $row);
        tx_newspaper_Article::getSingleField_preProcess($table, $field, $row, $altName, $palette, $extra, $pal, $that);
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
			// replace eb with article browser
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
	
	function getMainFields_preProcess($table, $row, $that) {
 t3lib_div::devlog('getMainFields_preProcess', 'newspaper', 0, array('table' => $table, 'row' => $row));	
		// pass down hook to newspaper classes
		tx_newspaper_articlelist::getMainFields_preProcess($table, $row, $that);
	}






	/// save hooks: new and update
	function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, $that) {
//t3lib_div::devlog('sh pre enter', 'newspaper', 0, array('incoming field array' => $incomingFieldArray, 'table' => $table, 'id' => $id, '_request' => $_REQUEST));
		// pass data to newspaper classes
		tx_newspaper_Workflow::processDatamap_preProcessFieldArray($incomingFieldArray, $table, $id, $that);
        tx_newspaper_Article::processDatamap_preProcessFieldArray($incomingFieldArray, $table, $id, $that);
	}



	/** \todo some documentation would be nice ;-) */
	function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, $that) {

		// call save hook in newspaper classes
		/// \todo do it in handleRegisteredSaveHooks() - or must this be executed first?
		tx_newspaper_Article::processDatamap_postProcessFieldArray($status, $table, $id, $fieldArray, $that);
		

		/// add modifications user if tx_newspaper_Article is updated
		$this->addModificationUserIfArticle($status, $table, $id, $fieldArray);

		/// check if a page zone type with is_article flag set is allowed
		$this->checkPageZoneWithIsArticleFlagAllowed($fieldArray, $table, $id); 
		
		/// check if the combination of get param name and value is unique
		$this->checkIfPageTypeGetVarGetValueIsUnique($fieldArray, $table, $id);

		/// handle uploads of tx_newspaper_Extra_Image
		/// \todo do it in handleRegisteredSaveHooks()
//		$this->handleImageUploads($status, $table, $id, $fieldArray, $that);
				
		$this->handleRegisteredSaveHooks('processDatamap_postProcessFieldArray', 
										 $status, $table, $id, $fieldArray, $that);

//        $fieldArray['tags'] = 2;
//        $_REQUEST['data'][$table][$id]['tags'] = '7,8';
		
/// \todo move to sysfolder class	
		if (class_exists($table) && !tx_newspaper::isAbstractClass($table)) { ///<newspaper specification: table name = class name
			$np_obj = new $table();
			/// check if a newspaper record is saved and make sure it's stored in the appropriate sysfolder
			if (in_array("tx_newspaper_StoredObject", class_implements($np_obj))) {
				/// tx_newspaper_StoredObject is implemented, so record is to be stored in a special sysfolder
				$pid = tx_newspaper_Sysfolder::getInstance()->getPid($np_obj);
				$fieldArray['pid'] = $pid; // map pid to appropriate sysfolder
#t3lib_div::devlog('sh post fields modified', 'newspaper', 0, $fieldArray);
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

	private function handleRegisteredSaveHooks($savehook_name, $status, $table, $id, $fieldArray, $that) {
		foreach (tx_newspaper::getRegisteredSaveHooks() as $savehook_object) {
			if (method_exists($savehook_object, $savehook_name)) {
				$savehook_object->$savehook_name($status, $table, $id, $fieldArray, $that);
			}
		}
	}


	/// save hook: delete
	function processCmdmap_preProcess($command, $table, $id, $value, $that) {
//t3lib_div::devlog('command pre enter', 'newspaper', 0, array('command' => $command, 'table' => $table, 'id' => $id, 'value' => $value));
//t3lib_div::debug($that); die();
		if ($command == 'delete') {
			$this->checkIfArticletypeCanBeDeleted($table, $id);
			$this->checkIfSectionCanBeDeleted($table, $id);
			$this->checkIfPageTypeCanBeDeleted($table, $id);
			$this->checkIfPageZoneTypeCanBeDeleted($table, $id);
/// \todo: check if articles are assigned to section
/// \todo: check if pages are assigned to section
			
			// pass hook to newspaper classes			
			tx_newspaper_extra::processCmdmap_preProcess($command, $table, $id, $value, $that);
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
//t3lib_div::devlog('adbo after enter', 'newspaper', 0, array('status' => $status, 'table' => $table, 'id' => $id, 'fieldArray' => $fieldArray, 'subst id' => $that->substNEWwithIDs)); // , $_REQUEST
		// pass hook to newspaper classes
		tx_newspaper_Section::processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, $that);
		tx_newspaper_ArticleList::processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, $that);
		tx_newspaper_extra::processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, $that);
	}

	
	
	/// Extension manager (EM) hook
	
	function tsStyleConfigForm($_funcRef, $_params, $that=null) {
//t3lib_div::devlog('tsStyleConfigForm()', 'newspaper', 0, array('dummy' => $_params->CMD, '_funcRef' => $_funcRef, 'print_r _params' => print_r($_params, true)));
		if (isset($_params->CMD['showExt']) && strtolower($_params->CMD['showExt']) == 'newspaper') {
			// update button in extension manager was pressed AND current extions is newspaper, so create all non-existing newspaper sysfolders
			tx_newspaper_sysfolder::createAll();
		}
	}
	
}	

?>