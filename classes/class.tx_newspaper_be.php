<?php
/**
 *  \file class.tx_newspaper_be.php
 *
 *  \author Oliver Schr�der <newspaper@schroederbros.de>
 *  \date Feb 27, 2009
 */

/// function for adding newspaper functionality to the backend
class tx_newspaper_BE {
	
	private static $smarty = null;
	

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
					$pagezone_type_data[$i]['AJAX_DELETE_URL'] = 'javascript:deletePageZone(' . $section_uid . ', ' . $page_uid . ', ' . $active_pagezone->getAbtractUid() . ', \'' . addslashes($LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_check_delete_pagezone_in_page', false)) . '\');';
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
		
		$default_found = false;
		
		$templateset = tx_newspaper_smarty::getAvailableTemplateSets(new tx_newspaper_Section(1));
#t3lib_div::devlog('templ set', 'newspaper', 0, $template
/// \todo: use tx_newspaper_smarty::getAvailableTemplateSets() !
$templateset = array('rot', 'test', 'default', 'dummy');

		$params['items'][] = array('', ''); // empty entry -> templateset is inherited 
		$params['items'][] = array('default', 'default'); // default set is sorted to top of list, if not existing, this entry is removed later
		
		for ($i = 0; $i < sizeof($templateset); $i++) {
			if (strtolower($templateset[$i]) != 'default') {
				$params['items'][] = array($templateset[$i], $templateset[$i]);				
			} else {
				$default_found = true;
			}
		}
		
		if (!$default_found) {
			unset($params['default']); // remover entry 'default' (because there's no templateset default available)
		}
		
	} 




	function addArticlelistDropdownEntries(&$params, &$pObj) {
		$s = new tx_newspaper_Section(intval($params['row']['uid']));
		try {
			$al_active = $s->getArticleList();	
		} catch (tx_newspaper_EmptyResultException $e) {
t3lib_div::devlog('remove try/catch later', 'newspaper', 0); /// \todo
			$al_active = null;
		};

		$al_available = tx_newspaper_ArticleList::getRegisteredArticleLists();
		for ($i = 0; $i < sizeof($al_available); $i++) {
			if ($al_available[$i]->getUid() > 0) 
				$value = $al_available[$i]->getUid();
			else 
				$value = $al_available[$i]->getTable(); // -($i+1);
			$params['items'][] = array($al_available[$i]->getTitle(), $value);
		}
		
t3lib_div::devlog('al dropdown', 'newspaper', 0, $params);
	}






//// UNNEEDED UNNEEDED UNNEEDED UNNEEDED UNNEEDED
//
//	/// render article list form for section backend
//	/// either called by userfunc in be or ajax
//	public static function renderArticleList($PA, $fObj=null) {
//		global $LANG;
//		
//		if (strtolower(substr($PA['row']['uid'], 0, 3)) == 'new') {
//			/// new section record, so no "real" section uid available
//			return $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:message_section_not_saved_articlelist', false);
//		}
//		$section_uid = intval($PA['row']['uid']);
//
//		$al_available = tx_newspaper_ArticleList::getRegisteredArticleLists();
//
///// \todo: aus tca auslesen
//$config['type'] = 'select';
//$config['size'] = 1;
//$config['maxitems'] = 1;
//$config['itemsProcFunc'] = 'tx_newspaper_BE->addArticlelistDropdownEntries';
//$config['form_type'] = 'select';
//
//	$selItems = array();
//	$selItems[] = array('1', '2', '');
//	$selItems[] = array('4', '5', '');
//	#$nMV_label = isset($PA['fieldTSConfig']['noMatchingValue_label']) ? $this->sL($PA['fieldTSConfig']['noMatchingValue_label']) : '[ '.$this->getLL('l_noMatchingValue').' ]';
//	$nMV_label = '[ INVALID VALUE ("%s") ]';
//
//	$obj = new t3lib_TCEforms(); 
//
//	$out = $obj->getSingleField_typeSelect_single('tx_newspaper_section', 'articlelist', $PA, $PA['row'], $config, $selItems, $nMV_label);
//t3lib_div::devlog('al out', 'newspaper', 0, $out);
//	return $out;
//	}






















/// \todo: remove from class.tx_newspaper_extra_be.php!
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


	
}

?>