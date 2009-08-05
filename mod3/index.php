<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Helge Preuss, Oliver Schroeder, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
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


// width: 100%
class fullWidthDoc extends template {
	var $divClass = 'typo3-fullWidthDoc';
}




//var_dump(debug_backtrace());
//debug($_REQUEST, '_reuqest');

$LANG->includeLLFile('EXT:newspaper/mod3/locallang.xml');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

define('DEBUG_OUTPUT', true); // show position etc.

/**
 * Module 'Placement' for the 'newspaper' extension.
 *
 * @author	Helge Preuss, Oliver Schroeder, Samuel Talleux <helge.preuss@gmail.com, typo3@schroederbros.de, samuel@talleux.de>
 * @package	TYPO3
 * @subpackage	tx_newspaper
 */
class  tx_newspaper_module3 extends t3lib_SCbase {
	var $pageinfo;
	
	private $section_id;
	private $page_id;
	private $pagezone_id;
	
	private $page_zone_id;
	private $pagezone_type_id;
	
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

	private function processPageTypeChange($pt_uid) {
		global $BE_USER;
		$BE_USER->pushModuleData("tx_newspaper/mod3/index.php/page_type_id", intval($pt_uid));
		die();
	}

	private function processPageZoneTypeChange($pzt_uid) {
		global $BE_USER;
		$BE_USER->pushModuleData("tx_newspaper/mod3/index.php/pagezone_type_id", intval($pzt_uid));
		die();
	}

	private function processExtraInsertAfter($origin_uid, $pz_uid, $paragraph=false) {
/// \todo: remove if not needed
//		if ($paragraph == 'false') {
//			$paragraph = false;
//		} else {
//			$paragraph = intval($paragraph);
//		}
		
		$e = new tx_newspaper_Extra_Image();
		$e->setAttribute('title', '');
		$e->store();		
		$e->setAttribute('show_extra', 1);
		$e->setAttribute('is_inheritable', 1);
		
		$e->store();
		$pz = tx_newspaper_PageZone_Factory::getInstance()->create(intval($pz_uid));
		$pz->insertExtraAfter($e, intval($origin_uid));
		die();
	}
	private function processExtraInsertAfterFromPoolCopy($origin_uid, $extra_class, $pooled_extra_uid, $pz_uid, $paragraph, $path) {
		$origin_uid = intval($origin_uid);
		$pooled_extra_uid = intval($pooled_extra_uid);
		$pz_uid = intval($pz_uid);
//		$paragraph = intval($paragraph); // \todo: needed???
		
		$pz = tx_newspaper_PageZone_Factory::getInstance()->create($pz_uid); // create pagezone or article

		$e = new $extra_class($pooled_extra_uid);
		$copied_extra = $e->duplicate();
		$copied_extra->setAttribute('pool', false);
		$copied_extra->store();	

		$pz->insertExtraAfter($copied_extra, $origin_uid);

		header('location: http://' . $_SERVER['SERVER_NAME'] . $path . 'typo3conf/ext/newspaper/mod3/close.html');
		die();
	}
	private function processExtraInsertAfterFromPoolReference($origin_uid, $extra_class, $pooled_extra_uid, $pz_uid, $paragraph, $path) {
		$origin_uid = intval($origin_uid);
		$pooled_extra_uid = intval($pooled_extra_uid);
		$pz_uid = intval($pz_uid);
//		$paragraph = intval($paragraph); // \todo: needed???

		$pz = tx_newspaper_PageZone_Factory::getInstance()->create($pz_uid); // create pagezone or article
		
		$abstract_uid = tx_newspaper_Extra::createExtraRecord($pooled_extra_uid, $extra_class, true); // true = force new record to be written
		$e = tx_newspaper_Extra_Factory::getInstance()->create($abstract_uid);
		
		$pz->insertExtraAfter($e, $origin_uid);		

		header('location: http://' . $_SERVER['SERVER_NAME'] . $path . 'typo3conf/ext/newspaper/mod3/close.html');
		die();
	}	
	
	
	private function processExtraInsertAfterDummy($origin_uid, $pz_uid) {
/// \todo: remove after testing
		$e = new tx_newspaper_Extra_Image();
		$e->setAttribute('title', 'Dummy ' . rand(1, 1000));
		$e->store();		
		$e->setAttribute('show_extra', 1);
		$e->setAttribute('is_inheritable', 1);
		
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

	private function processExtraSetShow($extra_uid, $show) {
		$e = tx_newspaper_Extra_Factory::getInstance()->create(intval($extra_uid));	
		$e->setAttribute('show_extra', $show);
		$e->store();
		die();
	}

	private function processExtraSetPassDown($pz_uid, $extra_uid, $pass_down) {
		$pz = tx_newspaper_PageZone_Factory::getInstance()->create(intval($pz_uid));
		$e = tx_newspaper_Extra_Factory::getInstance()->create(intval($extra_uid));	
		$pz->setInherits($e, $pass_down);
//		$e->setAttribute('is_inheritable', $pass_down);
//		$e->store();
		die();
	}

	private function processSaveExtraField($pz_uid, $extra_uid, $value, $type) {

		$pz = tx_newspaper_PageZone_Factory::getInstance()->create(intval($pz_uid));
		$e = tx_newspaper_Extra_Factory::getInstance()->create(intval($extra_uid));
		
		switch(strtolower($type)) {
			case 'para':
				$e->setAttribute('position', 0); // move as first element to new paragraph
				$pz->changeExtraParagraph($e, intval($value)); // change paragraph (and inherit the change); this function stores the extra (so the position change is stored there)
			break;
			case 'notes':
				$e->setAttribute('notes', $value);
				$e->store();
			break;
			default:
				die('Unknown type when saving field: ' + $type);
		}
		die(); 
	}

	private function processTemplateSetDropdownStore($table, $uid, $value) {
		$uid = intval($uid);
		
		switch(strtolower($table)) {
			case 'tx_newspaper_extra':
				$obj = tx_newspaper_Extra_Factory::getInstance()->create($uid);
			break;
			case 'tx_newspaper_page':
				$obj = new tx_newspaper_page($uid);
			break;
			case 'tx_newspaper_pagezone':
				$obj = tx_newspaper_PageZone_Factory::getInstance()->create($uid);
			break;
			default:
				die('Unknown table for template set: ' . $table);
		}
		$obj->setAttribute('template_set', $value);
		$obj->store();
		die();
	}














	private function check4Ajax() {
		/// \todo: check permissions
//t3lib_div::devlog('_request mod3 ajax', 'newspaper', 0, $_REQUEST);

		if (t3lib_div::_GP('toggle_show_levels_above') == 1) {
			$this->processToggleShowLevelsAbove(t3lib_div::_GP('checked')); 
		}

		if (t3lib_div::_GP('extra_insert_after') == 1) {
			$this->processExtraInsertAfter(t3lib_div::_GP('origin_uid'), t3lib_div::_GP('pz_uid')); 
		}
		if (t3lib_div::_GP('extra_insert_after_dummy') == 1) {
/// \todo: remove after testing
			$this->processExtraInsertAfterDummy(t3lib_div::_GP('origin_uid'), t3lib_div::_GP('pz_uid')); 
		}


		if (t3lib_div::_GP('extra_move_after') == 1) {
			$this->processExtraMoveAfter(t3lib_div::_GP('origin_uid'), t3lib_div::_GP('pz_uid'), t3lib_div::_GP('extra_uid')); 
		}

		if (t3lib_div::_GP('extra_delete') == 1) {
			$this->processExtraDelete(t3lib_div::_GP('pz_uid'), t3lib_div::_GP('extra_uid')); 
		}

		if (t3lib_div::_GP('extra_set_show') == 1) {
			$this->processExtraSetShow(t3lib_div::_GP('extra_uid'), t3lib_div::_GP('show')); 
		}

		if (t3lib_div::_GP('extra_set_pass_down') == 1) {
			$this->processExtraSetPassDown(t3lib_div::_GP('pz_uid'), t3lib_div::_GP('extra_uid'), t3lib_div::_GP('pass_down')); 
		}
		
		if (t3lib_div::_GP('extra_page_type_change') == 1) {
			$this->processPageTypeChange(t3lib_div::_GP('pt_uid')); 
		}
		
		if (t3lib_div::_GP('extra_pagezone_type_change') == 1) {
			$this->processPageZoneTypeChange(t3lib_div::_GP('pzt_uid')); 
		}
		
		if (t3lib_div::_GP('extra_save_field') == 1) {
			$this->processSaveExtraField(t3lib_div::_GP('pz_uid'), t3lib_div::_GP('extra_uid'), t3lib_div::_GP('value'), t3lib_div::_GP('type'));
		}


		if (t3lib_div::_GP('chose_extra') == 1) {
			die($this->getChoseExtraForm(t3lib_div::_GP('origin_uid'), t3lib_div::_GP('pz_uid'), t3lib_div::_GP('paragraph'), t3lib_div::_GP('new_at_top')));	
		}
		if (t3lib_div::_GP('chose_extra_from_pool') == 1) {
			die($this->getChoseExtraFromPoolForm(t3lib_div::_GP('origin_uid'), t3lib_div::_GP('extra'), t3lib_div::_GP('pz_uid'), t3lib_div::_GP('paragraph')));	
		}
		
		if (t3lib_div::_GP('extra_insert_after_from_pool_copy') == 1) {
			die($this->processExtraInsertAfterFromPoolCopy(t3lib_div::_GP('origin_uid'), t3lib_div::_GP('extra_class'), t3lib_div::_GP('pooled_extra_uid'), t3lib_div::_GP('pz_uid'), t3lib_div::_GP('paragraph'), t3lib_div::_GP('path')));	
		}
		if (t3lib_div::_GP('extra_insert_after_from_pool_ref') == 1) {
			die($this->processExtraInsertAfterFromPoolReference(t3lib_div::_GP('origin_uid'), t3lib_div::_GP('extra_class'), t3lib_div::_GP('pooled_extra_uid'), t3lib_div::_GP('pz_uid'), t3lib_div::_GP('paragraph'), t3lib_div::_GP('path')));	
		}

		if (t3lib_div::_GP('templateset_dropdown_store') == 1) {
			die($this->processTemplateSetDropdownStore(t3lib_div::_GP('table'), t3lib_div::_GP('uid'), t3lib_div::_GP('value')));	
		}
		
//t3lib_div::devlog('_request mod3 ajax - NO ajax found', 'newspaper', 0);		
//debug('no ajax');
		return; // no ajax request found
	}


	private function getChoseExtraForm($origin_uid, $pz_uid, $paragraph=false, $new_at_top=false) {
		global $LANG;
//debug(array($origin_uid, $pz_uid, $paragraph));

		// convert params, sent by js, so false is given as string, not a boolean
/// \todo: find a better way ...
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


		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->backPath = $GLOBALS['BACK_PATH'];
		
		$this->content .= $this->doc->startPage('');

		$this->content .= $this->getIconHeader();
				
		$this->content .= $this->doc->header($LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:title_new_extra', false));

 	 	$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod3/'));

		$label['new_extra_new'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_new_extra_new', false);
		$label['new_extra_from_pool'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_new_extra_from_pool', false);
		$message['no_extra_selected'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_no_extra_selected', false);

		/// list of registered extras
		$extra = tx_newspaper_Extra::getRegisteredExtras();
//debug($extra, 'e');
		
		$smarty->assign('LABEL', $label);
		$smarty->assign('MESSAGE', $message);
		$smarty->assign('EXTRA', $extra); // list of extras
		$smarty->assign('LIST_SIZE', max(2, min(12, sizeof($extra)))); /// size at least 2, otherwise list would be rendered as dropdown
		$smarty->assign('ORIGIN_UID', intval($origin_uid));
		$smarty->assign('PZ_UID', intval($pz_uid));

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
		
		
		$html = $smarty->fetch('mod3_new_extra.tmpl');

		$this->content .= $this->doc->section('', $html, 0, 1);
		$this->content .= $this->doc->endPage();
		
		return $this->content;
	}


	private function getChoseExtraFromPoolForm($origin_uid, $classname, $pz_uid, $paragraph) {
		global $LANG;

		$origin_uid = intval($origin_uid);
		$pz_uid = intval($pz_uid);
		$paragraph = intval($paragraph);


		$e = new $classname(); // instance of a concrete extra
		
		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->backPath = $GLOBALS['BACK_PATH'];
		
		$this->content .= $this->doc->startPage('');

		$this->content .= $this->getIconHeader();
		
		$this->content .= $this->doc->header($LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:title_new_extra_from_pool', false) . ': ' . $e->getTitle());
		
		$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod3/'));
		
		$label['extra_copy_from_pool'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_extra_copy_from_pool', false);
		$label['extra_reference_from_pool'] =  $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_extra_reference_from_pool', false);
		$message['pool_is_empty'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_pool_is_empty', false);
		$message['no_extra_selected'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_no_extra_selected', false);
		

		$pooled = $e->getPooledExtras();
		
		$smarty->assign('EXTRA_POOLED', $pooled);
		$smarty->assign('LABEL', $label);
		$smarty->assign('MESSAGE', $message);
		$smarty->assign('PARAGRAPH', $paragraph);
		$smarty->assign('EXTRA_CLASS', $classname);
		$smarty->assign('PZ_UID', $pz_uid);
		$smarty->assign('ORIGIN_UID', $origin_uid);
		$smarty->assign('LIST_SIZE', max(2, min(12, sizeof($pooled)))); /// size at least 2, otherwise list would be rendered as dropdown
		
		$html = $smarty->fetch('mod3_new_extra_from_pool.tmpl');
		
		$this->content .= $this->doc->section('', $html, 0, 1);
		$this->content .= $this->doc->endPage();
		
		return $this->content;
	}


/// \todo: auslagern? 
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
						$this->doc = t3lib_div::makeInstance('fullWidthDoc');
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

						$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />' . $LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						$this->content.=$this->doc->header($LANG->getLL('title'));
//						$this->content.=$this->doc->spacer(5);
//						$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
//						$this->content.=$this->doc->divider(5);


						// Render content:
						$this->moduleContent();


						// ShortCut
//						if ($BE_USER->mayMakeShortcut())	{
//							$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
//						}

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

					/// check if at least one section page type and page zone type are available. if not, this module is senseless.
					if (!tx_newspaper::atLeastOneRecord('tx_newspaper_section')) {	
						die($LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_section_placement_no_section_available', false));
					}
					if (!tx_newspaper::atLeastOneRecord('tx_newspaper_pagetype')) {	
						die($LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_section_placement_no_pagetype_available', false));
					}
					if (!tx_newspaper::atLeastOneRecord('tx_newspaper_pagezonetype')) {	
						die($LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_section_placement_no_pagezonetype_available', false));
					}

//global $BE_USER; debug(array('section id' => $BE_USER->getModuleData("tx_newspaper/mod3/index.php/section_id"), 'page type id' => $BE_USER->getModuleData("tx_newspaper/mod3/index.php/page_type_id"), 'pagezone type id' => $BE_USER->getModuleData("tx_newspaper/mod3/index.php/pagezone_type_id")));
					$this->readUidList(); // get ids for section, page and pagezone
//debug(array('section id' => $this->section_id, 'page type id' => $this->page_type_id, 'pagezone type id' => $this->pagezone_type_id, 'page id' => $this->page_id, 'pagezone id' => $this->pagezone_id));
//debug($_REQUEST);

					if (!$this->section_id) {
						if (tx_newspaper::atLeastOneRecord('tx_newspaper_section')) {	
							/// check if at least one section exists
							$this->content .= $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_section_placement_no_section_available', false);
						} else {
							/// no section id found, just display message to choose a section from the section tree
							$this->content .= $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_section_placement_no_section_chosen', false);
						}
					} else if (!$this->page_id) {
						/// no page has been activated for given section
						$this->content .= $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_section_placement_no_page_available_for_section', false);
					} else if (!$this->pagezone_id) {  
						/// no pagezone has been activated for given page for given section
						$this->content .= $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_section_placement_no_pagetype_available_for_page', false);
					} else {
						/// render form for pagezone
						$content = $this->renderBackendSmartyPageZone(
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
		if ($this->show_levels_above !== true) $this->show_levels_above = false; // make sure it's boolean
		
		// init
		$this->section_id = 0;
		$this->page_type_id = 0;
		$this->pagezone_type_id = 0;
		
		$this->page_type_id = 0;
		$this->pagezone_type_id = 0;


		/// process section id 
		if ($this->id) {
			$this->section_id = $this->id; // clicked in section tree
		} else if ($BE_USER->getModuleData("tx_newspaper/mod3/index.php/section_id")){
			$this->section_id = $BE_USER->getModuleData("tx_newspaper/mod3/index.php/section_id"); // read from be user
		} 
		if ($this->section_id) {
			$s = new tx_newspaper_Section(intval($this->section_id));
			if (!$s->isValid()) {
				// no valid section, nothing to show ...
				$this->section_id = 0; 
			}
		}
//debug(t3lib_div::view_array($s));


		/// process page type id 
		if ($this->section_id) {
			$active_pages = $s->getSubPages(); /// get list of available pages for given section
			if ($BE_USER->getModuleData("tx_newspaper/mod3/index.php/page_type_id")) {
				// check if page with stored page type is available for given section
				$this->page_type_id = $BE_USER->getModuleData("tx_newspaper/mod3/index.php/page_type_id"); // read from be user
				$pt = new tx_newspaper_PageType(intval($this->page_type_id));
				if ($pt->isValid()) {
					/// get page with given page type
					for ($i = 0; $i < sizeof($active_pages); $i++) {
						if ($active_pages[$i]->getPageType()->getUid() == $this->page_type_id) {
							$this->page_id = $active_pages[$i]->getUid();
							$p = $active_pages[$i]; /// save page object, needed for page zone check
							break;
						}
					}
				} else {
					// stored page type isn't valid
					$this->page_type_id = 0;
					$this->page_id = 0;
				}
			}
			if (!$this->page_id) {
				/// no page found so far, try to use first available page (as default value)
				if (sizeof($active_pages) > 0) {
					$this->page_type_id = $active_pages[0]->getPageType()->getUid();
					$this->page_id = $active_pages[0]->getUid();
					$p = $active_pages[0]; /// save page object, needed for page zone check
				}
			}
		}
//debug(t3lib_div::view_array($p));


		/// processpage zone type id 
		if ($this->page_id) {
			$active_pagezones = $p->getPageZones(); /// get list of available page zones for given page
//debug(t3lib_div::view_array($active_pagezones[0]));
			if ($BE_USER->getModuleData("tx_newspaper/mod3/index.php/pagezone_type_id")) {
				// check if page zone with stored pagezone type is available for given page
				$this->pagezone_type_id = $BE_USER->getModuleData("tx_newspaper/mod3/index.php/pagezone_type_id"); // read from be user
				$pzt = new tx_newspaper_PageZoneType(intval($this->pagezone_type_id));
				if (is_array($active_pagezones) && $pzt->isValid()) { ///  if no active pagezone is available, true is returned!
					/// get pagezone with given pagezone type
					for ($i = 0; $i < sizeof($active_pagezones); $i++) {
						if ($active_pagezones[$i]->getPageZoneType()->getUid() == $this->pagezone_type_id) {
							$this->pagezone_id = $active_pagezones[$i]->getAbstractUid();
							break;
						}
					}
				} else {
					// stored pagezone type isn't valid
					$this->pagezone_type_id = 0;
					$this->pagezone_id = 0;
				}
			}
			if (!$this->pagezone_id) {
				/// no pagezone found so far, try to use first available pagezone (as default value)
				if (is_array($active_pagezones) && sizeof($active_pagezones) > 0) {
					$this->pagezone_type_id = $active_pagezones[0]->getPageZoneType()->getUid();
					$this->pagezone_id = $active_pagezones[0]->getAbstractUid();
				}
			}
		}

		/// store ids for be user for later use
		$BE_USER->pushModuleData("tx_newspaper/mod3/index.php/section_id", $this->section_id);
		$BE_USER->pushModuleData("tx_newspaper/mod3/index.php/page_id", $this->page_type_id);
		$BE_USER->pushModuleData("tx_newspaper/mod3/index.php/pagezone_id", $this->pagezone_type_id);		
		
	}



	private function extractData(tx_newspaper_PageZone $pz) {
		$s = $pz->getParentPage()->getParentSection();
//debug(t3lib_div::view_array($s), 's');
		return array(
				'section' => array_reverse($s->getSectionPath()), 
				'page_type' => $pz->getParentPage()->getPageType(),
				'page_id' => $pz->getParentPage()->getUid(),
				'pagezone_type' => $pz->getPageZoneType(),
				'pagezone_id' => $pz->getPagezoneUid(),
				'pagezone_concrete_id' => $pz->getUid(),
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
				$data[] = $this->extractData($pz_up[$i]);
				$extra_data[] = tx_newspaper_BE::collectExtras($pz_up[$i]);
			}
		}

		/// add current page zone and extras		
		$data[] = $this->extractData($pz);
		$extra_data[] = tx_newspaper_BE::collectExtras($pz);
		
		$s = $pz->getParentPage()->getParentSection();
		$pages = $s->getSubPages(); // get activate pages for current section
		$pagetype = array();
		for ($i = 0; $i < sizeof($pages); $i++) {
			$pagetype[] = $pages[$i]->getPageType(); 
		}
		
		$pagezones = $pz->getParentPage()->getPageZones(); // get activate pages zone for current page
		$pagezonetype = array();
		for ($i = 0; $i < sizeof($pagezones); $i++) {
			$pagezonetype[] = $pagezones[$i]->getPageZoneType(); 
		}

		
 		$smarty = new tx_newspaper_Smarty();
		$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod3/'));

		$label['show_levels_above'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_show_levels_above', false);
		$label['pagetype'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_pagetype', false);
		$label['pagezonetype'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_pagezonetype', false);
		$message['pagezone_empty'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_pagezone_empty', false);

		$smarty->assign('LABEL', $label);
		$smarty->assign('MESSAGE', $message);
		$smarty->assign('DATA', $data);
		$smarty->assign('PAGETYPE', $pagetype);
		$smarty->assign('PAGEZONETYPE', $pagezonetype);
		$smarty->assign('SHOW_LEVELS_ABOVE', $this->show_levels_above);
		$smarty->assign('DUMMY_ICON', tx_newspaper_BE::renderIcon('gfx/dummy_button.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_new_top', false)));

		/// "new to top" buttons varies for pagezone_page (new to top) and article (new extra, set pos and paragraph in form)
		if ($data[0]['pagezone_type']->getAttribute('is_article') == 0)
			$smarty->assign('NEW_TOP_ICON', tx_newspaper_BE::renderIcon('gfx/new_record.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_new_top', false)));
		else
			$smarty->assign('NEW_TOP_ICON', tx_newspaper_BE::renderIcon('gfx/new_record.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_new_extra', false)));

		
		$smarty->assign('MODULE_PATH', TYPO3_MOD_PATH); // path to typo3, needed for edit article (form: /a/b/c/typo3/)

		
		// pagezones are render by a separate smarty templae - because 2 versions (pagezone_page or article) can be rendered
		$smarty_pz = $this->getPagezoneSmartyObject();
		$smarty_pz->assign('DEBUG_OUTPUT', DEBUG_OUTPUT);
		$smarty_pz->assign('ADMIN', $GLOBALS['BE_USER']->isAdmin());
		$pagezone = array();
		for ($i = 0; $i < sizeof($extra_data); $i++) {
			$smarty_pz->assign('DATA', $data[$i]); // so pagezone uid is available
			if ($data[$i]['pagezone_type']->getAttribute('is_article') == 0) {
				if (sizeof($extra_data[$i]) > 0) {
					// render pagezone table only if extras are available 
					$smarty_pz->assign('EXTRA_DATA', $extra_data[$i]);
					$pagezone[$i] = $smarty_pz->fetch('mod3_pagezone_page.tmpl');
				} else {
					$pagezone[$i] = false; // message "no extra so far" will be displayed in mod3.tmpl
				}
			} else {
				$tmp = $this->processExtraDataForExtraInArticle($extra_data[$i]);
				$smarty_pz->assign('EXTRA_DATA', $tmp);
				if ($tmp == false) {
					$pagezone[$i] = false; // indicates "no extra so far" message
				} else { 
					$pagezone[$i] = $smarty_pz->fetch('mod3_pagezone_article.tmpl'); // whole pagezone
				} 
			}
		}
		
		$smarty->assign('PAGEZONE', $pagezone);
		
		// admins can see a little more ...
		$smarty->assign('ADMIN', $GLOBALS['BE_USER']->isAdmin());
		
		return $smarty->fetch('mod3.tmpl');
	}


private function getPagezoneSmartyObject() {
	global $LANG;

	$label['extra'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_extra', false);
	$label['show'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_show', false);
	$label['pass_down'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_pass_down', false);
	$label['inherits_from'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_inherits_from', false);
	$label['commands'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_commands', false);
	$label['extra_delete_confirm'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:message_delete_confirm', false);
	$label['paragraph'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_paragraph', false);
	$label['notes'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_notes', false);
	$label['templateset'] = $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_templateset', false);

	$smarty_pz = new tx_newspaper_Smarty();
	$smarty_pz->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod3/'));

	$smarty_pz->assign('LABEL', $label);

	$smarty_pz->assign('SAVE_ICON', tx_newspaper_BE::renderIcon('gfx/savedok.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_save_extra', false)));
	$smarty_pz->assign('UNDO_ICON', tx_newspaper_BE::renderIcon('gfx/undo.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_undo_extra', false)));

	$smarty_pz->assign('HIDE_ICON', tx_newspaper_BE::renderIcon('gfx/button_hide.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_hide', false)));
	$smarty_pz->assign('UNHIDE_ICON', tx_newspaper_BE::renderIcon('gfx/button_unhide.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_unhide', false)));
	$smarty_pz->assign('EDIT_ICON', tx_newspaper_BE::renderIcon('gfx/edit2.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_edit_extra', false)));
	$smarty_pz->assign('MOVE_UP_ICON', tx_newspaper_BE::renderIcon('gfx/button_up.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_move_up', false)));
	$smarty_pz->assign('MOVE_DOWN_ICON', tx_newspaper_BE::renderIcon('gfx/button_down.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_move_down', false)));
	$smarty_pz->assign('NEW_BELOW_ICON', tx_newspaper_BE::renderIcon('gfx/new_record.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_new_below', false)));
	$smarty_pz->assign('DELETE_ICON', tx_newspaper_BE::renderIcon('gfx/garbage.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_delete', false)));
//		$smarty_pz->assign('REMOVE_ICON', tx_newspaper_BE::renderIcon('gfx/selectnone.gif', '', $LANG->sL('LLL:EXT:newspaper/mod3/locallang.xml:label_delete', false)));
	$smarty_pz->assign('EMPTY_ICON', '<img src="clear.gif" width=16" height="16" alt="" />');

	return $smarty_pz;
}

private function processExtraDataForExtraInArticle($extra_data) {

	if (sizeof($extra_data) == 0) {
		// message "no extra so far" shound be rendered in smarty template
		return false;
	}
		
	// prepare bg color
	$para = false; // init with false, so first paragraph can be identified
	$bg = 1;
	for ($i = 0; $i < sizeof($extra_data); $i++) {
		if (intval($extra_data[$i]['paragraph']) !== $para) {
			$para = intval($extra_data[$i]['paragraph']); // store new paragraph
			$bg = ($bg == 1)? 0 : 1; // switch bg type
		}
		$extra_data[$i]['bg_color_type'] = $bg;
	}
	return $extra_data;

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