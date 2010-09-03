<?php
/*
 * Created on 29.01.2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

define('NP_ACTIVE_ROLE_EDITORIAL_STAFF', 0);
define('NP_ACTIVE_ROLE_DUTY_EDITOR', 1);
define('NP_ACTIVE_ROLE_NONE', 1000);


define('NP_WORKLFOW_LOG_UNKNOWN', 0); 
define('NP_WORKLFOW_LOG_HIDE', 1);
define('NP_WORKLFOW_LOG_PUBLISH', 2);
define('NP_WORKLFOW_LOG_CHANGE_ROLE', 3);
define('NP_WORKLFOW_LOG_USERCOMMENT', 4);
define('NP_WORKLFOW_LOG_IMPORT', 5);
define('NP_WORKLFOW_LOG_ERRROR', 6);
define('NP_WORKLFOW_LOG_WARNING', 7);

define('NP_WORKFLOW_COMMENTS_PREVIEW_LIMIT', 2);

define('NP_ARTICLE_WORKFLOW_NOCLOSE', false); // if set to true the workflow buttons don't close the form (better for testing)
define('NP_SHOW_PLACE_BUTTONS', false); // \todo after pressing the place button the article gets stores, workflow_status is set to 1 AND the placement form is opened. as that "open placement form" feature isn't implemented, this const can be used to hide the buttons in the backend


 
class tx_newspaper_Workflow {   

	// render workflow storage buttons for tx_newspaper_areticle given in $row
	/** Depending on the workflow and the hidden field status various combinations of buttons can be rendered
	 *  hide if article is published, publish if article is hidden
	 *  show buttons: send to editorial staff or duty editor
	 *  and combinations of role and hidden status
	 *  \param $row article data as associative array 
	 *  \return html code with all the needed buttons
	 */
	public static function getWorkflowButtons($row) {
		global $LANG;
//t3lib_div::devlog('getWorkflowButtons()', 'newspaper', 0, array('PA[row]' => $PA['row']));
		
		$hidden = $row['hidden'];
		$workflow = intval($row['workflow_status']);
//t3lib_div::devlog('getWorkflowButtons()', 'newspaper', 0, array('workflow' => $workflow, 'hidden' => $hidden));

		// create hidden field to store workflow_status (might be modified by JS when workflow buttons are used)
		$html = '<input id="workflow_status" name="workflow_status" type="hidden" value="' . $workflow . '" />';
		$html .= '<input name="workflow_status_ORG" type="hidden" value="' . $workflow . '" />';
		
		// if hidden_status equals -1, the hidden status wasn't changed by hide/publish button
		// if hidden_status DOES NOT equal -1, the hide/publish button was pressed, so IGNORE the value of the "hidden" field
		$html .= '<input id="hidden_status" name="hidden_status" type="hidden" value="-1" />'; // init with -1

		// add javascript \todo: move to external file
		$html .= '<script language="javascript" type="text/javascript">
function changeWorkflowStatus(role, hidden_status) {
	role = parseInt(role);
	hidden_status = parseInt(hidden_status);
	if (role == ' . NP_ACTIVE_ROLE_EDITORIAL_STAFF . ' || role == ' . NP_ACTIVE_ROLE_DUTY_EDITOR . ' || role == ' . NP_ACTIVE_ROLE_NONE . ') {
		document.getElementById("workflow_status").value = role; // valid role found
	}
	document.getElementById("hidden_status").value = hidden_status;
//alert("wf: " + document.getElementById("workflow_status").value + ", h: " + document.getElementById("hidden_status").value);
	return false;
}
</script>
';

		// buttons to be displayed in article backend
		$button = array(); // init with false ...
		$button['hide'] = false;
		$button['publish'] = false; // show
		$button['check'] = false;
		$button['revise'] = false;
		$button['place'] = false;
		// hide or publish button is available for every workflow status
		if (!$hidden) {
			$button['hide'] = tx_newspaper_workflow::isFunctionalityAvailable('hide');
		} else {
			$button['publish'] = tx_newspaper_workflow::isFunctionalityAvailable('publish');
		}
		switch($workflow) {
			case NP_ACTIVE_ROLE_EDITORIAL_STAFF: 
				// active role: editor (Redakteur)
				$button['check'] = tx_newspaper_workflow::isFunctionalityAvailable('check');
				$button['place'] = tx_newspaper_workflow::isFunctionalityAvailable('place');
			break;
			case NP_ACTIVE_ROLE_DUTY_EDITOR:
				// active role: duty editor (CvD)
				$button['revise'] = tx_newspaper_workflow::isFunctionalityAvailable('revise');
				$button['place'] = tx_newspaper_workflow::isFunctionalityAvailable('place');

			break;
			case NP_ACTIVE_ROLE_NONE:
				// active role: none
				$button['check'] = tx_newspaper_workflow::isFunctionalityAvailable('check');
				$button['revise'] = tx_newspaper_workflow::isFunctionalityAvailable('revise');
				$button['place'] = tx_newspaper_workflow::isFunctionalityAvailable('place');
			break;
//	deprecated		case 2: // \todo: how to call placement form???
//				// active role: no one (the article has left the workflow)
//				$button['check'] = tx_newspaper_workflow::isFunctionalityAvailable('check');
//				$button['revise'] = tx_newspaper_workflow::isFunctionalityAvailable('revise');
//				$button['place'] = tx_newspaper_workflow::isFunctionalityAvailable('place');

			default:
				t3lib_div::devlog('getWorkflowButtons() - unknown workflow status', 'newspaper', 3, array('row' => $row, 'workflow_status' => $workflow));
		}
		$html .= self::renderWorkflowButtons($hidden, $button);
//t3lib_div::devlog('button', 'newspaper', 0, array('hidden' => $hidden, 'workflow' => $workflow, 'button' => $button, 'html' => $html));
		return $html;
	}

	/// Render all workflow buttons for articles
	/** \param $hidden
	 *  \param $button array stating (boolean) if the button for the various states should be displayed
	 */
	private static function renderWorkflowButtons($hidden, $button) {
//t3lib_div::devlog('renderWorkflowButtons', 'newspaper', 0, array('button' => $button));
		global $LANG;
		
		$content = '<span style="margin-left:20px;">&nbsp;</span>'; // left margin for first button
		
		/// hide / publish
		if (!$hidden && $button['hide']) {
			$content .= self::renderWorkflowButton(false, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_hide', false), $hidden, true);
		} elseif ($hidden && $button['publish']) {
			$content .= self::renderWorkflowButton(false, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_publish', false), $hidden, true);
		}
		
		/// check / revise / place
		if ($button['check']) {
			$content .= self::renderWorkflowButton(NP_ACTIVE_ROLE_DUTY_EDITOR, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_check', false), -1, false);
			if (!$hidden && $button['hide']) {
				$content .= self::renderWorkflowButton(NP_ACTIVE_ROLE_DUTY_EDITOR, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_check_hide', false), $hidden, true);
			} elseif ($hidden && $button['publish']) {
				$content .= self::renderWorkflowButton(NP_ACTIVE_ROLE_DUTY_EDITOR, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_check_publish', false), $hidden, true);
			}
		}
		if ($button['revise']) {
			$content .= self::renderWorkflowButton(NP_ACTIVE_ROLE_EDITORIAL_STAFF, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_revise', false), -1, false);
			if (!$hidden && $button['hide']) {
				$content .= self::renderWorkflowButton(NP_ACTIVE_ROLE_EDITORIAL_STAFF, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_revise_hide', false), $hidden, true);
			} elseif ($hidden && $button['publish']) {
				$content .= self::renderWorkflowButton(NP_ACTIVE_ROLE_EDITORIAL_STAFF, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_revise_publish', false), $hidden, true);
			}
		}
// deprecated, \todo: how to call placement form???
//		if (NP_SHOW_PLACE_BUTTONS) {
//			// hide place buttons until opening placement form feature is implemented
//			if ($button['place']) {
//				$content .= self::renderWorkflowButton(2, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_place', false), -1);
//				if (!$hidden && $button['hide'])
//					$content .= self::renderWorkflowButton(2, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_place_hide', false), $hidden);
//				elseif ($hidden && $button['publish'])
//					$content .= self::renderWorkflowButton(2, $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_place_publish', false), $hidden);
//			}
//		}
		return $content;
	}
	
	/// render a workflow button for an article
	/** \param $new_role if false, the role hasn't changes, else new role 
	 *  \param $title Title for the button
	 *  \param $hidden Specifies the hidden sdtatus of the current article
	 *  \param $changeHiddenStatus if true the hidden status changes when the button is pressed
	 *  \param $overWriteNoCloseConstValue: overwrited the const setting (NP_ARTICLE_WORKFLOW_NOCLOSE), if set to true, a save (plus whatever) button (without closing the form) is rendered
	 */
	private static function renderWorkflowButton($new_role, $title, $hidden, $changeHiddenStatus=false) {
//t3lib_div::devlog('renderWorkflowButton()', 'newspaper', 0, array('new_role' => $new_role, 'title' => $title, 'hidden' => $hidden, 'overWriteNoCloseConstValue' => $overWriteNoCloseConstValue));
		
		if (!$changeHiddenStatus) {
			$hideen= -1;
		} else {
			$hidden = intval(!$hidden); // negate first (button should toggle status); intval then, so js can handle the value
		}

		if ($new_role !== false) {
			$js = 'changeWorkflowStatus(' . intval($new_role) . ', ' . $hidden . '); ';
		} else {
			$js = 'changeWorkflowStatus(-1, ' . $hidden . '); '; 
		}

        $js .= 'return tabManagement.submitTabs(this);'; 
		
// \todo: add only if needed
		$html = $title . '<input style="margin-right:20px;" title="' . $title . '"';
		$html .= ' onclick="' . $js . '" '; // add js call 
		if (NP_ARTICLE_WORKFLOW_NOCLOSE) {
			// don't close after saving (for "just save" button or for test purposes)
			$html .= 'name="_savedok" src="sysext/t3skin/icons/gfx/savedok.gif" ';
		} else {
			// live version, save and close
			$html .= 'name="_saveandclosedok" src="sysext/t3skin/icons/gfx/saveandclosedok.gif" ';			
		}
		$html .= 'width="16" type="image" height="16" class="c-inputButton"/>';
		return $html;
	}
	
	
	




	 public static function renderBackend($table, $tableUid, $allComments=true, $showFoldLinks=false) {
        if(!$table || !$tableUid) {
            throw new tx_newspaper_Exception("Arguments table and tableUid may not be null");
        }
        $tableUid = intval($tableUid);
        if($allComments) {
            $comments = self::getComments($table, $tableUid);
        } else {
            $comments = self::getComments($table, $tableUid, NP_WORKFLOW_COMMENTS_PREVIEW_LIMIT);
        }

        $comments = self::addUsername($comments);
        return self::renderTemplate($comments, $tableUid, $allComments, $showFoldLinks);
    }
    
    /// return javascript for ajax calls
    /** This method is needed because the moderation list (mod3) shows multiple workflow log comment on one page
     *  On that page the JS should only be added once ...
     */
    public static function getJavascript() {
    	return '
		<script language="javascript">
        var tableUid = null;
        function getComments(uid, all_comments) {
        	if (all_comments == undefined || all_comments != true) {
				all_comments = 0;
			} else {
				all_comments = 1;
			}
            var path = window.location.pathname;
            document.tableUid = uid; // store for access in showMessage()

            var test = path.substring(path.lastIndexOf("/") - 5);
            if (test.substring(0, 6) == "typo3/") {
            	path = path.substring(0, path.lastIndexOf("/") - 5); // -5 -> cut of "typo3" 
            } else if (path.indexOf("typo3conf/ext/newspaper/") > 0) {
            	path = path.substring(0, path.indexOf("typo3conf/ext/newspaper/"));
            }
            new top.Ajax.Request(path + "typo3conf/ext/newspaper/mod1/index.php",
                    {   method : "GET",
                        parameters :{
                        	"param" : "workflowlog",
                        	"AJAX_CALL" : true,
                        	"show_all_comments" : all_comments, 
                        	"tbl" : "tx_newspaper_article", 
                        	"tbl_uid" : uid
                        },
                        onSuccess : showMessage,
                        onError : showError
                    } );
        }

        function showMessage(data) {
            document.getElementById("comments-" + document.tableUid).innerHTML = data.responseText;
        }

        function showError(data) {
            alert(data.responseText);
        }

    	</script>
    	';
    }
    
    public static function getComments($table, $table_uid, $limit = 0) {
        $comments = tx_newspaper::selectRows(
			"FROM_UNIXTIME(`crdate`, '%d.%m.%Y %H:%i') as created, crdate, be_user, action, comment",
			'tx_newspaper_log',
            'table_name = \''.$table.'\' AND table_uid = '.$table_uid, 
			'',
			'crdate desc', 
			($limit > 0) ? $limit : ''
		);
        return $comments;
    }

    private static function renderTemplate($comments, $tableUid, $allComments=true, $showFoldLinks=false) {
		global $LANG;
		self::addUsername($comments);
		$smarty = new tx_newspaper_Smarty();
		$smarty->assign('comments', $comments);
		$smarty->assign('tableUid', $tableUid);
		$smarty->assign('allComments', $allComments);
		$smarty->assign('showFoldLinks', $showFoldLinks);
		$smarty->assign('LABEL', array(
			'more' => $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:log_more_link', false),
			'less' => $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:log_less_link', false)
		));
		$smarty->setTemplateSearchPath(array(PATH_typo3conf . 'ext/newspaper/res/be/templates'));
		return $smarty->fetch('workflow_comment_output.tmpl');
    }

    public static function addUsername($comments) {
        $beFunc = t3lib_div::makeInstance('t3lib_BEfunc');
        foreach($comments as $i => $comment) {
            $userId = $comment['be_user'];
            $userdata= $beFunc->getUserNames('username, uid, realName', 'AND uid ='.$userId);            
            $comments[$i]['username'] = $userdata[$userId]['realName']? $userdata[$userId]['realName'] : $userdata[$userId]['username'];
        }

        return $comments;
    }
 

	/// \return role set in be_users (or false if not available)
	public static function getRole() {
		// check global object BE_USER first
		if (isset($GLOBALS['BE_USER']->user['tx_newspaper_role'])) {
			return $GLOBALS['BE_USER']->user['tx_newspaper_role'];
		}
		// check session then
		if ($_COOKIE['be_typo_user']) {	
			$row = tx_newspaper::selectOneRow(
				'tx_newspaper_role',
				'be_users',
				'uid=' . intval($_COOKIE['be_typo_user']) . ' AND deleted=0'
			);
			return $row['tx_newspaper_role'];
		}
		return false; 
	}
	
	/// \return true if be_user has newspaper role "duty editor"
	public static function isDutyEditor() {
		$role = self::getRole();
		if ($role === false) {
			return false;
		}
		return ($role == 1);
	}
	/// \return true if be_user has newspaper role "editorial staff"
	public static function isEditor() {
		$role = self::getRole();
		if ($role === false) {
			return false;
		}
		return ($role == 0);
	}
	
	
	/// Changes the newspaper role of the be_user
	/** 
	 * The role of the be_user will be set to the given new role in $new_role.
	 * The filter for the moderation list is modified to filter the list according to the new role.
	 * \param $new_role value for the new role to change to
	 * \return true if chnage was successful, else false
	 */
	public static function changeRole($new_role) {
		if (!isset($GLOBALS['BE_USER']->user)) {
			return false;
		}
		// store new role in be_user
		tx_newspaper::updateRows(
			'be_users',
			'uid=' . $GLOBALS['BE_USER']->user['uid'],
			 array('tx_newspaper_role' => intval($new_role))
		);
		$GLOBALS['BE_USER']->user['tx_newspaper_role'] = intval($new_role); // kind of a hack, but this way the new value is ready to use at once
		$storedFilter = unserialize($GLOBALS['BE_USER']->getModuleData('tx_newspaper/mod2/index.php/filter'));
		$storedFilter['role'] = intval($new_role);
		$GLOBALS['BE_USER']->pushModuleData("tx_newspaper/mod2/index.php/filter", serialize($storedFilter));
		return true;  
	}


	/// checks if a workflow feature is available for the current backend user and article workflow status
	/** \param $feature (internal) name of feature (currently: hide, publish, check, revise, place)
	 *  \return true, if feature is availa for be_user and workflow_status of article
	 */
	public static function isFunctionalityAvailable($feature) {
		$role = self::getRole();
//t3lib_div::devlog('isFunctionalityAvailable()', 'newspaper', 0, array('be_user->isAdmin()' => $GLOBALS['BE_USER']->isAdmin(), 'feature' => $feature, 'role' => $role));
		if ($GLOBALS['BE_USER']->isAdmin()) {
			return true; // admins can see all buttons
		}

		if ($role === false) {
			return false;
		}

		switch(strtolower($feature)) {
			case 'hide':
				return true;
			break;
			case 'publish':
				if ($role == NP_ACTIVE_ROLE_DUTY_EDITOR) {
					return true; // only duty editors are allowed to publish
				}
			break;
			case 'check':
				return true;
			break;
			case 'revise':
				if ($role == NP_ACTIVE_ROLE_DUTY_EDITOR) {
					return true;
				}
			break;
			case 'place':
				if ($role == NP_ACTIVE_ROLE_DUTY_EDITOR) {
					return true;
				}
			break;
		}
		
		return false;
	}


	/// \return message: what status change took place
	public static function getWorkflowStatusChangedComment($new_role, $old_role) {
		global $LANG;
		$log = $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_role_change', false);
		$log = str_replace('###OLD_ROLE###', self::getRoleTitle($old_role), $log);
		$log = str_replace('###NEW_ROLE###', self::getRoleTitle($new_role), $log);
		return $log;
//		return  . ' --> ' . self::getRoleTitle($new_role);
//		return $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_role_new', false) . ' "' . 
//			self::getRoleTitle($new_role) . '", ' . //$LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_role_' . intval($new_role), false) . '", ' . 
//			$LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_role_old', false) . '"' .
//			self::getRoleTitle($old_role) . '"'; // $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_role_' . intval($old_role), false) . '"';
	}

	public static function getRoleTitle($role) {
		global $LANG;
		switch($role) {
			case NP_ACTIVE_ROLE_EDITORIAL_STAFF:
				return $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_role_editorialstaff', false);
			case NP_ACTIVE_ROLE_DUTY_EDITOR:
				return $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_role_dutyeditor', false);
			case NP_ACTIVE_ROLE_NONE:
				return $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_role_none', false);
		}
		t3lib_div::devlog('getRoleTitle() - unknown role', 'newspaper', 3, array('role' => $role));
		return '[' . $role . ']'; // no role title available
	}
	


 	// typo3 save hook ...
 	public static function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, $that) {
 	}

	/// typo3 save hook ...
	public static function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, $that) {
		self::processAndLogWorkflow($status, $table, $id, $fieldArray);
	}


 	/// modify $fieldArray if the workflow and/ or the hidden status for an article changed
 	private static function checkIfWorkflowStatusChanged(&$fieldArray, $table, $id) {
//t3lib_div::devlog('checkIfWorkflowStatusChanged() - enter', 'newspaper', 0, array('fieldArray' => $fieldArray, 'table' => $table, 'id' => $id, '_REQUEST' => $_REQUEST));

		if (strtolower($table) != 'tx_newspaper_article') {
			return; // workflow status is currently defined for newspaper articles only
		}
		
		if (isset($_REQUEST['tx_newspaper_mod7'])) {
			// mod7 - moderation list
			$request = $_REQUEST['tx_newspaper_mod7']; // slightly different in mod7 (article placement in lists)
		} else {
			// tx_newspaper_article backend
			$request = $_REQUEST;
		}

		if (array_key_exists('hidden_status', $request) && $request['hidden_status'] != -1 && $request['hidden_status'] != $request['data'][$table][$id]['hidden']) {
			$fieldArray['hidden'] = $request['hidden_status']; // if hide/publish button was used, overwrite (if set) / set value of field "hidden" 
			if (!isset($fieldArray['tstamp'])) {
				$fieldArray['tstamp'] = time(); // modify tstamp if hidden status changes
			}
		}
		if (!array_key_exists('workflow_status', $request) || !array_key_exists('workflow_status_ORG', $request)) {
			return; // value not set, so can't decide if the status changed 
		}
		if ($request['workflow_status'] == $request['workflow_status_ORG']) {
			return; // status wasn't changed, so don't store value
		}
		$fieldArray['workflow_status'] = $request['workflow_status']; // change workflow status
		if (!isset($fieldArray['tstamp'])) {
			$fieldArray['tstamp'] = time(); // modify tstamp if workflow status changed
		}
//t3lib_div::devlog('checkIfWorkflowStatusChanged() - leave', 'newspaper', 0, array('fieldArray' => $fieldArray));
	}

	/// write to log directly
	/** \param $table name of table the log entry is associated with
	 *  \param $id id of record in $table  
	 *  \param $comment comment to log
	 *  \param $type value: see NP_WORKLFOW_LOG_... const at top of file
	 */
	public static function directLog($table, $id, $comment, $type = 0) {
		$type = intval($type);
		$current_time = time();
		tx_newspaper::insertRows('tx_newspaper_log', array(
			'pid' => 0,
			'tstamp' => $current_time,
			'crdate' => $current_time, 
			'cruser_id' => $GLOBALS['BE_USER']->user['uid'], 
			'be_user' => $GLOBALS['BE_USER']->user['uid'], // same value as cruser_id, but this field is visible in backend
			'table_name' => $table, 
			'table_uid' => $id,
			'action' => $type,
			'comment' => $comment
		));
	} 
	
	/// write log data for newspaper classes implemting the tx_newspaper_WritesLog interface
	public static function processAndLogWorkflow($status, $table, $id, &$fieldArray) {
		global $LANG;
//t3lib_div::devlog('processAndLogWorkflow()','newspaper', 0, array('table' => $table, 'id' => $id, 'fieldArray' => $fieldArray, '_request' => $_REQUEST));		
//t3lib_div::devlog('processAndLogWorkflow()','newspaper', 0, array('debug_backtrace' => debug_backtrace()));
		if (class_exists($table) && !tx_newspaper::isAbstractClass($table)) { ///<newspaper specification: table name = class name
			$np_obj = new $table();

			/// check if a newspaper record action should be logged
			if (in_array("tx_newspaper_WritesLog", class_implements($np_obj))) {

				self::checkIfWorkflowStatusChanged($fieldArray, $table, $id); // IMPORTANT: might alter $fieldArray !

//debug($GLOBALS['BE_USER']);				
				$be_user = $GLOBALS['BE_USER']->user['uid']; /// i'm not sure if this object is always available, we'll see ...
				
				/// check if auto log entry for hiding/publishing newspaper record should be written
				$current_time = time(); // make sure all log entries written in this run have the same time
				if (array_key_exists('hidden', $fieldArray)) {
					if ($table == 'tx_newspaper_article') {
						$action = $fieldArray['hidden']? $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:log_article_hidden', false) : $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:log_article_published', false);
					} else {
						$action = $fieldArray['hidden']? $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:log_record_hidden', false) : $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:log_record_published', false);
					}
//t3lib_div::devlog('processAndLogWorkflow() hidden status','newspaper', 0, array('debug_backtrace' => debug_backtrace()));
					tx_newspaper::insertRows('tx_newspaper_log', array(
						'pid' => $fieldArray['pid'],
						'tstamp' => $current_time,
						'crdate' => $current_time, 
						'cruser_id' => $be_user, 
						'be_user' => $be_user, // same value as cruser_id, but this field is visible in backend
						'table_name' => $table, 
						'table_uid' => $id,
						'action' => $fieldArray['hidden']? NP_WORKLFOW_LOG_HIDE : NP_WORKLFOW_LOG_PUBLISH,
						'comment' => $action
					));
				}
				
				/// check if auto log entry for change of workflow status should be written (article only)
				if ($table == 'tx_newspaper_article' & array_key_exists('workflow_status', $fieldArray) && array_key_exists('workflow_status', $_REQUEST)) {
					tx_newspaper::insertRows('tx_newspaper_log', array(
						'pid' => $fieldArray['pid'],
						'tstamp' => $current_time,
						'crdate' => $current_time, 
						'cruser_id' => $be_user, 
						'be_user' => $be_user, // same value as cruser_id, but this field is visible in backend
						'table_name' => $table, 
						'table_uid' => $id,
						'action' => NP_WORKLFOW_LOG_CHANGE_ROLE,
						'comment' => self::getWorkflowStatusChangedComment(intval($fieldArray['workflow_status']), intval($_REQUEST['workflow_status_ORG']))
					));
				}
				
				/// check if manual comment should be written (this log record should always be written LAST)
				if (isset($_REQUEST['workflow_comment']) && $_REQUEST['workflow_comment'] != '') {
					tx_newspaper::insertRows('tx_newspaper_log', array(
						'pid' => $fieldArray['pid'],
						'tstamp' => $current_time,
						'crdate' => $current_time, 
						'cruser_id' => $be_user, 
						'be_user' => $be_user, // same value as cruser_id, but this field is visible in backend
						'table_name' => $table, 
						'table_uid' => $id,
						'action' => NP_WORKLFOW_LOG_USERCOMMENT, 
						'comment' => $_REQUEST['workflow_comment']
					));
				}
/// \todo: if ($redirectToPlacementModule) { ...}
			}
		}
	}
}

//tx_newspaper::registerSaveHook(new tx_newspaper_Workflow());
 
?>