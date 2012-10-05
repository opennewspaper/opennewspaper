<?php
/*
 * Created on 29.01.2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require_once('private/class.tx_newspaper_logrun.php');
require_once('private/class.tx_newspaper_diff.php');

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
/*
 * @todo after pressing the place button the article gets stores, workflow_status is set to 1 AND the placement form is opened.
 *       as that "open placement form" feature isn't implemented, this const can be used to hide the buttons in the backend
 */
define('NP_SHOW_PLACE_BUTTONS', false);


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







	 public static function renderBackend($table, $tableUid, $show_all_comments=true, $show_fold_links=false) {
        if(!$table || !$tableUid) {
            throw new tx_newspaper_Exception("Arguments table and tableUid may not be null");
        }

        return self::renderTemplate(
            self::getComments($table, $tableUid, $show_all_comments? 0: NP_WORKFLOW_COMMENTS_PREVIEW_LIMIT),
            self::getComments($table, $tableUid, $show_all_comments? 0: NP_WORKFLOW_COMMENTS_PREVIEW_LIMIT, 1),
            intval($tableUid), $show_all_comments, $show_fold_links);
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

    private static $operations_in_loglevel = array(
        // default loglevel shown in production list
        0 => array( 1, 2, 3, 4, 5, 6),
        // all logged messages shown
        1 => array( 0, 1, 2, 3, 4, 5, 6, 7, 8, 9)
    );

    public static function getComments($table, $table_uid, $limit = 0, $log_level = 0) {

        $where = "tx_newspaper_log.table_name = '$table' AND tx_newspaper_log.table_uid = $table_uid";
        if (is_array(self::$operations_in_loglevel[$log_level])) {
            $where .= ' AND tx_newspaper_log.operation IN (' . implode(', ', self::$operations_in_loglevel[$log_level]) . ')';
        }

        return tx_newspaper::selectRows(
			"FROM_UNIXTIME(tx_newspaper_log.crdate, '%d.%m.%Y %H:%i') as created, tx_newspaper_log.crdate,
			    tx_newspaper_log.be_user, tx_newspaper_log.operation, tx_newspaper_log.comment, tx_newspaper_log.details,
                IF(be_users.realname != '', be_users.realname, be_users.username) AS username",
			'tx_newspaper_log JOIN be_users ON  tx_newspaper_log.be_user = be_users.uid',
            $where,
			'',
			'tx_newspaper_log.crdate desc',
			($limit > 0) ? $limit : ''
		);
    }

    public static function addWorkflowTranslations(tx_newspaper_Smarty $smarty) {
        foreach (array('label_workflow_show_all_messages', 'label_workflow_show_default_messages', 'label_workflow_show_details') as $label) {
            $smarty->assign($label, tx_newspaper::getTranslation($label));
        }
    }

    private static function renderTemplate(array $comments, array $all_comments, $tableUid, $show_all_comments=true, $show_fold_links=false) {
		$smarty = new tx_newspaper_Smarty();
		$smarty->assign('comments', $comments);
        $smarty->assign('all_comments', $all_comments);
        self::addWorkflowTranslations($smarty);
		$smarty->assign('tableUid', $tableUid);
		$smarty->assign('show_all_comments', $show_all_comments);
		$smarty->assign('showFoldLinks', $show_fold_links);
		$smarty->assign('LABEL', array(
			'more' => tx_newspaper::getTranslation('log_more_link'),
			'less' => tx_newspaper::getTranslation('log_less_link')
		));
        $smarty->assign('ABSOLUTE_PATH', tx_newspaper::getAbsolutePath());
		$smarty->setTemplateSearchPath(array(PATH_typo3conf . 'ext/newspaper/res/be/templates'));
		return $smarty->fetch('workflow_comment_output.tmpl');
    }

    /**
     * Get newspaper role (either from be_user or from User TSConfig
     * @static
     * @return 0 for editor, 1 for duty editor
     */
    /// \return role set in be_users (or false if not available)
    public static function getRole() {
        $role = tx_newspaper::getBeUserData('tx_newspaper_role');

        if ($role === false) {
            // No role stored in be_user so try to read a default role from User TSConfig
            $role = self::getDefaultRole();
        }

		return $role;
	}


    /**
     * Reads role from User TSConfig (or return 0 for editor as default)
     * User TSConfig: newspaper.defaultRole = [0 = editor, 1 = duty editor]
     * @static
     * @return int Role (defaults to 0 = editor)
     */
    public static function getDefaultRole() {
        $role = intval($GLOBALS['BE_USER']->getTSConfigVal('newspaper.defaultRole')); // This produces 0 as default ...

        // A basic check if the configured role is valid
        if ($role != 0 && $role != 1) {
            t3lib_div::devlog('newspaper.defaultRole set to wrong value, 0 and 1 are allowed. Role is set to 0 (= editor)', 'newspaper', 0, array('configured value' => $role));
            $role = 0;
        }

        //@todo: Store role in be_users ??

        return $role;
    }


	/// \return true if be_user has newspaper role "duty editor"
	public static function isDutyEditor() {
		return (self::getRole() == 1);
	}

	/// \return true if be_user has newspaper role "editorial staff"
	public static function isEditor() {
        return (self::getRole() === 0);
	}

    public static function placementAllowedLevel() {
        return intval($GLOBALS['BE_USER']->getTSConfigVal('newspaper.placementAllowedLevel'));
    }

    public static function canPlaceArticles(tx_newspaper_Section $section = null) {
        if (self::isDutyEditor()) return true;
        if (is_null($section)) return self::placementAllowedLevel() > 0;
        return self::placementAllowedLevel() <= sizeof($section->getRootLine());
    }

    /**
     * Check if the be_user is allowed to publish a newspaper article
     * @static
     * @return bool true, if publishing is allowed
     */
    public static function canPublishArticles() {
        return self::isDutyEditor() || self::mayPublishAsEditor();
    }

    /**
     * Checks if the be_user may publish even when newspaper role is set to editor
     * User TSConfig: newspaper.editorMayPublish = [0|1]
     * @static
     * @return bool true, if publishing is allowed
     */
    public static function mayPublishAsEditor() {
        return (bool) ($GLOBALS['BE_USER']->getTSConfigVal('newspaper.editorMayPublish'));
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

        /** @todo I don't think the following block is a good idea. It makes debugging harder for admins. */
		if ($GLOBALS['BE_USER']->isAdmin()) {
			return true; // admins can see all buttons
		}

		if ($role === false) {
			return false;
		}

		switch(strtolower($feature)) {
			case 'hide':
				return true;
			case 'publish':
                return self::canPublishArticles();
			case 'check':
				return true;
			case 'revise':
			    return ($role == NP_ACTIVE_ROLE_DUTY_EDITOR);
			case 'place':
				return self::canPlaceArticles();
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
        $object = self::safelyGenerateObject($table, $id);
        if (is_null($object)) return;
        $log_run = new tx_newspaper_LogRun($object, 0);
        $log_run->write(intval($type), $comment);
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
 */
		if (empty($fieldArray) || !self::isLoggableClass($table)) return;

        $object = self::safelyGenerateObject($table, $id);
        if (is_null($object)) return;

        $log_run = new tx_newspaper_LogRun($object, $fieldArray['pid']);

		self::checkIfWorkflowStatusChanged($fieldArray, $table, $id); // IMPORTANT: might alter $fieldArray !

        self::writePublishingStatusEntry($log_run, $fieldArray, $object);

        self::writeArticleSpecificLogEntries($log_run, $fieldArray, $object);

        self::writeWebElementSpecificLogEntries($log_run, $fieldArray, $object);

        self::writeManuallySpecifiedLogEntries($log_run);

/// \todo: if ($redirectToPlacementModule) { ...}
	}

    /**
     * @return tx_newspaper_StoredObject
     */
    private static function safelyGenerateObject($table, $id) {
        if (!intval($id)) return null;
        if (tx_newspaper::isAbstractClass($table)) return null;
        if (!tx_newspaper::classImplementsInterface($table, 'tx_newspaper_StoredObject')) return null;
        return new $table(intval($id));
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

        $changed_fields = self::getChangedFields($fieldArray, $object);
        if (!empty($changed_fields)) {
            $log_run->write(
                NP_WORKLFOW_LOG_CHANGE_FIELD,
                tx_newspaper::getTranslation('label_workflow_field_changed') . ' ' . implode(', ', self::getFieldTranslations($changed_fields)),
                "<table>\n" . implode('', self::getDiffDescriptions($object, $fieldArray, $changed_fields)) . "</table>\n"

            );
        }

    }

    private static function getDiffDescriptions(tx_newspaper_Article $article, array $fieldArray, array $changed_fields) {
        $diffs = array();
        foreach ($changed_fields as $field) {
            $diff = new tx_newspaper_Diff($article->getAttribute($field), $fieldArray[$field]);
            $diffs[$field] = self::getFormattedDetailsField(self::getFieldTranslation($field), $diff->textDiff());
        }
        return $diffs;
    }

    private static function getFormattedDetailsField($field_name, $field_content) {
        return "<tr><td width='100%'>$field_name</td></tr><tr><td width='100%'>$field_content</td></tr>\n";
    }

    private static function getChangedFields(array $fieldArray, tx_newspaper_Article $article) {
        $marked = array_intersect(array_keys($fieldArray), self::$fields_to_log_changes_for_in_article);
        if (in_array('bodytext', $marked)) {
            $diff = new tx_newspaper_Diff($article->getAttribute('bodytext'), $fieldArray['bodytext']);
            if (!$diff->isDifferent()) {
                unset($marked[array_search('bodytext', $marked)]);
            }
        }
        return $marked;
    }


    private static function writeWebElementSpecificLogEntries(tx_newspaper_LogRun $log_run, array $fieldArray, tx_newspaper_StoredObject $object) {
        if (!$object instanceof tx_newspaper_Extra) return;

        $article_uid = self::getArticleUid($object);
        if ($article_uid) {
            $log_run = new tx_newspaper_LogRun(new tx_newspaper_Article($article_uid), $fieldArray['pid']);
        }

        $log_run->write(
            NP_WORKLFOW_LOG_CHANGE_EXTRA,
            self::getExtraChangedText($object),
            self::getExtraChangedDetails($object, $fieldArray)
        );
    }

    private static function getArticleUid(tx_newspaper_Extra $extra) {
        $data = tx_newspaper::selectZeroOrOneRows(
            'tx_newspaper_article_extras_mm.uid_local',
            'tx_newspaper_extra
             JOIN tx_newspaper_article_extras_mm ON tx_newspaper_extra.uid = tx_newspaper_article_extras_mm.uid_foreign',
            'tx_newspaper_extra.uid = ' . $extra->getExtraUid()
        );
        return intval($data['uid_local']);
    }

    private static function getExtraChangedText(tx_newspaper_Extra $extra) {
        return tx_newspaper::getTranslation('label_workflow_extra_changed') . ' ' . $extra->getTitle();
    }

    private static function getExtraChangedDetails(tx_newspaper_Extra $extra, array $fieldArray) {
        if (!$extra->getUid()) {
            return tx_newspaper::getTranslation('label_workflow_extra_created');
        }

        $old = $extra->getDescription();
        foreach ($fieldArray as $attribute => $value) {
            $extra->setAttribute($attribute, $value);
        }
        $new = $extra->getDescription();
        return "$old -> $new";
    }

    private static function getFieldTranslations($fields) {
        $translations = array();
        foreach ($fields as $field) {
            $translations[] = self::getFieldTranslation($field);
        }
        return $translations;
    }

    private static function getFieldTranslation($field) {
        return tx_newspaper::getTranslation('tx_newspaper_article.' . $field, 'locallang_db.xml');
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
