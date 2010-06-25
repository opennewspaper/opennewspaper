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
	function moduleContent()	{
//t3lib_div::devlog('where', 'newspaper', 0, array('where' => $this->createWherePart()));

		global $LANG;
		
		$this->processGP();
		$this->processGPController(); // check if a controller was used (hide/unhide/delete article)

		/// get records (get one more than needed to find out if there's an next page)
		$row = tx_newspaper::selectRows(
			'*',
			'tx_newspaper_article',
			$this->createWherePart(), // get conditions for sql statement
			'',
			'tstamp DESC',
			intval(t3lib_div::_GP('start_page'))*intval(t3lib_div::_GP('step')) . ', ' . (intval(t3lib_div::_GP('step')) + 1)
		);
//t3lib_div::devlog('row', 'newspaper', 0, array('query' => tx_newspaper::$query, 'row' => $row));

		$content = $this->renderBackendSmarty($row);

		$this->content .= $this->doc->section('', $content, 0, 1);
//t3lib_div::devlog('mod2', 'newspaper', 0, array('content' => htmlspecialchars($content), 'this->content' => htmlspecialchars($this->content)));
	}

	
	function renderBackendSmarty($row) {
		global $LANG;
		
 		$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod2/res/'));

		$smarty->assign('IS_DUTY_EDITOR', tx_newspaper_workflow::isDutyEditor());

		$smarty->assign('PAGE_PREV_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.page_prev', false));
		$smarty->assign('PAGE_NEXT_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.page_next', false));
		$smarty->assign('PAGE_HITS_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.page_hits', false));

		$smarty->assign('RANGE', $this->getRangeArray()); // add data for range dropdown
		$smarty->assign('RANGE_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.range', false));

		$smarty->assign('STEP', array(10, 20, 30, 50, 100)); // add data for step dropdown (for page browser)
		$smarty->assign('STEP_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.step_items_per_page', false));
		$smarty->assign('START_PAGE', t3lib_div::_GP('start_page'));

		$smarty->assign('HIDDEN', $this->getHiddenArray()); // add data for "hidden" dropdown
		$smarty->assign('HIDDEN_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.status_hidden', false));

		$smarty->assign('ROLE', $this->getRoleArray()); // add data for role dropdown
		$smarty->assign('ROLE_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_status_role', false));

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
		));
		$smarty->assign('LABEL', array(
			'time_controlled_not_yet' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_time_controlled_not_yet', false),
			'time_controlled_not_anymore' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_time_controlled_not_anymore', false),
			'time_controlled_now_and_future' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_time_controlled_now_and_future', false),
			'time_controlled_now_but_will_end' => $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label_time_controlled_now_but_will_end', false),
		));

		$smarty->assign('GO_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.go', false));

		$smarty->assign('HIDE_ICON', tx_newspaper_BE::renderIcon('gfx/button_hide.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.hide', false)));
		$smarty->assign('UNHIDE_ICON', tx_newspaper_BE::renderIcon('gfx/button_unhide.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.unhide', false)));
		$smarty->assign('ARTICLE_PREVIEW_ICON', tx_newspaper_BE::renderIcon('gfx/zoom.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.preview_article', false)));
		$smarty->assign('ARTICLE_EDIT_ICON', tx_newspaper_BE::renderIcon('gfx/edit2.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.edit_article', false)));
		$smarty->assign('COMMENT_ICON', tx_newspaper_BE::renderIcon('gfx/zoom2.gif', '', '###COMMENT###'));
		$smarty->assign('TIME_HIDDEN_ICON', tx_newspaper_BE::renderIcon('gfx/history.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.time', false)));
		$smarty->assign('TIME_VISIBLE_ICON', tx_newspaper_BE::renderIcon('gfx/icon_ok2.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.time', false)));
		$smarty->assign('ARTICLE_DELETE_ICON', tx_newspaper_BE::renderIcon('gfx/garbage.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.delete_article', false)));
		$smarty->assign('ARTICLE_DELETE_MESSAGE', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:message_delete_article', false));

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
		
		
		$smarty->assign('IS_ARTICLE_BROWSER', (t3lib_div::_GP('form_table') || t3lib_div::_GP('ab4al'))? 1 : 0); // set flag if mod2 should be rendered as moderation list or as article browser 

		/// build browse sequence
		if (intval(t3lib_div::_GP('start_page')) > 0) {
			$smarty->assign('URL_PREV', tx_newspaper_UtilMod::convertPost2Querystring(array('start_page' => intval(t3lib_div::_GP('start_page')) - 1)));
		} else {
			$smarty->assign('URL_PREV', '');
		}
		if (sizeof($row) > intval(t3lib_div::_GP('step'))) {
			// so there's at least one next record
			$smarty->assign('URL_NEXT', tx_newspaper_UtilMod::convertPost2Querystring(array('start_page' => intval(t3lib_div::_GP('start_page')) + 1)));
			$row = array_slice($row, 0, intval(t3lib_div::_GP('step'))); // cut off entry from next page
		} else {
			$smarty->assign('URL_NEXT', '');
		}
		
		
		/// build url for switch visibility button
		$smarty->assign('URL_HIDE_UNHIDE', tx_newspaper_UtilMod::convertPost2Querystring(array('uid' => '###ARTILCE_UID###')));

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
			// add be_user
			$be_user_uid = $row[$i]['modification_user']? $row[$i]['modification_user'] : $row[$i]['cruser_id'];
			if ($be_user_uid) {
				$be_user = tx_newspaper::selectOneRow(
					'username, realName',
					'be_users',
					'uid=' . $be_user_uid
				);
			} else {
				$be_user['username'] = '---'; // no be_user stored in article
			}
			$row[$i]['be_user'] = $be_user['realName']? $be_user['realName'] : $be_user['username'];  
			// add workflowlog data to $row
			$row[$i]['workflowlog'] = tx_newspaper_workflow::renderBackend('tx_newspaper_article', $row[$i]['uid'], false);
		}
		$smarty->assign('LOCKED_ARTICLE', $locked_article);
		$smarty->assign('workflowlog_javascript', tx_newspaper_workflow::getJavascript()); // add js once only

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
		
		if (t3lib_div::_GP('form_table') || t3lib_div::_GP('ab4al')) {
			return $smarty->fetch('mod2_articlebrowser.tmpl'); // article browser 
		}
		return $smarty->fetch('mod2_main.tmpl'); // moderation list
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
			
			$storedFilter = unserialize($GLOBALS['BE_USER']->getModuleData('tx_newspaper/mod2/index.php/filter'));
			if (is_array($storedFilter)) {
				// use stored filter
				$storedFilter = $this->addDefaultFilterValues($storedFilter); // in case something went wrong when storing the filter settings
				$_POST = $storedFilter; 
			} else {
				// set default filter setting
				$_POST = $this->addDefaultFilterValues($_POST);
			}
		} elseif ((sizeof(t3lib_div::_POST()) == 0) && (sizeof(t3lib_div::_GET()) > 0)) {
			/// set some defaults for pages being called by url
			$_POST = t3lib_div::_GET(); // copy to $_post -> ring is created based on $_post
			$_POST = $this->addDefaultFilterValues($_POST);
		}

		/// if "go" button was pressed, reset page browsing
		if (t3lib_div::_GP('go') != '') {
			$_POST['start_page'] = 0;
			unset($_POST['go']); // if querystring contains this marker it indecates that the form was submitted, so it's unset to remove it from the browse urls
		}
		
		// store filter settings
		$GLOBALS['BE_USER']->pushModuleData("tx_newspaper/mod2/index.php/filter", serialize(array(
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
		
	}

	/// adds default filter settings if filter type is missing in given array
	/// \param $settings filter settings
	/// \return array with filter settings where missing filter type were added with default values
	private function addDefaultFilterValues(array $settings) {
		if (!array_key_exists('range', $settings)) $settings['range'] = 'today';
		if (!array_key_exists('hidden', $settings)) $settings['hidden'] = 'all';
		if (!array_key_exists('role', $settings)) $settings['role'] = '-1';
		if (!array_key_exists('author', $settings)) $settings['author'] = '';
		if (!array_key_exists('be_user', $settings)) $settings['be_user'] = '';
		if (!array_key_exists('section', $settings)) $settings['section'] = '';
		if (!array_key_exists('text', $settings)) $settings['text'] = '';
		if (!array_key_exists('step', $settings)) $settings['step'] = 10;
		if (!array_key_exists('start_page', $settings)) $settings['start_page'] = 0;
		return $settings;
	}


	/// Stores hidden/unhidden article status in ajax calls
	// this way to change visibility makes sure, that the current page browser selection lasts
	function processGPController() {
//t3lib_div::devlog('article_visibility', 'np', 0, array('_GP(article_visibility)' => t3lib_div::_GP('article_visibility')));
		if (t3lib_div::_GP('article_visibility') != '') {
/// \todo: permission check
			$article_uid = intval(t3lib_div::_GP('article_uid'));
			$hidden_status = strtolower(t3lib_div::_GP('article_visibility')); 

			// unset parameters (so they are not added to querystring later)
			unset($_POST['article_visibility']);
			unset($_POST['article_uid']);

			// prepare array with data to be stored
			switch($hidden_status) {
				case 'hidden':
					$fA = array('hidden' => 1);
				break;
				case 'visible':
					$fA = array('hidden' => 0);
				break;
				default:
					return;
			}

			// store data and call article save hooks then
			$this->storeHiddenStausWithHooks($article_uid, $fA);

		}
	}
	
	// \todo: replace with newspaper hook handling, see #1055
	/// This function use Typo3 datamap functionality to assure Typo3 save hooks are called, so registered Hooks in newspaper are called too.
	/** \param $uid article uid
	 *  \param $fieldArray data for tce datamap
	 */
	private function storeHiddenStausWithHooks($uid, array $fieldArray) {
//t3lib_div::devlog('storeHiddenStausWithHooks()', 'newspaper', 0, array('uid' => $uid, 'fieldArray' => $fieldArray));
			if (!intval($uid)) {
				return false;
			}
			
			// prepare datamap array data
			$datamap['tx_newspaper_article'][intval($uid)] = $fieldArray;
			
			// use datamap, so all save hooks get called
			$tce = t3lib_div::makeInstance('t3lib_TCEmain');
			$tce->start($datamap, array());
			$tce->process_datamap();
	}




	/// create where part of sql statement for filter
	/// return array with condition to be combined with "AND"
	private function createWherePart() {
//t3lib_div::devlog('createWherePart()', 'newspaper', 0, array('_request' => $_REQUEST));
		$where = array();
		
		$where[] = 'deleted=0';
		$where[] = 'is_template=0';
		$where[] = 'tstamp>=' . tx_newspaper_UtilMod::calculateTimestamp(t3lib_div::_GP('range'));
		
		// get article fromcorrect sysfolder only
		$where[] = 'pid=' . tx_newspaper_Sysfolder::getInstance()->getPid(new tx_newspaper_Article());
		
		switch(strtolower(t3lib_div::_GP('hidden'))) {
			case 'on':
				$where[] = 'hidden=1';
			break;
			case 'off':
				$where[] = 'hidden=0';
			break;
			case 'all':
			default:
				// nothing to do
		}
		switch(strtolower(t3lib_div::_GP('role'))) {
			case NP_ACTIVE_ROLE_EDITORIAL_STAFF:
			case NP_ACTIVE_ROLE_DUTY_EDITOR:
			case NP_ACTIVE_ROLE_NONE:
				$where[] = 'workflow_status=' . t3lib_div::_GP('role');
			break;
			case '-1': // all
			default:
				// nothing to do
		}
		
		
		if (trim(t3lib_div::_GP('author'))) {
			$where[] = 'author LIKE "%' . addslashes(trim(t3lib_div::_GP('author'))) . '%"';
		}

		if (trim(t3lib_div::_GP('be_user'))) {
			$where[] = 'modification_user IN (SELECT uid FROM be_users WHERE username LIKE "%' . addslashes(trim(t3lib_div::_GP('be_user'))) . '%")';
		}


		if (t3lib_div::_GP('section')) {
t3lib_div::devlog('moderation: section missing', 'newspaper', 3);
		}
		if (trim(t3lib_div::_GP('text'))) {
			$where[] = '(title LIKE "%' . addslashes(trim(t3lib_div::_GP('text'))) . '%" OR kicker LIKE "%' . 
				addslashes(trim(t3lib_div::_GP('text'))) . '%" OR teaser LIKE "%' . 
				addslashes(trim(t3lib_div::_GP('text'))) . '%" OR text LIKE "%' . 
				addslashes(trim(t3lib_div::_GP('text'))) . '%")';
		}
//t3lib_div::devlog('createWherePart()', 'newspaper', 0, array('where' => $where));
		return implode(' AND ', $where);				
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