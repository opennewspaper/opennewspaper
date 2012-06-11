<?php
/*
 * Created on 29.01.2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require_once('private/class.tx_newspaper_logrun.php');

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
define('NP_WORKLFOW_LOG_CHANGE_FIELD', 8);
define('NP_WORKLFOW_LOG_CHANGE_EXTRA', 9);

define('NP_WORKLFOW_LOG_PLACEMENT_INSERT_AFTER', 20);
define('NP_WORKLFOW_LOG_PLACEMENT_MOVE_AFTER', 21);
define('NP_WORKLFOW_LOG_PLACEMENT_SHOW', 22);
define('NP_WORKLFOW_LOG_PLACEMENT_INHERIT', 23);
define('NP_WORKLFOW_LOG_PLACEMENT_DELETE', 24);
define('NP_WORKLFOW_LOG_PLACEMENT_CUT_PASTE', 25);
define('NP_WORKLFOW_LOG_PLACEMENT_COPY_PASTE', 26);

define('NP_WORKLFOW_LOG_WEBMASTER_TOOL_INHERITANCE_SOURCE', 40);


define('NP_WORKFLOW_COMMENTS_PREVIEW_LIMIT', 2);

define('NP_ARTICLE_WORKFLOW_NOCLOSE', false); // if set to true the workflow buttons don't close the form (better for testing)
define('NP_SHOW_PLACE_BUTTONS', false); // \todo after pressing the place button the article gets stores, workflow_status is set to 1 AND the placement form is opened. as that "open placement form" feature isn't implemented, this const can be used to hide the buttons in the backend



class tx_newspaper_Workflow {

    static $operations_in_loglevel = array(
        // default loglevel shown in production list
        0 => array(
            1, 2, 3, 4, 5, 6
        )
    );

	// render workflow storage buttons for tx_newspaper_areticle given in $row
	/** Depending on the workflow and the hidden field status various combinations of buttons can be rendered
	 *  hide if article is published, publish if article is hidden
	 *  show buttons: send to editorial staff or duty editor
	 *  and combinations of role and hidden status
	 *  \param $row article data as associative array
	 *  \return html code with all the needed buttons
	 */
	public static function getWorkflowButtons($row) {
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

		$content = '<span style="margin-left:20px;">&nbsp;</span>'; // left margin for first button

		/// hide / publish
		if (!$hidden && $button['hide']) {
			$content .= self::renderWorkflowButton(false, tx_newspaper::getTranslation('label_workflow_hide'), $hidden, true);
		} elseif ($hidden && $button['publish']) {
			$content .= self::renderWorkflowButton(false, tx_newspaper::getTranslation('label_workflow_publish'), $hidden, true);
		}

		/// check / revise / place
		if ($button['check']) {
			$content .= self::renderWorkflowButton(NP_ACTIVE_ROLE_DUTY_EDITOR, tx_newspaper::getTranslation('label_workflow_check'), -1, false);
			if (!$hidden && $button['hide']) {
				$content .= self::renderWorkflowButton(NP_ACTIVE_ROLE_DUTY_EDITOR, tx_newspaper::getTranslation('label_workflow_check_hide'), $hidden, true);
			} elseif ($hidden && $button['publish']) {
				$content .= self::renderWorkflowButton(NP_ACTIVE_ROLE_DUTY_EDITOR, tx_newspaper::getTranslation('label_workflow_check_publish'), $hidden, true);
			}
		}
		if ($button['revise']) {
			$content .= self::renderWorkflowButton(NP_ACTIVE_ROLE_EDITORIAL_STAFF, tx_newspaper::getTranslation('label_workflow_revise'), -1, false);
			if (!$hidden && $button['hide']) {
				$content .= self::renderWorkflowButton(NP_ACTIVE_ROLE_EDITORIAL_STAFF, tx_newspaper::getTranslation('label_workflow_revise_hide'), $hidden, true);
			} elseif ($hidden && $button['publish']) {
				$content .= self::renderWorkflowButton(NP_ACTIVE_ROLE_EDITORIAL_STAFF, tx_newspaper::getTranslation('label_workflow_revise_publish'), $hidden, true);
			}
		}
// deprecated, \todo: how to call placement form???
//		if (NP_SHOW_PLACE_BUTTONS) {
//			// hide place buttons until opening placement form feature is implemented
//			if ($button['place']) {
//				$content .= self::renderWorkflowButton(2, tx_newspaper::getTranslation('label_workflow_place'), -1);
//				if (!$hidden && $button['hide'])
//					$content .= self::renderWorkflowButton(2, tx_newspaper::getTranslation('label_workflow_place_hide'), $hidden);
//				elseif ($hidden && $button['publish'])
//					$content .= self::renderWorkflowButton(2, tx_newspaper::getTranslation('label_workflow_place_publish'), $hidden);
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
			"FROM_UNIXTIME(`crdate`, '%d.%m.%Y %H:%i') as created, crdate, be_user, operation, comment",
			'tx_newspaper_log',
            'table_name = \''.$table.'\' AND table_uid = '.$table_uid,
			'',
			'crdate desc',
			($limit > 0) ? $limit : ''
		);
        return $comments;
    }

    private static function renderTemplate($comments, $tableUid, $allComments=true, $showFoldLinks=false) {
		self::addUsername($comments);
		$smarty = new tx_newspaper_Smarty();
		$smarty->assign('comments', $comments);
		$smarty->assign('tableUid', $tableUid);
		$smarty->assign('allComments', $allComments);
		$smarty->assign('showFoldLinks', $showFoldLinks);
		$smarty->assign('LABEL', array(
			'more' => tx_newspaper::getTranslation('log_more_link'),
			'less' => tx_newspaper::getTranslation('log_less_link')
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
		return tx_newspaper::getBeUserData('tx_newspaper_role');
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
		$log = tx_newspaper::getTranslation('label_workflow_role_change');
		$log = str_replace('###OLD_ROLE###', self::getRoleTitle($old_role), $log);
		$log = str_replace('###NEW_ROLE###', self::getRoleTitle($new_role), $log);
		return $log;
//		return  . ' --> ' . self::getRoleTitle($new_role);
//		return tx_newspaper::getTranslation('label_workflow_role_new') . ' "' .
//			self::getRoleTitle($new_role) . '", ' . //tx_newspaper::getTranslation('label_workflow_role_' . intval($new_role), false) . '", ' .
//			tx_newspaper::getTranslation('label_workflow_role_old') . '"' .
//			self::getRoleTitle($old_role) . '"'; // tx_newspaper::getTranslation('label_workflow_role_' . intval($old_role), false) . '"';
	}

	public static function getRoleTitle($role) {
		switch($role) {
			case NP_ACTIVE_ROLE_EDITORIAL_STAFF:
				return tx_newspaper::getTranslation('label_workflow_role_editorialstaff');
			case NP_ACTIVE_ROLE_DUTY_EDITOR:
				return tx_newspaper::getTranslation('label_workflow_role_dutyeditor');
			case NP_ACTIVE_ROLE_NONE:
				return tx_newspaper::getTranslation('label_workflow_role_none');
		}
		t3lib_div::devlog('getRoleTitle() - unknown role', 'newspaper', 3, array('role' => $role));
		return '[' . $role . ']'; // no role title available
	}



 	// typo3 save hook ...
 	public static function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, $that) {
 	}

	/// typo3 save hook ...
	public static function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, $that) {
        $timer = tx_newspaper_ExecutionTimer::create();
		self::processAndLogWorkflow($status, $table, $id, $fieldArray);
	}


 	/// modify $fieldArray if the workflow and/ or the hidden status for an article changed
 	private static function checkIfWorkflowStatusChanged(array &$fieldArray, $table, $id) {
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
        $log_run = new tx_newspaper_LogRun(0, $table, $id);
        $log_run->write($type, $comment);
	}



	/**
	 * Write to workflow log when placement changes are processed
	 * @param $table Assiciated table
	 * @param $id Assiciated uid
	 * @param array $data Data to be stored in lof entry
	 * @param $type workflow log type (NP_WORKLFOW_LOG_...)
	 */
	public static function logPlacement($table, $id, array $data, $type) {

		// see: http://php.net/manual/de/function.implode.php#103861
		$comment = implode(array_map(create_function('$key, $value', 'return $key.":".$value."| ";'), array_keys($data), array_values($data)));

		// \todo: remove when data is written as a seralized array to the database (when a backend module to access this log is available)
		switch($type) {
			case 20:
				$comment .= 'INSERT_AFTER';
			break;
			case 21:
				$comment .= 'MOVE_AFTER';
			break;
			case 22:
				$comment .= 'SHOW';
			break;
			case 23:
				$comment .= 'INHERIT';
			break;
			case 24:
				$comment .= 'DELETE';
			break;
			case 25:
				$comment .= 'CUT_PASTE';
			break;
			case 26:
				$comment .= 'COPY_PASTE';
			break;
			case 40:
				$comment .= 'WEBMASTER_TOOL_INHERITANCE_SOURCE';
			break;
			default:
				$comment .= 'UNKNOWN TYPE ' . intval($type);
		}

		// write log entry
		self::directLog($table, $id, $comment, $type);

	}


	/// write log data for newspaper classes implemting the tx_newspaper_WritesLog interface
	public static function processAndLogWorkflow($status, $table, $id, &$fieldArray) {

/*
t3lib_div::devlog('processAndLogWorkflow()','newspaper', 0, array('table' => $table, 'id' => $id, 'fieldArray' => $fieldArray, '_request' => $_REQUEST));
t3lib_div::devlog('processAndLogWorkflow()','newspaper', 0, array('debug_backtrace' => debug_backtrace()));

        self::writeLogEntry(
            NP_WORKLFOW_LOG_UNKNOWN,
            "Tabelle geaendert: $table",
            $fieldArray['pid'], $table, $id
        );
 */
		if (!self::isLoggableClass($table)) return;

        /** @var $object tx_newspaper_StoredObject */
        $object = new $table($id);

        $log_run = new tx_newspaper_LogRun($object, $fieldArray['pid']);

		self::checkIfWorkflowStatusChanged($fieldArray, $table, $id); // IMPORTANT: might alter $fieldArray !

        self::writePublishingStatusEntry($log_run, $fieldArray, $object);

        self::writeArticleSpecificLogEntries($log_run, $fieldArray, $object);

        self::writeWebElementSpecificLogEntries($log_run, $fieldArray, $object);

        self::writeManuallySpecifiedLogEntries($log_run);

/// \todo: if ($redirectToPlacementModule) { ...}
	}

    /// check if auto log entry for hiding/publishing newspaper record should be written
    private static function writePublishingStatusEntry(tx_newspaper_LogRun $log_run, array $fieldArray, tx_newspaper_StoredObject $object) {
        if (array_key_exists('hidden', $fieldArray)) {
            $log_run->write(
                $fieldArray['hidden'] ? NP_WORKLFOW_LOG_HIDE : NP_WORKLFOW_LOG_PUBLISH,
                self::getPublishingStatusComment($object, $fieldArray)
            );
//t3lib_div::devlog('processAndLogWorkflow() hidden status','newspaper', 0, array('debug_backtrace' => debug_backtrace()));
        }
    }

    private static $fields_to_log_changes_for_in_article = array('kicker', 'title', 'teaser', 'bodytext');
    /// check if auto log entry for change of workflow status should be written (article only)
    private static function writeArticleSpecificLogEntries(tx_newspaper_LogRun $log_run, array $fieldArray, tx_newspaper_StoredObject $object) {

        if (!$object instanceof tx_newspaper_Article) return;

        if(array_key_exists('workflow_status', $fieldArray) && array_key_exists('workflow_status', $_REQUEST)) {
            $log_run->write(
                NP_WORKLFOW_LOG_CHANGE_ROLE,
                self::getWorkflowStatusChangedComment(intval($fieldArray['workflow_status']), intval($_REQUEST['workflow_status_ORG']))
            );
        }

        tx_newspaper::devlog('writeArticleSpecificLogEntries()', $fieldArray);

        $changed_fields = self::getChangedFields($fieldArray, $object);
        if (!empty($changed_fields)) {
            $log_run->write(
                NP_WORKLFOW_LOG_CHANGE_FIELD,
                tx_newspaper::getTranslation('label_workflow_field_changed') . ' ' .
                        implode(', ', self::getFieldTranslations($changed_fields))
            );
        }

    }

    private static function getChangedFields(array $fieldArray, tx_newspaper_Article $article) {
        $marked = array_intersect(array_keys($fieldArray), self::$fields_to_log_changes_for_in_article);
        if (in_array('bodytext', $marked)) {
            $diff = self::diff(explode(' ', $article->getAttribute('bodytext')), explode(' ', $fieldArray['bodytext']));
            tx_newspaper::devlog('getChangedFields()', $diff);
        }
        return $marked;
    }

    private static function diff(array $old, array $new){
    	foreach($old as $oindex => $ovalue){
    		$nkeys = array_keys($new, $ovalue);
    		foreach($nkeys as $nindex){
    			$matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
    				$matrix[$oindex - 1][$nindex - 1] + 1 : 1;
    			if($matrix[$oindex][$nindex] > $maxlen){
    				$maxlen = $matrix[$oindex][$nindex];
    				$omax = $oindex + 1 - $maxlen;
    				$nmax = $nindex + 1 - $maxlen;
    			}
    		}
    	}
    	if($maxlen == 0) return array(array('d'=>$old, 'i'=>$new));
    	return array_merge(
    		self::diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
    		array_slice($new, $nmax, $maxlen),
    		self::diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
    }

    private static function writeWebElementSpecificLogEntries(tx_newspaper_LogRun $log_run, array $fieldArray, tx_newspaper_StoredObject $object) {
        if (!$object instanceof tx_newspaper_Extra) return;
        if (!self::getArticleUid($object)) return;

        $log_run->write(NP_WORKLFOW_LOG_CHANGE_EXTRA, self::getExtraChangedMessage($object));
    }

    private static function getArticleUid(tx_newspaper_Extra $extra) {

        $table = $extra->getTable();
        // @todo INDEX on tx_newspaper_article_extras_mm.uid_foreign is not used - why?
        $data = tx_newspaper::selectZeroOrOneRows(
            'tx_newspaper_article_extras_mm.uid_local',
            "$table JOIN tx_newspaper_extra ON tx_newspaper_extra.extra_uid = $table.uid AND tx_newspaper_extra.extra_table = '$table'
                    JOIN tx_newspaper_article_extras_mm ON tx_newspaper_extra.uid = tx_newspaper_article_extras_mm.uid_foreign",
            "$table.uid = " . $extra->getUid()
        );
        tx_newspaper::devlog('getArticleUid()', tx_newspaper::$query);
        return intval($data['uid_local']);
    }

    private static function getExtraChangedMessage(tx_newspaper_Extra $extra) {
        return tx_newspaper::getTranslation('label_workflow_extra_changed') . ' ' . $extra->getDescription();
    }

    private static function getFieldTranslations($fields) {
        $translations = array();
        foreach ($fields as $field) {
            $translations[] = tx_newspaper::getTranslation('tx_newspaper_article.' . $field, 'locallang_db.xml');
        }
        return $translations;
    }


    /// check if manual comment should be written (this log record should always be written LAST)
    private static function writeManuallySpecifiedLogEntries(tx_newspaper_LogRun $log_run) {
        if (isset($_REQUEST['workflow_comment']) && $_REQUEST['workflow_comment'] != '') {
            $log_run->write(NP_WORKLFOW_LOG_USERCOMMENT, $_REQUEST['workflow_comment']);
        }
    }

    /** check if a newspaper record operation should be logged */
    private static function isLoggableClass($class) {
        return class_exists($class) && !tx_newspaper::isAbstractClass($class) &&
               in_array("tx_newspaper_WritesLog", class_implements($class));
    }

    private static function getPublishingStatusComment(tx_newspaper_StoredObject $object, array $fieldArray) {
        $type = ($object instanceof tx_newspaper_Article? 'article': 'record');
        return $fieldArray['hidden']?
                tx_newspaper::getTranslation("log_${type}_hidden") :
                tx_newspaper::getTranslation("log_${type}_published");
    }

}

//tx_newspaper::registerSaveHook(new tx_newspaper_Workflow());
