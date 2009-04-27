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
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

//var_dump(debug_backtrace());
//var_dump($_REQUEST);

require_once('conf.php');
t3lib_div::devlog('mod3 index mconf', 'newspaper', 0, $MCONF);

$LANG->includeLLFile('EXT:newspaper/mod3/locallang.xml');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]



/**
 * Module 'Placement' for the 'newspaper' extension.
 *
 * @author	Helge Preuss, Oliver Schr√∂der, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
 * @package	TYPO3
 * @subpackage	tx_newspaper
 */
class  tx_newspaper_module3 extends t3lib_SCbase {
				var $pageinfo;

				/**
				 * Initializes the Module
				 * @return	void
				 */
				function init()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

					parent::init();

					$this->readUidList(); // get ids for section, page and pagezone

				}

				/**
				 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
				 *
				 * @return	void
				 */
				function menuConfig()	{
					global $LANG;
					$this->MOD_MENU = Array (
						'function' => Array (
							'1' => $LANG->getLL('function1'),
							'2' => $LANG->getLL('function2'),
							'3' => $LANG->getLL('function3'),
						)
					);
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
					
					
//					$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
//					$access = is_array($this->pageinfo) ? 1 : 0;
					$access = 1; /// \todo: maybe we should implement a more sophisticated version of this ;-)

					if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{

							// Draw the header.
						$this->doc = t3lib_div::makeInstance('noDoc');
						$this->doc->backPath = $BACK_PATH;
						$this->doc->form='<form action="" method="post" enctype="multipart/form-data">';

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
//						$this->content.=$this->doc->spacer(5);
//						$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
//						$this->content.=$this->doc->divider(5);


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
				function moduleContent() {
					global $LANG;
#t3lib_div::devlog('mod_settings', 'newspaper', 0, $this->MOD_SETTINGS);
				
#debug($_REQUEST);
					if (!$this->section_id) {
						/// no section id found, just display message to choose a section from the section tree
						$this->content .= $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_section_placement_no_section_chosen', false);
					} else { 

$content .= 'dummy #' . $this->section_id;
//					$content= $this->renderBackendSmartyPageZone(
//						tx_newspaper_PageZone_Factory::getInstance()->create(intval($this->id))
//					);
					}
					$this->content .= $this->doc->section('', $content, 0, 1);
				}

	
	function readUidList() {
		global $BE_USER;

		/// \todo: check permissions
		
		/// process section id param
		if ($this->id) {
			$this->section_id = $this->id; // clicked in section tree
		} else if (t3lib_div::_GP('section_id')) {
			$this->section_id = t3lib_div::_GP('section_id');
		} else if ($BE_USER->getModuleData("tx_newspaper/mod3/index.php/section_id")){
			$this->section_id = $BE_USER->getModuleData("tx_newspaper/mod3/index.php/section_id");
		} else {
			$this->section_id = 0; // nothing found
		}
		if ($this->section_id) {
			$s = new tx_newspaper_Section(intval($this->section_id));
			if (!$s->isValid()) {
				// no valid section, nothing to show ...
				$this->section_id = 0; 
				$this->page_id = 0;
				$this->pagezone_id = 0;
			}
		}
//t3lib_div::debug($s);


		/// process page id param
		if ($this->section_id) {
			if (t3lib_div::_GP('page_id')) {
				$this->page_id = t3lib_div::_GP('page_id'); 
			} else if ($BE_USER->getModuleData("tx_newspaper/mod3/index.php/page_id")){
				$this->page_id = $BE_USER->getModuleData("tx_newspaper/mod3/index.php/page_id"); 
			} else {
				$active_page = $s->getSubPages();
				if (sizeof($active_page) > 0)
					$this->page_id = $active_page[0]->getUid(); // use first assigned page initially
				else
					$this->page_id = 0; // nothing found
			}
		}
		if ($this->page_id) {
			$p = new tx_newspaper_Page();
			$p->setUid(intval($this->page_id));
			if (!$p->isValid($s)) {
				// no valid page, only the section seems to be useful
				$this->page_id = 0;
				$this->pagezone_id = 0;
			}
		}
t3lib_div::debug($p);

	
		if ($this->page_id) {
			if (t3lib_div::_GP('pagezone_id')) {
				$this->pagezone_id = t3lib_div::_GP('pagezone_id');
			} else if ($BE_USER->getModuleData("tx_newspaper/mod3/index.php/pagezone_id")){
				$this->pagezone_id = $BE_USER->getModuleData("tx_newspaper/mod3/index.php/pagezone_id");
			} else {

				$active_pagezone = $p->getPageZones();
				if (sizeof($active_pagezone) > 0)
					$this->pagezone_id = $active_pagezone[0]->getUid(); // use first activated pagezone initally
				else
					$this->pagezone_id = 0; // nothing found
			}
		}
		if ($this->pagezone_id) {
			$pz = tx_newspaper_PageZone_Factory::getInstance()->create(intval($this->pagezone_id));
t3lib_div::debug($pz);			
		}



		/// store ids for be user for later use
		$BE_USER->pushModuleData("tx_newspaper/mod3/index.php/section_id", $this->section_id);
		$BE_USER->pushModuleData("tx_newspaper/mod3/index.php/page_id", $this->page_id);
		$BE_USER->pushModuleData("tx_newspaper/mod3/index.php/pagezone_id", $this->pagezone_id);		
		
	}


	function renderBackendSmartyPageZone(tx_newspaper_PageZone $pz) {
		global $LANG;
		
 		$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/res/be/templates'));


//t3lib_div::devlog('moderation: comment still missing', 'newspaper', 0);
////dummy data
//for ($i=0; $i < sizeof($row); $i++) {
//	$row[$i]['comment'] = 'oliver (2008-03-21 17:37): Dies ist ein Beispiel f¸r die Anzeige des letzten Kommentars. Dies ist ein Beispiel f¸r die Anzeige des letzten Kommentars.Dies ist ein Beispiel f¸r die Anzeige des letzten Kommentars.Dies ist ein Beispiel f¸r die Anzeige des letzten Kommentars.Dies ist ein Beispiel f¸r die Anzeige des letzten Kommentars.Dies ist ein Beispiel f¸r die Anzeige des letzten Kommentars.';
//}
//t3lib_div::devlog('moderation: be user still missing', 'newspaper', 0);
//
//
//		$smarty->assign('PAGE_PREV_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.page_prev', false));
//		$smarty->assign('PAGE_NEXT_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.page_next', false));
//		$smarty->assign('PAGE_HITS_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.page_hits', false));
//
//		$smarty->assign('RANGE', $this->getRangeArray()); // add data for range dropdown
//		$smarty->assign('RANGE_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.range', false));
//
//		$smarty->assign('STEP', array(10, 20, 30, 50, 100)); // add data for step dropdoen (for page browser)
//		$smarty->assign('STEP_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.step_items_per_page', false));
//		$smarty->assign('START_PAGE', t3lib_div::_GP('start_page'));
//
//		$smarty->assign('HIDDEN', $this->getHiddenArray()); // add data for "hidden" dropdown
//		$smarty->assign('HIDDEN_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.status_hidden', false));
//
//		$smarty->assign('MODERATION', $this->getModerationArray()); // add data for moderation dropdown
//		$smarty->assign('MODERATION_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.status_moderation', false));
//
//		$smarty->assign('AUTHOR_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.author', false));
//		$smarty->assign('SECTION_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.section', false));
//		$smarty->assign('TEXTSEARCH_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.textsearch', false));
//
//		$smarty->assign('GO_LABEL', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.go', false));
//
//		$smarty->assign('HIDE_ICON', tx_newspaper_BE::renderIcon('gfx/button_hide.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.hide', false)));
//		$smarty->assign('UNHIDE_ICON', tx_newspaper_BE::renderIcon('gfx/button_unhide.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.unhide', false)));
//		$smarty->assign('ARTICLE_PREVIEW_ICON', tx_newspaper_BE::renderIcon('gfx/zoom.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.preview_article', false)));
//		$smarty->assign('ARTICLE_EDIT_ICON', tx_newspaper_BE::renderIcon('gfx/edit2.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.edit_article', false)));
//		$smarty->assign('COMMENT_ICON', tx_newspaper_BE::renderIcon('gfx/zoom2.gif', '', '###COMMENT###'));
//		$smarty->assign('TIME_HIDDEN_ICON', tx_newspaper_BE::renderIcon('gfx/history.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.time', false)));
//		$smarty->assign('TIME_VISIBLE_ICON', tx_newspaper_BE::renderIcon('gfx/icon_ok2.gif', '', $LANG->sL('LLL:EXT:newspaper/mod2/locallang.xml:label.time', false)));
//
//
//		/// build browse sequence
//		if (intval(t3lib_div::_GP('start_page')) > 0) {
//			$smarty->assign('URL_PREV', tx_newspaper_UtilMod::convertPost2Querystring(array('start_page' => intval(t3lib_div::_GP('start_page')) - 1)));
//		} else {
//			$smarty->assign('URL_PREV', '');
//		}
//		if (sizeof($row) > intval(t3lib_div::_GP('step'))) {
//			// so there's at least one next record
//			$smarty->assign('URL_NEXT', tx_newspaper_UtilMod::convertPost2Querystring(array('start_page' => intval(t3lib_div::_GP('start_page')) + 1)));
//			$row = array_slice($row, 0, intval(t3lib_div::_GP('step'))); // cut off entry from next page
//		} else {
//			$smarty->assign('URL_NEXT', '');
//		}
//		
//		
//		/// build url for switch visibility button
//		$smarty->assign('URL_HIDE_UNHIDE', tx_newspaper_UtilMod::convertPost2Querystring(array('uid' => '###ARTILCE_UID###')));
//		
//
//		$smarty->assign('DATA', $row);
//
//		$smarty->assign('_POST', t3lib_div::_POST()); // add _post data (for setting default values)
//
//
//		$smarty->assign('T3PATH', substr(PATH_typo3, strlen($_SERVER['DOCUMENT_ROOT']))); // path to typo3, needed for edit article (form: /a/b/c/typo3/)
		
		return $smarty->fetch('mod3_pagezone.tmpl');
	}




				
		}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod3/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod3/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_newspaper_module3');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>