<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Helge Preuss, Oliver Schr√∂der, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
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



/// \todo: require_once needed?
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_be.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_util_mod.php');
require_once(PATH_typo3conf . 'ext/newspaper/classes/class.tx_newspaper_smarty.php');




/**
 * Module 'Moderation list' for the 'newspaper' extension.
 *
 * @author	Helge Preuss, Oliver Schr√∂der, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
 * @package	TYPO3
 * @subpackage	tx_newspaper
 */
class  tx_newspaper_module2 extends t3lib_SCbase {
	var $pageinfo;

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
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{

				// Draw the header.
			$this->doc = t3lib_div::makeInstance('mediumDoc');
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

			$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
			$this->content.=$this->doc->divider(5);


			// Render content:
			$this->moduleContent();


			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
			}

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
		global $LANG;
		
		$this->processGP();
		$this->processGPVisibility(); // check if an article is hiudden/unhidden
		
		/// build where part for filter
		$where = $this->createWherePart(); // get conditions for sql statement
#t3lib_div::devlog('where', 'newspaper', 0, $where);

		/// get records (get one more than needed to find out if there's an next page)
		$row = tx_newspaper::selectRows(
			'*',
			'tx_newspaper_article',
			$where,
			'',
			'tstamp DESC',
			intval(t3lib_div::_GP('start_page'))*intval(t3lib_div::_GP('step')) . ', ' . (intval(t3lib_div::_GP('step')) + 1)
		);
#t3lib_div::devlog('row', 'newspaper', 0, $row);

		$content= $this->renderBackendSmarty($row);

		$this->content .= $this->doc->section('', $content, 0, 1);
	}

	
	function renderBackendSmarty($row) {
		global $LANG;
		
 		$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod2/'));


t3lib_div::devlog('moderation: comment still missing', 'newspaper', 0);
//dummy data
for ($i=0; $i < sizeof($row); $i++) {
	$row[$i]['comment'] = 'oliver (2008-03-21 17:37): Dies ist ein Beispiel f¸r die Anzeige des letzten Kommentars. Dies ist ein Beispiel f¸r die Anzeige des letzten Kommentars.Dies ist ein Beispiel f¸r die Anzeige des letzten Kommentars.Dies ist ein Beispiel f¸r die Anzeige des letzten Kommentars.Dies ist ein Beispiel f¸r die Anzeige des letzten Kommentars.Dies ist ein Beispiel f¸r die Anzeige des letzten Kommentars.';
}
t3lib_div::devlog('moderation: be user still missing', 'newspaper', 0);


		$smarty->assign('PAGE_PREV_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.page_prev', false));
		$smarty->assign('PAGE_NEXT_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.page_next', false));
		$smarty->assign('PAGE_HITS_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.page_hits', false));

		$smarty->assign('RANGE', $this->getRangeArray()); // add data for range dropdown
		$smarty->assign('RANGE_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.range', false));

		$smarty->assign('STEP', array(10, 20, 30, 50, 100)); // add data for step dropdoen (for page browser)
		$smarty->assign('STEP_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.step_items_per_page', false));
		$smarty->assign('START_PAGE', t3lib_div::_GP('start_page'));

		$smarty->assign('HIDDEN', $this->getHiddenArray()); // add data for "hidden" dropdown
		$smarty->assign('HIDDEN_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.status_hidden', false));

		$smarty->assign('MODERATION', $this->getModerationArray()); // add data for moderation dropdown
		$smarty->assign('MODERATION_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.status_moderation', false));

		$smarty->assign('AUTHOR_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.author', false));
		$smarty->assign('SECTION_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.section', false));
		$smarty->assign('TEXTSEARCH_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.textsearch', false));

		$smarty->assign('GO_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.go', false));

		$smarty->assign('HIDE_ICON', tx_newspaper_BE::renderIcon('gfx/button_hide.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.hide', false)));
		$smarty->assign('UNHIDE_ICON', tx_newspaper_BE::renderIcon('gfx/button_unhide.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.unhide', false)));
		$smarty->assign('ARTICLE_PREVIEW_ICON', tx_newspaper_BE::renderIcon('gfx/zoom.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.preview_article', false)));
		$smarty->assign('ARTICLE_EDIT_ICON', tx_newspaper_BE::renderIcon('gfx/edit2.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.edit_article', false)));
		$smarty->assign('COMMENT_ICON', tx_newspaper_BE::renderIcon('gfx/zoom2.gif', '', '###COMMENT###'));
		$smarty->assign('TIME_HIDDEN_ICON', tx_newspaper_BE::renderIcon('gfx/history.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.time', false)));
		$smarty->assign('TIME_VISIBLE_ICON', tx_newspaper_BE::renderIcon('gfx/icon_ok2.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.time', false)));


		/// build browse sequence
		if (intval(t3lib_div::_GP('start_page')) > 0) {
			$smarty->assign('URL_PREV', $this->convertPost2Querystring(array('start_page' => intval(t3lib_div::_GP('start_page')) - 1)));
		} else {
			$smarty->assign('URL_PREV', '');
		}
		if (sizeof($row) > intval(t3lib_div::_GP('step'))) {
			// so there's at least one next record
			$smarty->assign('URL_NEXT', $this->convertPost2Querystring(array('start_page' => intval(t3lib_div::_GP('start_page')) + 1)));
			$row = array_slice($row, 0, intval(t3lib_div::_GP('step'))); // cut off entry from next page
		} else {
			$smarty->assign('URL_NEXT', '');
		}
		
		
		/// build url for switch visibility button
		$smarty->assign('URL_HIDE_UNHIDE', tx_newspaper_UtilMod::convertPost2Querystring(array('uid' => '###ARTILCE_UID###')));
		

		$smarty->assign('DATA', $row);

		$smarty->assign('_POST', t3lib_div::_POST()); // add _post data (for setting default values)


		$smarty->assign('T3PATH', substr(PATH_typo3, strlen($_SERVER['DOCUMENT_ROOT']))); // path to typo3, needed for edit article (form: /a/b/c/typo3/)
		
		return $smarty->fetch('mod2.tmpl');
	}






	/// set default values
	function processGP() {
#t3lib_div::devlog('$_request', 'np', 0, $_REQUEST);
#t3lib_div::devlog('_post', 'np', 0, t3lib_div::_POST());
#t3lib_div::devlog('_get', 'np', 0, t3lib_div::_GET());
		if ((sizeof(t3lib_div::_POST()) == 0) && (sizeof(t3lib_div::_GET()) == 0)) {
			/// set default values for initial call of form
			$_POST['range'] = 'today';
			$_POST['hidden'] = 'all';
			$_POST['moderation'] = 'all';
			$_POST['author'] = '';
			$_POST['section'] = '';
			$_POST['text'] = '';
			$_POST['step'] = 10;
			$_POST['start_page'] = 0;
		} elseif ((sizeof(t3lib_div::_POST()) == 0) && (sizeof(t3lib_div::_GET()) > 0)) {
			/// set some defaults for pages being called by url
			$_POST = t3lib_div::_GET();
/// \todo: check: $_get[]=... - warum nicht $_post[]=... ???
			if (!t3lib_div::_POST('range')) $_GET['range'] = 'today';
			if (!t3lib_div::_POST('hidden')) $_GET['hidden'] = 'all';
			if (!t3lib_div::_POST('moderation')) $_GET['moderation'] = 'all';
			if (!t3lib_div::_POST('step')) $_GET['step'] = 10;
			if (!t3lib_div::_POST('start_page')) $_GET['start_page'] = 0;
		}

		/// if "go" button was pressed, reset page browsing
		if (t3lib_div::_GP('go') != '') {
			$_POST['start_page'] = 0;
			unset($_POST['go']); // if querystring contains this marker it indecates that the form was submitted, so it's unset to remove it from the browse urls
		}
	}

	/// check if an article is to be hidden/unhidden; write to database if yes
	function processGPVisibility() {
#t3lib_div::devlog('article_visibility', 'np', 0, t3lib_div::_GP('article_visibility'));
		if (t3lib_div::_GP('article_visibility') != '') {
/// \todo: permission check
			switch(strtolower(t3lib_div::_GP('article_visibility'))) {
/// \todo: use t3 api
				case 'hidden':
					tx_newspaper::updateRows('tx_newspaper_article', 'uid=' . intval(t3lib_div::_GP('article_uid')), array('hidden' => 1, 'tstamp' => time()));
				break;
				case 'visible':
					tx_newspaper::updateRows('tx_newspaper_article', 'uid=' . intval(t3lib_div::_GP('article_uid')), array('hidden' => 0, 'tstamp' => time()));
				default:
/// \todo: throw exception
			}
			// unset parameters (so they are not added to querystring later)
			unset($_POST['article_visibility']);
			unset($_POST['article_uid']);
		}
	}





	/// create where part of sql statement for filter
	/// return string 'WHERE' is NOT added to the string
	function createWherePart() {
		$where = array();
		
		$where[] = 'deleted=0';
		$where[] = 'tstamp>=' . tx_newspaper_UtilMod::calculateTimestamp(t3lib_div::_GP('range'));
		
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
t3lib_div::devlog('moderation: moderation field still missing', 'newspaper', 0);
		
		
		if (t3lib_div::_GP('author') != '')
			$where[] = 'author LIKE "%' . t3lib_div::_GP('author') . '%"';
		if (t3lib_div::_GP('section')) {
t3lib_div::devlog('moderation: section missing', 'newspaper', 0);
		}
		if (t3lib_div::_GP('text'))
			$where[] = '(title LIKE "%' . addslashes(t3lib_div::_GP('text')) . '%" OR kicker LIKE "%' . 
				addslashes(t3lib_div::_GP('text')) . '%" OR teaser LIKE "%' . 
				addslashes(t3lib_div::_GP('text')) . '%" OR text LIKE "%' . 
				addslashes(t3lib_div::_GP('text')) . '%")';
			
		return implode(' AND ', $where);				
	}







// function to fill filter dropdowns with data

	function getHiddenArray() {
		global $LANG;
		$hidden = array();
		$hidden['all'] = $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:option.status_hidden_all', false);
		$hidden['on'] = $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:option.status_hidden_on', false);
		$hidden['off'] = $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:option.status_hidden_off', false);
		return $hidden;		
	}
/// \todo: new statusses need to be added - this field is not just a flag anymore!
	function getModerationArray() {
		global $LANG;
		$moderation = array();
		$moderation['all'] = $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:option.status_moderation_all', false);
		$moderation['on'] = $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:option.status_moderation_on', false);
		$moderation['off'] = $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:option.status_moderation_off', false);
		return $moderation;		
	}
	function getRangeArray() {
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