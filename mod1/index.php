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

	private $prefixId = 'tx_newspaper_mod1';
	private $input = array(); // stores get/post data

	const FILTER_STEP = 20;


	private function parseParam($param, $length=4) {
//t3lib_div::devlog('newspaper parseparam', 'newspaper', 0, $param);
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
		require_once(t3lib_extMgm::extPath('newspaper'). 'classes/private/class.tx_newspaper_be.php');
		$param = $this->splitParams();
//t3lib_div::devlog('papt param', 'newspaper', 0, array('param' => $param));

		// get section
		$s = new tx_newspaper_Section(intval($param['section']));

		// activate page
		$s->activatePage(new tx_newspaper_PageType(intval($param['pagetype'])));

		// re-render backend
		$PA['row']['uid'] = $param['section']; // simulate call from be
		$PA['AJAX_CALL'] = true;
		$tmp['html'] = tx_newspaper_BE::renderPagePageZoneList($PA);
		echo json_encode($tmp);
		exit();
	}

	function processDeletePage() {
		require_once(t3lib_extMgm::extPath('newspaper'). 'classes/private/class.tx_newspaper_be.php');
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
		require_once(t3lib_extMgm::extPath('newspaper'). 'classes/private/class.tx_newspaper_be.php');
		$param = $this->splitParams();
//t3lib_div::devlog('papzt param', 'newspaper', 0, 'param' => $param);

		//get page
		$p = new tx_newspaper_page(intval($param['page']));

		// activate pagezone
		$p->activatePagezone(new tx_newspaper_PagezoneType(intval($param['pagezonetype'])));

		// re-render backend
		$PA['row']['uid'] = $param['section']; // simulate call from be
		$PA['AJAX_CALL'] = true;
		$tmp['html'] = tx_newspaper_BE::renderPagePageZoneList($PA);
		echo json_encode($tmp);
		exit();
	}
	function processDeletePageZone() {
		require_once(t3lib_extMgm::extPath('newspaper'). 'classes/private/class.tx_newspaper_be.php');
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
		// control tags can only be created with the list module - so this function isn't used for control tags ... and it CAN'T be used for control tags
        if(isset($_REQUEST['tag'])) {
            $tagValue = $_REQUEST['tag'];
            $type = $this->getTagTypeFromRequest();
            if ($type === false) {
            	return; // shouldn't be called, better safe than sorry
            }
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
        $where = 'tag_type = '.$tagType;
        if(isset($_REQUEST['ctrlCat']) && $tagType == tx_newspaper_Tag::getControlTagType()) {
            $where .= ' AND ctrltag_cat = '.$_REQUEST['ctrlCat'];
        }
        $results = tx_newspaper::selectRows('uid, tag', 'tx_newspaper_tag', $where, '', 'tag');
        $tags = array();
        foreach($results as $result) {
            $tags[$result['uid']] = $result['tag'];
        }
        exit(json_encode($tags));
    }

    private function getTagTypeFromRequest() {
        $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
        if ($type == 'tags') {
            return tx_newspaper_tag::getContentTagType();
        } else if (stristr($type,'tags_ctrl')) {
            return tx_newspaper_tag::getControlTagType();
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

					// get ll labels
					$localLang = t3lib_div::readLLfile('typo3conf/ext/newspaper/mod6/locallang.xml', $GLOBALS['LANG']->lang);
					$this->localLang = $localLang[$GLOBALS['LANG']->lang];

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

					// Access check!
					// \todo: better access check
					$access = $BE_USER->user['uid']? true : false; // \todo: better check needed
					if (!$access) {
						die('No access'); // \todo localization
					}


					// read data
					$this->input = t3lib_div::GParrayMerged($this->prefixId);
//t3lib_div::devlog('mod1 main', 'newspaper', 0, array('this->input' => $this->input));

					// Basic AJAX handling
					$this->processAjaxRequest();

					// newspaper element browser handling
					$this->processNewspaperElementBrowser();
					$this->processNewspaperElementBrowserAjax();


//t3lib_div::devlog('ajax $_REQUEST', 'newspaper', 0, $_REQUEST);
					if (!isset($_REQUEST['param']))
						return false; // no valid call without params possible


					// workflow log display
					if ($_REQUEST['param'] == 'workflowlog') {
						$this->processWorkflowLog();
					}


					if (isset($_REQUEST['extra_modalbox'])) {
						$this->processExtraForm(); // AJAX call for Extra form
					}

					if (isset($_REQUEST['extra_toggle_visibility'])) {
						$this->processExtraToggleVisibility(); // AJAX call for toggle visibility
					}

					if (isset($_REQUEST['extra_delete'])) {
						$this->processExtraDelete(); // AJAX call
					}

                    //Tag handling
                    if ($_REQUEST['param'] == 'tag-suggest') {
                        $this->processTagSuggest(); //AJAX call
                    }

                    if ($_REQUEST['param'] == 'tag-insert') {
                        $this->processTagInsert(); //AJAX call
                    }

                    if ($_REQUEST['param'] == 'tag-getall') {
                        $this->processTagGetAll(); //AJAX call
                    }



// new stuff for section
					if (isset($_REQUEST['activate_page_type'])) {
						$this->processActivatePageType(); // AJAX call
					}
					if (isset($_REQUEST['activate_pagezone_type'])) {
						$this->processActivatePageZoneType(); // AJAX call
					}
					if (isset($_REQUEST['delete_page'])) {
						$this->processDeletePage(); // AJAX call
					}
					if (isset($_REQUEST['delete_pagezone'])) {
						$this->processDeletePageZone(); // AJAX call
					}


					return false; // if processing was successful, the script died after the AJAX request was answered; if params weren't valid return false anyway

				}

		// renders newspaper element browsers
		// controller: eb (= element browser)
		// types: e=extra, al=article list
		private function processNewspaperElementBrowser() {

			if (
				!$this->input['controller'] ||
				strtolower($this->input['controller']) != 'eb' ||
				!$this->input['type']
			) {
				return; // no newspaper element browser this time
			}
//t3lib_div::devlog('eb', 'newspaper', 0, array('input' => $this->input));
			// prepare smarty object
			$smarty = new tx_newspaper_Smarty();
			$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod1/res/eb'));

			// get ll labels
			$tmp = t3lib_div::readLLfile('typo3conf/ext/newspaper/mod1/locallang.xml', $GLOBALS['LANG']->lang);
			$smarty->assign('LL', $tmp[$GLOBALS['LANG']->lang]); // localization

			$smarty->assign('INPUT', $this->input);

			switch(strtolower($this->input['type'])) {

				// create a new extra from within newspaper's extra element browser
				case 'e':
					// check if newOnly parameter is set. create extra right away if so
					if ($this->input['newOnly'] == 1 && $this->input['extraClassPreselect']) {
						$extra = $this->createNewConcreteExtra($this->input['extraClassPreselect']);
						$returnUrl = tx_newspaper::encodeUrlBasic(
							tx_newspaper::getBasePath() .
							'typo3conf/ext/newspaper/mod1/res/eb/js/closeNewExtraManageDossiers.html?' .
							'tx_newspaper_mod1[newExtraClass]=' . $this->input['extraClassPreselect'] .
							'&tx_newspaper_mod1[newExtraUid]=' . $extra->getUid() .
							'&tx_newspaper_mod1[table]=' . $this->input['table'] .
							'&tx_newspaper_mod1[field]=' . $this->input['field'] .
							'&tx_newspaper_mod1[uid]=' . $this->input['uid'] .
							'&tx_newspaper_mod1[abstractUid]=' . $extra->getExtraUid()
						);
						$url = tx_newspaper::getBasePath() . "typo3/alt_doc.php?returnUrl=" . $returnUrl . "&edit[" . $extra->getTable() . "][" . $extra->getUid() . "]=edit&tx_newspaper_mod1[newExtraInElementBrowser]=1";
						header('Location: ' . $url);
					}

					// element browser for extras
					$smarty->assign('formExtras', tx_newspaper_extra::getRegisteredExtras());
					$filter = $smarty->fetch('extra_filter.tmpl');
				break;

				// Edit an extra from a manipulated Typo3 element (see tx_newspaper_be::checkAddEditInRelationField())
				case 'editextra':
					$p = strrpos($this->input['abstractExtra'], '_');
					if ($p === false || strtolower(substr($this->input['abstractExtra'], 0, $p)) != 'tx_newspaper_extra' ) {
						die('Unknown extra: ' . htmlspecialchars($this->input['abstractExtra']));
					}
					$uid = intval(substr($this->input['abstractExtra'], $p+1)); // extract uid for abstract extra
					$extra = tx_newspaper_Extra_Factory::getInstance()->create($uid);

					// tx_newspaper_close_option=1 DO show close buttons
					$url = tx_newspaper::getBasePath() . "typo3/alt_doc.php?returnUrl=close.html&edit[" . $extra->getTable() . "][" . $extra->getUid() . "]=edit&tx_newspaper_close_option=1";
					header('Location: ' . $url);

				break;
//				case 'al':
//					// element browser for article lists
//					$filter = $smarty->fetch('filter_articlelist.tmpl');
//				break;
				default:
					t3lib_div::devlog('processNpElementBrowser() - unknown type', 'newspaper', 3, array('this->input' => $this->input));
					return; // no type given, nothing to do
			}

			if (!isset($this->input['allowMultipleSelection'])) {
				$this->input['allowMultipleSelection'] = true;
			} else {
				$this->input['allowMultipleSelection'] = (bool) $this->input['allowMultipleSelection'];
			}

			// add choseRecord js according to jsType setting
			switch($this->input['jsType']) {
                //@todo: use $this->isDossierWizard() ...
				case 'manageDossiers':
					$smarty->assign('CHOSERECORD', file_get_contents('res/eb/js/choseRecord_manageDossiers.js'));
				break;
				case 'Typo3':
					$smarty->assign('CHOSERECORD', file_get_contents('res/eb/js/choseRecord_Typo3.js'));
				break;
				default:
					if (strtolower($this->input['type'] != 'editextra')) {
						// write error message for types that need a javascript type
						t3lib_div::devlog('mod 1 - Element browser - unknown jsType', 'newspaper', 3, array('input' => $this->input));
					}
			}

			$smarty->assign('FILTER', $filter);

			$eb = $smarty->fetch('browser.tmpl');

			die($eb);

		}


		/// Creates a concrete extra (in Typo3 database)
		/**
		 * @param $extraClass Class of Extra to be created
		 * @return Extra
		 * @todo where to move this function to???
		 */
		private function createNewConcreteExtra($extraClass) {
			$extra = new $extraClass();
			$extra->setAttribute('crdate', time());
			$extra->setAttribute('tstamp', time());
			$extra->setAttribute('cruser_id', $GLOBALS['BE_USER']->user['uid']);
			$this->setNewExtraDefaultValues($extra);
			$extra->store();
			return $extra;
		}

		/**
		 * Set default values to an Extra
		 * Default values are set in TSConfig:
		 * newspaper.be.dossierWizard.default.[Extra].[field] = [value]
		 * @param Extra object
		 * @return void
		 * @todo Don't execute directly. Just offer a hook here ...
		 */
		private function setNewExtraDefaultValues(tx_newspaper_Extra &$extra) {
			if ($tsc = tx_newspaper::getTSConfig()) { // get tsconfig
				if ($this->isDossierWizard() && $currentTsc = $tsc['newspaper.']['be.']['dossierWizard.']['default.']) {
					if (array_key_exists(
						strtolower(get_class($extra)) . '.', // Extra class name plus "."
						$currentTsc // tsconfig
					)) {
						// set defaults ...
						foreach($tsc['newspaper.']['be.']['dossierWizard.']['default.'][strtolower(get_class($extra)) . '.'] as $field => $defaultValue) {
							$extra->setAttribute($field, $defaultValue);
						}
					}
				}
            }
		}


		/// Process AJAX requests, if any. Terminates with die() if AJAX request.
		private function processAjaxRequest() {
			switch($this->input['ajaxController']) {
				case 'fixPubDate':
					// update publish date for published articles without publish date
					die($this->fixPublishDate());
				break;
				case 'fixDefaultTemplateSet':
					// set all template_set fields to "default"
					die($this->fixDefaultTemplateSet());
				break;

			}
		}


		// processes newspaper element browsers ajax requests
		// ajaxcontroller: eb (= element browser)
		// types: see processNewspaperElementBrowser()
		private function processNewspaperElementBrowserAjax() {
//t3lib_div::devlog('processNewspaperElementBrowserAjax()', 'newspaper', 0, array('input' => $this->input));

			// Check if AJAX request should perform a simple operation and return data using JSON
			switch($this->input['ajaxController']) {
				case 'createNewExtra':
					$extra = $this->createNewConcreteExtra($this->input['extraClass']);
					die(json_encode(array('uid' => $extra->getUid(), 'abstractUid' => $extra->getExtraUid()))); // return uids
				break;
				case 'cloneAbstractExtra':
//tx_newspaper::devlog('cloneAbstractExtra', array('input' => $this->input));
					$extra = tx_newspaper_Extra_Factory::getInstance()->create(intval($this->input['extraUid']));
					$clonedExtra = clone $extra;
        			$clonedExtra->setOriginUid(0);
        			$clonedExtra->store();
        			die(json_encode(array(
        				'uid' => $clonedExtra->getExtraUid(),
        				'label' => htmlspecialchars($clonedExtra->getAbstractExtraLabel())
        			)));
				break;
			}



			// Check if AJAX request is to be processed here
			if (
				!$this->input['ajaxcontroller'] ||
				strtolower($this->input['ajaxcontroller']) != 'eb' ||
				!$this->input['type']
			) {
				return; // no newspaper element browser ajax request this time
			}



			// AJAX request returns Smarty based HTML code

			// prepare smarty object
			$smarty = new tx_newspaper_Smarty();
			$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod1/res/eb'));

			// get ll labels
			$tmp = t3lib_div::readLLfile('typo3conf/ext/newspaper/mod1/locallang.xml', $GLOBALS['LANG']->lang);
			$smarty->assign('LL', $tmp[$GLOBALS['LANG']->lang]); // localization

			$smarty->assign('INPUT', $this->input);

			// get icons
			$smarty->assign('ICON', $this->getIcons());

			switch(strtolower($this->input['type'])) {
				case 'e':
					// extras

					if (!$this->input['extra']) {
						$this->dieWithErrorMessage($tmp[$GLOBALS['LANG']->lang]['errorNoExtraChosen']);
					}
					$total = $this->countExtras();
					$smarty->assign('extras', $this->filterExtra());
					$results = $smarty->fetch('extra_result.tmpl');
				break;
//				case 'al':
//					// article lists
// \todo ...
//				break;
				default:
					t3lib_div::devlog('processNpElementBrowserAjax() - unknown type', 'newspaper', 3, array('this->input' => $this->input));
					return; // no type given, nothing to do
			}
			$browse = $this->renderBrowseSequence($total);
			die($browse . $results);
		}

        /**
         * Check if newspaper element browser is opened in dossier wizard
         * @return true if yes, else false
         */
        private function isDossierWizard() {
            return $this->input['jsType'] == 'manageDossiers';
        }

		private function getIcons() {
			return array(
				'add' => tx_newspaper_BE::renderIcon('gfx/add.gif', '', $GLOBALS['LANG']->sL('LLL:EXT:newspaper/mod1/locallang.xml:labelAdd', false)),
				'addClose' => tx_newspaper_BE::renderIcon('gfx/button_right.gif', '', $GLOBALS['LANG']->sL('LLL:EXT:newspaper/mod1/locallang.xml:labelAddClose', false)),
			);
		}

		private function renderBrowseSequence($total) {

			$ll = t3lib_div::readLLfile('typo3conf/ext/newspaper/mod1/locallang.xml', $GLOBALS['LANG']->lang);
			$localLang = $ll[$GLOBALS['LANG']->lang];

			$limit = intval($this->input['step'])? intval($this->input['step']) : self::FILTER_STEP;
			$start = intval($this->input['pointer']); // defaults to 0

			$total = intval($total);


			// prepare smarty object
			$smarty = new tx_newspaper_Smarty();
			$smarty->setTemplateSearchPath(array('typo3conf/ext/newspaper/mod1/res/eb'));

			$smarty->assign('BROWSE_PREV', $start-1);
			$smarty->assign('BROWSE_NEXT', ((($start * $limit) + $limit + 1) <= $total )? $start+1 : '');

			$smarty->assign('HIT_FIRST', ($start * $limit) + 1);
			$smarty->assign('HIT_LAST', min($total, ($start * $limit) + $limit));

			$smarty->assign('HIT_COUNT', $total);

			$smarty->assign('LL', $localLang);

			$browse = $smarty->fetch('browse.tmpl');

			return $browse;

		}

		/// \return Extra records (according to filter settings)
		private function filterExtra() {

			$search_term = $this->input['text'];

			$step = intval($this->input['step'])? intval($this->input['step']) : self::FILTER_STEP;
			$start = intval($this->input['pointer']) * $step; // defaults to 0

			$hidden = (isset($this->input['hidden']))? intval($this->input['hidden']) : false;

			$e = new $this->input['extra']();
			$extras = $e->getSearchResults($search_term, $start, $step, $hidden);

			return $extras;

			$rows = tx_newspaper::selectRows(
				'*',
				htmlspecialchars($this->input['extra']),
				'1' . tx_newspaper::enableFields($this->input['extra']),
				'',
				'tstamp DESC',
				$start . ',' . $step
			);
			return $rows;
		}

		/// \return total number of Extras matching the filter settings
		private function countExtras() {
			$search_term = $this->input['text'];
			$hidden = (isset($this->input['hidden']))? intval($this->input['hidden']) : false;

			$e = new $this->input['extra']();
			return $e->countSearchResults($search_term, $hidden);
		}


		private function dieWithErrorMessage($message) {
			die('<div class="errorMessage">' . $message . '</div>');
		}


		/// Fixes missing publish date for published articles with publish date missing
		/** 1. Try to get publish date from workflow log
		 *  2. Use article's tstamp
		 *  \todo: move to mod6, but some weird Typo3 path problem there ...
		 */
		private function fixPublishDate() {
			$rows = tx_newspaper::selectRows('*', 'tx_newspaper_article', 'deleted=0 AND hidden=0 AND publish_date=0');
			$count = array();
			foreach($rows as $row) {
				$a = new tx_newspaper_article($row['uid']);
				// trying to get publish date from workflow log
				$wf = tx_newspaper::selectZeroOrOneRows(
					'tstamp',
					'tx_newspaper_log',
					'table_name="tx_newspaper_article" AND table_uid=' . $a->getUid() . ' AND operation=2', // operation 2 -> publish article
					'',
					'tstamp DESC',
					1
				);
				if (isset($wf['tstamp'])) {
//t3lib_div::devlog('fixPublishDate()', 'newspaper', 0, array('wf_record' => $wf));
					// use timestamp the article was published last time
					$a->setAttribute('publish_date', $wf['tstamp']);
					$count['wf']++;
				} else {
					// use article's timestamp
					if ($a->getAttribute('tstamp')) {
						$a->setAttribute('publish_date', $a->getAttribute('tstamp'));
						$count['tstamp']++;
					} else {
						$count['wontfix']++;
					}
				}
				$a->store();
			}

			$msg = 'Used timestamp from workflow log (best method): ' . $count['wf'];
			$msg .= ', used article\'s timestamp: ' . $count['tstamp'];
			$msg .= ', problem couldn\'t be fixed: ' . $count['wontfix'];

			return $msg;
		}


		/// Sets all template set fields to "default"
		private function fixDefaultTemplateSet() {

			// get tables to check
			$templateSetTables = tx_newspaper_be::getTemplateSetTables();

			$msg = '';
			foreach ($templateSetTables as $table) {
				$count = tx_newspaper::updateRows(
					$table,
					'(template_set<>"default" OR ISNULL(template_set)) AND deleted=0',
					array('template_set' => 'default')
				);
//t3lib_div::devlog('fixDefaultTemplateSet()', 'newspaper', 0, array('q' => tx_newspaper::$query));
				if ($count) {
					$msg .= '<p><strong>' . $table . '</strong>: <i>' . $count . '</i> records fixed';
				}
			}
			if (!$msg) {
				return '<p>Nothing to do - all template set were set to "default"';
			}

			return $msg;
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