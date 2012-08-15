<?php
/**
 *  \file class.tx_newspaper_util_mod.php
 *
 *  \author Oliver Schröder <newspaper@schroederbros.de>
 *  \date Mar 21, 2009
 */

/// utility functions for backend modules
class tx_newspaper_UtilMod {


	/// calculates a timestamp in the past based on last midnight (and the given type)
	/** \param $type can be 'today' (default), 'no_limit' or 'day_n' [n = positiv integer]
	 *  \return timestamp (last midnight - n days (=n*86400) or 0 for no limit)
	 */
	static public function calculateTimestamp($type) {
		$type = strtolower($type);

		$tmp = getdate();
		$last_midnight = mktime(0, 0, 0, $tmp['mon'], $tmp['mday'], $tmp['year']);

		if ($type == 'today') {
			return $last_midnight;
		}
		if ($type == 'no_limit') {
			return 0;
		}
		if (substr($type, 0, 4) == 'day_') {
			return $last_midnight - intval(substr($type, 4)) * 86400; // 86400 = 1 day in seconds
		}
		return $last_midnight; // default: today only
	}

	/// removes fields from given $tca if a field is disabled in TSConfig: TCEFORM.[table].[field].disabled = 1
	/** \param $tca TCA for a table
	 *  \param $tableTCEFORM TCEFORM part from TSConfig for table that provided $tca fields
	 *  \return TCA fields but fields that where disabled in TSConfig
	 */
	static public function disableTsconfigFieldsInTca(array $tca, $tableTCEFORM) {
		if (!is_array($tableTCEFORM)) {
			return $tca; // no TSConfig found, so just return $tca
		}

		// check TCEFORM configuraion
		/** Example for $tableTCEFORM:
		 *  [field1.][disabled]=1
		 *  [field2.][disabled]=1 ...
		 */
		foreach($tableTCEFORM as $field => $config) {
			if (substr($field, -1) == '.') {
				$field = substr($field, 0, strlen($field)-1); // remove last char, if that char is '.' (TSConfig array is stored as "[table].")
			}
			foreach($config as $configParam => $configValue) {
				if (strtolower($configParam) == 'disabled' && $configValue == 1) {
					// a disabled field is found, so try to remove it from $tca array
					if (array_key_exists($field, $tca)) {
						unset($tca[$field]);
					}
				}
			}
		}
		return $tca;
	}


	/// checks if at least one field in $fields for a given $table are disabled in TCA using TSConfig
	/** \param $table name of table to check
	 *  \param $ fields check if these fields are disabled using TCConfig (string or array)
	 */
	static public function isAvailableInTceform($table, $fields) {
		if (!$table || !$fields) {
			return false;
		}
		if (!is_array($fields)) {
			$fields[] = $fields;
		}
		$table = strtolower($table);


		// read tsconfig
		$tsc = t3lib_BEfunc::getPagesTSconfig(tx_newspaper_Sysfolder::getInstance()->getPidRootfolder());
		if (!isset($tsc['TCEFORM.'][$table . '.'])) {
			return false; // no entries found for table
		}
		$tsc = $tsc['TCEFORM.'][$table . '.']; // extract config for given table
//t3lib_div::devlog('util_mod::tca ...', 'newspaper', 0, array('tsc' => $tsc, 'table' => $table, 'fields' => $fields));

		foreach($fields as $field) {
			if (!isset($tsc[$field . '.']['disabled']) || $tsc[$field . '.']['disabled'] = 0) {
				return true; // field not listed in tsconfig or NOT disabled, so return true
			}
		}

		return false;
	}


	// http://www.typo3.net/index.php?id=13&action=list_post&code_numbering=0&tid=85598
	public static function getTCEFormArray($table, $uid, $isNew=false) {
		$trData = t3lib_div::makeInstance('t3lib_transferData');
		$trData->addRawData = true;
		$trData->fetchRecord($table, $uid, $isNew? 'new' : ''); // 'new'
		reset($trData->regTableItems_data);
		return $trData->regTableItems_data;
	}


		// based on typo3/alt_doc.php;
	/**
	 * Put together the various elements (buttons, selectors, form) into a table
	 * \param $editForm HTML form
	 * \return Composite HTML
	 */
	public static function compileForm($editForm)	{
		return '
			<!-- EDITING FORM -->
			' . $editForm . '
			<input type="hidden" name="closeDoc" value="0" />
			<input type="hidden" name="doSave" value="0" />
			<input type="hidden" name="_scrollPosition" value="" />
			<input type="hidden" name="_serialNumber" value="' . md5(microtime()) . '" />';
	}


}

?>