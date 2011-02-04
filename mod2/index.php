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

$LANG->includeLLFile('EXT:newspaper/mod2/locallang.xml');
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
 * @author	Helge Preuss, Oliver Schröder, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
 */
class  tx_newspaper_module2 extends t3lib_SCbase {
	var $pageinfo;
	private $prefixId = 'tx_newspaper_mod2';

	/**
	 * Initializes the Module
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		parent::init();
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 * @return	void
	 */
	function menuConfig()	{
		global $LANG;
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
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		// Access check!
		$access = $BE_USER->user['uid']? true : false; // \todo: better check needed

		if ($access) {

				// Draw the header.
			$this->doc = t3lib_div::makeInstance('fullWidthDoc_mod2');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="" method="POST">';

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

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
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
//t3lib_div::devlog('where', 'newspaper', 0, array('where' => $this->createWherePart()));

		global $LANG;

		$this->processGP();
		$this->processGPController(); // check if a controller was used (hide/unhide/delete article)

		$where = $this->createWherePart(); // get conditions for sql statement

		if ($where !== false) {

			$count = tx_newspaper::countRows($where['table'], $where['where']);

			$row = tx_newspaper::selectRows(
				'*',
				$where['table'],
				$where['where'],
				'',
				'tstamp DESC',
				intval(t3lib_div::_GP('start_page'))*intval(t3lib_div::_GP('step')) . ', ' . (intval(t3lib_div::_GP('step')))
			);
//t3lib_div::devlog('row', 'newspaper', 0, array('query' => tx_newspaper::$query, 'row' => $row));
		} else {
			$row = array(); // empty result
		}

		$content = $this->renderBackendSmarty($row, $count);

		$this->content .= $this->doc->section('', $content, 0, 1);
//t3lib_div::devlog('mod2', 'newspaper', 0, array('content' => htmlspecialchars($content), 'this->content' => htmlspecialchars($this->content)));
	}


	function renderBackendSmarty($row, $count) {
		global $LANG;

 		$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod2/res/'));

		$smarty->assign('IS_DUTY_EDITOR', tx_newspaper_workflow::isDutyEditor());

		$smarty->assign('PAGE_PREV_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.page_prev', false));
		$smarty->assign('PAGE_NEXT_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.page_next', false));
		$smarty->assign('PAGE_HITS_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.page_hits', false));
		$smarty->assign('RESULT_COUNT', intval($count));

		$smarty->assign('RANGE', $this->getRangeArray()); // add data for range dropdown
		$smarty->assign('RANGE_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.range', false));

		$smarty->assign('STEP', array(10, 20, 30, 50, 100)); // add data for step dropdown (for page browser)
		$smarty->assign('STEP_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.step_items_per_page', false));
		$smarty->assign('START_PAGE', t3lib_div::_GP('start_page'));

		$smarty->assign('HIDDEN', $this->getHiddenArray()); // add data for "hidden" dropdown
		$smarty->assign('HIDDEN_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.status_hidden', false));

		$smarty->assign('ROLE', $this->getRoleArray()); // add data for role dropdown
		$smarty->assign('ROLE_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_status_role', false));
		$smarty->assign('ROLE_FILTER_EQUALS_USER_ROLE', $this->isRoleFilterEqualToUserRole());

		$smarty->assign('AUTHOR_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.author', false));
		$smarty->assign('SECTION_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.section', false));
		$smarty->assign('TEXTSEARCH_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.textsearch', false));
		$smarty->assign('BE_USER_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_title_be_user', false));

		$smarty->assign('LABEL_TITLE', array(
			'number' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_title_number', false),
			'article' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_title_article', false),
			'author' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_title_author', false),
			'be_user' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_title_be_user', false),
			'modification_date' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_title_modification_date', false),
			'visibility' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_title_visibility', false),
			'publish_date' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_title_publish_date', false),
			'time_controlled' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_title_time_controlled', false),
			'commands' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_title_commands', false),
			'role' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_title_role', false),
		));
		$smarty->assign('LABEL', array(
			'time_controlled_not_yet' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_time_controlled_not_yet', false),
			'time_controlled_not_anymore' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_time_controlled_not_anymore', false),
			'time_controlled_now_and_future' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_time_controlled_now_and_future', false),
			'time_controlled_now_but_will_end' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_time_controlled_now_but_will_end', false),
			'new_article' => $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_new_article', false),
			'module_title' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_module_title', false),
			'state' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_state', false),
			'article' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_article', false),
			'messages' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_messages', false),
			'not_yet_published' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_not_yet_published', false),
			'not_yet_published_BUT_ONLINE' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_not_yet_published_BUT_ONLINE', false),
			'published' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_published', false),
			'by_part' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_by_part', false),
			'flag_hidden' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.unhide', false),
			'flag_published' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.hide', false),
			'flag_placement' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.article_placement', false),
			'flag_preview' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.preview_article', false),
			'flag_edit' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.edit_article', false),
			'flag_delete' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.delete_article', false),
			'messages_show' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_messages_show', false),
			'messages_hide' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_messages_hide', false),
		));

		$smarty->assign('GO_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.go', false));
		$smarty->assign('RESET_FILTER_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.reset_filter', false));

		$smarty->assign('HIDE_ICON', tx_newspaper_BE::renderIcon('gfx/button_hide.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.hide', false)));
		$smarty->assign('UNHIDE_ICON', tx_newspaper_BE::renderIcon('gfx/button_unhide.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.unhide', false)));
		$smarty->assign('ARTICLE_PREVIEW_ICON', tx_newspaper_BE::renderIcon('gfx/zoom.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.preview_article', false)));
		$smarty->assign('ARTICLE_EDIT_ICON', tx_newspaper_BE::renderIcon('gfx/edit2.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.edit_article', false)));
		$smarty->assign('COMMENT_ICON', tx_newspaper_BE::renderIcon('gfx/zoom2.gif', '', '###COMMENT###'));
		$smarty->assign('TIME_HIDDEN_ICON', tx_newspaper_BE::renderIcon('gfx/history.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.time', false)));
		$smarty->assign('TIME_VISIBLE_ICON', tx_newspaper_BE::renderIcon('gfx/icon_ok2.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.time', false)));
		$smarty->assign('ARTICLE_DELETE_ICON', tx_newspaper_BE::renderIcon('gfx/garbage.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.delete_article', false)));
		$smarty->assign('ARTICLE_DELETE_MESSAGE', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:message_delete_article', false));
		$smarty->assign('PUBLISHED_ICON', tx_newspaper_BE::renderIcon('gfx/button_hide.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.published', false)));
		$smarty->assign('HIDDEN_ICON', tx_newspaper_BE::renderIcon('gfx/button_unhide.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.hidden', false)));


		$image_path = tx_newspaper::getAbsolutePath() . 'typo3conf/ext/newspaper/res/icons/';
		$smarty->assign('TIME_GREEN', tx_newspaper_BE::renderIcon($image_path . 'history_green.gif', '', ''));
		$smarty->assign('TIME_YELLOW', tx_newspaper_BE::renderIcon($image_path . 'history_yellow.gif', '', ''));
		$smarty->assign('TIME_RED', tx_newspaper_BE::renderIcon($image_path . 'history_red.gif', '', ''));
		$smarty->assign('TIME_VERY_GREEN', tx_newspaper_BE::renderIcon('gfx/icon_ok2.gif', '', ''));


		$smarty->assign('RECORD_LOCKED_ICON', tx_newspaper_BE::renderIcon('gfx/recordlock_warning3.gif', '', '###LOCK_MSG###', false));
		$smarty->assign('ARTICLE_PLACEMENT_ICON', tx_newspaper_BE::renderIcon('gfx/list.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.article_placement', false)));
		$smarty->assign('ARTICLE_ADD_ICON', tx_newspaper_BE::renderIcon('gfx/plusbullet2.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.article_add', false)));

		// some values for article browser functionality
		$smarty->assign('FORM_TABLE', (t3lib_div::_GP('form_table'))? t3lib_div::_GP('form_table') : '');
		$smarty->assign('FORM_FIELD', (t3lib_div::_GP('form_field'))? t3lib_div::_GP('form_field') : '');
		$smarty->assign('FORM_UID', intval(t3lib_div::_GP('form_uid'))? intval(t3lib_div::_GP('form_uid')) : 0);

		$smarty->assign('AB4AL', (t3lib_div::_GP('ab4al'))? t3lib_div::_GP('ab4al') : ''); // article browser for article lists
        $smarty->assign('select_box_id', (t3lib_div::_GP('select_box_id'))? t3lib_div::_GP('select_box_id') : ''); // selectbox id for article browser

		$smarty->assign('IS_ADMIN', $GLOBALS['BE_USER']->user['admin']);

		$smarty->assign('IS_ARTICLE_BROWSER', (t3lib_div::_GP('form_table') || t3lib_div::_GP('ab4al'))? 1 : 0); // set flag if mod2 should be rendered as moderation list or as article browser

		/// build browse sequence
		if (intval(t3lib_div::_GP('start_page')) > 0) {
			$smarty->assign('URL_PREV', tx_newspaper_UtilMod::convertPost2Querystring(array('start_page' => intval(t3lib_div::_GP('start_page')) - 1)));
		} else {
			$smarty->assign('URL_PREV', '');
		}
		if ($count > intval((t3lib_div::_GP('start_page')+1) * t3lib_div::_GP('step'))) {
			// so there's at least one next record
			$smarty->assign('URL_NEXT', tx_newspaper_UtilMod::convertPost2Querystring(array('start_page' => intval(t3lib_div::_GP('start_page')) + 1)));
		} else {
			$smarty->assign('URL_NEXT', '');
		}

		$smarty->assign('MODULE5_PATH', tx_newspaper::getAbsolutePath() . 'typo3conf/ext/newspaper/mod5/'); // path to typo3, needed for edit article (form: /a/b/c/typo3/)
		$smarty->assign('WIZARD_ICON', tx_newspaper_BE::renderIcon('gfx/wizard_rte2.gif', '', $LANG->sL('LLL:EXT:newspaper/mod5/locallang.xml:label_start_wizard', false)));


		/// build url for switch visibility button
		// \todo: check what's that '###ARTILCE_UID###' needed for?
		$smarty->assign('URL_HIDE_UNHIDE', tx_newspaper_UtilMod::convertPost2Querystring(array('uid' => '###ARTILCE_UID###')));

		$smarty->assign('URL_PLAIN', tx_newspaper_UtilMod::convertPost2Querystring());

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

//			// add be_user; currently not used in smarty template
//			$be_user_uid = $row[$i]['modification_user']? $row[$i]['modification_user'] : $row[$i]['cruser_id'];
//			if ($be_user_uid) {
//				$be_user = tx_newspaper::selectZeroOrOneRows(
//					'username, realName',
//					'be_users',
//					'uid=' . $be_user_uid
//				);
//				if (!$be_user['username']) {
//					$be_user['username'] = '---'; // stored be_user is deleted
//				}
//			} else {
//				$be_user['username'] = '---'; // no be_user stored in article
//			}
//			$row[$i]['be_user'] = $be_user['realName']? $be_user['realName'] : $be_user['username'];

			// add role title
			$row[$i]['workflow_status_TITLE'] = tx_newspaper_workflow::getRoleTitle($row[$i]['workflow_status']);

			//add workflowlog data to $row - simple version for mod2_main.tmpl
			$row[$i]['workflowlog'] = tx_newspaper_workflow::renderBackend('tx_newspaper_article', $row[$i]['uid'], false);

			// add workflowlog data to $row - new layout for production list version for mod2_main_v2.tmpl
			$comments = tx_newspaper_workflow::getComments('tx_newspaper_article', $row[$i]['uid']);
			$comments = tx_newspaper_workflow::addUsername($comments);
			$row[$i]['workflowlog_v2'] = $comments;

			// add sections
			$a = new tx_newspaper_article(intval($row[$i]['uid']));
			$sections = array();
			foreach($a->getSections() as $current_section) {
				$sections[] = $current_section->getAttribute('section_name');
			}
			$row[$i]['sections'] = implode(', ', $sections);
		}
		$smarty->assign('LOCKED_ARTICLE', $locked_article);

		// add information for time controlled articles
		for ($i = 0; $i < sizeof($row); $i++) {
			$row[$i]['time_controlled_not_yet'] = $this->insertStartEndtime($LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_time_controlled_not_yet', false), $row[$i]['starttime'] ,$row[$i]['endtime']);
			$row[$i]['time_controlled_not_anymore'] = $this->insertStartEndtime($LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_time_controlled_not_anymore', false), $row[$i]['starttime'] ,$row[$i]['endtime']);
			$row[$i]['time_controlled_now_and_future'] = $this->insertStartEndtime($LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_time_controlled_now_and_future', false), $row[$i]['starttime'] ,$row[$i]['endtime']);
			$row[$i]['time_controlled_now_but_will_end'] = $this->insertStartEndtime($LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_time_controlled_now_but_will_end', false), $row[$i]['starttime'] ,$row[$i]['endtime']);
		}

		$smarty->assign('DATA', $row);

		if (!isset($_POST['step'])) {
			$_POST['step'] = 10; // set default
		}
		if (!isset($_POST['start_page'])) {
			$_POST['start_page'] = 0; // set default
		}
		$smarty->assign('_POST', t3lib_div::_POST()); // add _post data (for setting default values)

		$smarty->assign('T3PATH', tx_newspaper::getAbsolutePath() . 'typo3/');
		$smarty->assign('ABSOLUTE_PATH', tx_newspaper::getAbsolutePath());

		if ($this->isArticleBrowser()) {
			return $smarty->fetch('mod2_articlebrowser.tmpl'); // article browser
		}
		return $smarty->fetch('mod2_main_v2.tmpl'); // production list
	}

	/// \return true if an article browser is rendered, false if production list is rendered
	private function isArticleBrowser() {
		return t3lib_div::_GP('form_table') || t3lib_div::_GP('ab4al');
	}
	/// \return true if production list is rendered, false if an article browser is rendered
	private function isProductionList() {
		return !$this->isArticleBrowser();
	}

	/// \return true if role filter equals the current role of the be_user, else false
	private function isRoleFilterEqualToUserRole() {
		return ($_POST['role'] ==  tx_newspaper_workflow::getRole());
	}

	private function insertStartEndtime($string, $starttime, $endtime) {
// \todo: time format string should be configurable
		$string = str_replace('###STARTTIME###', date("d.m.Y, H:i:s", $starttime), $string);
		$string = str_replace('###ENDTIME###', date("d.m.Y, H:i:s", $endtime), $string);
		return $string;
	}




	/// set default values
	function processGP() {
//t3lib_div::devlog('processGP()', 'np', 0, array('_request' => $_REQUEST, '_post' => $_POST, '_get' => $_GET));

		if ((sizeof(t3lib_div::_POST()) == 0) && (sizeof(t3lib_div::_GET()) == 0)) {
			/// module is called from menu

			$storedFilter = $this->getFilter();
			if (is_array($storedFilter)) {
				// use stored filter
				$storedFilter = $this->addDefaultFilterValues($storedFilter, true);
				$_POST = $storedFilter;
			} else {
				// set default filter setting
				$_POST = $this->addDefaultFilterValues($_POST, true);
			}
		} elseif ((sizeof(t3lib_div::_POST()) == 0) && (sizeof(t3lib_div::_GET()) > 0)) {
			/// set some defaults for pages being called by url
			$_POST = t3lib_div::_GET(); // copy to $_post -> ring is created based on $_post
			$_POST = $this->addDefaultFilterValues($_POST);
		}

		if (t3lib_div::_GP('go') != '') {
			// if "go" button was pressed, reset page browsing
			$_POST['start_page'] = 0;
			unset($_POST['go']); // if querystring contains this marker it indecates that the form was submitted, so it's unset to remove it from the browse urls
		} elseif (t3lib_div::_GP('reset_filter') != '') {
			// if "reset" button was pressed, read filter default settings
			$_POST = $this->addDefaultFilterValues(array());
			unset($_POST['reset_filter']);
		}

		$this->storeFilter();

	}

	/// \return filter settings (checks if settings for production list or article browser are requested)
	private function getFilter() {
		if ($this->isProductionList()) {
			return unserialize($GLOBALS['BE_USER']->getModuleData('tx_newspaper/mod2/index.php/filter_prodlist'));
		} else {
			return array(); // no filter is stored for the article browser
		}
	}

	/// Stores the filter setting (check whether to store production list or article browser settings)
	private function storeFilter() {
		// store filter settings
		if ($this->isProductionList()) {
			$GLOBALS['BE_USER']->pushModuleData("tx_newspaper/mod2/index.php/filter_prodlist", serialize(array(
				'range' => $_POST['range'],
				'hidden' => $_POST['hidden'],
				'role' => $_POST['role'],
				'author' => $_POST['author'],
				'be_user' => $_POST['be_user'],
				'section' => $_POST['section'],
				'text' => $_POST['text'],
				'step' => $_POST['step'],
				'start_page' => 0 // always start on first result page
			)));
		} // no need to store article browser filtrer settings. filter are reset each time an article browser is called
	}


	/// adds default filter settings if filter type is missing in given array
	/** if array $settings is empty or filled partly only, all missing filter values are filled with default values
     * \param $settings filter settings
	 * \param $forceReset if set to true some fields are forced to be filled with default values
	 * \return array with filter settings where missing filters were added (using default values)
	 */
	private function addDefaultFilterValues(array $settings, $forceReset=false) {
//t3lib_div::devlog('addDefaultFilterValues()', 'newspaper', 0, array($settings, $type));
		if (!array_key_exists('range', $settings)) {
			$settings['range'] = 'day_2'; // \todo: make tsconfigurable
		}
		if (!array_key_exists('hidden', $settings)) {
			$settings['hidden'] = 'all';
		}
		if (!array_key_exists('role', $settings) || $forceReset) {
			// add if missing or overwrite if $forceRole is set
			if ($this->isArticleBrowser()) {
				$settings['role'] = '-1'; // all role if article browser
			} elseif ($this->isProductionList()) {
				$settings['role'] = tx_newspaper_workflow::getRole(); // current role of be_user
			} else {
				t3lib_div::devlog('addDefaultFilterValues(): unknown type', 'newspaper', 3, array('settings' => $settings));
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
				$settings['section'] = '';
			}
		} elseif (!array_key_exists('section', $settings) && $this->isArticleBrowser()) {
			$settings['section'] = $_REQUEST['s'];
		}
		if (!array_key_exists('text', $settings) || $forceReset) {
			$settings['text'] = '';
		}
		if (!array_key_exists('step', $settings)) {
			$settings['step'] = 10;
		}
		if (!array_key_exists('start_page', $settings) || $forceReset) {
			$settings['start_page'] = 0;
		}

		return $settings;
	}


	/// Stores hidden/unhidden article status in ajax calls
	// this way to change visibility makes sure, that the current page browser selection lasts
// \todo: article_visibility: use controller too
	function processGPController() {
		/// \todo: permission check
		$article_uid = intval(t3lib_div::_GP('article_uid'));

		$input = t3lib_div::GParrayMerged($this->prefixId);
//t3lib_div::devlog('processGPController()', 'np', 0, array('input' => $input, 'article_uid' => $article_uid, '_GP(article_visibility)' => t3lib_div::_GP('article_visibility')));

		if (isset($input['controller'])) {
			switch($input['controller']) {
				case 'delete':
					tx_newspaper::deleteUsingCmdMap('tx_newspaper_article', array(intval($article_uid)));
				break;
			}
			unset($_POST[$this->prefixId]); // remove controller from query string
			unset($_POST['article_uid']);
			return; // don't check visibility if controller was set
		}

		if (t3lib_div::_GP('article_visibility') != '') {

			// publish/hide icon used in production list

			$hidden_status = strtolower(t3lib_div::_GP('article_visibility'));

			// unset parameters (so they are not added to querystring later)
			unset($_POST['article_visibility']);
			unset($_POST['article_uid']);

			// prepare array with data to be stored
			switch($hidden_status) {
				case 'hidden':
					$hidden = 1;
				break;
				case 'visible':
					$hidden = 0;
				break;
				default:
					return;
			}

			// store data and call article save hooks then
			$article = new tx_newspaper_article($article_uid);
			$article->storeHiddenStatusWithHooks($hidden);

			// redirect to module (in order to remove article_visibility and article_uid from url)
			header('Location: index.php');

		}
	}


	/// create where part of sql statement for current filter setting
	/// \return array key 'table' table(s) to be used, key 'where': condition combined with "AND"; or false if query will return an empty result set
	private function createWherePart() {
//t3lib_div::devlog('createWherePart()', 'newspaper', 0, array('_request' => $_REQUEST));
		$where = array();

		if (trim(t3lib_div::_GP('section'))) {
			$where_section = $this->getWhereForSection(t3lib_div::_GP('section'));
			if ($where_section === false) {
				return false; // no matching section found, so not article in search result
			}
			$table = 'tx_newspaper_article a, tx_newspaper_article_sections_mm mm';
			$where['section'] = 'a.uid=mm.uid_local AND mm.uid_foreign IN (' . $where_section . ')'; //
		} else {
			$table = 'tx_newspaper_article';
		}

		$where['deleted'] = 'deleted=0';
		$where['is_template'] = 'is_template=0';
		$where['tstamp'] = 'tstamp>=' . tx_newspaper_UtilMod::calculateTimestamp(t3lib_div::_GP('range'));

		// get articles from correct sysfolder only
		$where['pid'] = 'pid=' . tx_newspaper_Sysfolder::getInstance()->getPid(new tx_newspaper_Article());

		switch(strtolower(t3lib_div::_GP('hidden'))) {
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
		switch(strtolower(t3lib_div::_GP('role'))) {
			case NP_ACTIVE_ROLE_EDITORIAL_STAFF:
			case NP_ACTIVE_ROLE_DUTY_EDITOR:
			case NP_ACTIVE_ROLE_NONE:
				$where['workflow_status'] = 'workflow_status=' . t3lib_div::_GP('role');
			break;
			case '-1': // all
			default:
				// nothing to do
		}


		if (trim(t3lib_div::_GP('author'))) {
			$where['author'] = 'author LIKE "%' . addslashes(trim(t3lib_div::_GP('author'))) . '%"';
		}

		if (trim(t3lib_div::_GP('be_user'))) {
			$where['be_user'] = 'modification_user IN (SELECT uid FROM be_users WHERE username LIKE "%' . addslashes(trim(t3lib_div::_GP('be_user'))) . '%")';
		}

		if (trim(t3lib_div::_GP('text'))) {
			$where['text'] = '(title LIKE "%' . addslashes(trim(t3lib_div::_GP('text'))) . '%" OR kicker LIKE "%' .
				addslashes(trim(t3lib_div::_GP('text'))) . '%" OR teaser LIKE "%' .
				addslashes(trim(t3lib_div::_GP('text'))) . '%" OR text LIKE "%' .
				addslashes(trim(t3lib_div::_GP('text'))) . '%")';
			if (substr(trim(t3lib_div::_GP('text')), 0, 1) == '#') {
				// looking for an article uid?
				$uid = intval(substr(trim(t3lib_div::_GP('text')), 1));
				if (trim(t3lib_div::_GP('text')) == '#' . $uid) {
					// text contains a query like #[int], so search for this uid ONLY
					$where['uid'] = $uid;
					return array(
						'table' => $table,
						'where' => 'uid=' . $uid
					);
				}
			}
		}
//t3lib_div::devlog('createWherePart()', 'newspaper', 0, array('where' => $where, 'table' => $table));
		return array(
			'table' => $table,
			'where' => implode(' AND ', $where)
		);
	}



	/// get section uids for given search term $section
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




// function to fill filter dropdowns with data

	private function getHiddenArray() {
		global $LANG;
		$hidden = array();
		$hidden['all'] = $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:option.status_hidden_all', false);
		$hidden['on'] = $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:option.status_hidden_on', false);
		$hidden['off'] = $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:option.status_hidden_off', false);
		return $hidden;
	}
	private function getRoleArray() {
		global $LANG;
		$role = array();
		$role['-1'] = $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_status_role_all', false);
		$role[NP_ACTIVE_ROLE_EDITORIAL_STAFF] = $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_role_editorialstaff', false);
		$role[NP_ACTIVE_ROLE_DUTY_EDITOR] = $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_role_dutyeditor', false);
		$role[NP_ACTIVE_ROLE_NONE] = $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_role_none', false);
		return $role;
	}
	private function getRangeArray() {
		global $LANG;
		$range = array();
		$range['today'] = $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:option.range_today', false);
		$range['day_1'] = '1 ' . $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:option.range_day', false);
		$range['day_2'] = '2 ' . $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:option.range_days', false);
		$range['day_3'] = '3 ' . $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:option.range_days', false);
		$range['day_7'] = '7 ' . $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:option.range_days', false);
		$range['day_14'] = '14 ' . $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:option.range_days', false);
		$range['day_30'] = '30 ' . $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:option.range_days', false);
		$range['day_60'] = '60 ' . $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:option.range_days', false);
		$range['day_90'] = '90 ' . $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:option.range_days', false);
		$range['day_180'] = '180 ' . $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:option.range_days', false);
		$range['day_360'] = '360 ' . $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:option.range_days', false);
		$range['no_limit'] = $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:option.range_no_limit', false);
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