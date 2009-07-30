<?php
/**
 *  \file class.tx_newspaper_be.php
 *
 *  \author Oliver Schr�der <newspaper@schroederbros.de>
 *  \date Feb 27, 2009
 */

define('BE_DISPLAY_MODE_IFRAME', 1);
define('BE_DISPLAY_MODE_SUBMODAL', 2);

define('BE_ICON_CLOSE', '1');

define('DEBUG_POSITION', false);

define('NP_ARTICLE_WORKFLOW_NOCLOSE', true); // if set to true the workflow buttons don't close the form (better for testing)

/// function for adding newspaper functionality to the backend
class tx_newspaper_BE {
	
	private static $smarty = null;
	
	private static $backend_files_added = false; // are js/css files added for backend





	/// backend: render list of pages and pagezones for section
	/// either called by userfunc in be or ajax
	public static function renderPagePageZoneList($PA, $fObj=null) {
		global $LANG;
//t3lib_div::devlog('render ppzlist $pa', 'np', 0, $PA);
		if (strtolower(substr($PA['row']['uid'], 0, 3)) == 'new') {
			/// new section record, so no "real" section uid available
			return $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_section_not_saved_page', false);
		}
		$section_uid = intval($PA['row']['uid']);
		
		$page_types = tx_newspaper_PageType::getAvailablePageTypes(); // get page type objects
		$pagezone_types = tx_newspaper_PageZoneType::getAvailablePageZoneTypes(); // get page zone type objects
		
		$data = array(); // information for be rendering

		// add data for ACTIVE page types
		$section = new tx_newspaper_Section($section_uid);
		foreach($section->getActivePages() as $active_page) {
			for ($i = 0; $i < sizeof($page_types); $i++) {
				if ($page_types[$i]->getUid() == $active_page->getAttribute('pagetype_id')) {
					// active page type found
					$data[$i]['ACTIVE'] = true;
					$data[$i]['ACTIVE_PAGE_ID'] = $active_page->getUid();
					$data[$i]['AJAX_DELETE_URL'] = 'javascript:deletePage(' . $section_uid . ', ' . $active_page->getUid() . ', \'' . addslashes($LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_check_delete_pagezone_in_page', false)) . '\');';
					break;
				}
			}
		}

		// add delete ajax call to each activated page, add activate ajax call to each non-activated page
		// add delete ajax call to each activated pagezone, add activate ajax call to each non-activated pagezone 
		// and add page type name
		for ($i = 0; $i < sizeof($page_types); $i++) {
			$data[$i]['type_name'] = $page_types[$i]->getAttribute('type_name');
			if (isset($data[$i]['ACTIVE']) && $data[$i]['ACTIVE'] == true) {
				// page is activated, so add pagezone list
				$p = new tx_newspaper_Page(intval($data[$i]['ACTIVE_PAGE_ID']));
				foreach($p->getActivePageZones() as $active_pagezone) {
					/// get ACTIVE page zone type id for ACTIVE page in loop
					for ($j = 0; $j < sizeof($pagezone_types); $j++) {
						if ($pagezone_types[$j]->getUid() == $active_pagezone->getPageZoneType()->getUid()) {
							// active pagezone type found
							$data[$i]['pagezones'][$j]['ACTIVE'] = true;
							$data[$i]['pagezones'][$j]['ACTIVE_PAGEZONE_ID'] = $active_pagezone->getUid();
							$data[$i]['pagezones'][$j]['AJAX_DELETE_URL'] = 'javascript:deletePageZone(' . $section_uid . ', ' . $data[$i]['ACTIVE_PAGE_ID'] . ', ' . $active_pagezone->getAbstractUid() . ', \'' . addslashes($LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_check_delete_pagezone_in_page', false)) . '\');';
							break;
						}
					}
				}
				// add ajax call to each non-activated pagezone type (and add pagezone type name)
				for ($j = 0; $j < sizeof($pagezone_types); $j++) {
					$data[$i]['pagezones'][$j]['type_name'] = $pagezone_types[$j]->getAttribute('type_name');
					if (!isset($data[$i]['pagezones'][$j]['ACTIVE'])) {
						$data[$i]['pagezones'][$j]['ACTIVE'] = false;
						$data[$i]['pagezones'][$j]['AJAX_ACTIVATE_URL'] = 'javascript:activatePageZoneType(' . $section_uid . ', ' . $data[$i]['ACTIVE_PAGE_ID'] . ', ' . $pagezone_types[$j]->getUid() . ', \'' . addslashes($LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_check_new_pagezone_in_page', false)) . '\');';
					}
				}
			} else {
				// not active, so no pagezones
				$data[$i]['ACTIVE'] = false;
				$data[$i]['AJAX_ACTIVATE_URL'] = 'javascript:activatePageType(' . $section_uid . ' , ' . $page_types[$i]->getUid() . ', \'' . addslashes($LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_check_new_page_in_section', false)) . '\');';
			}
		}
//t3lib_div::devlog('data apz', 'np', 0, $data);
		/// generate be html code using smarty 
 		self::$smarty = new tx_newspaper_Smarty();
		self::$smarty->setTemplateSearchPath(array(PATH_typo3conf . 'ext/newspaper/res/be/templates'));
 
		// add skinned icons
		self::$smarty->assign('EDIT_ICON', self::renderIcon('gfx/edit2.gif', '', $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:flag_edit_page_in_section', false)));
		self::$smarty->assign('ADD_ICON', self::renderIcon('gfx/new_file.gif', '', $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:flag_new_page_in_section', false)));
		self::$smarty->assign('DELETE_ICON', self::renderIcon('gfx/garbage.gif', '', $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_delete_page_in_section', false)));
		self::$smarty->assign('CLEAR_ICON', self::renderIcon('', '', '', '', true));
		self::$smarty->assign('OK_ICON', self::renderIcon('gfx/icon_ok2.gif', '', ''));
		

		// add title and message
		self::$smarty->assign('TITLE', $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_title_page_in_section', false));

		/// add data rows
		self::$smarty->assign('DATA', $data);
		
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
//t3lib_div::devlog('remove try/catch later', 'newspaper', 0);
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
		$html .= '<a target="np" href="alt_doc.php?returnUrl=../typo3conf/ext/newspaper/res/be/just_close.html&edit[' . $al->getTable() . '][' . $al->getUid() . ']=edit">';
		$html .= self::renderIcon('gfx/edit2.gif', '', $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:flag_edit_articlelist_in_section', false));
		$html .= '</a>';
		$html .= '</span>';
		$html .= '<span style="display:none;" id="NO_edit_articlelist">' .  $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_edit_articlelist_in_section_save_first', false) . '</span>';
		return $html;
	}










/// \todo: move to pagezone?
/// \todo: correct sorting: negative paragraph at the bottom
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
				'pass_down' => $extra[$i]->getAttribute('is_inheritable'),
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
			try {
				$extra_data['position'] = $extra[$i]->getAttribute('position');
			} catch (tx_newspaper_WrongAttributeException $e) {
			
			}
			
			//	don't display extras for which attribute gui_hidden is set
			if (!$extra[$i]->getAttribute('gui_hidden'))
				$data[] = $extra_data;
		}
		return $data;
	} 
	

	function renderExtraInArticle($PA, $fobj) {
		global $LANG;
//t3lib_div::devlog('renderExtraInArticl np_e_be', 'newspaper', 0, $PA);

		if ($PA['row']['articletype_id'] == 0)
			return 'Ohne Artikeltyp keine Defaultbest�ckung'; /// \todo: ...
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

		// create hidden field to store workflow_status (might be modified by JS when workflow buttons are used)
		$html = '<input id="workflow_status" name="workflow_status" type="hidden" value="' . $workflow . '" />';
		$html .= '<input name="workflow_status_ORG" type="hidden" value="' . $workflow . '" />';
		$html .= '<input id="hidden_status" name="hidden_status" type="hidden" value="-1" />'; // init with -1

		// add javascript \todo: move to external file
		$html .= '<script language="javascript" type="text/javascript">
function changeWorkflowStatus(status, hidden_status) {
	status = parseInt(status);
	hidden_status = parseInt(hidden_status);
	if (status == 0 || status == 1 || status == 2) {
		document.getElementById("workflow_status").value = status; // valid status found
	}
	document.getElementById("hidden_status").value = hidden_status;
//alert(document.getElementById("hidden_status").value);
	return false;
}
</script>
';

		// chosen buttons to be displayed
		$button = array(); // init with false ...
		$button['hide'] = false;
		$button['publish'] = false; // show
		$button['check'] = false;
		$button['revise'] = false;
		$button['place'] = false;
		// hide or publish button is available for every workflow status
		if (!$hidden) {
			$button['hide'] = $this->isButtonVisible('hide', $PA['fieldTSConfig']['hide']);
		} else {
			$button['publish'] = $this->isButtonVisible('publish', $PA['fieldTSConfig']['publish']);
		}
		switch($workflow) {
			case 0:
				$button['check'] = $this->isButtonVisible('check', $PA['fieldTSConfig']['check']);
				$button['place'] = $this->isButtonVisible('place', $PA['fieldTSConfig']['place']);
			break;
			case 1:
				$button['revise'] = $this->isButtonVisible('revise', $PA['fieldTSConfig']['revise']);
				$button['place'] = $this->isButtonVisible('place', $PA['fieldTSConfig']['place']);
			break;
//			case 2:
// 				might take injunction button later ...
//			break;
			default:
				die('todo: throw exception unknown workflow status: ' . $workflow);
		}
//t3lib_div::devlog('button', 'newspaper', 0, array($hidden, $button));
		$html .= $this->renderWorkflowButtons($hidden, $button);
		
		/// add workflow comment field (using smarty)
 		$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array(PATH_typo3conf . 'ext/newspaper/res/be/templates'));

		$html .= $smarty->fetch('workflow_comment.tmpl');

		return $html;
	}

	/// \param hidden
	/// \param $button array stating (boolean) if the button for the various states should be displayed
	private function renderWorkflowButtons($hidden, $button) {
		GLOBAL $LANG;
		
		$content = '';

//t3lib_div::devlog('button', 'newspaper', 0, $button);	
		/// hide / publish
		if (!$hidden && $button['hide']) {
			$content .= $this->renderWorkflowButton(false, 'hide', $hidden);
		} elseif ($hidden && $button['publish']) {
			$content .= $this->renderWorkflowButton(false, 'publish', $hidden);
		}
		$content .= '<br />';
		
		/// check / revise
		if ($button['check']) {
			$content .= $this->renderWorkflowButton(1, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_check', false), $hidden);
			if (!$hidden && $button['hide'])
				$content .= $this->renderWorkflowButton(1, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_check_hide', false), $hidden);
			elseif ($hidden && $button['publish'])
				$content .= $this->renderWorkflowButton(1, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_check_publish', false), $hidden);
			$content .= '<br />';
		} elseif ($button['revise']) {
			$content .= $this->renderWorkflowButton(0, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_revise', false), $hidden);
			if (!$hidden && $button['hide'])
				$content .= $this->renderWorkflowButton(0, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_revise_hide', false), $hidden);
			elseif ($hidden && $button['publish'])
				$content .= $this->renderWorkflowButton(2, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_revise_publish', false), $hidden);
			$content .= '<br />';
		}

		/// place
/// \todo -> call placement form - but how to ????????????????????????????????????????????????????????????????????????

		return $content;
	}
	
	/// \param $new_status true indicates a workflow status change
	private function renderWorkflowButton($new_status, $title, $hidden) {
		$hidden = intval(!$hidden); // negate first (button should toggle status); intval then, so js can handle the value
		if ($new_status !== false) {
			$js = 'changeWorkflowStatus(' . intval($new_status) . ', ' . $hidden . ')';
		} else {
			$js = 'changeWorkflowStatus(-1, ' . $hidden . ')'; 
		}
		
		$html = $title . '<input style="margin-right:20px;" title="' . $title . '" onclick="' . $js . '" ';
		if (NP_ARTICLE_WORKFLOW_NOCLOSE) {
			$html .= 'name="_savedok" src="sysext/t3skin/icons/gfx/savedok.gif" ';
		} else {
			$html .= 'name="_saveandclosedok" src="sysext/t3skin/icons/gfx/saveandclosedok.gif" ';			
		}
		$html .= 'width="16" type="image" height="16" class="c-inputButton"/>';

		return $html;
	}


	/// \param String $button (internal) name of button
	/// \param String $be_groups uids of allowed be_groups (comma separated)
	/// \return boolean is be_user member of one of given be_groups
	private function isButtonVisible($button, $be_config) {
//t3lib_div::devlog('button', 'newspaper', 0, array($GLOBALS['BE_USER'], $button, $be_config));
		$be_group = explode(',', $be_config);
		// check all groups
		for ($i = 0; $i < sizeof($be_group); $i++) {
			if ($GLOBALS['BE_USER']->isMemberOfGroup($be_group[$i])) {
				return true; // group found
			}
		}
		return false; // no group found
	}


	public static function getWorkflowStatusActionTitle($new, $old) {
		$new = intval($new);
		$old = intval($old);
/// \todo: in abh�ngigkeit von new und old einen string zur�ckgeben
		return 'to come ...';		
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

/// \todo: read width and height from file? or hardcode 16x16px?
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