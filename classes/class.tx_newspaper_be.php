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

define('NP_ARTICLE_WORKFLOW_NOCLOSE', false); // if set to true the workflow buttons don't close the form (better for testing)
define('NP_SHOW_PLACE_BUTTONS', false); // \todo after pressing the place button the article gets stores, workflow_status is set to 1 AND the placement form is opened. as that "open placement form" feature isn't implemented, this const can be used to hide the buttons in the backend


/// function for adding newspaper functionality to the backend
/** \todo Oliver: document me!
 */
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
					$data[$i]['DEFAULT_ARTICLE_PAGE'] = $active_page->getPageType()->getAttribute('is_article_page');
					$data[$i]['AJAX_DELETE_URL'] = 'javascript:deletePage(' . $section_uid . ', ' . $active_page->getUid() . ', \'' . addslashes($LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_check_delete_pagezone_in_page', false)) . '\');';
					$data[$i]['TEMPLATE_SET_HTML'] = tx_newspaper_BE::createTemplateSetDropdown('tx_newspaper_page', $active_page->getUid(), $active_page->getAttribute('template_set'));
					break;
				}
			}
		}

		// add delete ajax call to each activated page, add activate ajax call to each non-activated page
		// add delete ajax call to each activated pagezone, add activate ajax call to each non-activated pagezone 
		// and add page type name
		// and add pagezone type name
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
							$data[$i]['pagezones'][$j]['TEMPLATE_SET_HTML'] = tx_newspaper_BE::createTemplateSetDropdown($active_pagezone->getTable(), $active_pagezone->getUid(), $active_pagezone->getAttribute('template_set'));
							break;
						} 
					}
				}
				// add ajax call to each non-activated pagezone type (and add pagezone type name)
				// or remove pagezone type if not applicable
				for ($j = 0; $j < sizeof($pagezone_types); $j++) {
					$data[$i]['pagezones'][$j]['type_name'] = $pagezone_types[$j]->getAttribute('type_name');
					if (!isset($data[$i]['pagezones'][$j]['ACTIVE'])) {
						// so this pagezone type hasn't been activated
						if ($pagezone_types[$j]->getAttribute('is_article') && !$data[$i]['DEFAULT_ARTICLE_PAGE']) {
							// default article pagezone for non-default article page, this combinations is not allowed (and nonsense)
							unset($data[$i]['pagezones'][$j]); // so remove data collected so far for this combination
						} else {
							// active pagezone type found ['ACTIVE'] = false;
							$data[$i]['pagezones'][$j]['AJAX_ACTIVATE_URL'] = 'javascript:activatePageZoneType(' . $section_uid . ', ' . $data[$i]['ACTIVE_PAGE_ID'] . ', ' . $pagezone_types[$j]->getUid() . ', \'' . addslashes($LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_check_new_pagezone_in_page', false)) . '\');';
						}
					}
				}
			} else {
				// page type not active, so no pagezones to display
				$data[$i]['ACTIVE'] = false;
				$data[$i]['AJAX_ACTIVATE_URL'] = 'javascript:activatePageType(' . $section_uid . ' , ' . $page_types[$i]->getUid() . ', \'' . addslashes($LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_check_new_page_in_section', false)) . '\');';
			}
			if (is_array($data[$i]['pagezones'])) {
				ksort($data[$i]['pagezones'], SORT_NUMERIC); // sort array, so order of pagezone is fixed
				// renumber indeces (in case an entry was unset; so {section} can still be used in smarty)
				$data[$i]['pagezones'] = array_values($data[$i]['pagezones']); 
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
	
	
	/// itemsProcFunc to fill templateset dropdowns in "normal" tceforms backend forms
	function addTemplateSetDropdownEntries(&$params, &$pObj) {
		$this->readTemplateSetItems($params);
	}

	/// get available templates and store in &$param
	/**
	 * If template named default is found, it is moved to first position in the dropdown
	 */
	private function readTemplateSetItems(&$params) {
		global $LANG; 
		
		$default_found = false;
		
		$templateset = tx_newspaper_smarty::getAvailableTemplateSets();

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
			unset($params['items'][1]); // remove entry 'default' (because there's no templateset "default" available)
		}
	}

	/// create html code for a template set dropdown (including AJAX call in onchange event)
	/// assumes that js function storeTemplateSet() is available
	public static function createTemplateSetDropdown($table, $uid, $default_value='') {
		$params = array();
		self::readTemplateSetItems($params); // call by reference ...

		$html = '<select id="templateset_' . $uid . '" onchange="storeTemplateSet(\'' . $table . '\', ' . $uid . ', this.options[this.selectedIndex].value); return false;">'; //         
		foreach($params['items'] as $item) {
			$selected = ($item[1] == $default_value)? ' selected="selected"' : ''; // item[0] = title, item[1] = value to store
			$html .= '<option value="' . $item[1] . '"' . $selected . '>' . $item[0] . '</option>';
		}
		$html .= '</select>';
		return $html;
	}
		
	/// itemsProcFunc to fill inheritance for pages dropdowns in "normal" tceforms backend forms
	function addInheritancePageDropdownEntries(&$params, &$pObj) {
		$this->readInheritancePageItems($params);
	}

	private function readInheritancePageItems(&$params) {
		global $LANG; 
		
		$pages = array('dummy', 'test', 'aha');

		$params['items'][] = array($LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:entry_templateset_inherit', false), ''); // empty entry -> templateset is inherited
		$params['items'][] = array('default', 'default'); // default set is sorted to top of list, if not existing, this entry is removed later
		for ($i = 0; $i < sizeof($pages); $i++) {
			$params['items'][] = array($pages[$i], $pages[$i]);				
		}

	}

	/// render article list form for section backend
	/// either called by userfunc in be or ajax
	public static function renderArticleList($PA, $fObj=null) {
		global $LANG;
/// \todo: move js to external file ... but how to handle localization then? And access to $PA?		
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

function findElementsByName(name, type) {
    var res = document.getElementsByTagName(type || '*');
    var ret = [];
    for (var i = 0; i < res.length; i++)
        if (res[i].getAttribute('name') == name) ret.push(res[i]);
    return ret;
};

</script>
";
		
		
		
		global $LANG;
//t3lib_div::devlog('renderArticleList()', 'newspaper', 0, array('PA' => $PA));		
		if (strtolower(substr($PA['row']['uid'], 0, 3)) == 'new') {
			/// new section record, so no "real" section uid available
			return $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_section_not_saved_articlelist', false);
		}
		$section_uid = intval($PA['row']['uid']);

		// add article lists to dropdown
		$al_available = tx_newspaper_ArticleList::getRegisteredArticleLists();
		$s = new tx_newspaper_Section($section_uid);
		try {
			$s_al = $s->getArticleList(); // tx_newspaper_ArticleList_Factory::getInstance()->create(intval($PA['row']['articlelist']), $s);
		} catch (tx_newspaper_EmptyResultException $e) {
			// article list couldn't be fetched, so create a new default article list
			$s->assignDefaultArticleList();
			$s_al = $s->getArticleList();
			
			// overwrite article list uids in $PA with new article list uids
			$PA['row']['articlelist'] = $s_al->getAbstractUid();
			$PA['itemFormElValue'] = $s_al->getAbstractUid();
		}
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
	
		// set configuration
		$config['type'] = 'select';
		$config['size'] = 1;
		$config['maxitems'] = 1;
		$config['form_type'] = 'select';
	
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

/// \todo: move to pagezone
/// \todo: correct sorting: negative paragraph at the bottom
	public static function collectExtras(tx_newspaper_PageZone $pz) {
		$extra = $pz->getExtras();
		
		$data = array();
		
		for ($i = 0; $i < sizeof($extra); $i++) {
			
			//	don't display extras for which attribute gui_hidden is set
			if ($extra[$i]->getAttribute('gui_hidden')) continue;
			
			$extra_data = array(
				'extra_type' => $extra[$i]->getTitle(),
				'uid' => $extra[$i]->getExtraUid(),
				'title' => $extra[$i]->getDescription(), 
				'origin_placement' => $extra[$i]->isOriginExtra(),
				'origin_uid' => $extra[$i]->getOriginUid(),
				'concrete_table' => $extra[$i]->getTable(),
				'concrete_uid' => $extra[$i]->getUid(),
				'inherits_from' =>  $pz->getExtraOriginAsString($extra[$i]),
				'pass_down' => $extra[$i]->getAttribute('is_inheritable'),
				'notes' => $extra[$i]->getAttribute('notes'),
				'template_set' => $extra[$i]->getAttribute('template_set'),
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
			
			// render html dropdown and add to array
			$extra_data['template_set_HTML'] = tx_newspaper_BE::createTemplateSetDropdown('tx_newspaper_extra', $extra_data['uid'], $extra_data['template_set']);
			
			$data[] = $extra_data;

		}
		return $data;
	} 
	
	/// render dummy field for kicker, title and teaser in order to place these 3 field in 1 row (in a palette)	
	function renderArticleKickerTtitleTeaser($PA, $fobj) {
//t3lib_div::devlog('renderArticleKickerTtitleTeaser()', 'newspaper', 0, array('PA' => $PA));
		return '';	
	}
	/// render dummy field for kicker, title and teaser in list views in order to place these 3 field in 1 row (in a palette)	
	function renderArticleKickerTtitleTeaserForListviews($PA, $fobj) {
//t3lib_div::devlog('renderArticleKickerTtitleTeaser()', 'newspaper', 0, array('PA' => $PA));
		return '';	
	}
	

/// function to render extras (article or pagezone_page)
/// \todo: move locallang and smarty templates from mod3 to res/be/...
	function renderExtraInArticle($PA, $fobj) {
		// create article
		$article = new tx_newspaper_Article(intval($PA['row']['uid']));
t3lib_div::devlog('e in a', 'np', 0, array($PA, $fobj, $article, $article->getAbstractUid()));
		return self::renderBackendPageZone($article, false);
	}

	public static function renderBackendPageZone(tx_newspaper_PageZone $pz, $show_levels_above=false, $ajax_reload=false) {
		global $LANG;

		$data = array();
		$extra_data = array();

		/// add upper level page zones and extras, if any
		if ($show_levels_above) {
			$pz_up = array_reverse($pz->getInheritanceHierarchyUp(false));
			for ($i = 0; $i < sizeof($pz_up); $i++) {
				$data[] = self::extractData($pz_up[$i]);
				$extra_data[] = tx_newspaper_BE::collectExtras($pz_up[$i]);
			}
		}

		$is_concrete_article = 0; // init
		/// add current page zone and extras
		$data[] = self::extractData($pz); // empty array if concrete article
		$extra_data[] = tx_newspaper_BE::collectExtras($pz);

//t3lib_div::devlog('extras in article (def/concr)', 'newspaper', 0, $data);
/// \todo: can't that be checked nicer???
		if (sizeof($data[0]) > 0) { // if concrete article: $data[0] = emtpy; 
			// so it's no concrete article (= default article or pagezone_page)
			
			$s = $pz->getParentPage()->getParentSection();
			$pages = $s->getSubPages(); // get activate pages for current section
			$pagetype = array();
			for ($i = 0; $i < sizeof($pages); $i++) {
				$pagetype[] = $pages[$i]->getPageType(); 
			}
			
			$pagezones = $pz->getParentPage()->getPageZones(); // get activate pages zone for current page
			$pagezonetype = array();
			for ($i = 0; $i < sizeof($pagezones); $i++) {
				$pagezonetype[] = $pagezones[$i]->getPageZoneType(); 
			}
			$data[0]['article_id'] = -1; // only needed for concrete article
		} else {
			$is_concrete_article = 1; // render list of extras for a concrete article
			$data[0]['pagezone_id'] = $pz->getAbstractUid(); // store pz_uid for backend buttons usage
			$data[0]['article_id'] = $pz->getUid(); // store article uid for backend buttons usage (edit)
		}
//t3lib_div::devlog('render Extra on pz - pz, data', 'newspaper', 0, array('pz' => $pz, 'data' => $data));


		// if concrete article: add shortcuts for missing should-have and must-have extras
		$shortcuts = $is_concrete_article? $pz->getMissingDefaultExtras() : array();
//t3lib_div::devlog('ex in a: shortcuts', 'newspaper', 0, array($shortcuts));


		// get a smarty object
 		$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod3/'));

		$label['show_levels_above'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_show_levels_above', false);
		$label['show_visible_only'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_show_visible_only', false);
		$label['pagetype'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_pagetype', false);
		$label['pagezonetype'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_pagezonetype', false);
		$label['pagezone_inheritancesource'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:pagezone_inheritancesource', false);
		$label['pagezone_inheritancesource_upper'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:pagezone_inheritancesource_upper', false);
		$label['pagezone_inheritancesource_none'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:pagezone_inheritancesource_none', false);
		$message['pagezone_empty'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_pagezone_empty', false);

		$smarty->assign('LABEL', $label);
		$smarty->assign('MESSAGE', $message);
		$smarty->assign('DATA', $data);
		$smarty->assign('PAGETYPE', $pagetype);
		$smarty->assign('PAGEZONETYPE', $pagezonetype);
		$smarty->assign('SHOW_LEVELS_ABOVE', $show_levels_above);
		$smarty->assign('DUMMY_ICON', tx_newspaper_BE::renderIcon('gfx/dummy_button.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_new_top', false)));
		$smarty->assign('IS_CONCRETE_ARTICLE', $is_concrete_article);
		$smarty->assign('IS_CONCRETE_ARTICLE_RELOAD', $ajax_reload);

		if (!$is_concrete_article) {
			// add possible inheritance sources for this page zone
			$pp = $pz->getPossibleParents(true);
			$page_name = array();
			for ($i = 0; $i < sizeof($pp); $i++) {
				if (false) {
					// this is the current page,, so remove from array
					unset($pp[$i]);
				} else {
					// get name of page
					$page_name[] = $pp[$i]->getParentPage()->getPageType()->getAttribute('type_name'); // can't be accessed with smarty
				}
			}
//t3lib_div::devlog('inh from', 'newspaper', 0, array($pp, $page_name));
			$smarty->assign('INHERITANCESOURCE', $pp);
			$smarty->assign('INHERITANCESOURCENAME', $page_name);
		}

		/// "new to top" buttons vary for pagezone_page (new to top) and article (new extra, set pos and paragraph in form)
		if ($data[0]['pagezone_type'] instanceof tx_newspaper_article && $data[0]['pagezone_type']->getAttribute('is_article') == 0) {
			$smarty->assign('NEW_TOP_ICON', tx_newspaper_BE::renderIcon('gfx/new_record.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_new_top', false)));
		} else {
			$smarty->assign('NEW_TOP_ICON', tx_newspaper_BE::renderIcon('gfx/new_record.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_new_extra', false)));
		}

		
	
		// pagezones are rendered by a separate smarty template - because 2 versions (pagezone_page or article) can be rendered
		$smarty_pz = self::getPagezoneSmartyObject();
		$smarty_pz->assign('DEBUG_OUTPUT', DEBUG_OUTPUT);
		$smarty_pz->assign('ADMIN', $GLOBALS['BE_USER']->isAdmin());
		$pagezone = array();
		for ($i = 0; $i < sizeof($extra_data); $i++) {
			$smarty_pz->assign('DATA', $data[$i]); // so pagezone uid is available
			$smarty_pz->assign('IS_CONCRETE_ARTICLE', $is_concrete_article);
			$smarty_pz->assign('USE_TEMPLATE_SETS', tx_newspaper::USE_TEMPLATE_SETS); // are template set dropdowns visible or not
			if (!$is_concrete_article && $data[$i]['pagezone_type']->getAttribute('is_article') == 0) {
				if (sizeof($extra_data[$i]) > 0) {
					// render pagezone table only if extras are available 
					$smarty_pz->assign('EXTRA_DATA', $extra_data[$i]);
					$pagezone[$i] = $smarty_pz->fetch('mod3_pagezone_page.tmpl');
				} else {
					$pagezone[$i] = false; // message "no extra so far" will be displayed in mod3.tmpl
				}
			} else {
				// needed for concrete articles
				$smarty_pz->assign('NEW_TOP_ICON', tx_newspaper_BE::renderIcon('gfx/new_record.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_new_top', false)));
				
				$smarty_pz->assign('SHORTCUT_DEFAULTEXTRA_ICON', tx_newspaper_BE::renderIcon('gfx/new_record.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_new_defaultextra_in_article', false)));
				$smarty_pz->assign('SHORTCUT_NEWEXTRA_ICON', tx_newspaper_BE::renderIcon('gfx/new_file.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_new_extra_in_article', false)));
	
				$tmp = self::processExtraDataForExtraInArticle($extra_data[$i]);
				$smarty_pz->assign('EXTRA_DATA', $tmp);
				$smarty_pz->assign('SHORTCUT', $shortcuts); // add array with shortcut list
				$smarty_pz->assign('MESSAGE', $message);
				$pagezone[$i] = $smarty_pz->fetch('mod3_pagezone_article.tmpl'); // whole pagezone
			}
		}

		$smarty->assign('PAGEZONE', $pagezone);

		// admins might see a little more ...
		$smarty->assign('ADMIN', $GLOBALS['BE_USER']->isAdmin());

		return $smarty->fetch('mod3.tmpl');
	}

    public function renderTagControlsInArticle($PA, $fobj) {
        $articleId = $PA['row']['uid'];
        $obj = new t3lib_TCEforms();
        $PA['fieldConf']['config']['size'] = 4;
        $PA['fieldConf']['config']['foreign_table'] = 'tx_newspaper_tag';
        $PA['fieldConf']['config']['form_type'] = 'select';
        $fld = $obj->getSingleField_typeSelect('tx_newspaper_article', 'tags' ,$PA['row'], $PA);

        //inset input field
        $pattern = '<select name="data\[tx_newspaper_article\]\['.$articleId.'\]\[tags\]_sel.*</select>';
        $with='<input type="text" id="autocomplete" name="autocomplete_parameter" /><span id="indicator1" style="display: none"><img src="/typo3_base/typo3/gfx/spinner.gif" alt="Working..." /></span><div id="autocomplete_choices" class="autocomplete"></div>';
        $fld = $this->replaceIncludingEndOfLine($fld, $with, $pattern);
        return $this->getFindTagsJs($articleId).$fld;
    }

    /**
     * @access private
     * @param  $what string that will be searched
     * @param  $with string  that will be inserted
     * @param  string $pattern Regexp
     * @param bool $reinsertMatch if true (default) $with will be inserteted before the match which will be inserted as well. 
     * @return replaced text or complete text if no match was found
     */
    private function replaceIncludingEndOfLine($what, $with, $pattern, $reinsertMatch = true) {
        $newText = str_replace("\r\n","\n",$what);
        $newText = str_replace("\n","\r",$newText);
        // convert blank lines too
        $newText= preg_replace("/\n{2,}/","\r\r",$newText);
        $toReplace = '|('.$pattern.')|m'; // with 'm' option . matches EOL  
        preg_match($toReplace, $newText, $matches);
        $hasMatches = (count($matches) > 0);
        if($hasMatches) {
            if($reinsertMatch) {
                $fld = preg_replace($toReplace, $with.$matches[0], $newText);
            } else {
                $fld = preg_replace($toReplace, $with, $newText);
            }
        }

        return $hasMatches ? $fld : $what;
    }

    public function getArticleTags(&$params, &$pObj) {
//        t3lib_div::devLog('getArticleTags', 'be', 0, array('params' => $params, 'pObj' => $piObj) );
        if(!$params['row']['uid']) {
            throw new tx_newspaper_Exception('Article Uid not passed in');
        }
        $articleID = $params['row']['uid'];
        $article = new tx_newspaper_Article($articleID);
        $tags = $article->getTags();
        $items = array();
        foreach($tags as $tag) {
            $items[] = array($tag->getAttribute('tag'), $tag->getUid(), '');
        }
//        t3lib_div::devLog('getArticleTags--items', 'be', 0, array('tags' => $items));
        $params['items'] = $items;
    }



    private function getFindTagsJs($articleId) {
        return <<<JSCODE
    <script language="JavaScript">
        var MyCompleter = Class.create(Ajax.Autocompleter, {

              onComplete: function(\$super, request) {
                var serverChoices = request.responseText;
                var currentChoice = "<ul><li>" + this.getToken() + "<" + "/li>";
                var allChoices = serverChoices.replace(/<ul>/, currentChoice);
                request.responseText = allChoices;

        });
    document.observe("dom:loaded", function() {
        $$('[name="data[tx_newspaper_article][$articleId][tags]_sel"]')[0].hide();
        var path = window.location.pathname;
        var test = path.substring(path.lastIndexOf("/") - 5);
        if (test.substring(0, 6) == "typo3/") {
            path = path.substring(0, path.lastIndexOf("/") - 5); // -5 -> cut of "typo3"
        } else if (path.indexOf("typo3conf/ext/newspaper/") > 0) {
            path = path.substring(0, path.indexOf("typo3conf/ext/newspaper/"));
        }
        new MyCompleter("autocomplete", "autocomplete_choices", path + 'typo3conf/ext/newspaper/mod1/index.php', {
            paramName: 'search',
            method: 'get',
            parameters: 'param=tag-suggest',
            indicator: 'indicator1',
            frequency: 0.1,
            afterUpdateElement : insertTag
        });
     });

     function insertTag(currInput, selectedElement) {
        if(!selectedElement.id) {
            //neuen tag einfügen
            new top.Ajax.Request(path +  'typo3conf/ext/newspaper/mod1/index.php', {
                    method: 'get',
                    parameters: {param : 'tag-insert', tag : selectedElement.innerHTML},
                    onSuccess: function(data) { selectedElement.id = data.responseText; insertTag(null, selectedElement); }
                });
        } else {
            setFormValueFromBrowseWin('data[tx_newspaper_article][$articleId][tags]',selectedElement.id, selectedElement.innerHTML); TBE_EDITOR.fieldChanged('tx_newspaper_article','$articleId','tags','data[tx_newspaper_article][$articleId][tags]');
        }
        return false;
     }
       </script>
JSCODE;

}

    public function renderTagsInArticle($PA, $fobj) {
        $obj = new t3lib_TCEforms();
//        unset($PA['fieldConf']['config']['internal_type']);
        $PA['fieldConf']['config']['size'] = 4;
        $PA['fieldConf']['config']['foreign_table'] = 'tx_newspaper_tag';        
        $PA['fieldConf']['config']['form_type'] = 'select';
//        $PA['fieldConf']['config']['renderMode'] = 'singlebox';
//        $PA['fieldConf']['config']['items'] = array( array('name', 'value') );
        $fld = $obj->getSingleField_typeSelect('tx_newspaper_article', 'tags' ,$PA['row'], $PA);
        $s=str_replace("\r\n","\n",$fld);
        $s=str_replace("\n","\r",$s);
        // Don't allow out-of-control blank lines
        $fld=preg_replace("/\n{2,}/","\r\r",$s);
        $toReplace = '|<td valign="top" class="thumbnails">.*<\/select><\/td>|m';
        $fld = preg_replace($toReplace, '<td valign="top"><input id="tag_input" type="text" onkeyup="findTags(\'tag_input\')"/></td>', $fld);
        return $this->getFindTagsJs().$fld;
    }


    private function getFindTagsJs() {
        return <<<JSCODE
    <script language="JavaScript">
    function findTags(inputId) {
        var request = new top.Ajax.Request(
        top.path + 'typo3conf/ext/newspaper/mod1/index.php',
        {
            method: 'get',
            parameters: {tag : $(inputId).value, getTag: 'true'},            
            onSuccess: function(e) { $(inputId) = e.responseText  }
        }
        );
       }
       </script>
JSCODE;

}

	/// read data for non concrete article pagezones
	private static function extractData(tx_newspaper_PageZone $pz) {
		if (!$pz || !($pz->getUid())) {
			return array(); // no data needed article was newly created in t3 list module
		}

		if ($pz instanceof tx_newspaper_article && $pz->getAttribute('is_template') == 0) { 
			return array(); // no data needed if concrete article
		}
		
		$s = $pz->getParentPage()->getParentSection();
		return array(
				'section' => array_reverse($s->getSectionPath()), 
				'page_type' => $pz->getParentPage()->getPageType(),
				'page_id' => $pz->getParentPage()->getUid(),
				'pagezone_type' => $pz->getPageZoneType(),
				'pagezone_id' => $pz->getPagezoneUid(),
				'pagezone_concrete_id' => $pz->getUid(),
				'inherits_from' => $pz->getAttribute('inherits_from')
			);
	}

	private static function getPagezoneSmartyObject() {
		global $LANG;
	
		$label['extra'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_extra', false);
		$label['show'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_show', false);
		$label['pass_down'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_pass_down', false);
		$label['inherits_from'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_inherits_from', false);
		$label['commands'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_commands', false);
		$label['extra_delete_confirm'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_delete_confirm', false);
		$label['paragraph'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_paragraph', false);
		$label['notes'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_notes', false);
		$label['templateset'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_templateset', false);
		$label['shortcuts'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_shortcuts', false);
	
		$smarty_pz = new tx_newspaper_Smarty();
		$smarty_pz->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod3/'));
	
		$smarty_pz->assign('LABEL', $label);
	
		$smarty_pz->assign('SAVE_ICON', tx_newspaper_BE::renderIcon('gfx/savedok.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_save_extra', false)));
		$smarty_pz->assign('UNDO_ICON', tx_newspaper_BE::renderIcon('gfx/undo.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_undo_extra', false)));
	
		$smarty_pz->assign('HIDE_ICON', tx_newspaper_BE::renderIcon('gfx/button_hide.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_hide', false)));
		$smarty_pz->assign('UNHIDE_ICON', tx_newspaper_BE::renderIcon('gfx/button_unhide.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_unhide', false)));
		$smarty_pz->assign('EDIT_ICON', tx_newspaper_BE::renderIcon('gfx/edit2.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_edit_extra', false)));
		$smarty_pz->assign('MOVE_UP_ICON', tx_newspaper_BE::renderIcon('gfx/button_up.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_move_up', false)));
		$smarty_pz->assign('MOVE_DOWN_ICON', tx_newspaper_BE::renderIcon('gfx/button_down.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_move_down', false)));
		$smarty_pz->assign('NEW_BELOW_ICON', tx_newspaper_BE::renderIcon('gfx/new_record.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_new_below', false)));
		$smarty_pz->assign('DELETE_ICON', tx_newspaper_BE::renderIcon('gfx/garbage.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_delete', false)));
//		$smarty_pz->assign('REMOVE_ICON', tx_newspaper_BE::renderIcon('gfx/selectnone.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_delete', false)));
		$smarty_pz->assign('EMPTY_ICON', '<img src="clear.gif" width=16" height="16" alt="" />');
	
		return $smarty_pz;
	}

	private static function processExtraDataForExtraInArticle($extra_data) {
	
		if (sizeof($extra_data) == 0) {
			// message "no extra so far" shound be rendered in smarty template
			return false;
		}
			
		// prepare bg color
		$para = false; // init with false, so first paragraph can be identified
		$bg = 1;
		for ($i = 0; $i < sizeof($extra_data); $i++) {
			if (intval($extra_data[$i]['paragraph']) !== $para) {
				$para = intval($extra_data[$i]['paragraph']); // store new paragraph
				$bg = ($bg == 1)? 0 : 1; // switch bg type
			}
			$extra_data[$i]['bg_color_type'] = $bg;
		}
		return $extra_data;
	
	}




















/// workflow logging functions

	function getWorkflowButtons($PA, $fobj) {
		global $LANG;
//t3lib_div::devlog('getWorkflowButtons()', 'newspaper', 0, array('PA[row]' => $PA['row']));
		
		$hidden = $PA['row']['hidden'];
		$workflow = intval($PA['row']['workflow_status']);
//t3lib_div::devlog('getWorkflowButtons()', 'newspaper', 0, array('workflow' => $workflow, 'hidden' => $hidden));

		// create hidden field to store workflow_status (might be modified by JS when workflow buttons are used)
		$html = '<input id="workflow_status" name="workflow_status" type="hidden" value="' . $workflow . '" />';
		$html .= '<input name="workflow_status_ORG" type="hidden" value="' . $workflow . '" />';
		
		// if hidden_status equals -1, the hidden status wasn't changed by hide/publish button
		// if hidden_status DOES NOT equal -1, the hide/publish button was pressed, so IGNORE the value of the "hidden" field
		$html .= '<input id="hidden_status" name="hidden_status" type="hidden" value="-1" />'; // init with -1

		// add javascript \todo: move to external file
		$html .= '<script language="javascript" type="text/javascript">
function changeWorkflowStatus(role, hidden_status) {
	role = parseInt(role);
	hidden_status = parseInt(hidden_status);
	if (role == ' . NP_ACTIVE_ROLE_EDITORIAL_STAFF . ' || role == ' . NP_ACTIVE_ROLE_DUTY_EDITOR . ' || role == ' . NP_ACTIVE_ROLE_NONE . ') {
		document.getElementById("workflow_status").value = role; // valid role found
	}
	document.getElementById("hidden_status").value = hidden_status;
//alert(document.getElementById("hidden_status").value);
	return false;
}
</script>
';

		// buttons to be displayed in article backend
		$button = array(); // init with false ...
		$button['hide'] = false;
		$button['publish'] = false; // show
		$button['check'] = false;
		$button['revise'] = false;
		$button['place'] = false;
		// hide or publish button is available for every workflow status
		if (!$hidden) {
			$button['hide'] = tx_newspaper_workflow::isFunctionalityAvailable('hide');
		} else {
			$button['publish'] = tx_newspaper_workflow::isFunctionalityAvailable('publish');
		}
		switch($workflow) {
			case NP_ACTIVE_ROLE_EDITORIAL_STAFF: 
				// active role: editor (Redakteur)
				$button['check'] = tx_newspaper_workflow::isFunctionalityAvailable('check');
				$button['place'] = tx_newspaper_workflow::isFunctionalityAvailable('place');
			break;
			case NP_ACTIVE_ROLE_DUTY_EDITOR:
				// active role: duty editor (CvD)
				$button['revise'] = tx_newspaper_workflow::isFunctionalityAvailable('revise');
				$button['place'] = tx_newspaper_workflow::isFunctionalityAvailable('place');

			break;
			case NP_ACTIVE_ROLE_NONE:
				// active role: none
				$button['check'] = tx_newspaper_workflow::isFunctionalityAvailable('check');
				$button['revise'] = tx_newspaper_workflow::isFunctionalityAvailable('revise');
				$button['place'] = tx_newspaper_workflow::isFunctionalityAvailable('place');
			break;
//	deprecated		case 2: // \todo: how to call placement form???
//				// active role: no one (the article has left the workflow)
//				$button['check'] = tx_newspaper_workflow::isFunctionalityAvailable('check');
//				$button['revise'] = tx_newspaper_workflow::isFunctionalityAvailable('revise');
//				$button['place'] = tx_newspaper_workflow::isFunctionalityAvailable('place');

			default:
				t3lib_div::devlog('getWorkflowButtons() - unknown workflow status', 'newspaper', 3, array('PA' => $PA, 'workflow_status' => $workflow));
		}
//t3lib_div::devlog('button', 'newspaper', 0, array('hidden' => $hidden, 'workflow' => $workflow, 'button' => $button));

		$html .= $this->renderWorkflowButtons($hidden, $button);
		
		/// add workflow comment field (using smarty)
 		$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array(PATH_typo3conf . 'ext/newspaper/res/be/templates'));

		$html .= $smarty->fetch('workflow_comment.tmpl');
		
		$html .= tx_newspaper_workflow::getJavascript();
		$html .= tx_newspaper_workflow::renderBackend('tx_newspaper_article', $PA['row']['uid']);

		return $html;
	}

	/** \param $hidden
	 *  \param $button array stating (boolean) if the button for the various states should be displayed
	 */
	private function renderWorkflowButtons($hidden, $button) {
		global $LANG;
		
		$content = '';

//t3lib_div::devlog('renderWorkflowButtons', 'newspaper', 0, array('button' => $button));	
		
		/// just save (and don't close the form)
		$content .= $this->renderWorkflowButton(false, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_save', false), -1, true);
		
		/// hide / publish
		if (!$hidden && $button['hide']) {
			$content .= $this->renderWorkflowButton(false, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_hide', false), $hidden);
		} elseif ($hidden && $button['publish']) {
			$content .= $this->renderWorkflowButton(false, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_publish', false), $hidden);
		}
		$content .= '<br />';
		
		/// check / revise / place
		if ($button['check']) {
			$content .= $this->renderWorkflowButton(NP_ACTIVE_ROLE_DUTY_EDITOR, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_check', false), -1);
			if (!$hidden && $button['hide'])
				$content .= $this->renderWorkflowButton(NP_ACTIVE_ROLE_DUTY_EDITOR, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_check_hide', false), $hidden);
			elseif ($hidden && $button['publish'])
				$content .= $this->renderWorkflowButton(NP_ACTIVE_ROLE_DUTY_EDITOR, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_check_publish', false), $hidden);
			$content .= '<br />';
		}
		if ($button['revise']) {
			$content .= $this->renderWorkflowButton(NP_ACTIVE_ROLE_EDITORIAL_STAFF, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_revise', false), -1);
			if (!$hidden && $button['hide'])
				$content .= $this->renderWorkflowButton(NP_ACTIVE_ROLE_EDITORIAL_STAFF, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_revise_hide', false), $hidden);
			elseif ($hidden && $button['publish'])
				$content .= $this->renderWorkflowButton(NP_ACTIVE_ROLE_EDITORIAL_STAFF, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_revise_publish', false), $hidden);
			$content .= '<br />';
		}
// deprecated, \todo: how to call placement form???
//		if (NP_SHOW_PLACE_BUTTONS) {
//			// hide place buttons until opening placement form feature is implemented
//			if ($button['place']) {
//				$content .= $this->renderWorkflowButton(2, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_place', false), -1);
//				if (!$hidden && $button['hide'])
//					$content .= $this->renderWorkflowButton(2, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_place_hide', false), $hidden);
//				elseif ($hidden && $button['publish'])
//					$content .= $this->renderWorkflowButton(2, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_place_publish', false), $hidden);
//				$content .= '<br />';
//			}
//		}

		return $content;
	}
	
	/** \param $new_role if false, the role hasn't changes, else new role 
	 *  \param $title Title for the button
	 *  \param $hidden Specifies the hidden sdtatus of the current article
	 *  \param $overWriteNoCloseConstValue: overwrited the const setting (NP_ARTICLE_WORKFLOW_NOCLOSE), if set to true, a save (plus whatever) button (without closing the form) is rendered
	 */
	private function renderWorkflowButton($new_role, $title, $hidden, $overWriteNoCloseConstValue=null) {
		$hidden = intval(!$hidden); // negate first (button should toggle status); intval then, so js can handle the value
		if ($new_role !== false) {
			$js = 'changeWorkflowStatus(' . intval($new_role) . ', ' . $hidden . ')';
		} else {
			$js = 'changeWorkflowStatus(-1, ' . $hidden . ')'; 
		}
		
		$html = $title . '<input style="margin-right:20px;" title="' . $title . '"'; 
		if (NP_ARTICLE_WORKFLOW_NOCLOSE || $overWriteNoCloseConstValue == true) {
			// don't close after saving (for "just save" button or for test purposes)
			$html .= 'name="_savedok" src="sysext/t3skin/icons/gfx/savedok.gif" ';
		} else {
			// live version, save and close (and add onclick js)
			$html .= ' onclick="' . $js . '" ';
			$html .= 'name="_saveandclosedok" src="sysext/t3skin/icons/gfx/saveandclosedok.gif" ';			
		}
		$html .= 'width="16" type="image" height="16" class="c-inputButton"/>';

		return $html;
	}






	/// get html for this icon (may include an anchor) 
	/** \param $image path to icon
	 *  \param $id if set, $id will be inserted as an html id
	 *  \param $title title for title flag of img
	 *  \param $ahref 
	 *  \param $replaceWithCleargifIfEmpty if set to true the icon is replaced with clear.gif, if $ahref is empty
	 *  \return String <img ...> or <a href><img ...></a> (if linked)
	 */
	public static function renderIcon($image, $id, $title='', $ahref='', $replaceWithCleargifIfEmpty=false, $width=16, $height=16) {

		$width = intval($width)? intval($width) : 16;
		$height = intval($height)? intval($height) : 16;

		if ($id) {
			$id = ' id="' . $id . '" '; // if id is set, set build attribute id="..."
		}

		$backPath = tx_newspaper::getAbsolutePath() . 'typo3/'; // build back path
		if (substr($backPath, 0, 1) != '/') {
			$backPath = '/' . $backPath;
		}
		if ($ahref == '' && $replaceWithCleargifIfEmpty) {
			// hide icon (= replace with clear.gif)
			$html = '<img' . $id . t3lib_iconWorks::skinImg($backPath, 'clear.gif', 'width="' . $width . '" height="' . $height . '"') . ' title="' . $title . '" alt="" />';
		} else {
			// show icon
			$html = '<img' . $id . t3lib_iconWorks::skinImg($backPath, $image) . ' title="' . $title . '" alt="" />';
		}
		if ($ahref)
			return $ahref . $html . '</a>'; // if linked wrap in link
		return $html; // return image html code

	}



	/**
	 * add javascript and css files needed for display mode (adds to $GLOBALS['TYPO3backend'])
	 * called by hook $GLOBALS['TYPO3_CONF_VARS']['typo3/backend.php']['additionalBackendItems'][]
	 */
	public static function addAdditionalScriptToBackend() {
		$GLOBALS['TYPO3backend']->addJavascriptFile(t3lib_extMgm::extRelPath('newspaper') . 'res/be/util.js');
		switch(self::getExtraBeDisplayMode()) {
			case BE_DISPLAY_MODE_IFRAME:
				self::$backend_files_added = true; // nothing to add for iframe mode
			break;
			case BE_DISPLAY_MODE_SUBMODAL:
				// add modalbox js to top (so modal box can be displayed over the whole backend, not only the content frame)
				$GLOBALS['TYPO3backend']->addJavascriptFile(t3lib_extMgm::extRelPath('newspaper') . 'contrib/subModal/newspaper_subModal.js');
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




	/// Generates some dummy content based on "Lorem ipsum"
	/** \param $numberOfParagrahs Number of Paragraphs to render
	 *  \param $wrapInP If set to true the paragraphs are wrapped in <p>...</p>
	 *  \param $useShortVersion if set to true if short paragraph is used, a longer paragraph text else
	 *  \return String with dummy content
	 */
	public static function getLoremIpsum($numberOfParagrahs=1, $wrapInP=false, $useShortVersion=true) {
		$numberOfParagrahs = intval($numberOfParagrahs);
		$loremLong = array(
			'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer ullamcorper feugiat pretium. Nullam id leo neque. Pellentesque at facilisis eros. Sed ullamcorper cursus massa, non vehicula nulla cursus vitae. Mauris vehicula, mi et elementum mattis, dui leo rhoncus est, ac ultrices nulla massa eu justo. Vivamus eros purus, pellentesque quis eleifend ut, hendrerit nec ligula. Integer aliquam hendrerit lacus, id vehicula tortor fringilla nec. Cras nibh felis, suscipit a consequat ut, sodales vitae mauris. Sed at eros urna, in accumsan metus. Morbi et lorem sem. Vivamus quis fringilla libero. Aliquam aliquam, sem eu dignissim interdum, neque enim faucibus massa, a venenatis massa magna in massa. Sed vel justo justo. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nulla facilisi. Quisque porttitor cursus dolor, eu rhoncus ligula commodo at. Donec et sapien vel elit consequat elementum. Nam facilisis blandit ligula, nec consequat felis suscipit eu. Nullam dui magna, varius vel sodales non, ullamcorper eu ligula.',
			'Maecenas a augue eget odio hendrerit ullamcorper et sed lorem. Maecenas rhoncus congue porta. Nam adipiscing ligula ac mi blandit lacinia. Vivamus tortor ante, sodales quis vehicula eu, porttitor sit amet magna. Phasellus eu ante aliquet dui porta porta. Vestibulum mollis elementum neque, quis varius est elementum vitae. Ut libero leo, lobortis non blandit at, consectetur at arcu. Etiam adipiscing volutpat justo quis viverra. Nulla pretium, tortor non feugiat venenatis, purus nisl porta dolor, eu venenatis lorem lectus ac augue. Donec sollicitudin tristique gravida. Donec bibendum orci in tortor ullamcorper tristique. Suspendisse ac tortor pretium nisl consequat bibendum vitae sit amet lacus. Fusce eu ligula eu est elementum posuere ac sed nulla. Pellentesque ultrices dapibus faucibus. Nullam mollis ante quis metus vestibulum vulputate. Sed tempor, nibh in imperdiet pretium, justo ipsum fringilla mauris, sodales semper nisl orci in nisi. Nulla ultricies neque vel erat accumsan suscipit. Maecenas et dui nunc, ut suscipit lorem. Aenean viverra orci sit amet lectus malesuada ultricies. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.',
			'Aenean gravida convallis fermentum. Nulla posuere mauris in lacus vulputate nec dapibus erat vehicula. Duis risus enim, facilisis non dapibus sit amet, accumsan nec ligula. Quisque neque risus, pretium a bibendum id, sollicitudin vitae elit. Etiam iaculis viverra interdum. Praesent faucibus vehicula tortor eget accumsan. Vestibulum placerat odio neque, id ornare lorem. Sed lacinia ornare purus, quis mattis erat sagittis in. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Cras sed fermentum lectus. Fusce porta faucibus mi, a mollis quam fermentum eu. Ut sit amet arcu vel arcu congue pellentesque non id augue. Nam molestie vestibulum commodo. Vivamus rutrum quam a ipsum viverra nec blandit magna sodales. Phasellus rutrum magna eros. Pellentesque ante orci, egestas eget fringilla a, viverra nec neque. Nam facilisis consectetur aliquam. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.',
			'Proin placerat eros magna, sed interdum tortor. Morbi aliquam nisi sed urna vehicula sed fermentum augue interdum. Nulla rhoncus congue aliquam. Vestibulum pharetra leo vitae sapien blandit nec hendrerit augue dignissim. Phasellus semper mollis tortor vitae commodo. Mauris fermentum, metus sed rhoncus tempus, eros metus consequat eros, id consequat nisi enim ut nibh. Ut vestibulum felis non felis imperdiet congue. Maecenas ultrices hendrerit erat sit amet viverra. Quisque sed lectus nunc, posuere pretium odio. Proin semper ultricies sagittis. Vestibulum nisi est, euismod in tincidunt ac, tincidunt nec diam. Suspendisse lorem metus, porttitor id hendrerit vitae, auctor nec neque. Phasellus dapibus sodales augue ut vestibulum. Praesent dapibus dui in dolor aliquet auctor. Quisque ornare faucibus nisi, molestie tincidunt orci suscipit sagittis. Aliquam interdum ultricies mollis.',
			'Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Ut varius urna at mauris accumsan suscipit. Cras laoreet ultrices urna, et rhoncus metus faucibus sed. Maecenas ultrices erat eget sem congue laoreet. Aenean ligula lectus, gravida ut pharetra ac, tincidunt sit amet mi. Nullam in ullamcorper lectus. Etiam interdum ante vitae diam commodo quis semper mi pretium. Pellentesque quam ante, faucibus vitae venenatis sed, vestibulum ut justo. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Cras et arcu massa. In ultrices imperdiet justo quis faucibus. Proin quam neque, condimentum ut tincidunt quis, lobortis in quam.',
			'Vivamus nunc nulla, condimentum id tempus a, volutpat non sem. Nam adipiscing, orci nec pretium porta, dolor odio faucibus risus, eu auctor turpis est nec velit. Aliquam vitae aliquet elit. Proin cursus rhoncus neque non rutrum. Quisque id dui libero. Duis et ligula mauris, non elementum arcu. Nam non libero eu purus luctus laoreet. Nam venenatis tempus magna et accumsan. Phasellus lacinia iaculis imperdiet. Proin rutrum lobortis mi, nec eleifend nunc malesuada eget. Donec dignissim velit id lorem fringilla vehicula. Proin ligula diam, commodo eu venenatis sit amet, consectetur vel justo. Maecenas non eros quis neque blandit iaculis non eget metus. Integer varius leo id neque semper tempor. Donec facilisis erat vel risus pretium vel bibendum purus sagittis. Suspendisse diam eros, vestibulum nec rutrum nec, accumsan vel nisi.',
			'Sed magna libero, egestas et volutpat ac, faucibus eu lacus. In rhoncus gravida tellus porttitor pulvinar. Fusce lacinia nunc non felis lacinia pellentesque. Aenean lectus lacus, condimentum a vestibulum eu, ornare vitae tellus. Cras interdum, erat eget tincidunt blandit, mi odio dignissim eros, quis lobortis elit ipsum eget enim. Phasellus sollicitudin dolor at risus vestibulum tincidunt. Nulla sit amet lorem in dui tempor aliquet et quis nulla. Phasellus auctor eros sit amet nisl blandit a vestibulum tellus consequat. Nam pulvinar purus vitae tortor venenatis iaculis. Nulla rutrum odio tempus metus volutpat consectetur. Suspendisse pulvinar bibendum diam, a varius libero adipiscing ac. Phasellus pretium leo in orci porta elementum. Sed nec auctor turpis. Sed nec mauris sed ligula porta tristique nec eget libero. Vivamus aliquam mauris ac nunc euismod lacinia. Aenean tincidunt, orci at dapibus consequat, tortor risus suscipit turpis, eu luctus felis dolor vel dolor. In id tellus vel velit molestie molestie.',
			'Phasellus mattis, odio sed tempor convallis, enim neque elementum ipsum, nec mattis dolor nibh vehicula est. Vestibulum ultricies, nibh non eleifend aliquam, leo leo eleifend odio, commodo egestas nibh tellus at ligula. Vestibulum ut augue ut lorem scelerisque sollicitudin. In sed ante nisi. Suspendisse facilisis, massa nec pellentesque sagittis, lacus urna cursus turpis, pretium elementum dolor lectus in nibh. Aenean id egestas magna. Nullam sed eros ipsum, non consectetur mauris. Suspendisse vitae erat sit amet metus tincidunt vehicula. Vivamus sagittis ipsum vel tellus lobortis eu tempor metus suscipit. Curabitur congue, dolor quis scelerisque interdum, urna nunc dictum lacus, eu volutpat quam diam at nisi. Fusce eget ligula sed mi sagittis gravida. Nunc molestie enim vitae ipsum condimentum pulvinar. Pellentesque tempus, justo at cursus dapibus, urna nisl scelerisque purus, sit amet egestas turpis lacus sed nulla. Vestibulum interdum ultricies justo non malesuada. Suspendisse molestie libero non sem sodales facilisis. In aliquam consectetur eros et euismod. Integer vitae dapibus tellus. Duis quam urna, gravida a lobortis ut, mollis dictum erat. Integer bibendum sapien malesuada justo consectetur sed sodales sem dapibus.',
			'Nulla blandit lorem odio, sed molestie eros. Duis eget augue in augue ultricies faucibus. Proin in tellus nec tortor eleifend posuere. Sed scelerisque, nulla quis sodales aliquam, nunc lectus posuere mi, vel tempus urna mi a ipsum. Curabitur eu ipsum lacus, eget mattis massa. Aliquam semper malesuada felis, id ullamcorper libero accumsan in. Donec volutpat adipiscing hendrerit. Aenean ullamcorper porttitor enim ac pellentesque. Sed vestibulum feugiat lectus et euismod. In ut adipiscing est. Suspendisse vel ante non leo bibendum venenatis. Vivamus vulputate placerat nunc quis fringilla. Nulla vitae ligula purus, ut scelerisque risus. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Nunc malesuada imperdiet eros, eget vehicula tellus pulvinar vel. Maecenas scelerisque volutpat nibh nec molestie. Donec placerat ultrices metus at ultricies.',
			'Suspendisse euismod nulla quis dui cursus nec imperdiet tortor suscipit. Praesent placerat vehicula risus sagittis rhoncus. Nam pulvinar, neque nec scelerisque blandit, lorem elit sollicitudin ante, at mollis risus ligula sit amet ipsum. Integer pellentesque viverra urna sit amet placerat. Phasellus non mauris arcu. Nulla ut nunc sem, sit amet semper metus. In sagittis bibendum purus a sollicitudin. Phasellus commodo consectetur nibh cursus congue. Vestibulum et nisi ligula. Sed sed nibh in neque posuere fringilla eu id orci. Suspendisse potenti. Maecenas lobortis cursus lectus, congue fringilla nibh facilisis eu. Donec semper, tellus vel tristique ullamcorper, ligula justo hendrerit ante, eget gravida ipsum velit eu nibh. In posuere molestie lacus, ut condimentum lorem aliquam ac. Nunc odio erat, eleifend vel posuere sit amet, condimentum vel tortor. Maecenas eleifend, augue vel blandit porta, odio neque tempor erat, sed mollis sem sem convallis mi. In hac habitasse platea dictumst. Nunc adipiscing, elit eget ultricies tristique, leo velit adipiscing augue, in sollicitudin ipsum sapien vel nunc. Curabitur eget diam a odio pulvinar posuere.',
			'Etiam lacus nulla, fermentum vel pulvinar sed, placerat quis risus. Morbi eu lacus ac nisi dapibus vestibulum. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. In pulvinar porta turpis, a venenatis ante fringilla ac. Quisque a leo ut purus convallis lobortis. Cras feugiat feugiat augue, ut accumsan leo consectetur et. Aliquam vulputate dolor ut nulla dignissim suscipit. Morbi eu turpis ante. Curabitur ac arcu sed ante mollis porttitor sed id velit. Phasellus dapibus mauris mattis leo posuere condimentum. Duis venenatis iaculis bibendum. Morbi vulputate lorem vitae tellus ornare vitae congue tortor gravida. Aenean feugiat ligula a orci egestas porta. Aenean sed blandit libero. In hac habitasse platea dictumst. Curabitur sit amet diam ut magna volutpat venenatis sit amet nec diam. Curabitur dictum ante nec sem vulputate vehicula. Vestibulum rutrum, dui ac pellentesque ultrices, nibh orci vehicula neque, cursus tincidunt neque nulla id dui. Vivamus semper risus ut purus sagittis at sollicitudin felis iaculis. Donec semper metus non arcu ornare condimentum.',
			'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Quisque ac vehicula ipsum. Nunc pharetra venenatis facilisis. Aliquam tincidunt sodales dolor. Pellentesque ultrices, erat at vulputate porta, nisi quam pretium elit, cursus facilisis turpis purus eget mi. Morbi a augue vel ligula suscipit bibendum ut sit amet nisi. Mauris blandit augue tincidunt nulla vehicula facilisis. Duis ultrices ipsum vehicula neque dictum vel tristique tortor bibendum. Vestibulum enim nibh, tempus vitae feugiat eu, euismod id velit. Mauris tempor placerat tristique.',
			'Aenean vulputate orci id urna elementum ornare. Fusce adipiscing dapibus ipsum quis feugiat. Cras vulputate tellus eget nisl pharetra eu bibendum ante faucibus. Maecenas gravida pharetra pretium. Duis sed nulla libero, sed molestie risus. Phasellus purus erat, consectetur id posuere et, fringilla ut dolor. Maecenas eu dolor erat, egestas aliquet neque. Maecenas auctor suscipit libero, vitae laoreet dui euismod nec. Proin et leo mauris. Duis cursus, ligula sed tincidunt semper, ante turpis hendrerit metus, iaculis volutpat est nunc et est. Phasellus ullamcorper felis lacus, in viverra nisi. Phasellus vel sapien purus, ut sodales enim. Aenean sit amet neque tellus.',
			'Aliquam in ornare diam. Duis in lorem at nisi ultrices pretium ut sed dolor. Aenean pulvinar lectus cursus enim tempus convallis. Morbi accumsan lorem ac nulla semper laoreet. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Suspendisse non molestie mauris. Aliquam quam ante, tempor et suscipit in, pulvinar et lacus. Donec sed lorem nec tellus interdum ultrices a sit amet dui. Suspendisse aliquam nulla viverra orci sodales sit amet laoreet neque scelerisque. Pellentesque et blandit enim. Suspendisse et est risus, sit amet pulvinar est. Vivamus eleifend consectetur luctus. Aliquam ornare risus ut nibh fermentum interdum. Vestibulum fringilla rutrum velit, non fermentum risus ornare eu. Donec in odio vel ipsum adipiscing mattis sit amet nec quam. Proin eleifend ligula non elit posuere at iaculis nulla vulputate. Pellentesque ornare, neque eget adipiscing lacinia, dolor ante mattis enim, non iaculis odio nisl bibendum lectus. Pellentesque nisi enim, luctus eget laoreet vel, elementum sit amet sapien. Aenean malesuada consectetur erat non consequat.',
			'Sed elementum diam eget tellus aliquet ac pellentesque metus scelerisque. Nam elit elit, euismod id laoreet ut, imperdiet ac justo. Sed condimentum gravida nisl, nec luctus nunc blandit a. Fusce id tortor risus. Aliquam sit amet est in lectus commodo euismod in at elit. Suspendisse aliquet viverra rhoncus. Etiam pulvinar fermentum purus a interdum. Aenean quis dui quis augue bibendum fringilla venenatis sit amet erat. Sed sit amet erat enim, et mattis augue. Nulla varius ultricies tempor. Proin et neque vitae ligula bibendum dapibus. Aliquam vel quam id augue hendrerit hendrerit id vehicula neque. Sed at libero nec massa consectetur suscipit. Mauris laoreet congue dui, eu tempus metus volutpat id. Cras convallis metus id velit mattis ac condimentum erat vehicula. Etiam ornare tortor ac velit faucibus molestie.',
			'Sed pretium, quam quis venenatis pulvinar, felis eros sagittis sem, nec accumsan dui augue sed tortor. Aenean vitae vestibulum sem. Pellentesque vel pulvinar augue. Donec interdum sem vitae libero facilisis mattis. Proin semper luctus tellus nec feugiat. Vestibulum dignissim, massa sed imperdiet hendrerit, nisi purus pellentesque mi, non dictum neque tortor sit amet libero. Nam lobortis pellentesque interdum. Integer fringilla mattis nulla, non consequat mi pellentesque laoreet. Proin dui felis, rhoncus ac pellentesque in, imperdiet at libero. Proin consequat lacinia velit. Nunc lorem massa, malesuada commodo condimentum sed, congue non quam. Curabitur tempus nunc sit amet augue porttitor at bibendum neque egestas. Ut a felis massa.',
			'Vestibulum vitae odio odio, sed molestie felis. Nullam eget elit quis lacus laoreet facilisis vitae ac arcu. Quisque consectetur lorem vitae est aliquet egestas. Nulla a vestibulum leo. Fusce sed libero dolor. Nunc vehicula, odio sed blandit aliquam, purus arcu viverra augue, eu volutpat massa elit ac lectus. Nullam mattis diam eu massa viverra posuere. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Duis mauris urna, egestas ac sodales eget, sodales pulvinar tortor. Mauris est lacus, fringilla sed tincidunt a, suscipit id felis. Proin purus metus, posuere nec faucibus varius, mattis in sem. Mauris eu odio eget tellus posuere dictum feugiat id quam. Aliquam erat volutpat. In commodo tincidunt sapien, eu tristique turpis facilisis at. Duis hendrerit sem non lorem suscipit at fringilla urna pellentesque. Phasellus quis eleifend ipsum. Aliquam erat volutpat. Vestibulum elit nunc, egestas vitae ultricies a, pretium sit amet urna.',
			'Sed sapien mauris, rutrum sed adipiscing eget, accumsan sit amet neque. Nulla eu ipsum felis, vitae posuere metus. Fusce ante est, tempus ac pulvinar ac, auctor ac eros. Suspendisse potenti. Nulla facilisi. Praesent vel elit ut ligula suscipit sagittis. Nam imperdiet bibendum faucibus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas volutpat nulla a mauris ultricies luctus. Mauris quam mauris, ultrices ac cursus in, eleifend iaculis neque. In mattis ultrices congue. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos.',
			'Duis turpis sem, iaculis quis rutrum ac, malesuada quis neque. Fusce sed lorem enim. Suspendisse cursus, metus tempus bibendum tincidunt, turpis lacus pulvinar nulla, eget porttitor urna velit eu massa. Nulla interdum egestas est et congue. Proin eu lacus at justo semper blandit eget vitae dui. Mauris lobortis dui enim, eu ultrices turpis. Vivamus placerat nisl eget ligula dignissim vel facilisis felis aliquam. Quisque quis dui lorem, in pellentesque nibh. Pellentesque at leo nunc, nec ultrices lectus. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Donec consequat egestas commodo. Ut eget fermentum orci. Sed nunc nisl, euismod a suscipit sed, vehicula ut nibh. Vestibulum volutpat interdum consequat. Fusce in turpis diam.',
			'Donec pulvinar massa et sapien feugiat tempus. Maecenas lacus elit, pellentesque quis ullamcorper in, fermentum et nibh. Nam quis pharetra turpis. Pellentesque vulputate tellus ac lacus iaculis malesuada. Donec magna lectus, mattis ac pulvinar non, malesuada sit amet mi. Suspendisse potenti. Nunc et orci in nisl ultrices pretium. Suspendisse nulla mi, pulvinar vitae aliquet ac, suscipit vitae elit. Nullam orci odio, dictum eu porttitor eu, accumsan dapibus risus. Fusce tempus nisi id justo porta ultrices. Donec interdum quam ut magna facilisis vel imperdiet sapien placerat. Nunc lacinia sodales tortor ac egestas. Cras consectetur neque vel lectus molestie vel vulputate ante porttitor. Fusce adipiscing imperdiet lacus sed consequat. Proin id rutrum neque.',
			'Donec iaculis erat et ante facilisis dictum. Sed vitae elit orci, quis ultricies enim. Phasellus sed tellus auctor neque tincidunt mattis. Quisque sit amet leo metus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi eget orci non nibh blandit congue. Morbi porta tortor id nunc dapibus at venenatis augue vestibulum. Aenean at orci neque, eu gravida ipsum. Maecenas mattis, diam at eleifend vestibulum, tortor ipsum lobortis metus, a pulvinar ligula lorem vel arcu. Sed et lacinia risus.',
			'Mauris ut libero neque, et luctus odio. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Aliquam est orci, malesuada vel accumsan ut, rhoncus nec lorem. Nulla dapibus, libero vel ultrices faucibus, odio nisi ultricies enim, quis fermentum neque felis ut libero. Aliquam magna risus, molestie et fermentum sit amet, ultrices vitae ligula. Nam ipsum lectus, consectetur id feugiat et, ornare nec dui. Nam porta nunc vel magna ullamcorper accumsan. Nullam et metus quam, quis fringilla tellus. Proin sapien dolor, iaculis eget dictum non, congue vitae felis. Maecenas tempus dapibus metus condimentum egestas. Ut est nunc, egestas id aliquet in, dictum quis libero. Vivamus nec accumsan arcu. Phasellus interdum laoreet lacus, nec suscipit nunc pellentesque a. In ultricies, lorem ac sodales pretium, enim felis consectetur nibh, sollicitudin eleifend lorem odio sed purus. Nulla facilisi. Duis ligula turpis, porta nec tincidunt ut, tincidunt vel justo. Nam lacinia ornare dui. Suspendisse aliquam laoreet lorem, vel tempor magna porta sed. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Suspendisse feugiat velit sit amet lorem dignissim vel tempor nulla adipiscing.',
			'Aliquam erat volutpat. Proin egestas auctor tincidunt. Morbi eu commodo mi. Nulla quis felis eu dolor cursus blandit. Aliquam erat volutpat. In quis magna purus, consectetur commodo elit. Nulla libero leo, posuere in ornare et, bibendum blandit nibh. Suspendisse potenti. Fusce quis metus at massa varius gravida eget in eros. Aenean adipiscing tortor lacus, viverra tempus erat. Phasellus vitae purus elit. Nulla vulputate fringilla eleifend. Fusce quis est ante. Proin viverra, mi non dapibus luctus, tortor felis dignissim ante, pharetra tincidunt arcu nisl at est. Cras tincidunt suscipit mauris, quis elementum eros hendrerit eu. Vestibulum vitae tortor libero, sed tempus mauris. Maecenas non imperdiet dolor. Vestibulum vel neque velit, tincidunt malesuada ligula. Suspendisse accumsan, quam vel tincidunt tempus, erat tellus lacinia ligula, ac ultrices justo mauris at nisi.',
			'Quisque lacinia dolor sit amet nibh laoreet aliquet. Mauris quis tellus libero. Ut accumsan facilisis magna et fringilla. Integer lacinia mauris at arcu tempor tempor tempor ante consequat. In dapibus rutrum auctor. Pellentesque eget magna sem, sit amet consectetur nisl. Praesent lacinia feugiat faucibus. Praesent leo elit, interdum quis consequat nec, varius quis tortor. In convallis congue urna, a tristique est pharetra et. Pellentesque eu lectus id sapien lobortis accumsan sit amet sed tellus. Vestibulum viverra congue eros, et ullamcorper turpis ullamcorper ut. Donec convallis vulputate tellus, et porta neque pulvinar blandit.',
			'Fusce ac orci vestibulum tortor mollis ultrices. Sed dolor leo, pharetra quis placerat sit amet, porttitor vel eros. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Ut vestibulum bibendum mauris sit amet fermentum. In sed est mi, et mattis odio. Sed porta elit eu libero pulvinar consectetur. Vivamus tempor faucibus erat quis tincidunt. Phasellus ultricies nisl vitae magna tempor vehicula. Nullam sodales mattis purus a imperdiet. Pellentesque non metus ante. Nullam rhoncus accumsan odio commodo aliquam. Sed dapibus nibh at turpis convallis in commodo ante ultricies. In tincidunt orci sapien, in viverra tellus. Vestibulum posuere aliquet bibendum. Phasellus et ullamcorper felis. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed vulputate iaculis dapibus.', 	
		);
		$loremShort = array(
			'Integer ullamcorper feugiat pretium. Nullam id leo neque. Pellentesque at facilisis eros.',
			'Maecenas rhoncus congue porta. Nam adipiscing ligula ac mi blandit lacinia.',
			'Nulla posuere mauris in lacus vulputate nec dapibus erat vehicula.',
			'Morbi aliquam nisi sed urna vehicula sed fermentum augue interdum.',
			'Ut varius urna at mauris accumsan suscipit. Cras laoreet ultrices urna, et rhoncus metus faucibus sed.',
			'Nam adipiscing, orci nec pretium porta, dolor odio faucibus risus, eu auctor turpis est nec velit.',
			'In rhoncus gravida tellus porttitor pulvinar. Fusce lacinia nunc non felis lacinia pellentesque. ',
			'Vestibulum ultricies, nibh non eleifend aliquam, leo leo eleifend odio, commodo egestas nibh tellus at ligula.',
			'Proin in tellus nec tortor eleifend posuere. Sed scelerisque, nulla quis sodales aliquam, nunc lectus posuere mi, vel tempus urna mi a ipsum.',
			'Praesent placerat vehicula risus sagittis rhoncus. Nam pulvinar, neque nec scelerisque blandit, lorem elit sollicitudin ante, at mollis risus ligula sit amet ipsum. ',
			'Morbi eu lacus ac nisi dapibus vestibulum. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. ',
			'Quisque ac vehicula ipsum. Nunc pharetra venenatis facilisis. ',
			'Fusce adipiscing dapibus ipsum quis feugiat. Cras vulputate tellus eget nisl pharetra eu bibendum ante faucibus. ',
			'Duis in lorem at nisi ultrices pretium ut sed dolor. Aenean pulvinar lectus cursus enim tempus convallis. ',
			'Nam elit elit, euismod id laoreet ut, imperdiet ac justo. Sed condimentum gravida nisl, nec luctus nunc blandit a. ',
			'Aenean vitae vestibulum sem. Pellentesque vel pulvinar augue. Donec interdum sem vitae libero facilisis mattis. ',
			'Nullam eget elit quis lacus laoreet facilisis vitae ac arcu. Quisque consectetur lorem vitae est aliquet egestas. ',
			'Nulla eu ipsum felis, vitae posuere metus. Fusce ante est, tempus ac pulvinar ac, auctor ac eros. Suspendisse potenti. ',
			'Fusce sed lorem enim. Suspendisse cursus, metus tempus bibendum tincidunt, turpis lacus pulvinar nulla, eget porttitor urna velit eu massa. ',
			'Maecenas lacus elit, pellentesque quis ullamcorper in, fermentum et nibh. Nam quis pharetra turpis. ',
			'Sed vitae elit orci, quis ultricies enim. Phasellus sed tellus auctor neque tincidunt mattis. Quisque sit amet leo metus. ',
			'Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. ',
			'Proin egestas auctor tincidunt. Morbi eu commodo mi. Nulla quis felis eu dolor cursus blandit. Aliquam erat volutpat. ',
			'Mauris quis tellus libero. Ut accumsan facilisis magna et fringilla. Integer lacinia mauris at arcu tempor tempor tempor ante consequat. ',
			'Sed dolor leo, pharetra quis placerat sit amet, porttitor vel eros. ', 	
		);
		$content = array();
		for ($i = 0; $i < $numberOfParagrahs; $i++) {
			if ($useShortVersion) {
				$content[] = $loremShort[rand(0, sizeof($loremShort)-1)];
			} else {
				$content[] = $loremLong[rand(0, sizeof($loremLong)-1)];
			} 
		}
		for ($i = 0; $i < sizeof($content); $i++) {
			if ($wrapInP) {
				$content[$i] = '<p>' . $content[$i] . "</p>\n";
			} else {
				$content[$i] = $content[$i] . "\n";
			}
		}
		return implode('', $content);
	}

	
}

?>