<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Helge Preuss, Oliver Schröder, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');

require_once(PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]


/// Class to generate a BE module with 100% width
class fullWidthDoc_mod2 extends template {
	var $divClass = 'typo3-fullWidthDoc';	///< Sets width to 100%
}


/**
 * Module 'Moderation list' for the 'newspaper' extension.
 *
 * @author	Helge Preuss, Oliver Schr�der, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
 */
class  tx_newspaper_module2 extends t3lib_SCbase {
	var $pageinfo;

	private $LL=array(); // localized strings

	private $prefixId = 'tx_newspaper_mod2';
	private $input=array(); // store get params (based on $this->prefixId)

	/**
	 * Initializes the Module
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		parent::init();
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 * @return	void
	 */
	function menuConfig()	{
		$this->MOD_MENU = array();
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return	[type]		...
	 */
	function main()	{
		global $BE_USER,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		// Access check!
		$access = $BE_USER->user['uid']? true : false; // \todo: better check needed

		if ($access) {
//t3lib_div::devlog('main()', 'newspaper',0, array('_r' => $_REQUEST));

			$this->input = t3lib_div::GParrayMerged($this->prefixId); // read params

			$this->processAjaxController(); // process Ajax request (teminutes with die() id any

			$this->processFilter(); // checks filter setting, adds default values if a mandatory filter setting is missing



			// get ll labels
			$localLang = t3lib_div::readLLfile('typo3conf/ext/newspaper/mod2/locallang.xml', $GLOBALS['LANG']->lang);
			$this->LL = $localLang[$GLOBALS['LANG']->lang];

				// Draw the header.
			$this->doc = t3lib_div::makeInstance('fullWidthDoc_mod2');
			$this->doc->backPath = $BACK_PATH;
//			$this->doc->form='<form action="" method="POST">'; // hide , so form id="moderation" is visible, can't nest forms

				// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
			';
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = 0;
				</script>
			';

			$this->content .= $this->doc->startPage('');

			// Render content:
			$this->moduleContent();

			$this->content.=$this->doc->spacer(10);
		} else {
				// If no access or if ID == zero

			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;

			$this->content.=$this->doc->startPage($this->LL['title']);
			$this->content.=$this->doc->header($this->LL['title']);
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	void
	 */
	function printContent()	{
		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 *
	 * @return	void
	 */
	function moduleContent() {
//t3lib_div::devlog('where', 'newspaper', 0, array('where' => $this->createWherePartArray()));

		$where = $this->createWherePartArray(); // get conditions for sql statement

		if ($where !== false) {

			$count = tx_newspaper::countRows($where['table'], $where['where']);

			$row = tx_newspaper::selectRows(
				'DISTINCT tx_newspaper_article.*', // Make sure articles are list once only, even if assigned to multiple secions
				$where['table'],
				$where['where'],
				'',
				'tstamp DESC',
				intval($this->input['startPage']) * intval($this->input['step']) . ', ' . (intval($this->input['step']))
			);
//t3lib_div::devlog('row', 'newspaper', 0, array('query' => tx_newspaper::$query, 'row' => $row));
		} else {
			$count = 0;
			$row = array(); // empty result
		}

		$content = $this->renderBackendSmarty($row, $count);

		$this->content .= $this->doc->section('', $content, 0, 1);
//t3lib_div::devlog('mod2', 'newspaper', 0, array('content' => htmlspecialchars($content), 'this->content' => htmlspecialchars($this->content)));
	}


	/**
	 *
	 * \param $row article to be rendered
	 * \param $count total number of article found for current filter settings
	 * \return HTML code, rendered backend
	 */
	function renderBackendSmarty($row, $count) {

 		$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod2/res/'));

		$smarty->assign('LL', $this->LL); // localized labels

		$smarty->assign('IS_ADMIN', $GLOBALS['BE_USER']->user['admin']);
		$smarty->assign('IS_DUTY_EDITOR', tx_newspaper_workflow::isDutyEditor());

		$smarty->assign('RESULT_COUNT', intval($count));

		$smarty->assign('RANGE', $this->getRangeArray()); // add data for range dropdown

		$smarty->assign('STEP', array(10, 20, 30, 50, 100)); // add data for step dropdown (for page browser)
		$smarty->assign('START_PAGE', intval($this->input['startPage']));

		$smarty->assign('HIDDEN', $this->getHiddenArray()); // add data for "hidden" dropdown

		$smarty->assign('ROLE', $this->getRoleArray()); // add data for role dropdown
		$smarty->assign('ROLE_FILTER_EQUALS_USER_ROLE', $this->isRoleFilterEqualToUserRole());

		$image_path = tx_newspaper::getAbsolutePath() . 'typo3conf/ext/newspaper/res/icons/';
		$smarty->assign('ICON', $this->getIcons());

		$smarty->assign('AB4AL', (t3lib_div::_GP('ab4al'))? t3lib_div::_GP('ab4al') : ''); // article browser for article lists
		$smarty->assign('IS_ARTICLE_BROWSER', (t3lib_div::_GP('form_table') || t3lib_div::_GP('ab4al'))? 1 : 0); // set flag if mod2 should be rendered as production list or as article browser
		$smarty->assign('select_box_id', (t3lib_div::_GP('select_box_id'))? t3lib_div::_GP('select_box_id') : ''); // selectbox id for article browser

		// some values for article browser functionality for Typo3 fields, tx_newspaper_be::checkReplaceEbWithArticleBrowser()
		$smarty->assign('FORM_TABLE', (t3lib_div::_GP('form_table'))? t3lib_div::_GP('form_table') : '');
		$smarty->assign('FORM_FIELD', (t3lib_div::_GP('form_field'))? t3lib_div::_GP('form_field') : '');
		$smarty->assign('FORM_UID', intval(t3lib_div::_GP('form_uid'))? intval(t3lib_div::_GP('form_uid')) : 0);

		$smarty->assign('MODULE5_PATH', tx_newspaper::getAbsolutePath() . 'typo3conf/ext/newspaper/mod5/'); // path to typo3, needed for edit article (form: /a/b/c/typo3/)

		// check if article is locked, add be_user to array and add workflow log
		$locked_article = array();
		for ($i = 0; $i < sizeof($row); $i++) {
			// is article locked?
			$t = t3lib_BEfunc::isRecordLocked('tx_newspaper_article', $row[$i]['uid']);
			if (isset($t['record_uid'])) {
				$locked_article[$i] = array(
					'username' => $t['username'],
					'msg' => htmlentities($t['msg'])
				);
			}

			// add role title
			$row[$i]['workflow_status_TITLE'] = tx_newspaper_workflow::getRoleTitle($row[$i]['workflow_status']);

			// add workflowlog data to $row - new layout for production list version for mod2_main_v2.tmpl
			$row[$i]['workflowlog_v2'] = tx_newspaper_workflow::getComments('tx_newspaper_article', $row[$i]['uid']);

   			// add extended workflowlog data to $row - displayable on demand
			$row[$i]['workflowlog_all'] = tx_newspaper_workflow::getComments('tx_newspaper_article', $row[$i]['uid'], 0, 1);

			// add sections
			$a = new tx_newspaper_article(intval($row[$i]['uid']));
			$sections = array();
			foreach($a->getSections() as $current_section) {
				$sections[] = $current_section->getAttribute('section_name');
			}
			$row[$i]['sections'] = implode(', ', $sections);
		}

        // redundant because tx_newspaper_workflow::renderBackend() calls it too; added for clarity's sake
        tx_newspaper_Workflow::addWorkflowTranslations($smarty);

		$smarty->assign('LOCKED_ARTICLE', $locked_article);


        // Publish date, starttime and endtime
		for ($i = 0; $i < sizeof($row); $i++) {
            // Add information for time controlled articles
			$row[$i]['time_controlled_not_yet'] = $this->insertStartEndtime($this->LL['label_time_controlled_not_yet'], $row[$i]['starttime'] ,$row[$i]['endtime']);
            $row[$i]['time_controlled_not_yet_with_endtime'] = $this->insertStartEndtime($this->LL['label_time_controlled_not_yet_with_endtime'], $row[$i]['starttime'] ,$row[$i]['endtime']);
			$row[$i]['time_controlled_not_anymore'] = $this->insertStartEndtime($this->LL['label_time_controlled_not_anymore'], $row[$i]['starttime'] ,$row[$i]['endtime']);
            $row[$i]['time_controlled_not_anymore_with_starttime'] = $this->insertStartEndtime($this->LL['label_time_controlled_not_anymore_with_starttime'], $row[$i]['starttime'] ,$row[$i]['endtime']);
			$row[$i]['time_controlled_now_and_future'] = $this->insertStartEndtime($this->LL['label_time_controlled_now_and_future'], $row[$i]['starttime'] ,$row[$i]['endtime']);
			$row[$i]['time_controlled_now_but_will_end'] = $this->insertStartEndtime($this->LL['label_time_controlled_now_but_will_end'], $row[$i]['starttime'] ,$row[$i]['endtime']);

            // Add formatted publish date
			$row[$i]['formattedPublishdate'] = $this->getFormattedPublishDate($row[$i]['publish_date']);
		}

		$smarty->assign('DATA', $row);

		if (!isset($this->input['step'])) {
			$this->input['step'] = 10; // set default
		}
		if (!isset($this->input['startPage'])) {
			$this->input['startPage'] = 0; // set default
		}
		$smarty->assign('FILTER', $this->input); // add filter settings (for setting selected values in select boxes and text fields)

		$smarty->assign('MAX_PAGE', $this->calculateMaxPage($count, $this->input['step']));

		$smarty->assign('T3PATH', tx_newspaper::getAbsolutePath() . 'typo3/');
		$smarty->assign('ABSOLUTE_PATH', tx_newspaper::getAbsolutePath());

		if ($this->isArticleBrowser()) {
			return $smarty->fetch('mod2_articlebrowser.tmpl'); // article browser
		}
		return $smarty->fetch('mod2_main_v2.tmpl'); // production list
	}

	/**
	 * Format timestamp for production list output (skips year if year is current year)
	 * @param $tstamp Timestamp
	 * @return Formatted publish date
	 */
	private function getFormattedPublishDate($tstamp) {
		$tstamp = intval($tstamp);
		if (!$tstamp) {
			return ''; // no timestamp set
		}
		return (date("Y", $tstamp) == date("Y", time()))? date("d.m", $tstamp) : date("d.m.Y", $tstamp);
	}

	/**
	 * Calculate the last page number for $count record with $step records per page
	 * \param $count Total number of records
	 * \param $step  Number of records per page
	 * \return Number of last page in browse sequence
	 */
	private function calculateMaxPage($count, $step) {
		return intval($count / $step);
	}

	// \return Array with icons for the backend
	private function getIcons() {
		return array(
			'hide' => tx_newspaper_BE::renderIcon('gfx/button_hide.gif', '', $this->LL['label_hide']),
			'unhide' => tx_newspaper_BE::renderIcon('gfx/button_unhide.gif', '', $this->LL['label_unhide']),
			'previewArticle' => tx_newspaper_BE::renderIcon('gfx/zoom.gif', '', $this->LL['label_preview_article']),
			'editArticle' => tx_newspaper_BE::renderIcon('gfx/edit2.gif', '', $this->LL['label_edit_article']),
			'comment' => tx_newspaper_BE::renderIcon('gfx/zoom2.gif', '', '###COMMENT###'),
			'timeHidden' => tx_newspaper_BE::renderIcon('gfx/history.gif', '', $this->LL['label_time']),
			'timeVisible' => tx_newspaper_BE::renderIcon('gfx/icon_ok2.gif', '', $this->LL['label_time']),
			'deleteArticle' => tx_newspaper_BE::renderIcon('gfx/garbage.gif', '', $this->LL['label_delete_article']),
			'published' => tx_newspaper_BE::renderIcon('gfx/button_hide.gif', '', $this->LL['label.published']),
			'hidden' => tx_newspaper_BE::renderIcon('gfx/button_unhide.gif', '', $this->LL['label_hidden']),
			'published' => tx_newspaper_BE::renderIcon('gfx/icon_ok2.gif', ''),
			'recordLocked' => tx_newspaper_BE::renderIcon('gfx/recordlock_warning3.gif', '', '###LOCK_MSG###', false),
			'placeArticle' => tx_newspaper_BE::renderIcon('gfx/list.gif', '', $this->LL['label_article_placement']),
			'addArticle' =>  tx_newspaper_BE::renderIcon('gfx/plusbullet2.gif', '', $this->LL['label_article_add']),
			'wizard' => tx_newspaper_BE::renderIcon('gfx/wizard_rte2.gif', '', $this->LL['label_start_wizard']),
		);
	}

	/// \return true if an article browser is rendered, false if production list is rendered
	private function isArticleBrowser() {
		// form_table -> article browser for Typo3 fields
		// ab4al article browser for articlelists
		return t3lib_div::_GP('form_table') || t3lib_div::_GP('ab4al');
	}

	/// \return true if production list is rendered, false if an article browser is rendered
	private function isProductionList() {
		return !$this->isArticleBrowser();
	}

	/// \return true if role filter equals the current role of the be_user, else false
	private function isRoleFilterEqualToUserRole() {
		return ($this->input['role'] ==  tx_newspaper_workflow::getRole());
	}

	private function insertStartEndtime($string, $starttime, $endtime) {
// @todo: time format string should be configurable
		$string = str_replace('###STARTTIME###', date("d.m.Y, H:i:s", $starttime), $string);
		$string = str_replace('###ENDTIME###', date("d.m.Y, H:i:s", $endtime), $string);
		return $string;
	}




	/// Read filter setting from get params (set default values if not set), stores in $this->input
	function processFilter() {
//t3lib_div::devlog('processFilter()', 'newspaper', 0, array('_r' => $_REQUEST, 'input' => $this->input));
		if ($this->input['type'] == 'filter' || $this->input['type'] == 'reset_startpage') {
			// use filter settings, add default values if needed
			// no_reset = 1 -> if an article is publish or deleted etc.: don't reset filter settings
			$filter = $this->addDefaultFilterValues($this->input);
			if ($this->input['type'] == 'reset_startpage') {
				$filter['startPage'] = 0; // reset startPage if filter settings were submitted
			}
		} else {
			// module was called from menu or filter were resetted
			$filter = $this->addDefaultFilterValues(array(), true); // Get default values
		}

		$this->input = $filter; // store filter setting (no matter in receive by get param or default value)
//t3lib_div::devlog('processFilter()', 'newspaper',0, array('input' => $this->input));
	}

/*
 * currently obsolete, filter is reset each time the module is called
 * const prodListFilterKey = 'tx_newspaper/mod2/index.php/filter_prodlist'; // stores filter setting in be_user

	/// \return filter settings (checks if settings for production list or article browser are requested)
	private function getFilter() {
		if ($this->isProductionList()) {
			return unserialize($GLOBALS['BE_USER']->getModuleData(self::prodListFilterKey));
		} else {
			return array(); // no filter is stored for the article browser
		}
	}

	/// Stores the filter setting (check whether to store production list or article browser settings)
	private function storeFilter() {
		// No need to store article browser filter settings. Filters are reset each time an article browser is called.
		if ($this->isProductionList()) {
			$GLOBALS['BE_USER']->pushModuleData(self::prodListFilterKey, serialize(array(
				'range' => $this->input['range'],
				'hidden' => $this->input['hidden'],
				'role' => intval($this->input['role']),
				'author' => $this->input['author'],
				'be_user' => $this->input['be_user'],
				'section' => $this->input['section'],
				'text' => $this->input['text'],
				'step' => intval($this->input['step']),
				'startPage' => 0 // always start on first result page
			)));
		}
	}
*/


	/// Adds default filter settings if filter type is missing in given array
	/** if array $settings is empty or filled partly only, all missing filter values are filled with default values
     * \param $settings filter settings
	 * \param $forceReset if set to true some fields are forced to be filled with default values
	 * \return array with filter settings where missing filters were added (using default values)
	 */
	private function addDefaultFilterValues(array $settings, $forceReset=false) {
//t3lib_div::devlog('addDefaultFilterValues()', 'newspaper', 0, array('settings' => $settings, 'type' => $type));

		if (!array_key_exists('range', $settings) || !$settings['range'] || $forceReset) {
			if ($this->isProductionList()) {
				$settings['range'] = 'day_2'; // \todo: make tsconfigurable
			} else {
				$settings['range'] = 'day_180'; // \todo: make tsconfigurable
			}
		}

		if (!array_key_exists('hidden', $settings) || !$settings['hidden'] || $forceReset) {
			$settings['hidden'] = 'all';
		}

		if (!array_key_exists('role', $settings) || $forceReset) {
			if ($this->isArticleBrowser()) {
				$settings['role'] = '-1'; // all role, if article browser
			} else {
				$settings['role'] = tx_newspaper_workflow::getRole(); // current role of be_user
			}
		}

		if (!array_key_exists('author', $settings) || $forceReset) {
			$settings['author'] = '';
		}

		if (!array_key_exists('be_user', $settings) || $forceReset) {
			$settings['be_user'] = '';
		}

		if ($this->isProductionList()) {
			if (!array_key_exists('section', $settings) || $forceReset) {
                $settings['section'] = $this->getDefaultSection();
			}
		} elseif (!array_key_exists('section', $settings) && $this->isArticleBrowser()) {
			$settings['section'] = $_REQUEST['s']? $_REQUEST['s'] : $this->getDefaultSection();
		}

		if (!array_key_exists('text', $settings) || $forceReset) {
			$settings['text'] = '';
		}

		if (!array_key_exists('step', $settings)) {
			$settings['step'] = 10;
		}

		if (!array_key_exists('startPage', $settings) || $forceReset) {
			$settings['startPage'] = 0;
		}
//t3lib_div::devlog('addDefaultFilterValues() done', 'newspaper', 0, array('settings' => $settings, 'type' => $type));
		return $settings;
	}

    /**
     * Get default value for section filter
     * If user TSConfig newspaper.baseSections is set, the first section will be used as default filter
     * @return string Default section title or empty string if not set
     */
    private function getDefaultSection() {
        // Read User TSConfig for base sections (if available): get uids of base sections
        if ($GLOBALS['BE_USER']) {
            if ($GLOBALS['BE_USER']->getTSConfigVal('newspaper.baseSections')) {
                $baseSectionUids = t3lib_div::trimExplode(',', $GLOBALS['BE_USER']->getTSConfigVal('newspaper.baseSections'));
            }
            if ($baseSectionUids) {
                $section = new tx_newspaper_Section(intval($baseSectionUids[0]));
                return $section->getAttribute('section_name');
            }
        }
        return ''; // Default section filter
    }


	/**
	 * Process AJAX functions (like publishing or deleting an article)
	 */
	private function processAjaxController() {
//t3lib_div::devlog('processAjaxController()', 'newspaper', 0, array('input' => $this->input));
		if (!isset($this->input['ajaxController']) || !isset($this->input['ajaxController'])) {
			return;
		}

		switch($this->input['ajaxController']) {
			case 'deleteArticle':
				tx_newspaper::deleteUsingCmdMap('tx_newspaper_article', array(intval($this->input['articleUid'])));
				die();
			break;
			case 'publishArticle':
				$this->changeArticleHiddenStatus(intval($this->input['articleUid']), false);
				die();
			break;
			case 'hideArticle':
				$this->changeArticleHiddenStatus(intval($this->input['articleUid']), true);
				die();
			break;
		}

	}


	/**
	 * Set article hidden flag according to $statusHidden
	 * param $articleUid
	 * param $statusHidden
	 */
	private function changeArticleHiddenStatus($articleUid, $statusHidden) {
        $timer = tx_newspaper_ExecutionTimer::create();
		$article = new tx_newspaper_article($articleUid);
		$article->storeHiddenStatusWithHooks($statusHidden);
	}


	/// create where part of sql statement for current filter setting
	/// \return array key 'table' table(s) to be used, key 'where': condition combined with "AND"; or false if query will return an empty result set
	private function createWherePartArray() {
//t3lib_div::devlog('createWherePartArray()', 'newspaper', 0, array('_request' => $_REQUEST, 'input' => $this->input));
		$where = array();

		if (trim($this->input['section'])) {
			$where_section = $this->getWhereForSection($this->input['section']);
			if ($where_section === false) {
				return false; // no matching section found, so not article in search result
			}
			$table = 'tx_newspaper_article, tx_newspaper_article_sections_mm';
			$where['section'] = 'tx_newspaper_article.uid=tx_newspaper_article_sections_mm.uid_local AND tx_newspaper_article_sections_mm.uid_foreign IN (' . $where_section . ')'; //
		} else {
			$table = 'tx_newspaper_article';
		}

		$where['is_template'] = 'is_template=0';
		$where['tstamp'] = 'tstamp>=' . tx_newspaper_UtilMod::calculateTimestamp($this->input['range']);

		// get articles from correct sysfolder only
		$where['pid'] = 'pid=' . tx_newspaper_Sysfolder::getInstance()->getPid(new tx_newspaper_Article());

		switch($this->input['hidden']) {
			case 'on':
				$where['hidden'] = 'hidden=1';
			break;
			case 'off':
				$where['hidden'] = 'hidden=0';
			break;
			case 'all':
			default:
				// nothing to do
		}

		switch(strtolower($this->input['role'])) {
			case NP_ACTIVE_ROLE_EDITORIAL_STAFF:
			case NP_ACTIVE_ROLE_DUTY_EDITOR:
			case NP_ACTIVE_ROLE_NONE:
				$where['workflow_status'] = 'workflow_status=' . intval($this->input['role']);
			break;
			case '-1': // all
			default:
				// nothing to do
		}


		if (trim($this->input['author'])) {
			$where['author'] = 'author LIKE "%' . addslashes(trim($this->input['author'])) . '%"';
		}

		if (trim($this->input['be_user'])) {
			$where['be_user'] = 'modification_user IN (SELECT uid FROM be_users WHERE username LIKE "%' . addslashes(trim($this->input['be_user'])) . '%")';
		}

		if (trim($this->input['text'])) {
			$where['text'] = '(title LIKE "%' . addslashes(trim($this->input['text'])) . '%" OR kicker LIKE "%' .
				addslashes(trim($this->input['text'])) . '%" OR teaser LIKE "%' .
				addslashes(trim($this->input['text'])) . '%" OR bodytext LIKE "%' .
				addslashes(trim($this->input['text'])) . '%")';
			if (substr(trim($this->input['text']), 0, 1) == '#') {
				// looking for an article uid?
				$uid = intval(substr(trim($this->input['text']), 1));
				if (trim($this->input['text']) == '#' . $uid) {
					// text contains a query like #[int], so search for this uid ONLY
					$where['uid'] = $uid;
					$table = 'tx_newspaper_article';
					return array(
						'table' => $table,
						'where' => 'uid=' . $uid
					);
				}
			}
		}
//t3lib_div::devlog('createWherePartArray()', 'newspaper', 0, array('where' => $where, 'table' => $table));
		return array(
			'table' => $table,
			'where' => implode(' AND ', $where)
		);
	}



	/// Get section uids for given search term $section
	/// \param $section search term for sections (is NOT trimmed)
	/// \param $recursive wheater or not sub section are searched too
	/// \return comma separated list of section uids or false if no section could be found
	private function getWhereForSection($section, $recursive=true) {
		$sectionUids = tx_newspaper::selectRows(
			'uid',
			'tx_newspaper_section',
			'section_name LIKE "%' . addslashes($section) . '%"' . // search for sections contains the section search string
				' AND pid=' . tx_newspaper_Sysfolder::getInstance()->getPid(new tx_newspaper_section()) // check current section sysfolder only
		);
		$uids = array();
		foreach($sectionUids as $sectionUid) {
			$uids[] = $sectionUid['uid'];
			$s = new tx_newspaper_section(intval($sectionUid['uid']));
			if ($recursive) {
				foreach($s->getChildSections(true) as $sub_section) {
					$uids[] = $sub_section->getUid();
				}
			}
		}
		$sectionUidList = implode(',', array_unique($uids));

		if (!$sectionUidList) {
			// no matching section found, so no article in result set
			return false;
		}
//t3lib_div::devlog('getWhereForSection()', 'newspaper', 0, array('$sectionUids' => $sectionUids, 'sectionUidList' => $sectionUidList, 'query' => tx_newspaper::$query));
		return $sectionUidList;
	}





// functions to fill filter dropdowns with data

	/// \return Array with options for publish state dropdown
	private function getHiddenArray() {
		$hidden = array();
		$hidden['all'] = $this->LL['option_status_hidden_all'];
		$hidden['on'] = $this->LL['option_status_hidden_on'];
		$hidden['off'] = $this->LL['option_status_hidden_off'];
		return $hidden;
	}

	/// \return Array with options for workflow/role dropdown
	private function getRoleArray() {
		global $LANG;
		$role = array();
		$role['-1'] = $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_status_role_all', false);
		$role[NP_ACTIVE_ROLE_EDITORIAL_STAFF] = $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_role_editorialstaff', false);
		$role[NP_ACTIVE_ROLE_DUTY_EDITOR] = $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_role_dutyeditor', false);
		$role[NP_ACTIVE_ROLE_NONE] = $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_role_none', false);
		return $role;
	}

	/// \return Array with options for time range dropdown
	private function getRangeArray() {
		$range = array();
		$range['today'] = $this->LL['option_range_today'];
		$range['day_1'] = '1 ' . $this->LL['option_range_day'];
		$range['day_2'] = '2 ' . $this->LL['option_range_days'];
		$range['day_3'] = '3 ' . $this->LL['option_range_days'];
		$range['day_7'] = '7 ' . $this->LL['option_range_days'];
		$range['day_14'] = '14 ' . $this->LL['option_range_days'];
		$range['day_30'] = '30 ' . $this->LL['option_range_days'];
		$range['day_60'] = '60 ' . $this->LL['option_range_days'];
		$range['day_90'] = '90 ' . $this->LL['option_range_days'];
		$range['day_180'] = '180 ' . $this->LL['option_range_days'];
		$range['day_360'] = '360 ' . $this->LL['option_range_days'];
		$range['no_limit'] = $this->LL['option_range_no_limit'];
		return $range;
	}


}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod2/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod2/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_newspaper_module2');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>