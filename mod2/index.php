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

require_once('class.tx_newspaper_module2_filter.php');

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

    /** @var tx_newspaper_module2_Filter */
    private $filter = null;

	/**
	 * Initializes the Module
	 * @return	void
	 */
	function init()	{
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

        if (!$this->isAccessAllowed()) {
            $this->denyAccess();
            return;
        }

		$this->input = t3lib_div::GParrayMerged($this->prefixId); // read params

		$this->processAjaxController(); // process Ajax request (terminates with die() if any)

		// get ll labels
		$localLang = t3lib_div::readLLfile('typo3conf/ext/newspaper/mod2/locallang.xml', $GLOBALS['LANG']->lang);
		$this->LL = $localLang[$GLOBALS['LANG']->lang];

        $this->filter = new tx_newspaper_module2_Filter($this->LL, $this->input, $this->isArticleBrowser());

		// Draw the header.
        $this->makeDoc();

		$this->content .= $this->doc->startPage('');

		// Render content:
		$this->moduleContent();

		$this->content.=$this->doc->spacer(10);
	}

    /**
     *  \todo: better check needed
     */
    private function isAccessAllowed() {
        return $GLOBALS['BE_USER']->user['uid'] ? true : false;
    }

    private function makeDoc() {

        global $BACK_PATH;

        $this->doc = t3lib_div::makeInstance('fullWidthDoc_mod2');
        $this->doc->backPath = $BACK_PATH;

        // JavaScript
        $this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
			';
        $this->doc->postCode = '
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = 0;
				</script>
			';
    }

    private function denyAccess() { // If no access or if ID == zero

        $this->doc = t3lib_div::makeInstance('mediumDoc');
        $this->doc->backPath = $GLOBALS['BACK_PATH'];

        $this->content .= $this->doc->startPage($this->LL['title']);
        $this->content .= $this->doc->header($this->LL['title']);
        $this->content .= $this->doc->spacer(5);
        $this->content .= $this->doc->spacer(10);
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

		$content = $this->renderBackendSmarty($this->filter->getArticleRecords(), $this->filter->getCount());

		$this->content .= $this->doc->section('', $content, 0, 1);
//t3lib_div::devlog('mod2', 'newspaper', 0, array('content' => htmlspecialchars($content), 'this->content' => htmlspecialchars($this->content)));
	}


	/**
	 * \param $row article to be rendered
	 * \param $count total number of article found for current filter settings
	 * \return HTML code, rendered backend
	 */
	function renderBackendSmarty(array $article_records, $count) {

 		$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod2/res/'));

        $smarty->assign('FILTER_BOX', $this->filter->renderBox($smarty));

		$smarty->assign('LL', $this->LL); // localized labels

        $smarty->assign('CAN_PUBLISH_ARTICLES', tx_newspaper_Workflow::canPublishArticles());
        $smarty->assign('CAN_PLACE_ARTICLES', tx_newspaper_workflow::canPlaceArticles());

		$smarty->assign('RESULT_COUNT', intval($count));

		$smarty->assign('ICON', $this->getIcons());

		$smarty->assign('AB4AL', (t3lib_div::_GP('ab4al'))? t3lib_div::_GP('ab4al') : ''); // article browser for article lists
		$smarty->assign('select_box_id', (t3lib_div::_GP('select_box_id'))? t3lib_div::_GP('select_box_id') : ''); // selectbox id for article browser

		// some values for article browser functionality for Typo3 fields, tx_newspaper_be::checkReplaceEbWithArticleBrowser()
		$smarty->assign('FORM_TABLE', (t3lib_div::_GP('form_table'))? t3lib_div::_GP('form_table') : '');
		$smarty->assign('FORM_FIELD', (t3lib_div::_GP('form_field'))? t3lib_div::_GP('form_field') : '');
		$smarty->assign('FORM_UID', intval(t3lib_div::_GP('form_uid'))? intval(t3lib_div::_GP('form_uid')) : 0);

        $smarty->assign('LOCKED_ARTICLES', self::getLockedArticles($article_records));

        $this->addArticleInfo($article_records);
        $smarty->assign('DATA', $article_records);

        tx_newspaper_Workflow::addWorkflowTranslations($smarty);

        //  paging
        $smarty->assign('START_PAGE', intval($this->input['startPage']));
        $step = intval($this->input['step']);
        $smarty->assign('STEP', $step? $step: 10);
		$smarty->assign('MAX_PAGE', $this->calculateMaxPage($count, $step? $step: 10));

		$smarty->assign('T3PATH', tx_newspaper::getAbsolutePath() . 'typo3/');
		$smarty->assign('ABSOLUTE_PATH', tx_newspaper::getAbsolutePath());
        $smarty->assign('MODULE5_PATH', tx_newspaper::getAbsolutePath() . 'typo3conf/ext/newspaper/mod5/'); // path to typo3, needed for edit article (form: /a/b/c/typo3/)

		return $smarty->fetch($this->getSmartyTemplate());
	}

   	/// \return Array with icons for the backend
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

    /// check which articles are locked
    private static function getLockedArticles(array $records) {
        $locked_articles = array();
        for ($i = 0; $i < sizeof($records); $i++) {
            // is article locked?
            $t = t3lib_BEfunc::isRecordLocked('tx_newspaper_article', $records[$i]['uid']);
            if (isset($t['record_uid'])) {
                $locked_articles[$i] = array(
                    'username' => $t['username'],
                    'msg' => htmlentities($t['msg'])
                );
            }
        }
        return $locked_articles;
    }

    /// add informations to each article that are not in the record
    private function addArticleInfo(array &$records) {
        for ($i = 0; $i < sizeof($records); $i++) {
            self::addWorkflowInfo($records[$i]);
            $records[$i]['sections'] = $this->getSectionNames(intval($records[$i]['uid']));
            $this->addTimeInfo($records[$i]);
        }
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

    private function getSmartyTemplate() {
        return $this->isArticleBrowser()? 'mod2_articlebrowser.tmpl': 'mod2_main_v2.tmpl';
    }

    private static function addWorkflowInfo(array &$record) {
        // add role title
        $record['workflow_status_TITLE'] = tx_newspaper_workflow::getRoleTitle($record['workflow_status']);

        // add workflowlog data to $row - new layout for production list version for mod2_main_v2.tmpl
        $record['workflowlog_v2'] = tx_newspaper_workflow::getComments('tx_newspaper_article', $record['uid']);

        // add extended workflowlog data to $row - displayable on demand
        $record['workflowlog_all'] = tx_newspaper_workflow::getComments('tx_newspaper_article', $record['uid'], 0, 1);
    }

    private function getSectionNames($article_uid) {
        $a = new tx_newspaper_article($article_uid);
     	$sections = $a->getSections();
        array_walk($sections, array($this, 'extractSectionTitle'));
     	return implode(', ', $sections);
    }

    private function extractSectionTitle(&$section, $key) { $section = $section->getAttribute('section_name'); }

    private function addTimeInfo(array &$record) {
        // Add information for time controlled articles
        $this->addTimeInfoToRow($record, 'time_controlled_not_yet');
        $this->addTimeInfoToRow($record, 'time_controlled_not_yet_with_endtime');
        $this->addTimeInfoToRow($record, 'time_controlled_not_anymore');
        $this->addTimeInfoToRow($record, 'time_controlled_not_anymore_with_starttime');
        $this->addTimeInfoToRow($record, 'time_controlled_now_and_future');
        $this->addTimeInfoToRow($record, 'time_controlled_now_but_will_end');

        // Add formatted publish date
        $record['formattedPublishdate'] = $this->getFormattedPublishDate($record['publish_date']);
    }

    private function addTimeInfoToRow(array &$record, $label) {
        $record[$label] = self::insertStartEndtime($this->LL[$label], $record['starttime'], $record['endtime']);
    }

    private static function insertStartEndtime($string, $starttime, $endtime) {
        // @todo: time format string should be configurable
   		$string = str_replace('###STARTTIME###', date("d.m.Y, H:i:s", $starttime), $string);
   		$string = str_replace('###ENDTIME###', date("d.m.Y, H:i:s", $endtime), $string);
   		return $string;
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

	/// \return true if an article browser is rendered, false if production list is rendered
	private function isArticleBrowser() {
		// form_table -> article browser for Typo3 fields
		// ab4al article browser for articlelists
		return t3lib_div::_GP('form_table') || t3lib_div::_GP('ab4al');
	}


	/**
	 * Process AJAX functions (like publishing or deleting an article)
	 */
	private function processAjaxController() {
//t3lib_div::devlog('processAjaxController()', 'newspaper', 0, array('input' => $this->input));
		if (!isset($this->input['ajaxController'])) return;

		switch($this->input['ajaxController']) {
			case 'deleteArticle':
				tx_newspaper::deleteUsingCmdMap('tx_newspaper_article', array(intval($this->input['articleUid'])));
				die();
			case 'publishArticle':
				$this->changeArticleHiddenStatus(intval($this->input['articleUid']), false);
				die();
			case 'hideArticle':
				$this->changeArticleHiddenStatus(intval($this->input['articleUid']), true);
				die();
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