<?php
/**
 *  \file class.tx_newspaper_be.php
 *
 *  \author Oliver Schröder <newspaper@schroederbros.de>
 *  \date Feb 27, 2009
 */

/// function for adding newspaper functionality to the backend
class tx_newspaper_BE {
	
	private static $smarty = null;
	
	
	private static function initSmarty() {
		if (self::$smarty == null) {
			$conf['pathToTemplateDirectory'] = PATH_typo3conf . 'ext/newspaper/res/be/templates/';
			$conf['compile_dir'] = PATH_typo3conf . 'ext/newspaper/res/be/templates/compile/';
			self::$smarty = tx_smarty::smarty($conf);
		}
	}
	
	
	
	public static function renderPageZoneList($PA, $fObj=null) {
		global $lang;
		return 'page zones ...';
	}
	
	
	
	public static function renderPageList($PA, $fObj=null) {
		global $LANG;

t3lib_div::devlog('rpl pa', 'newspaper', 0, $PA);

		$section_uid = $PA['row']['uid'];
t3lib_div::devlog('rpl section id', 'newspaper', 0, $section_uid);		

		if (strtolower(substr($section_uid, 0, 3)) == 'new') {
			/// new section record, so no "real" section uid available
/// \todo real text, localization
			return $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.php:message.section_not_saved', false);
		}
		
		// structure: [#]['uid'] <- uid of page type
		$page_type = tx_newspaper_PageType::getAvailablePageTypes();
#t3lib_div::devlog('rpl pt', 'newspaper', 0, $page_type);		

		// structure: ['pagetype_id'] <- ref to tx_newspaper_pagetype
		foreach(tx_newspaper_page::getActivePages($section_uid) as $active_page) {
#t3lib_div::devlog('rpl ap', 'newspaper', 0, $active_page);		
			for ($i = 0; $i < sizeof($page_type); $i++) {
				if ($page_type[$i]['uid'] == $active_page['pagetype_id']) {
					$page_type[$i]['ACTIVE'] = true;
					$page_type[$i]['ACTIVE_PAGE_ID'] = $active_page['uid'];
					break;
				}
			}
		}
	
		// add ajax call to each row
		for ($i = 0; $i < sizeof($page_type); $i++) {
			if (isset($page_type[$i]['ACTIVE'])) {
				$page_type[$i]['AJAX_URL'] = 'javascript:editActivePage(' . $page_type[$i]['ACTIVE_PAGE_ID'] . ');';
			} else {
				$page_type[$i]['ACTIVE'] = false;
				$page_type[$i]['AJAX_URL'] = 'javascript:activatePageType(' . $section_uid . ' , ' . $page_type[$i]['uid'] . ', \'' . addslashes($LANG->sL('LLL:EXT:newspaper/locallang_newspaper.php:message.check_new_page_in_section', false)) . '\');';
			}
		}
#t3lib_div::devlog('rpl pt', 'newspaper', 0, $page_type);

 
		self::initSmarty();

		/// add skinned icons
		self::$smarty->assign('EDIT_ICON', self::renderIcon('gfx/edit2.gif', '', $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.php:flag.edit_page_in_section', false)));
		self::$smarty->assign('ADD_ICON', self::renderIcon('gfx/new_file.gif', '', $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.php:flag.new_page_in_section', false)));

		// add title and message
		self::$smarty->assign('TITLE', $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.php:message.title_page_in_section', false));
		self::$smarty->assign('MESSAGE', $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.php:message.message_page_in_section', false));

		/// add data rows
		self::$smarty->assign('DATA', $page_type);
		
		$html = '';
		if (!$PA['AJAX_CALL']) {
			$html = '';
			self::$smarty->assign('AJAX_CALL', true);
		} else {
			self::$smarty->assign('AJAX_CALL', false);
		}
		$html .= self::$smarty->fetch('pagetype4section.tmpl');
#t3lib_div::devlog('smarty fetched', 'newspaper', 0, $tmp);		
		
		return $html;
	}
	




























/// \todo: remove from class.tx_newspaper_extra_be.php!
	/// get html for this icon (may include an anchor) 
	/** \param $image path to icon
	 *  \param $id 
	 *  \param $title title for title flag of img
	 *  \param $ahref 
	 *  \param $replaceWithCleargifIfEmpty if set to true the icon is replaced with clear.gif, if $ahref is empty
	 *  \return String <img ...> or <a href><img ...></a> (if linked)
	 */
	private static function renderIcon($image, $id, $title='', $ahref='', $replaceWithCleargifIfEmpty=false) {

/// \to do: read width and height from file? or hardcode 16x16px?
		$width = 16;
		$height = 16;
		
		if ($id)
			$id = ' id="' . $id . '" '; // if id is set, set build correct attribute id="..."

		if ($ahref == '' && $replaceWithCleargifIfEmpty) {
			// hide icon (= replace with clear.gif)
			$html = '<img' . $id . t3lib_iconWorks::skinImg('', 'clear.gif', 'width="' . $width . '" height="' . $height . '"') . ' title="' . $title . '" alt="" />';
		} else {
			// show icon
			//$html = '<img' . $id . t3lib_iconWorks::skinImg('', $image, 'width="' . $width . '" height="' . $height . '"') . ' title="' . $title . '" alt="" />';
			$html = '<img' . $id . t3lib_iconWorks::skinImg('', $image) . ' title="' . $title . '" alt="" />';
		}
		if ($ahref)
			return $ahref . $html . '</a>'; // if linked wrap in link
		return $html; // return image html code

	}


	
}

?>