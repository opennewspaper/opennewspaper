<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Oliver Schröder <typo3@schroederbros.de>
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

///TODO: implemention still very basic


	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH . 'init.php');
require_once($BACK_PATH . 'template.php');

$LANG->includeLLFile('EXT:newspaper/mod1/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]



/**
 * Module 'AJAX' for the 'newspaper' extension.
 *
 * @author	Oliver Schröder <typo3@schroederbros.de>
 * @package	TYPO3
 * @subpackage	tx_newspaper
 */
class  tx_newspaper_module1 extends t3lib_SCbase {
				var $pageinfo;

	private function parseParam($param, $length=4) {
t3lib_div::devlog('newspaper parseparam', 'newspaper', 0, $param);
		$p = explode('|', $param);
		if (sizeof($p) != $length)
			return false;
		return $p;
// TODO some more param checks needed (security!!!)
	}


	private function processExtraForm() {
		if (!$param = $this->parseParam($_REQUEST['param']))
			return false;
			
		// prepare JSON response data
		$tmp = array();
		$tmp['id'] = $param[0] . '[' . $param[1] . ']' . $param[2] . '[' . $param[3] . ']';
		$tmp['extra_param'] = 'edit[' . $param[0] . '][' . $param[1] . ']=edit';
		$tmp['extra_close_param'] = $param[0] . '[' . $param[1] . ']' . $param[2] . '[' . $param[3] . ']';
#t3lib_div::devlog('/mod1/index.php extra form ajax json', 'newspaper', 0, $tmp);
#header("Content-Type: application/json");
		echo json_encode($tmp);
		exit();
	}


	function processExtraToggleVisibility() {
		if (!$param = $this->parseParam($_REQUEST['param'], 5))
			return false;	

		// prepare JSON response data
		$tmp = array();
//TODO skinning missing
		if (strpos($param[4], 'gfx/button_unhide.gif')) { // toggle hide/unhide icon and write to db
			$tmp['img_src'] = 'sysext/t3skin/icons/gfx/button_hide.gif';
			$this->toggleVisibilityDb($param[0], $param[1], false);
		} else {
			$tmp['img_src'] = 'sysext/t3skin/icons/gfx/button_unhide.gif';
			$this->toggleVisibilityDb($param[0], $param[1], true);
		}
		$tmp['id'] = 'vis_icon_' . $param[0] . '[' . $param[1] . ']' . $param[2] . '[' . $param[3] . ']';
#t3lib_div::devlog('/mod1/index.php visibility ajax json', 'newspaper', 0, $tmp);
#header("Content-Type: application/json");
		echo json_encode($tmp);
		exit();			
	}

//TODO: move to Extra class
	function toggleVisibilityDb($table, $uid, $hidden) {
//TODO: check permissions (ajax call can be faked easily)
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, 'uid=' . $uid, array('hidden' => $hidden));
//TODO error handling missing
	}




	function processExtraDelete() {
		if (!$param = $this->parseParam($_REQUEST['param']))
			return false;	

		// prepare JSON response data
		$tmp = array();
		$this->deleteExtra($param[0], $param[1]);
		$tmp['id'] = 'list_' . $param[0] . '[' . $param[1] . ']' . $param[2] . '[' . $param[3] . ']';
#t3lib_div::devlog('/mod1/index.php delete ajax json', 'newspaper', 0, $tmp);
#header("Content-Type: application/json");
		echo json_encode($tmp);
		exit();			
	}
	
//TODO: save hook -> deleted Extra -> delete all relations
//move to Extra class
	function deleteExtra($table, $uid) {
#t3lib_div::devlog("delete Extra from $table, $uid", 'newspaper', 0);
	}









	function splitParams() {
		if (!isset($_REQUEST['param'])) return array();
	
		/// structure [test1]#|[test2]#
		$p = explode('|', $_REQUEST['param']); // split given params

		$param = array();
		for ($i = 0; $i < sizeof($p); $i++) {
			/// structure: [test1]#
			$row = explode(']', $p[$i]);
			$param[strtolower(substr($row[0], 1))] = $row[1];
		}
#t3lib_div::devlog('param', 'newspaper', 0, $param);
		return $param;
	}

//new stuff for section
	function processListPageTypes() {
		require_once(t3lib_extMgm::extPath('newspaper'). 'classes/class.tx_newspaper_be.php');	
		$param = $this->splitParams();
		$PA['row']['uid'] = $param['section']; // simulate call from be
		$PA['AJAX_CALL'] = true; 
		$tmp['html'] = tx_newspaper_BE::renderPageList($PA);
		echo json_encode($tmp);
		exit();
	}

	function processActivatePageType() {
		require_once(t3lib_extMgm::extPath('newspaper'). 'classes/class.tx_newspaper_be.php');	
		$param = $this->splitParams();
#t3lib_div::devlog('papt param', 'newspaper', 0, $param);
		$p = new tx_newspaper_Page(
			new tx_newspaper_Section(intval($param['section'])),
			new tx_newspaper_PageType(intval($param['pagetype']))
		);
		$dummy = $p->store(); 
#t3lib_div::devlog('papt after store', 'newspaper', 0, $dummy);
		$PA['row']['uid'] = $param['section']; // set section id to show
		$PA['AJAX_CALL'] = true; 
		$tmp['html'] = tx_newspaper_BE::renderPageList($PA);
		echo json_encode($tmp);
		exit();
	}
	function processEditPageType() {
		require_once(t3lib_extMgm::extPath('newspaper'). 'classes/class.tx_newspaper_be.php');
		$param = $this->splitParams();
		$PA['row']['uid'] = $param['page']; // simulate call from be
		$PA['AJAX_CALL'] = true;
		$PA['SECTION'] = $param['section']; 
		$tmp['html'] = tx_newspaper_BE::renderPageZoneList($PA);
		echo json_encode($tmp);
		exit();
	}

	function processActivatePageZoneType() {
		require_once(t3lib_extMgm::extPath('newspaper'). 'classes/class.tx_newspaper_be.php');	
		$param = $this->splitParams();
#t3lib_div::devlog('papzt param', 'newspaper', 0, $param);
		tx_newspaper_PageZone_Factory::getInstance()->createNew(
			new tx_newspaper_Page(intval($param['page'])), 
			new tx_newspaper_PageZoneType(intval($param['pagezonetype']))
		);
		
		$PA['row']['uid'] = $param['page']; // simulate call from be
		$PA['AJAX_CALL'] = true;
		$PA['SECTION'] = $param['section']; 
		$tmp['html'] = tx_newspaper_BE::renderPageZoneList($PA);
		echo json_encode($tmp);
		exit();
	}


	function processDeletePageZone() {
		require_once(t3lib_extMgm::extPath('newspaper'). 'classes/class.tx_newspaper_be.php');	
		$param = $this->splitParams();
#t3lib_div::devlog('pdpz param', 'newspaper', 0, $param);
		tx_newspaper::deleteRows('tx_newspaper_pagezone', array(intval($param['pagezone'])));
		
		$PA['row']['uid'] = $param['page']; // simulate call from be
		$PA['AJAX_CALL'] = true;
		$PA['SECTION'] = $param['section']; 
		$tmp['html'] = tx_newspaper_BE::renderPageZoneList($PA);
		echo json_encode($tmp);
		exit();
	}







				/**
				 * Initializes the Module
				 * @return	void
				 */
				function init()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

					parent::init();

					/*
					if (t3lib_div::_GP('clear_all_cache'))	{
						$this->include_once[] = PATH_t3lib.'class.t3lib_tcemain.php';
					}
					*/
				}

				/**
				 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
				 *
				 * @return	void
				 */
				function menuConfig()	{
/*
					global $LANG;
					$this->MOD_MENU = Array (
						'function' => Array (
							'1' => $LANG->getLL('function1'),
							'2' => $LANG->getLL('function2'),
							'3' => $LANG->getLL('function3'),
						)
					);
					parent::menuConfig();
*/
				}


				/**
				 * Main function of the module. Write the content to $this->content
				 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
				 *
				 * @return	[type]		...
				 */
				function main()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;


// TODO check permissions
t3lib_div::devlog('ajax $_REQUEST', 'newspaper', 0, $_REQUEST);
					if (!isset($_REQUEST['param']))
						return false; // no valid call without params possible
					
					
					if (isset($_REQUEST['extra_modalbox']) || isset($_REQUEST['extra_iframe']))
						$this->processExtraForm(); // AJAX call for Extra form (modalbox or iframe)
					
					if (isset($_REQUEST['extra_toggle_visibility']))
						$this->processExtraToggleVisibility(); // AJAX call for toggle visibility

					if (isset($_REQUEST['extra_delete']))
						$this->processExtraDelete(); // AJAX call



// new stuff for section
					if (isset($_REQUEST['list_page_types']))
						$this->processListPageTypes(); // AJAX call
					if (isset($_REQUEST['activate_page_type']))
						$this->processActivatePageType(); // AJAX call
					if (isset($_REQUEST['edit_page_type']))
						$this->processEditPageType(); // AJAX call
					if (isset($_REQUEST['activate_pagezone_type']))
						$this->processActivatePageZoneType(); // AJAX call
					if (isset($_REQUEST['delete_page']))
						$this->processDeletePage(); // AJAX call
					if (isset($_REQUEST['delete_pagezone']))
						$this->processDeletePageZone(); // AJAX call


					
					return false; // if processing was successful, the script died after the AJAX request was answered; if param weren't valid return false anyway

/*
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
*/
				}

				/**
				 * Prints out the module HTML
				 *
				 * @return	void
				 */
				function printContent()	{
/*
					$this->content.=$this->doc->endPage();
					echo $this->content;
*/
								}

				/**
				 * Generates the module content
				 *
				 * @return	void
				 */
				function moduleContent()	{
/*
					switch((string)$this->MOD_SETTINGS['function'])	{
						case 1:
							$content='<div align="center"><strong>Hello World!</strong></div><br />
								The "Kickstarter" has made this module automatically, it contains a default framework for a backend module but apart from that it does nothing useful until you open the script '.substr(t3lib_extMgm::extPath('newspaper'),strlen(PATH_site)).$pathSuffix.'index.php and edit it!
								<hr />
								<br />This is the GET/POST vars sent to the script:<br />'.
								'GET:'.t3lib_div::view_array($_GET).'<br />'.
								'POST:'.t3lib_div::view_array($_POST).'<br />'.
								'';
							$this->content.=$this->doc->section('Message #1:',$content,0,1);
						break;
						case 2:
							$content='<div align=center><strong>Menu item #2...</strong></div>';
							$this->content.=$this->doc->section('Message #2:',$content,0,1);
						break;
						case 3:
							$content='<div align=center><strong>Menu item #3...</strong></div>';
							$this->content.=$this->doc->section('Message #3:',$content,0,1);
						break;
					}
*/
				}


}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/newspaper/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_newspaper_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>