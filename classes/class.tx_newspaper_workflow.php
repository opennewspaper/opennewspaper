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

 
define('NP_WORKLFOW_LOG_HIDE', 1);
define('NP_WORKLFOW_LOG_PUBLISH', 2);
define('NP_WORKLFOW_LOG_CHANGE_ROLE', 3);
define('NP_WORKLFOW_LOG_USERCOMMENT', 4);

 
class tx_newspaper_Workflow {   

	 public static function renderBackend($table, $tableUid, $allComments=false) {
        if(!$table || !$tableUid) {
            throw new tx_newspaper_Exception("Arguments table and tableUid may not be null");
        }
        $tableUid = intval($tableUid);
        if($allComments) {
            $comments = self::getAllComments($table, $tableUid);
        } else {
            $comments = self::getLatestComments($table, $tableUid);
        }

        $comments = self::addUsername($comments);
        return self::renderTemplate($comments, $tableUid, $allComments);
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

            test = path.substring(path.lastIndexOf("/") - 5);
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
    
    private static function getAllComments($table, $uid) {
		$comments = tx_newspaper::selectRows("FROM_UNIXTIME( `crdate`, '%d.%m.%Y %H:%i' ) as created, be_user, action, comment",'tx_newspaper_log', 'table_name = \''.$table.'\' AND table_uid = '.$uid.' ORDER BY uid DESC');
        return $comments;
	}

    private static function getLatestComments($table, $table_uid) {
        $latestComments = "SELECT FROM_UNIXTIME( `crdate`, '%d.%m.%Y %H:%i' ) as created, crdate, be_user, action, comment FROM `tx_newspaper_log` WHERE";
        $latestComments = $latestComments." table_name = '$table' AND table_uid = $table_uid";
        $latestComments = $latestComments." AND crdate = (SELECT MAX(crdate) FROM tx_newspaper_log WHERE table_name = '$table' AND table_uid = $table_uid) ORDER BY uid DESC";

        $res = $GLOBALS['TYPO3_DB']->sql_query($latestComments);
        $comments = array();
        while($comment = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $comments[] = $comment;
        }
        return $comments;
    }

    private static function renderTemplate($comments, $tableUid, $allComments=false) {
		global $LANG;
		self::addUsername($comments);
		$smarty = new tx_newspaper_Smarty();
		$smarty->assign('comments', $comments);
		$smarty->assign('tableUid', $tableUid);
		$smarty->assign('allComments', $allComments);
		$smarty->assign('LABEL', array(
			'more' => $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:log_more_link', false),
			'less' => $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:log_less_link', false)
		));
		$smarty->setTemplateSearchPath(array(PATH_typo3conf . 'ext/newspaper/res/be/templates'));
		return $smarty->fetch('workflow_comment_output.tmpl');
    }

    private static function addUsername($comments) {
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
				return true;
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
		return $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_role_new', false) . ' "' . 
			self::getRoleTitle($new_role) . '", ' . //$LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_role_' . intval($new_role), false) . '", ' . 
			$LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_role_old', false) . '"' .
			self::getRoleTitle($old_role) . '"'; // $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_role_' . intval($old_role), false) . '"';
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
//t3lib_div::devlog('checkIfWorkflowStatusChanged()', 'newspaper', 0, array('incomingFieldArray' => $incomingFieldArray, 'table' => $table, 'id' => $id, '_REQUEST' => $_REQUEST));
 	}

	/// typo3 save hook ...
	public static function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, $that) {
		self::logWorkflow($status, $table, $id, $fieldArray);
	}


 	/// modify $fieldArray if the workflow status for an article changed
 	private static function checkIfWorkflowStatusChanged(&$fieldArray, $table, $id) {
//t3lib_div::devlog('checkIfWorkflowStatusChanged()', 'newspaper', 0, array('fieldArray' => $fieldArray, 'table' => $table, 'id' => $id, '_REQUEST' => $_REQUEST));

		if (strtolower($table) != 'tx_newspaper_article') {
			return; // workflow status is currently defined for newspaper articles only
		}
		
		if (isset($_REQUEST['tx_newspaper_mod7'])) {
			// mod7 - moderation list
			$request = $_REQUEST['tx_newspaper_mod7'];
		} else {
			// tx_newspaper_article backend
			$request = $_REQUEST;
		}


		if (strtolower($table) != 'tx_newspaper_article') {
			return; // workflow status is currently defined for newspaper articles only
		}
		
		if (array_key_exists('hidden_status', $request) && $request['hidden_status'] != -1 && $request['hidden_status'] != $request['data'][$table][$id]['hidden']) {
			$fieldArray['hidden'] = $request['hidden_status']; // if hide/publish button was used, overwrite value of field "hidden"
		}
		if (!array_key_exists('workflow_status', $request) || !array_key_exists('workflow_status_ORG', $request)) {
			return; // value not set, so can't decide if the status changed 
		}
		if ($request['workflow_status'] == $request['workflow_status_ORG']) {
			return; // status wasn't changed, so don't store value
		}
		$fieldArray['workflow_status'] = $request['workflow_status']; // change workflow status
	}

	
	/// write log data for newspaper classes implemting the tx_newspaper_WritesLog interface
	public static function logWorkflow($status, $table, $id, &$fieldArray) {
		global $LANG;
//t3lib_div::devlog('logWorkflow()','newspaper', 0, array('table' => $table, 'id' => $id, 'fieldArray' => $fieldArray, '_request' => $_REQUEST));		
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

tx_newspaper::registerSaveHook(new tx_newspaper_Workflow());
 
?>