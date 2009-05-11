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
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

//unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH . 'init.php');
require_once($BACK_PATH . 'template.php');


//var_dump(debug_backtrace());
//debug($_REQUEST, '_reuqest');

$LANG->includeLLFile('EXT:newspaper/mod3/locallang.xml');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]



/**
 * Module 'Placement' for the 'newspaper' extension.
 *
 * @author	Helge Preuss, Oliver Schröder, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
 * @package	TYPO3
 * @subpackage	tx_newspaper
 */
class  tx_newspaper_module3 extends t3lib_SCbase {
				var $pageinfo;
				
				private $section_id;
				private $page_id;
				private $pagezone_id;
				private $show_levels_above;
				

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
				 *
				 * @return	void
				 */
				function menuConfig()	{
					global $LANG;
//					$this->MOD_MENU = Array (
//						'function' => Array (
//							'1' => $LANG->getLL('function1'),
//							'2' => $LANG->getLL('function2'),
//							'3' => $LANG->getLL('function3'),
//						)
//					);
					parent::menuConfig();
				}


	private function processToggleShowLevelsAbove($checked) {
		global $BE_USER;
		if (strtolower($checked) == 'true')
			$checked = true;
		else
			$checked = false;
		$BE_USER->pushModuleData("tx_newspaper/mod3/index.php/show_levels_above", $checked);
		die();
	}

	private function processExtraInsertAfter($origin_uid, $pz_uid) {
		$e = new tx_newspaper_Extra_Image();
		$e->setAttribute('title', 'Dummy ' . rand(1, 1000));
		$e->store();
		$pz = tx_newspaper_PageZone_Factory::getInstance()->create(intval($pz_uid));
		$pz->insertExtraAfter($e, $origin_uid);
		die();
	}

	private function processExtraMoveAfter($origin_uid, $pz_uid, $extra_uid) {
		$e = tx_newspaper_Extra_Factory::getInstance()->create(intval($extra_uid));	
		$pz = tx_newspaper_PageZone_Factory::getInstance()->create(intval($pz_uid));
		$pz->moveExtraAfter($e, $origin_uid);
		die();
	}

	private function processExtraDelete($pz_uid, $extra_uid) {
		$pz = tx_newspaper_PageZone_Factory::getInstance()->create(intval($pz_uid));
		$e = tx_newspaper_Extra_Factory::getInstance()->create(intval($extra_uid));	
		$pz->removeExtra($e);
		die();
	}


	private function check4Ajax() {
		// TODO check permissions
t3lib_div::devlog('_request mod3 ajax', 'newspaper', 0, $_REQUEST);

		if (t3lib_div::_GP('toggle_show_levels_above') == 1) {
			$this->processToggleShowLevelsAbove(t3lib_div::_GP('checked')); 
		}

		if (t3lib_div::_GP('extra_insert_after') == 1) {
			$this->processExtraInsertAfter(t3lib_div::_GP('origin_uid'), t3lib_div::_GP('pz_uid')); 
		}

		if (t3lib_div::_GP('extra_move_after') == 1) {
			$this->processExtraMoveAfter(t3lib_div::_GP('origin_uid'), t3lib_div::_GP('pz_uid'), t3lib_div::_GP('extra_uid')); 
		}

		if (t3lib_div::_GP('extra_delete') == 1) {
			$this->processExtraDelete(t3lib_div::_GP('pz_uid'), t3lib_div::_GP('extra_uid')); 
		}


		if (t3lib_div::_GP('chose_extra') == 1) {
			die($this->getChoseExtraForm());	
		}
//t3lib_div::devlog('_request mod3 ajax - NO ajax found', 'newspaper', 0);		
		return; // no ajax request found
	}


	private function getChoseExtraForm() {
		global $LANG;


		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->backPath = $GLOBALS['BACK_PATH'];
		
		$this->content .= $this->doc->startPage('');

		$this->content .= $this->getIconHeader();
				
//$this->content .= $this->doc->header($LANG->getLL('title'));
/// \todo: warum geht das nicht?
		$this->content .= $this->doc->header($LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:title_new_extra', false));
		$this->content .= $this->doc->header('New extra');

		$html = '';		
		$extra = tx_newspaper_Extra::getRegisteredExtras();
		for ($i = 0; $i < sizeof($extra); $i++) {
			$html .= $extra[$i]->getTitle() . '<br />';
		}

		$this->content .= $this->doc->section('', $html, 0, 1);
		$this->content.=$this->doc->endPage();
		
		return $this->content;
	}

/// \todo: auslagern? extend template???
	function getIconHeader() {
		global $LANG;

		$html = '<div id="typo3-docheader-row1"><div class="buttonsleft"><div class="buttongroup">';

		$html .= tx_newspaper_BE::wrapInAhref(
			tx_newspaper_BE::renderIcon('gfx/close.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_close', false)),
			BE_ICON_CLOSE
		);
		
		$html .= '</div></div></div>';	
		
		return $html;
	}



				/**
				 * Main function of the module. Write the content to $this->content
				 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
				 *
				 * @return	[type]		...
				 */
				function main()	{
					
					$this->check4Ajax(); /// if this is an ajax call, the request gets process and execution of this file ends with die() 							
					
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

if (!tx_newspaper::atLeastOneRecord('tx_newspaper_section')) {	
	die($LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_section_placement_no_section_available', false));
}


					$this->readUidList(); // get ids for section, page and pagezone
t3lib_div::devlog('mod3 ids', 'newspaper', 0, array($this->id, $this->section_id, $this->page_id, $this->pagezone_id));
				
#debug($_REQUEST);
					if (!$this->section_id) {
						if (tx_newspaper::atLeastOneRecord('tx_newspaper_section')) {	
							/// check if at least one section exists
							$this->content .= $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_section_placement_no_section_available', false);
						} else {
							/// no section id found, just display message to choose a section from the section tree
							$this->content .= $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_section_placement_no_section_chosen', false);
						}
					} else { 
$content .= 'dummy #' . $this->section_id;
						$content= $this->renderBackendSmartyPageZone(
							tx_newspaper_PageZone_Factory::getInstance()->create(intval($this->pagezone_id))
						);
					}
					$this->content .= $this->doc->section('', $content, 0, 1);
				}

	
	/// read section_is, page_id and pagezone_id (if possible)
	/// fills $this->section_id, $this->page_id and $this->pagezone_id
	/// \return void
	function readUidList() {
		global $BE_USER;
		/// \todo: check permissions?
				
		$this->show_levels_above = $BE_USER->getModuleData('tx_newspaper/mod3/index.php/show_levels_above'); // read from be user
//		if ($this->show_levels_above !== true) $this->show_levels_above = false; // make sure it's boolean
t3lib_div::devlog('show levels above read from be_user', 'newspaper', 0, $this->show_levels_above);

		
/// \todo: remove hard coded uid !!!!!!!!!!!!!!!!!
if (TYPO3_OS == 'WIN') {
// localhost
	$this->section_id = 2;
	$this->page_id = 3;
	$this->pagezone_id = 8;
} else {
// hel
	$this->section_id = 1892;
	$this->page_id = 3023;
	$this->pagezone_id = 6448;
}	
return;		
		
		
		
		

		/// process section id param
		if ($this->id) {
			$this->section_id = $this->id; // clicked in section tree
		} else if (t3lib_div::_GP('section_id')) {
			$this->section_id = t3lib_div::_GP('section_id'); // _request param
		} else if ($BE_USER->getModuleData("tx_newspaper/mod3/index.php/section_id")){
			$this->section_id = $BE_USER->getModuleData("tx_newspaper/mod3/index.php/section_id"); // read from be user
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
				$this->page_id = t3lib_div::_GP('page_id'); // read from _request
			} else if ($BE_USER->getModuleData("tx_newspaper/mod3/index.php/page_type_id")){
				// try to get the page of the last used page type
				$page_type_id = $BE_USER->getModuleData("tx_newspaper/mod3/index.php/page_type_id"); // read from be user
				$pt = new tx_newspaper_PageType(intval($page_type_id));
				if ($pt->isValid()) {
					// is that page type available for given section?
					
//...				
					
					
					
					
					$active_page = $s->getSubPages();
					if (sizeof($active_page) > 0)
						$this->page_id = $active_page[0]->getUid(); // use first assigned page initially
					else
						$this->page_id = 0; // nothing found
				} else {
					// stored page type isn't valid
					$this->page_id = 0;
					$this->pagezone_id = 0;
				}
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
		#$BE_USER->pushModuleData("tx_newspaper/mod3/index.php/page_id", $this->page_id);
		#$BE_USER->pushModuleData("tx_newspaper/mod3/index.php/pagezone_id", $this->pagezone_id);		
		
	}



	private function collectExtras(array $extra) {
		$data = array();
		for ($i = 0; $i < sizeof($extra); $i++) {
		$data[] = array(
				'extra_type' => $extra[$i]->getTitle(),
				'uid' => $extra[$i]->getExtraUid(),
				'title' => $extra[$i]->getAttribute('title'),
				'show' => $extra[$i]->getAttribute('show_extra'),
				'pass_down' => $extra[$i]->getAttribute('is_inheritable'),
'inherits_from' => 'to come ...', /// \todo: function missing here ...
				'origin_placement' => $extra[$i]->isOriginExtra(),
				'origin_uid' => $extra[$i]->getOriginUid(),
			);
		}
		return $data;
	} 

	private function extractData($pz) {
		$s = $pz->getParentPage()->getParentSection();
//debug(t3lib_div::view_array($s), 's');
		return array(
				'section' => array_reverse($s->getSectionPath()), 
				'page_type' => $pz->getParentPage()->getPageType()->getAttribute('type_name'),
				'pagezone_type' => $pz->getPageZoneType()->getAttribute('type_name'),
				'pagezone_id' => $pz->getPagezoneUid(),
			);
	}



	private function renderBackendSmartyPageZone(tx_newspaper_PageZone $pz) {
		global $LANG;

		$data = array();
		$extra_data = array();

		/// add upper level page zones and extras, if any		
		if ($this->show_levels_above) {
			$pz_up = array_reverse($pz->getInheritanceHierarchyUp(false));
			for ($i = 0; $i < sizeof($pz_up); $i++) {
#debug($pz_up[$i]->getUid(), 'pz_up');	
				$data[] = $this->extractData($pz_up[$i]);
				$extra_data[] = $this->collectExtras($pz_up[$i]->getExtras());
			}
		}

		/// add current page zone and extras		
		$data[] = $this->extractData($pz);
		$extra_data[] = $this->collectExtras($pz->getExtras());

//debug(t3lib_div::view_array($extra_data), 'extra data');
//debug(t3lib_div::view_array($data), 'data');				
		
		
 		$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod3/'));

		$label['extra'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_extra', false);
		$label['show'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_show', false);
		$label['pass_down'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_pass_down', false);
		$label['inherits_from'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_inherits_from', false);
		$label['commands'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_commands', false);
		$label['show_levels_above'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_show_levels_above', false);
		$label['extra_delete_confirm'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_delete_confirm', false);

		$smarty->assign('LABEL', $label);
		$smarty->assign('EXTRA_DATA', $extra_data);
		$smarty->assign('DATA', $data);

		$smarty->assign('SHOW_LEVELS_ABOVE', $this->show_levels_above);

/// \todo: move to array (like $label)
		$smarty->assign('HIDE_ICON', tx_newspaper_BE::renderIcon('gfx/button_hide.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_hide', false)));
		$smarty->assign('UNHIDE_ICON', tx_newspaper_BE::renderIcon('gfx/button_unhide.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_unhide', false)));
		$smarty->assign('EDIT_ICON', tx_newspaper_BE::renderIcon('gfx/edit2.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_edit_extra', false)));
		$smarty->assign('MOVE_UP_ICON', tx_newspaper_BE::renderIcon('gfx/button_up.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_move_up', false)));
		$smarty->assign('MOVE_DOWN_ICON', tx_newspaper_BE::renderIcon('gfx/button_down.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_move_down', false)));
		$smarty->assign('NEW_TOP_ICON', tx_newspaper_BE::renderIcon('gfx/new_record.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_new_top', false)));
		$smarty->assign('NEW_BELOW_ICON', tx_newspaper_BE::renderIcon('gfx/new_record.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_new_below', false)));
		$smarty->assign('DELETE_ICON', tx_newspaper_BE::renderIcon('gfx/garbage.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_delete', false)));
//		$smarty->assign('REMOVE_ICON', tx_newspaper_BE::renderIcon('gfx/selectnone.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_delete', false)));
		$smarty->assign('EMPTY_ICON', '<img src="clear.gif" width=16" height="16" alt="" />');
		
		$smarty->assign('MODULE_PATH', TYPO3_MOD_PATH); // path to typo3, needed for edit article (form: /a/b/c/typo3/)
		
		return $smarty->fetch('mod3.tmpl');
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