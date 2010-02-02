<?php
/*
 * Created on 29.01.2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
define('NP_WORKLFOW_LOG_HIDE', 1);
define('NP_WORKLFOW_LOG_PUBLISH', 2);
define('NP_WORKLFOW_LOG_CHANGE_ROLE', 3);
define('NP_WORKLFOW_LOG_USERCOMMENT', 4);

 
class tx_newspaper_WorkflowLog {   

	public static function renderBackend($table, $uid) {
		return 'Hello newspaper!';
	}
 

	/// \return message: what status change took place
	public static function getWorkflowStatusChangedComment($new_status, $old_status) {
		global $LANG;
		return $LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_status_new', false) . ' "' . 
			$LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_status_' . intval($new_status), false) . '", ' . 
			$LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_status_old', false) . '"' .
			$LANG->sL('LLL:EXT:newspaper/locallang_newspaper.xml:label_workflow_status_' . intval($old_status), false) . '"';
	}
	


 	// typo3 save hook ...
 	public static function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, $that) {
 		self::checkIfWorkflowStatusChanged($incomingFieldArray, $table, $id);
 	}

 	
 	/// modify $incominigFieldArray if the workflow status for an article changed
 	private static function checkIfWorkflowStatusChanged(&$incomingFieldArray, $table, $id) {
//t3lib_div::devlog('checkIfWorkflowStatusChanged()', 'newspaper', 0, array('incomingFieldArray' => $incomingFieldArray, 'table' => $table, 'id' => $id, '_REQUEST' => $_REQUEST));

		if (strtolower($table) != 'tx_newspaper_article') {
			return; // workflow status is currently defined for newspaper articles only
		}
		
		$request = $_REQUEST; // copy array, because values might be overwritten
		if (array_key_exists('hidden_status', $request) && $request['hidden_status'] != -1 && $request['hidden_status'] != $request['data'][$table][$id]['hidden']) {
			$incomingFieldArray['hidden'] = $request['hidden_status']; // if hide/publish button was used, overwrite value of field "hidden"
		}
		if (!array_key_exists('workflow_status', $request) || !array_key_exists('workflow_status_ORG', $request)) {
			return; // value not set, so can't decide if the status changed 
		}
		if ($request['workflow_status'] == $request['workflow_status_ORG']) {
			return; // status wasn't changed, so don't store value
		}
		$incomingFieldArray['workflow_status'] = $request['workflow_status']; // change workflow status
	}



	/// typo3 save hook ...
	public static function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, $that) {
		self::logWorkflow($status, $table, $id, $fieldArray);
	}

	
	/// write log data for newspaper classes implemting the tx_newspaper_WritesLog interface
	private static function logWorkflow($status, $table, $id, &$fieldArray) {
		global $LANG;
		
		if (class_exists($table) && !tx_newspaper::isAbstractClass($table)) { ///<newspaper specification: table name = class name
			$np_obj = new $table();

			/// check if a newspaper record action should be logged
			if (in_array("tx_newspaper_WritesLog", class_implements($np_obj))) {

				/// IMPORTANT: checkIfWorkflowStatusChangeds() has run, so $fieldArray has been modified already

				$request = $_REQUEST;

//debug($GLOBALS['BE_USER']);				
				$be_user = $GLOBALS['BE_USER']->user['uid']; /// i'm not sure if this object is always available, we'll see ...
				
				// check if the placement form should be opened after saving the record
				// \todo if that's possible ...
				$redirectToPlacementModule = false;
				if (isset($request['workflow_status']) && isset($request['workflow_status_ORG']) && $request['workflow_status'] == 2 && $request['workflow_status_ORG'] != 2) {
					$redirectToPlacementModule = true;
					$request['workflow_status'] = 1; /// active role is set to duty editor, but placement form is opened immediately. it that form is saved, workflow_status is set to 2
					$fieldArray['workflow_status'] = 1;						
				}
				
				
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
				if ($table == 'tx_newspaper_article' & array_key_exists('workflow_status', $fieldArray) && array_key_exists('workflow_status', $request)) {
					tx_newspaper::insertRows('tx_newspaper_log', array(
						'pid' => $fieldArray['pid'],
						'tstamp' => $current_time,
						'crdate' => $current_time, 
						'cruser_id' => $be_user, 
						'be_user' => $be_user, // same value as cruser_id, but this field is visible in backend
						'table_name' => $table, 
						'table_uid' => $id,
						'action' => NP_WORKLFOW_LOG_CHANGE_ROLE,
						'comment' => tx_newspaper_WorkflowLog::getWorkflowStatusChangedComment(intval($fieldArray['workflow_status']), intval($request['workflow_status_ORG']))
					));
				}
				
				/// check if manual comment should be written (this log record should always be written LAST)
				if (isset($request['workflow_comment']) && $request['workflow_comment'] != '') {
					tx_newspaper::insertRows('tx_newspaper_log', array(
						'pid' => $fieldArray['pid'],
						'tstamp' => $current_time,
						'crdate' => $current_time, 
						'cruser_id' => $be_user, 
						'be_user' => $be_user, // same value as cruser_id, but this field is visible in backend
						'table_name' => $table, 
						'table_uid' => $id,
						'action' => NP_WORKLFOW_LOG_USERCOMMENT, 
						'comment' => $request['workflow_comment']
					));
				}
/// \todo: if ($redirectToPlacementModule) { ...}
			}
		}
	}


}
 
?>