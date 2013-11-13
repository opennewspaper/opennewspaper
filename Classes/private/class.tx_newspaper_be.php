<?php
/**
 *  \file class.tx_newspaper_be.php
 *
 *  \author Oliver Schröder <newspaper@schroederbros.de>
 *  \date Feb 27, 2009
 */

define('BE_EXTRA_DISPLAY_MODE_SUBMODAL', 1); // extras are edited in a subModal popup
define('BE_EXTRA_DISPLAY_MODE_TABBED', 2);   // extras are edited in tabs



define('BE_ICON_CLOSE', '1');


/// function for adding newspaper functionality to the backend
/** @todo Oliver: document me!
 *  @todo: Oliver: many functions should be converted to static functions
 */


/**
 * Debugging, User TSConfig
 * - newspaper.debug.be.placementModule = [0|1]
 * - newspaper.debug.be.article.extraPlacement = [0|1]
 *
 */
class tx_newspaper_BE {

	private static $smarty = null;

	const default_num_articles_in_articlelist = 50;
    const num_articles_tsconfig_var = 'num_articles_in_article_list_be';

    const clipboardKey = 'tx_newspaper/mod3/index.php/clipboard'; // store data in be_user


    /**
     * @todo no usages found - remove?
     * Get a little Typo3 backend CSS in order to render Flash messages with a die() statement
     * @return string CSS files and code
     */
    public static function getBackendCSS() {
        return '        <link rel="stylesheet" type="text/css" href="' . tx_newspaper::getBasePath() . TYPO3_mainDir  . 'sysext/t3skin/stylesheets/structure/element_message.css" />
        <link rel="stylesheet" type="text/css" href="' . tx_newspaper::getBasePath() . TYPO3_mainDir  . 'sysext/t3skin/stylesheets/visual/element_message.css" />
        <style>
        body {
          color: black;
          font-family: Verdana,Arial,Helvetica,sans-serif;
          font-size: 11px;
          line-height: 14px;
        }
        </style>';
    }


/// backend: render list of pages and pagezones for section


    /**
     * @todo no usages found - remove?
     * Get label for abstract extras in backend (Typo3 TCA user function)
     * The label is set in $params (call by reference)
     * @param $params Data fetched by Typo3
     */
    public function getAbstractExtraLabel(&$params) {
//t3lib_div::devlog('getAbstractExtraLabel()', 'newspaper', 0, array('params' => $params));
		if ($params['row']['extra_table']) {
			$e = new $params['row']['extra_table'](intval($params['row']['extra_uid']));
			$params['title'] = $e->getAbstractExtraLabel();
		}
		if (!$params['title']) {
			$params['title'] = $params['table'] . ' #' . $params['row']['uid'];
		}
    }

	/// either called by userfunc in be or ajax
	public static function renderPagePageZoneList($PA, $fObj=null) {
//t3lib_div::devlog('render ppzlist $pa', 'np', 0, $PA);
		if (strtolower(substr($PA['row']['uid'], 0, 3)) == 'new') {
			/// new section record, so no "real" section uid available
			return tx_newspaper::getTranslation('message_section_not_saved_page');
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
					$data[$i]['AJAX_DELETE_URL'] = 'javascript:NpPagePagetype.deletePage(' . $section_uid . ', ' . $active_page->getUid() . ', \'' . addslashes(tx_newspaper::getTranslation('message_check_delete_page_in_section')) . '\');';
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
						if ($pagezone_types[$j]->getUid() == $active_pagezone->getPageZoneType()->getUid() &&
							!$pagezone_types[$j]->getAttribute('is_article') // hide default articles, see #1518
						) {
							// active pagezone type found
							$data[$i]['pagezones'][$j]['ACTIVE'] = true;
							$data[$i]['pagezones'][$j]['ACTIVE_PAGEZONE_ID'] = $active_pagezone->getUid();
							$data[$i]['pagezones'][$j]['AJAX_DELETE_URL'] = 'javascript:NpPagePagetype.deletePageZone(' . $section_uid . ', ' . $data[$i]['ACTIVE_PAGE_ID'] . ', ' . $active_pagezone->getAbstractUid() . ', \'' . addslashes(tx_newspaper::getTranslation('message_check_delete_pagezone_in_page')) . '\');';
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
						if ($pagezone_types[$j]->getAttribute('is_article')) { // re-activate when default articles are used again, see #1518: && !$data[$i]['DEFAULT_ARTICLE_PAGE']) {
							// default article pagezone for non-default article page, this combinations is not allowed (and nonsense)
							// \todo: still needed? see #1518
							unset($data[$i]['pagezones'][$j]); // so remove data collected so far for this combination
						} else {
							// active pagezone type found ['ACTIVE'] = false;
							$data[$i]['pagezones'][$j]['AJAX_ACTIVATE_URL'] = 'javascript:NpPagePagetype.activatePageZoneType(' . $section_uid . ', ' . $data[$i]['ACTIVE_PAGE_ID'] . ', ' . $pagezone_types[$j]->getUid() . ');';
						}
					}
				}
			} else {
				// page type not active, so no pagezones to display
				$data[$i]['ACTIVE'] = false;
				$data[$i]['AJAX_ACTIVATE_URL'] = 'javascript:NpPagePagetype.activatePageType(' . $section_uid . ', ' . $page_types[$i]->getUid() . ');';
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
		self::$smarty->assign('EDIT_ICON', self::renderIcon('gfx/edit2.gif', '', tx_newspaper::getTranslation('flag_edit_page_in_section')));
		self::$smarty->assign('ADD_ICON', self::renderIcon('gfx/new_file.gif', '', tx_newspaper::getTranslation('flag_new_page_in_section')));
		self::$smarty->assign('DELETE_ICON', self::renderIcon('gfx/garbage.gif', '', tx_newspaper::getTranslation('message_delete_page_in_section')));
		self::$smarty->assign('CLEAR_ICON', self::renderIcon('', '', '', '', true));
		self::$smarty->assign('OK_ICON', self::renderIcon('gfx/icon_ok2.gif', '', ''));

		self::$smarty->assign('USE_TEMPLATE_SETS', self::useTemplateSetsForSections()); // are template set dropdowns visible or not


		// add title and message
		self::$smarty->assign('TITLE', tx_newspaper::getTranslation('message_title_page_in_section'));

		/// add data rows
		self::$smarty->assign('DATA', $data);
//t3lib_div::devlog('renderPagePageZoneList()', 'newspaper', 0, array('data' => $data));

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


/// template set functions

    /**
     * @todo no usages found - remove?
     * itemsProcFunc to fill templateset dropdowns in "normal" tceforms backend forms
     */
	function addTemplateSetDropdownEntries(&$params, &$pObj) {
		$this->readTemplateSetItems($params);
	}

	/// get available templates and store in &$param
	/**
	 * If template named default is found, it is moved to first position in the dropdown
	 */
	private function readTemplateSetItems(&$params) {

		// check if "inherit from above" should be used
		$key = 'use_template_sets_with_inherit_above_option';
		$value = tx_newspaper::getNewspaperConfig($key);
       	$useInheritAboveOption = (isset($value[$key]))? ((bool) $value[$key]) : true; // true is default

		$default_found = false;

		$templateset = tx_newspaper_smarty::getAvailableTemplateSets();

		if ($useInheritAboveOption) {
			$params['items'][] = array(tx_newspaper::getTranslation('entry_templateset_inherit'), ''); // empty entry -> templateset is inherited
		}
		$params['items'][] = array('default', 'default'); // default set is sorted to top of list, if not existing, this entry is removed later
		for ($i = 0; $i < sizeof($templateset); $i++) {
			if ($templateset[$i] != 'default') {
				$params['items'][] = array($templateset[$i], $templateset[$i]);
			} else {
				$default_found = true;
			}
		}

		if (!$default_found) {
			unset($params['items'][array_search('default', $params['items'])]); // remove entry 'default' (because there's no templateset "default" available)
		}
	}

	/// create html code for a template set dropdown (including AJAX call in onchange event)
	/// assumes that js function NpBackend.storeTemplateSet() is available (is defined in newspaper.js)
	public static function createTemplateSetDropdown($table, $uid, $default_value='') {
		$params = array();
		self::readTemplateSetItems($params); // call by reference ...

		$html = '<select id="templateset_' . $uid . '" onchange="NpBackend.storeTemplateSet(\'' . $table . '\', ' . $uid . ', this.options[this.selectedIndex].value); return false;">'; //
		foreach($params['items'] as $item) {
			$selected = ($item[1] == $default_value)? ' selected="selected"' : ''; // item[0] = title, item[1] = value to store
			$html .= '<option value="' . $item[1] . '"' . $selected . '>' . $item[0] . '</option>';
		}
		$html .= '</select>';
		return $html;
	}


	/// Returns whether or not template sets for sections are used in the backend (newspaper.conf)
	public static function useTemplateSetsForSections() {
        return self::useTemplateSets('use_template_sets_for_sections');
	}

	/// Returns whether or not template sets for content placement are used in the backend (newspaper.conf)
	public static function useTemplateSetsForContentPlacement() {
        return self::useTemplateSets('use_template_sets_for_content_placement');
	}

	/// Returns whether or not template sets are used in the backend for given key; deafults to true
	private static function useTemplateSets($key) {
        $value = tx_newspaper::getNewspaperConfig($key);

        if (!isset($value[$key])) {
        	return true; // default
        }
        return (bool) $value[$key];
	}

	/// Returns an array with tables where template sets should be set to "default" (regarding newspaper.conf settings)
	public static function getTemplateSetTables() {
		$templateSets = array(
			'tx_newspaper_page',
			'tx_newspaper_pagezone_page',
			'tx_newspaper_article',
			'tx_newspaper_extra'
		);

		// check if template sets are hidden for sections
		if (!self::useTemplateSetsForSections()) {
			$templateSets[] = 'tx_newspaper_section';
		}
//t3lib_div::devlog('tmp', 'np', 0, $templateSets);
		return $templateSets;
	}


/// pagezone inheritance source functions

    /**
     * @todo no usages found - remove?
     * itemsProcFunc to fill inheritance for pages dropdowns in "normal" tceforms backend forms
     */
    function addInheritancePageDropdownEntries(&$params, &$pObj) {
		$this->readInheritancePageItems($params);
	}

	private function readInheritancePageItems(&$params) {

		$pages = array('dummy', 'test', 'aha');

		$params['items'][] = array(tx_newspaper::getTranslation('entry_templateset_inherit'), ''); // empty entry -> templateset is inherited
		$params['items'][] = array('default', 'default'); // default set is sorted to top of list, if not existing, this entry is removed later
		for ($i = 0; $i < sizeof($pages); $i++) {
			$params['items'][] = array($pages[$i], $pages[$i]);
		}

	}



/// article list functions

    /**
     * @todo no usages found - remove?
     * render article list form for section backend
     * either called by userfunc in be or ajax
     */
	public static function renderArticleList($PA, $fObj=null) {

//t3lib_div::devlog('renderArticleList()', 'newspaper', 0, array('PA' => $PA));
		if (strtolower(substr($PA['row']['uid'], 0, 3)) == 'new') {
			// @todo: displayCond REC NEW
			/// new section record, so no "real" section uid available
			return tx_newspaper::getTranslation('message_section_not_saved_articlelist');
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

		$nMV_label = tx_newspaper::getTranslation('error_dropdown_invalid_articlelist');

		$obj = new t3lib_TCEforms();

		// set configuration
		$config['type'] = 'select';
		$config['size'] = 1;
		$config['maxitems'] = 1;
		$config['form_type'] = 'select';

		$out = $obj->getSingleField_typeSelect_single('tx_newspaper_section', 'articlelist', $PA['row'], $PA, $config, $selItems, $nMV_label);

		return $out;

	}


/// \todo: move to pagezone
/// \todo: correct sorting: negative paragraph at the bottom
	public static function collectExtras(tx_newspaper_PageZone $pz) {

        $timer = tx_newspaper_ExecutionTimer::create();

		$data = array();

		foreach ($pz->getExtras() as $extra) {
			//	don't display extras for which attribute gui_hidden is set
			if ($extra->getAttribute('gui_hidden')) continue;

			$data[] = self::populateExtraData($extra, $pz);
		}
		return $data;
	}

    private static function populateExtraData(tx_newspaper_Extra $extra, tx_newspaper_PageZone $pz) {

        $timer = tx_newspaper_ExecutionTimer::create();

        $extra_data = array(
            'extra_type' => $extra->getTitle(),
            'uid' => $extra->getExtraUid(),
            'title' => $extra->getDescription(),
            'origin_placement' => $extra->isOriginExtra(),
            'origin_uid' => $extra->getOriginUid(),
            'concrete_table' => $extra->getTable(),
            'concrete_uid' => $extra->getUid(),
            'inherits_from' => $pz->getExtraOriginAsString($extra),
            'pass_down' => $extra->getAttribute('is_inheritable'),
            'notes' => $extra->getAttribute('notes'),
            'template_set' => $extra->getAttribute('template_set'),
            'tstamp' => $extra->getAttribute('tstamp'),
        );
        // the following attributes aren't always available
        try {
            $extra_data['hidden'] = $extra->getAttribute('hidden');
        } catch (tx_newspaper_WrongAttributeException $e) { }
        try {
            $extra_data['show'] = $extra->getAttribute('show_extra');
        } catch (tx_newspaper_WrongAttributeException $e) { }
        try {
            $extra_data['paragraph'] = $extra->getAttribute('paragraph');
        } catch (tx_newspaper_WrongAttributeException $e) { }
        try {
            $extra_data['position'] = $extra->getAttribute('position');
        } catch (tx_newspaper_WrongAttributeException $e) { }

        // render html dropdown and add to array
        $extra_data['template_set_HTML'] = tx_newspaper_BE::createTemplateSetDropdown('tx_newspaper_extra', $extra_data['uid'], $extra_data['template_set']);

        return $extra_data;

    }


    /**
     * @todo no usages found - remove?
     * render dummy field for kicker, title and teaser in order to place these 3 field in 1 row (in a palette)
     */
    function renderArticleKickerTtitleTeaser($PA, $fobj) {
//t3lib_div::devlog('renderArticleKickerTtitleTeaser()', 'newspaper', 0, array('PA' => $PA));
		return '';
	}

    /**
     * @todo no usages found - remove?
	 * render dummy field for kicker, title and teaser in list views in order to place these 3 field in 1 row (in a palette)
     */
	function renderArticleKickerTtitleTeaserForListviews($PA, $fobj) {
//t3lib_div::devlog('renderArticleKickerTtitleTeaser()', 'newspaper', 0, array('PA' => $PA));
		return '';
	}


/**
 * @todo no usages found - remove?
 *
 * Userfunc for a texarea field in the backend with newspaper conf
 * Configuration array
 *  'type' => 'user'
 *  'userFunc' => 'tx_newspaper_be->renderTextarea'
 *  'width' => '[int+]' (default: 530)
 *  'height' => '[int+]' (default: 80)
 *  'maxlen' => '[int+]' (default: 1000)
 *  'useCountdown' => '1' (default: 0; if set, a countdown shows how many character are still available in the textarea field)
 *
 * @param $PA
 * @param $fobj
 * @return HTML code
 */
	function renderTextarea($PA, $fobj) {
//t3lib_div::debug($PA); die();

		$width = (intval($PA['fieldConf']['config']['width']) > 0)? intval($PA['fieldConf']['config']['width']) : 530;
		$height = (intval($PA['fieldConf']['config']['height']) > 0)? intval($PA['fieldConf']['config']['height']) : 80;
		$maxLen = (intval($PA['fieldConf']['config']['maxLen']) > 0)? intval($PA['fieldConf']['config']['maxLen']) : 1000;
		$useCountdown = (intval($PA['fieldConf']['config']['useCountdown']))? true : false;

		$uniq = $PA['field'] . $PA['row']['uid']; // unique string based on field name and record uid

		// add typo3 like html code with additional newspaper textarea according to given configuration
		$html = '<style type="text/css">
#countdown_' . $uniq . ' {
  float:left;
  margin-left:10px;
  margin-top:2px;
}
</style>
';
		if ($useCountdown) {
			// add textarea AND a countdown field
			$html .= '<div style="float:left;"><textarea onchange="' . $PA['fieldChangeFunc']['TBE_EDITOR_fieldChanged'] . '" onkeyup="NpBackend.checkMaxLenTextarea(this, '. $maxLen . ', \'countdown_' . $uniq . '\');" wrap="virtual" class="formField" style="width:' . $width . 'px; height:' . $height . 'px;" name="' . $PA['itemFormElName'] . '">' . $PA['itemFormElValue'] . '</textarea></div>
<div id="countdown_' . $uniq . '">' . intval($maxLen - strlen(utf8_decode($PA['row'][$PA['field']]))) . '</div>';
		} else {
			// add textarea only
			$html .= '<div style="float:left;"><textarea onchange="' . $PA['fieldChangeFunc']['TBE_EDITOR_fieldChanged'] . '" onkeyup="npBackend.checkMaxLenTextarea(this, '. $maxLen . ', \'\');" wrap="virtual" class="formField" style="width:' . $width . 'px; height:' . $height . 'px;" name="' . $PA['itemFormElName'] . '">' . $PA['itemFormElValue'] . '</textarea></div>';

		}

		return $html;
	}


/// Userfunc for a input field in the backend with newspaper conf
/**
 * @todo no usages found - remove?
 *  WARNING: DOES NOT WORK FOR required FIELDS
 *  Configuration array
 *  'type' => 'user'
 *  'userFunc' => 'tx_newspaper_be->renderInput'
 *  'width' => '[int+]' (default: 530)
 *  'height' => '[int+]' (default: 80)
 *
 *  \param $PA
 *  \param $fobj
 *  \return HTML code
 */
	function renderInput($PA, $fobj) {
//t3lib_div::debug($PA); die();

		$width = (intval($PA['fieldConf']['config']['width']) > 0)? 'width:' . intval($PA['fieldConf']['config']['width']) . 'px;' : 'width:288px';
		$height = (intval($PA['fieldConf']['config']['height']) > 0)? 'height:' . intval($PA['fieldConf']['config']['height']) . 'px;' : '';

		$html = '<input type="text" ';
		$html .= 'onchange="typo3form.fieldGet(\'' . $PA['itemFormElName'] . '\',\'\',\'\',0,\'\');';
		$html .= 'TBE_EDITOR.fieldChanged(\'' . $PA['table'] . '\',\'' . $PA ['row']['uid'] . '\',\'' . $PA['field']. '\',\'' . $PA['itemFormElName'] . '\');" ';
		$html .= 'maxlength="256" class="formField" style="' . $width . $height . '" value="' . htmlspecialchars($PA['itemFormElValue']) . '" name="' . $PA['itemFormElName'] . '_hr">';
		$html .= '<input type="hidden" value="' . htmlspecialchars($PA['itemFormElValue']) . '" name="' . $PA['itemFormElName'] . '">';

		return $html;
	}




/**
 * @todo only found in a commented out line in mod3_pagezone_article.tmpl - remove?
 * function to render extras (article or pagezone_page)
 */
	function renderExtraInArticle($PA, $fobj) {
		// create article
		$article = new tx_newspaper_Article(intval($PA['row']['uid']));
//t3lib_div::devlog('e in a', 'np', 0, array($PA, $fobj, $article, $article->getAbstractUid(), $_REQUEST));
		return self::renderBackendPageZone($article, false);
	}

	/// Returns the translation of \p $key, using \c mod3/locallang.xml as translation file
	private static function getTranslation($key) {
		return tx_newspaper::getTranslation($key, 'mod3/locallang.xml');
	}

    /**
     * @param tx_newspaper_PageZone $pz Either pagezone_page or article
     * @param bool $showLevelsAbove Boolean If true, levels above are rendered too
     * @param bool $ajax_reload
     * @return mixed HTML code with placement module backend
     */
    public static function renderBackendPageZone(tx_newspaper_PageZone $pz, $showLevelsAbove=false, $ajax_reload=false) {

        $timer = tx_newspaper_ExecutionTimer::create();

        // Smarty
        $smarty = new tx_newspaper_Smarty();
        $smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod3/res/'));


        $data = array();
        $extraData = array();
        // Fill $data and $extraData
        self::getPageZoneDataForPlacementModule($showLevelsAbove, $pz, $data, $extraData);

        if (!$pz->isConcreteArticle()) {
            // Placement module
            $pageTypes = self::getPageTypesForPagezone($pz);
            $pageZoneTypes = self::getPageZoneTypesForPagezone($pz);

            // add possible inheritance sources for this page zone
            $pp = $pz->getPossibleParents(true);
            $page_name = array();
            for ($i = 0; $i < sizeof($pp); $i++) {
                // Get name of page
                $page_name[] = $pp[$i]->getParentPage()->getPageType()->getAttribute('type_name'); // Can't be accessed with smarty
            }
//t3lib_div::devlog('inh from', 'newspaper', 0, array($pp, $page_name));
            $smarty->assign('INHERITANCESOURCE', $pp);
            $smarty->assign('INHERITANCESOURCENAME', $page_name);
        }

        $message['pagezone_empty'] = self::getTranslation('message_pagezone_empty');
        $message['confirmation'] = self::getTranslation('message_unsaved_data');

        $smarty->assign('LABEL', self::getLabelsForPlacementModule());
        $smarty->assign('MESSAGE', $message);
        $smarty->assign('DATA', $data);
        $smarty->assign('PAGETYPE', $pageTypes);
        $smarty->assign('PAGEZONETYPE', $pageZoneTypes);
        $smarty->assign('SHOW_LEVELS_ABOVE', $showLevelsAbove);
        $smarty->assign('DUMMY_ICON', tx_newspaper_BE::renderIcon('gfx/dummy_button.gif', '', self::getTranslation('label_new_top')));
        $smarty->assign('IS_CONCRETE_ARTICLE', $pz->isConcreteArticle());
        $smarty->assign('IS_CONCRETE_ARTICLE_RELOAD', $ajax_reload);
        if ($pz->isPagezonePage()) {
            $smarty->assign('DEBUG_OUTPUT', tx_newspaper::getUserTSConfigForDebugging('newspaper.debug.be.placementModule'));
        }
        $smarty->assign('CLEAR_CLIPBOARD_ICON', tx_newspaper_BE::renderIcon('gfx/closedok.gif', '', self::getTranslation('label_clear_clipboard')));


		/// "new to top" buttons vary for pagezone_page (new to top) and article (new extra, set pos and paragraph in form)
		if ($data[0]['pagezone_type'] instanceof tx_newspaper_article && $data[0]['pagezone_type']->getAttribute('is_article') == 0) {
			$smarty->assign('NEW_TOP_ICON', tx_newspaper_BE::renderIcon('gfx/new_record.gif', '', self::getTranslation('label_new_top')));
		} else {
			$smarty->assign('NEW_TOP_ICON', tx_newspaper_BE::renderIcon('gfx/new_record.gif', '', self::getTranslation('label_new_extra')));
		}



		// pagezones are rendered by a separate smarty template - because 2 versions (pagezone_page or article) can be rendered
		$smarty_pz = self::getPagezoneSmartyObject();
		$pagezone = array();
		for ($i = 0; $i < sizeof($extraData); $i++) {

			$smarty_pz->assign('IS_CURRENT', ($i == sizeof($extraData)-1)? true : false); // is this pagezone the currently edited page zone?

			$smarty_pz->assign('DATA', $data[$i]); // so pagezone uid is available
			$smarty_pz->assign('IS_CONCRETE_ARTICLE', $pz->isConcreteArticle());
			$smarty_pz->assign('USE_TEMPLATE_SETS', self::useTemplateSetsForContentPlacement()); // are template set dropdowns visible or not
			$smarty_pz->assign('CLIPBOARD', self::getClipboardData());
			if ($pz->isPagezonePage() && $data[$i]['pagezone_type']->getAttribute('is_article') == 0) {
                $smarty_pz->assign('DEBUG_OUTPUT', tx_newspaper::getUserTSConfigForDebugging('newspaper.debug.be.placementModule'));
				if (sizeof($extraData[$i]) > 0) {
					// render pagezone table only if extras are available
					$smarty_pz->assign('EXTRA_DATA', $extraData[$i]);
					$pagezone[$i] = $smarty_pz->fetch('mod3_pagezone_page.tmpl');
				} else {
					$pagezone[$i] = false; // message "no extra so far" will be displayed in mod3.tmpl
				}
			} else {
				// Needed for concrete articles

                $smarty_pz->assign('DEBUG_OUTPUT', tx_newspaper::getUserTSConfigForDebugging('newspaper.debug.be.article.extraPlacement'));

				$smarty_pz->assign('NEW_TOP_ICON', tx_newspaper_BE::renderIcon('gfx/new_record.gif', '', self::getTranslation('label_new_top')));

				$smarty_pz->assign('SHORTCUT_DEFAULTEXTRA_ICON', tx_newspaper_BE::renderIcon('gfx/new_record.gif', '', self::getTranslation('label_new_defaultextra_in_article')));
				$smarty_pz->assign('SHORTCUT_NEWEXTRA_ICON', tx_newspaper_BE::renderIcon('gfx/new_file.gif', '', self::getTranslation('label_new_extra_in_article')));

				$tmp = self::processExtraDataForExtraInArticle($extraData[$i]);
				$smarty_pz->assign('EXTRA_DATA', $tmp['extras']);

				$smarty_pz->assign('SHORTCUT', ($pz->isConcreteArticle()? $pz->getMissingDefaultExtras() : array())); // Add array with shortcut list
				$smarty_pz->assign('MESSAGE', $message);

               	switch(self::getExtraBeDisplayMode()) {
               		case BE_EXTRA_DISPLAY_MODE_TABBED:
		                 // tabbed backend, set which tab to show after loading
                          $lastTab = 'overview';
                          if(isset($_REQUEST['lastTab'])) { //is set after reload
                            $lastTab = $_REQUEST['lastTab'];
                          } else if(isset($extraData[0][0])) { //when opened first time
                              $lastTab = $extraData[0][0]['concrete_table'].'_'.$extraData[0][0]['concrete_uid'];
                          }
                         $smarty_pz->assign('lastTab', $lastTab);
                         tx_newspaper_BE::addNewExtraData($smarty_pz, tx_newspaper_extra::HIDE_IN_ARTICLE_BE);
		                 $pagezone[$i] = $smarty_pz->fetch('mod3_pagezone_article_tabbed.tmpl'); // whole pagezone
               		break;
               		case BE_EXTRA_DISPLAY_MODE_SUBMODAL:
               		default:
                        // @todo: Remove submodal code!
		                 // just a list of extras
		                 $pagezone[$i] = $smarty_pz->fetch('mod3_pagezone_article.tmpl'); // whole pagezone
               	}
			}
		}

		$smarty->assign('PAGEZONE', $pagezone);

		// clipboard
		$clipboard = self::getClipboardData();
		if ($clipboard) {
			if ($e = tx_newspaper_Extra_Factory::getInstance()->create(intval($clipboard['extraUid']))) {
				// store clipboard data
				$smarty->assign('CLIPBOARD', $clipboard);

				// read pagezone data
				$pz = tx_newspaper_PageZone_Factory::getInstance()->create(intval($clipboard['pagezoneUid']));
				$pz_title = $pz->getParentPage()->getParentSection()->getAttribute('section_name') . ' / ';
				$pz_title .= $pz->getParentPage()->getPageType()->getAttribute('type_name') . ' / ';
				$pz_title .= $pz->getPagezoneType()->getAttribute('type_name');

				// read extra data
				$e_title = $e->getTitle() . ' (#' . $e->getUid() . ')';
				$smarty->assign('CLIPBOARD_DATA', array(
					'pz' => $pz_title,
					'e'  => $e_title
				));

				// Smaryt stuff
				$smarty->assign('COPY_PASTE_ICON', tx_newspaper_BE::renderIcon('gfx/clip_pasteafter.gif', '', self::getTranslation('label_copy_paste')));
				$smarty->assign('CUT_PASTE_ICON', tx_newspaper_BE::renderIcon('gfx/clip_pasteafter.gif', '', self::getTranslation('label_cut_paste')));
			} else {
				self::clearClipboard(); // extra not valid, so clear clipboard
			}
		}

		return $smarty->fetch('mod3.tmpl');
	}

    /**
     * Get available page zone  types for page zone $pz
     * Some pagezone might be hidden via User TSConfig, @see getHiddenPageZoneTypeUids()
     * @param tx_newspaper_PageZone $pz Page zone
     * @return array ty_tx_newspaper_activatePageZoneType
     * @todo: Move to pagezone class?
     */
    public static function getPageZoneTypesForPagezone($pz)     {
        $pageZones = $pz->getParentPage()->getPageZones(); // Get activate pages zone for current page
        $pageZoneTypes = array();
        $hiddenPagezones = self::getHiddenPageZoneTypeUids();
        for ($i = 0; $i < sizeof($pageZones); $i++) {
            // Add all pagezone types except articles
            // \todo: make article exception ts-configurable if default articles are to be used (note: default article features have not implemented yet)
            if (!$pageZones[$i]->getPageZoneType()->getAttribute('is_article') &&
                    !in_array($pageZones[$i]->getPageZoneType()->getUid(), $hiddenPagezones)) {
                $pageZoneTypes[] = $pageZones[$i]->getPageZoneType();
            }
        }
        return $pageZoneTypes;
    }

    /**
     * Get pagezone uids which should be hidden (f. ex. in placement module)
     * Usage: User TSConfig
     * newspaper.placementModule.hidePagezoneTypes = [uid1, ..., uidn]
     * @todo: Move to pagezone class?
     * @return array Hidden pagezone uids
     */
    public  static function getHiddenPageZoneTypeUids() {
        if (!isset($GLOBALS['BE_USER'])) {
            return array(); // Doesn't make sense without a backend user ...
        }
        // Check User TSConfig setting
        if ($tsc = $GLOBALS['BE_USER']->getTSConfigVal('newspaper.placementModule.hidePagezoneTypes')) {
            return t3lib_div::trimExplode(',', $tsc); // Return array with hidden pagezone uids
        }
    }

    /**
     * Get available page types for page zone $pz
     * @param tx_newspaper_PageZone $pz Page zone
     * @return array tx_newspaper_PageType
     * @todo: Move to pagezone class?
     */
    public static function getPageTypesForPagezone(tx_newspaper_PageZone $pz) {
        $s = $pz->getParentPage()->getParentSection();
        $pages = $s->getSubPages(); // Get activate pages for current section
        $pageTypes = array();
        for ($i = 0; $i < sizeof($pages); $i++) {
            $pageTypes[] = $pages[$i]->getPageType();
        }
        return $pageTypes;
    }

    /**
     * Extra data for placement module
     * @param $showLevelsAbove Bool If true, levels above are added too
     * @param tx_newspaper_PageZone $pz tx_newspaper_PageZone Either pagezone_page or article
     * @param array $data Array (Reference) Data for pagezone
     * @param array $extraData (Reference) Data for Extras associated wth pagezone
     * @static
     * @return void (Data is stored in $data and $extraData)
     */
    private static function getPageZoneDataForPlacementModule($showLevelsAbove, tx_newspaper_PageZone $pz, array &$data, array &$extraData) {
        // Add UPPER level page zones and extras, if any
        if ($showLevelsAbove) {
            $pz_up = array_reverse($pz->getInheritanceHierarchyUp(false));
            for ($i = 0; $i < sizeof($pz_up); $i++) {
                if (self::accessToSectionIsGranted($pz_up[$i])) {
                    $data[] = self::extractData($pz_up[$i]);
                    $extraData[] = tx_newspaper_BE::collectExtras($pz_up[$i]);
                }
            }
        }
        // Add CURRENT page zone and extras
        $data[] = self::extractData($pz); // empty array if concrete article
        $extraData[] = tx_newspaper_BE::collectExtras($pz);
//t3lib_div::devlog('getPageZoneDataForPlacementModule() - all levels read', 'newspaper', 0, array('data' => $data, 'extra_data' => $extra_data));

        if (!$pz->isConcreteArticle()) {
            // Placement module
			$data[0]['article_id'] = -1; // Only needed for concrete article
		} else {
            // Article backend
			$data[0]['pagezone_id'] = $pz->getAbstractUid(); // Store pz_uid for backend buttons usage
			$data[0]['article_id'] = $pz->getUid(); // Store article uid for backend buttons usage (edit)
		}
//t3lib_div::devlog('getPageZoneDataForPlacementModule() - some more data added', 'newspaper', 0, array('pz' => $pz, 'data' => $data));

    }

    /**
     * @param tx_newspaper_PageZone $pz
     * @return bool
     * @todo: Move to pagezone class?
     */
    private static function accessToSectionIsGranted(tx_newspaper_PageZone $pz) {
        $section = $pz->getParentPage()->getParentSection();
        return $section->isSectionAccessGranted();
    }


    /**
     * @return mixed Localized labels for placement module
     */
    public static function getLabelsForPlacementModule() {
        return array(
            'show_levels_above' => self::getTranslation('label_show_levels_above'),
            'show_visible_only' => self::getTranslation('label_show_visible_only'),
            'pagetype' => self::getTranslation('label_pagetype'),
            'pagezonetype' => self::getTranslation('label_pagezonetype'),
            'pagezone_inheritancesource' => self::getTranslation('pagezone_inheritancesource'),
            'pagezone_inheritancesource_upper' => self::getTranslation('pagezone_inheritancesource_upper'),
            'pagezone_inheritancesource_none' => self::getTranslation('pagezone_inheritancesource_none'),
            'title' => self::getTranslation('title'),
            'clipboard' => self::getTranslation('label_clipboard'),
            'clipboard_cut' => self::getTranslation('label_clipboard_cut'),
            'clipboard_copied' => self::getTranslation('label_clipboard_copied'),
            'clear_clipboard' => self::getTranslation('label_clear_clipboard'),
            'extra_cut_paste_confirm' => self::getTranslation('message_cut_paste_confirm'),
            'extra_copy_paste_confirm' => self::getTranslation('message_copy_paste_confirm'),
            'publish' => self::getTranslation('label_publish')
        );
    }


// \todo: how are $new_at_top and $paragraph used? are those vars used at all???
    private static function addNewExtraData(&$smarty, $type) {

        // convert params, sent by js, so false is given as string, not a boolean
/// \todo: switch to 0/1 instead of false/true
        if ($new_at_top == 'false') {
            $new_at_top = false;
        } else {
            $new_at_top = true;
        }
        if ($paragraph == 'false') {
            $paragraph = false;
        } else {
            $paragraph = intval($paragraph);
        }


        $label = $smarty->get_template_vars('LABEL');
        $label['new_extra_new'] = self::getTranslation('label_new_extra_new');
        $label['new_extra_from_pool'] = self::getTranslation('label_new_extra_from_pool');

        $message = $smarty->get_template_vars('MESSAGE');
        $message['no_extra_selected'] = self::getTranslation('message_no_extra_selected');

        /// list of registered extras
        $extra = tx_newspaper_Extra::getAllowedExtras($type);
//debug($extra, 'e');
        $smarty->assign('LABEL', $label);
        $smarty->assign('MESSAGE', $message);
        $smarty->assign('EXTRA', $extra); // list of extras
        $smarty->assign('LIST_SIZE', max(2, min(12, sizeof($extra)))); /// size at least 2, otherwise list would be rendered as dropdown

        if ($paragraph === false) {
            // the param is received as string, not boolean ... sent with js
            $smarty->assign('PARAGRAPH_USED', false);
        } else {
            $smarty->assign('PARAGRAPH_USED', true);
            $smarty->assign('PARAGRAPH', intval($paragraph));
        }

        if ($new_at_top === false) {
            // the param is received as string, not boolean ... sent with js
            $smarty->assign('NEW_AT_TOP', false);
        } else {
            $smarty->assign('NEW_AT_TOP', true);
        }
    }





// clipboard functions

	/**
	 * Stores cut or copied extra in be_user
	 * \param array $input Params (probably by Ajax request)
	 * \param $cut if true, the extra is cut, else copied
	 * \return void
	 */
	public static function copyExtraToClipboard(array $input, $cut=false) {
//t3lib_div::devlog('cut/copy','newspaper', 0, array('pagezoneUid' => $input['e_uid'], 'extraUid' => $input['pz_uid'], 'type' => $cut? 'cut' : 'copy'));
		$GLOBALS['BE_USER']->pushModuleData(self::clipboardKey, serialize(array(
			'pagezoneUid' => $input['pz_uid'],
			'extraUid' => $input['e_uid'],
			'type' => $cut? 'cut' : 'copy'
		)));
	}

	public function processPasteFromClipboard(array $input) {
		$clipboard = self::getClipboardData();
//t3lib_div::devlog('paste', 'newspaper', 0, array('clipboard' => $clipboard, 'input' => $input));

		// get extra in clipboard
		$e_old = tx_newspaper_Extra_Factory::getInstance()->create(intval($clipboard['extraUid']));

		// copy the extra (for both copy and cut)
		$e = $e_old->duplicate();

		// get target pagezone
		$pz = tx_newspaper_PageZone_Factory::getInstance()->create(intval($input['pz_uid']));

		// insert extra
		$pz->insertExtraAfter($e, intval($input['origin_uid']), true);

		// assemble data for workflow log
		$log = array(
			'clipboardType' => $clipboard['type'],
			'sourcePagezoneUid' => $clipboard['pagezoneUid'],
			'targetPagezoneUid' => $input['pz_uid'],
			'targetOriginUid' => $input['origin_uid']
		);

		if ($clipboard['type'] == 'cut') {
			// delete cut extra and clear clipboard
			$pz_old = tx_newspaper_PageZone_Factory::getInstance()->create(intval($clipboard['pagezoneUid']));
			$success = $pz_old->removeExtra($e_old, true);
            if (!$success) {
                tx_newspaper::devlog('removeExtra failed', array('pagezone'=>$pz_old, 'extra'=>$e_old), 'newspaper', 2);
            }
			self::clearClipboard(); // clear clipboard

			$logType = NP_WORKLFOW_LOG_PLACEMENT_CUT_PASTE;

		} else {
			$logType = NP_WORKLFOW_LOG_PLACEMENT_COPY_PASTE;
		}

		tx_newspaper_Workflow::logPlacement('tx_newspaper_extra', $clipboard['extraUid'], $log, $logType);


	}


	/// Clears extra from clipboard in be_user
	public static function clearClipboard() {
		$GLOBALS['BE_USER']->pushModuleData(self::clipboardKey, serialize(array()));
	}

	// \return Clipboard datra array (pagezoneUid, extraUid, type (cut|copy))
	public static function getClipboardData() {
		return unserialize($GLOBALS['BE_USER']->getModuleData(self::clipboardKey));
	}






    /**
     * "Replace" the tag backend created by the kickstarter with a backend offering an list for content tags and for
     * each control tag. All tags ared stored in a single field and split here into the tag types.
     *
     * Used in tx_newspaper_article::modifyTagSelection():
     * $TCA['tx_newspaper_article']['columns']['tags']['config']['userFunc'] = 'tx_newspaper_be->renderTagControlsInArticle';
     *
     * @param $PA
     * @param $fobj
     * @return string
     */
    public function renderTagControlsInArticle(&$PA, $fobj) {
//t3lib_div::devlog('renderTagControlsInArticle', 'newspaper', 0, array('params' => $PA) );
        $articleId = intval($PA['row']['uid']);
        $PA['fieldConf']['config']['foreign_table'] = 'tx_newspaper_tag';
        $PA['fieldConf']['config']['form_type'] = 'select';
        $PA['fieldConf']['config']['size'] = '5';

        // Content tags
        $contentTagTitle = self::getTranslation('label_content_tag');
        $TCEformsObj = new t3lib_TCEforms();
        $contentTags = $this->createTagSelectElement($PA, $TCEformsObj, $articleId, 'tags', tx_newspaper_tag::getContentTagType(),$contentTagTitle);

        // Control tags
        $allControlTagCategories = tx_newspaper_Tag::getAllControltagCategories();
        $allowedControlTagCategories = tx_newspaper_Tag::getAllControlTagCategoriesWithRestrictions();
        $controlTags = '';
        $controlTagUids = array();
        foreach($allControlTagCategories as $controlTagCategory) {
            $tagType = 'tags_ctrl_'.$controlTagCategory['uid'];
            $controlTags .= $this->createTagSelectElement(
                $PA,
                $TCEformsObj,
                $articleId,
                $tagType,
                tx_newspaper_tag::getControlTagType(),
                $controlTagCategory['title'],
                $controlTagCategory['uid'],
                $allowedControlTagCategories
            );
            $controlTagUids[] = $controlTagCategory['uid'];
        }
//t3lib_div::devlog('renderTagControlsInArticle', 'newspaper', 0, array('params' => $PA) );
        return $this->getFindTagsJs($articleId, implode(',', $controlTagUids)) . $contentTags . $controlTags;
    }

    /**
     * Creates a select box for given tag type (using TCEforms)
     * @param $PA
     * @param $TCEformsObj
     * @param $articleId
     * @param $tagType
     * @param $tagTypeId
     * @param $title
     * @param $controlTagCategoryUid
     * @return mixed
     */
    private function createTagSelectElement(&$PA, $TCEformsObj, $articleId, $tagType, $tagTypeId, $title, $controlTagCategoryUid=false, $allowedControlTagCategories=array()) {
//t3lib_div::devLog('tag', 'np', 0, array('tag type' => $tagType, 'tt id' => $tagTypeId, 't' => $title, 'cat' => $controlTagCategoriyUid));
        $PA['itemFormElName'] = 'data[tx_newspaper_article]['.$articleId.'][' . $tagType . ']';
        $PA['itemFormElID'] = 'data_tx_newspaper_article_' . $articleId . '_' . $tagType;
        $PA['itemFormElValue'] = $this->fillItemValues($articleId, $tagTypeId, $controlTagCategoryUid);
        /** @var $TCEformsObj t3lib_TCEforms */
        $field = $this->addTagInputField(
            $TCEformsObj->getSingleField_typeSelect('tx_newspaper_article', $tagType ,$PA['row'], $PA),
            $articleId,
            $tagType
        );
        $field = str_replace($TCEformsObj->getLL('l_items'), $title, $field);
        if ($this->isTagFieldHiddenInBackend($tagTypeId, $controlTagCategoryUid, $allowedControlTagCategories)) {
            // Hide control tag categories when access is restricted
            $field = '<div style="display:none;">' . $field . '</div>';
        }
        return $field;
    }

    /**
     * Can the current be_user access the control tag category $controlTagCategoryUid
     * @param $tagTypeId int Tag type id (either content tag or control tag)
     * @param $controlTagCategoryUid int uid of control tag category
     * @param $allowedControlTagCategories array[array[uid, title]]
     * @return bool true, if current be_user can't access the control tag category, else false
     */
    private function isTagFieldHiddenInBackend($tagTypeId, $controlTagCategoryUid, $allowedControlTagCategories) {
        if ($tagTypeId != tx_newspaper_Tag::getControlTagType()) {
            return false; // Content tag field is always visible
        }
        // So tag field is a control tag field
        if (!is_array($allowedControlTagCategories) || !sizeof($allowedControlTagCategories)) {
            return false; // No restrictions were configured
        }

        foreach($allowedControlTagCategories as $controlTagCategory) {
            if ($controlTagCategory['uid'] == $controlTagCategoryUid) {
                return false; // Access to control tag category, so don't hide
            }
        }

        return true;
    }

    private function fillItemValues($articleId, $tagType, $category = false) {
        $where = " AND tag_type = " . $tagType;
        $where .= " AND uid_local = " . $articleId;
        if($category)
            $where .= ' AND ctrltag_cat=' . $category;

        $tags = tx_newspaper_DB::getInstance()->selectMMQuery('uid_foreign, tag', 'tx_newspaper_article',
            'tx_newspaper_article_tags_mm', 'tx_newspaper_tag', $where);
        $items = array();
        foreach($tags as $i => $tag) {
            $items[] = $tags[$i]['uid_foreign'].'|'.$tags[$i]['tag'];
        }
//t3lib_div::devLog('fillItemValues', 'newspaper', 0, array('items' => $items, 'tags' => $tags) );
        return implode(',', $items);
    }

    /**
     * Adds a text input field with auto completer to the $selectBox
     * @param $selectBox HTML code containng a seelct box
     * @param $articleId
     * @param $tagType
     * @return replaced
     */
    private function addTagInputField($selectBox, $articleId, $tagType) {
//t3lib_div::devlog('tag select box', 'np', 0 ,array('sB' => $selectBox, 'aId' => $articleId, 'tT' => $tagType));

        if (tx_newspaper::getTypo3Version() < 4005000) {
            // Pattern: HTML code TCEform 4.2.x generated
            $pattern = '<select name="data\[tx_newspaper_article\]\[' . $articleId . '\]\[' . $tagType . '\]_sel.*</select>';
        } else {
            // Pattern: HTML code TCEform 4.5.x (and above) generated
            $pattern = '<select id="tceforms-multiselect-[0-9a-f]*" name="data\[tx_newspaper_article\]\[' . $articleId . '\]\[' . $tagType . '\]_sel.*</select>';
        }

        $with = '<input type="text" id="autocomplete_' . $tagType . '" /><span id="indicator_' . $tagType .
            '" style="display: none"><img src="gfx/spinner.gif" alt="Working..." /></span><div id="autocomplete_choices_' .
            $tagType . '" class="autocomplete"></div>';
        return $this->replaceIncludingEndOfLine($selectBox, $with, $pattern, true); // Re-insert match!
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
        $newText = $this->replaceEol($what);
        $toReplace = '|(' . $pattern . ')|m'; // with 'm' option . matches EOL
        preg_match($toReplace, $newText, $matches);
        $hasMatches = (count($matches) > 0);
        if($hasMatches) {
            if($reinsertMatch) {
                $fld = preg_replace($toReplace, $with . $matches[0], $newText);
            } else {
                $fld = preg_replace($toReplace, $with, $newText);
            }
        }
        return $hasMatches? $fld : $what;
    }

    private function replaceEol($text) {
        $text = str_replace("\r\n","\n",$text);
        $text = str_replace("\n","\r",$text);
        // convert blank lines too
        return preg_replace("/\n{2,}/","\r\r",$text);
    }

    /**
     * @todo no usages found - remove?
     * $TCA['tx_newspaper_article']['columns']['tags']['config']['itemsProcFunc'] = 'tx_newspaper_be->getArticleTags';
     * @param $params
     * @param $pObj Parent object
     * @return void
     * @throws tx_newspaper_Exception
     */
    public function getArticleTags(&$params, &$pObj) {
//t3lib_div::devlog('getArticleTags', 'newspaper', 0, array('params' => $params) );
        if(!intval($params['row']['uid'])) {
            return; // New articles can't have tags ...
        }

        $article = new tx_newspaper_Article(intval($params['row']['uid']));
        if($params['field'] == 'tags') {
			$tags = $article->getTags(tx_newspaper_tag::getContentTagType());
        } else if(stristr($params['field'], 'tags_ctrl')) {
            $category = array_pop(explode('_',$params['field']));
            $tags = $article->getTags(tx_newspaper_tag::getControlTagType(), $category);
        } else {
            throw new tx_newspaper_Exception("field '" . $params['field'] . "' unkown");
        }
        $items = array();
        foreach($tags as $tag) {
            $items[] = array($tag->getAttribute('tag'), $tag->getUid(), '');
        }
//t3lib_div::devlog('getArticleTags--items', 'newspaper', 0, array('tags' => $items));
        $params['items'] = $items;
    }



    private function getFindTagsJs($articleId, $ctrlCatUids) {
        return <<<JSCODE
<link rel="stylesheet" type="text/css" href="../typo3conf/ext/newspaper/res/be/autocomplete.css" />
<script type="text/javascript" src="contrib/scriptaculous/scriptaculous.js?load=builder,effects,controls,dragdrop"></script>
    <script language="JavaScript">
        var mapSelector = function(instance) {
                var ret = []; // Beginning matches
                var partial = []; // Inside matches
                var entry = instance.getToken();
                var count = 0;

                instance.options.array.each(
                    function(pair) {
                        var elem = pair.value;
                        var foundPos = instance.options.ignoreCase ?
                            elem.toLowerCase().indexOf(entry.toLowerCase()) :
                            elem.indexOf(entry);

                        while (foundPos != -1) {
                            if (foundPos == 0 && elem.length != entry.length) {
                              ret.push('<li id="'+pair.key+'">' + elem.substr(0, entry.length) +
                                elem.substr(entry.length) + "</li>");
                              break;
                            } else if (entry.length >= instance.options.partialChars &&
                              instance.options.partialSearch && foundPos != -1) {
                              if (instance.options.fullSearch || /\s/.test(elem.substr(foundPos-1,1))) {
                                partial.push('<li id="'+pair.key+'">' + elem.substr(0, foundPos) +
                                  elem.substr(foundPos, entry.length) + elem.substr(
                                  foundPos + entry.length) + "</li>");
                                break;
                              }
                            }

                            foundPos = instance.options.ignoreCase ?
                              elem.toLowerCase().indexOf(entry.toLowerCase(), foundPos + 1) :
                              elem.indexOf(entry, foundPos + 1);
                        }
                    }
                  );
                if (partial.length)
                  ret = ret.concat(partial.slice(0, instance.options.choices - ret.length));
                return "<ul>" + ret.join('') + "</ul>";
            }

      var MyCompleter = Class.create(Autocompleter.Local, {
                    getUpdatedChoices: function() {
                        var serverChoices = this.options.selector(this);
                        var currentChoice = this._getCurrentInputAsPartialList();
                        var allChoices = serverChoices.replace(/<ul>/, currentChoice);
                        this.updateChoices(allChoices);
                     },

                     _getCurrentInputAsPartialList: function() {
                            return "<ul><li>" + this.getToken() + "<" + "/li>";
                     },

                     selectEntry : function(\$super) {
                         \$super();
                        this.element.value = '';
                     }
            });
    document.observe("dom:loaded", function() {
        var path = window.location.pathname;
        var test = path.substring(path.lastIndexOf("/") - 5);
        if (test.substring(0, 6) == "typo3/") {
            path = path.substring(0, path.lastIndexOf("/") - 5); // -5 -> cut of "typo3"
        } else if (path.indexOf("typo3conf/ext/newspaper/") > 0) {
            path = path.substring(0, path.indexOf("typo3conf/ext/newspaper/"));
        }

        var ctrlCats = [$ctrlCatUids];
        $$('[name="data[tx_newspaper_article][$articleId][tags]_sel"]')[0].hide();
        //create completer and tag caches for content- and control-tags
        createTagCompletion('tags', mapSelector, insertTag, true, null);
        ctrlCats.each(
                function(ctrlCat) {
                    var ctrlCatName = 'tags_ctrl_'+ ctrlCat;
                    $$('[name="data[tx_newspaper_article][$articleId]['+ctrlCatName+']_sel"]')[0].hide();

                    //without timeout the second autosuggest is not created properly, maybe because of ajax.
                    window.setTimeout(function() {createTagCompletion(ctrlCatName, mapSelector, addOnlyExistingTag, false, ctrlCat)}, 1000);
        });
     });

    /**
     * insertTagFunction is the function to be called when inserting tags
     * though it is possible too add different logic whether adding content- or control-tags
     */
    function createTagCompletion(tagType, mySelector, insertTagFunction, addCurrentInput, ctrlCatId) {
        //get all tags so they are cached
        return new top.Ajax.Request(path + 'typo3conf/ext/newspaper/mod1/index.php', {
                                method: 'get',
                                parameters: {param: 'tag-getall', type: tagType, ctrlCat: ctrlCatId},
                                onSuccess: function(request) {
                                                var serverTags = request.responseText.evalJSON();
                                                //had problems when using !serverTags instead serverTags == false
                                                var choices = (serverTags == false) ? new Hash() : new Hash(serverTags);
                                                new MyCompleter('autocomplete_'+tagType, 'autocomplete_choices_'+tagType, choices, {
                                                    frequency : 0.01,
                                                    selector : mySelector,
                                                    afterUpdateElement : function(currInput, selectedElement) {
                                                                            insertTagFunction(currInput, selectedElement, tagType);
                                                                         }
                                                });
                                           },
                            });
     }

     /**
      * only existing tags are allowed
      */
     function addOnlyExistingTag(currInput, selectedElement, tagType) {
         if(selectedElement.id) {
            setFormValueFromBrowseWin('data[tx_newspaper_article][$articleId]['+tagType+']',selectedElement.id, selectedElement.innerHTML); TBE_EDITOR.fieldChanged('tx_newspaper_article','$articleId','tags','data[tx_newspaper_article][$articleId][tags]');
         }
     }


     /**
      * adds tags and creates non-exising ones
      */
     function insertTag(currInput, selectedElement, tagType) {
         if(!selectedElement.id) {
            //neuen tag einfügen
            new top.Ajax.Request(path +  'typo3conf/ext/newspaper/mod1/index.php', {
                    method: 'get',
                    parameters: {param : 'tag-insert', type : tagType, tag : selectedElement.innerHTML},
                    onSuccess: function(request) {
                                    var newElem = request.responseText.evalJSON(true);
                                    setFormValueFromBrowseWin('data[tx_newspaper_article][$articleId]['+tagType+']',newElem.uid, newElem.tag); TBE_EDITOR.fieldChanged('tx_newspaper_article','$articleId','tags','data[tx_newspaper_article][$articleId]['+tagType+']');
                               }
                });
        } else {
            setFormValueFromBrowseWin('data[tx_newspaper_article][$articleId]['+tagType+']',selectedElement.id, selectedElement.innerHTML); TBE_EDITOR.fieldChanged('tx_newspaper_article','$articleId','tags','data[tx_newspaper_article][$articleId][tags]');
        }
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

		$label['extra'] = self::getTranslation('label_extra');
		$label['show'] = self::getTranslation('label_show');
		$label['pass_down'] = self::getTranslation('label_pass_down');
		$label['inherits_from'] = self::getTranslation('label_inherits_from');
		$label['commands'] = self::getTranslation('label_commands');
		$label['extra_delete_confirm'] = self::getTranslation('message_delete_confirm');
		$label['paragraph'] = self::getTranslation('label_paragraph');
		$label['notes'] = self::getTranslation('label_notes');
		$label['templateset'] = self::getTranslation('label_templateset');
		$label['shortcuts'] = self::getTranslation('label_shortcuts');
	    $label['overview'] = self::getTranslation('overview');
	    $label['extra_cut_paste_confirm'] = self::getTranslation('message_cut_paste_confirm');
	    $label['extra_copy_paste_confirm'] = self::getTranslation('message_copy_paste_confirm');

		$smarty_pz = new tx_newspaper_Smarty();
		$smarty_pz->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod3/res/'));

		$smarty_pz->assign('LABEL', $label);

		$smarty_pz->assign('SAVE_ICON', tx_newspaper_BE::renderIcon('gfx/savedok.gif', '', self::getTranslation('label_save_extra')));
		$smarty_pz->assign('UNDO_ICON', tx_newspaper_BE::renderIcon('gfx/undo.gif', '', self::getTranslation('label_undo_extra')));

		$smarty_pz->assign('HIDE_ICON', tx_newspaper_BE::renderIcon('gfx/button_hide.gif', '', self::getTranslation('label_hide')));
		$smarty_pz->assign('UNHIDE_ICON', tx_newspaper_BE::renderIcon('gfx/button_unhide.gif', '', self::getTranslation('label_unhide')));
		$smarty_pz->assign('EDIT_ICON', tx_newspaper_BE::renderIcon('gfx/edit2.gif', '', self::getTranslation('label_edit_extra')));
		$smarty_pz->assign('MOVE_UP_ICON', tx_newspaper_BE::renderIcon('gfx/button_up.gif', '', self::getTranslation('label_move_up')));
		$smarty_pz->assign('MOVE_DOWN_ICON', tx_newspaper_BE::renderIcon('gfx/button_down.gif', '', self::getTranslation('label_move_down')));
		$smarty_pz->assign('NEW_BELOW_ICON', tx_newspaper_BE::renderIcon('gfx/new_record.gif', '', self::getTranslation('label_new_below')));
		$smarty_pz->assign('DELETE_ICON', tx_newspaper_BE::renderIcon('gfx/garbage.gif', '', self::getTranslation('label_delete')));
//		$smarty_pz->assign('REMOVE_ICON', tx_newspaper_BE::renderIcon('gfx/selectnone.gif', '', self::getTranslation('label_delete')));
		$smarty_pz->assign('COPY_ICON', tx_newspaper_BE::renderIcon('gfx/clip_copy.gif', '', self::getTranslation('label_copy')));
		$smarty_pz->assign('CUT_ICON', tx_newspaper_BE::renderIcon('gfx/clip_cut.gif', '', self::getTranslation('label_cut')));
		$smarty_pz->assign('COPY_PASTE_ICON', tx_newspaper_BE::renderIcon('gfx/clip_pasteafter.gif', '', self::getTranslation('label_copy_paste')));
		$smarty_pz->assign('CUT_PASTE_ICON', tx_newspaper_BE::renderIcon('gfx/clip_pasteafter.gif', '', self::getTranslation('label_cut_paste')));
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
        $latest = 0;
		for ($i = 0; $i < sizeof($extra_data); $i++) {
			if (intval($extra_data[$i]['paragraph']) !== $para) {
				$para = intval($extra_data[$i]['paragraph']); // store new paragraph
				$bg = ($bg == 1)? 0 : 1; // switch bg type
			}
			$extra_data[$i]['bg_color_type'] = $bg;

		}
        $data = array();
        $data['extras'] = $extra_data;
		return $data;

	}






	/**
     * @todo no usages found - remove?
     *  Adds workflow log input field and workflow log output to article backend
     */
	function getWorkflowCommentBackend($PA, $fobj) {
//t3lib_div::devlog('getWorkflowButtons()', 'newspaper', 0, array('PA[row]' => $PA['row']));

		/// add workflow comment field (using smarty)
 		$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array(PATH_typo3conf . 'ext/newspaper/res/be/templates'));

		$html .= $smarty->fetch('workflow_comment.tmpl');

		$html .= tx_newspaper_workflow::getJavascript();
		$html .= tx_newspaper_workflow::renderBackend('tx_newspaper_article', $PA['row']['uid']);

		// check if a section or control tag is assigned to the article
		// \todo: remove when a better way to show messages in article backend is available (flash message etc.)
		if (!$PA['row']['sections']) {
			$html .= '<script type="text/javascript">alert("' . tx_newspaper::getTranslation('message_article_no_section') . '");</script>';
		}

		return $html;
	}


	/// get html for this icon (may include an anchor)
	/** \param $image path to icon in typo3 skin; if path start with a "/" t3 skinning is bypassed and the file is referenced directly
	 *  \param $id if set, $id will be inserted as an html id
	 *  \param $title title for title flag of img
	 *  \param $ahref
	 *  \param $replaceWithCleargifIfEmpty if set to true the icon is replaced with clear.gif, if $ahref is empty
	 *  \param $width width in px
	 *  \param $height height in px
	 *  \$srcOnly if set to true, only the path to the image is returned otherwise a complete html img tag is returned
	 *  \return String <img ...> or <a href><img ...></a> (if linked)
	 */
	public static function renderIcon($image, $id, $title='', $ahref='', $replaceWithCleargifIfEmpty=false, $width=16, $height=16, $srcOnly=false) {

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
			 if (!$srcOnly) {
				// hide icon (= replace with clear.gif)
				$html = '<img' . $id . t3lib_iconWorks::skinImg($backPath, 'clear.gif', 'width="' . $width . '" height="' . $height . '"') . ' title="' . $title . '" alt="" />';
			} else {
				return 'clear.gif';
			}
		} else {
			// show icon
			if (substr($image, 0, 1) != '/') {
				if (!$srcOnly) {
					// typo3 skinning
					$html = '<img' . $id . t3lib_iconWorks::skinImg($backPath, $image) . ' title="' . $title . '" alt="" />';
				} else {
					return t3lib_iconWorks::skinImg($backPath, $image, '', 1); // just return the src
				}
			} else {
				if (!$srcOnly) {
					// absolute path, use given file withiout using typo3 skinning
					$html = '<img' . $id . ' src="' . $image . '" title="' . $title . '" alt="" />';
				} else {
					return $image;
				}
			}
		}
		if ($ahref)
			return $ahref . $html . '</a>'; // if linked wrap in link
		return $html; // return image html code

	}



	/**
	 * Add javascript and css files needed (adds to $GLOBALS['TYPO3backend'])
	 * Called by hook $GLOBALS['TYPO3_CONF_VARS']['typo3/backend.php']['additionalBackendItems'][]
	 */
	public static function addAdditionalScriptToBackend() {
		$GLOBALS['TYPO3backend']->addJavascriptFile(t3lib_extMgm::extRelPath('newspaper') . 'res/be/newspaper.js');
		// add modalbox - is used for placing extra on pagezones
		// add modalbox js to top (so modal box can be displayed over the whole backend, not only the content frame)
		$GLOBALS['TYPO3backend']->addJavascriptFile(t3lib_extMgm::extRelPath('newspaper') . 'contrib/subModal/newspaper_subModal.js');
		$GLOBALS['TYPO3backend']->addCssFile('subModal', t3lib_extMgm::extRelPath('newspaper') . 'contrib/subModal/subModal.css');
	}


	// read tsconfig to render configured backend (default: subModal)
	public static function getExtraBeDisplayMode() {
		// read tsconfig from root newspaper sysfolder
		$tsc = t3lib_BEfunc::getPagesTSconfig(tx_newspaper_Sysfolder::getInstance()->getPidRootfolder());

		if (isset($tsc['newspaper.']['be.']['extra_in_article_mode'])) {
			switch (strtolower($tsc['newspaper.']['be.']['extra_in_article_mode'])) {
				case 'tabbed':
					return BE_EXTRA_DISPLAY_MODE_TABBED;
				break;
				case 'submodal':
					return BE_EXTRA_DISPLAY_MODE_SUBMODAL;
				break;
			}
		}

		return BE_EXTRA_DISPLAY_MODE_SUBMODAL; // default
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



	/// get a list of articles by a section id
	static function getArticleListBySectionId($sectionId) {

		$result = array();
		$sectionId = self::extractElementId($sectionId);
		$section = new tx_newspaper_section($sectionId);
		$listType = strtolower(get_class($section->getArticleList()));
		$articleList = self::getArticleListMaxArticles($section->getArticleList());

		// get offsets for semiautomtic list
		if ($listType == 'tx_newspaper_articlelist_semiautomatic') {
			$articleUids = self::getArticleIdsFromArticleList($articleList);
			$offsetList = $section->getArticleList()->getOffsets($articleUids);
		}

		// fill the section placers from their articlelists
		foreach ($articleList as $article) {
			if ($listType == 'tx_newspaper_articlelist_manual') {
				$result[$article->getAttribute('uid')] = $article->getAttribute('kicker') . ': ' . $article->getAttribute('title');
			}
			if ($listType == 'tx_newspaper_articlelist_semiautomatic') {
				$offset = $offsetList[$article->getAttribute('uid')];
				if ($offset > 0) {
					$offset = '+' . $offset;
				}
				$result[$offsetList[$article->getAttribute('uid')] . '_' . $article->getAttribute('uid')] = $article->getAttribute('kicker') . ': ' . $article->getAttribute('title') . ' (' . $offset . ')';
			}
		}

		return $result;
	}


		/// get a list of articles by a section id
	function getArticleListByArticlelistId($articlelistId, $articleId = false) {

		$result = array();

		$al_uid = intval($this->extractElementId($articlelistId));

		$al = tx_newspaper_ArticleList_Factory::getInstance()->create($al_uid);
		$articleList = self::getArticleListMaxArticles($al);
		$listType = $al->getTable();

		// get offsets
		if ($listType == 'tx_newspaper_articlelist_semiautomatic') {
			$articleUids = $this->getArticleIdsFromArticleList($articleList);
			$offsetList = $al->getOffsets($articleUids);
		}

		// prepend the article we are working on to list for semiautomatic lists
		if ($listType == 'tx_newspaper_articlelist_semiautomatic' && $articleId) {
			$article = new tx_newspaper_Article($articleId);
			$result['0_' . $article->getAttribute('uid')] = $article->getAttribute('kicker') . ': ' . $article->getAttribute('title');
		}

		// fill the articlelist
		foreach ($articleList as $article) {
			if ($listType == 'tx_newspaper_articlelist_manual') {
				$result[$article->getAttribute('uid')] = $article->getAttribute('kicker') . ': ' . $article->getAttribute('title');
			}
			if ($listType == 'tx_newspaper_articlelist_semiautomatic') {
				$offset = $offsetList[$article->getAttribute('uid')];
				if ($offset > 0) {
					$offset = '+' . $offset;
				}
				$result[$offsetList[$article->getAttribute('uid')] . '_' . $article->getAttribute('uid')] = $article->getAttribute('kicker') . ': ' . $article->getAttribute('title') . ' (' . $offset . ')';
			}
		}
//t3lib_div::devlog('getArticleListByArticlelistId()', 'newspaper', 0, array('result' => $result));
		return $result;
	}


    /**
     * @static
     * @param tx_newspaper_articlelist $al
     * @return tx_newspaper_Article[] articles from the article list $al (check the number of max articles in the article list AND self::getNumArticlesInArticleList())
     */
    public static function getArticleListMaxArticles(tx_newspaper_articlelist $al) {
		$max = ($al->getAttribute('num_articles'))?
			min($al->getAttribute('num_articles'), self::getNumArticlesInArticleList()) :
			self::getNumArticlesInArticleList();
        $al->useOptimizedGetArticles(true);
		return $al->getArticles($max);
	}

    public static function getNumArticlesInArticleList() {
        if (tx_newspaper::getTSConfigVar(self::num_articles_tsconfig_var)) {
            return tx_newspaper::getTSConfigVar(self::num_articles_tsconfig_var);
        }
        return self::default_num_articles_in_articlelist;
    }

	/// extract the section uid out of the select elements mames that are
	/// like "placer_10_11_12" where we need the "12" out of it
	static function extractElementId($sectionId) {
		if (strstr($sectionId, '_')) {
			$sectionId = explode('_', $sectionId);
			$sectionId = $sectionId[count($sectionId)-1];
		}
		return $sectionId;
	}


	/// extract just the article-uids from an article list
	static function getArticleIdsFromArticleList($articleList) {
		// collect all article uids
		$articleUids = array();
		foreach ($articleList as $article) {
			$articleUids[] = $article->getAttribute('uid');
		}
		return $articleUids;
	}


	/** \todo typo or not? \c renderIcon('...gif','',$LANG->sL('...',false,14,14)) or \c renderIcon('...gif','',$LANG->sL('...',false),14,14) ?
	 */
	public static function getArticlelistIcons() {
		global $LANG;
		return array(
			'group_totop' => tx_newspaper_BE::renderIcon('gfx/group_totop.gif', '', $LANG->sL('LLL:EXT:newspaper/mod7/locallang.xml:label_group_totop', false, 14, 14)),
			'up' => tx_newspaper_BE::renderIcon('gfx/up.gif', '', $LANG->sL('LLL:EXT:newspaper/mod7/locallang.xml:label_up', false, 14, 14)),
			'down' => tx_newspaper_BE::renderIcon('gfx/down.gif', '', $LANG->sL('LLL:EXT:newspaper/mod7/locallang.xml:label_down', false, 14, 14)),
			'group_tobottom' => tx_newspaper_BE::renderIcon('gfx/group_tobottom.gif', '', $LANG->sL('LLL:EXT:newspaper/mod7/locallang.xml:label_group_tobottom', false, 14, 14)),
			'group_clear' => tx_newspaper_BE::renderIcon('gfx/group_clear.gif', '', $LANG->sL('LLL:EXT:newspaper/mod7/locallang.xml:label_group_clear', false, 14, 14)),
			'button_left' => tx_newspaper_BE::renderIcon('gfx/button_left.gif', '', $LANG->sL('LLL:EXT:newspaper/mod7/locallang.xml:label_button_left', false, 14, 14)),
			'button_right' => tx_newspaper_BE::renderIcon('gfx/button_right.gif', '', $LANG->sL('LLL:EXT:newspaper/mod7/locallang.xml:label_button_right', false, 14, 14)),
			'preview' => tx_newspaper_BE::renderIcon('gfx/zoom.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.preview_article', false)),
			'articlebrowser' => tx_newspaper_BE::renderIcon('gfx/insert3.gif', '', $LANG->sL('LLL:EXT:newspaper/mod7/locallang.xml:label_button_articlebrowser', false, 14, 14)),
			'edit' => tx_newspaper_BE::renderIcon('gfx/edit2.gif', '', $LANG->sL('LLL:EXT:newspaper/mod7/locallang.xml:label_edit_articlelist', false)),
			'save' => tx_newspaper_BE::renderIcon('gfx/savedok.gif', '', '', '', false, 0, 0, true),
			'close' => tx_newspaper_BE::renderIcon('gfx/close.gif', '', $GLOBALS['LANG']->sL('LLL:EXT:newspaper/mod7/locallang.xml:label_close', false))
		);
	}

	/**
	 * Replaces element browser with article browser (if configured to do so)
	 * $GLOBALS['newspaper']['replaceEBwithArticleBrowser'][table] = array(field1, ..., fieldn);
	 * @param $table Typo3 table
	 * @param $field Field in table
	 * @param $uid of the record
	 * @param $out   HTML code that might be processed (if configured in $GLOBALS['newspaper']['replaceEBwithArticleBrowser'])
	 * @return void
	 */
	public static function checkReplaceEbWithArticleBrowser($table, $field, $uid, &$out) {
//t3lib_div::devlog('checkReplaceEbWithArticleBrowser()', 'newspaper', 0, array('GLOBALS[newspaper]' => $GLOBALS['newspaper'], 'table' => $table, $field => $field));
		//$GLOBALS['newspaper']['replaceEBwithArticleBrowser']['tx_newspaper_article'] = array(field1, ... fieldn);
		//$GLOBALS['newspaper']['replaceEBwithArticleBrowser'][another_table] = array(field1, ... fieldn);
		if (self::checkEbConfig($table, $field, 'replaceEBwithArticleBrowser')) {

			// Typo3 param for element browser
			$jsParams = 'data[' . $table . '][' . $row['uid'] . '][' . $field . ']|||tx_newspaper_article|';

			// conf for newspaper article browser
			$js = '<script type="text/javascript" src="../typo3conf/ext/newspaper/res/be/newspaper.js"> </script>
<script type="text/javascript">
	NpBackend.param["ElementBrowserUrl"] = "' . tx_newspaper::getAbsolutePath() .  'typo3conf/ext/newspaper/mod2/index.php?mode=db&bparams=' . $jsParams . '&form_table=' . $table . '&form_field=' . $field . '&form_uid=' . $uid . '";
	NpBackend.param["ElementBrowserWidth"] = 925;
	NpBackend.param["ElementBrowserHeight"] = 485;
</script>';

			// replace eb with article browser
			$replace = $js . '<a href="#" onclick="NpBackend.setFormValueOpenBrowser(); return false;" >';
			$out = preg_replace('/<a [^>]*setFormValueOpenBrowser[^>]*>/i', $replace, $out);
		}
	}


	/**
	 * Replaces Typo3 element browser with newspaper Extra browser (if configured to do so)
	 * $GLOBALS['newspaper']['replaceEBwithExtraBrowser'][table] = array(field1, ..., fieldn);
	 * @param $table Typo3 table
	 * @param $field Field in table
	 * @param $uid   uid in table
	 * @param $out   HTML code for the backend
	 */
	public static function checkReplaceEbWithExtraBrowser($table, $field, $uid, &$out) {
//t3lib_div::devlog('checkReplaceEbWithExtraBrowser()', 'newspaper', 0, array('GLOBALS[newspaper]' => $GLOBALS['newspaper'], 'table' => $table, 'field' => $field, 'uid' => $uid));
		if (self::checkEbConfig($table, $field, 'replaceEBwithExtraBrowser')) {
			// add table and field name to js function name

		$js = '<script type="text/javascript" src="../typo3conf/ext/newspaper/res/be/newspaper.js"> </script>
<script type="text/javascript">
	NpBackend.param["ElementBrowserUrl"] = "' . tx_newspaper::getAbsolutePath() .  'typo3conf/ext/newspaper/mod1/index.php?tx_newspaper_mod1[controller]=eb&tx_newspaper_mod1[type]=e&tx_newspaper_mod1[allowMultipleSelection]=1&tx_newspaper_mod1[jsType]=Typo3&tx_newspaper_mod1[table]=' . $table . '&tx_newspaper_mod1[field]=' . $field . '&tx_newspaper_mod1[uid]=' . $uid . '";
	NpBackend.param["ElementBrowserWidth"] = 650;
	NpBackend.param["ElementBrowserHeight"] = 800;
</script>';

			// Replace Typo3 element broser with newspaper etxra browser
			//$replace = $js . '<a href="#" onclick="top.NpBackend.setFormValueOpenBrowser(\'db\',\'data[' . $table . '][' . $row['uid'] . '][' . $field . ']|||tx_newspaper_article|\', \'' . $table . '\', \'' . $field . '\', \'' . $uid . '\'); return false;" >';
			$replace = $js . '<a href="#" onclick="NpBackend.setFormValueOpenBrowser(); return false;" >';
			$out = preg_replace('/<a [^>]*setFormValueOpenBrowser[^>]*>/i', $replace, $out);
//t3lib_div::devlog('checkReplaceEbWithExtraBrowser()', 'newspaper', 0, array('GLOBALS[newspaper]' => $GLOBALS['newspaper'], 'table' => $table, $field => $field, 'out' => $out));
		}
	}

	/**
	 * Adds an edit icon to a record browser field
	 * @param $table Typo3 table
	 * @param $field Field in table
	 * @param $uid   uid in table
	 * @param $out   HTML code for the backend
	 */
	public static function checkAddEditInRelationField($table, $field, $uid, &$out) {
		if (self::checkEbConfig($table, $field, 'replaceEBwithExtraBrowser')) {
//t3lib_div::devlog('checkAddEditInRelationField()', 'newspaper', 0, array('GLOBALS[newspaper]' => $GLOBALS['newspaper'], 'table' => $table, 'field' => $field, 'uid' => $uid, 'out' => $out));

			// html code for linked edit icon
			$html = '<a onclick="var addEditSelectValue = document.getElementsByName(\'data[' . $table . '][' . intval($uid) . '][' . $field . ']_list\')[0].value; if (!addEditSelectValue) {return false;} vHWin=window.open(\'' . tx_newspaper::getAbsolutePath() .  'typo3conf/ext/newspaper/mod1/index.php?tx_newspaper_mod1[controller]=eb&tx_newspaper_mod1[type]=editextra&tx_newspaper_mod1[abstractExtra]=\' + addEditSelectValue ,\'\',\'width=670,height=500,status=0,menubar=0,scrollbars=1,resizable=1\');vHWin.focus();return false;" href="#"><img width="16" height="16" alt="" title="" src="' . tx_newspaper::getAbsolutePath() .  'typo3/sysext/t3skin/icons/gfx/edit2.gif"></a>';

			// insert edit icon INBETWEEN of last </a></td> \todo: better way, this looks like a hack ....
			$p = strrpos($out, '</a></td>');
			if ($p !== false) {
				// insert edit icon
				$out = substr($out, 0, $p+4) . $html . substr($out, $p+4);
			}

		}
	}


	/** Returns true if table and field are set for key, else false
	 * $GLOBALS['newspaper']['replaceEBwithExtraBrowser']['tx_newspaper_article'] = array(field1, ... fieldn);
	 * $GLOBALS['newspaper']['replaceEBwithExtraBrowser'][another_table] = array(field1, ... fieldn);
	 * @param $table Typo3 record to check
	 * @param $field Field in table
	 * @param $key   Key in configuration array
	 */
	private static function checkEbConfig($table, $field, $key) {
		return array_key_exists($key, $GLOBALS['newspaper']) &&
			array_key_exists(strtolower($table), $GLOBALS['newspaper'][$key]) &&
			in_array(strtolower($field), $GLOBALS['newspaper'][$key][strtolower($table)]);
	}




	// Typo3 hooks

    /**
     * Do some clean up when user logs off Typo3, called by Typo3 log off hook
     */
	public function cleanUpBeforeLogoff() {

        if (TYPO3_MODE != 'BE') return;

        self::clearClipboard();

	}



}

?>