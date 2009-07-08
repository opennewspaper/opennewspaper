<?php
/**
 *  \file class.tx_newspaper_be.php
 *
 *  \author Oliver Schrï¿½der <newspaper@schroederbros.de>
 *  \date Feb 27, 2009
 */

define('BE_DISPLAY_MODE_IFRAME', 1);
define('BE_DISPLAY_MODE_SUBMODAL', 2);

define('BE_ICON_CLOSE', '1');

define('DEBUG_POSITION', false);

/// function for adding newspaper functionality to the backend
class tx_newspaper_BE {
	
	private static $smarty = null;
	
	private static $backend_files_added = false; // are js/css files added for backend

	public static function renderPageZoneList($PA, $fObj=null) {
		global $LANG;
#t3lib_div::devlog('pa in index.rPZL', 'newspaper', 0, $PA);		

		if (!isset($PA['SECTION'])) {
			t3lib_div::devlog('renderPZL: no section', 'newspaper', 3, $PA); exit(); /// \todo replace with exception
		}
		$section_uid = $PA['SECTION'];

		$pagezone_type = tx_newspaper_PageZoneType::getAvailablePageZoneTypes(); // get page  zone type objects

		$pagezone_type_data = array(); // to collect information for rendering

		$page_uid = $PA['row']['uid'];
		$p = new tx_newspaper_Page(intval($page_uid));
#t3lib_div::debug($p);
		// add data to active pagezone types
		foreach($p->getActivePageZones() as $active_pagezone) {
#t3lib_div::debug($active_pagezone);
#t3lib_div::devlog('gapz uid', 'newspaper', 0, $active_pagezone->getUid());
			/// get page zone type id for this active page
			for ($i = 0; $i < sizeof($pagezone_type); $i++) {
				if ($pagezone_type[$i]->getUid() == $active_pagezone->getPageZoneType()->getUid()) {
					$pagezone_type_data[$i]['ACTIVE'] = true;
					$pagezone_type_data[$i]['ACTIVE_PAGEZONE_ID'] = $active_pagezone->getUid();
#t3lib_div::devlog('gapz abstract uid 3', 'newspaper', 0, $active_pagezone->getAbtractUid());
					$pagezone_type_data[$i]['AJAX_DELETE_URL'] = 'javascript:deletePageZone(' . $section_uid . ', ' . $page_uid . ', ' . $active_pagezone->getAbstractUid() . ', \'' . addslashes($LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_check_delete_pagezone_in_page', false)) . '\');';
					break;
				}
			}
		}

		// add ajax call to each row
		for ($i = 0; $i < sizeof($pagezone_type); $i++) {
			$pagezone_type_data[$i]['type_name'] = $pagezone_type[$i]->getAttribute('type_name');
			// no edit icon needed - nothing to edit here
			if (!isset($pagezone_type_data[$i]['ACTIVE'])) {
				$pagezone_type_data[$i]['ACTIVE'] = false;
				$pagezone_type_data[$i]['AJAX_URL'] = 'javascript:activatePageZoneType(' . $section_uid . ', ' . $page_uid . ', ' . $pagezone_type[$i]->getUid() . ', \'' . addslashes($LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_check_new_pagezone_in_page', false)) . '\');';
			}
		}
#t3lib_div::devlog('pzt ajax inserted', 'newspaper', 0, $pagezone_type_data);

		/// generate be html code using smarty
 		self::$smarty = new tx_newspaper_Smarty();
		self::$smarty->setTemplateSearchPath(array(PATH_typo3conf . 'ext/newspaper/res/be/templates'));

		// add data rows
		self::$smarty->assign('DATA', $pagezone_type_data);

		// add skinned icons
		self::$smarty->assign('OK_ICON', self::renderIcon('gfx/icon_ok2.gif', '', $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:flag_activated_pagezone_in_section', false)));
		self::$smarty->assign('ADD_ICON', self::renderIcon('gfx/new_file.gif', '', $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:flag_new_pagezone_in_section', false)));
		self::$smarty->assign('CLOSE_ICON', self::renderIcon('gfx/goback.gif', '', $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_close_pagezone_in_section', false)));
		self::$smarty->assign('DELETE_ICON', self::renderIcon('gfx/garbage.gif', '', $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_delete_pagezone_in_section', false)));


		// add title and message
		self::$smarty->assign('TITLE', $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_title_pagezone_in_section', false));
		
		self::$smarty->assign('AJAX_RETURN_URL', 'javascript:listPages(' . $section_uid . ');');
		self::$smarty->assign('RETURN_TO_PAGETYPES', $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_close_pagezone_in_section', false));

		$html = self::$smarty->fetch('pagezonetype4section.tmpl');

		return $html;
	}
	
	
	
	
	/// render list of pages for section backend
	/// either called by userfunc in be or ajax
	public static function renderPageList($PA, $fObj=null) {
		global $LANG;
#t3lib_div::devlog('rpl pa', 'newspaper', 0, $PA);

		if (strtolower(substr($PA['row']['uid'], 0, 3)) == 'new') {
			/// new section record, so no "real" section uid available
			return $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_section_not_saved_page', false);
		}
		$section_uid = intval($PA['row']['uid']);
#t3lib_div::devlog('rpl section id', 'newspaper', 0, $section_uid);
		
		$page_type = tx_newspaper_PageType::getAvailablePageTypes();

		$page_type_data = array(); // to collect information for be rendering

		// add data to active page types
		foreach(tx_newspaper_Page::getActivePages(new tx_newspaper_Section($section_uid)) as $active_page) {
			for ($i = 0; $i < sizeof($page_type); $i++) {
				if ($page_type[$i]->getUid() == $active_page->getAttribute('pagetype_id')) {
					$page_type_data[$i]['ACTIVE'] = true;
					$page_type_data[$i]['ACTIVE_PAGE_ID'] = $active_page->getUid();
					$page_type_data[$i]['AJAX_DELETE_URL'] = 'javascript:deletePage(' . $section_uid . ', ' . $active_page->getUid() . ', \'' . addslashes($LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_check_delete_pagezone_in_page', false)) . '\');';
					break;
				}
			}
		}

		// add ajax call to each row
		for ($i = 0; $i < sizeof($page_type); $i++) {
			$page_type_data[$i]['type_name'] = $page_type[$i]->getAttribute('type_name');
			if (isset($page_type_data[$i]['ACTIVE'])) {
				$page_type_data[$i]['AJAX_URL'] = 'javascript:editActivePage(' . $section_uid . ' , ' . $page_type_data[$i]['ACTIVE_PAGE_ID'] . ');';
			} else {
				$page_type_data[$i]['ACTIVE'] = false;
				$page_type_data[$i]['AJAX_URL'] = 'javascript:activatePageType(' . $section_uid . ' , ' . $page_type[$i]->getUid() . ', \'' . addslashes($LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_check_new_page_in_section', false)) . '\');';
			}
		}

		/// generate be html code using smarty 
 		self::$smarty = new tx_newspaper_Smarty();
		self::$smarty->setTemplateSearchPath(array(PATH_typo3conf . 'ext/newspaper/res/be/templates'));
 
		// add skinned icons
		self::$smarty->assign('EDIT_ICON', self::renderIcon('gfx/edit2.gif', '', $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:flag_edit_page_in_section', false)));
		self::$smarty->assign('ADD_ICON', self::renderIcon('gfx/new_file.gif', '', $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:flag_new_page_in_section', false)));
		self::$smarty->assign('DELETE_ICON', self::renderIcon('gfx/garbage.gif', '', $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_delete_page_in_section', false)));
		

		// add title and message
		self::$smarty->assign('TITLE', $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_title_page_in_section', false));

		/// add data rows
		self::$smarty->assign('DATA', $page_type_data);
		
		$html = '';
		if (!$PA['AJAX_CALL']) {
			$html = '';
			self::$smarty->assign('AJAX_CALL', true);
		} else {
			self::$smarty->assign('AJAX_CALL', false);
		}
		$html .= self::$smarty->fetch('pagetype4section.tmpl');
		
		return $html;

	}
	

	/// itemsProcFunc to fill templateset dropdowns
	function addTemplateSetDropdownEntries(&$params, &$pObj) {
		global $LANG; 
		
		$default_found = false;
		
		$templateset = tx_newspaper_smarty::getAvailableTemplateSets();
//t3lib_div::devlog('available templ sets', 'newspaper', 0, $templateset);
		$params['items'][] = array($LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:entry_templateset_inherit', false), ''); // empty entry -> templateset is inherited
		$params['items'][] = array('default', 'default'); // default set is sorted to top of list, if not existing, this entry is removed later
		
		for ($i = 0; $i < sizeof($templateset); $i++) {
			if ($templateset[$i] != 'default') {
				$params['items'][] = array($templateset[$i], $templateset[$i]);				
			} else {
				$default_found = true;
			}
		}
		
		if (!$default_found) {
			unset($params['items'][1]); // remove entry 'default' (because there's no templateset default available)
		}
		
	} 



/// \todo: remove if really not needed
//	function addArticlelistDropdownEntries(&$params, &$pObj) {
//		$s = new tx_newspaper_Section(intval($params['row']['uid']));
//		try {
//			$al_active = $s->getArticleList();	
//		} catch (tx_newspaper_EmptyResultException $e) {
//t3lib_div::devlog('remove try/catch later', 'newspaper', 0); /// \todo
//			$al_active = null;
//		};
//
//		$al_available = tx_newspaper_ArticleList::getRegisteredArticleLists();
//		for ($i = 0; $i < sizeof($al_available); $i++) {
//			if ($al_available[$i]->getUid() > 0) 
//				$value = $al_available[$i]->getUid();
//			else 
//				$value = $al_available[$i]->getTable(); // -($i+1);
//			$params['items'][] = array($al_available[$i]->getTitle(), $value);
//		}
//		
//t3lib_div::devlog('al dropdown', 'newspaper', 0, $params);
//	}






	/// render article list form for section backend
	/// either called by userfunc in be or ajax
	public static function renderArticleList($PA, $fObj=null) {
		global $LANG;
/// \todo: move js to external file ... but how to handle localization then? And access to $PA?		
#return 'to come ... renderArticleList()';
echo "
<script language='javascript'>
 function processArticlelist() {

 	tmp = findElementsByName('" . $PA['itemFormElName'] . "', 'select');
 	if (tmp.length > 0)
 		selectbox = tmp[0];
 	else {
 		alert('Dropdown for article list cannot be found');
 		return false;
 	}
 	
 	selIndex = selectbox.selectedIndex;
	
	if (isNaN(selectbox.options[selIndex].value)) {
		// value is a class name -> create new super table record for article list
		document.getElementById('edit_articlelist').style.display = 'none';
		document.getElementById('NO_edit_articlelist').style.display = 'inline';
	} else {
		document.getElementById('edit_articlelist').style.display = 'inline';
		document.getElementById('NO_edit_articlelist').style.display = 'none';		
	}
 	
 		
 		
 }

function editArticleList() {



}

function findElementsByName(name, type) {
    var res = document.getElementsByTagName(type || '*');
    var ret = [];
    for (var i = 0; i < res.length; i++)
        if (res[i].getAttribute('name') == name) ret.push(res[i]);
    return ret;
};

</script>
<!--
		<a href='javascript:test();'>Test AL</a>
-->
";
		
		
		
		global $LANG;
//t3lib_div::devlog('ral pa', 'newspaper', 0, $PA);		
		if (strtolower(substr($PA['row']['uid'], 0, 3)) == 'new') {
			/// new section record, so no "real" section uid available
			return $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_section_not_saved_articlelist', false);
		}
		$section_uid = intval($PA['row']['uid']);

		// set configuration
		$config['type'] = 'select';
		$config['size'] = 1;
		$config['maxitems'] = 1;
		$config['form_type'] = 'select';

		// add article lists to dropdown
		$al_available = tx_newspaper_ArticleList::getRegisteredArticleLists();
		$s = new tx_newspaper_Section($section_uid);
		$s_al = tx_newspaper_ArticleList_Factory::getInstance()->create(intval($PA['row']['articlelist']), $s);
		$selItems = array();
		for ($i = 0; $i < sizeof($al_available); $i++) {
			if ($al_available[$i]->getTable() == $s_al->getTable()) {
				$value = $s->getAbstractArticleListUid(); // set value to uid of abstract article list
			} else {
				$value = $al_available[$i]->getTable(); // store class name as value
			}
			$selItems[] = array($al_available[$i]->getTitle(), $value, '');
		}
		
		$nMV_label = $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:error_dropdown_invalid_articlelist', false);	

		$obj = new t3lib_TCEforms(); 
	
		// add javascript to onchange field
		$PA['fieldChangeFunc']['TBE_EDITOR_fieldChanged'] = 'processArticlelist(); ' . $PA['fieldChangeFunc']['TBE_EDITOR_fieldChanged'];
	
		$out = $obj->getSingleField_typeSelect_single('tx_newspaper_section', 'articlelist', $PA['row'], $PA, $config, $selItems, $nMV_label);

		$out .= ' ' . self::renderEditIcon4ArticleList($s->getArticleList());

		return $out;

	}

	function renderEditIcon4ArticleList(tx_newspaper_Articlelist $al) {
		global $LANG;
		$html .= '<span id="edit_articlelist">';
		$html .= '<a target="np" href="alt_doc.php?returnUrl=close.html&edit[' . $al->getTable() . '][' . $al->getUid() . ']=edit">';
		$html .= self::renderIcon('gfx/edit2.gif', '', $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:flag_edit_articlelist_in_section', false));
		$html .= '</a>';
		$html .= '</span>';
		$html .= '<span style="display:none;" id="NO_edit_articlelist">' .  $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_edit_articlelist_in_section_save_first', false) . '</span>';
		return $html;
	}










// \todo: move to pagezone?
	public static function collectExtras(tx_newspaper_PageZone $pz) {
		$extra = $pz->getExtras();
		$data = array();
		for ($i = 0; $i < sizeof($extra); $i++) {
			
			$extra_data = array(
				'extra_type' => $extra[$i]->getTitle(),
				'uid' => $extra[$i]->getExtraUid(),
				'title' => $extra[$i]->getDescription(), //$extra[$i]->getAttribute('title'),
				'origin_placement' => $extra[$i]->isOriginExtra(),
				'origin_uid' => $extra[$i]->getOriginUid(),
				'concrete_table' => $extra[$i]->getTable(),
				'concrete_uid' => $extra[$i]->getUid(),
				'inherits_from' =>  $pz->getExtraOriginAsString($extra[$i]),
			);
			// the following attributes aren't always available 
			try {
				$extra_data['hidden'] = $extra[$i]->getAttribute('hidden');
			} catch (tx_newspaper_WrongAttributeException $e) {
				
			}
			try {
				$extra_data['show'] = $extra[$i]->getAttribute('show_extra');
			} catch (tx_newspaper_WrongAttributeException $e) {
				
			}
			try {
				$extra_data['paragraph'] = $extra[$i]->getAttribute('paragraph');
			} catch (tx_newspaper_WrongAttributeException $e) {
				
			}
			$data[] = $extra_data;
		}
		return $data;
	} 
	

	function renderExtraInArticle($PA, $fobj) {
		global $LANG;
//t3lib_div::devlog('renderExtraInArticl np_e_be', 'newspaper', 0, $PA);

		if ($PA['row']['articletype_id'] == 0)
			return 'Ohne Artikeltyp keine Defaultbestückung'; /// \todo: ...
		$current_record['table'] = $PA['table'];
		$current_record['uid'] = $PA['row']['uid'];
//debug($PA['row']);	



		$label['extra'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_extra', false);
		$label['show'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_show', false);
		$label['pass_down'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_pass_down', false);
		$label['inherits_from'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_inherits_from', false);
		$label['commands'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_commands', false);
		$label['show_levels_above'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_show_levels_above', false);
		$label['extra_delete_confirm'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_delete_confirm', false);
		
		$message['pagezone_empty'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_pagezone_empty', false);
		
		$a = new tx_newspaper_Article(intval($PA['row']['uid']));
		$e = $a->getExtras();
		
		$e = self::collectExtras($a);
		
//debug($e, 'getExtras()');
//debug($e[0], 'getExtras [0]');

 		$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/res/be/templates/'));

/// \todo: check title flags
/// \todo: move to array (like $label)
		$smarty->assign('NEW_EXTRA_ICON', tx_newspaper_BE::renderIcon('gfx/new_record.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_new_below', false)));
		$smarty->assign('HIDE_ICON', tx_newspaper_BE::renderIcon('gfx/button_hide.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_hide', false)));
		$smarty->assign('UNHIDE_ICON', tx_newspaper_BE::renderIcon('gfx/button_unhide.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_unhide', false)));
		$smarty->assign('EDIT_ICON', tx_newspaper_BE::renderIcon('gfx/edit2.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_edit_extra', false)));
		$smarty->assign('MOVE_UP_ICON', tx_newspaper_BE::renderIcon('gfx/button_up.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_move_up', false)));
		$smarty->assign('MOVE_DOWN_ICON', tx_newspaper_BE::renderIcon('gfx/button_down.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_move_down', false)));
		$smarty->assign('NEW_TOP_ICON', tx_newspaper_BE::renderIcon('gfx/new_record.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_new_top', false)));
		$smarty->assign('DELETE_ICON', tx_newspaper_BE::renderIcon('gfx/garbage.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_delete', false)));
		$smarty->assign('DUMMY_ICON', tx_newspaper_BE::renderIcon('gfx/dummy_button.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_delete', false)));
//		$smarty->assign('REMOVE_ICON', tx_newspaper_BE::renderIcon('gfx/selectnone.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_delete', false)));
		$smarty->assign('EMPTY_ICON', '<img src="clear.gif" width=16" height="16" alt="" />');


		$smarty->assign('EXTRA_DATA', $e);
		$smarty->assign('LABEL', $label);
		$smarty->assign('MESSAGE', $message);


		return $smarty->fetch('pagezone_extra_in_article.tmpl');

		$content = 'Extra list demo:<br />';
		for ($i = 0; $i < sizeof($e); $i++) {
			$content .= $e[$i]->getDescription() . '<br />';
		}
		return $content;
	}




	function getWorkflowButtons($PA, $fobj) {
		global $LANG;
//t3lib_div::devlog('button tsc', 'newspaper', 0, $PA['fieldTSConfig']);		
		
		$hidden = $PA['row']['hidden'];
		$workflow = intval($PA['row']['workflow_status']);
		
		$button = array(); // init with false ...
		$button['hide'] = false;
		$button['publish'] = false; // show
		$button['check'] = false;
		$button['revise'] = false;
		$button['place'] = false;

		// hide or publish button is available for every workflow status
		if (!$hidden)
			$button['hide'] = $this->isButtonVisible('hide', $PA['fieldTSConfig']['hide']);
		else
			$button['publish'] = $this->isButtonVisible('publish', $PA['fieldTSConfig']['publish']);

		switch($workflow) {
			case 0:
				$button['check'] = $this->isButtonVisible('check', $PA['fieldTSConfig']['check']);
				$button['place'] = $this->isButtonVisible('place', $PA['fieldTSConfig']['place']);
			break;
			case 1:
				$button['revise'] = $this->isButtonVisible('revise', $PA['fieldTSConfig']['revise']);
				$button['place'] = $this->isButtonVisible('place', $PA['fieldTSConfig']['place']);
			break;
			case 2:
				// no functionality right now; might take injunction button later ...
			break;
			default:
				die('todo: throw exception');
		}
//t3lib_div::devlog('button', 'newspaper', 0, $button);
		return $this->renderWorkflowButtons($hidden, $button);
	}

	private function renderWorkflowButtons($hidden, $button) {
		$content = '';
//$content .= '<input width="16" type="image" height="16" title="Save document" src="sysext/t3skin/icons/gfx/savedok.gif" name="_savedok" class="c-inputButton"/> <input width="16" type="image" height="16" title="Save and close document" src="sysext/t3skin/icons/gfx/saveandclosedok.gif" name="_saveandclosedok" class="c-inputButton"/><br />';
//$content .= ' <input width="16" type="image" height="16" title="CHECK: Save and close document" src="sysext/t3skin/icons/gfx/saveandclosedok.gif" name="_saveandclosedok_CHECK" class="c-inputButton"/>';
$content .= '<input              name="_savedok_check"                width="16" type="image" height="16" title="Save document AND SEND TO CvD" src="sysext/t3skin/icons/gfx/savedok.gif" class="c-inputButton"/>';

//t3lib_div::devlog('button', 'newspaper', 0, $button);	
		/// hide / publish
		if (!$hidden && $button['hide']) {
			$content .= 'hide<br />';
		} elseif ($hidden && $button['publish']) {
			$content .= 'publish<br />';
		}
		
		/// check / revise
		if ($button['check']) {
			$content .= 'check';
			if (!$hidden && $button['hide'])
				$content .= ' check&hide';
			elseif ($hidden && $button['publish'])
				$content .= 'check&publish';
			$content .= '<br />';
		} elseif ($button['revise']) {
			$content .= 'revise';
			if (!$hidden && $button['hide'])
				$content .= ' revise&hide';
			elseif ($hidden && $button['publish'])
				$content .= ' revise&publish';
			$content .= '<br />';
		}

		/// place
/// \todo 		

		return $content;
	}


	/// \param String $button (internal) name of button
	/// \param String $be_groups uids of allowed be_groups (comma separated)
	/// \return Boolean is be_user member of one of given be_groups
	private function isButtonVisible($button, $be_config) {
//t3lib_div::devlog('button', 'newspaper', 0, array($GLOBALS['BE_USER'], $button, $be_config));
		$be_group = explode(',', $be_config);
		for ($i = 0; $i < sizeof($be_group); $i++) {
			if ($GLOBALS['BE_USER']->isMemberOfGroup($be_group[$i]))
				return true;
		}
		return false;
	}







	/// get html for this icon (may include an anchor) 
	/** \param $image path to icon
	 *  \param $id 
	 *  \param $title title for title flag of img
	 *  \param $ahref 
	 *  \param $replaceWithCleargifIfEmpty if set to true the icon is replaced with clear.gif, if $ahref is empty
	 *  \return String <img ...> or <a href><img ...></a> (if linked)
	 */
	public static function renderIcon($image, $id, $title='', $ahref='', $replaceWithCleargifIfEmpty=false) {

/// \to do: read width and height from file? or hardcode 16x16px?
		$width = 16;
		$height = 16;

		// modify path if script in typo3conf/ext is called -> probably in a module
		$backPath = '';
		if (strpos($_SERVER['SCRIPT_FILENAME'], 'typo3conf/ext') > 0 && 
			substr(PATH_typo3, 0, strlen($_SERVER['DOCUMENT_ROOT'])) == $_SERVER['DOCUMENT_ROOT']
		) {
/// \todo: build correct backpath
			$backPath = ((TYPO3_OS == 'WIN')? '' : '/') . substr(PATH_typo3, strlen($_SERVER['DOCUMENT_ROOT']));
		}
	
		if ($id)
			$id = ' id="' . $id . '" '; // if id is set, set build attribute id="..."

		if ($ahref == '' && $replaceWithCleargifIfEmpty) {
			// hide icon (= replace with clear.gif)
			$html = '<img' . $id . t3lib_iconWorks::skinImg($backPath, 'clear.gif', 'width="' . $width . '" height="' . $height . '"') . ' title="' . $title . '" alt="" />';
		} else {
			// show icon
			//$html = '<img' . $id . t3lib_iconWorks::skinImg('', $image, 'width="' . $width . '" height="' . $height . '"') . ' title="' . $title . '" alt="" />';
			$html = '<img' . $id . t3lib_iconWorks::skinImg($backPath, $image) . ' title="' . $title . '" alt="" />';
		}
		if ($ahref)
			return $ahref . $html . '</a>'; // if linked wrap in link
		return $html; // return image html code

	}



	/**
	 * add javascript and css files needed for display mode (adds to $GLOBALS['TYPO3backend'])
	 * called by hook $GLOBALS['TYPO3_CONF_VARS']['typo3/backend.php']['additionalBackendItems'][]
	 * \return true, if files were added
	 */
	public static function addAdditionalScriptToBackend() {
		switch(self::getExtraBeDisplayMode()) {
			case BE_DISPLAY_MODE_IFRAME:
				self::$backend_files_added = true; // nothing to add for iframe mode
			break;
			case BE_DISPLAY_MODE_SUBMODAL:
				// add modalbox js to top (so modal box can be displayed over the whole backend, not only the content frame)
				$GLOBALS['TYPO3backend']->addJavascriptFile(t3lib_extMgm::extRelPath('newspaper') . 'contrib/subModal/common.js');
				$GLOBALS['TYPO3backend']->addJavascriptFile(t3lib_extMgm::extRelPath('newspaper') . 'contrib/subModal/subModal.js');
				$GLOBALS['TYPO3backend']->addJavascriptFile(t3lib_extMgm::extRelPath('newspaper') . 'res/be/extra/util.js');
				$GLOBALS['TYPO3backend']->addCssFile('subModal', t3lib_extMgm::extRelPath('newspaper') . 'contrib/subModal/subModal.css');
				self::$backend_files_added = true;
			break;
		}
	}

/// \todo: read from tsconfig
	public static function getExtraBeDisplayMode() {
		return BE_DISPLAY_MODE_SUBMODAL;
	}

	
	public static function wrapInAhref($html, $type) {
		switch ($type) {
			case BE_ICON_CLOSE:
				switch (self::getExtraBeDisplayMode()) {
					case BE_DISPLAY_MODE_SUBMODAL:
						$html = '<a href="#" onclick="top.hidePopWin(false);">' . $html . '</a>';
					break;
				}
			break;
		}
		return $html;
	}

	
}

?>