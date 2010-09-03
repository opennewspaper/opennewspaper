<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Oliver Schr�der <typo3@schroederbros.de>
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

/// \todo: _request ->_gp

	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH . 'init.php');
require_once($BACK_PATH . 'template.php');

$LANG->includeLLFile('EXT:newspaper/mod1/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]







/// \todo: CLEANUP NEEDED - many functions deprecated!!!







/**
 * Module 'AJAX' for the 'newspaper' extension.
 *
 * @author	Oliver Schr�der <typo3@schroederbros.de>
 * @package	TYPO3
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







//new stuff for section, this is in use
	function processActivatePageType() {
		require_once(t3lib_extMgm::extPath('newspaper'). 'classes/class.tx_newspaper_be.php');	
		$param = $this->splitParams();
//t3lib_div::devlog('papt param', 'newspaper', 0, 'param' => $param);

		// check if this pagetype REALLY isn't active
		$s = new tx_newspaper_Section(intval($param['section']));
		$ap = $s->getActivePages();
		foreach($ap as $active_page) {
			if ($active_page->getPageType()->getUid() == intval($param['pagetype'])) {
				// for some reason this page type has been activated already
				t3lib_div::devlog('Page type #' . intval($param['pagetype']) . ' already active for section #' . $s->getUid(), 'newspaper', 3);
				return;
			}
		}

		$p = new tx_newspaper_Page(
			$s,
			new tx_newspaper_PageType(intval($param['pagetype']))
		);
		$p->store();
		$p->setAttribute('crdate', time());
		$p->setAttribute('tstamp', time());
		$p->setAttribute('cruser_id', $GLOBALS['BE_USER']->user['uid']);
		$dummy = $p->store(); 
//t3lib_div::devlog('papt after store', 'newspaper', 0, $dummy);
		$PA['row']['uid'] = $param['section']; // simulate call from be
		$PA['AJAX_CALL'] = true; 
		$tmp['html'] = tx_newspaper_BE::renderPagePageZoneList($PA);
		echo json_encode($tmp);
		exit();
	}
	
	function processDeletePage() {
		require_once(t3lib_extMgm::extPath('newspaper'). 'classes/class.tx_newspaper_be.php');	
		$param = $this->splitParams();
//t3lib_div::devlog('papt param', 'newspaper', 0, 'param' => $param);

		// delete page and subsequent abstract and concrete pagezones
		$p = new tx_newspaper_page(intval($param['page']));
		$p->delete();
		
		$PA['row']['uid'] = $param['section']; // simulate call from be
		$PA['AJAX_CALL'] = true; 
		$tmp['html'] = tx_newspaper_BE::renderPagePageZoneList($PA);
		echo json_encode($tmp);
		exit();
	}	
	
	function processActivatePageZoneType() {
		require_once(t3lib_extMgm::extPath('newspaper'). 'classes/class.tx_newspaper_be.php');	
		$param = $this->splitParams();
//t3lib_div::devlog('papzt param', 'newspaper', 0, 'param' => $param);
		
		// check if this pagetype REALLY isn't active
		$p = new tx_newspaper_page(intval($param['page']));
		$apz = $p->getActivePagezones();
		foreach($apz as $active_pagezone) {
			if ($active_pagezone->getPagezoneType()->getUid() == intval($param['pagezonetype'])) {
				// for some reason this pagezone type has been activated already
				t3lib_div::devlog('Pagezone type #' . intval($param['pagezonetype']) . ' already active for page #' . $p->getUid(), 'newspaper', 3);
				return;
			}
		}
		
		$pz = tx_newspaper_PageZone_Factory::getInstance()->createNew(
			$p, 
			new tx_newspaper_PageZoneType(intval($param['pagezonetype']))
		);
		$pz->setAttribute('crdate', time());
		$pz->setAttribute('tstamp', time());
		$pz->setAttribute('cruser_id', $GLOBALS['BE_USER']->user['uid']);
		$pz->store();
		
		$PA['row']['uid'] = $param['section']; // simulate call from be
		$PA['AJAX_CALL'] = true;
		$tmp['html'] = tx_newspaper_BE::renderPagePageZoneList($PA);
		echo json_encode($tmp);
		exit();
	}
	function processDeletePageZone() {
		require_once(t3lib_extMgm::extPath('newspaper'). 'classes/class.tx_newspaper_be.php');	
		$param = $this->splitParams();
//t3lib_div::devlog('pdpz param', 'newspaper', 0, $param);

		// delete abstract and concrete pagezone		
		$pz = tx_newspaper_PageZone_Factory::getInstance()->create(intval($param['pagezone']));
		$pz->delete();
		
		$PA['row']['uid'] = $param['section']; // simulate call from be
		$PA['AJAX_CALL'] = true;
		$tmp['html'] = tx_newspaper_BE::renderPagePageZoneList($PA);
		echo json_encode($tmp);
		exit();
	}





	/// workflow log display and browser
	private function processWorkflowLog() {
//t3lib_div::devLog('processWorkflowLog()', 'newspaper' , 0, array('_request' => $_REQUEST));
		$table = isset($_REQUEST['tbl']) ? $_REQUEST['tbl'] : null;
		$tableUid = isset($_REQUEST['tbl_uid']) ? $_REQUEST['tbl_uid'] : null ;
		$showAllComments = isset($_REQUEST['show_all_comments'])? $_REQUEST['show_all_comments'] : false ; // show_all_comment = true meas render LINK "show all comments"
		$ajaxCall = isset($_REQUEST['AJAX_CALL'])? true : false;
		$content = tx_newspaper_Workflow::renderBackend($table, $tableUid, $showAllComments, true);
		if($ajaxCall) {
		    echo $content;
		    die();
		}
	}


    private function processTagSuggest() {
        if(isset($_REQUEST['search'])) {
            $suggestion = tx_newspaper_Tag::getCompletions($_REQUEST['search'], 10);
//t3lib_div::devLog('getProcessTag', 'newspaper' , 0, $suggestion);
            foreach($suggestion as $i => $suggest) {
                $html = $html.'<li id="'.$i.'">'.$suggest.'</li>';
            }
            exit('<ul>'.$html.'</ul>');
        }
    }

    /**
     * @return uid of inserted tag
     */
    private function processTagInsert() {
        if(isset($_REQUEST['tag'])) {
            $tagValue = $_REQUEST['tag'];
            $type = $this->getTagTypeFromRequest();
            $tag = new tx_newspaper_Tag();
		    $tag->setAttribute('cruser_id', $GLOBALS['BE_USER']->user['uid']);
            $tag->setAttribute('tag_type', $type);
            $tag->setAttribute('tag', $tagValue);
            $uid = $tag->store();
            $result = array('uid' => $uid, 'tag' => $tagValue);
//t3lib_div::devLog('processTagInsert', 'newspaper' , 0, $result);
            exit(json_encode($result));
        }
    }

    private function processTagGetAll() {
        $tagType = $this->getTagTypeFromRequest();
        $results = tx_newspaper::selectRows('uid, tag', 'tx_newspaper_tag', 'tag_type = '.$tagType);
        $tags = array();
        foreach($results as $result) {
            $tags[$result['uid']] = $result['tag'];
        }
        exit(json_encode($tags));
    }

    private function getTagTypeFromRequest() {
        $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
        if($type === 'tags') {
            return tx_newspaper::getContentTagType();
        } else if($type === 'tags_ctrl') {
            return tx_newspaper::getControlTagType();
        } else {
            throw new tx_newspaper_Exception('unknown tag_type \''.$type.'\'');
        }
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
//t3lib_div::devlog('ajax $_REQUEST', 'newspaper', 0, $_REQUEST);
					if (!isset($_REQUEST['param']))
						return false; // no valid call without params possible
					

					// workflow log display
					if ($_REQUEST['param'] == 'workflowlog') {
						$this->processWorkflowLog();
					}

					
					if (isset($_REQUEST['extra_modalbox']) || isset($_REQUEST['extra_iframe']))
						$this->processExtraForm(); // AJAX call for Extra form (modalbox or iframe)
					
					if (isset($_REQUEST['extra_toggle_visibility']))
						$this->processExtraToggleVisibility(); // AJAX call for toggle visibility

					if (isset($_REQUEST['extra_delete']))
						$this->processExtraDelete(); // AJAX call

                    //Tag handling
                    if ($_REQUEST['param'] == 'tag-suggest')
                        $this->processTagSuggest(); //AJAX call

                    if ($_REQUEST['param'] == 'tag-insert')
                        $this->processTagInsert(); //AJAX call

                    if ($_REQUEST['param'] == 'tag-getall')
                        $this->processTagGetAll(); //AJAX call



// new stuff for section
					if (isset($_REQUEST['activate_page_type']))
						$this->processActivatePageType(); // AJAX call
					if (isset($_REQUEST['activate_pagezone_type']))
						$this->processActivatePageZoneType(); // AJAX call
					if (isset($_REQUEST['delete_page']))
						$this->processDeletePage(); // AJAX call
					if (isset($_REQUEST['delete_pagezone']))
						$this->processDeletePageZone(); // AJAX call


					/// as the module is used for ajax only so far, why not use it as the shortcut for the section list module?
//<a onclick="return jumpTo('2',this,'pages2',0);" href="#">np_section</a>
//top.goToModule('web_list');this.blur();return false;
					
					return false; // if processing was successful, the script died after the AJAX request was answered; if params weren't valid return false anyway

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

?>